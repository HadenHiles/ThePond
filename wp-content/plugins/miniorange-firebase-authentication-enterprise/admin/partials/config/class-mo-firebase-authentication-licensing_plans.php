<?php


class Mo_Firebase_Authentication_Admin_Licensing_Plans
{
    public static function mo_firebase_authentication_licensing_plans()
    {
        ?>
			<!-- Important JSForms -->
	        <input type="hidden" value="<?php 
        echo mo_firebase_authentication_is_customer_registered();
        ?>
" id="mo_customer_registered">
	        <form style="display:none;" id="loginform"
	              action="<?php 
        echo get_option("\x68\157\163\x74\137\156\x61\x6d\x65") . "\x2f\x6d\157\x61\x73\57\x6c\x6f\x67\151\156";
        ?>
"
	              target="_blank" method="post">
	            <input type="email" name="username" value="<?php 
        echo get_option("\x6d\157\137\146\151\x72\x65\x62\x61\x73\145\x5f\141\165\x74\x68\x65\x6e\x74\151\x63\x61\164\151\157\x6e\x5f\141\144\155\151\156\137\145\x6d\x61\x69\x6c");
        ?>
"/>
	            <input type="text" name="redirectUrl"
	                   value="<?php 
        echo get_option("\x68\157\163\x74\x5f\x6e\x61\x6d\x65") . "\57\x6d\157\141\x73\57\151\156\151\164\x69\x61\154\151\172\145\160\x61\x79\155\x65\x6e\x74";
        ?>
"/>
	            <input type="text" name="requestOrigin" id="requestOrigin"/>
	        </form>
	        <form style="display:none;" id="viewlicensekeys"
	              action="<?php 
        echo get_option("\150\x6f\163\164\x5f\x6e\x61\155\145") . "\x2f\x6d\157\141\x73\57\x6c\157\147\151\x6e";
        ?>
"
	              target="_blank" method="post">
	            <input type="email" name="username" value="<?php 
        echo get_option("\155\157\x5f\146\151\x72\145\142\x61\x73\x65\137\141\x75\164\x68\145\x6e\164\151\x63\141\x74\151\x6f\156\x5f\x61\x64\x6d\x69\x6e\137\x65\155\x61\x69\x6c");
        ?>
"/>
	            <input type="text" name="redirectUrl"
	                   value="<?php 
        echo get_option("\150\x6f\163\x74\137\x6e\x61\155\145") . "\x2f\x6d\x6f\141\x73\57\166\x69\145\167\154\151\143\x65\156\163\145\x6b\x65\171\163";
        ?>
"/>
	        </form>
	        <!-- End Important JSForms -->
			<div class="row">
				<div class="col-1 moct-align-center">
				</div>
				<div class="col-5 moct-align-center">
					<div class="moc-licensing-plan card-body">
					    <div class="moc-licensing-plan-header">
					        <div class="moc-licensing-plan-name"><h2>Premium</h2></div>
					    </div><br>
					    <div class="moc-licensing-plan-price"><sup>$</sup>149<sup>*</sup></div>
					    <!-- <a class="btn btn-block btn-info text-uppercase moc-lp-buy-btn" href="mailto:info@xecurify.com" target="_blank">Contact Us</a> -->
					    <button class="btn btn-block btn-info text-uppercase moc-lp-buy-btn" onclick="upgradeform('wp_oauth_firebase_authentication_premium_plan')">Buy Now</button>
					    <br>
					    <div class="moc-licensing-plan-feature-list">
					        <ul>
					        	<li>&#9989; Allow login with Firebase and WordPress</li>
					        	<li>&#9989; Advanced Attribute mapping</li>
					            <li>&#9989; Auto register users in Firebase as well as WordPress</li>
					            <li>&#9989; Login & Registeration Form Integration (WooCommerce, BuddyPress)</li>
					            <li>&#9989; Custom redirect URL after Login and Logout</li>
					        </ul>
					    </div>
					</div>
				</div>
				<div class="col-5 moct-align-center">
					<div class="moc-licensing-plan card-body">
					    <div class="moc-licensing-plan-header">
					        <div class="moc-licensing-plan-name"><h2>Enterprise</h2></div>
					    </div><br>
					    <div class="moc-licensing-plan-price"><sup>$</sup>249<sup>*</sup></div>
					    <!-- <a class="btn btn-block btn-purple text-uppercase moc-lp-buy-btn" href="mailto:info@xecurify.com" target="_blank">Contact Us</a> -->
					    <button class="btn btn-block btn-purple text-uppercase moc-lp-buy-btn" onclick="upgradeform('wp_oauth_firebase_authentication_enterprise_plan')">Buy Now</button>
					    <br>
					    <div class="moc-licensing-plan-feature-list">
					        <ul>
					        	<li>&#9989; Allow login with Firebase and WordPress</li>
					        	<li>&#9989; Advanced Attribute mapping</li>
					            <li>&#9989; Auto register users in Firebase as well as WordPress</li>
					            <li>&#9989; Login & Registeration Form Integration (WooCommerce, BuddyPress)</li>
					            <li>&#9989; Custom redirect URL after Login and Logout</li>
					            <li>&#9989; Shortcode to add Firebase Login Form</li>
					            <li>&#9989; Firebase Authentication methods <br>Google, Facebook, Github, Twitter, Phone</li>
					            <li>&#9989; WP hooks to read Firebase token, login event and extend plugin functionality</li>
					        </ul>
					    </div>
					</div>
				</div>
			</div>
			<!-- End Licensing Table -->
	        <a id="mobacktoaccountsetup" style="display:none;" href="<?php 
        echo add_query_arg(array("\x74\x61\x62" => "\x61\x63\143\157\x75\156\x74"), htmlentities($_SERVER["\122\x45\121\125\105\x53\x54\137\125\x52\111"]));
        ?>
">Back</a>
	        <!-- JSForms Controllers -->
			<script>
				function upgradeform(planType) {
		                if(planType === "") {
		                    location.href = "https://wordpress.org/plugins/firebase-authentication/";
		                    return;
		                } else {
		                    jQuery('#requestOrigin').val(planType);
		                    if(jQuery('#mo_customer_registered').val()==1)
		                        jQuery('#loginform').submit();
		                    else{
		                        location.href = jQuery('#mobacktoaccountsetup').attr('href');
		                    }
		                }

		            }
			</script>
			<?php 
    }
}
