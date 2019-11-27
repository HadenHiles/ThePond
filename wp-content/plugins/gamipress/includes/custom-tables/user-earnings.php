<?php
/**
 * User Earnings
 *
 * @package     GamiPress\Custom_Tables\User_Earnings
 * @author      GamiPress <contact@gamipress.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.2.8
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Parse query args for user earnings to be applied on WHERE clause
 *
 * @since   1.2.8
 * @updated 1.6.5 Added support to before and after query vars
 *
 * @param string    $where
 * @param CT_Query  $ct_query
 *
 * @return string
 */
function gamipress_user_earnings_query_where( $where, $ct_query ) {

    global $ct_table;

    if( $ct_table->name !== 'gamipress_user_earnings' ) {
        return $where;
    }

    $table_name = $ct_table->db->table_name;

    // Shorthand
    $qv = $ct_query->query_vars;

    // User ID
    if( isset( $qv['user_id'] ) && absint( $qv['user_id'] ) !== 0 ) {

        $user_id = $qv['user_id'];

        if( is_array( $user_id ) ) {
            $user_id = implode( ", ", $user_id );

            $where .= " AND {$table_name}.user_id IN ( {$user_id} )";
        } else {
            $where .= " AND {$table_name}.user_id = {$user_id}";
        }
    }

    // Post ID
    if( isset( $qv['post_id'] ) && absint( $qv['post_id'] ) !== 0 ) {

        $post_id = $qv['post_id'];

        if( is_array( $post_id ) ) {
            $post_id = implode( ", ", $post_id );

            $where .= " AND {$table_name}.post_id IN ( {$post_id} )";
        } else {
            $where .= " AND {$table_name}.post_id = {$post_id}";
        }
    }

    // Post Type
    $post_type_where = '';

    if( isset( $qv['post_type'] ) && ! empty( $qv['post_type'] ) ) {

        $post_type = $qv['post_type'];

        if( is_array( $post_type ) ) {
            $post_type = "'" . implode( "', '", $post_type ) . "'";

            $post_type_where = "{$table_name}.post_type IN ( {$post_type} )";
        } else {
            $post_type_where = "{$table_name}.post_type = '{$post_type}'";
        }

    }

    // Points Type
    $points_type_where = '';

    if( isset( $qv['points_type'] ) && ! empty( $qv['points_type'] ) ) {

        $points_type = $qv['points_type'];

        if( is_array( $points_type ) ) {
            $points_type = "'" . implode( "', '", $points_type ) . "'";

            $points_type_where = "{$table_name}.points_type IN ( {$points_type} )";
        } else {
            $points_type_where = "{$table_name}.points_type = '{$points_type}'";
        }

    }


    if( ! empty( $post_type_where ) && ! empty( $points_type_where ) ) {

        if( isset( $qv['force_types'] ) && $qv['force_types'] === true ) {
            $where .= " AND ( {$post_type_where} AND {$points_type_where} )";
        } else {
            // If is querying by post and points type, then need to set this conditional as OR
            $where .= " AND ( {$post_type_where} OR {$points_type_where} )";
        }

    } else if( ! empty( $post_type_where ) ) {

        // Where if just looking for post types and not for points types
        $where .= " AND {$post_type_where}";

    } else if( ! empty( $points_type_where ) ) {

        // Where if just looking for points types and not for post types
        $where .= " AND {$points_type_where}";

    }

    // Include
    if( isset( $qv['user_earning__in'] ) && ! empty( $qv['user_earning__in'] ) ) {

        if( is_array( $qv['user_earning__in'] ) ) {
            $include = implode( ", ", $qv['user_earning__in'] );
        } else {
            $include = $qv['user_earning__in'];
        }

        if( ! empty( $include ) ) {
            $where .= " AND {$table_name}.user_earning_id IN ( {$include} )";
        }
    }

    // Exclude
    if( isset( $qv['user_earning__not_in'] ) && ! empty( $qv['user_earning__not_in'] ) ) {

        if( is_array( $qv['user_earning__not_in'] ) ) {
            $exclude = implode( ", ", $qv['user_earning__not_in'] );
        } else {
            $exclude = $qv['user_earning__not_in'];
        }

        if( ! empty( $exclude ) ) {
            $where .= " AND {$table_name}.user_earning_id NOT IN ( {$exclude} )";
        }
    }

    // Since
    if( isset( $qv['since'] ) ) {

        $since = $qv['since'];

        // Turn a string date into time
        if( gettype( $qv['since'] ) === 'string' ) {
            $since = strtotime( $qv['since'] );
        }

        if( $since > 0 ) {
            $since = date( 'Y-m-d H:i:s', $since );
            $where .= " AND {$table_name}.date > '{$since}'";
        }
    }

    // Before
    if( isset( $qv['before'] ) ) {

        $before = $qv['before'];

        // Turn a string date into time
        if( gettype( $qv['before'] ) === 'string' ) {
            $before = strtotime( $qv['before'] );
        }

        if( $before > 0 ) {
            $before = date( 'Y-m-d H:i:s', $before );
            $where .= " AND {$table_name}.date < '{$before}'";
        }

    }

    // After
    if( isset( $qv['after'] ) ) {

        $after = $qv['after'];

        // Turn a string date into time
        if( gettype( $qv['after'] ) === 'string' ) {
            $after = strtotime( $qv['after'] );
        }

        if( $after > 0 ) {
            $after = date( 'Y-m-d H:i:s', $after );
            $where .= " AND {$table_name}.date > '{$after}'";
        }

    }

    return $where;

}
add_filter( 'ct_query_where', 'gamipress_user_earnings_query_where', 10, 2 );

