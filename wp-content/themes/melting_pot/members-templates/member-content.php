<?php
global $smof_data;

/*  Template Name: Member Content*/
get_header('members'); ?>
</header>

<?php get_template_part('template-parts/content-blocks/bannerheader'); ?>


<section class="memberbenefits">
	<div class="row">
		<? if (have_rows('dashboard_benefits')) : $i = count(get_field('dashboard_benefits')); ?>

			<ul class="benefit-wrap">
				<? while (have_rows('dashboard_benefits')) : the_row();
					$gridsize = '';

					if ($i == 4) {
						$gridsize = 'large-3 medium-6';
					} elseif ($i == 3) {
						$gridsize = 'large-4 medium-4';
					} elseif ($i == 2) {
						$gridsize = 'large-6 medium-6';
					} ?>
					<li class="<? echo $gridsize; ?> columns">
						<div class="wrap-benefit">
							<a href="<? echo echo get_sub_field('link_page') ?>">
								<img src="<? echo echo get_sub_field('image'); ?>" alt="<? echo echo get_sub_field('benefit-title'); ?> icon">

								<h4><? echo echo get_sub_field('benefit-title'); ?></h4>
							</a>
							<p><? echo echo get_sub_field('benefit-small-text'); ?></p>

							<a class="BTN" href="<? echo echo get_sub_field('link_page') ?>"><? echo echo get_sub_field('button_text') ?></a>
						</div>
					</li>
				<? endwhile; ?>
			</ul>
		<? endif; ?>

		<?php if (get_field('content_after_links')) : ?>
			<div class="afterMainLinks"><?php echo get_field('content_after_links'); ?></div>
		<?php else : ?>
		<?php endif; ?>


	</div>
</section>
<?php
global $wpdb;
$history = $wpdb->get_results("select DISTINCT lesson_id from " . $wpdb->prefix . "lessontracker where `user_id`=" . get_current_user_id() . " and `lesson_status` IN ( 5, 2, 1) order by `viewed` desc LIMIT 3", ARRAY_A); ?>


<?php if (get_field('hide_course_feed') == false) {

	$columnOption = $smof_data['course_page_column'];
	$columClass = 'large-4';
	if ($columnOption == 'one')
		$columClass = 'large-12';
	if ($columnOption == 'two')
		$columClass = 'large-6';
	if ($columnOption == 'four')
		$columClass = 'large-3';

?>

	<section class="courses post-custom">
		<div class="row">

			<!--Custom loop-->
			<?php

			// vars	
			//$typecp = get_field('content_selector');
			//

			// check

			$typecp = get_field_object('content_selector');
			$value = $typecp['value'];
			$label = $typecp['choices'][$value];

			$amount = get_field('amount_of');
			$amountcol = get_field('amount_col');
			$course_feed = new WP_Query(array(
				'post_type' => $value,
				'posts_per_page' => $amount, // put number of posts that you'd like to display 
			));

			$courseyn = get_field('course_lesson');

			?>


			<div class="sectionHeader">
				<div class="large-8 medium-7 columns">
					<h3>Latest Courses</h3>
				</div>

				<div class="large-4 medium-5 columns">
					<!--<a href="/courses" class="BTN">View All Courses</a>-->
				</div>

			</div>

			<? if ($course_feed->have_posts()) : while ($course_feed->have_posts()) : $course_feed->the_post();
					$imgID  = get_post_thumbnail_id($post->ID);
					$img    = wp_get_attachment_image_src($imgID, 'full', false, '');
					$imgAlt = get_post_meta($imgID, '_wp_attachment_image_alt', true);
					if (empty($img[0]))
						$img[0] = DEFAULT_IMG;

					$course_id = get_the_ID();
					$course_status = learndash_course_status($course_id, $user_id);
					$course_steps_count = learndash_get_course_steps_count($course_id);
					$completed = learndash_course_get_completed_steps($user_id, $course_id, $coursep);

					$ld_course_steps_object = LDLMS_Factory_Post::course_steps($course_id);
					$total                  = $ld_course_steps_object->get_steps_count();

					if ($total > 0) {
						$percentage = intval($completed * 100 / $total);
						$percentage = ($percentage > 100) ? 100 : $percentage;
					} else {
						$percentage = 0;
					} ?>
					<div class="<?php if ($amountcol === '3') {
									echo "medium-4 small-6";
								} elseif ($amountcol === '4') {
									echo "medium-3 small-6";
								} ?> columns end">
						<div class="blogpreview">
							<div class="coursePrevImage" style="background-image: url('<? echo $img[0]; ?>');">
								<?php if (get_field('available')) { ?>
									<a href="<? the_permalink(); ?>" class="courseImgOver"></a>
									<div class="courseProgress">
										<?php if ($course_status == 'In Progress') { ?>
											<p><?php echo $percentage . '%'; ?></p>
										<?php } else { ?>
											<p>
												<?php echo $percentage . '%'; ?>
											</p>
										<?php } ?>
									</div>
									<?php echo do_shortcode('[learndash_course_progress]') ?>
							</div>
						<? } else {  ?>
							<div class="CourseSoon">Coming Soon</div>
						</div>
					<?php } ?>
					<?php if (get_field('available')) { ?>
						<div class="blogprevtext course-limit">
							<h5><a href="<? the_permalink(); ?>">
									<? the_title(); ?>
								</a></h5>
							<p><?php echo get_field('course_description'); ?> </p>

							<? if ($courseyn === '1') {
								echo do_shortcode('[course_content course_id="' . $course_id . '"]');
							} ?>


							<a href="<? the_permalink(); ?>" class="BTN">
								<?php if ($course_status == 'In Progress') {
									echo "Continue course";
								} else {
									echo "Start Course";
								}
								?>
							</a>
						<?php } else { ?>
							<div class="blogprevtext nolink">
								<h5><?php the_title(); ?></h5>
								<p><?php echo get_field('course_description'); ?></p>
								<span class="BTN nolink ">Coming Soon</span>
							<?php } ?>
							</div>
						</div>
					</div>

			<?php endwhile;
			endif; ?>

		</div>
	</section>

<?php } ?>
<!-- Main Section -->

<!-- End Main Section -->
<?php get_footer("members"); ?>