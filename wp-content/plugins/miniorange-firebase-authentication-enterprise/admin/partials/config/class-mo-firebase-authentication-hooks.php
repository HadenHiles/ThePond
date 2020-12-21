<?php


class Mo_Firebase_Authentication_Admin_Hooks
{
    public static function mo_firebase_authentication_hooks()
    {
        ?>
		<div class="row">
			<div class="col-md-12">
				<div class="mo_firebase_auth_card" style="width:90%">
			    	We provide multiple hooks to extend the plugin functionality.<br><br>
			    	<div id="mo_firebase_authentication_hooks_table">
			    		<div id="mo_firebase_authentication_hook_item">
					    	<code>mo_firebase_wplogin_form_start</code><br>
							<p id="mo_firebase_authentication_hook_item_desc">Used to perform action before WordPress login form.</p>
						</div>
						<br>
						<div id="mo_firebase_authentication_hook_item">	
							<code>mo_firebase_wplogin_form_end</code><br>
							<p id="mo_firebase_authentication_hook_item_desc">Used to perform action when WordPress login form ends.</p>
						</div>
						<br>
						<div id="mo_firebase_authentication_hook_item">
							<code>mo_firebase_user_attributes</code><br>
							<p id="mo_firebase_authentication_hook_item_desc">Used to fetch user attributes received from firebase.</p>
						</div>
						<br>
						<div id="mo_firebase_authentication_hook_item">
							<code>mo_firebase_get_jwt_token</code><br>
							<p id="mo_firebase_authentication_hook_item_desc">Used to fetch and store JWT token received from firebase.</p>
						</div>
						<br>
						<div id="mo_firebase_authentication_hook_item">
							<code>mo_firebase_enqueue_initialize_scripts</code><br>
							<p id="mo_firebase_authentication_hook_item_desc">You can use this hook in your code to initialize firebase.</p>
						</div>
						<br>
					</div>
				</div>
			</div>
		</div>
		<?php 
    }
}