/**
 * Define the search fields for user earnings
 *
 * @since 1.6.4.2
 *
 * @param array $search_fields
 *
 * @return array
 */
function gamipress_user_earnings_search_fields( $search_fields ) {

    $search_fields[] = 'title';

    return $search_fields;

}
add_filter( 'ct_query_gamipress_user_earnings_search_fields', 'gamipress_user_earnings_search_fields' );

/**
 * Parse search query args for user earnings
 *
 * @since 1.6.4.2
 *
 * @param string $search
 * @param CT_Query $ct_query
 *
 * @return string
 */
function gamipress_user_earnings_query_search( $search, $ct_query ) {

    global $ct_table, $wpdb;

    if( $ct_table->name !== 'gamipress_user_earnings' ) {
        return $search;
    }

    $table_name = $ct_table->db->table_name;

    // Shorthand
    $qv = $ct_query->query_vars;

    // Check if is search and query is not filtered by an specific user
    if( isset( $qv['s'] ) && ! empty( $qv['s'] ) && ! isset( $qv['user_id'] ) ) {

        // Made an user sub-search to retrieve them
        $users = get_users( array(
            'search' => sprintf( '*%s*', $qv['s'] ),
            'search_columns' => array(
                'user_login',
                'user_email',
                'display_name',
            ),
            'fields' => 'ID',
        ) );

        if( ! empty( $users ) ) {
            $search .= " OR ( {$table_name}.user_id IN (" . implode( ',', array_map( 'absint', $users ) ) . ") )";
        }

    }

    return $search;

}
add_filter( 'ct_query_search', 'gamipress_user_earnings_query_search', 10, 2 );

/**
 * Parse query args for user earnings to be applied on GROUP BY clause
 *
 * @since  1.5.5
 *
 * @param string    $groupby
 * @param CT_Query  $ct_query
 *
 * @return string
 */
function gamipress_user_earnings_query_group_by( $groupby, $ct_query ) {

    global $ct_table;

    if( $ct_table->name !== 'gamipress_user_earnings' ) {
        return $groupby;
    }

    // Shorthand
    $qv = $ct_query->query_vars;

    // Check if 'groupby' has been passed to the query
    if( isset( $qv['groupby'] ) && ! empty( $qv['groupby'] ) ) {

        $groupby = $qv['groupby'];

    }

    return $groupby;

}
add_filter( 'ct_query_groupby', 'gamipress_user_earnings_query_group_by', 10, 2 );

