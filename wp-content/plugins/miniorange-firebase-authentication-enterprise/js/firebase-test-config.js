var pid               = firebase_data_testconfig["project_id"];
var a_key             = firebase_data_testconfig["api_key"];
var email             = firebase_data_testconfig["test_username"];
var pass              = firebase_data_testconfig["test_password"];
var test_check_field  = firebase_data_testconfig["test_check_field"];
var provider_method   = "";
$data                 = firebase_data_testconfig;
document.documentElement.innerHTML = 'Please wait...';
jQuery(document).ready(mo_firebase_auth_firebaseAuthentication( pid, a_key, email, pass, test_check_field ) );