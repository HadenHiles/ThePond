<?php 
/**
 *  Template Name: Elementor with Head & Footer
 */
global $smof_data;
get_header("members");
if(has_post_thumbnail()) {
$imgID  = get_post_thumbnail_id($post->ID);
$img    = wp_get_attachment_image_src($imgID,'full', false, ''); 
$imgAlt = get_post_meta($imgID,'_wp_attachment_image_alt', true);
}
?>
<?php get_template_part ('template-parts/bannerheader'); ?>
</header>
<!-- Main Section -->
<section class="MainContent BlankTemp">

	<?php 
	if(have_posts()): while (have_posts()): the_post();
	?>

        <?php the_content();?>	

    <?php endwhile; endif;?>
</div>
</section>
<!-- End Main Section -->

<!-- Testimonial Section -->
<?php 
get_footer("members");
?>