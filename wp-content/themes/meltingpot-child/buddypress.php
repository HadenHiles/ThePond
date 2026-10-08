<?php
defined('ABSPATH') || exit;

if (!function_exists('thepond_community_is_page')) {
    require __DIR__ . '/page.php';
    return;
}

get_header('members');
?>
</header>
<section class="MainContent thepond-community-shell">
    <div class="row">
        <div class="large-12 columns">
            <main id="thepond-community-content">
                <?php while (have_posts()) : the_post(); ?>
                    <?php the_content(); ?>
                <?php endwhile; ?>
            </main>
        </div>
    </div>
</section>
<?php get_footer('members'); ?>
