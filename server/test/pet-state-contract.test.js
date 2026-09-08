'use strict';

const test = require('node:test');
const assert = require('node:assert/strict');
const db = require('../db');
const Weevil = require('../Weevil');

function socketStub() {
    return {
        destroyed: false,
        remoteAddress: '127.0.0.1',
        writes: [],
        write(value) { this.writes.push(value); },
        end() {},
        destroy() { this.destroyed = true; }
    };
}

function makePet(id = 17, overrides = {}) {
    return Object.assign({
        id,
        owner: 'Owner',
        name: 'Bink',
        nameHash: 'hash',
        defObj: { ac1: 1, ac2: 2, bc: 3, ec1: 4, ec2: 5 },
        fitness: 50,
        mentalEnergy: 50,
        locID: 5,
        ps: 0,
        x: 10,
        y: 0,
        z: 20,
        r: 30,
        scale: 0.07,
        inNest: false,
        ridingOwner: false
    }, overrides);
}

test('constructor owns an empty pet state and getPet resolves default and explicit pets', () => {
    const weevil = new Weevil(socketStub());

    assert.deepEqual(weevil.myPet, []);
    assert.equal(weevil.getPet(), false);

    const first = makePet(17);
    const second = makePet(23, { name: 'Bonk' });
    weevil.myPet[17] = first;
    weevil.myPet[23] = second;

    assert.equal(weevil.getPet(), first);
    assert.equal(weevil.getPet(23), second);
    assert.equal(weevil.getPet(999), undefined);
});

test('setRvars validates petDef ownership then relays petDef, petState, and petIDs', (t) => {
    const originalQuery = db.query;
    t.after(() => { db.query = originalQuery; });

    const owner = new Weevil(socketStub());
    owner.loggedIn = true;
    owner.nickname = 'Owner';
    owner.currentRoomId = 321;
    owner.server = { locWithScales: { '-5': 0.14, '5': 0.14 } };

    const observer = new Weevil(socketStub());
    observer.loggedIn = true;
    observer.currentRoomId = 321;
    observer.socketID = 2;
    owner.socketID = 1;
    const weevils = [owner, observer];
    const sockets = [0, 1];

    db.query = (sql, params, callback) => {
        assert.match(sql, /FROM pets WHERE id = \? AND ownerID = \? AND name = \?/);
        assert.deepEqual(params, ['17', 'Owner', 'Bink']);
        callback(null, [{
            id: 17,
            ownerID: 'Owner',
            name: 'Bink',
            nameHash: 'hash',
            ac1: 1,
            ac2: 2,
            bc: 3,
            ec1: 4,
            ec2: 5,
            fitness: 50,
            mentalEnergy: 60,
            rented: 0,
            adoptedDate: new Date()
        }]);
    };

    owner.setRvars("<msg><body action='setRvars' r='321'><vars><var n='petDef17' t='s'><![CDATA[name:Bink,id:17,ac1:1,ac2:2,bc:3,ec1:4,ec2:5]]></var></vars></body></msg>", weevils, sockets);
    assert.equal(owner.getPet(17).owner, 'Owner');
    assert.match(observer.socket.writes.at(-1), /action='rVarsUpdate'/);
    assert.match(observer.socket.writes.at(-1), /n='petDef17'/);

    owner.setRvars("<msg><body action='setRvars' r='321'><vars><var n='petState17' t='s'><![CDATA[locID:5,ps:28,x:11.5,y:0,z:22,r:33]]></var></vars></body></msg>", weevils, sockets);
    assert.equal(owner.getPet(17).ps, 28);
    assert.equal(owner.getPet(17).ridingOwner, true);
    assert.equal(owner.getPet(17).x, 11.5);
    assert.match(observer.socket.writes.at(-1), /n='petState17'/);

    owner.setRvars("<msg><body action='setRvars' r='321'><vars><var n='petIDs' t='s'><![CDATA[17]]></var></vars></body></msg>", weevils, sockets);
    assert.match(observer.socket.writes.at(-1), /n='petIDs'/);
    assert.match(observer.socket.writes.at(-1), /CDATA\[17\]/);
});

