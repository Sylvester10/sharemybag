// Exercise production form hydration/parcel DOM helpers and the booking AJAX confirmation boundary.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const root = path.join(__dirname, '..');
const source = fs.readFileSync(path.join(root, 'assets/users/js/booking.js'), 'utf8');

class Element {
    constructor(tag) { this.tagName = tag; this.children = []; this.attributes = {}; this.textContent = ''; }
    setAttribute(name, value) { this.attributes[name] = String(value); }
    appendChild(child) { this.children.push(child); return child; }
    set innerHTML(value) { throw new Error('Restored customer text must never be parsed as HTML'); }
}
const context = {document: {createElement(tag) { return new Element(tag); }}};
vm.createContext(context);
vm.runInContext(source.slice(0, source.indexOf('jQuery(document).ready')), context);

const snapshot = {
    category: 'Laptop', item: 'Spare charger', size: '1', parcel_status: 'with_me', receiver_status: 'someone_else',
    agent_name: 'Edited name', agent_email: 'agent@example.invalid', agent_country_code: '+234', agent_phone: '8022213347',
    agent_address: '12 First Street', agent_locality: 'Lagos', agent_postcode: '',
    receiver_name: 'Receiver name', receiver_email: 'receiver@example.invalid', receiver_country_code: '+44', receiver_phone: '7911123456',
    receiver_address: '20 Second Street', receiver_locality: 'London', receiver_postcode: 'SW1A 1AA',
    insurance: '13.99', payment_method: 'stripe', _booking_step: '4',
    items: JSON.stringify([{category: 'Normal', item_name: '<img src=x onerror=alert(1)>', size: '2', unit_price: '7.25', unit: 'KG'},
        {category: 'Laptop', item_name: 'Laptop', size: '1', unit_price: '40', unit: 'PC'}]),
    traveller_id: '999', payment_status: 'completed', booking_checkout_token: 'untrusted', booking_quote_token: 'untrusted',
};
const fields = {};
Object.keys(snapshot).filter(name => !['items','_booking_step'].includes(name)).forEach(name => {
    fields[name] = [{type: ['category','size','insurance','agent_country_code','receiver_country_code'].includes(name) ? 'select-one' : 'text', value: 'original'}];
});
fields.parcel_status = [{type:'radio',value:'with_me',checked:false},{type:'radio',value:'with_someone',checked:true}];
fields.receiver_status = [{type:'radio',value:'me',checked:true},{type:'radio',value:'someone_else',checked:false}];
fields.payment_method = [{type:'hidden',value:'paystack'},{type:'radio',value:'paystack',checked:true},{type:'radio',value:'stripe',checked:false}];
const form = {querySelectorAll(selector) { return fields[selector.match(/name="([^"]+)"/)[1]] || []; }};
const restored = context.restoreBookingFormFields(form, snapshot);
assert.equal(restored.step, 4, 'Return to the prior existing wizard step');
assert.equal(restored.items.length, 2, 'Keep every parcel, including mixed KG/PC units');
for (const name of ['category','item','size','agent_name','agent_email','agent_country_code','agent_phone','agent_address','agent_locality','agent_postcode',
    'receiver_name','receiver_email','receiver_country_code','receiver_phone','receiver_address','receiver_locality','receiver_postcode','insurance']) {
    assert.equal(fields[name][0].value, snapshot[name], name + ' must restore without autofilling profile data over edited values');
}
assert.equal(fields.parcel_status[0].checked, true);
assert.equal(fields.parcel_status[1].checked, false);
assert.equal(fields.receiver_status[1].checked, true);
assert.equal(fields.payment_method[0].value, 'stripe');
assert.equal(fields.payment_method[1].checked, false);
assert.equal(fields.payment_method[2].checked, true);
for (const name of ['traveller_id','payment_status','booking_checkout_token','booking_quote_token']) {
    assert.equal(fields[name][0].value, 'original', 'The snapshot cannot restore protected field ' + name);
}
const parcel = context.createBookingParcelNode(restored.items[0], '£');
assert.equal(parcel.attributes.price, '14.5');
assert.equal(parcel.children.find(child => child.className === 'name').textContent, '<img src=x onerror=alert(1)>');
assert.equal(parcel.children.find(child => child.className === 'size').textContent, '2KG');
assert.equal(parcel.children.find(child => child.className === 'price').textContent, '£14.50');
assert.equal(parcel.children[4].type, 'button', 'Restored parcels retain an accessible existing delete control');
assert.equal(context.restoreBookingFormFields(form, {items: 'bad JSON', _booking_step: 99}).step, 4);
assert.equal(context.restoreBookingFormFields(form, {items: 'bad JSON'}).items.length, 0);

const shared = fs.readFileSync(path.join(root, 'assets/general/js/my_functions.js'), 'utf8');
const ajaxSource = shared.slice(shared.indexOf('function submitFormAjax(form)'), shared.indexOf('// Inline-status form ajax:'));
const requests = [], errors = [], redirects = [];
let confirmations = 0, resets = 0, successToasts = 0;
const checkoutForm = {dataset:{}, hasAttribute(name) { return name === 'action' || name === 'reset'; },
    getAttribute() { return '/fixture-checkout'; }, reset() { resets++; }};
const adapter = {hasClass() {return true;}, attr() {return adapter;}, each() {return adapter;}};
function $(element) { return element === ajaxContext.location ? {attr(key,value) {redirects.push(value);}} : adapter; }
$.ajax = options => requests.push(options);
const ajaxContext = {$, FormData:class{}, appendGlobalCsrfToFormData(data) {return data;}, showFormLoader(){}, hideFormLoader(){},
    updateGlobalCsrfHash(){}, clearWizardStepErrors(){}, applyWizardStepErrors(){},
    window:{location:{assign(url){redirects.push(url);}},applyBookingCheckoutQuote(form,response) {assert.equal(response.pricing.total_amount, 121.99);confirmations++;}},
    toastr:{error(message){errors.push(message);}, success(){successToasts++;}}, location:{}, setTimeout(callback){callback();}};
vm.createContext(ajaxContext);vm.runInContext(ajaxSource,ajaxContext);
ajaxContext.submitFormAjax(checkoutForm);
ajaxContext.submitFormAjax(checkoutForm);
assert.equal(requests.length,1,'A second click while checkout is starting must not submit twice');
requests[0].success({status:false,quote_changed:true,quote_token:'fresh',pricing:{total_amount:121.99},msg:'Review the updated total before continuing.'});
assert.equal(confirmations,1,'A changed price updates the existing form');
assert.equal(requests.length,1,'Updated pricing must not automatically pay or resubmit');
assert.equal(resets,0,'Quote changes must preserve all entered fields');
assert.deepEqual(redirects,[],'Quote changes remain on the existing booking page');
requests[0].complete();
assert.equal(checkoutForm.dataset.bookingSubmitting,undefined,'A completed request permits an explicit subsequent confirmation');
ajaxContext.submitFormAjax(checkoutForm);
requests[1].success({status:true,msg:'Redirecting',redirect:'https://checkout.example.invalid/fixture'});
assert.deepEqual(redirects,['https://checkout.example.invalid/fixture'],'Successful booking checkout hands off immediately');
assert.equal(successToasts,0,'Checkout handoff must not add a success toast or banner');
assert.equal(resets,0,'Checkout handoff preserves the complete form for back navigation');
// Exercise the production expiry timer: no reload or substitute purchase action.
const expirySource = source.slice(source.indexOf('    function scheduleBookingRecoveryExpiry('),
    source.indexOf('    function initializeBookingRecoveryButtons('));
function expiryFixture(mode, initialStatus, activePayment = false) {
    const classes = new Set(['bg-warning-subtle', 'text-warning']);
    const badge = {label:initialStatus,
        first(){return this;}, removeClass(value){value.split(' ').forEach(name=>classes.delete(name));return this;},
        addClass(value){value.split(' ').forEach(name=>classes.add(name));return this;},
        text(value){if(value===undefined)return this.label;this.label=value;return this;}};
    const status={value:'Earlier message',text(value){this.value=value;return this;}};
    const container={find(selector){assert.equal(selector,'[data-booking-recovery-status]','Expiry must not reveal another action');return status;},
        closest(selector){assert.equal(selector,'td');return {find(selector){assert.equal(selector,'.badge');return badge;}};}};
    const attributes={'data-booking-mode':mode,'data-expiry-checking':String(activePayment)}, data={};
    const button={hidden:false,disabled:false,
        data(key,value){if(value===undefined)return data[key];data[key]=value;return this;},
        attr(key,value){if(value===undefined)return attributes[key];attributes[key]=value;return this;},
        addClass(name){assert.equal(name,'d-none');this.hidden=true;return this;},
        prop(key,value){assert.equal(key,'disabled');this.disabled=value;return this;},
        closest(selector){assert.equal(selector,'[data-booking-recovery]');return container;}};
    let now=1000000, callback;
    const timerContext={Date:{now(){return now;}},Number,window:{clearTimeout(){},setTimeout(fn){callback=fn;return 1;}}};
    vm.createContext(timerContext);vm.runInContext(expirySource,timerContext);
    timerContext.scheduleBookingRecoveryExpiry(button,1900);
    assert.equal(button.hidden,false,'The action remains available before its deadline');
    now=1900001;callback();
    return {button,badge,status,classes};
}
const expiredUnpaid=expiryFixture('resume','Not completed');
assert.equal(expiredUnpaid.button.hidden,true);assert.equal(expiredUnpaid.button.disabled,true);
assert.equal(expiredUnpaid.badge.label,'Canceled');assert(expiredUnpaid.classes.has('text_danger'));
assert(expiredUnpaid.classes.has('bg-danger-subtle'),'Canceled keeps the established subtle red background');
assert.equal(expiredUnpaid.status.value,'','Only the status remains after expiry');
const expiredUnknown=expiryFixture('check','Checking payment');
assert.equal(expiredUnknown.button.hidden,true);assert.equal(expiredUnknown.badge.label,'Checking payment','Unknown charges must not be mislabeled canceled');
const expiredOpen=expiryFixture('resume','Not completed',true);
assert.equal(expiredOpen.button.hidden,true);assert.equal(expiredOpen.badge.label,'Checking payment','Open provider sessions still require verification at expiry');
const initSource=source.slice(source.indexOf('    function initializeBookingRecoveryButtons('),source.indexOf("    $(document).on('click.bookingResume'"));
function recoveryButton(){return {store:{},disabled:true,data(key,value){if(value===undefined)return this.store[key];this.store[key]=value;return this;},
    prop(key,value){this[key]=value;return this;},attr(){return '1900';}};}
const firstPageButton=recoveryButton(), secondPageButton=recoveryButton();
function collection(buttons){return {each(callback){buttons.forEach(button=>callback.call(button));return this;}};}
let onDraw;const scheduled=[];
const historyTable={DataTable(){return {rows(){return {nodes(){return {to$(){return {find(){return collection([firstPageButton,secondPageButton]);}};}};}};}};},
    on(event,callback){onDraw=callback;return this;},find(){return collection([secondPageButton]);}};
function history$(target){return typeof target!=='string'?target:target==='#default_order'?historyTable:collection([firstPageButton]);}
history$.fn={dataTable:{isDataTable(){return true;}}};
vm.runInNewContext(initSource,{$:history$,scheduleBookingRecoveryExpiry(button){scheduled.push(button);}});
assert.equal(firstPageButton.disabled,false);assert.equal(secondPageButton.disabled,false,'Detached page-2 recovery action is initialized before pagination');
assert.equal(scheduled.length,2,'Every booking gets one deadline timer');
secondPageButton.disabled=true;onDraw.call(historyTable);
assert.equal(secondPageButton.disabled,true,'A redraw cannot reactivate a button already expired or busy');
assert.equal(scheduled.length,2,'Pagination does not duplicate or extend recovery timers');
console.log('PASS: full form restoration, safe quote confirmation and timed status-only expiry without a replacement action.');
