'use strict';

const assert = require('assert');
const fs = require('fs');
const path = require('path');
const { loadRegistry, selectDestination } = require('../teleporter');

function seededRandom(seed) {
    let state = seed >>> 0;
    return function random() {
        state = (1664525 * state + 1013904223) >>> 0;
        return state / 0x100000000;
    };
}

const registry = loadRegistry();
const rolls = 100000;
const rng = seededRandom(0xB17BEE);
const categoryCounts = Object.fromEntries(Object.keys(registry.categoryWeights).map(category => [category, 0]));
const perRoomCounts = {};
let disabledSelections = 0;
let currentRoomSelections = 0;
let immediateRepeats = 0;
let recentRepeats = 0;
let currentLocId = 190;
let recentLocIds = [];

for (let i = 0; i < rolls; i++) {
    const destination = selectDestination(registry, { currentLocId, recentLocIds }, rng);
    if (!destination.enabled) disabledSelections++;
    if (destination.locId === currentLocId) currentRoomSelections++;
    if (recentLocIds[0] === destination.locId) immediateRepeats++;
    if (recentLocIds.includes(destination.locId)) recentRepeats++;

    categoryCounts[destination.category]++;
    perRoomCounts[destination.locId] = (perRoomCounts[destination.locId] || 0) + 1;
    recentLocIds.unshift(destination.locId);
    recentLocIds = recentLocIds.slice(0, registry.recentHistorySize);
    currentLocId = destination.locId;
}

assert.strictEqual(disabledSelections, 0, 'disabled destination selected');
assert.strictEqual(currentRoomSelections, 0, 'current room selected');
assert.strictEqual(immediateRepeats, 0, 'immediate repeat selected');
assert.strictEqual(recentRepeats, 0, 'recent-history destination selected despite available alternatives');

const categoryPercentages = {};
for (const [category, configured] of Object.entries(registry.categoryWeights)) {
    const observed = categoryCounts[category] * 100 / rolls;
    categoryPercentages[category] = Number(observed.toFixed(3));
    assert.ok(Math.abs(observed - configured) < 0.8, category + ' distribution outside tolerance');
}

const enabled = registry.destinations.filter(destination => destination.enabled);
const disabled = registry.destinations.filter(destination => !destination.enabled);
for (const destination of enabled) {
    assert.ok(perRoomCounts[destination.locId] > 0, 'enabled room never selected: ' + destination.locId);
}

const result = {
    rolls,
    configuredCategoryPercentages: registry.categoryWeights,
    observedCategoryPercentages: categoryPercentages,
    enabledDestinations: enabled.length,
    disabledDestinations: disabled.length,
    disabledSelections,
    currentRoomSelections,
    immediateRepeats,
    recentRepeats,
    recentHistorySize: registry.recentHistorySize,
    perRoomCounts
};

const resultPath = path.join(__dirname, 'teleporter-simulation-result.json');
fs.writeFileSync(resultPath, JSON.stringify(result, null, 2) + '\n');
console.log('TELEPORTER SIMULATION PASS');
console.log(JSON.stringify({ ...result, perRoomCounts: '[written to ' + resultPath + ']' }, null, 2));
