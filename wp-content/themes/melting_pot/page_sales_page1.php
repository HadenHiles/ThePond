<?php 
/**
*  Template Name: Sales Page 1
*/
get_header( 'blank' ); ?>
</header>

<!-- Main Section -->


<section style="background-image:url(<?php the_field('background_image'); ?>);position:relative;" id="HeaderTopOne" class="salesBanner">
	
	<div class="salesBannerOverlay">


<div class="row">

<div class="salesHeader">

<div class="large-4 medium-6 columns">
<div class="headerlogo">
<?php if($smof_data['member_dashboard_logo']) { ?>
<img class="memberlogo" src="<?php echo $smof_data['member_dashboard_logo']; ?>" alt="<?php bloginfo( 'name' ); ?> Logo" />
<?php } else if( $smof_data['header_logo']) : ?>
<a href="/"><img class="logo" src="<?php echo $smof_data['header_logo']; ?>" alt="Logo" /></a>
<?php endif;?>
</div>
</div>

<div class="large-8 medium-6 columns">

<div class="headerBTNS">

<a href="#Pricingboxes" class="BTN">Join Now</a>
<a href="/member-login" class="textLink">Already A Member? Login</a>
</div>

</div>


</div>


<div class="large-12 columns centered">
<?php if( get_field('main_title') ): ?>
<h1 class="HeaderTopTitle"><?php the_field('main_title'); ?></h1>
<?php endif; ?>
<?php if( get_field('sub_title') ): ?>
<h2 class="HeaderTopSubTitle"><?php the_field('sub_title'); ?></h2>
<?php endif; ?> 
<?php if( get_field('cta_button_text') ): ?>
<a class="HeaderTopButton BTN" style="background-color:<?php the_field('cta_button_colour'); ?>;" href="#Pricingboxes"><?php the_field('cta_button_text'); ?></a>
<?php endif; ?>
</div>
	
		</div>
</div>	
</section>


<section id="SectionOne" class="UseThemeOption">
<div class="row">
<div class="large-10 large-push-1 columns ">
<?php if( get_field('section_2_title') ): ?>
<h2 class="SectTwoSubTitle centered">
<?php the_field('section_2_title'); ?></h2>
<?php endif; ?>

<?php if( have_rows('icon_list') ): ?>
<ul class="salesPain">
<?php while( have_rows('icon_list') ): the_row();
// vars
$listcontent = get_sub_field('list_conent');
?>
<li>
 <i class="fal fa-thumbs-down"></i> <?php echo $listcontent; ?>
</li>
<?php endwhile; ?>
</ul>
<?php endif; ?>

</div>
</div>	
</section>



<section id="SectionTwo" class="CTABannerBG">
<div class="row">
<div class="large-10 large-push-1 columns ">
<h2><?php the_field('cta_banner_text'); ?></h2>
</div></div>
</section>	


<section class="salesIntroducing">

<div class="row">

<div class="salesSectHeader centered">

<?php if( get_field('introducing_title') ): ?>
<h2>
<?php the_field('introducing_title'); ?></h2>
<?php endif; ?>

</div>

<div class="columns large-6">
<?php if( get_field('introducing_content') ): ?>

<?php the_field('introducing_content'); ?>
<?php endif; ?>
</div>

<div class="columns large-6">
<?php if( get_field('introducting_image') ): ?>

<img src="<?php the_field('introducting_image'); ?>" alt="" />
<?php endif; ?>
</div>



</div>

</section>


<section class="salesIncluded">

<div class="row">

<div class="salesSectHeader centered">

<?php if( get_field('included_heading') ): ?>
<h2>
<?php the_field('included_heading'); ?></h2>
<?php endif; ?>

</div>

<?php if( have_rows('included_items') ): ?>
<ul>
<?php while( have_rows('included_items') ): the_row();
// vars
$incimage = get_sub_field('incitemimage');
$inctitle = get_sub_field('incitem_title');
$inccontent = get_sub_field('incitem_content');
?>
<li>
<div class="inscudeItem">
<img src="<?php echo $incimage; ?>" alt="Included Item"/>
<h4><?php echo $inctitle; ?></h4>
<p><?php echo $inccontent; ?></p>
</div>
</li>
<?php endwhile; ?>
</ul>
<?php endif; ?>



</div>

</section>


<section class="salesMeet" style="background-image:url(<?php the_field('meet_background_image'); ?>);position:relative;">

<div class="row">

<div class="large-6 medium-8 columns">
	
<div class="meetOwners">

<?php if( get_field('meet_title') ): ?>
<h2>
<?php the_field('meet_title'); ?></h2>
<?php endif; ?>

<?php if( get_field('meet_intro') ): ?>
<?php the_field('meet_intro'); ?>
<?php endif; ?>
		
</div>

</div>

<div class="large-6 columns">
<?php if( get_field('meet_image') ): ?>
<img src="<?php the_field('meet_image'); ?>" alt="meet the owners" />
<?php endif; ?>

</div>

</div>

</section>

<section class="salesTestimonials">

<div class="row">

<div class="salesSectHeader centered">

<?php if( get_field('testimonial_title') ): ?>
<h2>
<?php the_field('testimonial_title'); ?></h2>
<?php endif; ?>

</div>

 <div class="large-12 columns">

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

<section id="Pricingboxes">
<?php get_template_part('template-parts/content-blocks/sales-pricing'); ?>
</section>

<section class="saleGuarantee">

<div class="row">

<div class="GuaranteeWrap">

<div class="salesSectHeader centered">
	
	<?php if( get_field('guarantee_title') ): ?>
<h2><?php the_field('guarantee_title'); ?></h2>
<?php endif; ?>
	
</div>

<div class="large-4 medium-4 columns">
	
	<div class="GuaranteeBadge">
	
<?php if( get_field('guarantee_image') ): ?>
<img src="<?php the_field('guarantee_image'); ?>"/>
<?php endif; ?>
		
		</div>

</div>

<div class="large-8 medium-8 columns">
	
<?php if( get_field('guarnatee_content') ): ?>
<?php the_field('guarnatee_content'); ?>
<?php endif; ?>

</div>
</div>
</div>
</section>



<section class="saleFAQ">
<div class="row">

<div class="salesSectHeader centered">
	
<?php if( get_field('faq_main_title') ): ?>
<h2><?php the_field('faq_main_title'); ?></h2>
<?php endif; ?>
	

<?php if( get_field('faq_sub_title') ): ?>
<p><?php the_field('faq_sub_title'); ?></p>
<?php endif; ?>



</div>


<div class="salesFAQwrap">

	<?php get_template_part('template-parts/content-blocks/accordian'); ?>
	
	
<?php if( get_field('faq_extra') ): ?>
<div class="faqExtra"><p><?php the_field('faq_extra'); ?></p></div>
<?php endif; ?>

</div>
</div>
</section>

<section id="Pricingboxes">

<?php get_template_part('template-parts/content-blocks/sales-pricing'); ?>

</section>






<!-- End Main Section -->

<?php  get_footer( 'blank' ); ?>