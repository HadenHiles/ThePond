<?php
get_header('members');
if(has_post_thumbnail()) {
$imgID  = get_post_thumbnail_id($post->ID);
$img    = wp_get_attachment_image_src($imgID,'full', false, '');
$imgAlt = get_post_meta($imgID,'_wp_attachment_image_alt', true);
}
$course_id = learndash_get_course_id();
$ProgThumb = get_field('Course_thumbnail', $course_id ); 
$parent_course_id = learndash_get_setting( $post, 'course' );
?>
</header>
<script>(function($){$(document).ready(function(){flip_expand_all("#lessons_list");});})(jQuery)</script>
<?php get_template_part('template-parts/courses/course-header'); ?>

<section class="MembersContent">
<div class="row">
<?php if(have_posts()): the_post();?>
<div class="medium-8 columns">
<main role="main">
<article>
<div class="CourseContent">
<?php get_template_part('template-parts/courses/lesson-topic-fields'); ?>

<?php get_template_part('template-parts/courses/lesson-downloads'); ?>
	
	<?php get_template_part('template-parts/courses/coursehistory'); ?>	
	
	<?php get_template_part('template-parts/courses/course-forum-link'); ?>
</div>
</article>	
	
<?php the_content();?>
</main>
</div>
<div class="medium-4 columns">
<div class="courseSideList">
	
	
	
<?php echo do_shortcode('[course_content course_id="'.$course_id.'"]') ?>

<?php get_template_part('template-parts/courses/course-downloads'); ?>
</div>

</div>
</div><!--row-->
<?php   endif;?>
</div>
</div>
</section>
<?php get_footer("members");?>