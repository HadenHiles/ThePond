(function () {
    var skills = document.querySelector(".pond-skills");
    var puck = document.querySelector("[data-pond-puck]");
    var reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
    var pending = false;

    function updatePuck() {
        pending = false;
        var rect = skills.getBoundingClientRect();
        puck.hidden = reducedMotion.matches || window.innerWidth < 768 ||
            rect.top >= window.innerHeight || rect.bottom <= 0;
        if (puck.hidden) {
            return;
        }

        var progress = Math.max(0, Math.min(1,
            (window.innerHeight - rect.top) / (window.innerHeight + rect.height)));
        var arc = Math.sin(progress * Math.PI);
        var x = progress * Math.min(window.innerWidth * 0.6, 700);
        var y = -arc * Math.min(window.innerHeight * 0.5, 400);
        var scale = 0.7 + arc * 0.3;
        puck.style.transform = "translate3d(" + x + "px," + y + "px,0) rotate(" +
            progress * 720 + "deg) scale(" + scale + ")";
    }

    function queueUpdate() {
        if (!pending) {
            pending = true;
            window.requestAnimationFrame(updatePuck);
        }
    }

    window.addEventListener("scroll", queueUpdate, { passive: true });
    window.addEventListener("resize", queueUpdate);
    reducedMotion.addEventListener("change", queueUpdate);
    updatePuck();

    new IntersectionObserver(function (entries) {
        var belowIntro = entries[0].boundingClientRect.bottom <= 0;
        document.querySelectorAll(".bottle, .target").forEach(function (image) {
            image.hidden = belowIntro;
        });
    }).observe(skills);

    document.querySelectorAll(".c-accordion-item-q").forEach(function (button) {
        button.addEventListener("click", function () {
            button.setAttribute("aria-expanded", button.getAttribute("aria-expanded") === "true" ? "false" : "true");
        });
    });
})();
