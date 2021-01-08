<?php
get_header("members");
if (has_post_thumbnail()) {
	$imgID  = get_post_thumbnail_id($post->ID);
	$img    = wp_get_attachment_image_src($imgID, 'full', false, '');
	$imgAlt = get_post_meta($imgID, '_wp_attachment_image_alt', true);
}
?>
</header>
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

					<a class="clCatLink"><?php echo $term->name; ?></a>
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
			} if (in_array("Routines", $categories)) {
				?>
				<a class="backBTN" href="/content-library/">
				<i class="fas fa-angle-left"></i> All Routines</a>
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
				<div class="large-8 medium-8 columns">
					<main role="main">
						<article>
							<div class="CourseContent">
								<main role="main" class="main">
									<article>
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
														<h2>This content is for The Pond members only</h2>
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
											<h2 style="margin-bottom: 5px;">Related Skills</h2>
										<?php
										}
										?>
										<div class="bootstrap-styles skills-list">
											<?php
											if (!empty($relatedSkills)) {
												foreach ($relatedSkills as $relatedSkill) {
													$performanceLevels = get_the_terms($relatedSkill->ID, 'performance-level');
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
															<a href="<?= get_post_permalink($relatedSkill->ID) ?>" class="ghost"></a>
															<a href="<?= get_post_permalink($relatedSkill->ID) ?>" class="title"><?= get_the_title($relatedSkill->ID) ?></a>
															<span class="level"><?= $performanceLevelString ?></span>
														</div>
													</div>
											<?php
												}
											}
											?>
										</div>

										<?php
										if (current_user_can("memberpress_authorized")) {
											get_template_part('template-parts/courses/lesson-downloads');
										?>
											<div class="cl-history">
												<?php get_template_part('template-parts/courses/coursehistory'); ?>
											</div>
										<?php
											the_content();
										}
										?>
									</article>
								</main>
							</div>
				</div>

				<div class="large-4 medium-4 columns">

					<?php
					$term_list = wp_get_post_terms(get_the_ID(), 'performance-level', array("fields" => "all"));
					if ($term_list) {
						foreach ($term_list as $key => $term) {
					?>
							<a class="clCatLink"><?php echo $term->name; ?></a>
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
								<p style="font-size: 14px;">To keep track of your score, please <a href="/" style="color: #cc3333;">join now</a> or <a href="/login/" style="color: #cc3333;">login</a></p>
							</div>
						<?php
						} else {
						?>
							<div class="challenge-scores" id="challenge-scores">
								<div class="ld-section-heading">
									<h2>Your Scores</h2>
								</div>
								<div class="scores" id="scores">
									<i class="fa fa-spinner fa-spin" style="align-self: center; margin: 2% auto; position: relative; z-index: 5;"></i>
								</div>
								<div class="add-score">
									<input type="hidden" name="challenge_id" id="challenge-id" value="<?php echo get_the_ID() ?>" />
									<input type="hidden" name="user_id" id="user-id" value="<?php echo get_current_user_id() ?>" />
									<label for="challenge-score" id="success-message" class="success message">Score added</label>
									<label for="challenge-score" id="error-message" class="error message">Failed to add score</label>
									<input type="number" name="score" id="challenge-score" step="0.01" min="0" placeholder="Add your new best score" />
									<a href="#" class="add-score-button" id="add-score"><i class="fa fa-plus-circle"></i></a>
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
								'post_per_page' => 5,
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
								wp_reset_query(); ?>
							</ul>
							<?php
						}
						?>
					</div>
				</div>

		<?php endwhile;
		endif; ?>
	</div>
</section>

<?php get_footer("members"); ?>