/**
 * Bulk actions for user earnings list view
 *
 * @since  1.3.9.7
 *
 * @param array $actions
 *
 * @return array
 */
function custom_gamipress_user_earnings_bulk_actions( $actions = array() ) {

    return array();

}
add_filter( 'gamipress_user_earnings_bulk_actions', 'custom_gamipress_user_earnings_bulk_actions' );

/**
 * Columns for user earnings list view
 *
 * @since  1.2.8
 *
 * @param array $columns
 *
 * @return array
 */
function gamipress_manage_user_earnings_columns( $columns = array() ) {

    global $pagenow;

    $columns['name']    = __( 'Name', 'gamipress' );

    if( $pagenow === 'admin.php' ) {
        $columns['user_id']    = __( 'User', 'gamipress' );
    }

    $columns['date']    = __( 'Date', 'gamipress' );

    if( current_user_can( gamipress_get_manager_capability() ) ) {
        $columns['action']  = __( 'Action', 'gamipress' );
    }

    return $columns;
}
add_filter( 'manage_gamipress_user_earnings_columns', 'gamipress_manage_user_earnings_columns' );

/**
 * Sortable columns for user earnings list view
 *
 * @since 1.6.7
 *
 * @param array $sortable_columns
 *
 * @return array
 */
function gamipress_manage_user_earnings_sortable_columns( $sortable_columns ) {

    global $pagenow;

    if( $pagenow === 'admin.php' ) {
        $sortable_columns['name']       = array( 'title', false );
        $sortable_columns['date']       = array( 'date', true );
        $sortable_columns['user_id']    = array( 'user_id', false );
    }

    return $sortable_columns;

}
add_filter( 'manage_gamipress_user_earnings_sortable_columns', 'gamipress_manage_user_earnings_sortable_columns' );

/**
 * Columns rendering for user earnings list view
 *
 * @since  1.2.8
 *
 * @param string $column_name
 * @param integer $object_id
 */
