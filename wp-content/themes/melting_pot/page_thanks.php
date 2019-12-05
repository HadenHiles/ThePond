<?php 

/**
 *  Template Name: Thanks
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
</header>
<?php get_template_part ('template-parts/content-blocks/bannerheader'); ?>
<!-- Main Section -->

<section class="fullwidth contactbox">
<div class="row">
	
	
	
	
	
	<?php if (is_front_page() ) {
        echo '<div class="col-md-8 la-content-inside col-md-offset-2">';
        while ( have_posts() ) { the_post(); }
    } else {
        echo '<div class="col-md-8 la-content-inside">';
        while ( have_posts() ) { the_post(); }
    }
    ?>
	
	
	<?php 
	if(have_posts()): while (have_posts()): the_post();
	?>
	<div class="large-12 columns">
        <main role="main">
       
			
	<style>
		.show<?php echo $_GET['score']; ?>{display:block !important;}
		.ElseNoShow{display:none;}
	</style>	
	<?php if( have_rows('results') ): ?>
			<ul class="">
				<?php while( have_rows('results') ): the_row();
					// vars
						$Myscore = get_sub_field('show_if_score_is');
						$content = get_sub_field('thanks_content');
						?>
				
				
				<li class="slide show<?php echo $Myscore; ?> ElseNoShow">
					
					
					<?php if( $Myscore ): ?>
					
						<?php endif; ?>
						<img src="<?php echo $image['url']; ?>" alt="<?php echo $image['alt'] ?>"/>
						<?php if( $link ): ?>
					</a>
					<?php endif; ?>
					<div class="large-12 columns slideInner text-center">
		 				<h1>
							<?php echo $Btitle; ?>
						</h1>
						<p>
							<?php echo $content; ?>
						</p>
						<a class="MoreLink" href="<?php echo $link; ?>">Find Out More</a>
					</div>
				</li>
		
				<?php endwhile; ?>
			</ul>
			<?php endif; ?>
			
			
			
        </main>
    </div>

    <?php endwhile; endif;?>
</div>
</section>
<!-- End Main Section -->





<!-- End Blog Section -->
<?php
/* Template Name: Logged in Footer*/
if (is_user_logged_in()):
get_footer('members');
else: 
get_footer();
endif;
?>