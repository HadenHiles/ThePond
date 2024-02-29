 <?php get_header('members'); ?>
</header>

<?php  if ( get_field('hide_header') == 'keepHead'): ?>	
<section class="memberDashWelc">
	<div class="row">
		<div class="large-12 columns">
			<h1>Blog Posts</h1>
		</div>
	</div>
</section>
<?php endif; ?>
<!-- section-->
<section class="MainContent blogpage">
<div class="row">
		<div class="large-12 columns">
			
			
				<?php
				$categories = get_terms( 'category', array(
					'orderby'    => 'count',
					'hide_empty' => 0
				) );
			
			
			if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
			?>
            	<ul class="clFilters"> 
				 <li data-filter="all"> All </li>
				<?php foreach( $categories as $cat) { ?>
				 
				 <li data-filter="<?php echo $cat->term_id;?>"> <?php echo $cat->name;?> </li>
				 
				 <?php } ?>
				  
				</ul>
				
			 <?php } ?>	
			
			<div class="filtr-container">
			<?php 
			while(have_posts()):the_post();
                $terms = get_the_terms( get_the_ID(), 'category' ); 
			$imgID  = get_post_thumbnail_id($post->ID);
			$img    = wp_get_attachment_image_src($imgID,'full', false, ''); 
			$imgAlt = get_post_meta($imgID,'_wp_attachment_image_alt', true);
			if( empty($img[0]))
				$img[0] = DEFAULT_IMG;
				
			?>
				 <div class="filtr-item" data-category="<?php echo $terms[0]->term_id;?>" data-sort="value">
 
             	<div class="blogpreview">
		
        <div class="large-12 medium-12 columns nopad">
			<a href="<?php the_permalink();?>" class="blogprevimage shorterprevimage" style="background: url('<?php echo $img[0];?>')no-repeat;background-size:cover;background-position:center;"></a>
        </div>

        <div class="large-12 medium-12 columns nopad">
        <div class="blogprevtext">
		<h3><a href="<?php the_permalink();?>" alt="Article - <?php the_title();?>"><?php the_title();?></a></h3>
		<p class="timestamp"><?php  if ( get_field('author')): ?><span><i class="fa fa-user"></i> <?php the_field('author'); ?></span><?php endif; ?> <span><i class="far fa-calendar-alt"></i> <?php echo get_the_date();?></span> <span><i class="far fa-folder"></i> <?php $cat = get_the_category(); echo $cat[0]->name?></span></p>
		<p class="excerpt"><?php $customexerpt = get_the_content(); echo wp_trim_words( $customexerpt , '12' ); ?></p>
        <a href="<?php the_permalink();?>" alt="Article - <?php the_title();?>" class="BTN">Read More</a>
	    </div>
        </div>
    </div>

				 </div>
            <?php endwhile; ?>	 
           		
		    </div>
			  <nav class="navigation paging-navigation" role="navigation">

	 

		<div class="nav-links">



			<?php if ( get_next_posts_link() ) : ?>

			<div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'battle' ) ); ?></div>

			<?php endif; ?>



			<?php if ( get_previous_posts_link() ) : ?>

			<div class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'battle' ) ); ?></div>

			<?php endif; ?>



		</div><!-- .nav-links -->

	</nav>
            
            
            

			<?php 
			wp_reset_query();
			?>
    </div>
</div>
</section>
<script>
jQuery(document).ready(function($){
var filterizd = $('.filtr-container').filterizr({
 
});
jQuery('#clearFilter').click( function($){ 
	$('#filtrSearch').val('');
	$('.filtr-container').filterizr({
	 
	});
});


});

</script>
<?php get_footer('members'); ?>