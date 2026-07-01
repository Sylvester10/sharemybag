(function (root, factory) {
  const api = factory();

  if (typeof module === 'object' && module.exports) {
    module.exports = api;
  }

  if (root) {
    root.ShareMyBagPricing = api;
  }
})(typeof window !== 'undefined' ? window : globalThis, function () {
  function payoutRateForCategory(category, rates) {
    if (category === 'Laptop') {
      return rates.premiumLaptop;
    }

    if (
      category === 'Documents/Small Electronics' ||
      category === 'Documents/Electronics' ||
      category === 'Gold'
    ) {
      return rates.premiumSmall;
    }

    if (
      category === 'Fish/Medicine' ||
      category === 'Fish/Meat' ||
      category === 'Medication'
    ) {
      return rates.special;
    }

    return rates.normal;
  }

  function calculateConfiguredTravellerCommission(items, rates) {
    const total = items.reduce(function (commission, item) {
      const size = Number(item.size) || 0;
      if (size <= 0) {
        return commission;
      }

      return commission + payoutRateForCategory(item.category, rates) * size;
    }, 0);

    return Number(total.toFixed(2));
  }

  return {
    calculateConfiguredTravellerCommission,
  };
});
