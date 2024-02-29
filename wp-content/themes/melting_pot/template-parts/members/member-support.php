<?php ?>
<main role="main">
    <article>
        <?php if (get_field('sub_header')) : ?>
            <h2><?php echo get_field('sub_header'); ?></h2>
        <?php endif; ?>

        <?php if (get_field('above_media')) : ?>
            <?php echo get_field('above_media'); ?>
        <?php endif; ?>
        <div class="faqs">
            <?php get_template_part('template-parts/content-blocks/accordian'); ?>
        </div>
        <?php if (get_field('below_media')) : ?>
            <?php echo get_field('below_media'); ?>
        <?php endif; ?>
        <?php
        $FormID = get_field('form_id');
        ?>
        <div class="membSupportForm">
            <?php echo do_shortcode('[wpforms id="' . $FormID .  '" title="false"]'); ?>
        </div>
    </article>
</main>