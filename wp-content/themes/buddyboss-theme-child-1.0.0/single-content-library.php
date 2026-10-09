<?php
get_header(); ?>
<div class="pond-portal pond-library-detail">
<?php
if (has_post_thumbnail()) {
	$imgID  = get_post_thumbnail_id($post->ID);
	$img    = wp_get_attachment_image_src($imgID, 'full', false, '');
	$imgAlt = get_post_meta($imgID, '_wp_attachment_image_alt', true);
}
?>

<!-- Main Section -->
<section class="clbHeader">
	<div class="row">
		<div class="large-8 medium-8 columns">
			<h1><?php the_title(); ?></h1>
			<?php
			$category_list = wp_get_post_terms(get_the_ID(), 'library_category', array("fields" => "all"));
			$categories = [];
			foreach ($category_list as $category) {
				$categories[] = $category->name;
			}

			$term_list = wp_get_post_terms(get_the_ID(), 'skill-type', array("fields" => "all"));
			if ($term_list) {
				foreach ($term_list as $term) {
			?>

					<span class="clCatLink"><?php echo esc_html($term->name); ?></span>
			<?php }
			}
			?>
		</div>

		<div class="large-4 medium-4 columns">
			<?php
			if (in_array("Challenges", $categories)) {
				?>
				<a class="backBTN" href="/challenges/">
				<i class="fas fa-angle-left"></i> All Challenges</a>
				<?php
			} else if (in_array("Routines", $categories)) {
				?>
				<a class="backBTN" href="/routines/">
				<i class="fas fa-angle-left"></i> All Routines</a>
				<?php
			} else if (in_array("Move Makers", $categories)) {
				?>
				<a class="backBTN" href="/move-makers/">
				<i class="fas fa-angle-left"></i> All Move Makers</a>
				<?php
			} else {
			?>
				<a class="backBTN" href="/content-library/">
					<i class="fas fa-angle-left"></i> All Library Items</a>
			<?php
			}
			?>
		</div>
	</div>
