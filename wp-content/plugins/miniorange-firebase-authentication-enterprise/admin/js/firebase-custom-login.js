
$fb_data = firebase_data_custom;

jQuery(document).ready(function() {

	var a_key     = $fb_data["api_key"];
	var pid       = $fb_data["project_id"];
	var username_id  = $fb_data["username_id"];
	var pass_id   = $fb_data["pass_id"];
	var submit_id = $fb_data["submit_id"];
//	var mo_firebase_auto_register_user_in_firebase = $fb_data["mo_firebase_auto_register_user_in_firebase"];
	var mo_enable_firebase_auto_register = $fb_data["mo_enable_firebase_auto_register"];

	if (typeof firebase_data_custom.registration !== 'undefined' && mo_enable_firebase_auto_register == 1) {
		if ( document.getElementById(submit_id) !== null ) {
			var Registrationform = document.getElementById(submit_id).getElementsByTagName("form");
			jQuery( Registrationform ).submit(function( event ) {
	  			event.preventDefault();
	  			var email            = document.getElementById(username_id).value;
				var pass             = document.getElementById(pass_id).value;
	  			mo_firebase_auth_createFirebaseUser( pid, a_key, email, pass, "" );
			});
		}
	} else{
		if ( document.getElementById(submit_id) !== null ) {
			var Loginform = document.getElementById(submit_id).getElementsByTagName("form");
			jQuery( Loginform ).submit(function( event ) {
	  			event.preventDefault();
	  			var email            = document.getElementById(username_id).value;
				var pass             = document.getElementById(pass_id).value;
	  			mo_firebase_auth_firebaseAuthentication( pid, a_key, email, pass, "" );
			});
		}
	}
	
