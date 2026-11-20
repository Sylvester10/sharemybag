// Production view + destination handlers with an intercepted table adapter; no live data.
const fs = require('fs');
const path = require('path');
const assert = require('assert');
const {execFileSync} = require('child_process');
const root = path.resolve(__dirname, '..');
const playwright = require(process.env.PLAYWRIGHT_MODULE_PATH || '/Users/mac/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const source = fs.readFileSync(path.join(root, 'assets/admin/custom/js/admin_script.js'), 'utf8').replace(/\r\n/g, '\n');
const start = source.indexOf("    if ($('#arrivals_travellers_table').length)");
assert(start !== -1);
const handlers = source.slice(start, source.indexOf('    /////////////////////////////////////////////////////////', start));
const html = execFileSync('php', [path.join(root, 'tests/arrivals_destination_test.php'), '--render'], {encoding: 'utf8'});
(async () => {
  const browser = await playwright.chromium.launch({headless: true, executablePath: process.env.CHROME_EXECUTABLE || '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome'});
  try {
    const page = await browser.newPage({viewport: {width: 1100, height: 850}});
    const errors = []; page.on('pageerror', error => errors.push(error.message));
    await page.route('**/*', route => route.request().url().startsWith('http://smb-arrivals-fixture.test/')
      ? route.fulfill({contentType: 'text/html', body: '<!doctype html><meta name="viewport" content="width=device-width, initial-scale=1"><main>' + html + '</main>'}) : route.abort());
    async function install() {
      await page.addStyleTag({path: path.join(root, 'assets/admin/vendors/bootstrap/dist/css/bootstrap.min.css')});
      await page.addStyleTag({path: path.join(root, 'assets/admin/custom/css/custom.css')});
      await page.addScriptTag({path: path.join(root, 'assets/admin/vendors/jquery/dist/jquery.min.js')});
      await page.addScriptTag({content: `var base_url='http://smb-arrivals-fixture.test/'; window.requests=[];
        function initializeDataTable(selector,url,label,filter){
          function capture(){var request={};filter(request);window.requests.push(request);}
          return {order:function(){return this;},draw:function(){capture();return this;},ajax:{reload:capture}};
        }\n` + handlers});
    }
    await page.goto('http://smb-arrivals-fixture.test/'); await install();
    const initialUrl = page.url();
    const nigeria = page.getByRole('button', {name: 'Nigeria Arrivals', exact: true});
    const uk = page.getByRole('button', {name: 'UK Arrivals', exact: true});
    const all = page.getByRole('button', {name: 'All Arrivals', exact: true});
    assert.strictEqual(await nigeria.getAttribute('aria-pressed'), 'true');
    assert.strictEqual(await page.evaluate(() => requests[0].destination), 'Nigeria');
    await uk.click();
    assert.strictEqual(await uk.getAttribute('aria-pressed'), 'true');
    assert.strictEqual(await nigeria.getAttribute('aria-pressed'), 'false');
    assert.strictEqual(await page.evaluate(() => requests[1].destination), 'United Kingdom');
    await uk.click();
    assert.strictEqual(await page.evaluate(() => requests.length), 2, 'Repeated active clicks do not duplicate requests');
    await page.locator('#arrivals_lifecycle_filter').selectOption('Cleared');
    assert.deepStrictEqual(await page.evaluate(() => requests[2]), {destination: 'United Kingdom', lifecycle: 'Cleared'});
    await all.click();
    assert.deepStrictEqual(await page.evaluate(() => requests[3]), {destination: '', lifecycle: 'Cleared'});
    await uk.focus(); await page.keyboard.press('Space');
    assert.strictEqual(await uk.getAttribute('aria-pressed'), 'true', 'Native buttons support keyboard switching');
    assert.strictEqual(page.url(), initialUrl, 'Switching does not navigate or change the URL');
    await page.reload(); await install();
    assert.strictEqual(await uk.getAttribute('aria-pressed'), 'true', 'Selected view survives success reloads and profile navigation');
    assert.strictEqual(await page.evaluate(() => requests[0].destination), 'United Kingdom');
    await page.setViewportSize({width: 390, height: 844});
    for (const button of [nigeria, uk, all]) {
      const bounds = await button.boundingBox();
      assert(bounds && bounds.width > 0 && bounds.x >= 0 && bounds.x + bounds.width <= 391, 'Destination controls fit the mobile viewport');
    }
    await page.evaluate(() => sessionStorage.setItem('smb_arrivals_destination', 'invalid-country'));
    await page.reload(); await install();
    assert.strictEqual(await nigeria.getAttribute('aria-pressed'), 'true', 'Invalid saved destinations fall back to Nigeria');
    await page.reload();
    await page.evaluate(() => Object.defineProperty(window, 'sessionStorage', {configurable: true, get() {throw new DOMException('Blocked', 'SecurityError');}}));
    await install();
    await uk.click();
    assert.strictEqual(await page.evaluate(() => requests.at(-1).destination), 'United Kingdom', 'Blocked storage does not prevent filtering');
    assert.deepStrictEqual(errors, []);
    console.log('PASS: Nigeria/UK/All switching, lifecycle preservation, keyboard/mobile controls, no navigation, restored selection and unavailable storage.');
  } finally {await browser.close();}
})().catch(error => {console.error(error); process.exit(1);});
