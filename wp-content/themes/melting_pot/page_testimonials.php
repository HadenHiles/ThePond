<?php 
global $smof_data;
/* Template Name: Testiomonials */
get_header();
if(has_post_thumbnail()) {
$imgID  = get_post_thumbnail_id($post->ID);
$img    = wp_get_attachment_image_src($imgID,'full', false, ''); 
$imgAlt = get_post_meta($imgID,'_wp_attachment_image_alt', true);
}

if (get_field('select_testimonials')){
$testids = get_field('select_testimonials');
}
else {
$testids = '';
}

if (get_field('exclude_testimonials')){
$excludeids = get_field('exclude_testimonials');
}
else {
$excludeids = '';
}


?>
<section class="Banner TestimonialHeader" style="background: url('<?php the_field('banner_image'); ?>')no-repeat;background-size:cover;background-position:center;">
<div class="row">       
	<div class="large-7 medium-7 columns">
	<?php the_field('banner_left'); ?>
	</div>
	<div class="large-5 medium-5 columns">
	<?php the_field('banner_right'); ?>
	</div>
</div>
</section>
</header>

<!-- section-->
<section class="MainContent testimonialpage">
<div class="row">
		<div class="large-12 columns">
        
        <main>
            <ul class="<?php the_field('test_columns'); ?>">
	<?php
		$args=array( 'posts_per_page'=>'-1', 'orderby' => 'rand', 'post_type'=>'testimonials', 'post__in'=>$testids, 'post__not_in'=>$excludeids) ;
		$the_query = new WP_Query( $args );
		?>
<?php if ( $the_query->have_posts() ) : ?>
<?php while ( $the_query->have_posts() ) : $the_query->the_post(); $img = wp_get_attachment_url(get_post_thumbnail_id($post->ID),"Full"); ?>
            <li class="testimonial ">
							
                    
			    	<h4>"<?php the_title();?>"</h4>
				    <?php the_content();?>
				    <div class="testimage" style="background: url('<?php echo $img;?>')no-repeat;background-size:cover;background-position:center;"></div>
                    <div class="testcontent">
                    <p class="starrating"><?php the_field('star_rating'); ?></p>
                    <p class="author"><strong><?php the_field('testimonial_author'); ?></strong></p>
                    <p class="locationcompany"><?php the_field('location_company'); ?></p>
                    </div>
						
			</li>
			<?php  endwhile; ?>
             </ul>
			<?php endif;wp_reset_query();?>
        </main>
		</div>

</div>
</section>

<?php get_footer(); ?>