function gamipress_manage_user_earnings_custom_column( $column_name, $object_id ) {

    $can_manage = current_user_can( gamipress_get_manager_capability() );

    // Setup vars
    $requirement_types = gamipress_get_requirement_types();
    $achievement_types = gamipress_get_achievement_types();
    $rank_types = gamipress_get_rank_types();
    $user_earning = ct_get_object( $object_id );

    switch( $column_name ) {
        case 'name':

            // On network wide active installs, we need to switch to main blog mostly for posts permalinks and thumbnails
            $blog_id = gamipress_switch_to_main_site_if_network_wide_active();

            // Check if assigned post exists to modify the output
            if( gamipress_post_exists( $user_earning->post_id ) ) : ?>

                <?php if( in_array( $user_earning->post_type, gamipress_get_requirement_types_slugs() ) ) : ?>

                    <?php if( $user_earning->post_type === 'step' && $parent_achievement = gamipress_get_step_achievement( $user_earning->post_id ) ) : ?>

                        <?php // Step ?>

                        <?php // Achievement thumbnail ?>
                        <?php echo gamipress_get_achievement_post_thumbnail( $parent_achievement->ID, array( 32, 32 ) ); ?>

                        <?php // Step title ?>
                        <strong><?php echo gamipress_get_post_field( 'post_title', $user_earning->post_id ); ?></strong>
                        <br>

                        <?php // Step relationship details ?>
                        <?php echo ( isset( $requirement_types[$user_earning->post_type] ) ? $requirement_types[$user_earning->post_type]['singular_name'] : '' ); ?>
                        <?php echo ( isset( $achievement_types[$parent_achievement->post_type] ) ? ', ' . $achievement_types[$parent_achievement->post_type]['singular_name'] . ': ' : '' ); ?>
                        <?php echo '<a href="' . ( $can_manage ? get_edit_post_link( $parent_achievement->ID ) : get_post_permalink( $parent_achievement->ID ) ) . '">' . gamipress_get_post_field( 'post_title', $parent_achievement->ID ) . '</a>'; ?>

                    <?php elseif( $user_earning->post_type === 'points-award' && $points_type = gamipress_get_points_award_points_type( $user_earning->post_id ) ) : ?>

                        <?php // Points award ?>

                        <?php // Points type thumbnail ?>
                        <?php echo gamipress_get_points_type_thumbnail( $points_type->ID, array( 32, 32 ) ); ?>

                        <?php // Points award title ?>
                        <strong><?php echo gamipress_get_post_field( 'post_title', $user_earning->post_id ); ?></strong>
                        <br>

                        <?php // Points award relationship details ?>
                        <?php echo ( isset( $requirement_types[$user_earning->post_type] ) ? $requirement_types[$user_earning->post_type]['singular_name'] : '' ); ?>
                        <?php echo ', ' . ( $can_manage ? '<a href="' . get_edit_post_link( $points_type->ID ) . '">' . gamipress_get_post_field( 'post_title', $points_type->ID ) . '</a>' : gamipress_get_post_field( 'post_title', $points_type->ID ) ); ?>

                    <?php elseif( $user_earning->post_type === 'points-deduct' && $points_type = gamipress_get_points_deduct_points_type( $user_earning->post_id ) ) : ?>

                        <?php // Points deduct ?>

                        <?php // Points type thumbnail ?>
                        <?php echo gamipress_get_points_type_thumbnail( $points_type->ID, array( 32, 32 ) ); ?>

                        <?php // Points deduct title ?>
                        <strong><?php echo gamipress_get_post_field( 'post_title', $user_earning->post_id ); ?></strong>

                        <?php // Points deduct relationship details ?>
                        <?php echo ( isset( $requirement_types[$user_earning->post_type] ) ? '<br>' . $requirement_types[$user_earning->post_type]['singular_name'] : '' ); ?>
                        <?php echo ', ' . ( $can_manage ? '<a href="' . get_edit_post_link( $points_type->ID ) . '">' . gamipress_get_post_field( 'post_title', $points_type->ID ) . '</a>' : gamipress_get_post_field( 'post_title', $points_type->ID ) ); ?>

                    <?php elseif( $user_earning->post_type === 'rank-requirement' && $rank = gamipress_get_rank_requirement_rank( $user_earning->post_id ) ) : ?>

                        <?php // Rank Requirement ?>

                        <?php // Rank thumbnail ?>
                        <?php echo gamipress_get_rank_post_thumbnail( $rank->ID, array( 32, 32 ) ); ?>

                        <?php // Rank requirement title ?>
                        <strong><?php echo gamipress_get_post_field( 'post_title', $user_earning->post_id ); ?></strong>

                        <?php // Rank relationship details ?>
                        <?php echo ( isset( $requirement_types[$user_earning->post_type] ) ? '<br>' . $requirement_types[$user_earning->post_type]['singular_name'] : '' ); ?>
                        <?php echo ( isset( $rank_types[$rank->post_type] ) ? ', ' . $rank_types[$rank->post_type]['singular_name'] . ': ' : '' ); ?>
                        <?php echo '<a href="' . ( $can_manage ? get_edit_post_link( $rank->ID ) : get_post_permalink( $rank->ID ) ) . '">' . gamipress_get_post_field( 'post_title', $rank->ID ) . '</a>'; ?>

                    <?php endif; ?>

                <?php elseif( in_array( $user_earning->post_type, gamipress_get_achievement_types_slugs() ) ) : ?>

                    <?php // Achievement ?>

                    <?php // Achievement thumbnail ?>
                    <?php echo gamipress_get_achievement_post_thumbnail( $user_earning->post_id, array( 32, 32 ) ); ?>

                    <?php // Achievement title ?>
                    <strong><?php echo '<a href="' . ( $can_manage ? get_edit_post_link( $user_earning->post_id ) : get_post_permalink( $user_earning->post_id ) ) . '">' . gamipress_get_post_field( 'post_title', $user_earning->post_id ) . '</a>'; ?></strong>
                    <?php echo ( isset( $achievement_types[$user_earning->post_type] ) ? '<br>' . $achievement_types[$user_earning->post_type]['singular_name'] : '' ); ?>

                <?php elseif( in_array( $user_earning->post_type, gamipress_get_rank_types_slugs() ) ) : ?>

                    <?php // Rank ?>

                    <?php // Rank thumbnail ?>
                    <?php echo gamipress_get_rank_post_thumbnail( $user_earning->post_id, array( 32, 32 ) ); ?>

                    <?php // Rank title ?>
                    <strong><?php echo '<a href="' . ( $can_manage ? get_edit_post_link( $user_earning->post_id ) : get_post_permalink( $user_earning->post_id ) ) . '">' . gamipress_get_post_field( 'post_title', $user_earning->post_id ) . '</a>'; ?></strong>
                    <?php echo ( isset( $rank_types[$user_earning->post_type] ) ? '<br>' . $rank_types[$user_earning->post_type]['singular_name'] : '' ); ?>

                <?php endif; ?>
            <?php else : ?>

                <?php // Default output if assigned post doesn't exists ?>

                <strong><?php echo $user_earning->title; ?></strong>

                <?php if( $post_type_object = get_post_type_object( $user_earning->post_type ) ) : ?>
                    <br>
                    <?php echo $post_type_object->labels->singular_name; ?>
                <?php endif; ?>

            <?php endif; ?>

            <button type="button" class="toggle-row"><span class="screen-reader-text"><?php __( 'Show more details', 'gamipress' ); ?></span></button>

            <?php

            // If switched to blog, return back to que current blog
            if( $blog_id !== get_current_blog_id() && is_multisite() ) {
                restore_current_blog();
            }

            break;
        case 'user_id':
            $user = get_userdata( $user_earning->user_id );

            if( $user ) :

                if( current_user_can( 'edit_users' ) ) {
                    ?>

                    <a href="<?php echo get_edit_user_link( $user_earning->user_id ); ?>"><?php echo $user->display_name . ' (' . $user->user_login . ')'; ?></a>

                    <?php
                } else {
                    echo $user->display_name;
                }

            endif;
            break;
        case 'date':
            $time = strtotime( $user_earning->date );
            ?>

            <abbr title="<?php echo date( 'Y/m/d g:i:s a', $time ); ?>"><?php echo date( 'Y/m/d', $time ); ?></abbr>

            <?php
            break;
        case 'action':

            // If user is not manager then skip this
            if( ! $can_manage ) {
                break;
            }

            // Setup our revoke URL
            $revoke_url = add_query_arg( array(
                'action'         => 'revoke',
                'user_id'        => absint( $user_earning->user_id ),
                'achievement_id' => absint( $user_earning->post_id ),
                'user_earning_id' => absint( $user_earning->user_earning_id ),
            ) );
            ?>

            <span class="delete"><a class="error gamipress-revoke-user-earning" href="<?php echo esc_url( wp_nonce_url( $revoke_url, 'gamipress_revoke_achievement' ) ); ?>"><?php _e( 'Revoke Award', 'gamipress' ); ?></a></span>

            <?php
            break;
    }
}
add_action( 'manage_gamipress_user_earnings_custom_column', 'gamipress_manage_user_earnings_custom_column', 10, 2 );

/**
 * Remove row actions on user earnings table
 *
 * @since  1.2.8
 *
 * @param array $row_actions
 * @param stdClass $object
 *
 * @return array
 */
function gamipress_user_earnings_row_actions( $row_actions, $object ) {
    return array();
}
add_filter( 'gamipress_user_earnings_row_actions', 'gamipress_user_earnings_row_actions', 10, 2 );