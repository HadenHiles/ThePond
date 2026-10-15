const assert = require("node:assert/strict");
const fs = require("node:fs");
const path = require("node:path");
const vm = require("node:vm");
const { test } = require("node:test");

const source = fs.readFileSync(path.join(__dirname,
    "../../../themes/buddyboss-theme-child-1.0.0/assets/landing/landing.js"), "utf8");

function fixture({ width = 1440, reduced = false, top = 1200 } = {}) {
    const puck = { hidden: true, style: {} };
    const rect = { top, bottom: top + 1000, height: 1000 };
    const skills = { getBoundingClientRect: () => rect };
    const listeners = {};
    const frames = [];
    const decorations = [{ hidden: false }, { hidden: false }];
    const button = {
        expanded: "false",
        getAttribute() { return this.expanded; },
        setAttribute(name, value) { this.expanded = value; },
        addEventListener(name, callback) { this.click = callback; },
    };
    const motion = { matches: reduced, addEventListener(name, callback) { this.change = callback; } };
    let observer;
    const window = {
        innerWidth: width, innerHeight: 1000,
        matchMedia: () => motion,
        addEventListener: (name, callback) => { listeners[name] = callback; },
        requestAnimationFrame: callback => frames.push(callback),
    };
    vm.runInNewContext(source, {
        window,
        document: {
            querySelector: selector => selector === ".pond-skills" ? skills : puck,
            querySelectorAll: selector => selector === ".bottle, .target" ? decorations : [button],
        },
        IntersectionObserver: class {
            constructor(callback) { observer = callback; }
            observe(element) { assert.equal(element, skills); }
        },
    });
    function scroll(top) {
        rect.top = top;
        rect.bottom = top + rect.height;
        listeners.scroll();
        frames.shift()();
    }
    return { puck, rect, scroll, listeners, frames, window, motion, decorations, button,
        observe: bottom => observer([{ boundingClientRect: { bottom } }]) };
}

test("puck moves visibly within the skills section and disappears before lower content", () => {
    const f = fixture();
    assert.equal(f.puck.hidden, true);
    f.scroll(500);
    assert.equal(f.puck.hidden, false);
    const first = f.puck.style.transform;
    f.scroll(0);
    assert.equal(f.puck.hidden, false);
    assert.notEqual(f.puck.style.transform, first);
    assert.match(f.puck.style.transform, /translate3d\(350px,-400px,0\) rotate\(360deg\) scale\(1\)/);
    f.scroll(-1000);
    assert.equal(f.puck.hidden, true);
    f.scroll(500);
    assert.equal(f.puck.hidden, false);
    assert.equal(f.puck.style.transform, first);
});

test("mobile and reduced-motion preferences disable the puck and update live", () => {
    for (const options of [{ width: 390 }, { reduced: true }]) {
        const f = fixture({ ...options, top: 0 });
        assert.equal(f.puck.hidden, true);
    }
    const f = fixture({ top: 0 });
    f.motion.matches = true;
    f.motion.change();
    f.frames.shift()();
    assert.equal(f.puck.hidden, true);
    f.motion.matches = false;
    f.window.innerWidth = 390;
    f.listeners.resize();
    f.frames.shift()();
    assert.equal(f.puck.hidden, true);
    f.window.innerWidth = 1440;
    f.listeners.resize();
    f.frames.shift()();
    assert.equal(f.puck.hidden, false);
});

test("scroll updates are coalesced and existing decoration and FAQ behavior remains", () => {
    const f = fixture();
    f.listeners.scroll();
    f.listeners.scroll();
    assert.equal(f.frames.length, 1);
    f.frames.shift()();
    f.observe(-1);
    assert.ok(f.decorations.every(image => image.hidden));
    f.observe(100);
    assert.ok(f.decorations.every(image => !image.hidden));
    f.button.click();
    assert.equal(f.button.expanded, "true");
    f.button.click();
    assert.equal(f.button.expanded, "false");
});