test('petState and petIDs wait for an in-flight petDef ownership check', async (t) => {
    const originalQuery = db.query;
    t.after(() => { db.query = originalQuery; });

    const owner = new Weevil(socketStub());
    owner.loggedIn = true;
    owner.nickname = 'Owner';
    owner.currentRoomId = 321;
    owner.socketID = 1;
    owner.server = { locWithScales: { '-5': 0.14 } };

    const observer = new Weevil(socketStub());
    observer.loggedIn = true;
    observer.currentRoomId = 321;
    observer.socketID = 2;
    const room = [owner, observer];
    const sockets = [0, 1];

    let resolvePetDef;
    db.query = (sql, params, callback) => {
        assert.match(sql, /FROM pets WHERE id = \? AND ownerID = \? AND name = \?/);
        resolvePetDef = () => callback(null, [{
            id: 17,
            ownerID: 'Owner',
            name: 'Bink',
            nameHash: 'hash',
            ac1: 1,
            ac2: 2,
            bc: 3,
            ec1: 4,
            ec2: 5,
            fitness: 50,
            mentalEnergy: 60,
            rented: 0,
            adoptedDate: new Date()
        }]);
    };

    owner.setRvars("<msg><body action='setRvars' r='321'><vars><var n='petDef17' t='s'><![CDATA[name:Bink,id:17,ac1:1,ac2:2,bc:3,ec1:4,ec2:5]]></var></vars></body></msg>", room, sockets);
    owner.setRvars("<msg><body action='setRvars' r='321'><vars><var n='petState17' t='s'><![CDATA[locID:-5,ps:0,x:51,y:0,z:160,r:90]]></var></vars></body></msg>", room, sockets);
    owner.setRvars("<msg><body action='setRvars' r='321'><vars><var n='petIDs' t='s'><![CDATA[17]]></var></vars></body></msg>", room, sockets);

    assert.equal(owner.getPet(17), false);
    assert.equal(typeof resolvePetDef, 'function');

    resolvePetDef();
    await new Promise(resolve => setImmediate(resolve));

    assert.equal(owner.getPet(17).x, 51);
    assert.equal(owner.getPet(17).z, 160);
    assert.equal(owner.getPet(17).locID, -5);
    assert.match(observer.socket.writes.at(-1), /n='petIDs'/);
    assert.match(observer.socket.writes.at(-1), /CDATA\[17\]/);
});

test('setUVars reconstructs mounted pet state and relays all received owner variables', () => {
    const owner = new Weevil(socketStub());
    owner.loggedIn = true;
    owner.nickname = 'Owner';
    owner.userID = 41;
    owner.socketID = 1;
    owner.currentRoomId = 321;
    owner.server = { locWithScales: { '5': 0.14 } };
    owner.myPet[17] = makePet(17);

    const observer = new Weevil(socketStub());
    observer.loggedIn = true;
    observer.socketID = 2;
    observer.currentRoomId = 321;

    owner.setUVars("<msg><body action='setUvars' r='321'><vars><var n='x' t='s'><![CDATA[44]]></var><var n='y' t='s'><![CDATA[0]]></var><var n='z' t='s'><![CDATA[55]]></var><var n='r' t='s'><![CDATA[66]]></var><var n='petState17' t='s'><![CDATA[locID:5,ps:28,x:44,y:0,z:55,r:66]]></var></vars></body></msg>", [owner, observer], [0, 1]);

    assert.equal(owner.X, 44);
    assert.equal(owner.getPet(17).ps, 28);
    assert.equal(owner.getPet(17).ridingOwner, true);
    const packet = observer.socket.writes.at(-1);
    assert.match(packet, /action='uVarsUpdate'/);
    assert.match(packet, /n='petState17'/);
    assert.match(packet, /ps:28/);
});

test('changeRoom keeps a mounted pet synchronized and passes it to room spawn', () => {
    const owner = new Weevil(socketStub());
    owner.loggedIn = true;
    owner.nickname = 'Owner';
    owner.socketID = 1;
    owner.userID = 41;
    owner.currentRoomId = 0;
    owner.currentLocId = 0;
    owner.server = {
        roomWithIds: { Main: 321, nest_Owner: 500 },
        locWithScales: { '5': 0.14, '-5': 0.14 }
    };
    owner.returnJoinOK = () => '<joinOK />';
    owner.myPet[17] = makePet(17, { locID: -5, ps: 28, ridingOwner: true });

    const observer = new Weevil(socketStub());
    observer.loggedIn = true;
    observer.socketID = 2;
    observer.currentRoomId = 321;
    let spawnArgs;
    observer.spawnWeevil = (...args) => { spawnArgs = args; };

    owner.changeRoom('Main', '100', '0', '200', '45', '5', [owner, observer], [0, 1]);

    const pet = owner.getPet(17);
    assert.equal(pet.locID, 5);
    assert.equal(pet.x, 100);
    assert.equal(pet.z, 200);
    assert.equal(pet.ps, 28);
    assert.equal(pet.scale, 0.07);
    assert.equal(spawnArgs[13], pet);
});

function prepareRoomWeevil(weevil, values) {
    Object.assign(weevil, {
        loggedIn: true,
        nickname: values.nickname,
        socketID: values.socketID,
        userID: values.userID,
        isModerator: '0',
        def: '1,2,3',
        X: 1,
        Y: 0,
        Z: 2,
        R: 3,
        idx: values.userID,
        curHat: '|null:-140,-140,-140',
        curExpression: '0',
        ps: '0',
        currentRoomId: values.currentRoomId,
        currentLocId: values.currentLocId
    });
}

