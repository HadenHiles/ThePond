<?php
/**
 * User-related Functions
 *
 * @package     GamiPress\User_Functions
 * @author      GamiPress <contact@gamipress.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Get a user's gamipress achievements
 *
 * @since   1.0.0
 * @updated 1.6.3 Return an empty array if not user provided or current user is not logged in
 *
 * @param  array $args An array of all our relevant arguments
 *
 * @return array       An array of all the achievement objects that matched our parameters, or empty if none
 */
function gamipress_get_user_achievements( $args = array() ) {

	// If not properly upgrade to required version fallback to compatibility function
	if( ! is_gamipress_upgraded_to( '1.2.8' ) ) {
		return gamipress_get_user_achievements_old( $args );
	}

	// Setup our default args
	$defaults = array(
		'user_id'          => 0,     					// The given user's ID
		'site_id'          => get_current_blog_id(), 	// The given site's ID
		'achievement_id'   => false, 					// A specific achievement's post ID
		'achievement_type' => false, 					// A specific achievement type
		'since'            => 0,     					// A specific timestamp to use in place of $limit_in_days
		'limit'            => -1,    					// Limit of achievements to return
		'groupby'          => false,    				// Group by clause, setting it to 'post_id' or 'achievement_id' will prevent duplicated achievements
	);

	$args = wp_parse_args( $args, $defaults );

	// Use current user's ID if none specified
	if ( ! $args['user_id'] )
		$args['user_id'] = get_current_user_id();

	// Bail if not user provided or current user is not logged in
	if( absint( $args['user_id'] ) === 0 )
	    return array();

	// Setup CT object
	ct_setup_table( 'gamipress_user_earnings' );

	// Setup query args
	$query_args = array(
		'user_id' 			=> $args['user_id'],
		'nopaging' 			=> true,
		'items_per_page' 	=> $args['limit'],
	);

	if( $args['achievement_id'] !== false ) {
		$query_args['post_id'] = $args['achievement_id'];
	}

	if( $args['achievement_type'] !== false ) {
		$query_args['post_type'] = $args['achievement_type'];
	}

	if( $args['groupby'] !== false ) {
		$query_args['groupby'] = $args['groupby'];

		// achievement_id is allowed
		if( $args['groupby'] === 'achievement_id' ) {
			$query_args['groupby'] = 'post_id';
		}
	}

	if( $args['since'] !== 0 ) {
		$query_args['since'] = $args['since'];
	}

	$ct_query = new CT_Query( $query_args );

	$achievements = $ct_query->get_results();

	foreach ( $achievements as $key => $achievement ) {

		// Update object for backward compatibility for usages previously to 1.2.7
		$achievement->ID = $achievement->post_id;
		$achievement->date_earned = strtotime( $achievement->date );

		$achievements[$key] = $achievement;

        // If achievements earned will be displayed, then need to pass some filters
		if( isset( $args['display'] ) && $args['display'] ) {

		    // Unset not existent achievements
		    if( ! gamipress_post_exists( $achievement->post_id ) )
                unset( $achievements[$key] );

		    // Unset not published achievements
            if( gamipress_get_post_field( 'post_status', $achievement->post_id ) !== 'publish' )
                unset( $achievements[$key] );

			// Unset hidden achievements on display context
			if( gamipress_is_achievement_hidden( $achievement->post_id ) )
				unset( $achievements[$key] );
		}

	}

	ct_reset_setup_table();

	return $achievements;

}

/**
 * Updates the user's earned achievements
 *
 * @since  1.0.0
 *
 * @param  array $args 	An array containing all our relevant arguments
 *
 * @return bool 		The updated umeta ID on success, false on failure
 */
function gamipress_update_user_achievements( $args = array() ) {

	// If not properly upgrade to required version fallback to compatibility function
	if( ! is_gamipress_upgraded_to( '1.2.8' ) ) {
		return gamipress_update_user_achievements_old( $args );
	}

	// Setup our default args
	$defaults = array(
		'user_id'          => 0,     // The given user's ID
		'site_id'          => get_current_blog_id(), // The given site's ID
		'new_achievements' => false, // An array of NEW achievements earned by the user
	);

	$args = wp_parse_args( $args, $defaults );

	// Use current user's ID if none specified
	if ( ! $args['user_id'] )
		$args['user_id'] = get_current_user_id();

	// Lets to append the new achievements array
	if ( is_array( $args['new_achievements'] ) && ! empty( $args['new_achievements'] ) ) {

		foreach( $args['new_achievements'] as $new_achievement ) {

			$user_earning_data = array(
				'title' => gamipress_get_post_field( 'post_title', $new_achievement->ID ),
				'post_id' => $new_achievement->ID,
				'post_type' => $new_achievement->post_type,
				'points' => absint( $new_achievement->points ),
				'points_type' => $new_achievement->points_type,
				'date' => date( 'Y-m-d H:i:s', $new_achievement->date_earned )
			);

			gamipress_insert_user_earning( absint( $args['user_id'] ), $user_earning_data );

		}

	}

	return true;

}

