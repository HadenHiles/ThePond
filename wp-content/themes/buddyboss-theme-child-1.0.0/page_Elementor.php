<?php
/* Template Name: Elementor with Head & Footer */
get_header();
?>
<main class="content-area">
    <?php while (have_posts()) : the_post(); ?>
        <?php the_content(); ?>
    <?php endwhile; ?>
</main>
<?php get_footer(); ?>