</section>
<section class="memberContent">
	<div class="row">
		<?php
		if (have_posts()) : while (have_posts()) : the_post();
		?>
				<div class="large-8 medium-8 columns pond-library-main">
							<div class="CourseContent">
									<article class="pond-library-article">
										<?php
										if (!current_user_can("memberpress_authorized")) {
										?>
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
															<a href="/" class="BTN joinBTN">Join now</a>
															<a href="/login" class="BTN askBTN">Login</a>
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
										<?php
										} else {
											get_template_part('template-parts/courses/lesson-topic-fields');
										}
										?>

										<?php
										$relatedSkills = get_field('skills', $post->ID);
										if (!empty($relatedSkills)) {
										?>
											<section class="pond-related-skills" aria-labelledby="pond-related-skills-heading">
											<h2 id="pond-related-skills-heading">Related Skills</h2>
										<?php
										?>
										<ul class="pond-related-skill-list">
											<?php
											if (!empty($relatedSkills)) {
												foreach ($relatedSkills as $relatedSkill) {
													$performanceLevels = get_the_terms($relatedSkill->ID, 'performance-level');
													$performanceLevelString = '';
													if (count(is_array($performanceLevels) ? $performanceLevels : array()) > 0) {
														$count = 0;
														foreach ($performanceLevels as $performanceLevel) {
															if (++$count > 1 && $count <= count(is_array($performanceLevels) ? $performanceLevels : array())) {
																$performanceLevelString .= ', ';
															}
															$performanceLevelString .= $performanceLevel->name;
														}
													}
											?>
													<li>
														<a href="<?php echo esc_url(get_post_permalink($relatedSkill->ID)); ?>">
															<span class="pond-related-skill-title"><?php echo esc_html(get_the_title($relatedSkill->ID)); ?></span>
															<?php if ($performanceLevelString) : ?>
																<span class="pond-related-skill-level"><?php echo esc_html($performanceLevelString); ?></span>
															<?php endif; ?>
															<i class="bb-icon-l bb-icon-angle-right" aria-hidden="true"></i>
														</a>
													</li>
											<?php
												}
											}
											?>
										</ul>
											</section>
										<?php } ?>

										<?php
										if (current_user_can("memberpress_authorized")) {
											get_template_part('template-parts/courses/lesson-downloads');
										?>
											<?php if (!array_intersect(array('Challenges', 'Routines'), $categories)) : ?>
											<div class="cl-history">
												<?php get_template_part('template-parts/courses/coursehistory'); ?>
											</div>
											<?php endif; ?>
										<?php
											the_content();
										}
										?>
									</article>
							</div>
				</div>

				<aside class="large-4 medium-4 columns pond-library-sidebar" aria-label="<?php esc_attr_e('Training details', 'buddyboss-theme-child'); ?>">

					<?php
					$term_list = wp_get_post_terms(get_the_ID(), 'performance-level', array("fields" => "all"));
					if ($term_list) {
						foreach ($term_list as $key => $term) {
					?>
							<span class="clCatLink"><?php echo esc_html($term->name); ?></span>
					<?php
						}
					} ?>
					<div class="clearfix" style="margin-bottom: 10px;"></div>

					<?php /* if (has_post_thumbnail()) { ?>
						<?php the_post_thumbnail('full'); ?>
					<?php } */ ?>

					<?php
					if (in_array("Challenges", $categories)) {
						if (!current_user_can("memberpress_authorized")) {
					?>
							<div class="challenge-scores" id="challenge-scores">
								<div class="ld-section-heading">
									<h2>Your Scores</h2>
								</div>
								<p>To keep track of your score, please <a href="/">join now</a> or <a href="/login/">login</a></p>
							</div>
						<?php
						} else {
						?>
							<div class="challenge-scores" id="challenge-scores">
								<div class="ld-section-heading">
									<h2>Your Scores</h2>
								</div>
								<div class="scores" id="scores" aria-live="polite">
									<i class="fa fa-spinner fa-spin" style="align-self: center; margin: 2% auto; position: relative; z-index: 5;"></i>
								</div>
								<div class="add-score">
									<input type="hidden" name="challenge_id" id="challenge-id" value="<?php echo get_the_ID() ?>" />
									<input type="hidden" name="user_id" id="user-id" value="<?php echo get_current_user_id() ?>" />
									<label for="challenge-score" id="success-message" class="success message" role="status">Score added</label>
									<label for="challenge-score" id="error-message" class="error message" role="alert">Failed to add score</label>
									<label class="screen-reader-text" for="challenge-score">Your new score</label>
									<input type="number" name="score" id="challenge-score" step="0.01" min="0" placeholder="Add your new best score" />
									<button type="button" class="add-score-button" id="add-score" aria-label="Add score"><i class="fa fa-plus-circle" aria-hidden="true"></i></button>
								</div>
							</div>
					<?php
						}
					}
					?>

					<div class="relatedFeed">
						<?php
						if (in_array("Challenges", $categories)) {
						?>
							<h4>More Challenges</h4>
						<?php
						} else if (in_array("Routines", $categories)) {
						?>
							<h4>More Routines</h4>
						<?php
						}

						if (in_array("Challenges", $categories) || in_array("Routines", $categories)) {
							$term_list = wp_get_post_terms(get_the_ID(), 'library_category', array("fields" => "ids"));
							$arg = array(
								'post_type' => 'content-library',
								'posts_per_page' => 5,
								'post__not_in' => array(get_the_ID()),
								'tax_query' => array(
									array(
										'taxonomy' => 'library_category',
										'field' => 'id',
										'terms' => $term_list,
									),
								),
							);
							$newQuery = new WP_Query($arg);
							?>
							<ul>
								<?php
								if ($newQuery->have_posts()) : while ($newQuery->have_posts()) : $newQuery->the_post();
								?>
										<li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a> </li>
								<?php endwhile;
								endif;
								wp_reset_postdata(); ?>
							</ul>
							<?php
						}
						?>
					</aside>
				</div>

		<?php endwhile;
		endif; ?>
	</div>
</section>

<?php ?>
</div>
<?php get_footer(); ?>