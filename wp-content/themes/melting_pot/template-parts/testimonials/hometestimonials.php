<?php

if ( get_field( 'select_testimonials' ) ) {
	$testids = get_field( 'select_testimonials' );
} else {
	$testids = '';
}

$numbertest = get_field('number_of_testimonial_columns')

?>

<div class="testimonials noslide">
	<?php
	$args = array( 
		'posts_per_page' => $numbertest,
		'orderby' => 'rand',
		'post_type' => 'testimonials',
		'post__in' => $testids
	);
	$the_query = new WP_Query( $args );
	?>
	<?php if ( $the_query->have_posts() ) : ?>
	<?php while ( $the_query->have_posts() ) : $the_query->the_post(); 
	$img = wp_get_attachment_url(get_post_thumbnail_id($post->ID),"Full"); 
	?>
	<div class="<?php if (($numbertest) == '1'): ?>large-12<?php endif; ?> <?php if (($numbertest) == '2'): ?>large-6 medium-12<?php endif; ?> <?php if (($numbertest) == '3'): ?>large-4 medium-12<?php endif; ?>  columns">
		<div class="testimonial">
			<h4>"<?php the_title();?>"</h4>
			<?php the_content();?>
			<div class="testimage" style="background: url('<?php echo $img;?>')no-repeat;background-size:cover;background-position:center;"></div>
			<div class="testcontent">
				<p class="starrating"><?php the_field('star_rating'); ?></p>
				<p class="author"><strong><?php the_field('testimonial_author'); ?></strong></p>
				<p class="locationcompany"><?php the_field('location_company'); ?></p>
			</div>
		</div>
	</div>
	<?php endwhile; ?>
	<?php endif; ?>
	<?php wp_reset_postdata(); wp_reset_query(); ?>
</div>


