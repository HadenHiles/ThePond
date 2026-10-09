const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.resolve(__dirname, '../../challenge-score/js/score-management.js'), 'utf8');
let checks = 0;

function harness(member = true) {
  const elements = new Map();
  const document = {};
  const calls = [];
  const failures = {};
  let records = [{id: 'existing', score: 37}];
  let listener;
  function element(key) {
    if (!elements.has(key)) {
      elements.set(key, {
        length: 1, value: '', markup: '', properties: {}, handlers: {}, classes: new Set(), visible: false,
        val(value) { if (value === undefined) return this.value; this.value = value; return this; },
        html(value) { if (value === undefined) return this.markup; this.markup = value; return this; },
        text(value) { if (value === undefined) return this.markup; this.markup = value; return this; },
        prop(name, value) { if (value === undefined) return this.properties[name]; this.properties[name] = value; return this; },
        on(event, selector, fn) { this.handlers[event] = typeof selector === 'function' ? selector : fn; return this; },
        off(event) { delete this.handlers[event]; return this; },
        parent() { return element('row'); },
        addClass(name) { this.classes.add(name); return this; },
        removeClass(name) { this.classes.delete(name); return this; },
        hasClass(name) { return this.classes.has(name); },
        children() { return {length: (this.markup.match(/class="score"/g) || []).length}; },
        show() { this.visible = true; return this; },
        hide() { this.visible = false; return this; },
        css() { return this; },
        outerHeight() { return 100; },
        data() { return 'existing'; },
        remove() { this.removed = true; element('#scores').markup = ''; return this; },
      });
    }
    return elements.get(key);
  }
  function jquery(selector) {
    if (selector === document) return {ready(fn) { fn(); }};
    if (typeof selector === 'object') return selector;
    if (selector.includes(', ')) {
      return {prop(name, value) { selector.split(', ').forEach(key => element(key).prop(name, value)); }};
    }
    return element(selector.replace(/^input/, ''));
  }
  element('#challenge-id').value = '20804';
  element('#challenge-id').length = member ? 1 : 0;
  element('#user-id').value = '123';
  element('#error-message').markup = 'Failed to add score';
  const db = {
    collection(name) { calls.push(['collection', name]); return this; },
    doc(id) { calls.push(['doc', id]); return this; },
    where(...args) { calls.push(['where', ...args]); return this; },
    orderBy(name) { calls.push(['orderBy', name]); return this; },
    get() {
      calls.push(['get']);
      return failures.get ? Promise.reject(new Error('Read denied')) : Promise.resolve({
        forEach(fn) { records.forEach(record => fn({
          id: record.id, data() { return {...record, created: {toDate() { return new Date('2022-10-17T00:08:00Z'); }}}; },
        })); },
      });
    },
    add(data) {
      calls.push(['add', data]);
      if (failures.add) return Promise.reject(new Error('Write denied'));
      records.push({id: 'new', ...data});
      return Promise.resolve({id: 'new'});
    },
    delete() {
      calls.push(['delete']);
      if (failures.delete) return Promise.reject(new Error('Delete denied'));
      records = records.filter(record => record.id !== 'existing');
      return Promise.resolve();
    },
  };
  vm.runInNewContext(source, {
    document, jQuery: jquery, Date, console: {log() {}, error(...args) { calls.push(['error', ...args]); }},
    setTimeout() {},
    firebase: {apps: [{}], auth() { return {onAuthStateChanged(fn) { listener = fn; }}; }, firestore() { return db; }},
  });
  return {
    element, calls, failures, authenticate(user) { listener(user); },
    trigger(key, event, extra = {}) { return element(key).handlers[event].call(element(key), {preventDefault() {}, ...extra}); },
  };
}
const settle = () => new Promise(resolve => setImmediate(resolve));

(async () => {
  const unauthorized = harness(false);
  assert.equal(unauthorized.calls.length, 0); checks++;
  const app = harness();
  app.authenticate({uid: 'firebase-member'});
  await settle();
  assert.match(app.element('#scores').markup, /37/); checks++;
  assert.ok(app.calls.some(call => JSON.stringify(call) === '["where","challenge_id","==",20804]')); checks++;
  assert.ok(app.calls.some(call => call[0] === 'doc' && call[1] === 'firebase-member')); checks++;
  assert.ok(app.calls.some(call => call[0] === 'orderBy' && call[1] === 'created')); checks++;
  app.authenticate({uid: 'firebase-member'});
  await settle();
  assert.equal(Object.keys(app.element('#add-score').handlers).length, 1); checks++;

  app.element('#challenge-score').val('42.1256');
  app.trigger('#add-score', 'click.pondScores');
  app.trigger('#add-score', 'click.pondScores');
  assert.equal(app.calls.filter(call => call[0] === 'add').length, 1); checks++;
  await settle();
  const saved = app.calls.find(call => call[0] === 'add')[1];
  assert.equal(saved.challenge_id, 20804); checks++;
  assert.equal(saved.wp_user_id, 123); checks++;
  assert.equal(saved.score, 42.1256); checks++;
  assert.ok(saved.created instanceof Date); checks++;
  assert.match(app.element('#scores').markup, /42.1256/); checks++;
  assert.equal(app.element('#challenge-score').val(), ''); checks++;
  assert.equal(app.element('#add-score').prop('disabled'), false); checks++;

  for (const invalid of ['', '-1', 'Infinity', 'bad score']) {
    const previous = app.calls.filter(call => call[0] === 'add').length;
    app.element('#challenge-score').val(invalid);
    app.trigger('#challenge-score', 'keypress.pondScores', {which: 13});
    assert.equal(app.calls.filter(call => call[0] === 'add').length, previous); checks++;
    assert.equal(app.element('#error-message').visible, true); checks++;
  }
  app.failures.add = true;
  app.element('#challenge-score').val('50');
  app.trigger('#add-score', 'click.pondScores');
  await settle();
  assert.equal(app.element('#error-message').text(), 'Write denied'); checks++;
  assert.equal(app.element('#add-score').prop('disabled'), false); checks++;
  assert.match(app.element('#scores').markup, /37/); checks++;

  const deleteLink = app.element('deleteLink');
  app.failures.delete = true;
  app.element('.scores').handlers['click.pondScores'].call(deleteLink, {preventDefault() {}});
  await settle();
  assert.equal(app.element('row').removed, undefined); checks++;
  assert.equal(app.element('row').hasClass('deleting'), false); checks++;
  assert.equal(app.element('#error-message').text(), 'Delete denied'); checks++;
  app.failures.delete = false;
  app.element('.scores').handlers['click.pondScores'].call(deleteLink, {preventDefault() {}});
  await settle();
  assert.equal(app.element('row').removed, true); checks++;

  app.failures.get = true;
  app.authenticate({uid: 'firebase-member'});
  await settle();
  assert.match(app.element('#scores').markup, /Unable to load/); checks++;
  assert.equal(app.element('#error-message').text(), 'Read denied'); checks++;
  app.authenticate(null);
  assert.match(app.element('#scores').markup, /Sign in/); checks++;
  assert.equal(app.element('#add-score').prop('disabled'), true); checks++;
  assert.equal(Object.keys(app.element('#add-score').handlers).length, 0); checks++;
  console.log(`PASS: ${checks} challenge score loading, saving, deletion and failure checks`);
})().catch(error => { console.error(error); process.exitCode = 1; });
