const fs = require('fs');
const vm = require('vm');
const assert = require('assert');
const source = fs.readFileSync('assets/admin/custom/js/admin_script.js', 'utf8');
const elements = new Map();
function $(selector) {
    if (!elements.has(selector)) elements.set(selector, { classes: new Set(), attrs: {}, value: '' });
    const element = elements.get(selector);
    return {
        addClass(value) { element.classes.add(value); return this; },
        removeClass(value) { element.classes.delete(value); return this; },
        attr(key, value) { if (value === undefined) return element.attrs[key]; element.attrs[key] = value; return this; },
        val() { return element.value; },
    };
}
$.each = (values, callback) => Object.entries(values).forEach(([key, value]) => callback(key, value));
const messages = [];
const context = { $, window: { AdminModalValidation: { setFieldError: (field, message) => messages.push(message) } } };
vm.createContext(context);
for (const name of ['shippingSetStep', 'shippingShowFieldErrors']) {
    const match = source.match(new RegExp('    function ' + name + '\\([^]*?\\n    }'));
    assert(match, `${name} exists`);
    vm.runInContext(match[0], context);
}
$('#manageShippingModal').attr('data-prefill-only', '1');
context.shippingSetStep(2);
assert(elements.get('#shipping_back_btn').classes.has('d-none'), 'Prefilled Arrival shipping hides Back');
$('#manageShippingModal').attr('data-prefill-only', '0');
context.shippingSetStep(2);
assert(!elements.get('#shipping_back_btn').classes.has('d-none'), 'Search-based shipping retains Back');
context.shippingShowFieldErrors({ pickup_address: 'Do not show this message' });
assert(elements.get('#shipping_pickup_address').classes.has('admin-form-modal__control--invalid'));
assert.strictEqual(elements.get('#shipping_pickup_address').attrs['aria-invalid'], 'true');
assert.strictEqual(messages.length, 0, 'Pickup validation adds a border without explanatory text');
context.shippingShowFieldErrors({ courier: 'Select a courier' });
assert.deepStrictEqual(messages, ['Select a courier'], 'Other field errors remain inline');
console.log('PASS: prefilled/search Back behavior and pickup border-only validation.');
