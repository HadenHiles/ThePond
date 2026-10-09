<?php
/* Template Name: Member Support */
get_header();
?>
<div class="pond-portal">
    <h1><?php the_title(); ?></h1>
    <?php while (have_posts()) : the_post(); ?>
        <div class="row">
            <div class="<?php echo get_field('hide_sidebar') === 'hideSidebar' ? 'large-12' : 'large-8'; ?> columns">
                <?php get_template_part('template-parts/members/member-support'); ?>
            </div>
            <?php if (get_field('hide_sidebar') === 'keepSidebar') : ?>
                <div class="large-4 columns">
                    <?php the_field('sidebar_content'); ?>
                    <?php get_template_part('template-parts/members/member-sidebar'); ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
</div>
<?php get_footer(); ?>
