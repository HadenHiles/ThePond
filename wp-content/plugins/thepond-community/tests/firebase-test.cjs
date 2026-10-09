const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const root = path.resolve(__dirname, '../../../..');
let checks = 0;
for (const initialized of [false, true]) {
  for (const name of ['auth', 'auth-no-cookie', 'scores']) {
    const document = {cookie: name === 'auth-no-cookie' ? '' : 'fb_user={"providerData":[{"providerId":"password"}]}'};
    let initializationCount = 0;
    let authListeners = 0;
    const bindings = [];
    const auth = {onAuthStateChanged() { authListeners++; }};
    const firebase = {
      apps: initialized ? [{}] : [],
      initializeApp() { initializationCount++; this.apps.push({}); },
      auth() { return auth; }, firestore() { return {}; },
    };
    function jquery(selector) {
      if (selector === document) return {ready(fn) { fn(); }};
      return {
        length: 1,
        click(fn) { bindings.push(selector); return this; },
        submit(fn) { bindings.push(selector); return this; },
        insertAfter() { return this; }, attr() { return this; }, removeAttr() { return this; },
      };
    }
    const context = {
      document, jQuery: jquery, firebase, console,
      getCookie() { return '{"providerData":[{"providerId":"password"}]}'; },
      Date: class extends Date {
        constructor() { super('2026-10-09T00:00:00Z'); }
      },
    };
    const file = name !== 'scores' ?
      'wp-content/themes/buddyboss-theme-child-1.0.0/assets/js/firebase.js' :
      'wp-content/plugins/challenge-score/js/score-management.js';
    vm.runInNewContext(fs.readFileSync(path.join(root, file), 'utf8'), context);
    assert.equal(initializationCount, initialized ? 0 : 1, name + ': Firebase initialized more than once');
    checks++;
    if (name !== 'scores') {
      context.setCookie('probe', 'value', 1);
      assert.ok(document.cookie.includes('expires=Fri, 09 Oct 2026 00:01:00 GMT'), 'Firebase cookie duration changed from minutes');
      checks++;
      for (const selector of ['.mepr-signup-form .mepr-submit', '#mepr_forgot_password_form', '#mepr_account_form', '#mepr-newpassword-form']) {
        assert.ok(bindings.includes(selector), 'Auth handler missing: ' + selector);
        checks++;
      }
    } else {
      assert.equal(authListeners, 1, 'Challenge scores fail with an existing Firebase app');
      checks++;
    }
  }
}
assert.ok(!fs.readFileSync(path.join(root, 'wp-content/themes/buddyboss-theme-child-1.0.0/assets/js/pond-dashboard.js'), 'utf8').includes('function setCookie'), 'Dashboard overwrites the Firebase cookie helper');
checks++;
for (const scenario of ['remember', 'without-remember', 'other-page']) {
  const movements = [];
  const exists = scenario !== 'other-page';
  function collection(kind) {
    return {
      length: kind === 'empty' || !exists || (kind === 'remember-row' && scenario !== 'remember') ? 0 : 1,
      closest() { return collection(kind === 'login' ? 'wrapper' : 'label'); },
      find(selector) {
        return collection(selector === '.mepr-login-actions' ? 'actions' :
          selector === '#rememberme' ? 'remember' : 'submit');
      },
      parent() { return collection('remember-row'); },
      addClass() { return this; },
      append(actions) { assert.equal(actions.length, 1); movements.push('remember-row'); return this; },
      insertBefore(submit) { assert.equal(submit.length, 1); movements.push('before-submit'); return this; },
      on() { return this; },
      each() { return this; },
    };
  }
  function jquery(selector) {
    if (typeof selector === 'function') { selector(jquery); return; }
    return collection(selector === '#firebase-login-page #mepr_loginform' ? 'login' : 'empty');
  }
  vm.runInNewContext(fs.readFileSync(path.join(root, 'wp-content/themes/buddyboss-theme-child-1.0.0/assets/js/custom.js'), 'utf8'),
    {jQuery: jquery, document: {}, console});
  assert.deepEqual(movements, scenario === 'remember' ? ['remember-row'] :
    scenario === 'without-remember' ? ['before-submit'] : [], 'Forgot-password placement: ' + scenario);
  checks++;
}
console.log(`PASS: ${checks} Firebase initialization, auth UI and handler checks`);