/**
 * Display achievements for a user on their profile screen
 *
 * @since  1.0.0
 * @param  object $user The current user's $user object
 * @return void
 */
function gamipress_user_profile_data( $user = null ) {

	?>

	<hr>

	<?php // Verify user meets minimum role to manage earned achievements
	if ( current_user_can( gamipress_get_manager_capability() ) ) : ?>

		<h2><?php echo gamipress_dashicon( 'gamipress' ); ?> <?php _e( 'GamiPress', 'gamipress' ); ?></h2>

	<?php endif; ?>

	<?php // Output markup to user rank
	gamipress_profile_user_rank( $user );

	// Output markup to list user points
	gamipress_profile_user_points( $user );

	// Output markup to list user achievements
	gamipress_profile_user_achievements( $user );

	// Output markup for awarding achievement for user
	gamipress_profile_award_achievement( $user );

    // Output markup for awarding requirement for user
    gamipress_profile_award_requirement( $user );

}
add_action( 'show_user_profile', 'gamipress_user_profile_data' );
add_action( 'edit_user_profile', 'gamipress_user_profile_data' );

/**
 * Update user rank ajax handler
 *
 * @since 1.5.9
 */
function gamipress_ajax_profile_update_user_rank() {

    $rank_id = $_POST['rank_id'];
    $user_id = absint( $_POST['user_id'] );

    // Check if user has permissions
    if ( ! current_user_can( 'edit_user', $user_id ) )
        wp_send_json_error( __( 'You can perform this action.', 'gamipress' ) );

    // Check if valid user ID
    if( $user_id === 0 )
        wp_send_json_error( __( 'Invalid user ID.', 'gamipress' ) );

    $rank = gamipress_get_post( $rank_id );

    // Check if is a valid rank
    if ( ! $rank )
        wp_send_json_error( __( 'Invalid post ID.', 'gamipress' ) );

    if ( ! gamipress_is_rank( $rank ) )
        wp_send_json_error( __( 'Invalid rank ID.', 'gamipress' ) );

    // Update the user rank
    gamipress_update_user_rank( $user_id, absint( $rank_id ), get_current_user_id() );

    wp_send_json_success( array(
        'message' => __( 'Rank updated successfully.', 'gamipress' ),
        'rank' => array(
            'ID' => $rank->ID,
            'post_title' => $rank->post_title,
            'thumbnail' => gamipress_get_rank_post_thumbnail( $rank->ID, array( 32, 32 ) ),
        )
    ) );

}
add_action( 'wp_ajax_gamipress_profile_update_user_rank', 'gamipress_ajax_profile_update_user_rank' );

/**
 * Update user points ajax handler
 *
 * @since   1.5.9
 * @updated 1.6.0 Now also return the current user ranks in order to see any rank change through the points earned
 */
