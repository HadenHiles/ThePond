jQuery(function ($) {
    document.querySelectorAll(".mepr-signup-form").forEach(function (form, index) {
        var input = form.querySelector(".mepr-password");
        var meter = form.querySelector(".mp-password-strength-display");
        var score = form.querySelector(".mp-pass-strength");
        if (!input || !meter || !score) return;

        var area = meter.closest(".mp-password-strength-area");
        var guidance = document.createElement("p");
        guidance.id = "pond-password-guidance-" + index;
        guidance.className = "pond-password-guidance";
        guidance.textContent = pondSignupPassword.guidance;
        var status = document.createElement("p");
        status.id = "pond-password-status-" + index;
        status.className = "pond-password-status";
        status.setAttribute("role", "status");
        status.setAttribute("aria-live", "polite");
        status.setAttribute("aria-atomic", "true");
        area.appendChild(guidance);
        area.appendChild(status);
        input.setAttribute("aria-describedby", [input.getAttribute("aria-describedby"), guidance.id, status.id].filter(Boolean).join(" "));

        function updateStatus() {
            var state = "pending";
            var message = pondSignupPassword.checking;
            if (!input.value) {
                state = "empty";
                message = pondSignupPassword.empty;
            } else if (Array.from(input.value).length < pondSignupPassword.minLength) {
                state = "rejected";
                message = pondSignupPassword.tooShort;
            } else if (typeof window.zxcvbn === "function") {
                var accepted = Number(score.value) >= pondSignupPassword.requiredScore;
                state = accepted ? "accepted" : "rejected";
                message = accepted ? pondSignupPassword.accepted : pondSignupPassword.tooWeak;
            }
            area.setAttribute("data-pond-password-state", state);
            if (status.textContent !== message) status.textContent = message;
        }

        // MemberPress writes the hidden score after changing the visible meter.
        // Observe the meter so our verdict runs after both writes, including async loading.
        new MutationObserver(updateStatus).observe(meter, { childList: true, characterData: true, subtree: true });
        input.addEventListener("input", function () {
            if (typeof window.zxcvbn === "function") {
                window.mepr_score_it($(input));
            }
            updateStatus();
        });
        updateStatus();
    });
});
