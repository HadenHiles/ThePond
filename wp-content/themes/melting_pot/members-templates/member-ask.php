<?php
global $smof_data;
$current_user = wp_get_current_user();
$gravityformid = $smof_data['gravity_form_id'];
/*  Template Name: Member Ask*/
get_header('members'); ?>
</header>

<?php get_template_part('template-parts/content-blocks/bannerheader'); ?>

<section class="member-ask">
	<div class="row rowNarrow">
		<?php echo get_field('ask_text'); ?>

		<div class="formbox questionbox">

			<?php
			if ($gravityformid) {
				echo do_shortcode('[gravityform id="' . $gravityformid . '" title="false" description="false" ajax="true" tabindex="5"]');
			}
			?>

		</div>
		<div class="QandASection" id="qanda">
			<div class="large-5 medium-12 columns nopad">
				<h3>Questions & Responses</h3>
			</div>
			<div class="large-7 medium-12 columns nopad">
				<form action="<?php the_permalink(); ?>" method="get">
					<input type="text" name="questionSearch" value="<?php echo $_GET['questionSearch']; ?>" />
					<button type="submit"> Search</button>
					<a href="/ask/?questionSearch=" class="BTN refreshsearch"><i class="far fa-sync-alt"></i></a>
				</form>
			</div>
			<?php
			$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
			$args = array(
				'post_type' => 'question',
				'post_status' => 'publish',
				'paged' => $paged,
				//'posts_per_page' => 2

			);
			if (isset($_GET['questionSearch'])) {
				$args['s'] = $_GET['questionSearch'];
				/*$args['meta_query'] = array(
				array(
				'key' => 'topic',
				'value' => $_GET['questionSearch'],
				'compare' => 'LIKE' 
				)
		);*/
			}

			query_posts($args);

			?>



			<?php if (have_posts()) : ?>
				<div class="FAQ_Wrap">
					<?php while (have_posts()) :  the_post(); ?>
						<div id="faq_container">
							<div class="faq">
								<div class="faq_question"><span class="question"><strong>Topic: </strong> <?php echo get_field('topic'); ?></span></div>
								<div class="faq_answer_container">
									<div class="faq_answer">
										<div class="topic">
											<p><strong>Question:</strong></p>
											<div class="cliquestion">
												<p><?php the_title(); ?></p>
											</div>
										</div>
										<div class="answer">
											<p><strong>Answer: </strong></p>
											<div class="clianswer"><?php echo get_field('answer'); ?></div>
										</div>

									</div>
								</div>
							</div>
						</div>
					<?php endwhile; ?>
				</div>

				<nav class="navigation paging-navigation" role="navigation">



					<div class="nav-links">

						<?php if (get_next_posts_link()) : ?>

							<div class="nav-previous"><?php next_posts_link(__('<span class="meta-nav">&larr;</span> Older posts', 'battle')); ?></div>

						<?php endif; ?>



						<?php if (get_previous_posts_link()) : ?>

							<div class="nav-next"><?php previous_posts_link(__('Newer posts <span class="meta-nav">&rarr;</span>', 'battle')); ?></div>

						<?php endif; ?>



					</div><!-- .nav-links -->

				</nav>
			<?php endif;
			wp_reset_query(); ?>



		</div>
		<script>
			jQuery(document).ready(function($) {
				$('.faq_question').click(function() {
					if ($(this).parent().is('.open')) {
						$(this).closest('.faq').find('.faq_answer_container').animate({
							'height': '0'
						}, 500);
						$(this).closest('.faq').removeClass('open');
						$(this).parent().find('.accordion-button-icon').removeClass('fa-minus').addClass('fa-plus');
					} else {
						var newHeight = $(this).closest('.faq').find('.faq_answer').height() + 'px';
						$(this).closest('.faq').find('.faq_answer_container').animate({
							'height': newHeight
						}, 500);
						$(this).closest('.faq').addClass('open');
						$(this).parent().find('.accordion-button-icon').removeClass('fa-plus').addClass('fa-minus');
					}
				});
			});
		</script>
	</div>
	</div>
</section>
<!-- End Main Section -->
<?php
/* Template Name: Logged in Footer*/

get_footer('members');

?>