function gamipress_ajax_profile_update_user_points() {

    $points = $_POST['points'];
    $points_type = $_POST['points_type'];
    $user_id = absint( $_POST['user_id'] );

    // Check if user has permissions
    if ( ! current_user_can( 'edit_user', $user_id ) )
        wp_send_json_error( __( 'You can perform this action.', 'gamipress' ) );

    // Check if valid user ID
    if( $user_id === 0 )
        wp_send_json_error( __( 'Invalid user ID.', 'gamipress' ) );

    // Check if valid amount
    if( ! is_numeric( $points ) )
        wp_send_json_error( __( 'Invalid points amount.', 'gamipress' ) );

    // Check if is valid points type
    if( $points_type !== '' && ! in_array( $points_type, gamipress_get_points_types_slugs() ) )
        wp_send_json_error( __( 'Invalid points type.', 'gamipress' ) );

    // Update the user points
    gamipress_update_user_points( $user_id, absint( $points ), get_current_user_id(), null, $points_type );

    // After update the user points balance, is possible that user unlocks a rank
    // For that, we need to return the current user ranks again and check for differences
    $ranks = array();

    foreach( gamipress_get_rank_types_slugs() as $rank_type ) {

        // Get the rank object to build the same response as gamipress_ajax_profile_update_user_rank() function
        $rank = gamipress_get_user_rank( $user_id, $rank_type );

        $ranks[] = array(
            'ID' => $rank->ID,
            'post_title' => $rank->post_title,
            'post_type' => $rank->post_type, // Included to meet the rank type
            'thumbnail' => gamipress_get_rank_post_thumbnail( $rank->ID, array( 32, 32 ) ),
        );
    }

    wp_send_json_success( array(
        'message' => __( 'Points updated successfully.', 'gamipress' ),
        'points' => gamipress_format_amount( $points, $points_type ),
        'ranks' => $ranks
    ) );

}
add_action( 'wp_ajax_gamipress_profile_update_user_points', 'gamipress_ajax_profile_update_user_points' );

/**
 * Generate markup to show user rank
 *
 * @since  1.0.0
 *
 * @param  object $user         The current user's $user object
 *
 * @return string               concatenated markup
 */
function gamipress_profile_user_rank( $user = null ) {

	$rank_types = gamipress_get_rank_types();

	$can_manage = current_user_can( gamipress_get_manager_capability() );

	// Return if not rank types and user is not a manager
	if( empty( $rank_types ) && ! $can_manage ) {
		return;
	}
	?>

	<table class="form-table">
        <tr>
            <th>
                <label><?php echo $can_manage ? __( 'Ranks', 'gamipress' ) : __( 'Your Ranks', 'gamipress' ); ?></label>
            </th>
            <td>

                <?php if( empty( $rank_types ) && $can_manage ) : ?>

                    <?php // No rank types configured yet ?>
                    <span class="description">
                        <?php echo sprintf( __( 'No rank types configured, visit %s to configure some rank types.', 'gamipress' ), '<a href="' . admin_url( 'edit.php?post_type=rank-type' ) . '">' . __( 'this page', 'gamipress' ) . '</a>' ); ?>
                    </span>

                <?php else : ?>

                    <div class="profile-ranks gamipress-profile-cards">

                        <?php // Show the information of each user rank ?>
                        <?php foreach( $rank_types as $rank_type => $data ) : ?>

                            <div class="profile-rank-wrapper gamipress-profile-card-wrapper">
                                <div class="profile-rank profile-rank-<?php echo $rank_type; ?> gamipress-profile-card">

                                    <span class="profile-rank-type-name"><?php echo $data['singular_name']; ?></span>

                                    <?php // Get and display the current user rank
                                    $user_rank = gamipress_get_user_rank( $user->ID, $rank_type );

                                    if( $user_rank ) : ?>

                                        <div class="profile-rank-thumbnail"><?php echo gamipress_get_rank_post_thumbnail( $user_rank->ID, array( 32, 32 ) ); ?></div>

                                        <span class="profile-rank-title"><?php echo $user_rank->post_title; ?></span>

                                    <?php endif; ?>


                                    <?php if( $can_manage ) :
                                        // Show an editable form of ranks

                                        // Get all published ranks of this type
                                        $ranks = gamipress_get_ranks( array(
                                            'post_type' => $rank_type,
                                            'posts_per_page' => -1
                                        ) ); ?>

                                        <?php if( empty( $ranks ) ) : ?>

                                            <?php // No ranks of this type configured yet ?>
                                            <span class="description">
                                                <?php echo sprintf( __( 'No %1$s configured, visit %2$s to configure some %1$s.', 'gamipress' ),
                                                    strtolower( $data['plural_name'] ),
                                                    '<a href="' . admin_url( 'edit.php?post_type=' . $rank_type ) . '">' . __( 'this page', 'gamipress' ) . '</a>'
                                                ); ?>
                                            </span>

                                        <?php else : ?>

                                            <a href="#" class="profile-rank-toggle"><?php echo __( 'Edit', 'gamipress' ); ?></a>

                                            <div class="profile-rank-form-wrapper">

                                                <select name="user_<?php echo $rank_type; ?>_rank" id="user_<?php echo $rank_type; ?>_rank" style="min-width: 15em;">
                                                    <?php foreach( $ranks as $rank ) : ?>
                                                        <option value="<?php echo $rank->ID; ?>" <?php selected( $user_rank->ID, $rank->ID ); ?>><?php echo $rank->post_title; ?></option>
                                                    <?php endforeach; ?>
                                                </select>

                                                <span class="description"><?php echo sprintf( __( '%s listed are ordered by priority.', 'gamipress' ), $data['plural_name'] ); ?></span>

                                                <div class="profile-rank-form-buttons">
                                                    <a href="#" class="button button-primary profile-rank-save"><?php echo __( 'Save', 'gamipress' ); ?></a>
                                                    <a href="#" class="button profile-rank-cancel"><?php echo __( 'Cancel', 'gamipress' ); ?></a>
                                                    <span class="spinner"></span>
                                                </div>

                                            </div>

                                        <?php endif; ?>

                                    <?php endif; ?>

                                </div>
                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>

            </td>
        </tr>
	</table>

    <hr>
	<?php
}

