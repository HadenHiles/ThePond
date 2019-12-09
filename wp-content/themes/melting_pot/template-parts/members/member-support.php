<?php ?>
<main role="main">
<article>
<?php if( get_field('sub_header') ): ?>
<h2><?php the_field('sub_header'); ?></h2>
<?php endif; ?>
	
<?php if( get_field('above_media') ): ?>
<?php the_field('above_media'); ?>
<?php endif; ?>
<div class="faqs">
<?php get_template_part('template-parts/content-blocks/accordian'); ?>
</div>
<?php if( get_field('below_media') ): ?>
<?php the_field('below_media'); ?>
<?php endif; ?>
<?php 
$FormID = get_field('form_id'); 
?>
<div class="membSupportForm">
<?php echo do_shortcode('[gravityform id="' . $FormID .  '" title="false" description="false" tabindex="10"]'); ?>
</div>
</article>
</main>