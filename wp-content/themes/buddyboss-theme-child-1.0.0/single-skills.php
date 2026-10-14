<?php
if (!function_exists('skillBreakdownSection')) {
function skillBreakdownSection($listName, $heading, $showCount = false) {
	if (!have_rows($listName)) {
		return;
	}
	$rows = get_field($listName);
	$count = is_array($rows) ? count($rows) : 0;
	?>
	<section class="pond-skill-card">
		<h2><?php echo esc_html(($showCount && $count > 1 ? $count . ' ' : '') . $heading); ?></h2>
		<?php skillBreakdownList($listName); ?>
	</section>
	<?php
}
}

if (!function_exists('skillBreakdownList')) {
function skillBreakdownList($listName) {
	?>
	<ol class="pond-skill-breakdown">
		<?php while (have_rows($listName)) : the_row();
			$title = get_sub_field('title');
			$content = get_sub_field('content');
		?>
			<li>
				<?php if (!empty($title)) : ?><div class="pond-skill-breakdown__title"><?php echo wp_kses_post($title); ?></div><?php endif; ?>
				<?php if (!empty($content)) : ?><div class="pond-skill-breakdown__content"><?php echo wp_kses_post($content); ?></div><?php endif; ?>
			</li>
		<?php endwhile; ?>
	</ol>
	<?php
}
}

get_header();

$isMember = current_user_can('memberpress_authorized');
$skillTypes = get_the_terms($post->ID, 'skill-type');
$skillTypes = is_array($skillTypes) ? $skillTypes : array();
$performanceLevels = get_the_terms($post->ID, 'performance-level');
$performanceLevels = is_array($performanceLevels) ? $performanceLevels : array();
$puckLevel = max(0, min(5, intval(get_field('puck_level'))));
?>
<div class="pond-portal pond-library-detail pond-skill-detail">
<section class="clbHeader">
	<div class="row">
		<div class="large-8 medium-8 columns">
			<?php
			$parentTitle = !empty($post->post_parent) ? get_the_title($post->post_parent) : '';
			if (!empty($parentTitle) && $parentTitle !== get_the_title($post->ID)) :
			?>
				<a class="pond-skill-parent" href="<?php echo esc_url(get_permalink($post->post_parent)); ?>"><?php echo esc_html($parentTitle); ?></a>
			<?php endif; ?>
			<h1><?php the_title(); ?></h1>
			<div class="pond-skill-detail__meta">
				<?php foreach ($skillTypes as $skillType) : ?>
					<span class="clCatLink"><?php echo esc_html($skillType->name); ?></span>
				<?php endforeach; ?>
				<?php foreach ($performanceLevels as $performanceLevel) : ?>
					<span class="clCatLink pond-skill-detail__level"><?php echo esc_html($performanceLevel->name); ?></span>
				<?php endforeach; ?>
				<span class="pond-skill-detail__frequency" title="<?php echo esc_attr(sprintf('Frequency: %d of 5', $puckLevel)); ?>">
					<span class="pond-skill-detail__frequency-label">Frequency</span>
					<span class="screen-reader-text"><?php echo esc_html(sprintf('%d of 5', $puckLevel)); ?></span>
					<?php for ($x = 0; $x < 5; $x++) : ?><svg class="puck<?php echo $x < $puckLevel ? '' : ' faded'; ?>" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" viewBox="0 0 512 512"><path d="M0 160c0-53 114.6-96 256-96s256 43 256 96s-114.6 96-256 96S0 213 0 160zm0 82.2V352c0 53 114.6 96 256 96s256-43 256-96V242.2c-113.4 82.3-398.5 82.4-512 0z" /></svg><?php endfor; ?>
				</span>
			</div>
		</div>
		<div class="large-4 medium-4 columns">
			<a class="backBTN" href="/skills/"><i class="fas fa-angle-left" aria-hidden="true"></i> All Skills</a>
		</div>
	</div>
</section>

