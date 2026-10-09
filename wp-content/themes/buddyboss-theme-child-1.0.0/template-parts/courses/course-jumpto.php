<?php
if (!have_rows('video_jump_to')) {
    return;
}
wp_enqueue_script('pond-vimeo', 'https://player.vimeo.com/api/player.js', array(), null, true);
?>
<h4><?php echo esc_html(get_field('video_jump_to_title') ?: 'Looking for something specific?'); ?></h4>
<div class="pond-jump-links">
    <?php while (have_rows('video_jump_to')) : the_row(); ?>
        <button type="button" data-jumptime="<?php echo esc_attr(get_sub_field('jump_to_time')); ?>">
            <?php echo esc_html(get_sub_field('jump_to_name')); ?>
            (<?php echo esc_html(get_sub_field('jump_to_time')); ?> secs)
        </button>
    <?php endwhile; ?>
    <span class="pond-jump-error" role="status"></span>
</div>
