// Exercise the actual reset handler with local DOM adapters; no camera or user photos.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

const source = fs.readFileSync(path.join(__dirname, '../assets/users/js/booking.js'), 'utf8');
const start = source.indexOf("    $('.reset_img_input').click(function () {");
const end = source.indexOf('\n    autoLoadPageHelpers();', start);
assert(start >= 0 && end > start, 'Reset handler must be present');

function fixture(type, inputId) {
    let handler;
    const state = { value: 'captured-image', src: 'captured-image', label: 'Retake Selfie',
        changed: true, blob: true, retake: true, changeEvents: 0 };
    const input = { type, id: inputId, hidden: true, dispatchEvent() { state.changeEvents++; } };
    const image = {
        attr(key, value) {
            if (value !== undefined) { state.src = value; return this; }
            return key === 'id' ? 'preview' : state.src;
        },
        data() { return 'placeholder.png'; },
        removeClass() { state.changed = false; return this; }
    };
    const field = { 0: input, length: 1,
        val(value) { state.value = value; return this; },
        removeClass() { state.blob = false; return this; }
    };
    const button = {};
    function $(selector) {
        if (selector === button) return { siblings() { return image; } };
        if (selector === '.reset_img_input') return { click(callback) { handler = callback; } };
        if (selector === 'input[holder="preview"]') return field;
        if (selector === '.take-selfie[target-input="' + inputId + '"]') {
            return { removeClass() { state.retake = false; return this; }, html() { state.label = 'Take Selfie'; } };
        }
        throw new Error('Unexpected selector: ' + selector);
    }
    vm.runInNewContext(source.slice(start, end), { $, Event: class {} });
    return { state, click: () => handler.call(button) };
}

for (const inputId of ['image-input', 'image-input2']) {
    for (const type of ['text', 'hidden']) {
        const { state, click } = fixture(type, inputId);
        click();
        assert.equal(state.value, '', 'Captured value must clear');
        assert.equal(state.src, 'placeholder.png', 'Placeholder must replace the captured selfie');
        assert.equal(state.changed, false, 'Remove Selfie must stop being displayed');
        assert.equal(state.blob, false, 'Cleared input must not be submitted as a captured image');
        assert.equal(state.retake, false);
        assert.equal(state.label, 'Take Selfie');
        click();
        assert.equal(state.src, 'placeholder.png', 'Repeated removal must remain safe');
    }
}
const file = fixture('file', 'id_photo');
file.click();
assert.equal(file.state.value, '');
assert.equal(file.state.changeEvents, 1, 'File uploads must retain their change-event reset');
assert.equal(file.state.label, 'Retake Selfie', 'Removing an ID must not change the selfie button');
console.log('PASS: both selfie fields reset their image, value and button; repeated reset and ID-upload behavior are preserved.');
