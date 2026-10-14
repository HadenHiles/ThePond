<?php

get_header(); ?>
<div class="pond-portal pond-library-page">
<?php

$is_skills = is_post_type_archive('skills');
$archive_title = $is_skills ? __('Skills', 'buddyboss-theme-child') : __('Resources', 'buddyboss-theme-child');
$search_label = $is_skills ? __('Search skills', 'buddyboss-theme-child') : __('Search resources', 'buddyboss-theme-child');
$taxonomy = $is_skills ? 'skill-type' : 'library_category';
?>


<section class="clbHeader">
	<div class="row">
		<div class="large-8 columns">
        <h1><?php echo esc_html($archive_title); ?></h1>
        <p><?php echo esc_html($is_skills ? __('Explore hockey skills and find your next focus for training.', 'buddyboss-theme-child') : __('Explore drills, routines, challenges and resources for your training.', 'buddyboss-theme-child')); ?></p>
		</div>

		<div class="large-4 columns">
			<div class="searchfilter"><label class="screen-reader-text" for="filterSearch"><?php echo esc_html($search_label); ?></label><input type="search" id="filterSearch" name="filtr-search" class="filtr-search" value="" placeholder="<?php echo esc_attr($search_label); ?>" data-search>
			<button type="button" id="clearFilter" class="backBTN"><?php esc_html_e('Clear', 'buddyboss-theme-child'); ?></button></div>
			<?php if ($is_skills) : ?>
			<div class="pond-skill-archive-sort">
				<label for="pond-skill-archive-sort"><?php esc_html_e('Sort by', 'buddyboss-theme-child'); ?></label>
				<select id="pond-skill-archive-sort">
					<option value="frequency"><?php esc_html_e('Most frequent', 'buddyboss-theme-child'); ?></option>
					<option value="title"><?php esc_html_e('Name (A–Z)', 'buddyboss-theme-child'); ?></option>
					<?php if (is_user_logged_in()) : ?>
					<option value="rating-desc"><?php esc_html_e('Your rating: highest', 'buddyboss-theme-child'); ?></option>
					<option value="rating-asc"><?php esc_html_e('Your rating: lowest', 'buddyboss-theme-child'); ?></option>
					<?php endif; ?>
				</select>
			</div>
			<?php endif; ?>
		</div>
	</div>
</section>

<!-- section-->
<section class="dashboardbenefits" style="min-height: 80vh; padding: 0 0 2rem 0;">
	<div class="challenges-wrapper">
		<?php
			$categories = get_terms( $taxonomy, array(
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

			<div class="bootstrap-styles challenges<?php echo $is_skills ? ' pond-skill-cards' : ''; ?>">
                <?php
                $challengesQuery = new WP_Query( array(
                    'posts_per_page' => -1,
                    'post_status'    => 'publish',
                    'post_type' => $is_skills ? 'skills' : 'content-library',
                    'order' => $is_skills ? 'asc' : 'desc',
                    'orderby' => $is_skills ? 'title' : 'post_date',
                    'suppress_filters' => true
                ));
                if ($is_skills && !empty($challengesQuery->posts)) {
                    $challengesQuery->posts = pond_theme_sort_skills_by_frequency($challengesQuery->posts);
                }
                $isMember = current_user_can('memberpress_authorized');
                if (!$challengesQuery->have_posts()) {
                    echo '<p class="pond-library-empty">' . esc_html($is_skills ? __('No skills are available yet.', 'buddyboss-theme-child') : __('No resources are available yet.', 'buddyboss-theme-child')) . '</p>';
                }
				while ($challengesQuery->have_posts()) : $challengesQuery->the_post();
					$terms = get_the_terms(get_the_ID(), $taxonomy);

					$post_thumbnail_id = get_post_thumbnail_id();

					$img = wp_get_attachment_image_url( $post_thumbnail_id , 'full');
					if (empty($img))
						$img = '/wp-content/themes/meltingpot-child/images/placeholder.png';

					$available = $is_skills || get_field('available');
					$description = $is_skills ? get_the_excerpt() : (string) get_field('description_short');
					$class = '';
					if (!$available) {
						$class='nolink';
					}

					$termsString = '';
					foreach ((is_array($terms) ? $terms : array()) as $term) {
						$termsString .= $term->term_id . ',';
					}

					if ($is_skills) :
						$skillId = get_the_ID();
						include get_stylesheet_directory() . '/template-parts/skills/card.php';
					else :
					?>
					<a <?php if($available) : ?>href="<?php echo esc_url(get_permalink()); ?>"<?php else : ?>aria-disabled="true"<?php endif; ?> class="card shadow challenge filter <?php echo esc_attr($class); ?>" data-category="<?php echo esc_attr($termsString); ?>" data-sort="value" data-search="<?php echo esc_attr(get_the_title() . ' ' . wp_strip_all_tags($description)); ?>">
						<div class="card-img-top" style="background-image: url('<?php echo esc_url($img); ?>'); overflow: hidden; position: relative;">
							<?php
							foreach ((is_array($terms) ? $terms : array()) as $term) {
								?>
								<span class="postLabel" style="float: right; margin: 10px 10px 0 0;"><?php echo esc_html($term->name); ?></span>
								<?php
							}

							if(!$available) {
								?>
								<div class="CourseSoon" style="bottom: 0; left: 0; right: 0;">Coming Soon</div>
								<?php
							}
							?>
						</div>
						<div class="card-body">
							<h4><?php  the_title(); ?></h4>
							<p class="filterDesc card-text"><?php echo wp_kses_post($description); ?></p>
						</div>
					</a>
					<?php endif; ?>
					<?php
				endwhile;
				?>
			</div>
	</div>
	<?php if ($is_skills && $isMember) include get_stylesheet_directory() . '/template-parts/skills/vault-modal.php'; ?>
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

		$('#pond-skill-archive-sort').on('change', function() {
            var mode = $(this).val();
            var $grid = $('.challenges');
            var num = function(el, key) { return parseFloat($(el).attr(key)) || 0; };
            var items = $grid.children('.filter').get().sort(function(a, b) {
                var diff = 0;
                if (mode === 'frequency') diff = num(b, 'data-frequency') - num(a, 'data-frequency');
                else if (mode === 'rating-desc') diff = num(b, 'data-rating') - num(a, 'data-rating');
                else if (mode === 'rating-asc') diff = num(a, 'data-rating') - num(b, 'data-rating');
                return diff || String($(a).attr('data-title')).localeCompare(String($(b).attr('data-title')));
            });
            $grid.append(items);
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
