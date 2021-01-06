(($) => {
    var count = getCookie("member_count") ?? 0;
    // var firstLoad = true;

    function flipTo(digit, n) {
        var current = digit.attr('data-num');
        digit.attr('data-num', n);
        digit.find('.front').attr('data-content', current);
        digit.find('.back, .under').attr('data-content', n);
        digit.find('.flap').css('display', 'block');
        setTimeout(function () {
            digit.find('.base').text(n);
            digit.find('.flap').css('display', 'none');
        }, 350);
    }

    function jumpTo(digit, n) {
        digit.attr('data-num', n);
        digit.find('.base').text(n);
    }

    function updateDigit(digit, n, flip) {
        var digit1 = $('.' + digit);
        n = String(n);
        if (digit1.attr('data-num') != n) {
            if (flip) {
                flipTo(digit1, n);
            } else {
                jumpTo(digit1, n);
            }
        }
    }

    function updateMemberCount(flip) {
        var prevCount = count;

        // Retrieve the count via ajax
        $.ajax({
            url: "./count.php", // this will point to admin-ajax.php
            type: 'GET',
            success: function (response) {
                if (!(response instanceof Object)) {
                    response = JSON.parse(response);
                }
                
                count = response.count != null ? response.count : 0;
                fullCount = count.toString().padStart(6, "0");

                updateDigit('six', fullCount.charAt(0), flip);
                updateDigit('five', fullCount.charAt(1), flip);
                updateDigit('four', fullCount.charAt(2), flip);
                updateDigit('three', fullCount.charAt(3), flip);
                updateDigit('two', fullCount.charAt(4), flip);
                updateDigit('one', fullCount.charAt(5), flip);

                if (count > prevCount) {
                    var audio = document.querySelector('#new-member-sound');
                    audio.play();
                }

                setCookie("member_count", count, 2)

                // firstLoad = false;
            },
            complete: function () {
                setTimeout(() => {
                    $('#valid-icon').html("Valid").hide();
                    $('#invalid-icon').hide();
                    $('#validate').attr('disabled', false).removeClass('disabled');
                }, 2000);
            }
        });
    }

    $(document).ready(function () {
        updateMemberCount(false);

        setInterval(function () {
            updateMemberCount(true);
        }, 60000);
    });
})(jQuery);

function setCookie(name, value, minutes) {
	var expires = "";
	if (minutes) {
		var date = new Date();
		date.setTime(date.getTime() + (minutes * 60 * 1000));
		expires = "; expires=" + date.toUTCString();
	}
	document.cookie = name + "=" + (value || "") + expires + "; path=/";
}
function getCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for (var i = 0; i < ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0) == ' ') c = c.substring(1, c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
	}
	return null;
}
function eraseCookie(name) {
	document.cookie = name + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
}