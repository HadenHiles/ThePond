<?php

get_header(); ?>
<div class="pond-portal pond-library-page">
<?php

$rename_content_library = __('Resources', 'buddyboss-theme-child');
?>


<section class="clbHeader">
	<div class="row">
		<div class="large-8 columns">
        <h1><?php echo esc_html($rename_content_library); ?></h1>
        <p><?php esc_html_e('Explore drills, routines, challenges and resources for your training.', 'buddyboss-theme-child'); ?></p>
		</div>

		<div class="large-4 columns">
			<div class="searchfilter"><label class="screen-reader-text" for="filterSearch"><?php esc_html_e('Search resources', 'buddyboss-theme-child'); ?></label><input type="search" id="filterSearch" name="filtr-search" class="filtr-search" value="" placeholder="<?php esc_attr_e('Search resources', 'buddyboss-theme-child'); ?>" data-search>
			<button type="button" id="clearFilter" class="backBTN"><?php esc_html_e('Clear', 'buddyboss-theme-child'); ?></button></div>
		</div>
	</div>
</section>

<!-- section-->
<section class="dashboardbenefits" style="min-height: 80vh; padding: 0 0 2rem 0;">
	<div class="challenges-wrapper">
		<?php
			$categories = get_terms( 'library_category', array(
				'orderby'    => 'count',
				'hide_empty' => 0
			) );


		if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
		?>
			<ul class="clFilters" id="filteringModeSingle">
				<li><button type="button" class="filtertoggle filtr-active" data-filter="all" aria-pressed="true"><?php esc_html_e('All', 'buddyboss-theme-child'); ?></button></li>
			<?php foreach( $categories as $cat) {
				?>
				<li><button type="button" class="filtertoggle" data-filter="<?php echo esc_attr($cat->term_id);?>" aria-pressed="false"><?php echo esc_html($cat->name);?></button></li>
				<?php
			} ?>

			</ul>

			<?php } ?>

			<div class="bootstrap-styles challenges">
                <?php
                $challengesQuery = new WP_Query( array(
                    'posts_per_page' => -1,
                    'post_status'    => 'publish',
                    'post_type' => 'content-library',
                    'order' => 'desc',
                    'orderby' => 'post_date',
                    'suppress_filters' => true
                ));
                if (!$challengesQuery->have_posts()) {
                    echo '<p class="pond-library-empty">' . esc_html__('No resources are available yet.', 'buddyboss-theme-child') . '</p>';
                }
				while ($challengesQuery->have_posts()) : $challengesQuery->the_post();
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
					foreach ((is_array($terms) ? $terms : array()) as $term) {
						$termsString .= $term->term_id . ',';
					}

					?>
					<a <?php if(get_field('available')) : ?>href="<?php echo esc_url(get_permalink()); ?>"<?php else : ?>aria-disabled="true"<?php endif; ?> class="card shadow challenge filter <?php echo esc_attr($class); ?>" data-category="<?php echo esc_attr($termsString); ?>" data-sort="value" data-search="<?php echo esc_attr(get_the_title() . ' ' . wp_strip_all_tags((string) get_field('description_short'))); ?>">
						<div class="card-img-top" style="background-image: url('<?php  echo $img; ?>'); overflow: hidden; position: relative;">
							<?php
							foreach ((is_array($terms) ? $terms : array()) as $term) {
								?>
								<span class="postLabel" style="float: right; margin: 10px 10px 0 0;"><?=$term->name;?></span>
								<?php
							}

							if(!get_field('available')) {
								?>
								<div class="CourseSoon" style="bottom: 0; left: 0; right: 0;">Coming Soon</div>
								<?php
							}
							?>
						</div>
						<div class="card-body">
							<h4><?php  the_title(); ?></h4>
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
            $('#filteringModeSingle .filtertoggle').removeClass('filtr-active').attr('aria-pressed', 'false');
            $(this).addClass('filtr-active').attr('aria-pressed', 'true');

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

        filter = filter == null ? 'all' : String(filter);
        search = String(search || '').toLowerCase();
        if (filter !== 'all' || search !== '') {
            items.filter(function() {
                var categories = String($(this).attr('data-category') || '').split(',');
                return (filter !== 'all' && categories.indexOf(filter) === -1) ||
                    String($(this).attr('data-search') || '').toLowerCase().indexOf(search) === -1;
            }).hide();
        }

        this.removeClass('filtering');

      return this;
   };
})(jQuery);

</script>
<?php ?>
</div>
<?php wp_reset_postdata(); get_footer(); ?>
