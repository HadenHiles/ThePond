<?php ?>

<section class="testimonialsection">
	<div class="headerWrap testimonials">
		<div class="row">
			<div class="regular slider">

				<?php
				$args = array(
					'post_type' => 'testimonials',
					'post_status' => 'publish',
					'posts_per_page' => 3,
					'orderby' => 'rand'
				);
				$testimonials = query_posts($args);
				if ($testimonials) : ?>
					<?php while (have_posts($testimonials)) : the_post();
						$imgID  = get_post_thumbnail_id($post->ID);
						$img    = wp_get_attachment_image_src($imgID, 'full', false, '');
						$imgAlt = get_post_meta($imgID, '_wp_attachment_image_alt', true);
					?>
						<div>
							<div class="large-7 columns">
								<p><?php echo get_field('short_title'); ?></p>
							</div>
							<div class="large-5 columns">
								<span><?php echo get_field('tesimtonial_author'); ?><br><?php echo get_field('author_location'); ?></span>
							</div>
						</div>
					<?php endwhile; ?>
				<?php endif;
				wp_reset_query();
				wp_reset_postdata(); ?>

			</div>
		</div>
	</div>
</section>