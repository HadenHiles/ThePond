<?php
/*
 * Template Name: Member Register
 * Template Post Type: page, mepr-product
 */
if (!is_user_logged_in()) {
    setcookie('selected_membership', get_permalink(get_queried_object_id()), time() + (3600 * 30), '/');
}
get_header(); ?>
<div class="pond-portal">
<?php
?>

<section class="pond-auth-shell pond-signup-shell">
        <div class="pond-signup-layout">
            <?php  if(have_posts()): while (have_posts()): the_post(); ?>
                <main class="pond-auth-card pond-signup-card">

                    <header class="pond-signup-heading">
                        <h1><?php the_title(); ?></h1>
                        <p class="pond-signup-price">$<?php echo esc_html(get_field('_mepr_product_price', $post->ID)); ?></p>
                    </header>
                    <?php
                    if (!is_user_logged_in()) {
                        ?>
                        <p class="pond-auth-intro"><?php esc_html_e('Choose how to create your account and join The Pond.', 'buddyboss-theme-child'); ?></p>
                        <div class="pond-auth-providers">
                            <button type="button" class="google-btn pond-auth-provider" id="mo_firebase_Google_provider_login">
                                <span class="google-icon-wrapper">
                                    <img class="google-icon" alt="" src="https://cdn.thepond.howtohockey.com/2020/09/Google__G__Logo.svg">
                                </span>
                                <span class="btn-text">Sign up with Google</span>
                            </button>
                            <button type="button" class="facebook-btn pond-auth-provider" id="mo_firebase_Facebook_provider_login">
                                <span class="facebook-icon-wrapper">
                                    <svg class="svg-inline--fa fa-facebook-f fa-w-9" aria-hidden="true" data-prefix="fab" data-icon="facebook-f" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 264 512" data-fa-i2svg="">
                                        <path fill="currentColor" d="M76.7 512V283H0v-91h76.7v-71.7C76.7 42.4 124.3 0 193.8 0c33.3 0 61.9 2.5 70.2 3.6V85h-48.2c-37.8 0-45.1 18-45.1 44.3V192H256l-11.7 91h-73.6v229"></path>
                                    </svg><!-- <i class="fab fa-facebook-f"></i> -->
                                </span>
                                <span class="btn-text">Sign up with Facebook</span>
                            </button>
                            <div style="display: none;">
                                <?= do_shortcode('[mo_firebase_auth_login]'); ?>
                            </div>
                            <button type="button" class="email-btn pond-auth-provider" id="signup-with-email-btn">
                                <span class="btn-text">Sign up with Email</span>
                            </button>
                        </div>

                        <div id="register-form-wrapper" class="pond-signup-form-wrapper" style="display: none;">
                            <?php the_content(); ?>
                        </div>
                    <?php
                    } else {
                        echo '<div class="pond-signup-form-wrapper">';
                        the_content();
                        echo '</div>';
                    }
                    ?>

                    <div class="trustIcons"></div>
                    <?php if (!is_user_logged_in()) : ?>
                    <p class="pond-auth-signup"><?php esc_html_e('Already a member?', 'buddyboss-theme-child'); ?> <a href="<?php echo esc_url(home_url('/login/')); ?>"><?php esc_html_e('Sign in', 'buddyboss-theme-child'); ?></a></p>
                    <?php endif; ?>

                </main>
            <?php if (get_field('sidebar') || get_field('sidebar_two')) : ?>
                <aside class="pond-signup-benefits memberSideBar">

                    <?php get_template_part('template-parts/members/member-sidebar'); ?>

                </aside>
            <?php endif; ?>
            <?php  endwhile; endif;?>
        </div>
</section>
<?php
?>
</div>
<?php get_footer();
?>