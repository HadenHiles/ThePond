<?php


require "\x63\157\x6e\x66\x69\x67\57\143\154\x61\x73\163\x2d\155\x6f\55\146\151\162\145\x62\141\x73\145\x2d\x61\165\164\x68\x65\156\164\x69\x63\141\x74\151\x6f\156\55\143\x6f\156\x66\151\x67\56\160\150\160";
require "\x63\x6f\156\146\151\x67\57\x63\154\141\x73\163\55\155\x6f\x2d\x66\151\x72\x65\x62\141\x73\145\x2d\x61\x75\164\150\145\x6e\x74\151\x63\141\164\x69\x6f\156\55\x61\x64\x76\x73\145\x74\x74\151\156\x67\163\56\x70\150\160";
require "\143\x6f\x6e\146\x69\147\x2f\143\154\141\x73\163\x2d\x6d\x6f\55\146\151\x72\145\x62\141\163\x65\55\x61\165\x74\150\x65\x6e\164\x69\x63\x61\164\x69\157\x6e\55\x6c\x6f\147\x69\x6e\x73\145\x74\x74\151\x6e\x67\163\56\x70\150\x70";
require "\143\x6f\156\x66\151\147\57\143\x6c\x61\163\163\x2d\x6d\157\x2d\146\151\162\145\x62\141\163\145\x2d\x61\x75\164\150\x65\x6e\164\x69\x63\x61\x74\151\157\156\x2d\x68\157\x6f\x6b\x73\x2e\160\x68\x70";
require "\x63\157\156\x66\x69\x67\57\x63\x6c\x61\163\x73\x2d\x6d\x6f\55\x66\x69\x72\145\142\x61\x73\145\55\141\x75\164\150\x65\156\x74\151\x63\x61\164\151\157\x6e\x2d\x6c\x69\x63\145\x6e\x73\151\156\147\137\160\x6c\x61\156\x73\x2e\160\150\160";
require "\163\x75\160\160\157\162\164\57\143\154\x61\163\163\55\155\x6f\55\x66\151\162\x65\142\141\x73\145\55\x61\x75\164\150\145\x6e\x74\151\143\141\x74\151\157\156\55\x66\141\161\56\160\150\160";
require "\x61\x63\x63\x6f\x75\x6e\164\x2f\143\154\x61\x73\x73\55\x6d\157\x2d\146\151\162\x65\x62\141\x73\145\55\x61\165\x74\150\145\x6e\164\x69\x63\x61\164\x69\x6f\156\x2d\x61\x63\x63\157\165\156\164\56\160\x68\x70";
function mo_firebase_authentication_main_menu()
{
    $HJ = '';
    if (!isset($_GET["\164\141\x62"])) {
        goto c4;
    }
    $HJ = $_GET["\164\141\x62"];
    c4:
    Mo_Firebase_Authentication_Admin_Menu::mo_firebase_auth_show_menu($HJ);
}
class Mo_Firebase_Authentication_Admin_Menu
{
    public static function mo_firebase_auth_show_menu($HJ)
    {
        ?>
		 <div style="margin-left:20px;overflow:hidden">
			<div class="wrap">
				<div class="wrap">
					<div><img style="float:left;" src="<?php 
        echo dirname(plugin_dir_url(__FILE__));
        ?>
/images/logo.png"></div>
				</div>
			       	<h1>
			            miniOrange Firebase Authentication&nbsp
			           	<!-- <a class="add-new-h2" href="https://forum.miniorange.com/" target="_blank">Ask questions on our forum</a>
						<a class="add-new-h2" href="https://faq.miniorange.com/" target="_blank">FAQ</a>	 -->
			       	</h1>
	       	</div>
	       	<br>

			<div class="row">
			<div class="col-md-8">

				<?php 
        ?>
	
				<ul class="row mo_firebase_authentication_nav">
					<a href="admin.php?page=mo_firebase_authentication_settings&tab=config"><li class="mo_firebase_authentication_nav_item <?php 
        if (!($HJ === '' || $HJ === "\143\x6f\x6e\x66\151\x67")) {
            goto bo;
        }
        echo "\141\143\164\x69\166\145";
        bo:
        ?>
">Configure</li></a>
					<a href="admin.php?page=mo_firebase_authentication_settings&tab=advsettings"><li  class="mo_firebase_authentication_nav_item <?php 
        if (!($HJ === "\x61\144\x76\163\x65\x74\164\151\156\147\x73")) {
            goto c_;
        }
        echo "\141\x63\164\x69\x76\145";
        c_:
        ?>
">Advanced Settings</li></a>
					<a href="admin.php?page=mo_firebase_authentication_settings&tab=loginsettings"><li  class="mo_firebase_authentication_nav_item <?php 
        if (!($HJ === "\x6c\157\x67\151\x6e\163\145\164\x74\151\x6e\x67\163")) {
            goto vP;
        }
        echo "\x61\x63\x74\x69\166\x65";
        vP:
        ?>
">Login Settings</li></a>
					<a href="admin.php?page=mo_firebase_authentication_settings&tab=hooks"><li  class="mo_firebase_authentication_nav_item <?php 
        if (!($HJ === "\x68\157\157\153\x73")) {
            goto J6;
        }
        echo "\x61\x63\164\x69\x76\145";
        J6:
        ?>
">Hooks</li></a>
					<a href="admin.php?page=mo_firebase_authentication_settings&tab=account"><li  class="mo_firebase_authentication_nav_item <?php 
        if (!($HJ === "\141\x63\143\x6f\165\x6e\164")) {
            goto wX;
        }
        echo "\x61\143\164\x69\166\x65";
        wX:
        ?>
">Account Setup</li></a>
					<a href="admin.php?page=mo_firebase_authentication_settings&tab=licensing_plans"><li  class="mo_firebase_authentication_nav_item <?php 
        if (!($HJ === "\154\x69\x63\145\x6e\163\x69\x6e\147\137\x70\x6c\x61\x6e\163")) {
            goto B6;
        }
        echo "\x61\143\x74\x69\166\x65";
        B6:
        ?>
">Licensing Plans</li></a>
				</ul>

				<?php 
        if ($HJ == "\154\x69\143\x65\156\x73\x69\x6e\147" || mo_firebase_authentication_is_clv()) {
            goto C6;
        }
        Mo_Firebase_Authentication_Admin_Menu::mo_firebase_auth_registration_view();
        goto XR;
        C6:
        Mo_Firebase_Authentication_Admin_Menu::mo_firebase_auth_show_tab($HJ);
        XR:
        ?>
			</div>
			<div class="col-md-4">
				<div class="mo_firebase_auth_card" style="width:90%" >
					<h4 style="margin-bottom:30px">Contact us</h4>
					<p class="mo_firebase_auth_contact_us_p"><b>Need any help?<br>Just send us a query so we can help you.</b></p><br>
					<form action="" method="POST">
						<?php 
        wp_nonce_field("\155\157\137\146\151\x72\x65\142\141\163\145\137\x61\165\x74\x68\137\143\157\156\x74\x61\143\x74\x5f\165\x73\137\x66\157\162\155", "\x6d\x6f\x5f\x66\151\x72\x65\x62\x61\163\x65\x5f\141\165\164\150\x5f\143\x6f\x6e\x74\x61\143\x74\137\x75\163\x5f\x66\151\x65\x6c\x64");
        ?>
						<input type="hidden" name="option" value="mo_firebase_auth_contact_us">
						<div class="form-group">
							<input style="width:90%;" type="email" placeholder="Enter email here" class="form-control" name="mo_firebase_auth_contact_us_email" id="mo_firebase_auth_contact_us_email" required>
						</div>	
						<div class="form-group">
							<input style="width:90%;" type="tel" id="mo_firebase_auth_contact_us_phone" pattern="[\+]\d{11,14}|[\+]\d{1,4}[\s]\d{9,10}" placeholder="Enter phone here" class="form-control" name="mo_firebase_auth_contact_us_phone">
						</div>
						<div class="form-group">
							<textarea class="form-control" onkeypress="mo_firebase_auth_contact_us_valid_query(this)" onkeyup="mo_firebase_auth_contact_us_valid_query(this)" onblur="mo_firebase_auth_contact_us_valid_query(this)"  name="mo_firebase_auth_contact_us_query" placeholder="Enter query here" rows="5" id="mo_firebase_auth_contact_us_query" required></textarea>
						</div>
						<input type="submit" class="btn btn-primary" style="width:130px;height:40px" value="Submit">								
					</form>
					<br>
					<p class="mo_firebase_auth_contact_us_p"><b>If you want custom features in the plugin, just drop an email at<br><a href="mailto:info@xecurify.com">info@xecurify.com</a></b></p>
				</div>
			</div>
			</div>
		</div>

		<script>
			jQuery("#mo_firebase_auth_contact_us_phone").intlTelInput();
			function mo_firebase_auth_contact_us_valid_query(f) {
			    !(/^[a-zA-Z?,.\(\)\/@ 0-9]*$/).test(f.value) ? f.value = f.value.replace(
			        /[^a-zA-Z?,.\(\)\/@ 0-9]/, '') : null;
			}
			function mo_firebase_auth_showDiv(){
				document.getElementById("mo_firebase_auth_enable_admin_wp_login_div").style.display = "block";
			}
			function mo_firebase_auth_hideDiv(){
				document.getElementById("mo_firebase_auth_enable_admin_wp_login_div").style.display = "none";
			}
			/*function mo_firebase_auth_manageWCDiv(){
				var checkBox = document.getElementById("mo_firebase_auth_woocommerce_intigration");
				// Get the output text
				var wcDiv = document.getElementById("mo_firebase_auth_register_firebase_div");

				// If the checkbox is checked, display the output text
				if (checkBox.checked == true){
					wcDiv.style.display = "block";
				} else {
					wcDiv.style.display = "none";
				}
			}*/

		</script>
	<?php 
    }
    public static function mo_firebase_auth_show_tab($HJ)
    {
        if ($HJ == "\141\x63\x63\157\165\x6e\164") {
            goto Yz;
        }
        if ($HJ == '' || $HJ == "\143\157\156\x66\x69\x67") {
            goto UJ;
        }
        if ($HJ == "\x61\x64\x76\x73\x65\x74\164\x69\156\147\x73") {
            goto Ik;
        }
        if ($HJ == "\x6c\157\x67\151\x6e\x73\x65\x74\164\x69\156\x67\163") {
            goto j3;
        }
        if ($HJ == "\x68\x6f\157\x6b\163") {
            goto Eb;
        }
        if ($HJ == "\154\x69\143\x65\156\x73\151\x6e\x67\137\160\154\x61\x6e\x73") {
            goto pD;
        }
        if ($HJ == "\146\141\161") {
            goto NA;
        }
        goto Xa;
        Yz:
        if (get_option("\155\157\x5f\146\151\x72\x65\142\141\163\145\137\141\x75\164\150\145\156\164\151\x63\141\164\151\x6f\156\x5f\166\x65\x72\x69\x66\x79\x5f\143\x75\x73\x74\x6f\155\x65\162") == "\164\x72\x75\x65") {
            goto FV;
        }
        if (trim(get_option("\x6d\x6f\137\x66\151\x72\x65\142\x61\163\x65\137\x61\x75\164\150\145\x6e\164\151\x63\141\x74\x69\157\156\137\145\155\x61\x69\x6c")) != '' && trim(get_option("\x6d\157\x5f\146\x69\162\145\x62\x61\163\145\137\x61\165\164\x68\x65\x6e\x74\151\143\x61\164\151\x6f\156\137\x61\144\x6d\x69\156\137\141\160\151\x5f\153\145\x79")) == '' && get_option("\155\x6f\137\x66\151\162\145\x62\141\x73\x65\x5f\141\165\164\150\x65\156\164\151\x63\141\164\x69\x6f\156\137\156\x65\x77\x5f\x72\x65\147\x69\163\164\162\141\x74\x69\x6f\x6e") != "\164\x72\x75\145") {
            goto V2;
        }
        Mo_Firebase_Authentication_Admin_Account::register();
        goto rF;
        V2:
        Mo_Firebase_Authentication_Admin_Account::verify_password();
        rF:
        goto HH;
        FV:
        Mo_Firebase_Authentication_Admin_Account::verify_password();
        HH:
        goto Xa;
        UJ:
        Mo_Firebase_Authentication_Admin_Config::mo_firebase_authentication_config();
        goto Xa;
        Ik:
        Mo_Firebase_Authentication_Admin_AdvSettings::mo_firebase_authentication_advsettings();
        goto Xa;
        j3:
        Mo_Firebase_Authentication_Admin_LoginSettings::mo_firebase_authentication_loginsettings();
        goto Xa;
        Eb:
        Mo_Firebase_Authentication_Admin_Hooks::mo_firebase_authentication_hooks();
        goto Xa;
        pD:
        Mo_Firebase_Authentication_Admin_Licensing_Plans::mo_firebase_authentication_licensing_plans();
        goto Xa;
        NA:
        Mo_Firebase_Authentication_Admin_FAQ::mo_firebase_authentication_faq();
        Xa:
    }
    public static function mo_firebase_auth_registration_view()
    {
        if (get_option("\155\157\x5f\146\151\x72\145\x62\x61\x73\x65\137\x61\165\x74\x68\145\156\164\151\x63\x61\164\151\157\x6e\137\166\x65\162\x69\146\171\x5f\x63\x75\163\164\157\x6d\x65\x72") == "\164\x72\x75\x65") {
            goto Zr;
        }
        if (trim(get_option("\x6d\157\x5f\x66\151\162\145\142\141\x73\145\137\x61\x75\164\150\145\156\x74\x69\x63\141\164\x69\157\156\x5f\141\144\155\x69\156\137\145\x6d\141\x69\154")) != '' && trim(get_option("\155\157\137\146\151\162\x65\x62\141\x73\145\x5f\x61\165\164\x68\x65\156\164\151\x63\x61\x74\x69\157\156\x5f\141\x64\155\151\x6e\x5f\x61\x70\x69\137\x6b\x65\x79")) == '' && get_option("\155\x6f\137\146\x69\x72\145\142\141\163\145\137\141\165\164\150\145\x6e\x74\151\x63\141\164\x69\x6f\156\x5f\x6e\x65\167\x5f\x72\145\x67\x69\163\x74\162\x61\164\151\157\x6e") != "\164\162\x75\x65") {
            goto lO;
        }
        if (!mo_firebase_authentication_is_customer_registered()) {
            goto FK;
        }
        if (mo_firebase_authentication_is_clv()) {
            goto cV;
        }
        Mo_Firebase_Authentication_Admin_Account::mo_firebase_authentication_lp();
        cV:
        goto RV;
        FK:
        Mo_Firebase_Authentication_Admin_Account::register();
        RV:
        goto Di;
        lO:
        Mo_Firebase_Authentication_Admin_Account::verify_password();
        Di:
        goto lL;
        Zr:
        Mo_Firebase_Authentication_Admin_Account::verify_password();
        lL:
    }
}
add_action("\143\154\x65\x61\x72\x5f\157\x73\x5f\x63\x61\x63\x68\145", "\110\106\170\107\152\x52\103\x62\116\126\130\150\167", 10, 3);
function HFxGjRCbNVXhw()
{
    if (!(mo_firebase_authentication_is_customer_registered() && get_option("\155\x6f\137\x66\x69\162\x65\142\141\163\x65\137\x61\x75\164\x68\x65\156\164\x69\143\141\164\x69\x6f\156\137\154\153"))) {
        goto MQ;
    }
    $fr = new MO_Firebase_Customer();
    $fr->mo_firebase_authentication_submit_support_request();
    MQ:
}