/**
 * Generate markup to list user earned points
 *
 * @since  1.0.0
 *
 * @param  object $user         The current user's $user object
 *
 * @return string               concatenated markup
 */
function gamipress_profile_user_points( $user = null ) {

    $points_types = gamipress_get_points_types();

	$can_manage = current_user_can( gamipress_get_manager_capability() );

	// Return if not points types and user is not a manager
	if( empty( $points_types ) && ! $can_manage ) {
		return;
	}
	?>

    <table class="form-table">
        <tr>
            <th>
                <label><?php echo $can_manage ? __( 'Points Balances', 'gamipress' ) : __( 'Your Balances', 'gamipress' ); ?></label>
            </th>
            <td>

                <?php if( empty( $points_types ) && $can_manage ) : ?>

                    <span class="description">
                        <?php echo sprintf( __( 'No points types configured, visit %s to configure some points types.', 'gamipress' ), '<a href="' . admin_url( 'edit.php?post_type=points-type' ) . '">' . __( 'this page', 'gamipress' ) . '</a>' ); ?>
                    </span>

                <?php else : ?>

                    <div class="profile-points gamipress-profile-cards">

                        <?php // Filter available to re-enable the default points
                        if( apply_filters( 'gamipress_user_points_backward_compatibility', false ) ) :
                            $user_points = gamipress_get_user_points( $user->ID ); ?>

                            <div class="profile-points-wrapper gamipress-profile-card-wrapper">

                                <div class="profile-points profile-points-default gamipress-profile-card">

                                    <span class="profile-points-type-name"><?php _e( 'Default Points', 'gamipress' ); ?></span>

                                    <div class="profile-points-thumbnail"></div>

                                    <span class="profile-points-amount"><?php echo $user_points; ?></span>

                                    <?php if( $can_manage ) :
                                        // Show an editable form of points ?>

                                        <a href="#" class="profile-points-toggle"><?php echo __( 'Edit', 'gamipress' ); ?></a>

                                        <div class="profile-points-form-wrapper">

                                            <input type="number" name="user_points" id="user_points" value="<?php echo $user_points; ?>" class="regular-text" data-points-type="" />

                                            <span class="description"><?php echo __( 'Enter a new total will automatically log the change and difference between totals.', 'gamipress' ); ?></span>

                                            <div class="profile-points-form-buttons">
                                                <a href="#" class="button button-primary profile-points-save"><?php echo __( 'Save', 'gamipress' ); ?></a>
                                                <a href="#" class="button profile-points-cancel"><?php echo __( 'Cancel', 'gamipress' ); ?></a>
                                                <span class="spinner"></span>
                                            </div>

                                        </div>

                                    <?php endif; ?>

                                </div>

                            </div>

                        <?php endif; ?>

                        <?php foreach( $points_types as $points_type => $data ) :
                            $user_points = gamipress_get_user_points( $user->ID, $points_type ); ?>

                            <div class="profile-points-wrapper gamipress-profile-card-wrapper">

                                <div class="profile-points profile-points-<?php echo $points_type; ?> gamipress-profile-card">

                                    <span class="profile-points-type-name"><?php echo $data['plural_name']; ?></span>

                                    <div class="profile-points-thumbnail"><?php echo gamipress_get_points_type_thumbnail( $points_type, array( 32, 32 ) ); ?></div>

                                    <span class="profile-points-amount"><?php echo gamipress_format_amount( $user_points, $points_type ); ?></span>

                                    <?php if( $can_manage ) :
                                        // Show an editable form of points ?>

                                        <a href="#" class="profile-points-toggle"><?php echo __( 'Edit', 'gamipress' ); ?></a>

                                        <div class="profile-points-form-wrapper">

                                            <input type="number" name="user_<?php echo $points_type; ?>_points" id="user_<?php echo $points_type; ?>_points" value="<?php echo $user_points; ?>" class="regular-text" data-points-type="<?php echo $points_type; ?>" />

                                            <span class="description"><?php echo __( 'Enter a new total will automatically log the change and difference between totals.', 'gamipress' ); ?></span>

                                            <div class="profile-points-form-buttons">
                                                <a href="#" class="button button-primary profile-points-save"><?php echo __( 'Save', 'gamipress' ); ?></a>
                                                <a href="#" class="button profile-points-cancel"><?php echo __( 'Cancel', 'gamipress' ); ?></a>
                                                <span class="spinner"></span>
                                            </div>

                                        </div>

                                    <?php endif; ?>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>
            </td>
        </tr>
    </table>

    <hr>
	<?php
}

