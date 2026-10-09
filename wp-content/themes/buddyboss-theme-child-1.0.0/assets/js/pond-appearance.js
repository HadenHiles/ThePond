(function () {
    "use strict";
    var root = document.documentElement;
    var config = pondAppearance;

    function savedTheme() {
        var cookie = document.cookie.split(";").find(function (value) {
            return value.trim().indexOf("bbtheme=") === 0;
        });
        return cookie && cookie.trim() === "bbtheme=dark" ? "dark" : "light";
    }

    function actionText(color) {
        var channels = color.replace("#", "");
        if (channels.length === 3) {
            channels = channels.split("").map(function (value) { return value + value; }).join("");
        }
        var luminance = [0, 2, 4].map(function (offset) {
            var channel = parseInt(channels.slice(offset, offset + 2), 16) / 255;
            return channel <= 0.04045 ? channel / 12.92 : Math.pow((channel + 0.055) / 1.055, 2.4);
        });
        var lightness = luminance[0] * 0.2126 + luminance[1] * 0.7152 + luminance[2] * 0.0722;
        return (lightness + 0.05) / 0.05 > 1.05 / (lightness + 0.05) ? "#000000" : "#ffffff";
    }

    function applyTheme(theme) {
        var color = theme === "dark" ? config.darkColor : config.lightColor;
        if (!/^#(?:[0-9a-f]{3}|[0-9a-f]{6})$/i.test(color)) {
            console.error("The Pond appearance: invalid saved ReadyLaunch color", color);
            return;
        }
        root.dataset.pondTheme = theme;
        root.style.setProperty("--pond-accent", color);
        root.style.setProperty("--pond-action-text", actionText(color));
        var hex = color.slice(1);
        if (hex.length === 3) hex = hex.split("").map(function (value) { return value + value; }).join("");
        root.style.setProperty("--pond-accent-rgb", [0, 2, 4].map(function (offset) {
            return parseInt(hex.slice(offset, offset + 2), 16);
        }).join(", "));
        if (document.body) document.body.classList.toggle("bb-dark-theme", theme === "dark");
        document.querySelectorAll("[data-pond-theme-toggle]").forEach(function (button) {
            var label = theme === "dark" ? config.lightLabel : config.darkLabel;
            button.setAttribute("aria-label", label);
            button.title = label;
            button.setAttribute("aria-pressed", String(theme === "dark"));
            button.firstElementChild.className = theme === "dark" ? "bb-icon-l bb-icon-sun" : "bb-icon-l bb-icon-moon";
        });
    }

    applyTheme(savedTheme());
    function setupToggles() {
        document.querySelectorAll("#header-aside .header-aside-inner, .bb-mobile-header > .header-aside").forEach(function (header) {
            var button = document.createElement("button");
            button.type = "button";
            button.className = "pond-theme-toggle";
            button.setAttribute("data-pond-theme-toggle", "");
            var icon = document.createElement("i");
            icon.setAttribute("aria-hidden", "true");
            button.appendChild(icon);
            header.prepend(button);
        });
        applyTheme(root.dataset.pondTheme || savedTheme());
    }
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", setupToggles);
    } else {
        setupToggles();
    }
    document.addEventListener("click", function (event) {
        var button = event.target.closest("[data-pond-theme-toggle], #bb-toggle-theme");
        if (!button) return;
        event.preventDefault();
        event.stopImmediatePropagation();
        var theme = root.dataset.pondTheme === "dark" ? "light" : "dark";
        document.cookie = "bbtheme=" + theme + "; Max-Age=31536000; Path=/; SameSite=Lax" +
            (location.protocol === "https:" ? "; Secure" : "");
        applyTheme(theme);
        if (savedTheme() !== theme) {
            console.error("The Pond appearance: preference cookie could not be saved");
            alert(config.saveError);
        }
    }, true);
}());
