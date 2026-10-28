const assert = require("node:assert/strict");
const fs = require("node:fs");
const path = require("node:path");
const vm = require("node:vm");
const { test } = require("node:test");

const root = path.resolve(__dirname, "../../../..");
const theme = path.join(root, "wp-content/themes/buddyboss-theme-child-1.0.0");
const source = fs.readFileSync(path.join(theme, "assets/js/signup-password.js"), "utf8");
const memberpress = path.join(root, "wp-content/plugins/memberpress");
const scorer = fs.readFileSync(path.join(memberpress, "js/zxcvbn-async.js"), "utf8")
    .split("function mepr_score_it")[1].split("jQuery(function")[0];
const dictionary = fs.readFileSync(path.join(memberpress, "js/zxcvbn.js"), "utf8");

function element() {
    return {
        attrs: {}, children: [], textContent: "", value: "", listeners: {},
        setAttribute(name, value) { this.attrs[name] = value; },
        getAttribute(name) { return this.attrs[name] || null; },
        appendChild(child) { this.children.push(child); },
        addEventListener(name, callback) { this.listeners[name] = callback; },
    };
}

function fixture({ requiredScore = 3, loaded = true, count = 1 } = {}) {
    const observers = [];
    const forms = Array.from({ length: count }, () => {
        const area = element();
        const input = element();
        input.setAttribute("aria-describedby", "existing-help");
        const meter = element();
        meter.closest = () => area;
        const score = element();
        score.value = "0";
        const fields = { ".mepr-password": input, ".mp-password-strength-display": meter, ".mp-pass-strength": score };
        return { area, input, meter, score, querySelector: selector => fields[selector] };
    });
    const config = {
        requiredScore, minLength: 6, guidance: 'Password must be "Very Strong" or stronger. Try unrelated words.',
        empty: "Enter a password to check it.", checking: "Checking password strength...",
        tooWeak: "Not ready: choose a stronger password.", tooShort: "Not ready: use at least 6 characters.",
        accepted: "Meets password requirements.",
    };
    function $(target) {
        if (typeof target === "function") return target($);
        const form = forms.find(form => form.input === target);
        function field(selector) {
            const node = form.querySelector(selector);
            return {
                attr(name, value) { node.setAttribute(name, value); return this; },
                html(value) { node.textContent = value; return this; },
                val(value) { if (value === undefined) return node.value; node.value = value; return this; },
            };
        }
        return { val: () => target.value, closest: () => ({ find: field }) };
    }
    const context = {
        jQuery: $, pondSignupPassword: config,
        document: {
            querySelectorAll(selector) { assert.equal(selector, ".mepr-signup-form"); return forms; },
            createElement: element,
        },
        MutationObserver: class {
            constructor(callback) { observers.push(callback); }
            observe(node, options) {
                assert.ok(forms.some(form => form.meter === node));
                assert.equal(options.subtree, true);
            }
        },
        MeprZXCVBN: {
            indicator: "Password Strength", required: "", very_weak: "Weak", weak: "Medium",
            medium: "Strong", strong: "Very Strong", very_strong: "Unbreakable",
        },
    };
    context.window = context;
    vm.createContext(context);
    vm.runInContext("function mepr_score_it" + scorer, context);
    if (loaded) vm.runInContext(dictionary, context);
    vm.runInContext(source, context);
    const flush = () => observers.forEach(callback => callback());
    function input(value, index = 0) {
        forms[index].input.value = value;
        forms[index].input.listeners.input();
        flush();
    }
    return { forms, config, context, input, flush,
        load() { vm.runInContext(dictionary, context); },
        status(index = 0) { return forms[index].area.children[1]; },
        state(index = 0) { return forms[index].area.attrs["data-pond-password-state"]; },
    };
}

