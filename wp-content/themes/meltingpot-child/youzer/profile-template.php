<?php
/**
 * Template Name: Youzer Profile Template
 */

global $Youzer;

// Get Header Data
$header_effect = yz_options( 'yz_hdr_load_effect' );
$header_data   = $Youzer->widgets->get_loading_effect( $header_effect );
$header_class  = $Youzer->header->get_class( 'user' );

?>

<div id="youzer">

<?php do_action( 'youzer_profile_before_profile' ); ?>

<div id="<?php echo apply_filters( 'yz_profile_template_id', 'yz-bp' ); ?>" class="youzer noLightbox yz-page yz-profile <?php echo yz_get_profile_class(); ?>">

	<?php do_action( 'youzer_profile_before_content' ); ?>

	<div class="yz-content">

		<?php do_action( 'youzer_profile_before_header' ); ?>

    <header id="yz-profile-header" class="<?php echo $header_class; ?>" <?php echo $header_data; ?>>

			<?php do_action( 'youzer_profile_header' ); ?>

      <div id="user-rank" style="opacity: 0;"><?php echo do_shortcode('[gamipress_user_rank type="skilllevel" prev_rank="no" current_rank="yes" next_rank="no" current_user="no" user_id="' . bp_displayed_user_id() . '" columns="1" title="yes" link="no" thumbnail="no" excerpt="no" requirements="no" toggle="no" unlock_button="" earners="" earners_limit="0" layout="left"]'); ?></div>

		</header>

		<div class="yz-profile-content">

			<div class="yz-inner-content">

				<?php do_action( 'youzer_profile_navbar' ); ?>

				<main class="yz-page-main-content">

					<?php

					/**
					 * Fires before the display of member home content.
					 *
					 * @since 1.2.0
					 */
					do_action( 'bp_before_member_home_content' ); ?>

					<?php do_action( 'yz_profile_main_content' ); ?>

					<?php

						/**
						 * Fires after the display of member home content.
						 *
						 * @since 1.2.0
						 */
						do_action( 'bp_after_member_home_content' );

					?>

				</main>

			</div>

		</div>

		<?php do_action( 'youzer_profile_sidebar' ); ?>
	</div>

	<?php do_action( 'youzer_profile_after_content' ); ?>

</div>

<?php do_action( 'youzer_profile_after_profile' ); ?>

</div>
