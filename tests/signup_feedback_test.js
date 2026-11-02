// Run the real shared form callback with recording UI/network adapters.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const source = fs.readFileSync(path.join(__dirname, '../assets/general/js/my_functions.js'), 'utf8');
const start = source.indexOf('function submitInlineAjax(form, opts)');
const end = source.indexOf('// show form loader', start);
assert(start >= 0 && end > start);
function run(response, options) {
    const alerts = [], delays = [];
    let resets = 0;
    const location = { href: null };
    const node = { length: 1 };
    for (const method of ['find','stop','empty','append','css','fadeIn','delay','fadeOut','removeClass','addClass','attr']) {
        node[method] = () => node;
    }
    function $(selector, props) { if (props && props.class) alerts.push(props.class); return node; }
    $.ajax = options => options.success(response);
    const context = { $, window: { location }, FormData: class { append() {} },
        appendGlobalCsrfToFormData: data => data, updateGlobalCsrfHash() {},
        clearAuthFieldError() {}, showAuthFieldError() { return false; },
        setTimeout(callback, delay) { delays.push(delay); callback(); }, console };
    vm.runInNewContext(source.slice(start, end), context);
    context.submitInlineAjax({ getAttribute() { return '/fixture'; }, reset() { resets++; } }, options);
    return { alerts, delays, location, resets };
}
let result = run({ status: true, redirect: '/verify-email/fixture' }, { suppressSuccess: true, redirectDelay: 0 });
assert.deepEqual(result.alerts, [], 'Signup must not show a success banner');
assert.equal(result.location.href, '/verify-email/fixture');
assert.deepEqual(result.delays, [0], 'Successful signup must not delay code entry');
result = run({ status: true, msg: 'Saved' }, {});
assert.deepEqual(result.alerts, ['alert alert-success text-center'], 'Other shared-form callers keep their existing success feedback');
let retryToken;
result = run({ status: false, resume_token: 'fixture-token', msg: 'Could not send' }, {
    suppressSuccess: true, onError(response) { retryToken = response.resume_token; }
});
assert.equal(retryToken, 'fixture-token');
assert.equal(result.location.href, null, 'A failed send must stay on the form');
assert.deepEqual(result.alerts, ['alert alert-danger text-center'], 'Errors remain visible');
result = run({ status: true, code_verified: true }, {
    suppressSuccess: true, resetOnSuccess: true, redirect: '/dashboard', onSuccess() { return false; }
});
assert.equal(result.resets, 0, 'Code precheck must preserve hidden proof for the password step');
assert.equal(result.location.href, null, 'Code precheck must not sign in or redirect');
console.log('PASS: silent signup transitions, immediate redirects, failed-send retry callbacks and unchanged feedback for other forms.');
