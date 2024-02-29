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
<!-- Main Section -->

<section class="MembersContent v2">
	<div class="row">
		<?php

		if (have_posts()) : while (have_posts()) : the_post();

		?>
				<div class="large-8 medium-7 columns">
					<main role="main">
						<article>
							<div class="CourseContentv2">

								<div class="CourseIntroBoxV2">

									<?php get_template_part('template-parts/courses/course-headerv2'); ?>

									<div class="clearfix"></div>

									<?php if (get_field('course_image')) : ?>
										<div class="sfwdvideo">
											<?php if (get_field('course_image')) : ?>
												<div class="videoWrapper"><img src="<?php echo get_field('course_image'); ?>" alt="<?php the_title(); ?>" /></div>
											<?php endif; ?>
										</div>
									<?php endif; ?>
									<!-- End Image Embed -->


									<?php if (get_field('video_code')) : ?>
										<div class="sfwdvideo">
											<?php if (get_field('video_code')) : ?>
												<div class="videoWrapper"><?php echo get_field('video_code'); ?></div>
											<?php endif; ?>
										</div>
									<?php endif; ?>
									<!-- End Video Embed -->


									<div class="clearfix"></div>


									<div class="CourseOptions">
										<ul class="post_tools">

											<li><strong>Course Options</strong></li>

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

								</div>



								<?php if (get_field('lession_audio')) : ?>
									<div class="sfwdaudio">
										<?php if (get_field('lession_audio')) : ?>
											<h4>Listen to audio lesson</h4>
											<?php echo get_field('lession_audio'); ?>
										<?php endif; ?>
									</div>
								<?php endif; ?>
								<!-- End Audio Embed -->
								<?php if (get_field('below_media')) : ?>
									<?php echo get_field('below_media'); ?>
								<?php endif; ?>



								<?php echo do_shortcode('[course_content course_id="' . $course_id . '"]') ?>


							</div>
						</article>
					</main>


				</div>

				<div class="large-4 medium-5 columns">

					<div class="courseIntroBox">
						<h3>Course Introduction</h3>

						<?php if (get_field('above_media')) : ?>
							<?php echo get_field('above_media'); ?>
						<?php endif; ?>
					</div>




					<?php get_template_part('template-parts/courses/course-downloads'); ?>

					<?php get_template_part('template-parts/courses/course-forum-link'); ?>

				</div>
		<?php endwhile;
		endif; ?>
	</div>
</section>
<?php get_footer("members"); ?>