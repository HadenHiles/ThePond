<?php ?>
<script>(function($){$(document).ready(function(){flip_expand_all("#lessons_list");});})(jQuery)</script>

<section class="courseHeaderv2">
	
	<div class="medium-10 columns nopadLeft">
	
<h1><?php if (get_field('custom_title')): ?><?php the_field('custom_title'); ?><?php else: ?><?php the_title();?><?php endif; ?></h1>
<?php if( get_field('lesson_sub') ): ?>
<p><?php the_field('lesson_sub'); ?></p>
<?php endif; ?>
		
		
	<div class="breadcrumbs">
	
	<?php
if ( function_exists( 'aj_breadcrumbs' ) ) {
	aj_breadcrumbs();
}
    ?>
	
	</div>
		
		<?php// get_template_part('template-parts/courses/coursehistoryv3'); ?>
		
	</div>
	
	
	
	<div class="medium-2 columns nopadRight">
	<div class="progressWrapper">
<?php  //dynamic_sidebar( 'lms-progress-widget' );
 
 $post = get_post( get_the_ID() );
 if ( ! ( $post instanceof WP_Post ) ) {
		return false;
 }
 if ( $post->post_type != 'sfwd-courses' ) {
 	 $course_id = learndash_get_course_id(get_the_ID());
 }else{
  $course_id = get_the_ID();
 } 
 
$user_id =  get_current_user_id();
$course_status = learndash_course_status( $course_id, $user_id );
$course_steps_count = learndash_get_course_steps_count( $course_id ); 
$completed = learndash_course_get_completed_steps( $user_id, $course_id);

$ld_course_steps_object = LDLMS_Factory_Post::course_steps( $course_id );
$total                  = $ld_course_steps_object->get_steps_count();

if ( $total > 0 ) {
	$percentage = intval( $completed * 100 / $total );
	$percentage = ( $percentage > 100 ) ? 100 : $percentage;
} else {
	$percentage = 0;
}
?>
<div class="courseProgress"> 
<?php if($course_status == 'In Progress' ) {?>
<p><?php echo $percentage .'%'; ?></p>
<?php } else { ?>
<p><?php  echo $percentage .'%'; ?></p>
 <?php } ?>

</div>
</div>
</div>
	
</section>	