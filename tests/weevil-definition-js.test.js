'use strict';

const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

const source = fs.readFileSync(path.resolve(__dirname, '../weevil-creator/src/runtime/WeevilDef.js'), 'utf8');
const moduleUrl = 'data:text/javascript;base64,' + Buffer.from(source).toString('base64');

async function rendererModule() {
    return import(moduleUrl);
}

test('website renderer preserves all five exact extended RGB colours', async () => {
    const {getDefObj} = await rendererModule();
    const parsed = getDefObj('401135129001323200~12ABEF7F3ACC000000FFFFFFFF0000');
    assert.equal(parsed.hc, 0x12ABEF);
    assert.equal(parsed.bc, 0x7F3ACC);
    assert.equal(parsed.ec, 0x000000);
    assert.equal(parsed.ac, 0xFFFFFF);
    assert.equal(parsed.lc, 0xFF0000);
});

test('legacy definitions still resolve historical palette colours', async () => {
    const {getDefObj, clrs1, clrs2} = await rendererModule();
    const parsed = getDefObj('401135129001323200');
    assert.equal(parsed.hc, clrs1[1]);
    assert.equal(parsed.bc, clrs1[35]);
    assert.equal(parsed.ec, clrs2[29]);
    assert.equal(parsed.ac, clrs1[32]);
    assert.equal(parsed.lc, clrs1[32]);
});
