<?php 
global $smof_data;
get_header("members"); 

?>
</header>

<section class="clbHeader">
	<div class="row">
		<div class="large-8 columns">
        <h1>Podcasts</h1>
		</div>
		
		<div class="large-4 columns">
			<p class="searchText">Search the Podcasts</p>
			<div class="searchfilter"><input type="text" id="filtrSearch" name="filtr-search" class="filtr-search" value="" placeholder="Enter you keyword here and press enter..." data-search>
			<a id="clearFilter" class="backBTN" href="javascript:;">Clear</a></div>
		</div>
	</div>
</section>

<!-- section-->
<section class="memberContent">
<div class="row">
		<div class="large-12 columns">
				
			 
			 <div class="filtr-container">
			 <?php while(have_posts()) : the_post();
			 	 $terms = get_the_terms( get_the_ID(), 'library_category' ); 
				 
				  $post_thumbnail_id = get_post_thumbnail_id();
				  
				 $img =  wp_get_attachment_image_url( $post_thumbnail_id , 'full');
				 if( empty($img) )
				 	$img =  DEFAULT_IMG;
				
				$class = '';	
				if(  get_field('available') == false ) {
					$class='nolink';
				}	
				 
			 ?>
				 <div class="filtr-item <?php echo $class; ?> podcast-item" data-category="<?php echo $terms[0]->term_id;?>" data-sort="value">
					 

		
			    <div class="coursePrevImage" style="background: url('<? echo $img; ?>')no-repeat;background-size:cover;background-position:center;">
				 <span class="postLabel"><?php echo $terms[0]->name;?></span>
					
					<a href="<? the_permalink();?>" class="courseImgOver"></a>
				  </div>					 
				  	<h4><a href="<?php the_permalink();?>"><?php the_title();?></a>
					</h4>
					 <div class="filterDesc">
					  <a href="<?php the_permalink();?>"><p class="excerpt"><?php $customexerpt = get_the_content(); echo wp_trim_words( $customexerpt , '15' ); ?></p></a>
						 </div>
					<!--<a class="BTN" href="<?php the_permalink();?>">View</a>-->
					 
				 </div>
			 <?php endwhile; ?>	 
				</div>
		</div>
</div>
</section>
<script>
jQuery(document).ready(function($){
var filterizd = $('.filtr-container').filterizr({
 
});
 jQuery('#filteringModeSingle li').click(function($) {
		$('#filteringModeSingle .filtr').removeClass('filtr-active');
		$(this).addClass('filtr-active');
		var filter = $(this).data('fltr');
		//filteringModeSingle.filterizr('filter', filter);
});
	
jQuery('#clearFilter').click( function($){ 
	$('#filtrSearch').val('');
	$('.filtr-container').filterizr({
	 
	});
});


});

</script>
<?php get_footer("members"); ?>