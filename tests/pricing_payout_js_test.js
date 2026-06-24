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

console.log('PASS: booking summary uses configured category payouts.');
