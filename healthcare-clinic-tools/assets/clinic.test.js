const { test } = require('node:test');
const assert = require('node:assert/strict');
const { hcRecommend, hcTotal } = require('./clinic.js');

test('checkup suggestion validates measurements and follows the simple planning rules', () => {
  assert.equal(hcRecommend(40, 0, 70, 'active', false), null);
  assert.equal(hcRecommend(29, 170, 65, 'active', false).key, 'basic');
  assert.equal(hcRecommend(40, 170, 65, 'active', false).key, 'standard');
  assert.equal(hcRecommend(29, 170, 65, 'active', true).key, 'executive');
});

test('builder adds only selected test prices', () => {
  const prices = { packages: { standard: { price: 2990, includes: ['sugar'] } }, addons: { ecg: { price: 800 }, sugar: { price: 350 } } };
  assert.equal(hcTotal('standard', ['ecg', 'sugar'], prices), 3790);
});