test("Strong stays amber below the actual Very Strong requirement; accepted passwords alone go green", () => {
    const f = fixture();
    assert.equal(f.state(), "empty");
    const examples = ["password", "hockeypond42", "sunset otter", "BlueSkates!27", "correct horse battery staple"];
    const scores = new Set();
    for (const password of examples) {
        f.input(password);
        const score = f.context.zxcvbn(password).score;
        scores.add(score);
        assert.equal(Number(f.forms[0].score.value), score, "MemberPress's submitted score must be unchanged");
        assert.equal(f.state(), score >= 3 ? "accepted" : "rejected");
        assert.equal(f.status().textContent, score >= 3 ? f.config.accepted : f.config.tooWeak);
        if (score === 2) {
            assert.match(f.forms[0].meter.textContent, /^Strong/);
            assert.equal(f.forms[0].meter.attrs.class, "mp-password-strength-display mp-mediumpass");
        }
    }
    assert.ok(scores.has(2), "Exercise the misleading Strong label, not just weak and very strong");
    assert.ok(scores.has(3), "Exercise the exact required threshold");
    assert.ok(scores.has(4));
    f.input("");
    assert.equal(f.state(), "empty");
    assert.equal(f.status().textContent, f.config.empty);
});

test("Verdict tracks each configured MemberPress threshold, without inventing composition rules", () => {
    for (const requiredScore of [0, 1, 2, 3]) {
        const f = fixture({ requiredScore });
        for (const value of ["password", "hockeypond42", "sunset otter", "BlueSkates!27", "correct horse battery staple"]) {
            f.input(value);
            assert.equal(f.state(), f.context.zxcvbn(value).score >= requiredScore ? "accepted" : "rejected");
        }
        f.input("X7!q");
        assert.equal(f.state(), "rejected");
        assert.equal(f.status().textContent, f.config.tooShort);
    }
});

test("Loading, initial values, paste/change updates and multiple signup forms stay in sync", () => {
    const f = fixture({ loaded: false, count: 2 });
    f.input("correct horse battery staple");
    assert.equal(f.state(), "pending");
    assert.equal(f.status().textContent, f.config.checking);
    f.load();
    // The vendor's async initialization, paste and change handlers all call this scorer.
    f.context.mepr_score_it(f.context.jQuery(f.forms[0].input));
    f.flush();
    assert.equal(f.state(), "accepted");
    assert.equal(f.state(1), "empty");
    f.forms[0].input.value = "password";
    f.context.mepr_score_it(f.context.jQuery(f.forms[0].input));
    f.flush();
    assert.equal(f.state(), "rejected");
    f.input("correct horse battery staple", 1);
    assert.equal(f.state(1), "accepted");
    assert.equal(f.state(), "rejected");
    assert.notEqual(f.status().id, f.status(1).id);
});

test("Status is accessible, preserves other help, and ignores pages without signup forms", () => {
    const f = fixture();
    const status = f.status();
    assert.equal(status.attrs.role, "status");
    assert.equal(status.attrs["aria-live"], "polite");
    assert.equal(status.attrs["aria-atomic"], "true");
    assert.equal(f.forms[0].input.attrs["aria-describedby"],
        "existing-help pond-password-guidance-0 pond-password-status-0");
    assert.equal(f.forms[0].area.children[0].textContent, f.config.guidance);
    assert.doesNotThrow(() => fixture({ count: 0 }));
});

test("Theme wiring uses the server policy and overrides vendor green only for signup verdicts", () => {
    const php = fs.readFileSync(path.join(theme, "functions.php"), "utf8");
    assert.match(php, /'requiredScore' => MeprZxcvbnCtrl::get_required_int\(\)/);
    assert.match(php, /THEPOND_FIREBASE_MIN_PASSWORD/);
    assert.match(php, /array\('jquery', 'mepr-zxcvbn'\)/);
    const css = fs.readFileSync(path.join(theme, "assets/css/custom.css"), "utf8");
    for (const state of ["rejected", "accepted"]) {
        assert.match(css, new RegExp('\\.mepr-signup-form \\.mp-password-strength-area\\[data-pond-password-state="' + state + '"\\] \\.mp-password-strength-display'));
    }
});
