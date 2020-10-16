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

<?php
if (current_user_can('memberpress_authorized')) {
	?>
	<!-- Tips for success list -->
	<div class="ListWithHeading">
		<?php
		// Check rows exists.
		if( have_rows('tips_for_success') ):
			$count = count(get_field('tips_for_success'));
			?>
			<h3><?=($count > 1) ? $count : ''?> Tips For Success</h3>
			<?php
			// Loop through rows.
			keyLessonList('tips_for_success');
		// No value.
		else :
			// Do something...
		endif;
		?>
	</div>
	
	<!-- Common mistakes list -->
	<div class="ListWithHeading">
		<?php
		// Check rows exists.
		if( have_rows('common_mistakes') ):
			$count = count(get_field('common_mistakes'));
			?>
			<h3><?=($count > 1) ? $count : ''?> Common Mistakes</h3>
			<?php
			// Loop through rows.
			keyLessonList('common_mistakes');
		// No value.
		else :
			// Do something...
		endif;
		?>
	</div>

	<!-- What To Practice list -->
	<div class="ListWithHeading">
		<?php
		// Check rows exists.
		if( have_rows('what_to_practice') ):
			?>
			<h3>What To Practice</h3>
			<?php
			// Loop through rows.
			keyLessonList('what_to_practice');
		// No value.
		else :
			// Do something...
		endif;
		?>
	</div>

	<?php get_template_part('template-parts/courses/lesson-downloads'); ?>
		
		<?php get_template_part('template-parts/courses/coursehistory'); ?>	
		
		<?php /* get_template_part('template-parts/courses/course-forum-link'); */ ?>
	</div>
	</article>
	<?php
}
?>	
	
<?php the_content();?>
</main>
</div>
<div class="medium-4 columns">
<div class="courseSideList">
	
	
	
<?php 
if (current_user_can('memberpress_authorized')) {
	echo do_shortcode('[course_content course_id="'.$course_id.'"]');
}
?>

<?php
$relatedSkills = get_field('skills', $post->ID);
if (!empty($relatedSkills)) {
	?>
	<h2 style="margin-bottom: 5px;">Related Skills</h2>
	<?php
}
?>
<div class="bootstrap-styles skills-list">
	<?php
	if (!empty($relatedSkills)) {
		foreach($relatedSkills as $relatedSkill) {
			$performanceLevels = get_the_terms( $relatedSkill->ID, 'performance-level' ); 
			$performanceLevelString = '';
			if(sizeof($performanceLevels) > 0) {
				$count = 0;
				foreach($performanceLevels as $performanceLevel) {
					if (++$count > 1 && $count <= sizeof($performanceLevels)) {
						$performanceLevelString .= ', ';
					}
					$performanceLevelString .= $performanceLevel->name;
				}
			}
			?>
			<div class="card skill">
				<div class="card-body content">
					<a href="<?=get_post_permalink($relatedSkill->ID)?>" class="ghost"></a>
					<a href="<?=get_post_permalink($relatedSkill->ID)?>" class="title"><?=get_the_title($relatedSkill->ID)?></a>
					<div class="right">
						<span class="level"><?=$performanceLevelString?></span>
					</div>
				</div>
			</div>
			<?php
		}
	}
	?>
</div>

<?php 
if (current_user_can('memberpress_authorized')) {
	get_template_part('template-parts/courses/course-downloads'); 
}
?>
</div>

</div>
</div><!--row-->
<?php   endif;?>
</div>
</div>
</section>
<?php get_footer("members");

function keyLessonList($listName) {
	?>
	<ol class="lesson-list">
		<?php
		while( have_rows($listName) ) : the_row();
			?>
			<li class="lesson-list-item">
				<?php
				// Load sub field values.
				$title = get_sub_field('title');
				$content = get_sub_field('content');
				?>
				<div class="title">
					<?php
					if (!empty($title) && empty($content)) {
						echo $title;
					}
					if (!empty($content)) {
						echo $content;
					}
					?>
				</div>
			</li>
			<?php
		// End loop.
		endwhile;
		?>
		</ol>
	<?php
}

?>