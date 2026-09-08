'use strict';

const assert = require('assert');
const fs = require('fs');
const path = require('path');
const { loadRegistry } = require('../teleporter');

const serverRoot = path.resolve(__dirname, '..');
const repoRoot = path.resolve(serverRoot, '..');
const cdnPlayRoot = path.join(repoRoot, 'game-full', 'cdn.binw.net', 'play');
const roomIdsText = fs.readFileSync(path.join(serverRoot, 'roomids.txt'), 'utf8');
const locDefsText = fs.readFileSync(path.join(repoRoot, 'game-full', 'binConfig', 'getFile', '7', 'uk', 'locationDefinitions.xml'), 'utf8');
const registry = loadRegistry();

const roomsByName = new Map();
for (const match of roomIdsText.matchAll(/<rm id=['"](\d+)['"][^>]*><n><!\[CDATA\[([^\]]+)\]\]><\/n><\/rm>/g)) {
    roomsByName.set(match[2], Number(match[1]));
}

const locationsById = new Map();
for (const match of locDefsText.matchAll(/<location\s+id=['"](\d+)['"]\s+name=['"]([^'"]+)['"]/g)) {
    locationsById.set(Number(match[1]), match[2]);
}

let missingAssets = [];
for (const destination of registry.destinations.filter(entry => entry.enabled)) {
    assert.strictEqual(locationsById.get(destination.locId), destination.name,
        'locationDefinitions mismatch for locId ' + destination.locId);
    assert.strictEqual(roomsByName.get(destination.name), destination.roomId,
        'roomids mismatch for ' + destination.name);

    const swfCandidates = [
        path.join(cdnPlayRoot, destination.swf),
        path.join(repoRoot, 'game-full', 'cdn.binw.net', destination.swf),
        path.join(repoRoot, 'game-full', 'cdn.binw.net', 'play', destination.swf.replace(/^play[\\/]/, ''))
    ];
    if (!swfCandidates.some(candidate => fs.existsSync(candidate))) {
        missingAssets.push(destination.locId + ':' + destination.swf);
    }
}
assert.deepStrictEqual(missingAssets, [], 'enabled destinations with unresolved primary assets');

for (const required of [106, 108, 196]) {
    const destination = registry.destinations.find(entry => entry.locId === required);
    assert.ok(destination && destination.enabled && destination.category === 'STANDARD',
        'permanent map destination missing from teleporter STANDARD pool: ' + required);
}

const disabled = registry.destinations.filter(entry => !entry.enabled);
assert.strictEqual(disabled.length, 7);
assert.ok(disabled.every(entry => entry.weight === 0 && entry.disabledReason));

console.log('TELEPORTER REGISTRY CONTRACT PASS');
console.log(JSON.stringify({
    canonicalRegistryEntries: registry.destinations.length,
    enabled: registry.destinations.length - disabled.length,
    disabled: disabled.length,
    roomMappingsChecked: roomsByName.size,
    locationDefinitionsChecked: locationsById.size,
    missingAssets: missingAssets.length
}, null, 2));
