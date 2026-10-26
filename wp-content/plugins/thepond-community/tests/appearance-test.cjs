const assert = require("node:assert/strict");
const fs = require("node:fs");
const path = require("node:path");
const vm = require("node:vm");
const { test } = require("node:test");

const source = fs.readFileSync(path.join(__dirname,
    "../../../themes/buddyboss-theme-child-1.0.0/assets/js/pond-appearance.js"), "utf8");

function fixture(cookie = "", readyState = "loading", blockCookies = false) {
    const buttons = [];
    const listeners = {};
    const properties = {};
    const errors = [];
    const alerts = [];
    const classes = new Set();
    const root = { dataset: {}, style: { setProperty: (key, value) => { properties[key] = value; } } };
    const body = { classList: { toggle: (name, enabled) => enabled ? classes.add(name) : classes.delete(name) } };
    const document = {
        documentElement: root, body: readyState === "loading" ? null : body, readyState,
        querySelectorAll: selector => selector === "[data-pond-theme-toggle]" ? buttons :
            [{ prepend: button => buttons.push(button) }, { prepend: button => buttons.push(button) }],
        createElement: () => ({
            attrs: {}, setAttribute(key, value) { this.attrs[key] = value; },
            appendChild(child) { this.firstElementChild = child; },
        }),
        addEventListener: (name, callback, capture) => { listeners[name] = { callback, capture }; },
    };
    let writtenCookie = "";
    Object.defineProperty(document, "cookie", {
        get: () => cookie,
        set: value => {
            writtenCookie = value;
            if (!blockCookies) cookie = value.split(";")[0];
        },
    });
    const context = {
        document, location: { protocol: "https:" },
        pondAppearance: {
            lightColor: "#14345a", darkColor: "#36cce4",
            lightLabel: "Switch to light mode", darkLabel: "Switch to dark mode",
            saveError: "Appearance could not be saved",
        },
        console: { error: (...args) => errors.push(args) }, alert: text => alerts.push(text),
    };
    vm.runInNewContext(source, context);
    function ready() {
        document.body = body;
        listeners.DOMContentLoaded?.callback();
    }
    function click(button = buttons[0]) {
        let prevented = false;
        let stopped = false;
        listeners.click.callback({
            target: { closest: () => button },
            preventDefault: () => { prevented = true; },
            stopImmediatePropagation: () => { stopped = true; },
        });
        return { prevented, stopped };
    }
    return { root, properties, classes, buttons, errors, alerts, listeners, ready, click,
        get writtenCookie() { return writtenCookie; }, get cookie() { return cookie; } };
}

test("head script applies saved dark preference before body rendering", () => {
    const f = fixture("other=value; bbtheme=dark");
    assert.equal(f.root.dataset.pondTheme, "dark");
    assert.equal(f.properties["--pond-accent"], "#36cce4");
    assert.equal(f.properties["--pond-accent-rgb"], "54, 204, 228");
    f.ready();
    assert.ok(f.classes.has("bb-dark-theme"));
    assert.equal(f.buttons.length, 2);
    for (const button of f.buttons) {
        assert.equal(button.attrs["aria-pressed"], "true");
        assert.equal(button.attrs["aria-label"], "Switch to light mode");
    }
});

test("light defaults to navy and both toggles share a persistent preference", () => {
    const f = fixture();
    assert.equal(f.properties["--pond-accent"], "#14345a");
    assert.equal(f.properties["--pond-action-text"], "#ffffff");
    f.ready();
    assert.deepEqual(f.click(), { prevented: true, stopped: true });
    assert.equal(f.cookie, "bbtheme=dark");
    assert.match(f.writtenCookie, /Max-Age=31536000; Path=\/; SameSite=Lax; Secure$/);
    assert.equal(f.properties["--pond-action-text"], "#000000");
    f.click(f.buttons[1]);
    assert.equal(f.cookie, "bbtheme=light");
    assert.equal(f.root.dataset.pondTheme, "light");
    assert.ok(!f.classes.has("bb-dark-theme"));
    assert.equal(fixture(f.cookie).root.dataset.pondTheme, "light");
});

test("late loading and native LMS toggle use the same behavior without duplicate handlers", () => {
    const f = fixture("bbtheme=dark", "complete");
    assert.equal(f.buttons.length, 2);
    assert.equal(f.listeners.click.capture, true);
    assert.deepEqual(f.click({ id: "bb-toggle-theme" }), { prevented: true, stopped: true });
    assert.equal(f.cookie, "bbtheme=light");
});

