<?php


class Mo_Firebase_Authentication_Admin_LoginSettings
{
    public static function mo_firebase_authentication_loginsettings()
    {
        ?>
	<div class="row">
		<div class="col-md-12">
			<div class="mo_firebase_auth_card" style="width:90%;font-size:14px;">
				<h6 style="display: inline;"><b>Sign in options</b></h6><br><br>
				<h8>Option 1: Use a Login button on WordPress default Login Form for different providers login method.</h8>
				<ol>
					<li>Go to Advanced Settings tab.</li>
					<li>Select <b>"Show Login button on WP login page"</b>.</li>
				</ol>
				<h8>Option 2: Use a Shortcode </h8>
				<ul>
					<li>Place shortcode <b>[mo_firebase_auth_login]</b> in WordPress pages or posts.</li>
				</ul>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-12">
			<div class="mo_firebase_auth_card" style="width:90%;font-size:14px;">
				<h6 style="display: inline;"><b>Advanced Sign in options</b></h6><br><br>
				<form name="f" method="post" action="">
					<?php 
        wp_nonce_field("\155\157\x5f\146\151\x72\x65\142\x61\163\145\137\x61\165\x74\x68\137\163\x69\147\156\137\x69\x6e\x5f\157\160\x74\x69\x6f\x6e\137\x66\x6f\x72\155", "\155\x6f\x5f\x66\x69\x72\x65\x62\141\x73\x65\x5f\141\x75\164\150\x5f\163\x69\147\156\x5f\x69\156\137\157\160\x74\x69\x6f\156\x5f\146\x69\145\154\x64");
        ?>
					<input type="hidden" name="option" value="mo_firebase_authentication_sign_in_option" />
					<div class="row">
						<div class="col-md-6">
							<p style="margin-bottom: 3px"><b>Custom redirect URL after login </b></p>
							<p>(Keep blank in case you want users to redirect to page from where SSO originated)</p>
						</div>
						<div class="col-md-6">
						<input name="custom_after_login_url" value="<?php 
        if (!(get_option("\x6d\x6f\x5f\146\151\162\145\142\x61\x73\145\x5f\141\x75\164\150\137\x63\165\x73\x74\x6f\x6d\x5f\141\146\x74\x65\x72\137\154\x6f\x67\151\156\x5f\165\162\154") !== false)) {
            goto TT;
        }
        echo get_option("\155\x6f\137\146\x69\162\x65\142\x61\163\145\137\141\165\164\x68\x5f\143\165\x73\x74\157\155\137\x61\x66\164\x65\162\137\154\157\x67\x69\x6e\x5f\165\162\154");
        TT:
        ?>
" pattern="https?://.+" title="Include https://" placeholder="https://" style="width:100%;" type="url">
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<p style="margin-bottom: 3px"><b>Custom redirect URL after logout </b></p>
						</div>
						<div class="col-md-6">
						<input name="custom_after_logout_url" value="<?php 
        if (!(get_option("\x6d\157\137\x66\151\162\145\x62\141\x73\145\137\x61\x75\x74\150\x5f\143\165\x73\x74\x6f\x6d\x5f\141\x66\x74\145\x72\x5f\x6c\x6f\147\x6f\x75\164\x5f\x75\162\x6c") !== false)) {
            goto WL;
        }
        echo get_option("\155\x6f\x5f\146\x69\162\x65\142\141\x73\145\x5f\x61\165\164\x68\x5f\143\x75\x73\x74\157\155\x5f\141\146\x74\145\x72\x5f\154\x6f\x67\157\x75\x74\x5f\x75\x72\154");
        WL:
        ?>
" pattern="https?://.+" title="Include https://" placeholder="https://" style="width:100%;" type="url">
						</div>
					</div>
					<br>
					<div class="row">
						<div class="col-md-12">
							<input type="submit" class="btn btn-primary" style="width:170px;height:40px" name="verify_user" value="Save Configuration" id = "mo_auth_configure_button"onclick="showAlert();">
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<?php 
    }
}
