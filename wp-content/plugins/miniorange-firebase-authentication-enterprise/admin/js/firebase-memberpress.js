$fb_data = firebase_data_memberpress;

jQuery(document).ready(function() {
	var a_key                                      = $fb_data["api_key"];
	var pid                                        = $fb_data["project_id"];
	var mo_firebase_auth_woocommerce_intigration   = $fb_data["mo_firebase_auth_woocommerce_intigration"];
	var mo_enable_firebase_auto_register = $fb_data["mo_enable_firebase_auto_register"];

	// if (jQuery("#user_email1").length == 1) {
	// 	jQuery(".mepr-submit").click(function() {
	// 		//event.preventDefault();
	// 		//event.stopPropagation();
	// 		mo_firebase_auth_create_fb_user(a_key, pid);
	// 	});
	// }

});

function mo_firebase_auth_create_fb_user(a_key, pid) {
	var email            = document.getElementById("user_email1").value;
	var pass             = document.getElementById("mepr_user_password1").value;
	var confirm_pass     = document.getElementById("mepr_user_password_confirm1").value;
	if( pass == confirm_pass ) {
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
		
		firebase.auth().createUserWithEmailAndPassword( email, pass )
	            .then(function (firebaseUser) {
					setCookie("fb_user", JSON.stringify(firebaseUser), 90);
	            })
	            .catch(function (error) {
	            });
    }
}