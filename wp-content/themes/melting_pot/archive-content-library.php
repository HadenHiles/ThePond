<?php 
global $smof_data;
get_header("members"); 

$rename_content_library = $smof_data['rename_content_library']  ? $smof_data['rename_content_library'] : "Content Library";
?> 
</header>

<section class="clbHeader">
	<div class="row">
		<div class="large-8 columns">
        <h1><?php echo $rename_content_library; ?></h1>
		</div>
		
		<div class="large-4 columns">
			<p class="searchText">Search the <?php echo $rename_content_library; ?></p>
			<div class="searchfilter"><input type="text" id="filtrSearch" name="filtr-search" class="filtr-search" value="" placeholder="Enter you keyword here and press enter..." data-search>
			<a id="clearFilter" class="backBTN" href="javascript:;">Clear</a></div>
		</div>
	</div>
</section>

<!-- section-->
<section class="memberContent">
<div class="row">
		<div class="large-12 columns">
			<?php
				$categories = get_terms( 'library_category', array(
					'orderby'    => 'count',
					'hide_empty' => 0
				) );
			
			
			if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
			?>
            	<ul class="clFilters" id="filteringModeSingle"> 
				 <li class="filtr filtr-active" data-filter="all"> All </li>
				<?php foreach( $categories as $cat) { ?>
				 
				 <li class="filtr" data-filter="<?php echo $cat->term_id;?>"> <?php echo $cat->name;?> </li>
				 
				 <?php } ?>
				  
				</ul>
				
			 <?php } ?>	
			 
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
				 <div class="filtr-item <?php echo $class; ?>" data-category="<?php echo $terms[0]->term_id;?>" data-sort="value">
					 

		
			    <div class="coursePrevImage" style="background: url('<? echo $img; ?>')no-repeat;background-size:cover;background-position:center;">
				<?php if ($terms[0]): ?> <span class="postLabel"><?php echo $terms[0]->name;?></span><?php endif; ?>
					 
		 <?php if( get_field('available') ){ ?>
					
			<a href="<? the_permalink();?>" class="courseImgOver"></a>
			  
			  
				  
			  </div>

            <? }else {  ?>
				  <div class="CourseSoon">Coming Soon</div>
				  
			  </div>
			
			<?php } ?>
					 
				  	<h4>
					 <?php if( get_field('available') ){ ?>
					<a href="<?php the_permalink();?>"><?php the_title();?></a>
					 <?php }else{ ?>
					 <?php the_title();?>
					 <?php } ?>
					 
					</h4>
					 <div class="filterDesc">
					 <?php the_field('description_short'); ?>
						 </div>
					<!--<a class="BTN" href="<?php the_permalink();?>">View</a>-->
					 
				 </div>
			 <?php endwhile; ?>	 
				</div>
		</div>
</div>
</section>
<script type="text/javascript">
(function($){
	$(document).ready(function(){
		var filterizd = $('.filtr-container').filterizr({});
		$('#filteringModeSingle li').click(function() {
			$('#filteringModeSingle .filtr').removeClass('filtr-active');
			$(this).addClass('filtr-active');
			var filter = $(this).data('fltr');
			//filteringModeSingle.filterizr('filter', filter);
		});
			
		$('#clearFilter').click( function(){ 
			$('#filtrSearch').val('');
			$('.filtr-container').filterizr({});
		});
	});
})(jQuery);

</script>
<?php get_footer("members"); ?>
