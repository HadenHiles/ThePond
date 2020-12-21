<?php


function mo_firebase_auth_verify_password_ui()
{
    ?>
		<form name="f" method="post" action="">
			<input type="hidden" name="option" value="mo_firebase_authentication_verify_customer" />
			<div class="mo_firebase_auth_card" style="width:100%">
				<!-- <div id="toggle1" class="mo_panel_toggle"> -->
					<h4>Login with miniOrange</h4>
				<!-- </div> -->
				<p style="font-size: 13px"><b>It seems you already have an account with miniOrange. Please enter your miniOrange email and password.<br/> <a href="#mo_firebase_authentication_forgot_password_link">Click here if you forgot your password?</a></b>
				</p>

				<!-- <div id="panel1"> -->
					<table class="mo_settings_table">
						<tr>
							<td><b><font color="#FF0000">*</font>Email:</b></td>
							<td><input class="mo_table_textbox" type="email" name="email"
								required placeholder="person@example.com"
								value="<?php 
    echo get_option("\x6d\x6f\x5f\146\x69\162\145\x62\x61\x73\x65\137\x61\165\164\150\145\156\164\x69\143\x61\x74\x69\157\x6e\137\x61\144\x6d\151\156\137\x65\155\141\151\x6c");
    ?>
" /></td>
						</tr>
						<td><b><font color="#FF0000">*</font>Password:</b></td>
						<td><input class="mo_table_textbox" required type="password"
							name="password" placeholder="Choose your password" /></td>
						</tr>
						</table>
						<br>
						<div>
							<input style="margin-left:30%;width:16%" type="submit" name="submit" value="Login"
								class="button button-primary button-large" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</form>

							<input style="margin-left:2%;width:16%" type="button" name="back-button" id="mo_firebase_authentication_back_button" onclick="document.getElementById('mo_firebase_authentication_change_email_form').submit();" value="Back" class="button button-primary button-large" />
					
							<form id="mo_firebase_authentication_change_email_form" method="post" action="">
								<input type="hidden" name="option" value="mo_firebase_authentication_change_email" />
							</form>
						</div>
				</div>
			<!-- </div>
 -->
		<!-- <form name="f" method="post" action="" id="mo_firebase_authentication_forgotpassword_form">
			<input type="hidden" name="option" value="mo_firebase_authentication_forgot_password_form_option"/>
		</form> -->
		<script>
			jQuery("a[href=\"#mo_firebase_authentication_forgot_password_link\"]").click(function(){
				window.open('https://login.xecurify.com/moas/idp/resetpassword');
				//jQuery("#mo_firebase_authentication_forgotpassword_form").submit();
			});
		</script>
		<?php 
}
