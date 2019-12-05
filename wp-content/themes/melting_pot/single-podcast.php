<?php
if( is_user_logged_in() ) {
 get_header('members');
}else {get_header();}
	
if(has_post_thumbnail()) {
$imgID  = get_post_thumbnail_id($post->ID);
$img    = wp_get_attachment_image_src($imgID,'full', false, ''); 
$imgAlt = get_post_meta($imgID,'_wp_attachment_image_alt', true);
}

global $smof_data;
$podcast_link_one = $smof_data['podcast_sub_link_one'];
$podcast_image_one = $smof_data['podcast_sub_image_one'];
$podcast_link_two = $smof_data['podcast_sub_link_two'];
$podcast_image_two = $smof_data['podcast_sub_image_two'];
?> 








</header>
<!-- Main Section -->
<section class="blogpage">


	<?php if(have_posts()): while (have_posts()): the_post(); ?>
	
<section class="blogHeader podcastHeader">
			<div class="row">
				<div class="large-8 columns">
				
				<? if(get_field('episode_number')): ?><h6><? echo the_field('episode_number')?></h6><? endif; ?>
				<h1><?php the_title(); ?></h1>
<!--				<p><?php echo get_the_category_list(); ?> <span class="entry-date"><?php echo get_the_date(); ?></span></p>-->
				<? if(get_field('podcast_author')): ?>
				<p>With <? echo the_field('podcast_author');?> &nbsp; | &nbsp; <a href="/podcast">View all Episodes</a></p>
				<? endif; ?>
			</div>
				
			<?php if($podcast_link_one): ?>
				<div class="large-4 columns">
					<div class="wrap-sub">
					<h3>Subscribe</h3>
					<p>To get episodes on your favourite player</p>
					<?php if($podcast_link_one): ?>
						<a href="<? echo ($podcast_link_one)?>"><img src="<? echo ($podcast_image_one)?>"></a>
					<?php endif; ?>
                    <?php if($podcast_link_two): ?>
						<a href="<? echo ($podcast_link_two)?>"><img src="<? echo ($podcast_image_two)?>"></a>
					<?php endif; ?>    
					</div>
				</div>
			<?php endif; ?>
		</div>
	</section>
	
	<div class="row">
		
		<?
		
			$title = get_the_title();
			$embed = get_field('audio_embed')
		?>
		
	<div class="large-12 columns">
		<? echo do_shortcode('[smart_track_player url="'. $embed .'" title="'. $title .'" ]'); ?>
	</div>
	
	<div class="large-8 columns blogContent">
        <main role="main">
        <article>
		<? if(get_field('feature_image')): ?>
		<div style="background-image:url('<? echo the_field('feature_image'); ?>')" class="Pogcast-thumb"></div>
		 <? endif; ?>
		<h2><? echo the_title(); ?></h2>	
                <?php the_content();?>
        </article>
        </main>
		
    </div>

    <div class="large-4 columns">
	<aside>
    <?php get_template_part('template-parts/sidebar-promo'); ?>
	</aside>
		
		
	<?	$arg = array(
		'post_type' => 'podcast',
		'posts_per_page' => 2,
	);
		$newQuery = new WP_Query($arg); ?>
		
		<div class="wrap-related-pod">
			<h2>Related Podcasts</h2>
			<ul>

			<?php if($newQuery->have_posts()) : while( $newQuery->have_posts()): $newQuery->the_post(); ?>

			<li class="podcast-related-item">
			  <div class="coursePrevImage" style="background: url('<? echo $img[0]; ?>')no-repeat;background-size:cover;background-position:center;">
				 <span class="postLabel"><?php echo $terms[0]->name;?></span>

					<a href="<? the_permalink();?>" class="courseImgOver"></a>
				  </div>			
				<h4><a href="<?php the_permalink();?>"><?php the_title();?></a></h4>
				 <a href="<?php the_permalink();?>"><p class="excerpt"><?php $customexerpt = get_the_content(); echo wp_trim_words( $customexerpt , '15' ); ?></p></a>

				</li>

			<?php endwhile; endif; wp_reset_query();?>


			</ul>
		</div>
		
		
		
		
		
		
		
		
		
		
		
		
		
		
    </div>

    <?php endwhile; endif;?>
</div>
</section>

<?php if( is_user_logged_in() ) {
 get_footer('members');
}else {get_footer();}
?>