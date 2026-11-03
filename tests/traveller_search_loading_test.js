// Run the production submit handler with recording UI and network adapters.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync(require('node:path').join(__dirname,'../assets/users/js/search.js'),'utf8');
const start = source.indexOf("    $('#search_form').submit(function (e) {");
const handler = source.slice(start,source.lastIndexOf('\n});'));
const nodes = new Map(), requests = [], notices = [];
function node(key) {
    if(!nodes.has(key)) nodes.set(key,{classes:new Set(key==='#search-spinner'||key==='.search-results-card'?['d-none']:[]),attributes:{},disabled:false,value:'',content:'',
        submit(fn){this.onSubmit=fn;return this;},val(){return this.value;},find(){return node('button');},
        attr(key,value){if(value===undefined)return key==='action'?'/fixture/search':this.attributes[key];this.attributes[key]=value;return this;},
        prop(key,value){if(value===undefined)return this[key];this[key]=value;return this;},
        addClass(key){this.classes.add(key);return this;},removeClass(key){this.classes.delete(key);return this;},
        html(value){this.content=value;return this;},serialize(){return 'fixture=1';}});
    return nodes.get(key);
}
function $(key){return typeof key==='string'?node(key):key;}
$.ajax=options=>requests.push(options);
vm.runInNewContext(handler,{$,toastr:{error(message){notices.push(message);}},updateGlobalCsrfHash(){},
    buildTravellerCard(row){return '<article>'+row.hash+'</article>';},escapeHtml(value){return value;}});
node('#select_location').value='Nigeria';node('#select_destination').value='United Kingdom';
const submit=()=>node('#search_form').onSubmit.call(node('#search_form'),{preventDefault(){}});
submit();assert.equal(requests.length,1);assert(!node('#search-spinner').classes.has('d-none'));
assert.equal(node('#search-results').attributes['aria-busy'],'true');assert(node('button').disabled);
submit();assert.equal(requests.length,1,'Repeated submit while searching does not start another request');
requests[0].success({status:true,travellers:[{hash:'fixture-result'}]});requests[0].complete();
assert(node('#search-spinner').classes.has('d-none'));assert(!node('button').disabled);
assert(!node('.search-results-card').classes.has('d-none'));assert(node('#search-results').content.includes('fixture-result'));
submit();requests[1].success({status:false,msg:'No travellers'});requests[1].complete();
assert(node('#search-spinner').classes.has('d-none'));assert(node('#search-results').content.includes('No Traveller Available'));
submit();requests[2].error();requests[2].complete();
assert(node('#search-spinner').classes.has('d-none'));assert(!node('button').disabled);assert(notices.length>0);
assert.equal(node('#search-results').attributes['aria-busy'],'false');
console.log('PASS: search loading starts and stops across results, empty results, errors and duplicate submissions.');
