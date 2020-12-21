<?php


class Mo_Firebase_Authentication_Admin_AdvSettings
{
    public static function mo_firebase_authentication_advsettings()
    {
        ?>
	<div class="row">
		<div class="col-md-12">
			<div class="mo_firebase_auth_card" style="width:90%">
				<form name="autosync_form" id="mo_firebase_auth_autosync"  method="post">
					<?php 
        wp_nonce_field("\x6d\x6f\137\146\x69\162\145\142\x61\163\145\137\141\165\x74\x68\137\145\156\141\x62\x6c\145\x5f\x61\x75\164\x6f\163\x79\x6e\143\137\146\x6f\162\155", "\155\157\137\146\x69\162\145\142\x61\x73\145\137\141\165\164\x68\137\145\156\141\142\154\x65\x5f\x61\165\164\157\163\171\x6e\143\137\146\151\145\154\x64");
        ?>
					<input type="hidden" name="option" value="mo_firebase_auth_autosync">
					<h6><b>Sync WordPress and Firebase users</b></h6><br>
					
					<div style="display:inline-block"><label class="mo_firebase_auth_switch">
						<input value="1" name="mo_enable_firebase_auto_register" type="checkbox" id="mo_enable_firebase_auto_register" <?php 
        echo get_option("\x6d\157\137\x65\x6e\x61\x62\x6c\145\137\x66\151\162\145\x62\141\163\145\x5f\141\x75\x74\x6f\x5f\162\x65\147\x69\163\164\145\x72") ? "\143\x68\x65\x63\x6b\x65\x64" : '';
        ?>
>
						<span class="mo_firebase_auth_slider round"></span>
						</label>
					</div>
					<strong>Auto register users into Firebase</strong>
					<br>
					<h8>Enabling this option will create new user in Firebase project when a user registers in WordPress site.</h8>
					<br><br>
					<input type="submit" style="width:25%"class="btn btn-primary" name="autosync_settings" value=" Save Settings" id = "mo_auth_autosync_save_settings_button">
			    </form>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<div class="mo_firebase_auth_card" style="width:90%">
				<form name="integration_form" id="mo_firebase_auth_integration"  method="post">
					<input type="hidden" name="option" value="mo_firebase_auth_integration">
					<p><b>Select below if you want to allow users to login using firebase credentials with WooCommerce or BuddyPress.</b></p>
					<input type="checkbox" name = "mo_firebase_auth_woocommerce_intigration" id = "mo_firebase_auth_woocommerce_intigration" value= "1" <?php 
        echo get_option("\155\157\x5f\146\x69\x72\x65\x62\x61\x73\x65\137\141\165\164\x68\137\167\157\157\x63\x6f\155\155\x65\x72\x63\145\x5f\x69\x6e\164\x69\147\x72\x61\164\151\x6f\156") ? "\x63\x68\x65\143\153\145\144" : '';
        ?>
 >
							<img src="<?php 
        echo plugin_dir_url(__FILE__) . "\x2e\56\57\x2e\56\x2f\x69\x6d\x61\147\145\163\57\167\x6f\157\143\157\155\x6d\145\162\x63\145\x2d\143\x69\x72\143\154\145\56\x70\x6e\147";
        ?>
" width="50px"> 
							 WooCommerce
							<!-- <div style="<?php 
        if (get_option("\x6d\157\137\146\x69\x72\x65\142\x61\163\145\137\x61\165\164\150\137\167\x6f\157\143\x6f\x6d\155\x65\x72\x63\x65\137\151\156\x74\x69\147\162\x61\164\x69\157\156") == 1) {
            goto vO;
        }
        echo "\x64\x69\x73\160\x6c\x61\171\x3a\x20\x6e\157\x6e\145";
        goto WO;
        vO:
        echo "\x64\x69\163\x70\154\141\x79\72\x20\142\154\157\x63\x6b";
        WO:
        ?>
"id="mo_firebase_auth_register_firebase_div">
								</p><div style="padding:5px;"></div>
								<p style="margin-left: 10px;">Enabling <b>Auto register users in Firebase</b> will create new user in Firebase project when a user registers in WooCommerce.
								</p>
								<input style="margin-left: 10px;" type="checkbox" id="mo_firebase_auto_register_user_in_firebase" name="mo_firebase_auto_register_user_in_firebase" value="1" <?php 
        checked(esc_attr(get_option("\155\157\x5f\x66\x69\162\x65\x62\141\x73\145\137\x61\165\x74\157\x5f\162\145\x67\151\163\x74\145\x72\x5f\165\x73\145\162\x5f\x69\156\137\146\x69\162\x65\x62\x61\163\145")) == 1);
        ?>
 /> Auto register users in Firebase&emsp;<div class="mo-firebase-auth-tooltip">&#x1F6C8;<div class="mo-firebase-auth-tooltip-text mo-tt-right">Selecting this option will create new users in Firebase Project.</div> </div>
							</div> -->
							<br><br>
						<input type="checkbox" name = "mo_firebase_auth_buddypress_intigration"value="1"<?php 
        echo get_option("\x6d\x6f\137\x66\151\x72\145\142\141\x73\145\137\x61\165\x74\x68\137\x62\x75\144\x64\171\x70\x72\145\163\163\x5f\151\156\x74\151\147\162\141\164\151\157\156") ? "\x63\x68\x65\143\x6b\x65\x64" : '';
        ?>
>
							<img src="<?php 
        echo plugin_dir_url(__FILE__) . "\56\x2e\x2f\56\56\57\x69\155\141\x67\145\163\x2f\x62\165\x64\144\171\x70\x72\x65\x73\x73\56\160\156\x67";
        ?>
" width="50px"> BuddyPress
			    	<br><br>
			    	<input type="submit" style="text-align:center;"class="btn btn-primary" style="width:120px;height:40px" name="integration_settings" value="Save Settings" id = "mo_auth_integration_save_settings_button">
			    </form>
			</div>
		</div>
	</div>
	<?php 
        $u4 = array();
        $Iw = get_option("\155\157\137\146\151\162\x65\142\x61\x73\145\x5f\x6f\x61\x75\x74\x68\137\147\157\x6f\147\x6c\x65\137\x70\x72\157\166\151\144\145\x72\x5f\154\157\147\x69\x6e\137\155\x65\x74\150\157\x64");
        if (!(isset($Iw) && $Iw == 1)) {
            goto br;
        }
        array_push($u4, "\107\x6f\x6f\x67\x6c\145");
        br:
        $Sx = get_option("\155\x6f\x5f\146\x69\162\x65\x62\141\163\145\137\157\x61\165\164\x68\137\146\141\x63\x65\142\x6f\157\x6b\x5f\x70\162\157\166\x69\144\145\x72\137\154\x6f\x67\151\156\x5f\x6d\x65\x74\x68\x6f\144");
        if (!(isset($Sx) && $Sx == 1)) {
            goto RU;
        }
        array_push($u4, "\106\x61\x63\145\142\157\157\x6b");
        RU:
        $J5 = get_option("\155\x6f\x5f\146\151\162\145\142\x61\163\x65\137\x6f\141\x75\x74\x68\137\147\x69\164\150\x75\x62\137\160\162\157\x76\x69\x64\x65\x72\x5f\154\157\x67\x69\156\x5f\155\145\164\x68\x6f\144");
        if (!(isset($J5) && $J5 == 1)) {
            goto bW;
        }
        array_push($u4, "\x47\151\164\150\x75\x62");
        bW:
        $Nd = get_option("\x6d\157\x5f\146\x69\162\x65\x62\x61\163\145\137\157\141\165\164\150\x5f\x74\x77\x69\164\x74\145\x72\x5f\x70\162\x6f\x76\x69\x64\145\162\x5f\x6c\157\147\151\156\137\155\145\x74\150\157\x64");
        if (!(isset($Nd) && $Nd == 1)) {
            goto vV;
        }
        array_push($u4, "\x54\167\x69\164\x74\x65\162");
        vV:
        $DX = get_option("\x6d\x6f\137\146\151\162\x65\142\141\x73\145\137\157\141\x75\164\x68\x5f\155\x69\x63\x72\157\163\157\x66\164\137\x70\162\157\166\151\x64\145\x72\137\x6c\157\147\151\156\x5f\155\x65\164\x68\157\x64");
        if (!(isset($DX) && $DX == 1)) {
            goto W5;
        }
        array_push($u4, "\115\x69\143\162\157\163\157\x66\x74");
        W5:
        $ZF = get_option("\155\157\x5f\x66\x69\162\145\x62\141\x73\x65\137\x6f\141\165\x74\150\x5f\x79\x61\x68\x6f\x6f\137\160\162\157\x76\151\x64\x65\162\137\x6c\157\x67\x69\x6e\137\x6d\145\x74\x68\x6f\144");
        if (!(isset($ZF) && $ZF == 1)) {
            goto LW;
        }
        array_push($u4, "\131\141\150\x6f\x6f");
        LW:
        $yN = get_option("\x6d\157\137\146\x69\162\x65\x62\x61\163\x65\137\157\x61\165\x74\150\x5f\141\160\x70\x6c\x65\x5f\160\162\x6f\166\x69\x64\x65\x72\137\x6c\x6f\x67\151\156\137\x6d\145\164\x68\x6f\144");
        if (!(isset($yN) && $yN == 1)) {
            goto oz;
        }
        array_push($u4, "\101\x70\x70\x6c\x65");
        oz:
        update_option("\155\157\x5f\x66\151\162\145\142\x61\163\x65\x5f\x61\165\164\150\137\160\x72\157\166\x69\x64\145\x72\137\x6d\x65\x74\x68\x6f\x64\137\154\x69\x73\164", $u4);
        $Qb = get_option("\155\x6f\x5f\146\x69\162\145\142\x61\163\145\137\141\x75\164\x68\x5f\x73\150\157\x77\x5f\157\x6e\137\x6c\157\147\x69\156\x5f\160\141\147\145");
        ?>
	<div class="row">
		<div class="col-md-12">
			<div class="mo_firebase_auth_card" style="width:90%">
				<form name="mo_firebase_auth_provider_method_form" id="mo_firebase_auth_provider_method_form"  method="post">
					<input type="hidden" name="option" value="mo_firebase_auth_provider_method">
					<h6><b>Firebase Authentication methods </b></h6><br>
					<h8>Select any one method to Login into your site using one of the Firebase Authentication method. </h8><br><br>
					<!-- <input type="radio" id="emailPassword" value="emailPassword" disabled>
					<label for="male">Email and Password</label><br> -->
					<input type="checkbox" id="google" name="google_provider_method" <?php 
        if (!(isset($Iw) && $Iw == 1)) {
            goto Mq;
        }
        echo "\143\150\x65\x63\x6b\x65\x64";
        Mq:
        ?>
 value="1">
					<label for="google">Google</label><br>
					<input type="checkbox" id="facebook" name="facebook_provider_method" <?php 
        if (!(isset($Sx) && $Sx == 1)) {
            goto t6;
        }
        echo "\143\150\145\x63\153\145\144";
        t6:
        ?>
 value="1">
					<label for="facebook">Facebook</label><br>
					<input type="checkbox" id="github" name="github_provider_method" <?php 
        if (!(isset($J5) && $J5 == 1)) {
            goto LY;
        }
        echo "\143\150\x65\143\153\145\x64";
        LY:
        ?>
 value="1">
					<label for="github">GitHub</label><br>
					<input type="checkbox" id="twitter" name="twitter_provider_method" <?php 
        if (!(isset($Nd) && $Nd == 1)) {
            goto E2;
        }
        echo "\x63\150\x65\143\x6b\x65\144";
        E2:
        ?>
 value="1">
					<label for="twitter">Twitter</label><br>
					<input type="checkbox" id="microsoft" name="microsoft_provider_method" <?php 
        if (!(isset($DX) && $DX == 1)) {
            goto gG;
        }
        echo "\143\x68\x65\x63\153\x65\x64";
        gG:
        ?>
 value="1">
					<label for="microsoft">Microsoft</label><br>
					<input type="checkbox" id="yahoo" name="yahoo_provider_method" <?php 
        if (!(isset($ZF) && $ZF == 1)) {
            goto nI;
        }
        echo "\x63\150\145\x63\153\x65\x64";
        nI:
        ?>
 value="1">
					<label for="yahoo">Yahoo</label><br>
					<input type="checkbox" id="apple" name="apple_provider_method" <?php 
        if (!(isset($yN) && $yN == 1)) {
            goto VG;
        }
        echo "\x63\150\145\x63\153\x65\144";
        VG:
        ?>
 value="1">
					<label for="apple">Apple</label><br>
					<!-- <input type="radio" id="phone" name="provider_method" <?php 
        if (!(isset($z5) && $z5 == "\x70\x68\157\156\x65")) {
            goto gM;
        }
        echo "\143\150\x65\143\x6b\x65\x64";
        gM:
        ?>
 value="phone">
					<label for="phone">Phone</label><br> -->
					<br>
					<input type="checkbox" name="mo_firebase_auth_show_on_login_page" value ="1" <?php 
        if (!(isset($Qb) && $Qb == 1)) {
            goto jf;
        }
        echo "\143\150\x65\143\153\x65\x64";
        jf:
        ?>
/>Show Login button on WP login page
					<br><br>
					<input type="submit" style="text-align:center;"class="btn btn-primary" style="width:120px;height:40px" name="authentication_settings" value=" Save Settings" id = "mo_auth_authentication_save_settings_button" ><br>
			    </form>
			</div>
		</div>
	</div>
	<?php 
    }
}
