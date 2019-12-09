<?php 
/* Template Name: Home */
global $smof_data;
 get_header();
if(has_post_thumbnail()) {
$image = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'large' );
}
?> 			
<section class="Banner" style="background-image: <?php if ( get_field('banner_background')): ?>url('<?php the_field('banner_background'); ?>');<?php else:?><?php endif; ?>" >
<div class="row">       
	<div class="<?php  if ( get_field('banner_media') == 'none'): ?>large-12<?php else: ?>large-6 <?php endif; ?>medium-12 columns">
	<h1><?php the_field('banner_header'); ?></h1>
    <h2><?php the_field('banner_sub_header'); ?></h2>
    <a href="<?php the_field('button_url'); ?>" class="BTN"><?php the_field('main_cta_button'); ?></a>   

    </div>
    
    
    
    <?php  if ( get_field('banner_media') == 'none'): ?>
    <?php else: ?>
        <div class="large-6 medium-12 columns">
        <?php  if ( get_field('banner_media') == 'image'): ?>
        <img src="<?php the_field('banner_image'); ?>" alt="Home Banner Image">
        <?php endif; ?>
            
        <?php  if ( get_field('banner_media') == 'video'): ?>
        <div class="videoWrapper"><?php the_field('banner_video'); ?></div>
        <?php endif; ?>    
        </div>
    <?php endif; ?>
    

    
    
</div>
</section>
</header>

<?php  if ( get_field('show_pain_points') == 'yes'): ?>
<section class="MainContent servicesection PainpointsSection">
<div class="row">
    <div class="painpoints">
        <h3><?php the_field('pain_points_header'); ?></h3>
        <p><?php the_field('pain_points_header'); ?></p>
            <ul class="clearfix">
            <?php while( have_rows('pain_points') ): the_row(); 
            // vars
            $item = get_sub_field('pain_point_item');?>
            <li><?php echo $item ?></li>
            <?php endwhile; ?>
            </ul>
        <a href="<?php the_field('button_url'); ?>" class="BTN"><?php the_field('main_cta_button'); ?></a>
    </div>
</div>
</section>
<?php endif; ?>  

<!-- Main Section -->
<section class="MainContent homeContent">
<div class="row">
    
        <?php  if ( get_field('main_image')): ?>
        <div class="large-6 columns">
        <img src="<?php the_field('main_image'); ?>">
        </div>
        <?php endif; ?>
        <div class="<?php  if ( get_field('main_image')): ?>large-6<?php else: ?>large-12<?php endif; ?> columns">
        <div class="homemain">
        <?php the_field('main_section_text'); ?>
        </div>
        </div>
</div>
</section>
<!-- End Main Section -->

<section class="MainContent servicesection">
<div class="row">
<h3><?php the_field('service_heading'); ?></h3>
<?php the_field('service_sub_heading'); ?>
<ul class="services">
<?php while( have_rows('service_repeater') ): the_row(); 
// vars
$title = get_sub_field('title');
$link= get_sub_field('link');
$image = get_sub_field('image');
$text= get_sub_field('service_text');
?>

<li class="large-4 columns">
<a class="service" href="<?php echo $link ?>">
<div class="serviceimage"p>
<img src="<?php echo $image ?>" alt="<?php echo $title ?> icon">
</div>
<h4><?php echo $title ?></h4>
<p><?php echo $text ?></p>
<span class="BTN">Learn More</span>
</a>
</li>
<?php endwhile; ?>
</ul>  

</div>
</section>


<?php get_template_part('template-parts/content/theproccess'); ?>

<?php get_template_part('template-parts/leadgen/modal-guide'); ?>


<!-- Blog Section -->
<?php  if ( get_field('show_testimonial') == 'yes'): ?>
<section class="blogfull hometest">
<div class="row">
 <div class="large-12 columns">
<div class="ctabox">
<p>Lorem ipsum dolor sit amet, consectetur acing elit, sed doasasaaassa. <a href="#" class="BTN whiteBTN">Find Out More</a>  
</div>
 <div class="posttitle">   
<h3><?php the_field('testimonials_heading'); ?></h3>
<p><?php the_field('testimonials_sub_heading'); ?></p>
</div>
	<?php 
	$display_as = 'hometestimonials';
	 if( get_field('display_as') == 'slider' ) {
			 $display_as = 'hometestimonials-slider';
		}
	?>
   <?php get_template_part('template-parts/testimonials/'.$display_as); ?>
</div>
  
</div>
</section>

<?php  if ( get_field('show_blog') == 'yes'): ?>
<?php get_template_part('template-parts/posts/blogs'); ?>
<?php endif; ?>

<?php  if ( get_field('show_case_study') == 'yes'): ?>					
<?php get_template_part('template-parts/posts/case-study'); ?>
<?php endif; ?>

<?php get_template_part('template-parts/leadgen/form-guide'); ?>



<?php endif; ?>
<!-- End Blog Section -->
<?php get_footer();?>