test('returnJoinOK reconstructs occupant pets and a nest owner pet', () => {
    const server = {
        roomWithIds: { nest_Owner: 500 },
        flumsMushrooms: [],
        figgsTrays: {},
        figgsPlates: {},
        grabTopTenPoolLeaderBoardUsers: () => [],
        grabTopTenTrackLeaderBoardUsers: () => []
    };
    const viewer = new Weevil(socketStub());
    prepareRoomWeevil(viewer, { nickname: 'Viewer', socketID: 1, userID: 41, currentRoomId: 321, currentLocId: 5 });
    viewer.server = server;

    const owner = new Weevil(socketStub());
    prepareRoomWeevil(owner, { nickname: 'Owner', socketID: 2, userID: 42, currentRoomId: 321, currentLocId: 5 });
    owner.server = server;
    owner.myPet[17] = makePet(17, { locID: 5 });

    const normalJoin = viewer.returnJoinOK(321, [viewer, owner], [0, 1]);
    assert.match(normalJoin, /<u i='42'/);
    assert.match(normalJoin, /n='petDef'/);
    assert.match(normalJoin, /n='petState'/);
    assert.match(normalJoin, /n='petIDs'/);

    viewer.currentRoomId = 500;
    owner.myPet[17].locID = -5;
    const nestJoin = viewer.returnJoinOK(500, [viewer, owner], [0, 1]);
    assert.match(nestJoin, /n='petDef17'/);
    assert.match(nestJoin, /n='petState17'/);
    assert.match(nestJoin, /CDATA\[17\]/);
});

test('spawnWeevil includes matching pet state for existing room occupants', () => {
    const recipient = new Weevil(socketStub());
    recipient.loggedIn = true;
    const pet = makePet(17, { locID: 5 });

    recipient.spawnWeevil(321, 5, 42, 'Owner', '0', '1,2,3', 1, 0, 2, 3, 42, '|null:-140,-140,-140', '0', pet);

    const packet = recipient.socket.writes.at(-1);
    assert.match(packet, /action='uER'/);
    assert.match(packet, /n='petDef'/);
    assert.match(packet, /n='petState'/);
    assert.match(packet, /n='petIDs'/);
});

test('pet 6#1 through 6#7 methods update owned state and relay canonical packets', () => {
    const owner = new Weevil(socketStub());
    prepareRoomWeevil(owner, { nickname: 'Owner', socketID: 1, userID: 41, currentRoomId: 321, currentLocId: 5 });
    owner.server = { locWithScales: { '5': 0.14, '-5': 0.14 } };
    owner.myPet[17] = makePet(17);

    const observer = new Weevil(socketStub());
    prepareRoomWeevil(observer, { nickname: 'Viewer', socketID: 2, userID: 42, currentRoomId: 321, currentLocId: 5 });
    const room = [owner, observer];
    const sockets = [0, 1];
    const lastRelay = () => observer.socket.writes.at(-1);

    owner.sendPetJoinNestLoc('321', '17', '-5', '2', '11.5', '0', '22', '33', '1', room, sockets);
    assert.equal(owner.getPet(17).locID, -5);
    assert.match(lastRelay(), /%xt%6#1%-1%17%-5%2%11\.5%0%22%33%1%/);

    owner.sendPetNestDoor('321', '17', '2', '1', room, sockets);
    assert.match(lastRelay(), /%xt%6#2%-1%17%2%1%/);

    owner.sendPetExpression('321', '17', '4', '1', room, sockets);
    assert.match(lastRelay(), /%xt%6#3%-1%17%4%1%/);

    owner.sendPetAction('321', '17', '28', '-1', '1', room, sockets);
    assert.equal(owner.getPet(17).ridingOwner, true);
    assert.match(lastRelay(), /%xt%6#4%-1%17%28%-1%1%-1%/);

    owner.sendPetGotBall('321', '17', room, sockets);
    assert.match(lastRelay(), /%xt%6#5%-1%17%/);

    owner.sendPetHome('321', '17', 'locID:-5,ps:0,x:50,y:0,z:160,r:0', room, sockets);
    assert.equal(owner.getPet(17).ps, 0);
    assert.match(lastRelay(), /%xt%6#6%-1%17%locID:-5,ps:0,x:50,y:0,z:160,r:0%/);

    owner.sendPetCommand('321', 'Bink', 'hash', '7', room, sockets);
    assert.match(lastRelay(), /%xt%6#7%Owner%41%Bink%7%/);
});
