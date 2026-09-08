'use strict';

const assert = require('assert');
const xml2js = require('xml2js');
const BinWeevils = require('../BinWeevils');
const { loadRegistry } = require('../teleporter');

function makeWeevil(overrides = {}) {
    const sent = [];
    return Object.assign({
        loggedIn: true,
        nickname: 'TeleporterTester',
        currentRoomName: 'nest_TeleporterTester',
        currentLocId: 190,
        teleporterRecentLocIds: [191, 102, 103],
        actionAllowed: () => true,
        send: packet => sent.push(packet),
        sent
    }, overrides);
}

const server = {
    teleporterRegistry: loadRegistry(),
    handleTeleporter: BinWeevils.prototype.handleTeleporter
};

const weevil = makeWeevil();
server.handleTeleporter(weevil);
assert.strictEqual(weevil.sent.length, 1, 'own-Nest request should receive one response');
assert.ok(!weevil.sent[0].includes('<![CDATA[<![CDATA['), 'response must not contain nested CDATA');

const destinationMatch = weevil.sent[0].match(/<var n='locID' t='n'>(\d+)<\/var>/);
assert.ok(destinationMatch, 'response should contain numeric server-selected locID');
const destinationId = Number(destinationMatch[1]);
const registryEntry = server.teleporterRegistry.destinations.find(entry => entry.locId === destinationId);
assert.ok(registryEntry && registryEntry.enabled, 'response must be an enabled registry destination');
assert.notStrictEqual(destinationId, 190, 'response must not select current location');
assert.ok(![191, 102, 103].includes(destinationId), 'response must suppress recent destinations');
assert.strictEqual(weevil.teleporterRecentLocIds[0], destinationId, 'history should record latest destination');
assert.ok(weevil.teleporterRecentLocIds.length <= server.teleporterRegistry.recentHistorySize);

xml2js.parseString(weevil.sent[0], error => {
    assert.ifError(error);
});

const publicRoomWeevil = makeWeevil({ currentRoomName: 'FlumsFountain' });
server.handleTeleporter(publicRoomWeevil);
assert.strictEqual(publicRoomWeevil.sent.length, 0, 'forged request outside own Nest must be ignored');

const loggedOutWeevil = makeWeevil({ loggedIn: false });
server.handleTeleporter(loggedOutWeevil);
assert.strictEqual(loggedOutWeevil.sent.length, 0, 'logged-out request must be ignored');

console.log('TELEPORTER HANDLER PASS');
