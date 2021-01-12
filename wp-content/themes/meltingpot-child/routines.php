<?php
/**
* Template Name: Challenges
*
* @package WordPress
* @subpackage Meltingpot-child
* @since Twenty Twenty
*/
global $smof_data;
get_header("members"); 
?> 
</header>

<section class="clbHeader">
	<div class="row">
		<div class="large-8 columns">
        <h1>Routines</h1>
		</div>
		
		<div class="large-4 columns">
			<!-- <p class="searchText">Search Challenges</p> -->
			<div class="searchfilter" style="margin-top: 5px;"><input type="text" id="filterSearch" name="filtr-search" class="filtr-search" value="" placeholder="Search challenges" data-search>
			<a id="clearFilter" class="backBTN" href="javascript:;">Clear</a></div>
		</div>
	</div>
</section>

<!-- section-->
<section class="dashboardbenefits" style="min-height: 80vh; padding: 0 0 2rem 0;">
	<?php
	$challengesQuery = new WP_Query( array(
		'posts_per_page' => $limit,
		'post_status'    => 'publish',
		'post_type' => 'content-library',
		'order' => 'desc',
		'orderby' => 'post_date',
		'suppress_filters' => true,
		'tax_query' => array(
			array(
				'taxonomy' => 'library_category',
				'field' => 'slug',
				'terms' => 'routines', //pass your term name here
				'include_children' => true
			)
		)
	));

	$latestChallenge = $challengesQuery->posts[0];
	$title = get_the_title($latestChallenge->ID);
	$shortDescription = get_field('description_short', $latestChallenge->ID);
	$videoCode = get_field('video_code', $latestChallenge->ID);
	if (empty($videoCode)) {
		$post_thumbnail_id = get_post_thumbnail_id($latestChallenge);
		$img = wp_get_attachment_image_url( $post_thumbnail_id , 'full');
		if (empty($img))
			$img = '/wp-content/themes/meltingpot-child/images/placeholder.png';

		$videoCode = '<img src="' . $img . '" alt="' . $title . '" />';

	}
	?>
	<div class="challenges-wrapper">
		<?php
			$categories = get_terms( 'skill-type', array(
				'orderby'    => 'count',
				'hide_empty' => 0
			) );
		
		
		if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
		?>
			<ul class="clFilters" id="filteringModeSingle"> 
				<li class="filtertoggle filtr-active" data-filter="all"> All </li>
			<?php foreach( $categories as $cat) {
				?>
				<li class="filtertoggle" data-filter="<?php echo $cat->term_id;?>"> <?php echo $cat->name;?> </li>
				<?php
			} ?>
				
			</ul>
			
			<?php } ?>	
			
			<div class="bootstrap-styles challenges">
				<?php
				while ($challengesQuery->have_posts()) : $challengesQuery->the_post();
					$isLatestChallenge = ($latestChallenge->ID == get_the_ID());
					$terms = get_the_terms(get_the_ID(), 'skill-type'); 
					
					$post_thumbnail_id = get_post_thumbnail_id();
					
					$img = wp_get_attachment_image_url( $post_thumbnail_id , 'full');
					if (empty($img))
						$img = '/wp-content/themes/meltingpot-child/images/placeholder.png';

					$termsString = '';
					foreach ($terms as $term) {
						$termsString .= $term->term_id . ',';
					}

					?>
					<a 	href="<?php the_permalink(); ?>"
						class="card shadow challenge filter"
						data-category="<?=$termsString?>"
						data-sort="value"
						data-search="<?php the_title(); ?>\n<?php the_field('description_short'); ?>"
					>
						<?php
						$isMembersOnlyChallenge = false;
						if(get_field('available') && !current_user_can('memberpress_authorized')) {
							$isMembersOnlyChallenge = true;
						}

						if (!$isMembersOnlyChallenge) {
							?>
							<div class="card-img-top" style="background-image: url('<? echo $img; ?>'); overflow: hidden; position: relative;">
							<?php
						} else {
							?>
							<div class="card-img-top" style="background-image: url('<? echo $img; ?>'); overflow: hidden; position: relative; filter: blur(5px) brightness(80%); -webkit-filter: blur(5px) brightness(80%);"></div>
							<div class="card-img-top" style="overflow: hidden; position: absolute;">
							<?php
						}
							foreach ($terms as $term) {
								?> 
								<span class="postLabel" style="float: right; margin: 10px 10px 0 0;"><?=$term->name;?></span>
								<?php 
							}
							
							if(!get_field('available') && (!$isLatestChallenge || current_user_can('memberpress_authorized'))) {
								?>
								<div class="CourseSoon" style="bottom: 0; right: 0; left: 0;">Coming Soon</div>
								<?php
							} else if (!current_user_can('memberpress_authorized')) {
								?>
								<div class="CourseSoon" style="background: none; top: 42%; bottom: 0; right: 0; left: 0; font-family: 'Teko', Noto-sans, sans-serif; font-size: 2rem;">Members Only</div>
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
		$('#filteringModeSingle .filtertoggle').click(function() {
            $('#filteringModeSingle .filtertoggle').removeClass('filtr-active');
            $(this).addClass('filtr-active');

            $('.challenges').filterize($(this).data('filter'), $('#filterSearch').val());
        });
        
        $('#filterSearch').on('keyup change', function() {
            $('.challenges').filterize($('.filtertoggle.filtr-active').data('filter'), $(this).val());
        });
			
		$('#clearFilter').click( function(){
            $('#filterSearch').val('');
			$('.challenges').filterize($('.filtertoggle.filtr-active').data('filter'), $('#filterSearch').val());
		});
	});
    
    $.fn.filterize = function(filter = 'all', search = '') {
        this.addClass('filtering');

        var items = this.children('.filter');
        items.show();

        if ((filter != '' || search != '') && filter != undefined && filter != null) {
            items.filter(function() { 
                return (!$(this).data("category").includes(filter) && filter != 'all') || !$(this).data('search').toLowerCase().includes(search.toLowerCase())
            }).hide();
        }

        this.removeClass('filtering');

      return this;
   }; 
})(jQuery);

</script>
<?php get_footer("members"); ?>

