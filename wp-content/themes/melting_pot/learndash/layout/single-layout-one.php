<?php
get_header('members');
if (has_post_thumbnail()) {
	$imgID  = get_post_thumbnail_id($post->ID);
	$img    = wp_get_attachment_image_src($imgID, 'full', false, '');
	$imgAlt = get_post_meta($imgID, '_wp_attachment_image_alt', true);
}
$course_id = learndash_get_course_id();
?>
</header>

<?php get_template_part('template-parts/courses/course-header'); ?>

<!-- Main Section -->

<section class="MembersContent">
	<div class="row">
		<?php

		if (have_posts()) : while (have_posts()) : the_post();

		?>
				<div class="large-7 medium-7 columns">
					<main role="main">
						<article>
							<div class="CourseContent">
								<?php
								$user_id =  get_current_user_id();
								$course_status = learndash_course_status($course_id, $user_id);

								$lessons = learndash_get_course_lessons_list($course_id);
								if (!empty($lessons)) {
									$firstLesson = $lessons[0];
									$continueLesson = false;
									foreach ($lessons as $lesson) {
										if ($continueLesson == false) {
											if ($lesson['status'] != 'completed') {
												$continueLesson = $lesson;
											}
										}
									}

									if ($continueLesson != false) {
										$url = get_permalink($continueLesson["post"]->ID);

										if (!empty($url)) {
											if ($course_status == "In Progress") {
								?>
												<a href="<?= esc_url($url) ?>" class="BTN" style="width: 100%; margin: 5px 0 15px 0; padding: 15px; font-size: 16px; border-radius: 5px;">Resume Course <i class="fa fa-caret-right" style="font-size: 24px; position: relative; right: -10px; top: 3px; line-height: 16px;"></i></a>
											<?php
											} else {
											?>
												<a href="<?= esc_url($url) ?>" class="BTN" style="width: 100%; margin: 5px 0 15px 0; padding: 15px; font-size: 16px; border-radius: 5px;">Start Course <i class="fa fa-caret-right" style="font-size: 24px; position: relative; right: -10px; top: 3px; line-height: 16px;"></i></a>
									<?php
											}
										}
									}
								}

								$prerequisiteSkills = get_field('prerequisite_skills', $post->ID);
								if (!empty($prerequisiteSkills)) {
									?>
									<h2 style="margin-bottom: 5px;">Prerequisite Skills</h2>
								<?php
								}
								?>
								<div class="bootstrap-styles skills-list">
									<?php
									if (!empty($prerequisiteSkills)) {
										foreach ($prerequisiteSkills as $prerequisiteSkill) {
											$performanceLevels = get_the_terms($prerequisiteSkill->ID, 'performance-level');
											$performanceLevelString = '';
											if (sizeof($performanceLevels) > 0) {
												$count = 0;
												foreach ($performanceLevels as $performanceLevel) {
													if (++$count > 1 && $count <= sizeof($performanceLevels)) {
														$performanceLevelString .= ', ';
													}
													$performanceLevelString .= $performanceLevel->name;
												}
											}
									?>
											<div class="card skill">
												<div class="card-body content">
													<a href="<?= get_post_permalink($prerequisiteSkill->ID) ?>" class="ghost"></a>
													<a href="<?= get_post_permalink($prerequisiteSkill->ID) ?>" class="title"><?= get_the_title($prerequisiteSkill->ID) ?></a>
													<span class="level"><?= $performanceLevelString ?></span>
												</div>
											</div>
									<?php
										}
									}
									?>
								</div>

								<?php
								if (!current_user_can('memberpress_authorized')) {
								?>
									<div class="main">
										<div class="card unauthorized">
											<div class="card-img-top">
												<?php
												$thumbnail_url = get_the_post_thumbnail_url();
												$thumbnail_url = !empty($thumbnail_url) ? $thumbnail_url : "https://cdn.thepond.howtohockey.com/2021/01/vimeo-postroll-thumbnail.jpg";
												?>
												<img src="<?= $thumbnail_url ?>" />
												<div class="unauthorized-message-wrapper">
													<h2>This content is for members only</h2>
													<p>To view please join now or login</p>
													<div class="actions">
														<a href="/" class="BTN small joinBTN">Join now</a>
														<a href="/login" class="BTN small askBTN">Login</a>
													</div>
												</div>
											</div>
											<?php
											if (!empty(get_the_content())) {
											?>
												<div class="card-body">
													<?php the_content(); ?>
												</div>
											<?php
											}
											?>
										</div>
									</div>
								<?php
								} else {
									get_template_part('template-parts/courses/lesson-topic-fields');
								}

								// Check rows exists.
								if (have_rows('goals')) :
								?>
									<h2 style="margin-bottom: 5px;">Goals</h2>
									<div class="bootstrap-styles skills-list">
										<?php
										// Loop through rows.
										goalList('goals');
										?>
									</div>
								<?php
								// No value.
								else :
								// Do something...
								endif;
								?>

								<?php
								$targetedSkills = get_field('targeted_skills', $post->ID);
								if (!empty($targetedSkills)) {
								?>
									<h2 style="margin-bottom: 5px;">Skills</h2>
								<?php
								}
								?>
								<div class="bootstrap-styles skills-list" style="margin-bottom: 10px;">
									<?php
									if (!empty($targetedSkills)) {
										foreach ($targetedSkills as $targetedSkill) {
											$performanceLevels = get_the_terms($targetedSkill->ID, 'performance-level');
											$performanceLevelString = '';
											if (sizeof($performanceLevels) > 0) {
												$count = 0;
												foreach ($performanceLevels as $performanceLevel) {
													if (++$count > 1 && $count <= sizeof($performanceLevels)) {
														$performanceLevelString .= ', ';
													}
													$performanceLevelString .= $performanceLevel->name;
												}
											}
									?>
											<div class="card skill">
												<div class="card-body content">
													<a href="<?= get_post_permalink($targetedSkill->ID) ?>" class="ghost"></a>
													<a href="<?= get_post_permalink($targetedSkill->ID) ?>" class="title"><?= get_the_title($targetedSkill->ID) ?></a>
													<span class="level"><?= $performanceLevelString ?></span>
												</div>
											</div>
									<?php
										}
									}
									?>
								</div>

								<?php 
								if (!current_user_can('memberpress_authorized')) {
									get_template_part('template-parts/courses/course-downloads');
									get_template_part('template-parts/courses/coursehistory');
								}
								?>
							</div>
						</article>
					</main>
				</div>

				<div class="large-5 medium-5 columns">
					<div class="courseSideList">
						<?php
						echo do_shortcode('[course_content course_id="' . $course_id . '"]') ?>
					</div>

					<?php
					if (current_user_can('memberpress_authorized')) {
						?>
						<div class="CourseOptions">
							<h4> Course Options</h4>
							<ul class="post_tools">

								<?php
								$lesson_class_complete = ((check_lesson_track(get_the_id(), get_current_user_id(), '1', '3')) ? ' post_tool_active' : ' post_tool_inactive');
								$lesson_class_watchlist = ((check_lesson_track(get_the_id(), get_current_user_id(), '1', '5')) ? ' post_tool_active' : ' post_tool_inactive');
								$lesson_class_bookmark = ((check_lesson_track(get_the_id(), get_current_user_id(), '1', '1')) ? ' post_tool_active' : ' post_tool_inactive');

								?>
								<?php if (trim($lesson_class_complete)  == 'post_tool_inactive') { ?>
									<li><a class="lesson_tool tool_complete<?php echo $lesson_class_complete; ?>" data-lesson-id="<?php echo get_the_id(); ?>" data-track-type="3"><i class="fa fa-check-circle"></i><span> Mark Course as Complete</span></a></li>
								<?php } else { ?>
									<li><a class="lesson_tool tool_complete<?php echo $lesson_class_complete; ?>" data-lesson-id="<?php echo get_the_id(); ?>" data-track-type="3"><i class="fa fa-check-circle"></i><span> Remove from Completed</span></a></li>
								<?php } ?>

								<?php if (trim($lesson_class_watchlist)  == 'post_tool_inactive') { ?>
									<li><a class="lesson_tool tool_watchlist<?php echo $lesson_class_watchlist; ?>" data-lesson-id="<?php echo get_the_id(); ?>" data-track-type="5"><i class="fa fa-star"></i><span> Add to Watchlist</span></a></li>

								<?php } else { ?>
									<li><a class="lesson_tool tool_watchlist<?php echo $lesson_class_watchlist; ?>" data-lesson-id="<?php echo get_the_id(); ?>" data-track-type="5"><i class="fa fa-star"></i><span> Remove from Watchlist</span></a></li>

								<?php } ?>
							</ul>
						</div>
						<?php
					}
					?>
				</div>
		<?php endwhile;
		endif; ?>
	</div>
</section>


<?php get_footer("members");

function goalList($listName) {
	$x = 1;
	while (have_rows($listName)) : the_row();
?>
		<div class="card skill no-hover" style="width: 100%;">
			<div class="card-body content">
				<?php
				// Load sub field values.
				$goal = get_sub_field('goal');
				if (!empty($goal)) {
				?>
					<div class="title"><span><?= $x++ ?>.</span><?= $goal ?></div>
				<?php
				}
				?>
			</div>
		</div>
<?php
	// End loop.
	endwhile;
}

// Facebook retargetting for non-members who have viewed locked course pages
if (!current_user_can('memberpress_authorized')) {
	?>
	<script>fbq('trackCustom', 'ViewContent', {type: 'course', name: '<?php the_title(); ?>'});</script>
	<?php
}
?>