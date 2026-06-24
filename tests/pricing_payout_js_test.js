'use strict';

const assert = require('assert');
const {
  calculateConfiguredTravellerCommission,
} = require('../assets/users/js/pricing_payout');

const rates = {
  normal: 5,
  special: 7,
  premiumSmall: 11,
  premiumLaptop: 17,
};

const items = [
  { category: 'Normal', size: 2 },
  { category: 'Medication', size: 1 },
  { category: 'Documents/Small Electronics', size: 3 },
  { category: 'Laptop', size: 1 },
  { category: 'Duty Free', size: 1 },
];

assert.strictEqual(
  calculateConfiguredTravellerCommission(items, rates),
  72,
  'Each item must receive only its configured category payout.'
);

const categoryPayouts = {
  Normal: 5,
  'Duty Free': 5,
  'Fish/Medicine': 7,
  'Fish/Meat': 7,
  Medication: 7,
  'Documents/Electronics': 11,
  'Documents/Small Electronics': 11,
  Gold: 11,
  Laptop: 17,
};

Object.entries(categoryPayouts).forEach(([category, expectedPayout]) => {
  assert.strictEqual(
    calculateConfiguredTravellerCommission([{ category, size: 1 }], rates),
    expectedPayout,
    `${category} must receive exactly one category payout.`
  );
});

console.log('PASS: booking summary uses configured category payouts.');