/**
 * Generate markup to list user earned achievements
 *
 * @since  1.0.0
 *
 * @param  object $user         The current user's $user object
 *
 * @return string               concatenated markup
 */
function gamipress_profile_user_achievements( $user = null ) {

	$can_manage = current_user_can( gamipress_get_manager_capability() );
	?>

    <h2><?php echo $can_manage ? __( 'User Earnings', 'gamipress' ) : __( 'Your Achievements', 'gamipress' ); ?></h2>

	<?php ct_render_ajax_list_table( 'gamipress_user_earnings',
		array(
			'user_id' => absint( $user->ID )
		),
		array(
			'views' => false,
			'search_box' => false
		)
	); ?>

    <hr>

    <?php
}

/**
 * Generate markup for awarding an achievement to a user
 *
 * @since  1.0.0
 *
 * @param  object $user         The current user's $user object
 *
 * @return string               concatenated markup
 */
function gamipress_profile_award_achievement( $user = null ) {

	$can_manage = current_user_can( gamipress_get_manager_capability() );

	// Return if user is not a manager
	if( ! $can_manage ) {
		return;
	}

    // Grab our types
    $achievement_types = gamipress_get_achievement_types();

	$achievements = gamipress_get_user_achievements( array(
        'user_id' => absint( $user->ID ),
        'achievement_type' => gamipress_get_achievement_types_slugs()
    ) );

    $achievement_ids = array_map( function( $achievement ) {
        return $achievement->ID;
    }, $achievements );

	// On network wide active installs, we need to switch to main blog mostly for posts permalinks and thumbnails
    $blog_id = gamipress_switch_to_main_site_if_network_wide_active();
	?>

	<h2><?php _e( 'Award Achievement', 'gamipress' ); ?></h2>

	<table class="form-table">

		<tr>
			<th><label for="gamipress-award-achievement-type-select"><?php _e( 'Select an achievement type to award:', 'gamipress' ); ?></label></th>
			<td>
				<select id="gamipress-award-achievement-type-select">
				<option><?php _e( 'Choose an achievement type', 'gamipress' ); ?></option>
				<?php foreach ( $achievement_types as $slug => $data ) :
					echo '<option value="'. $slug .'">' . ucwords( $data['singular_name'] ) .'</option>';
				endforeach; ?>
				</select>
			</td>
		</tr>

	</table>

	<div id="gamipress-awards-options">
		<?php foreach ( $achievement_types as $slug => $data ) : ?>
			<table id="<?php echo esc_attr( $slug ); ?>" class="wp-list-table widefat fixed striped gamipress-table" style="display: none;">

				<thead>
					<tr>
						<th><?php echo ucwords( $data['singular_name'] ); ?></th>
						<th><?php _e( 'Actions', 'gamipress' ); ?></th>
					</tr>
				</thead>

				<tbody>
				<?php
				// Load achievement type entries
				$the_query = new WP_Query( array(
					'post_type'      	=> $slug,
					'posts_per_page' 	=> -1,
					'post_status'    	=> 'publish',
					'suppress_filters' 	=> false
				) );

				if ( $the_query->have_posts() ) : ?>

					<?php while ( $the_query->have_posts() ) : $the_query->the_post();

						// Setup our award URL
						$award_url = add_query_arg( array(
							'action'         => 'award',
							'achievement_id' => absint( get_the_ID() ),
							'user_id'        => absint( $user->ID )
						) );
						?>
						<tr>
							<td>
                                <?php // Thumbnail ?>
                                <?php echo gamipress_get_achievement_post_thumbnail( get_the_ID(), array( 32, 32 ) ); ?>

                                <?php // Title ?>
                                <strong><?php echo '<a href="' . get_edit_post_link( get_the_ID() ) . '">' . gamipress_get_post_field( 'post_title', get_the_ID() ) . '</a>'; ?></strong>
							</td>
							<td>
								<a class="gamipress-award-achievement" href="<?php echo esc_url( wp_nonce_url( $award_url, 'gamipress_award_achievement' ) ); ?>"><?php printf( __( 'Award %s', 'gamipress' ), ucwords( $data['singular_name'] ) ); ?></a>
								<?php if ( in_array( get_the_ID(), (array) $achievement_ids ) ) :
									// Setup our revoke URL
									$revoke_url = add_query_arg( array(
										'action'         => 'revoke',
										'user_id'        => absint( $user->ID ),
										'achievement_id' => absint( get_the_ID() ),
									) );
									?>
									| <span class="delete"><a class="error gamipress-revoke-achievement" href="<?php echo esc_url( wp_nonce_url( $revoke_url, 'gamipress_revoke_achievement' ) ); ?>"><?php _e( 'Revoke Award', 'gamipress' ); ?></a></span>
								<?php endif; ?>

							</td>
						</tr>
					<?php endwhile; ?>

				<?php else : ?>
					<tr>
						<td colspan="3"><?php printf( __( 'No %s found.', 'gamipress' ), $data['plural_name'] ); ?></td>
					</tr>
				<?php endif; wp_reset_postdata(); ?>

				</tbody>

			</table><!-- #<?php echo esc_attr( $slug ); ?> -->

		<?php endforeach; ?>

	</div><!-- #gamipress-awards-options -->

    <hr>

	<?php

	// If switched to blog, return back to que current blog
    if( $blog_id !== get_current_blog_id() && is_multisite() ) {
        restore_current_blog();
    }
}

