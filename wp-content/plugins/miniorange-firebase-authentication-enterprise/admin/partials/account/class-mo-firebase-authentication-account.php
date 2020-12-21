<?php


require "\x6c\157\147\151\156\x2f\162\145\x67\151\163\164\145\x72\x2e\160\150\160";
require "\x6c\x6f\147\x69\x6e\57\166\145\162\x69\x66\x79\55\x70\141\x73\x73\167\157\x72\144\56\x70\x68\160";
class Mo_Firebase_Authentication_Admin_Account
{
    public static function verify_password()
    {
        mo_firebase_auth_verify_password_ui();
    }
    public static function register()
    {
        if (!mo_firebase_authentication_is_customer_registered()) {
            goto YG;
        }
        mo_firenase_auth_show_customer_info();
        goto Um;
        YG:
        mo_firebase_auth_register_ui();
        Um:
    }
    public static function mo_firebase_authentication_lp()
    {
        $pW = '';
        if (!isset($_POST["\155\157\x5f\146\151\162\145\142\x61\x73\x65\x5f\x61\x75\x74\x68\x65\156\164\151\143\x61\x74\151\157\x6e\x5f\154\x69\143\145\x6e\x73\x65\137\x6b\x65\x79"])) {
            goto w_;
        }
        $pW = $_POST["\x6d\157\x5f\146\151\162\x65\x62\141\163\145\137\141\x75\164\x68\145\x6e\x74\x69\x63\141\164\151\x6f\x6e\137\154\151\143\145\156\163\145\137\x6b\145\x79"];
        w_:
        ?>

			<div class="mo_firebase_auth_card" style="width:100%">
				<h4>Verify your license</h4>
				<br>
				<form name="f" method="post" action="">
					<input type="hidden" name="option" value="mo_firebase_authentication_verify_license" />
					<table class="mo_settings_table">
						<tr>
							<td><b><font color="#FF0000">*</font>License Key:</b></td>
							<td><input style="width:350px;" required type="text" name="mo_firebase_authentication_license_key" placeholder="Enter your license key to activate the plugin" value="<?php 
        echo $pW;
        ?>
" /></td>
						</tr>
					</table>
					<br>
					<input style="margin-left:30%;width:16%" type="submit" name="submit" value="Activate License" class="button button-primary button-large" />
					<br><br>
				</form>

			</div>

		<?php 
    }
}
