'use strict';

const test = require('node:test');
const assert = require('node:assert/strict');
const Module = require('node:module');

function loadWeevilWithDb(fakeDb) {
    const originalLoad = Module._load;
    try {
        Module._load = function(request, parent, isMain) {
            if(request === './db' && parent && parent.filename.endsWith('Weevil.js')) return fakeDb;
            return originalLoad.call(this, request, parent, isMain);
        };
        delete require.cache[require.resolve('../Weevil')];
        return require('../Weevil');
    } finally {
        Module._load = originalLoad;
    }
}

test('refreshDefinition loads the persisted exact extended definition', async () => {
    const expected = '401135129001323200~12ABEF7F3ACC000000FFFFFFFF0000';
    const fakeDb = {
        query(sql, params, callback) {
            assert.match(sql, /SELECT def FROM users WHERE username = \?/);
            assert.deepEqual(params, ['admintest']);
            callback(null, [{def: expected}]);
        }
    };
    const Weevil = loadWeevilWithDb(fakeDb);
    const weevil = new Weevil();
    weevil.loggedIn = true;
    weevil.nickname = 'admintest';
    weevil.def = '401135129001323200';

    await new Promise((resolve) => weevil.refreshDefinition(resolve));
    assert.equal(weevil.def, expected);
});

test('refreshDefinition preserves the current definition on database failure', async () => {
    const fakeDb = {query(sql, params, callback) { callback(new Error('offline')); }};
    const Weevil = loadWeevilWithDb(fakeDb);
    const weevil = new Weevil();
    weevil.loggedIn = true;
    weevil.nickname = 'admintest';
    weevil.def = '401135129001323200';

    await new Promise((resolve) => weevil.refreshDefinition(resolve));
    assert.equal(weevil.def, '401135129001323200');
});

test('refreshDefinition rejects a structurally unsafe database value', async () => {
    const fakeDb = {query(sql, params, callback) { callback(null, [{def: '401135129001323200]]></var><script>'}]); }};
    const Weevil = loadWeevilWithDb(fakeDb);
    const weevil = new Weevil();
    weevil.loggedIn = true;
    weevil.nickname = 'admintest';
    weevil.def = '401135129001323200';

    await new Promise((resolve) => weevil.refreshDefinition(resolve));
    assert.equal(weevil.def, '401135129001323200');
});

test('broadcastDefinition sends the exact persisted definition to self and room peers', () => {
    const fakeDb = {query() { throw new Error('not used'); }};
    const Weevil = loadWeevilWithDb(fakeDb);
    const writes = [];
    const self = new Weevil({destroyed:false, write(value) { writes.push(['self', value]); }});
    const peer = new Weevil({destroyed:false, write(value) { writes.push(['peer', value]); }});
    self.loggedIn = true;
    self.currentRoomId = 321;
    self.userID = 72;
    self.socketID = 1;
    self.def = '401135129001323200~12ABEF7F3ACC000000FFFFFFFF0000';
    peer.loggedIn = true;
    peer.currentRoomId = 321;
    peer.socketID = 2;

    self.broadcastDefinition([self, peer], [0, 1]);
    assert.equal(writes.length, 2);
    for(const [, packet] of writes) {
        assert.match(packet, /action='uVarsUpdate'/);
        assert.match(packet, /n='weevilDef'/);
        assert.match(packet, /401135129001323200~12ABEF7F3ACC000000FFFFFFFF0000/);
    }
});