test("unrelated clicks are untouched and blocked persistence is explicitly reported", () => {
    const f = fixture("", "complete", true);
    assert.deepEqual(f.click(null), { prevented: false, stopped: false });
    f.click();
    assert.equal(f.errors.length, 1);
    assert.deepEqual(f.alerts, ["Appearance could not be saved"]);
    assert.equal(f.root.dataset.pondTheme, "dark");
});

test("saved navy and cyan action text exceed WCAG AA normal-text contrast", () => {
    function luminance(hex) {
        const channels = [1, 3, 5].map(offset => {
            const value = parseInt(hex.slice(offset, offset + 2), 16) / 255;
            return value <= 0.04045 ? value / 12.92 : ((value + 0.055) / 1.055) ** 2.4;
        });

        return channels[0] * 0.2126 + channels[1] * 0.7152 + channels[2] * 0.0722;
    }
    for (const cookie of ["bbtheme=light", "bbtheme=dark"]) {
        const f = fixture(cookie);
        const background = luminance(f.properties["--pond-accent"]);
        const text = luminance(f.properties["--pond-action-text"]);
        const contrast = (Math.max(background, text) + 0.05) / (Math.min(background, text) + 0.05);
        assert.ok(contrast >= 4.5, `Action text contrast ${contrast} is below 4.5:1`);
    }
});

test("mobile dashboard keeps Community above Facebook while moving both cards and restoring desktop placement", () => {
    const community = { id: "pond-dashboard-community" };
    const facebook = { id: "facebook-secret-phrase" };
    const socialCards = { id: "pond-dashboard-social", children: [community, facebook] };
    const profile = { id: "ld-profile" };
    const vault = { id: "skills-vault" };
    const courses = {
        children: [socialCards, profile],
        insertBefore(node, next) {
            node.parentNode.children.splice(node.parentNode.children.indexOf(node), 1);
            this.children.splice(this.children.indexOf(next), 0, node);
            node.parentNode = this;
        },
    };
    const portal = {
        children: [{ id: "courses" }, { id: "challenges" }, { id: "move-makers" }, { id: "routines" }, vault],
        insertBefore: courses.insertBefore,
    };
    socialCards.parentNode = courses;
    socialCards.nextSibling = profile;
    vault.parentNode = portal;
    const media = {
        matches: true,
        addEventListener(event, handler) {
            assert.equal(event, "change");
            this.change = handler;
        },
    };
    const empty = {
        length: 0, closest() { return this; }, find() { return this; },
        on() { return this; }, each() { return this; },
    };
    function jquery(selector) {
        if (typeof selector === "function") { selector(jquery); return; }
        return empty;
    }
    vm.runInNewContext(fs.readFileSync(path.join(__dirname,
        "../../../themes/buddyboss-theme-child-1.0.0/assets/js/custom.js"), "utf8"), {
        jQuery: jquery,
        document: {
            querySelector(selector) { return selector.endsWith("#skills-vault") ? vault : socialCards; },
            querySelectorAll() { return []; },
        },
        window: { matchMedia(query) { assert.equal(query, "(max-width: 799px)"); return media; } },
    });
    assert.deepEqual(portal.children.map(node => node.id),
        ["courses", "challenges", "move-makers", "routines", "pond-dashboard-social", "skills-vault"]);
    assert.deepEqual(courses.children, [profile]);
    for (const mobile of [false, true, true, false]) {
        media.matches = mobile;
        media.change();
        assert.equal(socialCards.parentNode, mobile ? portal : courses);
        assert.equal((mobile ? portal : courses).children.filter(node => node === socialCards).length, 1);
        assert.deepEqual(socialCards.children, [community, facebook]);
        assert.equal(courses.children.at(-1), profile);
        assert.equal(portal.children.at(-1), vault);
    }
    assert.deepEqual(courses.children, [socialCards, profile]);
});

test("dashboard without accessible social cards does not move an empty padded wrapper", () => {
    const empty = {
        length: 0, closest() { return this; }, find() { return this; },
        on() { return this; }, each() { return this; },
    };
    function jquery(selector) {
        if (typeof selector === "function") { selector(jquery); return; }
        return empty;
    }
    vm.runInNewContext(fs.readFileSync(path.join(__dirname,
        "../../../themes/buddyboss-theme-child-1.0.0/assets/js/custom.js"), "utf8"), {
        jQuery: jquery,
        document: {
            querySelector() { return { children: [] }; },
            querySelectorAll() { return []; },
        },
        window: { matchMedia() { assert.fail("Empty social wrapper should stay in place"); } },
    });
});
