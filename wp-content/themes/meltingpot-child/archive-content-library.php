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
<section class="memberbenefits dashboardbenefits" style="min-height: 80vh;">
	<div class="challenges-wrapper">
		<?php
			$categories = get_terms( 'library_category', array(
				'orderby'    => 'count',
				'hide_empty' => 0
			) );
		
		
		if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
		?>
			<ul class="clFilters" id="filteringModeSingle"> 
				<li class="filtr filtr-active" data-filter="all"> All </li>
			<?php foreach( $categories as $cat) {
				?>
				<li class="filtr" data-filter="<?php echo $cat->term_id;?>"> <?php echo $cat->name;?> </li>
				<?php
			} ?>
				
			</ul>
			
			<?php } ?>	
			
			<div class="filtr-container bootstrap-styles challenges">
				<?php 
				while (have_posts()) : the_post();
					$terms = get_the_terms(get_the_ID(), 'library_category'); 
					
					$post_thumbnail_id = get_post_thumbnail_id();
					
					$img = wp_get_attachment_image_url( $post_thumbnail_id , 'full');
					if (empty($img))
						$img = '/wp-content/themes/meltingpot-child/images/placeholder.png';
				
					$class = '';	
					if (get_field('available') == false) {
						$class='nolink';
					}	

					$termsString = '';
					foreach ($terms as $term) {
						$termsString .= $term->term_id . ',';
					}

					?>
					<a href="<?php if(get_field('available')) { ?><? the_permalink(); } ?>" class="card shadow challenge <?php echo $class; ?>" data-category="<?=$termsString?>" data-sort="value">
						<div class="card-img-top" style="background-image: url('<? echo $img; ?>'); overflow: hidden; position: relative;">
							<?php 
							foreach ($terms as $term) {
								?> 
								<span class="postLabel" style="float: right; margin: 10px 10px 0 0;"><?=$term->name;?></span>
								<?php 
							}
							
							if(!get_field('available')) {
								?>
								<div class="CourseSoon" style="top: auto; left: auto; bottom: 25px; right: -150px; transform: rotate(-35deg); -webkit-transform: rotate(-35deg);">Coming Soon</div>
								<?php 
							} 
							?>
						</div>
						<div class="card-body">
							<h4><? the_title(); ?></h4>
							<p class="filterDesc card-text"><?php the_field('description_short'); ?></p>
						</div>
					</a>
					<?php
				endwhile;
				?>	 
			</div>
	</div>
</section>
<script type="text/javascript">
(function($){
	$(document).ready(function(){
		var filterizd = $('.challenges').filterizr({
			gridItemsSelector: '.challenge',
			callbacks: {
				onInit: removeFilterizrStyles()
			}
		});
		$('#filteringModeSingle li').click(function() {
			$('#filteringModeSingle .filtr').removeClass('filtr-active');
			$(this).addClass('filtr-active');
			var filter = $(this).data('fltr');
			removeFilterizrStyles();
		});
			
		$('#clearFilter').click( function(){ 
			$('#filtrSearch').val('');
			$('.challenges').filterizr({
				gridItemsSelector: '.challenge',
				callbacks: {
					onInit: removeFilterizrStyles()
				}
			});
		});
	});

	$(window).on('resize', function() {
		setTimeout(() => {
			removeFilterizrStyles();
		}, 50);
	});

	function removeFilterizrStyles() {
		setTimeout(() => {
			$('.challenges').attr('style', '').children('.challenge').each(function() {
				$(this).attr('style', '');
			});
		}, 50);
	}
})(jQuery);

</script>
<?php get_footer("members"); ?>
