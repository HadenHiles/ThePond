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
<?php get_template_part('template-parts/courses/course-forum-link'); ?>
</div>
</article>
</main>
</div>
<div class="medium-4 columns">
<div class="courseSideList">
<?php echo do_shortcode('[course_content course_id="'.$course_id.'"]') ?>

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
					<span class="level"><?=$performanceLevelString?></span>
				</div>
			</div>
			<?php
		}
	}
	?>
</div>

<?php get_template_part('template-parts/courses/course-downloads'); ?>
</div>
</div>
</div><!--row-->
<?php   endif;?>
</div>
</div>
</section>

<?php	get_footer('members');?>