/**
 * Generate markup for awarding an achievement to a user
 *
 * @since  1.6.8
 *
 * @param  object $user         The current user's $user object
 *
 * @return string               concatenated markup
 */
function gamipress_profile_award_requirement( $user = null ) {

    $can_manage = current_user_can( gamipress_get_manager_capability() );

    // Return if user is not a manager
    if( ! $can_manage ) {
        return;
    }

    // Grab our types
    $achievement_types = gamipress_get_achievement_types();
    $rank_types = gamipress_get_rank_types();
    $requirement_types = gamipress_get_requirement_types();

    $achievements = gamipress_get_user_achievements( array(
        'user_id' => absint( $user->ID ),
        'achievement_type' => gamipress_get_requirement_types_slugs()
    ) );

    $achievement_ids = array_map( function( $achievement ) {
        return $achievement->ID;
    }, $achievements );

    // On network wide active installs, we need to switch to main blog mostly for posts permalinks and thumbnails
    $blog_id = gamipress_switch_to_main_site_if_network_wide_active();
    ?>

    <h2><?php _e( 'Award Requirement', 'gamipress' ); ?></h2>

    <table class="form-table">

        <tr>
            <th><label for="gamipress-award-requirement-type-select"><?php _e( 'Select a requirement type to award:', 'gamipress' ); ?></label></th>
            <td>
                <select id="gamipress-award-requirement-type-select">
                    <option><?php _e( 'Choose a requirement type', 'gamipress' ); ?></option>
                    <?php foreach ( $requirement_types as $slug => $data ) :
                        echo '<option value="'. $slug .'">' . ucwords( $data['singular_name'] ) .'</option>';
                    endforeach; ?>
                </select>
            </td>
        </tr>

    </table>

    <div id="gamipress-awards-options">
        <?php foreach ( $requirement_types as $slug => $data ) : ?>
            <table id="<?php echo esc_attr( $slug ); ?>" class="wp-list-table widefat fixed striped gamipress-table" style="display: none;">

                <thead>
                <tr>
                    <th><?php echo ucwords( $data['singular_name'] ); ?></th>
                    <th><?php _e( 'Actions', 'gamipress' ); ?></th>
                </tr>
                </thead>

                <tbody>
                <?php
                // Load achievement type entries
                $the_query = new WP_Query( array(
                    'post_type'      	=> $slug,
                    'posts_per_page' 	=> -1,
                    'post_status'    	=> 'publish',
                    'suppress_filters' 	=> false
                ) );

                if ( $the_query->have_posts() ) : ?>

                    <?php while ( $the_query->have_posts() ) : $the_query->the_post();

                        // If not parent object, skip
                        if( $slug === 'step' && ! $achievement = gamipress_get_step_achievement( get_the_ID() ) ) {
                            continue;
                        } else if( $slug === 'points-award' && ! $points_type = gamipress_get_points_award_points_type( get_the_ID() ) ) {
                            continue;
                        } else if( $slug === 'points-deduct' && ! $points_type = gamipress_get_points_deduct_points_type( get_the_ID() ) ) {
                            continue;
                        } else if( $slug === 'rank-requirement' && ! $rank = gamipress_get_rank_requirement_rank( get_the_ID() ) ) {
                            continue;
                        }

                        // Setup our award URL
                        $award_url = add_query_arg( array(
                            'action'         => 'award',
                            'achievement_id' => absint( get_the_ID() ),
                            'user_id'        => absint( $user->ID )
                        ) );
                        ?>
                        <tr>
                            <td>
                                <?php // Output parent achievement
                                if( $slug === 'step' && $achievement ) : ?>

                                    <?php // Achievement thumbnail ?>
                                    <?php echo gamipress_get_achievement_post_thumbnail( $achievement->ID, array( 32, 32 ) ); ?>

                                    <?php // Step title ?>
                                    <strong><?php echo gamipress_get_post_field( 'post_title', get_the_ID() ); ?></strong>

                                    <?php // Step relationship details ?>
                                    <?php echo ( isset( $achievement_types[$achievement->post_type] ) ? '<br> ' . $achievement_types[$achievement->post_type]['singular_name'] . ': ' : '' ); ?>
                                    <?php echo '<a href="' . get_edit_post_link( $achievement->ID ) . '">' . gamipress_get_post_field( 'post_title', $achievement->ID ) . '</a>'; ?>

                                <?php elseif( in_array( $slug, array( 'points-award', 'points-deduct' ) ) && $points_type ) : ?>

                                    <?php // Points type thumbnail ?>
                                    <?php echo gamipress_get_points_type_thumbnail( $points_type->ID, array( 32, 32 ) ); ?>

                                    <?php // Points award/deduct title ?>
                                    <strong><?php echo gamipress_get_post_field( 'post_title', get_the_ID() ); ?></strong>
                                    <br>
                                    <?php echo '<a href="' . get_edit_post_link( $points_type->ID ) . '">' . gamipress_get_post_field( 'post_title', $points_type->ID ) . '</a>'; ?>

                                <?php elseif( $slug === 'rank-requirement' && $rank ) : ?>

                                    <?php // Rank thumbnail ?>
                                    <?php echo gamipress_get_rank_post_thumbnail( $rank->ID, array( 32, 32 ) ); ?>

                                    <?php // Rank requirement title ?>
                                    <strong><?php echo gamipress_get_post_field( 'post_title', get_the_ID() ); ?></strong>

                                    <?php // Rank requirement relationship details ?>
                                    <?php echo ( isset( $rank_types[$rank->post_type] ) ? '<br> ' . $rank_types[$rank->post_type]['singular_name'] . ': ' : '' ); ?>
                                    <?php echo '<a href="' . get_edit_post_link( $rank->ID ) . '">' . gamipress_get_post_field( 'post_title', $rank->ID ) . '</a>'; ?>

                                <?php endif; ?>
                            </td>
                            <td>
                                <a class="gamipress-award-achievement" href="<?php echo esc_url( wp_nonce_url( $award_url, 'gamipress_award_achievement' ) ); ?>"><?php printf( __( 'Award %s', 'gamipress' ), ucwords( $data['singular_name'] ) ); ?></a>
                                <?php if ( in_array( get_the_ID(), (array) $achievement_ids ) ) :
                                    // Setup our revoke URL
                                    $revoke_url = add_query_arg( array(
                                        'action'         => 'revoke',
                                        'user_id'        => absint( $user->ID ),
                                        'achievement_id' => absint( get_the_ID() ),
                                    ) );
                                    ?>
                                    | <span class="delete"><a class="error gamipress-revoke-achievement" href="<?php echo esc_url( wp_nonce_url( $revoke_url, 'gamipress_revoke_achievement' ) ); ?>"><?php _e( 'Revoke Award', 'gamipress' ); ?></a></span>
                                <?php endif; ?>

                            </td>
                        </tr>
                    <?php endwhile; ?>

                <?php else : ?>
                    <tr>
                        <td colspan="3"><?php printf( __( 'No %s found.', 'gamipress' ), $data['plural_name'] ); ?></td>
                    </tr>
                <?php endif; wp_reset_postdata(); ?>

                </tbody>

            </table><!-- #<?php echo esc_attr( $slug ); ?> -->

        <?php endforeach; ?>

    </div><!-- #gamipress-awards-options -->

    <hr>

    <?php

    // If switched to blog, return back to que current blog
    if( $blog_id !== get_current_blog_id() && is_multisite() ) {
        restore_current_blog();
    }
}

