<?
/***  Template Name: Member Register */ 
get_header("members");

$cookie_name = "selected_membership";
$cookie_value = get_permalink();
setcookie($cookie_name, $cookie_value, time() + (3600 * 30), "/"); // 3600 = 1 hour in seconds

?>
</header>
<section class="login-contain">
    <div class="fullWidth">
        <div class="row">
            <? if(have_posts()): while (have_posts()): the_post(); ?>
            <div class="large-8 medium-8 columns">
                <main>

                    <div style="width: 100%;">
                        <h1 style="margin-top: 25px;float: left;"><?php the_title(); ?></h1>
                        <h2 style="float: left; margin: 35px 15px; color: #cc3333;">$<?= get_field('_mepr_product_price', $post->ID) ?></h2>
                    </div>
                    <div style="clear: both;"></div>
                    <?php
                    if (!is_user_logged_in()) {
                    ?>
                        <h4 style="margin-top: 10px; float: left; width: 100%; font-size: 24px;">Choose how you would like to login to The Pond</h4>
                        <div style="clear: both;"></div>
                        <div style="display: inline-block; width: 100%;">
                            <div class="google-btn" id="mo_firebase_Google_provider_login">
                                <div class="google-icon-wrapper">
                                    <img class="google-icon" src="https://cdn.thepond.howtohockey.com/2020/09/Google__G__Logo.svg">
                                </div>
                                <p class="btn-text"><b>Sign up with Google</b></p>
                            </div>
                            <div class="facebook-btn" id="mo_firebase_Facebook_provider_login">
                                <div class="facebook-icon-wrapper">
                                    <svg class="svg-inline--fa fa-facebook-f fa-w-9" aria-hidden="true" data-prefix="fab" data-icon="facebook-f" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 264 512" data-fa-i2svg="">
                                        <path fill="currentColor" d="M76.7 512V283H0v-91h76.7v-71.7C76.7 42.4 124.3 0 193.8 0c33.3 0 61.9 2.5 70.2 3.6V85h-48.2c-37.8 0-45.1 18-45.1 44.3V192H256l-11.7 91h-73.6v229"></path>
                                    </svg><!-- <i class="fab fa-facebook-f"></i> -->
                                </div>
                                <p class="btn-text"><b>Sign up with Facebook</b></p>
                            </div>
                            <div style="display: none;">
                                <?= do_shortcode('[mo_firebase_auth_login]'); ?>
                            </div>
                            <div class="email-btn" id="signup-with-email-btn">
                                <div class="email-icon-wrapper">
                                    <i class="fa fa-envelope"></i>
                                </div>
                                <p class="btn-text"><b>Sign up with Email</b></p>
                            </div>
                        </div>

                        <div id="register-form-wrapper" style="display: none; width: 100%; margin-top: 25px; border-top: 2px solid #ccc;">
                            <?php the_content(); ?>
                        </div>
                    <?php
                    } else {
                        the_content();
                    }
                    ?>

                    <div class="trustIcons"></div>

                </main>
            </div>
            <div class="large-4 medium-4 columns">

                <div class="memberSideBar">

                    <?php get_template_part('template-parts/members/member-sidebar'); ?>

                </div>



            </div>
            <? endwhile; endif;?>
        </div>
    </div><!-- End Main Content -->
</section>
<?
get_footer("members");
?>