<?php


class Mo_Firebase_Authentication_Admin_Config
{
    public static function mo_firebase_authentication_config()
    {
        ?>
	<!-- <div id="mo_firebase_authentication_support_layout" class="mo_firebase_authentication_support_layout"> -->
	<div class="row">
			<div class="col-md-12">
				<div>
					<div class="mo_firebase_auth_card" style="width:100%" >
						<form action="" method="post" id="mo_enable_firebase_auth_form">
							<?php 
        wp_nonce_field("\x6d\157\137\x66\x69\162\x65\142\141\163\x65\x5f\141\x75\164\150\x5f\x65\156\141\142\x6c\145\137\x66\x6f\162\155", "\x6d\157\137\x66\x69\x72\145\x62\141\x73\145\x5f\x61\x75\x74\x68\137\x65\156\x61\142\154\145\x5f\146\x69\145\x6c\x64");
        ?>
							<div style="display:inline"><div style="display:inline-block;padding:0px 10px 10px 0px"><strong>Enable Firebase Authentication:</strong></div>
								<div style="display:inline-block"><label class="mo_firebase_auth_switch">
									<input value="1" name="mo_enable_firebase_auth" type="checkbox" id="mo_enable_firebase_auth" <?php 
        echo get_option("\x6d\x6f\x5f\x65\x6e\141\142\154\x65\x5f\146\x69\x72\x65\x62\x61\163\x65\x5f\141\165\164\150") ? "\x63\150\x65\143\x6b\x65\x64" : '';
        ?>
>
									<span class="mo_firebase_auth_slider round"></span>
									<input type="hidden" name="option" value="mo_enable_firebase_auth">
									</label>
								</div>
							</div>
						</form>
						<form action="" method="post" id="mo_firebase_auth_form">
							<?php 
        wp_nonce_field("\x6d\157\x5f\146\151\x72\x65\x62\141\x73\145\x5f\141\165\x74\150\x5f\143\x6f\156\x66\151\147\x5f\146\157\162\155", "\155\x6f\x5f\146\x69\x72\x65\x62\x61\x73\145\137\x61\165\x74\x68\137\x63\x6f\156\146\151\x67\x5f\146\151\145\x6c\144");
        ?>
							<div style="padding:5px;"></div>Allow login with
							<input style="margin-left: 5px;"type="radio" name="disable_wordpress_login" id="disable_wordpress_login" <?php 
        if (get_option("\155\x6f\137\x65\156\141\x62\154\145\137\x66\x69\x72\145\142\x61\163\x65\x5f\x61\x75\164\x68") == false) {
            goto AP;
        }
        echo get_option("\155\157\137\146\151\x72\x65\x62\141\x73\x65\137\x61\x75\164\x68\x5f\144\151\163\141\x62\x6c\x65\137\x77\157\x72\144\160\162\145\163\163\x5f\154\x6f\147\151\156") ? '' : "\143\x68\x65\x63\153\x65\x64";
        goto dp;
        AP:
        echo '';
        dp:
        ?>
 value="0"onclick="mo_firebase_auth_hideDiv();">Both Firebase and WordPress
							<span style="padding-right:10px" ></span>
							<input type="radio" name="disable_wordpress_login" id="disable_wordpress_login" <?php 
        if (get_option("\155\x6f\x5f\145\156\141\142\154\x65\x5f\x66\151\x72\145\x62\x61\163\145\137\141\165\x74\150") == false) {
            goto JG;
        }
        echo get_option("\x6d\x6f\137\146\x69\162\145\x62\141\163\x65\x5f\x61\165\x74\150\137\x64\151\x73\x61\142\154\145\137\x77\x6f\x72\144\x70\x72\x65\163\163\137\154\157\x67\x69\x6e") ? "\x63\x68\145\x63\153\145\144" : '';
        goto i1;
        JG:
        echo '';
        i1:
        ?>
 value="1"onclick="mo_firebase_auth_showDiv();">Only Firebase
							<div style="padding:5px;"></div>
							<div style="<?php 
        if (get_option("\x6d\x6f\x5f\x66\x69\x72\x65\x62\x61\x73\x65\x5f\141\x75\164\x68\137\144\151\x73\141\x62\154\x65\137\x77\x6f\x72\x64\160\x72\x65\x73\163\137\x6c\x6f\147\x69\156") == 1) {
            goto fe;
        }
        echo "\144\151\x73\160\x6c\x61\171\72\x20\156\157\x6e\x65";
        goto B0;
        fe:
        echo "\x64\x69\163\160\x6c\x61\x79\72\40\x62\154\x6f\143\x6b";
        B0:
        ?>
"id="mo_firebase_auth_enable_admin_wp_login_div">
								<p>Enabling Firebase login will restrict logins with only Firebase credentials and won't allow WP login. <b>Please enable this only after you have successfully tested your configuration</b> as the default WordPress login will stop working.
								</p>
								<input type="checkbox" id="mo_firebase_auth_enable_admin_wp_login" name="mo_firebase_auth_enable_admin_wp_login" value="1" <?php 
        checked(esc_attr(get_option("\x6d\x6f\x5f\x66\x69\162\x65\142\x61\163\145\x5f\141\165\x74\x68\137\x65\156\x61\142\154\x65\137\x61\144\155\151\x6e\137\x77\160\137\154\157\x67\151\156")) === false || esc_attr(get_option("\155\x6f\x5f\x66\151\x72\145\x62\141\x73\145\137\x61\165\164\x68\137\x65\156\x61\142\154\145\137\141\x64\x6d\151\156\x5f\167\x70\x5f\x6c\x6f\x67\151\156")) == 1);
        ?>
 /> Allow Administrators to use WordPress login&emsp;<div class="mo-firebase-auth-tooltip">&#x1F6C8;<div class="mo-firebase-auth-tooltip-text mo-tt-right">Selecting this option will only allow Wordpress Administrators to log in.</div> </div>
								<br>
							</div>
							<br>
							<h6><font color="#FF0000">*</font><font color="#000000">Project Id</font>&emsp;<div class="mo-firebase-auth-tooltip">&#x1F6C8;<div class="mo-firebase-auth-tooltip-text mo-tt-right">collect project Id from your firebase project</div> </div></h6>
							<input style="width:60%"type="text" id="project_id" name="projectid" value= "<?php 
        echo get_option("\155\157\x5f\146\x69\x72\145\x62\141\163\x65\137\141\x75\164\150\137\160\162\x6f\x6a\x65\143\164\x5f\151\144");
        ?>
" placeholder="Enter Project Id.." required="">
							<br><br>
							<h6><font color="#FF0000">*</font><font color="#000000">API Key</font>&emsp;<div class="mo-firebase-auth-tooltip">&#x1F6C8;<div class="mo-firebase-auth-tooltip-text mo-tt-right">collect API key from your firebase project</div> </div></h6>
							<input style="width:60%"type="text" id="api_key" name="apikey" value="<?php 
        echo get_option("\155\157\x5f\x66\x69\162\145\x62\141\x73\145\137\x61\x75\164\150\x5f\141\x70\x69\x5f\153\x65\171");
        ?>
" placeholder="Enter your API Key.." required="">
							<br>
							<br>
							<input type="submit" class="btn btn-primary" style="width:170px;height:40px" name="verify_user" value=" Save Configuration" id = "mo_auth_configure_button"onclick="showAlert();">
						</form>
					</div>
				</div>
				<div>
					<div class="mo_firebase_auth_card" id ="test_authentication" style="width:100%" >
						<h4 style="margin-bottom:30px">Test Authentication</h4>
						<form name="test_configuration_form" id="mo_firebasetestconfig"  method="post" target="firebasetestconfig">
							<?php 
        wp_nonce_field("\x6d\x6f\x5f\146\151\162\145\x62\x61\x73\x65\137\x61\x75\x74\150\137\x74\145\163\164\137\143\x6f\156\x66\x69\147\x5f\146\157\162\155", "\x6d\x6f\137\x66\x69\162\145\142\141\163\145\x5f\141\x75\x74\x68\137\x74\x65\x73\x74\x5f\143\157\156\146\151\147\137\146\x69\145\x6c\144");
        ?>
							<input type="hidden" name="option" value="mo_firebase_auth_test_configuration">
							<font color="#FF0000">* </font><input type="text" id="test_username" name="test_username" value="" placeholder="Username" style="margin-bottom:30px;width:35%;" required=""> <br>
							<font color="#FF0000">* </font><input type="password" id="test_password" name="test_password" value="" placeholder="Password" style="margin-bottom:30px;width:35%;" required=""> <br>
							<input type="hidden" id="test_check_field" name="test_check_field" value="test_check_true">
							<input type="submit" class="btn btn-primary" id="mo_firebase_auth_test_config_button" style="width:170px;height:40px" name="test_configuration" value="Test Authentication" <?php 
        if (!(!get_option("\155\157\137\x66\x69\162\x65\142\141\x73\145\x5f\141\165\164\x68\137\160\x72\157\152\x65\x63\x74\x5f\151\x64") && !get_option("\155\x6f\137\x66\x69\162\x65\x62\141\x73\x65\137\141\165\164\150\137\x61\160\151\137\x6b\x65\171"))) {
            goto wK;
        }
        echo "\x64\151\163\141\x62\154\145\x64";
        wK:
        ?>
 >
						</form>
					</div>
				
			</div>

			<div>
				<?php 
        $ga = get_option("\x6d\157\x5f\146\151\x72\145\x62\141\163\145\x5f\155\x61\x70\x70\x65\144\137\141\x74\x74\162");
        $ga = isset($ga) ? json_decode($ga, true) : array();
        ?>
					
					<div class="mo_firebase_auth_card" style="width:100%" >
						<h4 style="margin-bottom:30px">Attribute Mapping</h4>
						<form name="mo_firebase_attr_mapping_form" id="mo_firebase_attr_mapping_form"  method="post">
							<?php 
        wp_nonce_field("\155\157\137\x66\151\162\x65\x62\x61\x73\145\137\x61\x74\x74\x72\x5f\x6d\141\x70\x70\151\156\147\x5f\146\157\x72\155", "\x6d\157\137\146\x69\162\145\142\x61\163\x65\x5f\x61\x74\164\162\137\x6d\141\160\160\x69\x6e\x67\137\x66\151\145\154\x64");
        ?>
							<input type="hidden" name="option" value="mo_firebase_attr_mapping">
							<table class="mo_firebase_attr_table">
							<tr><td><strong><span class="mo_premium_feature">*</span>Username Attribute:</strong></td>
							<td><input type="text" id="mo_firebase_username" name="username_attr" value="<?php 
        echo isset($ga["\155\x6f\x5f\146\x69\x72\145\142\141\163\145\x5f\x75\163\145\162\x6e\141\155\145\137\141\164\164\162"]) ? $ga["\155\x6f\x5f\x66\151\162\145\142\141\163\x65\x5f\x75\x73\145\x72\156\x61\x6d\145\137\141\164\164\x72"] : '';
        ?>
" placeholder="Username Attribute Name" class="mo_table_textbox"></td></tr>

							<tr><td><span class="mo_premium_feature"></span><strong>Email Attribute:</strong></td>
							<td><input type="text" id="mo_firebase_email" name="email_attr" value="<?php 
        echo isset($ga["\155\157\137\146\151\x72\145\x62\141\163\145\x5f\x65\x6d\141\151\154\137\x61\164\164\162"]) ? $ga["\155\x6f\137\146\x69\162\145\x62\141\163\x65\137\x65\155\141\x69\154\x5f\141\x74\x74\x72"] : '';
        ?>
" placeholder="Email Attribute Name" class="mo_table_textbox"></td></tr>

							<!-- <tr><td><strong><span class="mo_premium_feature"></span>First Name Attribute:</strong></td>
							<td><input type="text" id="mo_firebase_firstname" name="firstname_attr" value="<?php 
        echo isset($ga["\x6d\x6f\x5f\146\x69\162\145\142\141\x73\145\x5f\146\x69\162\163\164\x6e\141\155\x65\x5f\x61\x74\x74\x72"]) ? $ga["\x6d\157\x5f\x66\151\x72\x65\x62\141\x73\x65\x5f\x66\151\162\x73\x74\x6e\141\155\145\x5f\141\x74\164\x72"] : '';
        ?>
"  placeholder="Firstname Attribute Name" class="mo_table_textbox"></td></tr>

							<tr><td><strong><span class="mo_premium_feature"></span>Last Name Attribute:</strong></td>			
							<td><input type="text" id="mo_firebase_lastname" name="lastname_attr" value="<?php 
        echo isset($ga["\x6d\x6f\x5f\x66\151\x72\x65\x62\x61\163\145\x5f\154\x61\x73\x74\156\141\155\x65\x5f\141\x74\x74\162"]) ? $ga["\x6d\157\x5f\x66\x69\162\145\x62\141\x73\145\137\154\141\163\x74\x6e\141\x6d\145\x5f\141\x74\x74\162"] : '';
        ?>
" placeholder="Lastname Attribute Name" class="mo_table_textbox"></td></tr>

							<tr><td><strong><span class="mo_premium_feature"></span>Group Attribute Name:</strong></td>
							<td><input type="text" id="mo_firebase_group" name="group_attr" value="<?php 
        echo isset($ga["\155\157\137\x66\151\x72\145\142\141\163\145\x5f\x67\162\x6f\165\x70\x6e\141\155\x65\x5f\141\164\x74\x72"]) ? $ga["\155\157\x5f\x66\151\162\145\142\141\x73\145\137\147\x72\x6f\165\160\156\x61\x6d\145\x5f\x61\164\x74\x72"] : '';
        ?>
" placeholder="Group Attribute Name" class="mo_table_textbox"></td></tr>

							<?php 
        $ES = isset($ga["\x6d\x6f\137\x66\x69\x72\x65\142\x61\163\x65\137\144\x69\163\160\154\x61\x79\156\x61\155\145\137\x61\164\164\x72"]) ? $ga["\x6d\157\x5f\x66\x69\x72\x65\x62\x61\163\145\137\144\x69\163\x70\x6c\x61\171\x6e\x61\x6d\x65\137\x61\164\164\162"] : "\125\x53\x45\122\116\101\115\105";
        ?>

							<tr><td><strong><span class="mo_premium_feature"></span>Display Name:</strong></td>							
							<td>
							<select name="mo_firebase_display_name" id="mo_firebase_display_name">
								<option value="USERNAME" <?php 
        echo $ES === "\125\123\x45\x52\116\x41\115\105" ? "\163\145\154\145\x63\164\x65\144" : '';
        ?>
>Username</option>
								<option value="FNAME" <?php 
        echo $ES === "\x46\116\x41\115\x45" ? "\163\145\x6c\x65\143\164\x65\x64" : '';
        ?>
>Firstname</option>
								<option value="LNAME" <?php 
        echo $ES === "\x4c\x4e\x41\115\x45" ? "\x73\145\x6c\x65\x63\x74\145\144" : '';
        ?>
>Lastname</option>
								<option value="FNAME_LNAME" <?php 
        echo $ES === "\106\x4e\101\115\105\137\114\x4e\101\115\105" ? "\x73\145\154\145\143\164\x65\144" : '';
        ?>
>Firstname Lastname</option>
								<option value="LNAME_FNAME" <?php 
        echo $ES === "\114\x4e\101\115\105\137\106\116\x41\115\105" ? "\x73\x65\154\145\x63\164\145\x64" : '';
        ?>
>Lastname Firstname</option>
							</select>
							</td></tr> -->
							</table><br>
							<input type="submit" class="btn btn-primary" id="mo_firebase_save_attr_mapping_button" style="width:170px;height:40px" name="save_settings" value="Save Settings">
						</form>
					</div>
				
			</div>
		</div>
	</div>
	<script>
		jQuery("#mo_firebase_auth_test_config_button").on("click", function(event) {
					var test_username = document.forms["test_configuration_form"]["test_username"].value;
					var test_password = document.forms["test_configuration_form"]["test_password"].value;
					if( test_username == "" || test_password == "" ){
						return;
					}
					event.preventDefault();
					let url = "<?php 
        echo site_url();
        ?>
/?mo_action=firebaselogin&test=true";
					jQuery("#mo_firebasetestconfig").attr("action", url);
					let newwindow = window.open("about:blank", 'firebasetestconfig', 'location=yes,height=700,width=600,scrollbars=yes,status=yes');
					jQuery("#mo_firebasetestconfig").submit();
				});
				function mo_firebase_auth_showDiv(){
						document.getElementById("mo_firebase_auth_enable_admin_wp_login_div").style.display = "block";
				}
				function mo_firebase_auth_hideDiv(){
					document.getElementById("mo_firebase_auth_enable_admin_wp_login_div").style.display = "none";
				}

	</script>
	<?php 
    }
}