/**
 * Process the adding/revoking of achievements on the user profile page
 *
 * @since  1.0.0
 */
function gamipress_process_user_data() {

	// verify user meets minimum role to view earned achievements
	if ( current_user_can( gamipress_get_manager_capability() ) ) {

		// Process awarding achievement to user
		if ( isset( $_GET['action'] ) && $_GET['action'] === 'award' &&  isset( $_GET['user_id'] ) && isset( $_GET['achievement_id'] ) ) {

			// Verify our nonce
			check_admin_referer( 'gamipress_award_achievement' );

			// Award the achievement
			gamipress_award_achievement_to_user( absint( $_GET['achievement_id'] ), absint( $_GET['user_id'] ), get_current_user_id() );

			// Redirect back to the user editor
			wp_redirect( add_query_arg( 'user_id', absint( $_GET['user_id'] ), admin_url( 'user-edit.php' ) ) );
			exit();
		}

		// Process revoking achievement from a user
		if ( isset( $_GET['action'] ) && $_GET['action'] === 'revoke' && isset( $_GET['user_id'] ) && isset( $_GET['achievement_id'] ) ) {

			// Verify our nonce
			check_admin_referer( 'gamipress_revoke_achievement' );

			$earning_id = isset( $_GET['user_earning_id'] ) ? absint( $_GET['user_earning_id'] ) : 0 ;

			// Revoke the achievement
			gamipress_revoke_achievement_to_user( absint( $_GET['achievement_id'] ), absint( $_GET['user_id'] ), $earning_id );

			// If revoking from user earnings screen return directly without redirect
			if( isset( $_GET['page'] ) && $_GET['page'] === 'gamipress_user_earnings' ) {
                exit();
            }

			// Redirect back to the user editor
			wp_redirect( add_query_arg( 'user_id', absint( $_GET['user_id'] ), admin_url( 'user-edit.php' ) ) );
			exit();

		}

	}

}
add_action( 'init', 'gamipress_process_user_data' );

