<?php


function mo_firebase_auth_register_ui()
{
    update_option("\x6d\157\x5f\146\x69\x72\x65\x62\141\163\145\137\141\x75\x74\150\145\x6e\x74\151\x63\x61\164\x69\157\x6e\137\x6e\x65\x77\137\162\145\147\x69\x73\164\162\141\x74\151\x6f\x6e", "\164\x72\165\145");
    $current_user = wp_get_current_user();
    ?>
			<!--Register with miniOrange-->
		<form name="f" method="post" action="">
			<input type="hidden" name="option" value="mo_firebase_authentication_register_customer" />
			<!-- <div class="mo_table_layout"> -->
			<!-- <div class="row">
				<div class="col-md-8"> -->
					<div class="mo_firebase_auth_card" style="width:100%">
						<!-- <div id="toggle1" class="mo_panel_toggle"> -->
							<h4>Register with miniOrange<small style="font-size: x-small;"> [OPTIONAL]</small></h4>
						<!-- </div> -->
						<!-- <div id="panel1"> -->
							<!--<p><b>Register with miniOrange</b></p>-->
		<!-- 					<p>Please enter a valid Email ID that you have access to. You will be able to move forward after verifying an OTP that we will be sending to this email.
							</p> -->
							<p style="font-size:14px;"><b>Why should I register? </b></p>
			                    <div id="help_register_desc" style="background: aliceblue; padding: 10px 10px 10px 10px; border-radius: 10px;font-size: small;">
			                        You should register so that in case you need help, we can help you with step by step instructions.
			                        <b>You will also need a miniOrange account to upgrade to the premium version of the plugins.</b> We do not store any information except the email that you will use to register with us.
			                    </div>
		                    </p>
							<table class="mo_settings_table">
								<tr>
									<td><b><font color="#FF0000">*</font>Email:</b></td>
									<td><input class="mo_table_textbox" type="email" name="email"
										required placeholder="person@example.com"
										value="<?php 
    echo get_option("\x6d\157\137\146\x69\x72\145\142\x61\163\145\137\141\x75\164\x68\145\x6e\164\151\x63\141\x74\151\157\x6e\x5f\141\x64\x6d\x69\x6e\137\145\155\x61\x69\x6c");
    ?>
" />
									</td>
								</tr>
								<tr class="hidden">
									<td><b><font color="#FF0000">*</font>Website/Company Name:</b></td>
									<td><input class="" type="text" name="company"
									required placeholder="Enter website or company name"
									value="<?php 
    echo $_SERVER["\x53\x45\x52\x56\105\x52\x5f\116\x41\x4d\x45"];
    ?>
"/></td>
								</tr>
								<tr  class="hidden">
									<td><b>&nbsp;&nbsp;First Name:</b></td>
									<td><input class="" type="text" name="fname"
									placeholder="Enter first name" value="<?php 
    echo $current_user->user_firstname;
    ?>
" /></td>
								</tr>
								<tr class="hidden">
									<td><b>&nbsp;&nbsp;Last Name:</b></td>
									<td><input class="" type="text" name="lname"
									placeholder="Enter last name" value="<?php 
    echo $current_user->user_lastname;
    ?>
" /></td>
								</tr>

								<tr  class="hidden">
									<td><b>&nbsp;&nbsp;Phone number :</b></td>
									 <td><input class="" type="text" name="phone" pattern="[\+]?([0-9]{1,4})?\s?([0-9]{7,12})?" id="phone" title="Phone with country code eg. +1xxxxxxxxxx" placeholder="Phone with country code eg. +1xxxxxxxxxx" value="<?php 
    echo get_option("\x6d\x6f\x5f\146\x69\162\145\142\x61\163\145\x5f\x61\165\x74\150\x65\x6e\x74\x69\x63\141\x74\x69\157\x6e\137\141\x64\x6d\151\156\x5f\160\x68\157\x6e\x65");
    ?>
" />
									 This is an optional field. We will contact you only if you need support.</td>
									</tr>
								</tr>
								<tr  class="hidden">
									<td></td>
									<td>We will call only if you need support.</td>
								</tr>
								<tr>
									<td><b><font color="#FF0000">*</font>Password:</b></td>
									<td><input class="mo_table_textbox" required type="password"
										name="password" placeholder="Choose your password (Min. length 8)" /></td>
								</tr>
								<tr>
									<td><b><font color="#FF0000">*</font>Confirm Password:</b></td>
									<td><input class="mo_table_textbox" required type="password"
										name="confirmPassword" placeholder="Confirm your password" /></td>
								</tr>
								<!-- <tr>
									<td>&nbsp;</td>
									 <td><br /><input type="submit" name="submit" value="Save" style="width:100px;"
										class="button button-primary button-large" /></td>
									<td><br><input type="submit" name="submit" value="Register" class="button button-primary button-large"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		                            <input type="button" name="mo_firebase_authentication_goto_login" id="mo_firebase_authentication_goto_login" value="Already have an account?" class="button button-primary button-large"/>&nbsp;&nbsp;</td>
								</tr> -->
							</table>
							<div>
									
									<!-- <td><br /><input type="submit" name="submit" value="Save" style="width:100px;"
										class="button button-primary button-large" /></td> -->
									<br><input style="margin-left:20px;width:20%" type="submit" name="submit" value="Register" class="button button-primary button-large"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		                            <input style="width:30%" type="button" name="mo_firebase_authentication_goto_login" id="mo_firebase_authentication_goto_login" value="Already have an account?" class="button button-primary button-large"/>&nbsp;&nbsp;<br>
							</div>
						<!-- </div> -->
					</div>
				<!-- </div>
			</div> -->
		</form>
			<form name="f1" method="post" action="" id="mo_firebase_authentication_goto_login_form">
            <?php 
    wp_nonce_field("\155\x6f\x5f\x66\151\162\x65\x62\x61\x73\145\137\x61\165\x74\x68\145\156\164\x69\x63\141\x74\x69\x6f\156\x5f\x67\157\x74\x6f\x5f\154\x6f\147\x69\x6e");
    ?>
                <input type="hidden" name="option" value="mo_firebase_authentication_goto_login"/>
            </form>
            <script>
            	jQuery("#phone").intlTelInput();
                jQuery('#mo_firebase_authentication_goto_login').click(function () {
                    jQuery('#mo_firebase_authentication_goto_login_form').submit();
                } );
            </script>
		<!-- <script>
			jQuery("#phone").intlTelInput();
		</script> -->
		<?php 
}
function mo_firenase_auth_show_customer_info()
{
    ?>
	<div class="mo_firebase_auth_card" style="width:100%">
		<h4>Thank you for registering with miniOrange.</h4>

		<table border="1"
		   style="background-color:#FFFFFF; border:1px solid #CCCCCC; border-collapse: collapse; padding:0px 0px 0px 10px; margin:2px; width:85%">
		<tr>
			<td style="width:45%; padding: 10px;">miniOrange Account Email</td>
			<td style="width:55%; padding: 10px;"><?php 
    echo get_option("\155\157\137\146\x69\162\145\x62\141\163\145\x5f\141\165\164\150\145\x6e\164\x69\x63\x61\164\x69\x6f\x6e\137\x61\x64\x6d\151\156\137\145\x6d\x61\x69\x6c");
    ?>
</td>
		</tr>
		<tr>
			<td style="width:45%; padding: 10px;">Customer ID</td>
			<td style="width:55%; padding: 10px;"><?php 
    echo get_option("\155\x6f\137\x66\x69\162\145\x62\x61\x73\x65\137\141\165\164\x68\x65\156\164\151\143\x61\x74\x69\x6f\156\137\141\x64\155\151\156\137\143\165\163\x74\157\x6d\145\x72\137\153\145\171");
    ?>
</td>
		</tr>
		</table>
		<br /><br />

	<table>
	<tr>
	<td>
	<form name="f1" method="post" action="" id="mo_firebase_authentication_goto_login_form">
		<input type="hidden" value="change_miniorange" name="option"/>
		<input type="submit" value="Change Email Address" class="button button-primary button-large"/>
	</form>
	</td><td>
	<!-- <a href="<?php 
    ?>
"><input type="button" class="button button-primary button-large" value="Check Licensing Plans"/></a> -->
	</td>
	</tr>
	</table>

				<br />
	</div>

	<?php 
}
