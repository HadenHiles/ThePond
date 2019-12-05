<?php
get_header('members');
if(has_post_thumbnail()) {
$imgID  = get_post_thumbnail_id($post->ID);
$img = wp_get_attachment_url(get_post_thumbnail_id($post->ID),"Full"); 
$imgAlt = get_post_meta($imgID,'_wp_attachment_image_alt', true);
}
$course_id = learndash_get_course_id();
?>	
</header>

<?php get_template_part('template-parts/courses/course-header'); ?>

<section class="MembersContent">
<div class="row">
<?php if(have_posts()):   the_post();?>
<div class="medium-8 columns">
<div class="CourseContent">
<main role="main">
<article>
<?php get_template_part('template-parts/courses/lesson-topic-fields'); ?>

<?php get_template_part('template-parts/courses/lesson-downloads'); ?>
<?php get_template_part('template-parts/courses/coursehistory'); ?>	
<?php the_content();?>
</div>
</article>
</main>
</div>
<div class="medium-4 columns">
<div class="courseSideList">
<?php echo do_shortcode('[course_content course_id="'.$course_id.'"]') ?>
</div>
<?php get_template_part('template-parts/courses/course-downloads'); ?>
</div>
</div><!--row-->
<?php   endif;?>
</div>
</div>
</section>

<?php	get_footer('members');?>