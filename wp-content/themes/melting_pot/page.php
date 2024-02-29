<?php 
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
<section class="MainContent">
<div class="row">
	<?php 
	if(have_posts()): while (have_posts()): the_post();
	?>
	<div class="<?php  if ( get_field('show_sidebar') == 'yes'): ?>large-8<?php else: ?>large-12<?php endif; ?> columns">
        <main role="main">
        <article>
        <?php the_content();?>	
        </article>
        </main>
    </div>
    <?php  if ( get_field('show_sidebar') == 'yes'): ?>
    <div class="large-4 columns">
    <?php the_field('sidebar'); ?>
    <?php  if ( get_field('show_testimonial_in_sidebar') == 'yes'): ?>
    <?php get_template_part ('template-parts/testimonials'); ?>
    <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endwhile; endif;?>
</div>
</section>
<!-- End Main Section -->

<!-- Full Width Section -->
<?php  if ( get_field('show_full_width_section') == 'yes'): ?>		
<section class="fullwidth">
<div class="row">
<div class="large-12 columns">
<?php the_field('full_width_section'); ?>   
</div>
</div>
</section>
<?php endif; ?>
<!-- End Full Width Section -->


<!-- Testimonial Section -->
<?php 
get_footer("members");
?>