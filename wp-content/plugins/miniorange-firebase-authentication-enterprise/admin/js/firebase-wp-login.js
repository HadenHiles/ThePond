
$fb_data = firebase_data;

jQuery(document).ready(function() {
	var enable_firebase_login = $fb_data["enable_firebase_login"];
	var disable_wp_login      = $fb_data["disable_wp_login"];
	var enable_admin_wp_login = $fb_data["enable_admin_wp_login"];
	var a_key            = $fb_data["api_key"];
	var pid              = $fb_data["project_id"];
	var firebaseConfig    = {
	    apiKey: a_key,
	    authDomain: pid+'.firebaseapp.com',
	    databaseURL: 'https://'+pid+'.firebaseio.com',
	    projectId: pid,
	    storageBucket: ''
    };

    // Initialize Firebase
    if (!firebase.apps.length) {
    	firebase.initializeApp(firebaseConfig);
	}

	jQuery("#mo_firebase_Google_provider_login").on("click", function(event) {
		event.preventDefault();
		mo_firebase_auth_do_fb_provider_login("google");
	});
	jQuery("#mo_firebase_Facebook_provider_login").on("click", function(event) {
		event.preventDefault();
		mo_firebase_auth_do_fb_provider_login("facebook");
	});
	jQuery("#mo_firebase_Github_provider_login").on("click", function(event) {
		event.preventDefault();
		mo_firebase_auth_do_fb_provider_login("github");
	});
	jQuery("#mo_firebase_Twitter_provider_login").on("click", function(event) {
		event.preventDefault();
		mo_firebase_auth_do_fb_provider_login("twitter");
	});
	jQuery("#mo_firebase_Microsoft_provider_login").on("click", function(event) {
		event.preventDefault();
		mo_firebase_auth_do_fb_provider_login("microsoft");
	});
	jQuery("#mo_firebase_Yahoo_provider_login").on("click", function(event) {
		event.preventDefault();
		mo_firebase_auth_do_fb_provider_login("yahoo");
	});
	jQuery("#mo_firebase_Yahoo_provider_login").on("click", function(event) {
		event.preventDefault();
		mo_firebase_auth_do_fb_provider_login("yahoo");
	});
	jQuery("#mo_firebase_Apple_provider_login").on("click", function(event) {
		event.preventDefault();
		mo_firebase_auth_do_fb_provider_login("apple");
	});
	jQuery('#mepr_loginform #wp-submit').on("click", (e) => {
		e.preventDefault();
		mo_firebase_auth_do_fb_login();
	});

	var url = window.location.href;
	url = new URL(url);
	var action = url.searchParams.get("action");
	var submitted = url.searchParams.get("submit");
	if (action == "mooauthfirebaselogin" && submitted == "true") {
		jQuery('#fbErrorMsg').remove();
		jQuery('<p id="fbErrorMsg" style="color: #cc3333;">Invalid email/password combination.</p>').insertBefore('#wp-submit');
	}

	if( ( jQuery("#login_error").text().length === 0 || enable_firebase_login == 0 ) && ((!disable_wp_login) ||(disable_wp_login && enable_admin_wp_login) )) {
		return;
	}
	if( disable_wp_login && !enable_admin_wp_login ) {
		jQuery("#wp-submit").on("click", function(event) {
			event.preventDefault();
			mo_firebase_auth_do_fb_login();
		});
	} else if( ( jQuery("#login_error").text().length > 0 && enable_firebase_login ) ) {
		mo_firebase_auth_do_fb_login();
	}
	
});

function mo_firebase_auth_do_fb_login() {
	var a_key            = $fb_data["api_key"];
	var pid              = $fb_data["project_id"];
	var email            = document.getElementById("user_login").value;
	var pass             = document.getElementById("user_pass").value;
	
	if( email.length === 0 ) {
		email = $fb_data['log'];
	}
	if( pass.length === 0 ) {
		pass = $fb_data['pwd'];
	}
	if(typeof(email) !== 'undefined' && typeof(pass) !== 'undefined'){
		if( email.length > 0 && pass.length > 0 ) {
			jQuery("#login_error").remove();
			document.body.innerHTML = "Please wait...";
		}
	} else {
		jQuery("#login_error").remove();
		return;
	}
	mo_firebase_auth_firebaseAuthentication( pid, a_key, email, pass, "" );
}

function mo_firebase_auth_do_fb_provider_login(provider_method) {
	var a_key            = $fb_data["api_key"];
	var pid              = $fb_data["project_id"];
	mo_firebase_auth_provider_firebaseAuthentication( provider_method, pid, a_key, "" );
}