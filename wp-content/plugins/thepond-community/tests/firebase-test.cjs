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
    {jQuery: jquery, document: {querySelector() { return null; }}, console});
  assert.deepEqual(movements, scenario === 'remember' ? ['remember-row'] :
    scenario === 'without-remember' ? ['before-submit'] : [], 'Forgot-password placement: ' + scenario);
  checks++;
}
async function checkTokenHandoff() {
  const source = fs.readFileSync(path.join(root,
    'wp-content/plugins/miniorange-firebase-authentication-enterprise/admin/js/firebase-auth-main-script.js'), 'utf8');
  for (const scenario of ['google', 'facebook', 'new-account', 'registration', 'token-error', 'popup-error', 'password']) {
    const forms = [];
    const cookies = [];
    const errors = [];
    const handlers = {};
    let lookupCount = 0;
    let resolveToken;
    let rejectToken;
    const tokenPromise = new Promise((resolve, reject) => {
      resolveToken = resolve;
      rejectToken = reject;
    });
    const user = {
      email: 'member@example.com', _lat: 'expired-private-token',
      getIdToken(forceRefresh) {
        assert.equal(forceRefresh, true);
        return tokenPromise;
      },
    };
    const failure = {code: 'auth/network-request-failed', message: 'Token request failed'};
    const document = {
      forms: {},
      body: {appendChild(form) { forms.push(form); document.forms.jwtform = form; }},
      createElement() {
        return {
          elements: {}, attributes: {}, value: '', submissions: 0,
          setAttribute(name, value) { this.attributes[name] = value; },
          appendChild(input) { this.elements[input.attributes.name] = input; },
          submit() { this.submissions++; },
        };
      },
      getElementById(id) { return forms[0].elements[id]; },
    };
    function jquery(selector) {
      return {
        length: selector === '#firebase-login-page' && scenario === 'registration' ? 0 : 1,
        text(value) { if (selector === '#login-error') errors.push(value); return this; },
        show() { return this; }, hide() { return this; }, modal() { return this; },
        off(event) { delete handlers[event]; return this; },
        on(event, fn) { handlers[event] = fn; return this; },
      };
    }
    jquery.ajax = options => {
      lookupCount++;
      options.success({exists: scenario !== 'new-account'});
    };
    const auth = {
      signInWithPopup() {
        return scenario === 'popup-error' ? Promise.reject(failure) : Promise.resolve({user});
      },
      signInWithEmailAndPassword() { return Promise.resolve({user}); },
    };
    function firebaseAuth() { return auth; }
    firebaseAuth.GoogleAuthProvider = function () {};
    firebaseAuth.FacebookAuthProvider = function () {};
    const context = {
      document, jQuery: jquery,
      firebase: {apps: [{}], auth: firebaseAuth},
      window: {location: {href: 'https://example.com/login/'}},
      navigator: {userAgent: 'test'}, console: {log() {}, error() {}},
      setCookie(name, value) { cookies.push({name, value}); },
    };
    vm.runInNewContext(source, context);
    if (scenario === 'password') {
      context.mo_firebase_auth_firebaseAuthentication('project', 'key', user.email, 'password', '');
    } else {
      context.mo_firebase_auth_provider_firebaseAuthentication(
        scenario === 'facebook' ? 'facebook' : 'google', 'project', 'key', 'test_check_true');
    }
    await new Promise(resolve => setImmediate(resolve));
    assert.equal(forms[0].submissions, 0, scenario + ': submitted before token was ready');
    assert.equal(lookupCount, 0, scenario + ': checked email before token was ready');
    assert.equal(cookies.length, 0, scenario + ': saved user before token was ready');
    if (scenario === 'token-error') rejectToken(failure);
    else resolveToken('fresh-id-token');
    await new Promise(resolve => setImmediate(resolve));
    if (scenario.endsWith('-error')) {
      assert.equal(forms[0].submissions, 0, scenario + ': submitted failed authentication');
      assert.equal(forms[0].elements.fb_jwt.value, 'empty_string');
      assert.deepEqual(errors, [failure.message]);
    } else {
      assert.equal(forms[0].elements.fb_jwt.value, 'fresh-id-token');
      assert.equal(cookies.length, 1);
      if (scenario === 'new-account') {
        assert.equal(forms[0].submissions, 0, 'New account must wait for confirmation');
        handlers['click.moFirebase']({preventDefault() {}});
      }
      assert.equal(forms[0].submissions, 1, scenario + ': login must submit exactly once');
      if (scenario !== 'password') {
        assert.equal(forms[0].elements.fb_is_test.value, 'test_check_true');
        assert.equal(JSON.parse(forms[0].elements.fb_user.value).email, user.email);
      }
    }
    checks++;
  }
}

checkTokenHandoff().then(() => {
  console.log(`PASS: ${checks} Firebase initialization, auth UI, handler and token handoff checks`);
}).catch(error => {
  console.error(error);
  process.exitCode = 1;
});