<section class="memberContent">
	<div class="row">
		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
			<div class="large-8 medium-8 columns pond-library-main">
				<div class="CourseContent">
					<article class="pond-library-article bootstrap-styles">
						<?php if (!$isMember) : ?>
							<div class="card unauthorized">
								<div class="card-img-top">
									<?php
									$thumbnail_url = get_the_post_thumbnail_url();
									$thumbnail_url = !empty($thumbnail_url) ? $thumbnail_url : 'https://cdn.thepond.howtohockey.com/2021/01/vimeo-postroll-thumbnail.jpg';
									?>
									<img src="<?php echo esc_url($thumbnail_url); ?>" alt="" />
									<div class="unauthorized-message-wrapper">
										<h2>This content is for members only</h2>
										<p>To view please join now or login</p>
										<div class="actions">
											<a href="/" class="BTN joinBTN">Join now</a>
											<a href="/login" class="BTN askBTN">Login</a>
										</div>
									</div>
								</div>
							</div>
							<?php if (!empty(get_the_content())) : ?>
								<div class="pond-skill-detail__content"><?php the_content(); ?></div>
							<?php endif; ?>
						<?php else : ?>
							<?php if (!empty(get_field('video_code'))) : ?>
								<div class="videowrapper pond-skill-detail__video">
									<div class="videoWrapper"><?php echo get_field('video_code'); ?></div>
								</div>
							<?php endif; ?>
							<?php if (!empty(get_the_content())) : ?>
								<div class="pond-skill-detail__content"><?php the_content(); ?></div>
							<?php endif; ?>
							<?php echo do_shortcode('[skill_competency_slider skill_id="' . get_the_ID() . '" user_id="' . get_current_user_id() . '"]'); ?>
							<?php skillBreakdownSection('skill_quick_tips', 'Quick Tips', true); ?>
						<?php endif; ?>
						<?php skillBreakdownSection('common_mistakes', 'Common Mistakes', true); ?>
					</article>
				</div>
			</div>

			<aside class="large-4 medium-4 columns pond-library-sidebar" aria-label="Skill details">
				<?php
				if ($isMember) {
					skillBreakdownSection('skill_pros', 'Pros');
					skillBreakdownSection('skill_cons', 'Cons');
				}
				$whenToUseIt = get_field('when_to_use_it', $post->ID);
				if (!empty($whenToUseIt)) :
				?>
					<section class="pond-skill-card">
						<h2>When To Use It</h2>
						<div class="pond-skill-card__body"><?php echo wp_kses_post($whenToUseIt); ?></div>
					</section>
				<?php endif; ?>

				<?php
				$relatedLessons = get_posts(array(
					'post_type' => 'sfwd-lessons',
					'meta_query' => array(
						array(
							'key' => 'skills',
							// Quoted ID matches the serialized value exactly, so "12" does not match "123".
							'value' => '"' . $post->ID . '"',
							'compare' => 'LIKE'
						)
					)
				));
				if (!empty($relatedLessons)) :
				?>
					<section class="pond-skill-card pond-related-skills">
						<h2>Related Lessons</h2>
						<ul class="pond-related-skill-list">
							<?php foreach ($relatedLessons as $relatedLesson) :
								$lessonLevels = get_the_terms($relatedLesson->ID, 'performance-level');
								$lessonLevelNames = array();
								foreach ((is_array($lessonLevels) ? $lessonLevels : array()) as $lessonLevel) {
									$lessonLevelNames[] = $lessonLevel->name;
								}
								$courseId = function_exists('learndash_get_course_id') ? learndash_get_course_id($relatedLesson->ID) : 0;
							?>
								<li>
									<a href="<?php echo esc_url(get_permalink($relatedLesson->ID)); ?>">
										<span class="pond-related-skill-title"><?php echo esc_html(get_the_title($relatedLesson->ID)); ?></span>
										<?php if ($courseId || $lessonLevelNames) : ?>
											<span class="pond-related-skill-level"><?php echo esc_html(implode(' · ', array_filter(array($courseId ? get_the_title($courseId) : '', implode(', ', $lessonLevelNames))))); ?></span>
										<?php endif; ?>
										<i class="bb-icon-l bb-icon-angle-right" aria-hidden="true"></i>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</section>
				<?php endif; ?>
			</aside>

			<?php
			$skillExamples = get_field('skill_examples', $post->ID);
			$exampleItems = array();
			foreach ((is_array($skillExamples) ? $skillExamples : array()) as $skillExample) {
				$examplesList = get_field('examples', $skillExample->ID);
				foreach ((is_array($examplesList) ? $examplesList : array()) as $example) {
					if (!empty($example['gif']) || !empty($example['video_code'])) {
						$exampleItems[] = $example;
					}
				}
			}
			if ($exampleItems) :
			?>
				<section class="CourseContent pond-skill-examples bootstrap-styles">
					<h2>Examples</h2>
					<div class="pond-skill-examples__grid">
						<?php foreach ($exampleItems as $example) : ?>
							<figure class="pond-skill-example">
								<?php if (!empty($example['gif'])) : ?>
									<img src="<?php echo esc_url($example['gif']); ?>" alt="" loading="lazy" data-enlargable />
								<?php else : ?>
									<div class="videowrapper"><div class="videoWrapper"><?php echo $example['video_code']; ?></div></div>
								<?php endif; ?>
								<?php if (!empty($example['description'])) : ?>
									<figcaption><?php echo wp_kses_post($example['description']); ?></figcaption>
								<?php endif; ?>
							</figure>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>
		<?php endwhile; endif; ?>
	</div>
</section>

<?php if (get_field('show_full_width_section') == 'yes') : ?>
	<section class="fullwidth">
		<div class="row">
			<div class="large-12 columns">
				<?php the_field('full_width_section'); ?>
			</div>
		</div>
	</section>
<?php endif; ?>
</div>
<?php
get_footer();