/**
 * Returns array of achievement types a user has earned across a multisite network
 *
 * @since  1.0.0
 * @param  integer $user_id  The user's ID
 * @return array             An array of post types
 */
function gamipress_get_network_achievement_types_for_user( $user_id ) {

    $blog_id = get_current_blog_id();

	// Assume we have no achievement types
	$all_achievement_types = array();

	// Loop through all active sites
	$sites = gamipress_get_network_site_ids();

	foreach( $sites as $site_blog_id ) {

		// If we're polling a different blog, switch to it
		if ( $blog_id != $site_blog_id ) {
			switch_to_blog( $site_blog_id );
		}

		// Merge earned achievements to our achievement type array
		$achievement_types = gamipress_get_user_earned_achievement_types( $user_id );

		if ( is_array( $achievement_types ) ) {
			$all_achievement_types = array_merge( $achievement_types, $all_achievement_types );
		}

        // If switched to blog, return back to que current blog
        if ( $blog_id != $site_blog_id && is_multisite() ) {
            restore_current_blog();
        }
	}

    // Restore the original blog so the sky doesn't fall
	if ( $blog_id != get_current_blog_id() && is_multisite() ) {
        restore_current_blog();
	}

	// Pare down achievement type list so we return no duplicates
	$achievement_types = array_unique( $all_achievement_types );

	// Return all found achievements
	return $achievement_types;

}
