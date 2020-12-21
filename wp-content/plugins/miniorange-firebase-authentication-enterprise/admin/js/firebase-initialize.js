
$fb_data = firebase_initialize_data;
var firebaseConfig = {};

//jQuery(document).ready(function() {
	var a_key            = $fb_data["api_key"];
	var pid              = $fb_data["project_id"];
	firebaseConfig    = {
	    apiKey: a_key,
	    authDomain: pid+'.firebaseapp.com',
	    databaseURL: 'https://'+pid+'.firebaseio.com',
	    projectId: pid,
	    storageBucket: ''
    };
//});
if (!firebase.apps.length) {
    firebase.initializeApp(firebaseConfig);
}
