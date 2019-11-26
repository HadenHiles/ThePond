<?php 

/**
 *  Template Name: Contact Details
 */
global $smof_data;
if( is_user_logged_in() ) {
 get_header('members');
}else {get_header();}
if(has_post_thumbnail()) {
$imgID  = get_post_thumbnail_id($post->ID);
$img    = wp_get_attachment_image_src($imgID,'full', false, ''); 
$imgAlt = get_post_meta($imgID,'_wp_attachment_image_alt', true);
}
?>
<section class="Banner">
<div class="row">       
	<div class="large-6 medium-6 columns">
	<?php the_field('banner_left'); ?>
	</div>
	<div class="large-6 medium-6 columns">
	<?php the_field('banner_right'); ?>
	</div>
</div>
</section>
</header>
<!-- Main Section -->

<section class="fullwidth contactbox">
<div class="row">
	<?php 
	if(have_posts()): while (have_posts()): the_post();
	?>
	<div class="large-7 columns">
        <main role="main">
        <article>
			<div class="contactform">
			<?php the_field('form'); ?>
			</div>
       
        </article>
        </main>
    </div>
    <div class="large-5 columns">
    <?php the_content();?>
    <p><?php the_field('opening_times'); ?></p>
    <ul class="contactdetails">
    <li><i class="fal fa-phone"></i> <a href="tel:<?php the_field('telephone_number'); ?>" alt="Call"><?php the_field('telephone_number'); ?></a></li>
    <li><i class="fal fa-at"></i> <a href="mailto:<?php the_field('email_address'); ?>" alt="Email"><?php the_field('email_address'); ?></a></li>
    <li><i class="fal fa-map-marker-alt"></i> <?php the_field('physical_address'); ?></li>
    </ul>
    </div>
    <?php endwhile; endif;?>
</div>
</section>
<!-- End Main Section -->

<!-- Full Width Section -->

<section class="MainContent ">
<div class="row">
<div class="large-12 columns">
 <?php the_field('map'); ?>
</div>
</div>
</section>
<!-- End Full Width Section -->


<!-- Testimonial Section -->

<section class="blogfull hometest">
<div class="row">
 <div class="large-12 columns">
<div class="ctabox">
<p>Lorem ipsum dolor sit amet, consectetur acing elit, sed doasasaaassa. <a href="#" class="BTN whiteBTN">Find Out More</a>  
</div>
<h3>What our clients say</h3>
   <?php get_template_part('template-parts/hometestimonials'); ?>
</div>
  
</div>
</section>
<!-- End Blog Section -->
<?php get_footer();?>