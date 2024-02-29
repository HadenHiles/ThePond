<?php

if (get_field('select_case_study')) {
	$testids = get_field('select_case_study');
} else {
	$testids = '';
}
$numbercase = get_field('number_of_case_study_columns')
?>
<section class="recentpost casestudy">
	<div class="row <?php if (($numbercase) == '1') : ?>rowNarrow<?php endif; ?>">
		<div class="posttitle">
			<h3><?php echo get_field('case_study_heading'); ?></h3>
			<p><?php echo get_field('case_study_sub_heading'); ?></p>
		</div>
		<?php
		$args = array(
			'posts_per_page' => $numbercase,
			'orderby' => 'rand',
			'post_type' => 'enable_case_study',
			'post__in' => $testids
		);
		$the_query = new WP_Query($args);
		?>
		<?php if ($the_query->have_posts()) : ?>
			<?php while ($the_query->have_posts()) : $the_query->the_post();
				$img = wp_get_attachment_url(get_post_thumbnail_id($post->ID), "Full");
			?>
				<div class="<?php if (($numbercase) == '1') : ?>large-12<?php endif; ?><?php if (($numbercase) == '2') : ?>large-6<?php endif; ?><?php if (($numbercase) == '3') : ?>large-4<?php endif; ?> medium-12 columns">

					<div class="blogpreview">

						<div class="<?php if (($numbercase) == '1') : ?>large-6<?php else : ?>large-12<?php endif; ?> medium-12 columns nopad">
							<a href="<?php the_permalink(); ?>" class="blogprevimage <?php if (($numbercase) == '1') : ?><?php else : ?>shorterprevimage<?php endif; ?>" style="background: url('<?php echo $img; ?>')no-repeat;background-size:cover;background-position:center;"></a>
						</div>

						<div class="<?php if (($numbercase) == '1') : ?>large-6<?php else : ?>large-12<?php endif; ?> medium-12 columns nopad">
							<div class="blogprevtext">
								<h3>CASE STUDY:</h3>
								<h3><a href="<?php the_permalink(); ?>" alt="Article - <?php the_title(); ?>"><?php the_title(); ?></a></h3>
								<p class="timestamp">
									<?php if (get_field('client_name')) : ?><span><i class="far fa-walking"></i><?php echo get_field('client_name'); ?></span><?php endif; ?>
									<?php if (get_field('company')) : ?><span><i class="far fa-building"></i> <?php echo get_field('company'); ?></span><?php endif; ?>
									<?php if (get_field('location')) : ?><span><i class="fa fa-map-marker-alt"></i> <?php echo get_field('location'); ?></span></p><?php endif; ?>
							<p class="excerpt"><?php $customexerpt = get_the_content();
												echo wp_trim_words($customexerpt, '14'); ?></p>
							<a href="<?php the_permalink(); ?>" alt="Case Study - <?php the_title(); ?>" class="BTN">Learn More</a>
							</div>
						</div>
					</div>
				</div>
		<?php endwhile;
		endif;
		wp_reset_postdata();
		wp_reset_query(); ?>

	</div>
</section>




<?php if (get_field('author')) : ?><span><i class="fa fa-user"></i> <?php echo get_field('author'); ?></span><?php endif; ?>