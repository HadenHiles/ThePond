function mo_firebase_auth_firebaseAuthentication(pid, a_key, email, pass, test_check_field) {
	if (email.length === 0 || pass.length === 0) {
		console.log("Email or Password is empty.");
		return;
	}

	var re = new RegExp(/^.*\//);
	var url = re.exec(window.location.href);
	var createform = document.createElement('form');
	if (test_check_field == 'woocommerce') {
		createform.setAttribute("action", url);
	}
	else {
		createform.setAttribute("action", url + '?action=mooauthfirebaselogin&submit=true');
	}
	createform.setAttribute("method", "post");
	createform.setAttribute("name", "jwtform");
	createform.setAttribute("id", "jwtform");

	var inputelement = document.createElement('input'); // Create Input Field for Name
	inputelement.setAttribute("type", "hidden");
	inputelement.setAttribute("name", "fb_jwt");
	inputelement.setAttribute("id", "fb_jwt");
	createform.appendChild(inputelement);
	var inputelement = document.createElement('input'); // Create Input Field for Name
	inputelement.setAttribute("type", "hidden");
	inputelement.setAttribute("name", "fb_is_test");
	inputelement.setAttribute("id", "fb_is_test");
	createform.appendChild(inputelement);
	var inputelement = document.createElement('input'); // Create Input Field for Name
	inputelement.setAttribute("type", "hidden");
	inputelement.setAttribute("name", "fb_error_msg");
	inputelement.setAttribute("id", "fb_error_msg");
	createform.appendChild(inputelement);
	var inputelement = document.createElement('input'); // Create Input Field for Name
	inputelement.setAttribute("type", "hidden");
	inputelement.setAttribute("name", "wp_email");
	inputelement.setAttribute("id", "wp_email");
	createform.appendChild(inputelement);
	var inputelement = document.createElement('input'); // Create Input Field for Name
	inputelement.setAttribute("type", "hidden");
	inputelement.setAttribute("name", "wp_secret");
	inputelement.setAttribute("id", "wp_secret");
	createform.appendChild(inputelement);

	document.body.appendChild(createform);

	var firebaseConfig = {
		apiKey: a_key,
		authDomain: pid + '.firebaseapp.com',
		databaseURL: 'https://' + pid + '.firebaseio.com',
		projectId: pid,
		storageBucket: ''
	};

	// Initialize Firebase
	if (!firebase.apps.length) {
		firebase.initializeApp(firebaseConfig);
	}
	// firebase.initializeApp(firebaseConfig);
	firebase.auth().signInWithEmailAndPassword(email, pass)
		.then(function (firebaseUser) {
			setCookie("fb_user", JSON.stringify(firebaseUser), 90);

			if (test_check_field == 'test_check_true') {
				document.getElementById('fb_is_test').value = 'test_check_true';
			}
			document.getElementById('fb_jwt').value = firebaseUser['user']['_lat'];
			document.forms['jwtform'].submit();
		})
		.catch(function (error) {
			// Error Handling
			if (test_check_field == 'test_check_true') {
				document.getElementById('fb_is_test').value = 'test_check_true';
			}
			if (test_check_field == 'woocommerce') {
				document.getElementById('fb_is_test').value = 'woocommerce_error';
				document.getElementById('wp_email').value = email;
				document.getElementById('wp_secret').value = pass;
			}
			document.getElementById('fb_jwt').value = 'empty_string';
			document.getElementById('fb_error_msg').value = error.message;
			document.forms['jwtform'].submit();
			var errorCode = error.code;
			var errorMessage = error.message;
		});
}

function mo_firebase_auth_createFirebaseUser(pid, a_key, email, pass, test_check_field) {
	var createform = document.createElement('form');
	createform.setAttribute("method", "post");
	createform.setAttribute("name", "wcErrorform");
	createform.setAttribute("id", "wcErrorform");

	var inputelement = document.createElement('input'); // Create Input Field for Name
	inputelement.setAttribute("type", "hidden");
	inputelement.setAttribute("name", "wc_email");
	inputelement.setAttribute("id", "wc_email");
	createform.appendChild(inputelement);
	var inputelement = document.createElement('input'); // Create Input Field for Name
	inputelement.setAttribute("type", "hidden");
	inputelement.setAttribute("name", "wc_error_msg");
	inputelement.setAttribute("id", "wc_error_msg");
	createform.appendChild(inputelement);

	document.body.appendChild(createform);

	var firebaseConfig = {
		apiKey: a_key,
		authDomain: pid + '.firebaseapp.com',
		databaseURL: 'https://' + pid + '.firebaseio.com',
		projectId: pid,
		storageBucket: ''
	};

	// Initialize Firebase
	if (!firebase.apps.length) {
		firebase.initializeApp(firebaseConfig);
	}

	firebase.auth().createUserWithEmailAndPassword(email, pass)
		.then(function (firebaseUser) {
			//document.getElementById('wc_email').value = email;
			//document.forms['wcErrorform'].submit();
			setCookie("fb_user", JSON.stringify(firebaseUser), 90);
			mo_firebase_auth_firebaseAuthentication(pid, a_key, email, pass, test_check_field);
		})
		.catch(function (error) {
			document.getElementById('wc_error_msg').value = error.message;
			document.forms['wcErrorform'].submit();
		});

}

function mo_firebase_auth_resetPassword(pid, a_key, email) {
	var createform = document.createElement('form');
	createform.setAttribute("method", "post");
	createform.setAttribute("name", "wcResetform");
	createform.setAttribute("id", "wcResetform");

	var inputelement = document.createElement('input'); // Create Input Field for Name
	inputelement.setAttribute("type", "hidden");
	inputelement.setAttribute("name", "wc_success_msg");
	inputelement.setAttribute("id", "wc_success_msg");
	createform.appendChild(inputelement);
	var inputelement = document.createElement('input'); // Create Input Field for Name
	inputelement.setAttribute("type", "hidden");
	inputelement.setAttribute("name", "wc_error_msg");
	inputelement.setAttribute("id", "wc_error_msg");
	createform.appendChild(inputelement);

	document.body.appendChild(createform);

	var firebaseConfig = {
		apiKey: a_key,
		authDomain: pid + '.firebaseapp.com',
		databaseURL: 'https://' + pid + '.firebaseio.com',
		projectId: pid,
		storageBucket: ''
	};

	// Initialize Firebase
	if (!firebase.apps.length) {
		firebase.initializeApp(firebaseConfig);
	}

	firebase.auth().sendPasswordResetEmail(email).then(function () {
		document.getElementById('wc_success_msg').value = "Password reset email has been sent.";
		document.forms['wcResetform'].submit();
	}).catch(function (error) {
		document.getElementById('wc_error_msg').value = error.message;
		document.forms['wcResetform'].submit();
	});


}

function mo_firebase_auth_provider_firebaseAuthentication(provider_method, pid, a_key, test_check_field) {
	//alert(provider_method);
	/*if( email.length === 0 || pass.length === 0 ) {
		console.log("Email or Password is empty.");
		return;	
	}*/

	if (isMobileAppBrowser()) {
		window.location.href = "/open-in-web-browser";
	} else {
		var re = new RegExp(/^.*\//);
		var url = re.exec(window.location.href);
		var createform = document.createElement('form');
		createform.setAttribute("action", '');
		createform.setAttribute("method", "post");
		createform.setAttribute("name", "jwtform");
		createform.setAttribute("id", "jwtform");

		var inputelement = document.createElement('input'); // Create Input Field for Name
		inputelement.setAttribute("type", "hidden");
		inputelement.setAttribute("name", "fb_jwt");
		inputelement.setAttribute("id", "fb_jwt");
		createform.appendChild(inputelement);
		var inputelement = document.createElement('input'); // Create Input Field for Name
		inputelement.setAttribute("type", "hidden");
		inputelement.setAttribute("name", "fb_user");
		inputelement.setAttribute("id", "fb_user");
		createform.appendChild(inputelement);
		var inputelement = document.createElement('input'); // Create Input Field for Name
		inputelement.setAttribute("type", "hidden");
		inputelement.setAttribute("name", "fb_is_test");
		inputelement.setAttribute("id", "fb_is_test");
		createform.appendChild(inputelement);
		var inputelement = document.createElement('input'); // Create Input Field for Name
		inputelement.setAttribute("type", "hidden");
		inputelement.setAttribute("name", "fb_error_msg");
		inputelement.setAttribute("id", "fb_error_msg");
		createform.appendChild(inputelement);

		document.body.appendChild(createform);

		var firebaseConfig = {
			apiKey: a_key,
			authDomain: pid + '.firebaseapp.com',
			databaseURL: 'https://' + pid + '.firebaseio.com',
			projectId: pid,
			storageBucket: ''
		};

		// Initialize Firebase
		if (!firebase.apps.length) {
			firebase.initializeApp(firebaseConfig);
		}
		if (provider_method == "google") {
			var provider = new firebase.auth.GoogleAuthProvider();
		} else if (provider_method == "facebook") {
			var provider = new firebase.auth.FacebookAuthProvider();
		} else if (provider_method == "github") {
			var provider = new firebase.auth.GithubAuthProvider();
		} else if (provider_method == "twitter") {
			var provider = new firebase.auth.TwitterAuthProvider();
		} else if (provider_method == "microsoft") {
			var provider = new firebase.auth.OAuthProvider('microsoft.com');
		} else if (provider_method == "yahoo") {
			var provider = new firebase.auth.OAuthProvider('yahoo.com');
		} else if (provider_method == "apple") {
			var provider = new firebase.auth.OAuthProvider('apple.com');
		}

		//firebase.auth().signInWithRedirect(provider);

		firebase.auth().signInWithPopup(provider)
			.then(function (result) {
				// This gives you a Google Access Token. You can use it to access the Google API.
				var token = result.credential.accessToken;
				// The signed-in user info.
				var user = result.user;

				setCookie("fb_user", JSON.stringify(user), 90);

				if (test_check_field == 'test_check_true') {
					document.getElementById('fb_is_test').value = 'test_check_true';
				}
				document.getElementById('fb_jwt').value = user['_lat'];
				document.getElementById('fb_user').value = JSON.stringify(user, null, 4);

				// If the user is trying to login - Check if the email exists or not and prompt the user before automatically creating a new account
				var promptCreateAccount = false;
				if (jQuery('#firebase-login-page').length == 1) {
					var data = {
						action: 'user_email_exists',
						email: user.email,
					};
			
					jQuery.ajax({
						url: "/wp-admin/admin-ajax.php",
						type: 'POST',
						data: data,
						success: function (response) {
							if (!response.exists) {
								promptCreateAccount = true;
								
								jQuery('#provider-email').text(user.email);
								jQuery("#confirm-create-account-modal").modal({
									backdrop: true
								}).modal('toggle');

								jQuery('#continue-create-account').click((e) => {
									e.preventDefault();
									document.forms['jwtform'].submit();
								});
							} else {
								document.forms['jwtform'].submit();
							}
						},
						error: function(jqXHR, textStatus, errorThrown) {
							console.error('Ajax server error for endpoint "user_email_exists": ' + textStatus + ': ' + errorThrown);
							document.forms['jwtform'].submit();
						}
					});
				} else {
					document.forms['jwtform'].submit();
				}
			})
			.catch(function (error) {
				// Handle Errors here.
				if (test_check_field == 'test_check_true') {
					document.getElementById('fb_is_test').value = 'test_check_true';
				}
				document.getElementById('fb_jwt').value = 'empty_string';
				document.getElementById('fb_error_msg').value = error.message;

				var $errContainer = jQuery('#login-error');
				if ($errContainer.length == 1) {
					console.log(error.code + ": " + error.message);
					$errContainer.html(error.message);
					$errContainer.show();
				} else {
					$errContainer.hide();
					document.forms['jwtform'].submit();
				}
			});
	}
}

function isMobileAppBrowser() {
	var ua = navigator.userAgent || navigator.vendor || window.opera;
	return (ua.indexOf("FBAN") > -1) || (ua.indexOf("FBAV") > -1 || ua.indexOf('Instagram') > -1);
}