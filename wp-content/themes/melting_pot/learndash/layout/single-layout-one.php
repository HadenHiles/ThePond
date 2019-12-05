<?php
get_header('members');
if(has_post_thumbnail()) {
$imgID  = get_post_thumbnail_id($post->ID);
$img    = wp_get_attachment_image_src($imgID,'full', false, '');
$imgAlt = get_post_meta($imgID,'_wp_attachment_image_alt', true);
}
$course_id = learndash_get_course_id();
?>
</header>

<?php get_template_part('template-parts/courses/course-header'); ?>

<!-- Main Section -->

<section class="MembersContent">
<div class="row">
<?php

if(have_posts()): while (have_posts()): the_post();

?>
<div class="large-8 medium-7 columns">
<main role="main">
<article>
<div class="CourseContent">
<?php get_template_part('template-parts/courses/lesson-topic-fields'); ?>
<?php get_template_part('template-parts/courses/course-downloads'); ?>
	
<?php get_template_part('template-parts/courses/course-forum-link'); ?>
	
	
<?php get_template_part('template-parts/courses/coursehistory'); ?> 
	</div>
	</article>
</main>

</div>
	
<div class="large-4 medium-5 columns">
<div class="courseSideList">
<?php echo do_shortcode('[course_content course_id="'.$course_id.'"]') ?>
</div>

	
<div class="CourseOptions">
	<h4> Course Options</h4>
<ul class="post_tools">

<?php
$lesson_class_complete=((check_lesson_track(get_the_id(),get_current_user_id(),'1','3')) ? ' post_tool_active' : ' post_tool_inactive');
$lesson_class_watchlist=((check_lesson_track(get_the_id(),get_current_user_id(),'1','5')) ? ' post_tool_active' : ' post_tool_inactive');
$lesson_class_bookmark=((check_lesson_track(get_the_id(),get_current_user_id(),'1','1')) ? ' post_tool_active' : ' post_tool_inactive');
 
?>
<?php if( trim($lesson_class_complete)  == 'post_tool_inactive') { ?>
<li><a class="lesson_tool tool_complete<?php echo $lesson_class_complete; ?>" data-lesson-id="<?php echo get_the_id(); ?>" data-track-type="3"><i class="fa fa-check-circle"></i><span> Mark Course as Complete</span></a></li>
<?php }else { ?>
 <li><a class="lesson_tool tool_complete<?php echo $lesson_class_complete; ?>" data-lesson-id="<?php echo get_the_id(); ?>" data-track-type="3"><i class="fa fa-check-circle"></i><span> Remove from Completed</span></a></li>
<?php } ?>

<?php if( trim($lesson_class_watchlist)  == 'post_tool_inactive') { ?>
 <li><a class="lesson_tool tool_watchlist<?php echo $lesson_class_watchlist; ?>" data-lesson-id="<?php echo get_the_id(); ?>" data-track-type="5"><i class="fas fa-star"></i><span> Add to Watchlist</span></a></li>
 
<?php }else { ?>
<li><a class="lesson_tool tool_watchlist<?php echo $lesson_class_watchlist; ?>" data-lesson-id="<?php echo get_the_id(); ?>" data-track-type="5"><i class="fas fa-star"></i><span> Remove from  Watchlist</span></a></li>
 
<?php } ?>  
</ul>
</div>

</div>
<?php endwhile; endif;?>
</div>
</section>


<?php  get_footer("members"); ?>