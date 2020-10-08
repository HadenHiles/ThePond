<?php
/* Template Name: Firebase Login */

global $smof_data;
get_header("members");
if(has_post_thumbnail()) {
$imgID  = get_post_thumbnail_id($post->ID);
$img    = wp_get_attachment_image_src($imgID,'full', false, ''); 
$imgAlt = get_post_meta($imgID,'_wp_attachment_image_alt', true);
}
?>
<?php get_template_part ('template-parts/bannerheader'); ?>
</header>
<!-- Main Section -->
<section class="MainContent">
<div class="row">
	<?php 
	if(have_posts()): while (have_posts()): the_post();
        ?>
        <div class="large-4 columns">&nbsp;</div>
        <div class="large-4 columns">
            <main role="main">    
                <article>
                    <?php
                    if (!is_user_logged_in()) {
                        ?>
                        <h1><?the_title();?></h1>
                        <div class="google-btn" id="mo_firebase_Google_provider_login">
                            <div class="google-icon-wrapper">
                                <img class="google-icon" src="https://cdn.thepond.howtohockey.com/2020/09/Google__G__Logo.svg">
                            </div>
                            <p class="btn-text"><b>Sign in with Google</b></p>
                        </div>
                        <div class="facebook-btn" id="mo_firebase_Facebook_provider_login">
                            <div class="facebook-icon-wrapper">
                                <svg class="svg-inline--fa fa-facebook-f fa-w-9" aria-hidden="true" data-prefix="fab" data-icon="facebook-f" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 264 512" data-fa-i2svg="">
                                    <path fill="currentColor" d="M76.7 512V283H0v-91h76.7v-71.7C76.7 42.4 124.3 0 193.8 0c33.3 0 61.9 2.5 70.2 3.6V85h-48.2c-37.8 0-45.1 18-45.1 44.3V192H256l-11.7 91h-73.6v229"></path>
                                </svg><!-- <i class="fab fa-facebook-f"></i> -->
                            </div>
                            <p class="btn-text"><b>Sign in with Facebook</b></p>
                        </div>
                        <div class="content" style="width: 100%; float: left; margin-top: 20px; padding-top: 10px; border-top: 2px solid #ccc;">
                            <?=do_shortcode('[mepr-login-form use_redirect="true"]')?>
                            <?php do_action('mo_custom_login_form_end', 'user_login', 'user_pass', 'wp-submit'); ?>
                        </div>
                        <div style="display: none;">
                            <?=do_shortcode('[mo_firebase_auth_login]') ?>
                        </div>
                        <?php
                        the_content();
                    } else {
                        $user = get_user_by('id', get_current_user_id());
                        ?>
                        <div style="text-align: center;">
                            <?php
                            if (!current_user_can('memberpress_authorized')) {
                                ?>
                                <div class="bootstrap-styles">
                                    <div class="alert alert-danger" role="alert">
                                        In order to access this content you must have an active subscription. <a href="/account?action=subscriptions">Manage Subscriptions</a>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>
                            <h2><span style="font-size: .6em;">Currently signed in as</span> <?=$user->user_login?></h1>
                            <a href="#" class="BTN logout">Logout</a>
                        </div>
                        <?php
                    }
                    ?>
                </article>
            </main>
        </div>
        <div class="large-4 columns">&nbsp;</div>
        <?php 
    endwhile; endif;
    ?>
</div>
</section>
<!-- End Main Section -->

<!-- Full Width Section -->
<?php  if ( get_field('show_full_width_section') == 'yes'): ?>		
<section class="fullwidth">
<div class="row">
<div class="large-12 columns">
<?php the_field('full_width_section'); ?>   
</div>
</div>
</section>
<?php endif; ?>
<!-- End Full Width Section -->


<!-- Testimonial Section -->
<?php 
get_footer("members");
?>