const fs = require('fs');
const vm = require('vm');
const assert = require('assert');
const buttons = ['all', 'users', 'bookings', 'travellers'].map(type => ({
    attrs: { 'data-activity-filter': type }, classList: { toggle() {} },
    getAttribute(name) { return this.attrs[name]; }, setAttribute(name, value) { this.attrs[name] = value; },
    addEventListener(name, handler) { this.handler = handler; }
}));
const rows = ['users', 'bookings', 'users', 'travellers'].map(type => ({ hidden: false, getAttribute() { return type; } }));
const points = Array.from({ length: 30 }, (_, i) => ({ date: new Date(Date.UTC(2026, 9, 7 + i)).toISOString().slice(0, 10), value: i % 5 }));
const payload = { days: 30, series: [{ name: 'New users', color: '#00b6a0', points }] };
let options;
const empty = { hidden: true };
const periods = [7, 30].map(days => ({
    attrs: { 'data-chart-days': String(days) }, classList: { toggle() {} },
    getAttribute(name) { return this.attrs[name]; }, setAttribute(name, value) { this.attrs[name] = value; },
    addEventListener(name, handler) { this.handler = handler; }
}));
const element = { setAttribute() {}, removeAttribute() {} };
const context = {
    window: {
        innerWidth: 1280,
        getComputedStyle() { return { fontFamily: 'Figtree', fontSize: '14px', color: '#73879c' }; },
        ApexCharts: function (element, config) { options = config; this.render = () => {}; this.updateOptions = update => { Object.assign(options, update); return Promise.resolve(); }; }
    },
    document: {
        readyState: 'complete',
        getElementById(id) { return id === 'admin-activity-data' ? { textContent: JSON.stringify(payload) } : element; },
        querySelectorAll(selector) { return selector === '[data-activity-type]' ? rows : selector === '[data-chart-days]' ? periods : selector === '[data-chart-date]' ? [] : buttons; },
        querySelector() { return empty; }
    }
};
vm.runInNewContext(fs.readFileSync('assets/admin/custom/js/dashboard.js', 'utf8'), context);
assert.strictEqual(options.chart.type, 'line');
assert.strictEqual(options.stroke.curve, 'smooth');
assert.strictEqual(options.chart.fontFamily, 'Figtree');
assert.strictEqual(options.legend.fontSize, '14px');
assert.strictEqual(options.series[0].data.length, 30);
assert(Number.isInteger(options.yaxis.max / options.yaxis.tickAmount), 'Count-axis ticks are whole numbers.');
assert.strictEqual(options.series[0].data[29].x, '2026-11-05');
assert.strictEqual(options.xaxis.labels.formatter('2026-10-07'), '7 Oct');
assert.strictEqual(options.xaxis.labels.formatter('2026-11-05'), '5 Nov');
buttons[1].handler();
assert.deepStrictEqual(rows.map(row => row.hidden), [false, true, false, true]);
assert.strictEqual(buttons[1].attrs['aria-pressed'], 'true');
buttons[0].handler();
assert(rows.every(row => !row.hidden));
periods[0].handler();
assert.strictEqual(options.series[0].data.length, 7, 'Seven-day switch updates the existing chart.');
assert.strictEqual(options.series[0].data[0].x, '2026-10-30');
periods[1].handler();
assert.strictEqual(options.series[0].data.length, 30, 'Thirty-day switch restores the full range.');
console.log('PASS: smooth line chart, inherited typography, endpoint dates and functional activity filters.');
