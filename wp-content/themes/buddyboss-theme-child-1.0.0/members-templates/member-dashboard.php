<?php


/*  Template Name: Member Dashboard*/
get_header(); ?>
<div class="pond-portal">
<?php  ?>


<?php
/*
<section class="memberDashWelc">
<div class="row">
	<div class="large-12 medium-12 columns user-points-wrapper" style="font-family: 'Teko', sans-serif; color: #fff; font-size: 28px !important; font-weight: bold;">
		<?php
		echo do_shortcode('[gamipress_user_rank type="skilllevel" prev_rank="no" current_rank="yes" next_rank="no" current_user="yes" user_id="" columns="1" title="yes" link="no" thumbnail="no" excerpt="no" requirements="no" toggle="no" unlock_button="" earners="" earners_limit="0" layout="left"]');
		echo do_shortcode('[gamipress_points type="pondpoints" thumbnail="no" label="yes" current_user="yes" user_id="" period="" period_start="" period_end="" inline="" columns="1" layout="left"]');
		?>
	</div>
</div>
</section>
*/
?>

<section id="member-dashboard" class="memberbenefits dashboardbenefits">
	<header class="pond-dashboard-heading">
		<h1><?php esc_html_e('Your training dashboard', 'buddyboss-theme-child'); ?></h1>
		<p><?php esc_html_e('Explore courses, continue your training, and find your next challenge.', 'buddyboss-theme-child'); ?></p>
	</header>
	<!-- <div class="row"> -->
	<div>
		<div class="large-12 columns" style="padding: 0;">
			<?php
			echo do_shortcode('[ld_courses_by_categories categories="skating,stickhandling,shooting,passing"]');
			?>
		</div>

		<?php if (get_field('content_after_links')) : ?>
			<div class="afterMainLinks"><?php the_field('content_after_links'); ?></div>
		<?php else : ?>
		<?php endif; ?>
	</div>
</section>

<section class="profile Dashboard">
	<div class="row">

		<div class="sectionHeader">
			<div class="large-12 columns" id="courses">
				<div class="pond-dashboard-social">
					<?php pond_theme_dashboard_community(); ?>
					<?php if (current_user_can('memberpress_authorized')) : ?>
						<section class="pond-dashboard-facebook" id="facebook-secret-phrase" aria-label="<?php esc_attr_e('The Pond Facebook Group', 'buddyboss-theme-child'); ?>">
							<?php
							if (shortcode_exists('facebook_secret_phrase_shortcode')) {
								echo do_shortcode('[facebook_secret_phrase_shortcode]');
							} else {
								echo '<p role="alert">' . esc_html__('The group code generator is unavailable. Please contact support.', 'buddyboss-theme-child') . '</p>';
							}
							?>
						</section>
					<?php endif; ?>
				</div>
				<?= do_shortcode('[ld_profile course_points_user="false" show_quizzes="false"]'); ?>
			</div>
		</div>
	</div>
</section>

<section class="challenges-dashboard Dashboard" id="challenges">
	<?= do_shortcode('[list_library_items title="Latest Challenges" btn="All Challenges" btn-url="/challenges"]') ?>
</section>

<section class="Dashboard" id="move-makers">
	<?= do_shortcode('[list_library_items title="Move Makers" btn="All Move Makers" btn-url="/move-makers" term="move-makers"]') ?>
</section>

<section class="challenges-dashboard Dashboard" id="routines">
	<?= do_shortcode('[list_library_items title="Routines" btn="All Routines" btn-url="/routines" term="routines"]') ?>
</section>

<!-- Skills Vault -->
<section class="skillsVault Dashboard" id="skills-vault">
	<div class="challenges-wrapper">
		<div class="row">
			<div class="large-12 columns">
				<h1>Skills Vault</h1>
				<div class="clearfix"></div>
			</div>
		</div>
		<div class="clearfix"></div>
		<div class="bootstrap-styles challenges pond-skill-cards">
			<?php
			$skillsQuery = new WP_Query(array(
				'post_type' => 'skills',
				'post_status' => 'publish',
				'posts_per_page' => -1
			));
			$skills = $skillsQuery->get_posts();
			$isMember = current_user_can('memberpress_authorized');
			if (empty($skills)) {
				echo '<p class="pond-library-empty">There are no skills in the skills vault yet.</p>';
			}
			foreach (array_slice(pond_theme_sort_skills_by_frequency(is_array($skills) ? $skills : array()), 0, 3) as $skill) {
				$skillId = $skill->ID;
				include get_stylesheet_directory() . '/template-parts/skills/card.php';
			}
			?>
		</div>
		<a href="/skills/" class="BTN all-challenges">All Skills</a>
	</div>
	<?php include get_stylesheet_directory() . '/template-parts/skills/vault-modal.php'; ?>
</section>

<!-- End Main Section -->
<?php ?>
</div>
<?php wp_reset_postdata(); get_footer(); ?>