<?php
/* Template Name: Firebase Login */



// Resolve the requested destination before the member header sends output.
nocache_headers();
if (!defined('DONOTCACHEPAGE')) {
    define('DONOTCACHEPAGE', true);
}
$is_password_reset = isset($_GET['action']) && $_GET['action'] === 'forgot_password';
$login_destination = pond_theme_login_destination();
if (!empty($_GET['redirect_to'])) {
    if (!is_string($_GET['redirect_to'])) {
        wp_die('Invalid login destination.', '', array('response' => 400));
    }
    $login_destination = pond_theme_login_destination(wp_unslash($_GET['redirect_to']));
    setcookie('redirect_to', $login_destination, time() + (3600 * 30), '/');
}

if (is_user_logged_in() && !$is_password_reset) {
    wp_safe_redirect($login_destination);
    exit;
}

get_header(); ?>
<div class="pond-portal">
<?php
if (has_post_thumbnail()) {
    $imgID  = get_post_thumbnail_id($post->ID);
    $img    = wp_get_attachment_image_src($imgID, 'full', false, '');
    $imgAlt = get_post_meta($imgID, '_wp_attachment_image_alt', true);
}

?>


<!-- Main Section -->
<section class="pond-auth-shell" id="firebase-login-page">
    <!--Need this ID to differentiate between firebase sign up and firebase login-->
        <?php
        if (have_posts()) : while (have_posts()) : the_post();
        ?>
                    <main class="pond-auth-card">
                        <article>
                            <?php
                            if (!is_user_logged_in()) {
                            ?>
                                <h1>
                                    <?php if ($is_password_reset) { esc_html_e('Reset your password', 'buddyboss-theme-child'); } else { the_title(); } ?>
                                </h1>
                                <p class="pond-auth-intro"><?php
                                    esc_html_e($is_password_reset
                                        ? 'Enter your email below to request a password reset.'
                                        : 'Sign in to continue your training and connect with The Pond community.', 'buddyboss-theme-child');
                                ?></p>
                                <p id="login-error" style="color: #cc3333; display: none;">Error logging in.</p>
                                <div class="bootstrap-styles transparent-modal">
                                    <div class="modal fade confirm-create-account-modal" id="confirm-create-account-modal" tabindex="-1" role="dialog" aria-labelledby="confirm-create-account-modal" aria-hidden="true">
                                        <div class="modal-dialog modal-lg" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h2 class="modal-title" id="skillVaultModalLabel">Account does not exist</h2>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="medium-12 columns">
                                                        <p>No account exists for the email <span id="provider-email"></span></p>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <a class="BTN" href="#" id="cancel-create-account" data-dismiss="modal" aria-label="Close">Try another login method</a>
                                                    <a class="BTN action loginBTN" href="#" id="continue-create-account">Create Account</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php if (!$is_password_reset) : ?>
                                <div class="pond-auth-providers">
                                <button type="button" class="google-btn pond-auth-provider" id="mo_firebase_Google_provider_login">
                                    <span class="google-icon-wrapper">
                                        <img class="google-icon" alt="" src="https://cdn.thepond.howtohockey.com/2020/09/Google__G__Logo.svg">
                                    </span>
                                    <span class="btn-text">Sign in with Google</span>
                                </button>
                                <button type="button" class="facebook-btn pond-auth-provider" id="mo_firebase_Facebook_provider_login">
                                    <span class="facebook-icon-wrapper">
                                        <svg class="svg-inline--fa fa-facebook-f fa-w-9" aria-hidden="true" data-prefix="fab" data-icon="facebook-f" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 264 512" data-fa-i2svg="">
                                            <path fill="currentColor" d="M76.7 512V283H0v-91h76.7v-71.7C76.7 42.4 124.3 0 193.8 0c33.3 0 61.9 2.5 70.2 3.6V85h-48.2c-37.8 0-45.1 18-45.1 44.3V192H256l-11.7 91h-73.6v229"></path>
                                        </svg><!-- <i class="fab fa-facebook-f"></i> -->
                                    </span>
                                    <span class="btn-text">Sign in with Facebook</span>
                                </button>
                                </div>
                                <?php endif; ?>
                                <div class="pond-auth-email<?php echo $is_password_reset ? ' pond-auth-reset' : ''; ?>">
                                    <?= do_shortcode('[mepr-login-form use_redirect="true"]') ?>
                                    <?php do_action('mo_custom_login_form_end', 'user_login', 'user_pass', 'wp-submit'); ?>
                                </div>
                                <div style="display: none;">
                                    <?= do_shortcode('[mo_firebase_auth_login]') ?>
                                </div>
                            <?php
                                the_content();
                                ?>
                                <p class="pond-auth-signup"><?php esc_html_e('Not a member yet?', 'buddyboss-theme-child'); ?> <a href="<?php echo esc_url(home_url('/#pricingSect')); ?>"><?php esc_html_e('View memberships', 'buddyboss-theme-child'); ?></a></p>
                                <?php
                            } else {
                                $user = get_user_by('id', get_current_user_id());
                            ?>
                                <div style="text-align: center;">
                                    <?php
                                    if (!current_user_can('memberpress_authorized')) {
                                    ?>
                                        <div class="bootstrap-styles">
                                            <div class="alert alert-danger" role="alert">
                                                In order to access this content you must have an active subscription. <a href="/account?action=subscriptions">Subscriptions</a>
                                            </div>
                                        </div>
                                    <?php
                                    }
                                    ?>
                                    <h2><span style="font-size: .6em;">Currently signed in as</span> <?php echo esc_html($user->user_login); ?></h2>
                                        <a href="/member-dashboard" class="BTN darkblue">Dashboard</a>
                                        <a href="/account" class="BTN blue">Account</a>
                                        <a href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>" class="BTN logout">Logout</a>
                                </div>
                            <?php
                            }
                            ?>
                        </article>
                    </main>
        <?php
            endwhile;
        endif;
        ?>
</section>
<!-- End Main Section -->

<!-- Full Width Section -->
<?php if (get_field('show_full_width_section') == 'yes') : ?>
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
?>
</div>
<?php get_footer();
?>