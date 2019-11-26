<?/* Template Name: Member Login */

if ( get_field('hide_header') == 'hidehead'):
get_header('blank');
else: 
get_header('members');
endif;
?>
</header>

<?php get_template_part('template-parts/members/member-page-header'); ?>

<section class="login-contain <?php  if ( get_field('template_selection') == 'simple'): ?>backgroundlogin<?php endif; ?>" <?php  if ( get_field('template_selection') == 'simple'): ?>style="background-image: url('<?php echo $img;?>');"<?php endif; ?>>

<div class="row">
<? if(have_posts()): while (have_posts()): the_post(); ?>
<?php  if ( get_field('template_selection') == 'simple'): ?>
<div class="large-6 columns centered">
	
<?php if (get_field('login_logo')): ?>
<img class="loginLogo" src="<?php the_field('login_logo'); ?>" alt="<?php bloginfo( 'name' ); ?> Logo" />
<?php else: ?>
 <?php if($smof_data['member_dashboard_logo']) { ?>
        <a href="/dashboard/"><img class="memberloginlogo" src="<?php echo $smof_data['member_dashboard_logo']; ?>" alt="Logo" /></a>
        <?php } else if( $smof_data['header_logo']) : ?>
        <a href="/"><img class="logo" src="<?php echo $smof_data['header_logo']; ?>" alt="Logo" /></a>
        <?php endif;?>	
<?php endif; ?>
	

	

	<div class="memberLoginWrapper">
<?php the_content();?>  
		</div>
</div>
<?php else: ?>
<div class="large-8 columns">
<h1><?php the_title(); ?></h1>
<?php the_content();?>
</div>
<div class="large-4 columns">
<aside>
<?php get_template_part('template-parts/members/member-sidebar'); ?>
</aside>
</div>
<?php endif; ?>
<? endwhile; endif;?>
</div><!-- End Main Content -->
</section>
<?php
/* Template Name: Member Blank*/
if ( get_field('hide_footer') == 'hidefoot'):
get_footer('blank');
else: 
get_footer('members');
endif;
?>