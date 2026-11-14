// Actual modal/handlers with intercepted responses; no application records or emails.
const fs = require('fs');
const path = require('path');
const assert = require('assert');
const {execFileSync} = require('child_process');
const root = path.resolve(__dirname, '..');
const playwright = require(process.env.PLAYWRIGHT_MODULE_PATH || '/Users/mac/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const source = fs.readFileSync(path.join(root, 'assets/admin/custom/js/admin_script.js'), 'utf8').replace(/\r\n/g, '\n');
function extract(name) {
  const match = source.match(new RegExp('    function ' + name + '\\([^]*?\\n    }'));
  assert(match, name); return match[0];
}
(async () => {
  const modal = execFileSync('php', ['-r',
    "function html_escape($v){return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');} $y=(object)['id'=>42,'additional_info'=>'']; include $argv[1];",
    path.join(root, 'application/views/admin/travellers/modal/collection_address.php')], {encoding: 'utf8'});
  const browser = await playwright.chromium.launch({headless: true, executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome'});
  try {
    const page = await browser.newPage({viewport: {width: 390, height: 844}});
    const errors = []; page.on('pageerror', error => errors.push(error.message));
    await page.route('http://smb-address-fixture.test/**', route => route.fulfill({contentType: 'text/html', body:
      '<input id="csrf_hash" value="fixture-csrf"><button data-toggle="modal" data-target="#travellerCollectionAddressModal">Edit Collection Address</button>' + modal}));
    await page.goto('http://smb-address-fixture.test/');
    await page.addStyleTag({path: path.join(root, 'assets/admin/vendors/bootstrap/dist/css/bootstrap.min.css')});
    await page.addScriptTag({path: path.join(root, 'assets/admin/vendors/jquery/dist/jquery.min.js')});
    await page.addScriptTag({path: path.join(root, 'assets/admin/vendors/bootstrap/dist/js/bootstrap.min.js')});
    await page.addScriptTag({content: `var base_url='http://smb-address-fixture.test/'; var csrfHash='fixture-csrf'; $.support.transition=false;
      window.requests=[]; $.ajax=function(options){window.requests.push(options);return {};};
      window.reply=function(index,result){var request=window.requests[index];request.success(result);if(request.complete)request.complete();};`});
    const validation = source.slice(source.indexOf('    var adminModalFieldIndex'), source.indexOf("    $('#filePreviewModal').on('hidden.bs.modal'"));
    const address = source.slice(source.indexOf('    // ARRIVAL COLLECTION ADDRESS'), source.indexOf('    // TRAVELLER PAYOUT CONFIRMATION'));
    await page.addScriptTag({content: [extract('getCsrfHash'), extract('updateCsrfHash'), extract('parseJsonResponse'), validation, address].join('\n')});
    await page.getByRole('button', {name: 'Edit Collection Address', exact: true}).click();
    const field = page.locator('#traveller_collection_address');
    const submit = page.locator('#traveller_collection_address_submit');
    await submit.click();
    assert.strictEqual(await page.evaluate(() => requests.length), 0, 'Empty form never sends or reloads');
    assert.strictEqual(await page.locator('#traveller_collection_address_error').count(), 1);
    assert(await page.locator('#traveller_collection_address').getAttribute('aria-invalid'));
    await field.fill('Collection address, Benin');
    assert.strictEqual(await field.getAttribute('aria-invalid'), null, 'Typing clears inline validation');
    await submit.click();
    assert(await submit.isDisabled(), 'Duplicate saves are blocked');
    const request = await page.evaluate(() => ({url: requests[0].url, data: requests[0].data}));
    assert(request.url.endsWith('/shipping/update_collection_address_ajax/42'));
    assert(request.data.some(item => item.name === 'q2r_secure' && item.value === 'fixture-csrf'));
    assert(request.data.some(item => item.name === 'address_token' && item.value.length === 64));
    await page.evaluate(() => reply(0, {status: false, csrf_hash: 'next-csrf', field_errors: {collection_address: 'Enter a valid collection address.'}}));
    assert(await submit.isEnabled());
    assert.strictEqual(await field.getAttribute('aria-invalid'), 'true');
    await field.fill('Corrected address, Benin');
    assert.strictEqual(await field.getAttribute('aria-invalid'), null);
    await submit.click();
    await page.evaluate(() => {requests[1].error({responseText: JSON.stringify({status: false, msg: 'Please retry.'})}); requests[1].complete();});
    assert((await page.locator('#traveller_collection_address_form_error').textContent()).includes('Please retry.'));
    await submit.click();
    const navigation = page.waitForEvent('framenavigated');
    await page.evaluate(() => reply(2, {status: true, msg: 'Collection address updated successfully.'}));
    await navigation;
    assert.strictEqual(await page.evaluate(() => sessionStorage.getItem('parcel_success_msg')), 'Collection address updated successfully.');
    assert.deepStrictEqual(errors, []);
    console.log('PASS: collection-address modal empty validation, clear-on-input, CSRF, stale token, busy/retry errors and success reload.');
  } finally {await browser.close();}
})().catch(error => {console.error(error); process.exit(1);});
