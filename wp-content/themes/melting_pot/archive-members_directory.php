<?php get_header("members");
global $smof_data;
?>
</header>

<section class="clbHeader directoryHeader">
	<div class="row">
		<div class="large-8 columns">
			<h1><?php if ($smof_data['directry_custom_title']) : ?><?php echo $smof_data['directry_custom_title']; ?><?php else : ?>Member Directory<?php endif; ?></h1>
			<?php if ($smof_data['directry_custom_sub_title']) : ?><h2><?php echo $smof_data['directry_custom_sub_title']; ?></h2><?php endif; ?>
		</div>

		<div class="large-4 columns">
			<p class="searchText">Search the members</p>
			<div class="searchfilter">
				<input type="text" name="filtr-search" class="filtr-search" value="" placeholder="Enter you keyword here and press enter..." data-search>
				<a id="clearFilter" class="backBTN" href="javascript:;">Clear</a>
			</div>
		</div>
	</div>
</section>

<!-- section-->
<section class="memberContent directorycontent">
	<div class="row">
		<div class="large-12 columns">

			<main>


				<?php
				/*$categories = get_terms( 'library_category', array(
					'orderby'    => 'count',
					'hide_empty' => 0
				) );*/


				if (!empty($categories) && !is_wp_error($categories)) {
				?>
					<ul class="clFilters">
						<li data-filter="all"> All </li>
						<?php foreach ($categories as $cat) { ?>

							<li data-filter="<?php echo $cat->term_id; ?>"> <?php echo $cat->name; ?> </li>

						<?php } ?>

					</ul>

				<?php } ?>

				<div class="filtr-container">
					<?php while (have_posts()) : the_post();
						$terms = get_the_terms(get_the_ID(), 'library_category');
						$post_thumbnail_id = get_post_thumbnail_id();

						$img =  wp_get_attachment_image_url($post_thumbnail_id, 'full');
						if (empty($img))
							$img =  DEFAULT_IMG;
					?>
						<div class="filtr-item" data-category="<?php echo $terms[0]->term_id; ?>" data-sort="value">


							<div class="coursePrevImage" style="background: url('<? echo $img; ?>')no-repeat;background-size:cover;background-position:center;">
								<a href="<? the_permalink(); ?>" class="courseImgOver">
									<span>Say hello to <strong><?php the_title(); ?></strong></span></a>
							</div>
							<div class="filterDesc">
								<h4><?php the_title(); ?></h4>
								<p class="shortdescript"><?php echo get_field('short_bio'); ?></p>
							</div>

							<a class="BTN" href="<?php the_permalink(); ?>">Say Hello</a>

						</div>
					<?php endwhile; ?>
				</div>
			</main>
		</div>
	</div>
</section>
<script>
	jQuery(document).ready(function($) {
		var filterizd = $('.filtr-container').filterizr({

		});
		jQuery('#filteringModeSingle li').click(function($) {
			$('#filteringModeSingle .filtr').removeClass('filtr-active');
			$(this).addClass('filtr-active');
			var filter = $(this).data('fltr');
			//filteringModeSingle.filterizr('filter', filter);
		});

		jQuery('#clearFilter').click(function($) {
			$('#filtrSearch').val('');
			$('.filtr-container').filterizr({

			});
		});


	});
</script>

<?php get_footer("members"); ?>