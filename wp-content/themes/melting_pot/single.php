<?php
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
<!-- Main Section -->
<section class="blogpage">


	<?php 
	if(have_posts()): while (have_posts()): the_post();
	?>
	
<section class="blogHeader">
			<div class="row">
				<div class="large-12 columns">
	<h1><?php the_title(); ?></h1>
<p><?php echo get_the_category_list(); ?> <span class="entry-date"><?php echo get_the_date(); ?></span></p>
			</div>
		</div>
	</section>
	
	<div class="row">
	
	<div class="large-8 columns blogContent">
        <main role="main">
        <article>
        <?php the_content();?>	
        </article>
        </main>
		
		
		
		<div class="PromoBox endOfPost">
			Add a template here for for lead generation
		</div>
		
    </div>

    <div class="large-4 columns">
	<aside>
    <?php get_template_part('template-parts/sidebar-promo'); ?>
	</aside>
    </div>

    <?php endwhile; endif;?>
</div>
</section>

<?php 
if( is_user_logged_in() ) {
 get_footer('members');
}else {get_footer();}
?>