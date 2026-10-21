function setCookie(name, value, days) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + days * 86400000);
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "") + expires + "; path=/";
}
function getCookie(name) {
    var prefix = name + "=";
    var cookies = document.cookie.split(";");
    for (var i = 0; i < cookies.length; i++) {
        var value = cookies[i].trim();
        if (value.indexOf(prefix) === 0) return value.substring(prefix.length);
    }
    return null;
}
function eraseCookie(name) {
    document.cookie = name + "=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;";
}

jQuery(function ($) {
    document.querySelectorAll(".pond-support-faq .c-accordion-item-q").forEach(function (button) {
        button.addEventListener("click", function () {
            var panel = document.getElementById(button.getAttribute("aria-controls"));
            var isExpanded = button.getAttribute("aria-expanded") === "true";
            if (!panel) {
                console.error("Support FAQ panel not found:", button.getAttribute("aria-controls"));
                return;
            }

            button.setAttribute("aria-expanded", String(!isExpanded));
            if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
                panel.style.height = isExpanded ? "0px" : "auto";
                return;
            }
            if (isExpanded) {
                panel.style.height = panel.scrollHeight + "px";
                window.requestAnimationFrame(function () {
                    panel.style.height = "0px";
                });
            } else {
                panel.style.height = panel.scrollHeight + "px";
            }
        });
    });
    document.querySelectorAll(".pond-support-faq .c-accordion-item-a").forEach(function (panel) {
        panel.addEventListener("transitionend", function (event) {
            if (event.propertyName === "height" &&
                panel.previousElementSibling &&
                panel.previousElementSibling.getAttribute("aria-expanded") === "true") {
                panel.style.height = "auto";
            }
        });
    });

    var facebook = document.querySelector(".page-template-member-dashboard #facebook-secret-phrase");
    var skillsVault = document.querySelector(".page-template-member-dashboard #skills-vault");
    if (facebook && skillsVault) {
        var originalParent = facebook.parentNode;
        var originalNext = facebook.nextSibling;
        var mobileDashboard = window.matchMedia("(max-width: 799px)");
        function placeFacebookCard() {
            if (mobileDashboard.matches) {
                skillsVault.parentNode.insertBefore(facebook, skillsVault);
            } else {
                originalParent.insertBefore(facebook, originalNext);
            }
        }
        placeFacebookCard();
        mobileDashboard.addEventListener("change", placeFacebookCard);
    }

    var $login = $("#firebase-login-page #mepr_loginform");
    var $loginActions = $login.closest(".mp_wrapper").find(".mepr-login-actions");
    if ($login.length === 1 && $loginActions.length === 1) {
        var $remember = $login.find("#rememberme").closest("label").parent("div");
        if ($remember.length === 1) {
            $remember.addClass("pond-login-options").append($loginActions);
        } else {
            $loginActions.insertBefore($login.find(".submit"));
        }
    }
    $(document).on("click", 'a[href*="action=logout"]', function (event) {
        var url = $(this).attr("href");
        if (url && url.indexOf("action=logout") !== -1) {
            event.preventDefault();
            if (typeof firebase !== "undefined" && firebase.apps.length) {
                firebase.auth().signOut().then(function () {
                    eraseCookie("fb_user");
                    window.location.href = url;
                }).catch(function (error) {
                    console.error(error);
                    alert("Sign out failed. Please try again.");
                });
            } else {
                eraseCookie("fb_user");
                window.location.href = url;
            }
        }
    });
    $(document).on("click", 'a[href="#search"]', function (event) {
        event.preventDefault();
        $("#pond-search").prop("hidden", false);
        $("#pond-search-input").trigger("focus");
    });
    $(".pond-search-close").on("click", function () {
        $("#pond-search").prop("hidden", true);
    });
    $(document).on("keydown", function (event) {
        if (event.key === "Escape") $("#pond-search").prop("hidden", true);
    });
    $("#savedContentTabList .filter_option").on("click", function () {
        $("#savedContentTabList .filter_option").removeClass("active");
        $(".filtered_content").removeClass("active");
        $(this).addClass("active");
        $("." + $(this).data("tab")).addClass("active");
    });
    $(document).on("click", ".lesson_tool", function (event) {
        event.preventDefault();
        var $button = $(this);
        if ($button.attr("aria-busy") === "true") return;
        $button.attr("aria-busy", "true");
        $.ajax({
            url: pondPortal.ajaxUrl, method: "POST",
            data: {
                action: "track_lesson_ajax", nonce: pondPortal.trackerNonce,
                lesson_id: $button.data("lesson-id"), track_type: $button.data("track-type")
            }
        }).done(function (response) {
            if (!response.success) {
                alert(response.data.message);
                return;
            }
            window.location.reload();
        }).fail(function (xhr) {
            alert(xhr.responseJSON && xhr.responseJSON.data ? xhr.responseJSON.data.message : "Your saved content could not be updated. Please try again.");
        }).always(function () {
            $button.removeAttr("aria-busy");
        });
        $(document).on("keydown", ".lesson_tool", function (event) {
            if (event.key === "Enter" || event.key === " ") {
                event.preventDefault();
                $(this).trigger("click");
            }
        });
    });
    $(".pond-jump-links").each(function () {
        var $links = $(this);
        var iframe = $links.closest(".pond-course-material, .pond-portal").find('iframe[src*="player.vimeo.com"]').get(0);
        if (!iframe || typeof Vimeo === "undefined") {
            $links.find("button").prop("disabled", true);
            $links.find(".pond-jump-error").text("Video jump links require a Vimeo player.");
            return;
        }
        var player = new Vimeo.Player(iframe);
        $links.find("button").on("click", function () {
            player.setCurrentTime(Number($(this).data("jumptime"))).then(function () {
                return player.play();
            }).catch(function (error) {
                console.error(error);
                $links.find(".pond-jump-error").text("Unable to jump to that time. Please use the video controls.");
            });
        });
    });
});