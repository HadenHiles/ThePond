<? get_header('members');


$user_id = get_current_user_id();
/**
* Displays a course
*
* Available Variables:
* $course_id       : (int) ID of the course
* $course      : (object) Post object of the course
* $course_settings : (array) Settings specific to current course
*
* $courses_options : Options/Settings as configured on Course Options page
* $lessons_options : Options/Settings as configured on Lessons Options page
* $quizzes_options : Options/Settings as configured on Quiz Options page
*
* $user_id         : Current User ID
* $logged_in       : User is logged in
* $current_user    : (object) Currently logged in user object
*
* $course_status   : Course Status
* $has_access  : User has access to course or is enrolled.
* $materials       : Course Materials
* $has_course_content      : Course has course content
* $lessons         : Lessons Array
* $quizzes         : Quizzes Array
* $lesson_progression_enabled  : (true/false)
* $has_topics      : (true/false)
* $lesson_topics   : (array) lessons topics
*
* @since 2.1.0
*
* @package LearnDash\Course
*/
?>
</header>


<?php get_template_part('template-parts/member-page-header'); ?>

<section class="memberDashWelc">
<div class="row">
<div class="large-8 medium-8 columns">
<h1><?php echo single_cat_title();?></h1>
</div>
<div class="large-4 medium-4 columns">
<a class="backBTN" href="/courses">
<i class="fa fa-angle-left"></i> View All Courses</a>
</div>
</div>
</section>
<!-- section-->

<section class="memberContent CourseFeed">
<div class="row">

<main><?
if(have_posts()):while(have_posts()):the_post();
$imgID  = get_post_thumbnail_id($post->ID);
$img    = wp_get_attachment_image_src($imgID,'full', false, '');
$imgAlt = get_post_meta($imgID,'_wp_attachment_image_alt', true);
if( empty($img[0]) )
$img[0] = DEFAULT_IMG;	

$course_id = get_the_ID();
$course_status = learndash_course_status( $course_id, $user_id );
$course_steps_count = learndash_get_course_steps_count( $course_id ); 
$completed = learndash_course_get_completed_steps( $user_id, $course_id, $coursep );

$ld_course_steps_object = LDLMS_Factory_Post::course_steps( $course_id );
$total                  = $ld_course_steps_object->get_steps_count();

if ( $total > 0 ) {
	$percentage = intval( $completed * 100 / $total );
	$percentage = ( $percentage > 100 ) ? 100 : $percentage;
} else {
	$percentage = 0;
}
	
	
?>
<div class="large-4 medium-6 columns">
<div class="blogpreview">
<div class="coursePrevImage" style="background-image: url('<? echo $img[0]; ?>');">


<?php if( get_field('available') ){ ?>

<a href="<? the_permalink();?>" class="courseImgOver"></a>

<div class="courseProgress"> 
<?php if($course_status == 'In Progress' ) {?>
<p><?php echo $percentage .'%'; ?></p>
<?php } else { ?>
<p><?php  echo $percentage .'%'; ?></p>
 <?php } ?>

</div>
<?php echo do_shortcode('[learndash_course_progress]') ?>
</div>

<? }else {  ?>
<div class="CourseSoon">Coming Soon</div>
</div>

<?php } ?>


<?php if( get_field('available') ){ ?>
<div class="blogprevtext">
<h5><a href="<? the_permalink();?>"><? the_title();?></a></h5>
<p><?php the_field('course_description'); ?></p>
<a href="<? the_permalink();?>" class="BTN">
<?php if($course_status == 'In Progress' ) { 
	echo "Continue course";
	}else {
echo "Start Course" ;
}
?></a>
<?php }else{ ?>
<div class="blogprevtext nolink">
<h5><?php the_title();?></h5>
<p><?php the_field('course_description'); ?></p>
<span class="BTN nolink ">Coming Soon</span>
<?php } ?>

</div>
</div>
</div>
<?  endwhile; ?>

<nav class="navigation paging-navigation" role="navigation">

<div class="nav-links">

<? if ( get_next_posts_link() ) : ?>

<div class="nav-previous">

<? next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'battle' ) ); ?>

</div>

<? endif; ?>

<? if ( get_previous_posts_link() ) : ?>

<div class="nav-next">

<? previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'battle' ) ); ?>

</div>

<? endif; ?>

</div>

<!-- .nav-links -->

</nav>

<?

endif;
wp_reset_query();
?>
</main>

</div>
</section>
<? get_footer("members"); ?>