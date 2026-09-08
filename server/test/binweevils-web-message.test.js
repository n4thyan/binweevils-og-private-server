'use strict';

const test = require('node:test');
const assert = require('node:assert/strict');
const Module = require('node:module');

const originalLoad = Module._load;
Module._load = function(request, parent, isMain) {
    if(request === './Weevil' && parent && parent.filename.endsWith('BinWeevilsWeb.js')) {
        return class StubWeevil {};
    }
    return originalLoad.call(this, request, parent, isMain);
};

const BinWeevilsWeb = require('../BinWeevilsWeb');
Module._load = originalLoad;

test('handleData accepts ws Buffer messages and dispatches friends/get-list', async () => {
    const server = new BinWeevilsWeb('127.0.0.1', 0);
    const sent = [];
    let buddyListCalls = 0;
    const weevil = {
        buddyList: [],
        destroyed: false,
        async getBuddyList() {
            buddyListCalls++;
        },
        async getBuddyRequests() {
            return [];
        },
        socket: {
            send(payload) {
                sent.push(JSON.parse(payload));
            },
            close() {
                throw new Error('valid Buffer request must not close the socket');
            }
        }
    };

    await server.handleData(Buffer.from('friends/get-list{}'), weevil);

    assert.equal(buddyListCalls, 1);
    assert.equal(sent.length, 1);
    assert.equal(sent[0].cn, 'friends.get-list');
    assert.equal(sent[0].responseCode, 1);
});
