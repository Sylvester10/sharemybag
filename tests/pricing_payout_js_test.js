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

const routeRates = {
  ng_uk: { normal: 5, special: 5, premiumSmall: 10, premiumLaptop: 15 },
  uk_ng: { normal: 4.5, special: 4.5, premiumSmall: 10, premiumLaptop: 15 },
  ng_ca: { normal: 10, special: 10, premiumSmall: 18.47, premiumLaptop: 27.7 },
  ca_ng: { normal: 10, special: 10, premiumSmall: 18.47, premiumLaptop: 27.7 },
};

const categoryRateKeys = {
  Normal: 'normal',
  'Duty Free': 'normal',
  'Fish/Medicine': 'special',
  'Fish/Meat': 'special',
  Medication: 'special',
  'Documents/Electronics': 'premiumSmall',
  'Documents/Small Electronics': 'premiumSmall',
  Gold: 'premiumSmall',
  Laptop: 'premiumLaptop',
};

Object.entries(routeRates).forEach(([routeKey, routePayouts]) => {
  Object.entries(categoryRateKeys).forEach(([category, rateKey]) => {
    assert.strictEqual(
      calculateConfiguredTravellerCommission([{ category, size: 2 }], routePayouts),
      Number((routePayouts[rateKey] * 2).toFixed(2)),
      `${routeKey} ${category} must use only the matching configured payout.`
    );
  });
});

console.log('PASS: all routes and categories use configured category payouts.');
