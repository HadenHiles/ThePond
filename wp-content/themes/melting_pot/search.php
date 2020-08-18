 <?php get_header('members'); ?>
</header>
 
<section class="memberDashWelc">
	<div class="row">
		<div class="large-12 columns centered">
			<h1 class="page-title">
<?php _e( 'Search results for:', 'meltdefault' ); ?>
 <span class="searchResult"><?php echo get_search_query(); ?></span>
</h1>
		
			<div class="searchResults">
		<form role="search" method="get" class="search-form" action="<?php echo site_url();?>">
				    <input type="search" class="search-field" placeholder="Search..." value="<?php echo get_search_query(); ?>" name="s" title="Search">
				    <input type="submit" class="search-submit" value="Search">
				</form>
			</div>
			</div>
	</div>
</section> 
<!-- section-->
<section class="searchContent">
<div class="row">

	<?php if ( have_posts() ) : ?>
		<div class="large-12 columns">
			
			
		 
            	<ul class="clFilters"> 
				 <li class="active" data-filter="all"> All </li>
			  	 <li data-filter="sfwd-courses"> <?php echo "Course"; ?> </li>
				 <li data-filter="sfwd-lessons"> <?php echo "Lesson"; ?> </li>
				 <li data-filter="sfwd-drill"> <?php echo "Drill"; ?> </li>
				 <li data-filter="content-library"> <?php echo "Challenge/Resource"; ?> </li>
				</ul>
				
		 
			
			<div class="filtr-container">
			<?php 
			while(have_posts()):the_post();
                $terms = get_the_terms( get_the_ID(), 'category' ); 
			$post_thumbnail_id = get_post_thumbnail_id();
				 $img =  wp_get_attachment_image_url( $post_thumbnail_id , 'full');
				 if( empty($img) )
				 	$img =  DEFAULT_IMG;
			?>
				 <div class="filtr-item" data-category="<?php echo get_post_type(get_the_ID());?>" data-sort="value">
 
                <div class="searchItem">
                    <div class="searchtext">
                        
                <div class="coursePrevImage" style="background: url('<? echo $img; ?>')no-repeat;background-size:cover;background-position:center;">
				<span class="postLabel"><?php 
						$obj = get_post_type_object(get_post_type() );
						echo $obj->labels->singular_name;
						$taxonomy = '';
						$taxonomy_names = get_post_taxonomies(get_the_ID() );
						if( !empty($taxonomy_names) ) {
							if( in_array('product_cat' , $taxonomy_names) ){
								$taxonomy = 'product_cat';
						 	}elseif( in_array('ld_lesson_category' , $taxonomy_names) ){
								$taxonomy = 'ld_lesson_category';
						 	}else{
								$taxonomy = 'category';		
							 }	
						
						}
   						 
						 ?>
                </span>
			    <a href="<? the_permalink();?>" class="courseImgOver"></a>
			    </div>    
                    
                <h4><a href="<?php the_permalink();?>" alt="Article - <?php the_title();?>"><?php the_title();?></a></h4> 
						
		<!--			<?php if( !empty($taxonomy) ){ 
						$categories = get_the_terms(get_the_ID(), $taxonomy );
						if( $categories ) {
							foreach( $categories as $term ) { ?>
							<a href="<?php echo get_term_link($term);?>" > <?php echo $term->name; ?></a>
                        
					 	<?php } 
						
							}  
						
						}?>-->
                     <div class="filterDesc">
					 <?php the_excerpt()?>
					 </div>
                    <a href="<?php the_permalink();?>" alt="<?php the_title();?>" class="BTN">Take A Look</a>
                    </div> 
                </div>

				 </div>
            <?php endwhile; ?>	 
           		
		    </div>
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
           
    </div>
	<?php else : ?>
		<p class="searchNothingFound"><?php _e( 'Sorry, but nothing matched your search terms. Please try again with a different keyword or phrase.', 'meltdefault' ); ?></p>
	<?php endif;?>
</div>
</section>

<?php get_footer('members'); ?>