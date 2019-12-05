<?/***  Template Name: Member Register */ get_header("members");?>
</header>
<?php  if ( get_field('hide_header') == 'keepHead'): ?>	
<section class="memberDashWelc">
<div class="row">
<div class="large-8 medium-8 columns">
<h1><?php the_title(); ?></h1>
</div>
</div>
</section>
<?php endif; ?>
<section class="login-contain">
<div class="fullWidth">
<div class="row">
<? if(have_posts()): while (have_posts()): the_post(); ?>
<div class="large-8 medium-8 columns">
<main>
	
	
	
<?php the_content();?>
	
<div class="trustIcons"></div>
	
</main> 	  
</div>
<div class="large-4 medium-4 columns">

<div class="memberSideBar">
		
<?php get_template_part('template-parts/members/member-sidebar'); ?>
	
</div>
	
	
	
</div>
<? endwhile; endif;?>
</div>
</div><!-- End Main Content -->
</section>
<?  get_footer("members"); ?>