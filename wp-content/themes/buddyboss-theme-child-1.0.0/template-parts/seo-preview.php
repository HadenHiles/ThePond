<?php
defined('ABSPATH') || exit;

$pond_post_id = get_queried_object_id();
$pond_is_course = get_post_type($pond_post_id) === 'sfwd-courses';
$pond_course_id = pond_seo_course_id($pond_post_id);
$pond_summary = pond_seo_summary($pond_post_id);
$pond_lessons = $pond_is_course ? pond_seo_course_lessons($pond_post_id) : array();
$pond_goals = $pond_is_course ? pond_seo_repeater_values($pond_post_id, 'goals', 'goal', 6) : array();

get_header();
?>
<div id="primary" class="content-area bb-grid-cell">
    <main id="main" class="site-main">
        <article class="pond-seo-preview">
            <div class="pond-seo-preview__body">
                <?php if (!$pond_is_course && $pond_course_id) : ?>
                    <a class="pond-seo-preview__eyebrow" href="<?php echo esc_url(get_permalink($pond_course_id)); ?>">
                        <?php echo esc_html(get_the_title($pond_course_id)); ?>
                    </a>
                <?php else : ?>
                    <span class="pond-seo-preview__eyebrow"><?php esc_html_e('Hockey training course', 'buddyboss-theme'); ?></span>
                <?php endif; ?>
                <h1 class="pond-seo-preview__title"><?php echo esc_html(get_the_title($pond_post_id)); ?></h1>
                <?php if ($pond_summary !== '') : ?>
                    <p class="pond-seo-preview__summary"><?php echo esc_html(wp_trim_words($pond_summary, 70, '…')); ?></p>
                <?php endif; ?>
                <?php if ($pond_goals) : ?>
                    <ul class="pond-seo-preview__goals">
                        <?php foreach ($pond_goals as $pond_goal) : ?>
                            <li><?php echo esc_html($pond_goal); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <div class="pond-seo-preview__actions">
                    <a class="button pond-seo-preview__join" href="<?php echo esc_url(pond_seo_join_url($pond_post_id)); ?>">
                        <?php echo esc_html($pond_is_course ? __('Join to start this course', 'buddyboss-theme') : __('Join to watch this lesson', 'buddyboss-theme')); ?>
                    </a>
                    <a class="pond-seo-preview__login" href="<?php echo esc_url(pond_seo_login_url($pond_post_id)); ?>">
                        <?php esc_html_e('Already a member? Sign in', 'buddyboss-theme'); ?>
                    </a>
                </div>
            </div>
            <?php if (has_post_thumbnail($pond_post_id)) : ?>
                <figure class="pond-seo-preview__media">
                    <?php echo get_the_post_thumbnail($pond_post_id, 'large', array('alt' => esc_attr(get_the_title($pond_post_id)))); ?>
                    <span class="pond-seo-preview__lock" aria-hidden="true"><i class="bb-icon-l bb-icon-lock"></i></span>
                </figure>
            <?php endif; ?>
        </article>
        <?php if ($pond_lessons) : ?>
            <section class="pond-seo-preview__lessons">
                <h2><?php echo esc_html(sprintf(_n('%d lesson', '%d lessons', count($pond_lessons), 'buddyboss-theme'), count($pond_lessons))); ?></h2>
                <ol>
                    <?php foreach ($pond_lessons as $pond_lesson) : ?>
                        <li><a href="<?php echo esc_url(get_permalink($pond_lesson)); ?>"><?php echo esc_html(get_the_title($pond_lesson)); ?></a></li>
                    <?php endforeach; ?>
                </ol>
            </section>
        <?php endif; ?>
    </main>
</div>
<?php
get_footer();
