// Actual modal markup, jQuery/Bootstrap, shared validation and payout handlers in headless Chrome.
// Requests are intercepted fixtures: no account, bank transfer, email or database is involved.
const fs = require('fs');
const path = require('path');
const assert = require('assert');
const root = path.resolve(__dirname, '..');
const playwright = require(process.env.PLAYWRIGHT_MODULE_PATH || '/Users/mac/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const source = fs.readFileSync(path.join(root,'assets/admin/custom/js/admin_script.js'),'utf8').replace(/\r\n/g,'\n');
function extract(name) {
  const match=source.match(new RegExp('    function '+name+'\\([^]*?\\n    }'));
  assert(match,name); return match[0];
}
(async()=>{
  const browser=await playwright.chromium.launch({headless:true, executablePath:process.env.CHROME_EXECUTABLE || '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome'});
  try {
    const page=await browser.newPage({viewport:{width:1100,height:850}});
    const errors=[]; page.on('pageerror',error=>errors.push(error.message));
    const html='<input id="csrf_hash" value="fixture-csrf"><button class="open-traveller-payout" data-traveller-id="42">Payout</button>'+fs.readFileSync(path.join(root,'application/views/admin/travellers/modal/traveller_payout.php'),'utf8');
    await page.route('http://smb-payout-fixture.test/**',route=>route.fulfill({status:200,contentType:'text/html',body:html}));
    await page.goto('http://smb-payout-fixture.test/');
    await page.addStyleTag({content:fs.readFileSync(path.join(root,'assets/admin/vendors/bootstrap/dist/css/bootstrap.min.css'),'utf8')});
    await page.addScriptTag({path:path.join(root,'assets/admin/vendors/jquery/dist/jquery.min.js')});
    await page.addScriptTag({path:path.join(root,'assets/admin/vendors/bootstrap/dist/js/bootstrap.min.js')});
    await page.addScriptTag({content:`var base_url='http://smb-payout-fixture.test/'; var csrfHash='fixture-csrf'; $.support.transition=false;
      window.payoutRequests=[];
      $.ajax=function(options){window.payoutRequests.push(options); return {abort:function(){if(options.error)options.error({responseText:''},'abort'); if(options.complete)options.complete();}};};
      window.replyPayout=function(index,result){var request=window.payoutRequests[index]; request.success(result); if(request.complete)request.complete();};`});
    const validation=source.slice(source.indexOf('    var adminModalFieldIndex'),source.indexOf("    $('#filePreviewModal').on('hidden.bs.modal'"));
    const payout=source.slice(source.indexOf('    // TRAVELLER PAYOUT CONFIRMATION'),source.indexOf('    // SHIPPING RECORDS'));
    await page.addScriptTag({content:[extract('getCsrfHash'),extract('updateCsrfHash'),extract('parseJsonResponse'),validation,payout].join('\n')});
    const context={traveller_id:42,traveller_name:'Fixture Traveller',eligible:true,eligibility_message:'',paid:false,
      currencies:{GBP:{amount:'12.34'},CAD:{amount:'5.67'}},history:[],request_key:'a'.repeat(32),snapshot_token:'b'.repeat(64)};
    await page.locator('.open-traveller-payout').click();
    assert(await page.locator('#traveller_payout_submit').isDisabled());
    await page.evaluate(context=>window.replyPayout(0,{status:true,csrf_hash:'new-csrf',context}),context);
    assert((await page.locator('#traveller_payout_amounts').textContent()).includes('GBP 12.34'));
    assert((await page.locator('#traveller_payout_amounts').textContent()).includes('CAD 5.67'));
    assert(await page.locator('#traveller_payout_submit').isDisabled(),'Confirmation is required');
    await page.locator('#traveller_payout_confirm_paid').check();
    assert(await page.locator('#traveller_payout_submit').isEnabled());
    await page.locator('#traveller_payout_submit').click();
    assert(await page.locator('#traveller_payout_submit').isDisabled(),'Duplicate clicks blocked while saving');
    const request=await page.evaluate(()=>window.payoutRequests[1].data);
    assert.strictEqual(request.confirm_paid,'1'); assert.strictEqual(request.q2r_secure,'new-csrf');
    assert(!('amount' in request),'No client payment amount is submitted');
    await page.evaluate(()=>window.replyPayout(1,{status:false,msg:'Confirm payment.',csrf_hash:'retry-csrf',field_errors:{confirm_paid:'Confirmation required.'}}));
    assert.strictEqual(await page.locator('#traveller_payout_confirm_paid_error').textContent(),'Confirmation required.');
    await page.locator('#traveller_payout_confirm_paid').uncheck();
    assert.strictEqual(await page.locator('#traveller_payout_confirm_paid_error').count(),0,'Editing clears inline error');
    await page.locator('#traveller_payout_confirm_paid').check();
    await page.locator('#traveller_payout_submit').click();
    const retry=await page.evaluate(()=>window.payoutRequests[2].data);
    assert.strictEqual(retry.request_key,request.request_key,'Retry retains its identity');
    assert.strictEqual(retry.q2r_secure,'retry-csrf');
    await page.evaluate(()=>window.replyPayout(2,{status:false,msg:'Fixture retry completed.'}));
    await page.locator('#travellerPayoutModal [data-dismiss="modal"]').click();
    await page.locator('.open-traveller-payout').click();
    await page.evaluate(context=>window.replyPayout(3,{status:true,context:{...context,eligible:false,eligibility_message:'Arrange shipping for every active booking.'}}),context);
    assert(await page.locator('#traveller_payout_submit').isDisabled());
    assert(await page.locator('#traveller_payout_confirm_paid').isDisabled());
    assert((await page.locator('#traveller_payout_eligibility').textContent()).includes('Arrange shipping'));
    await page.locator('#travellerPayoutModal [data-dismiss="modal"]').click();
    await page.locator('.open-traveller-payout').click();
    await page.evaluate(context=>window.replyPayout(4,{status:true,context}),context);
    await page.locator('#traveller_payout_confirm_paid').check();
    await page.locator('#traveller_payout_submit').click();
    const navigation=page.waitForEvent('framenavigated');
    await page.evaluate(()=>window.replyPayout(5,{status:true,msg:'Payout recorded.'}));
    await navigation;
    assert.strictEqual(await page.evaluate(()=>sessionStorage.getItem('parcel_success_msg')),'Payout recorded.');
    assert.deepStrictEqual(errors,[]);
    console.log('PASS: real payout modal loading, separate amounts, eligibility/confirmation, inline clearing, busy state, CSRF/retry identity and success reload.');
  } finally {await browser.close();}
})().catch(error=>{console.error(error);process.exit(1);});
