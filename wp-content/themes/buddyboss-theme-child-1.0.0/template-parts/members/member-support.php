<?php ?>
<main role="main">
    <article>
        <?php if (get_field('sub_header')) : ?>
            <h2><?php the_field('sub_header'); ?></h2>
        <?php endif; ?>

        <?php if (get_field('above_media')) : ?>
            <?php the_field('above_media'); ?>
        <?php endif; ?>
        <?php if (get_field('faq_sub_heading')) : ?>
            <p class="pond-support-intro"><?php the_field('faq_sub_heading'); ?></p>
        <?php endif; ?>
        <?php if (get_field('below_media')) : ?>
            <?php the_field('below_media'); ?>
        <?php endif; ?>
        <section class="pond-support-faq pond-faq" aria-labelledby="pond-support-faq-title">
            <h2 id="pond-support-faq-title">Common questions</h2>
            <?php get_template_part('template-parts/content-blocks/support-questions'); ?>
        </section>
        <?php
        $FormID = get_field('form_id');
        ?>
        <div class="membSupportForm">
            <?php echo do_shortcode('[wpforms id="' . $FormID .  '" title="false"]'); ?>
        </div>
    </article>
</main>