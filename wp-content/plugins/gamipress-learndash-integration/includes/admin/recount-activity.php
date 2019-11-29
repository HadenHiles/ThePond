<?php
/**
 * Recount Activity
 *
 * @package GamiPress\LearnDash\Admin\Recount_Activity
 * @since 1.0.9
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Add recountable options to the Recount Activity Tool
 *
 * @since 1.0.9
 *
 * @param array $recountable_activity_triggers
 *
 * @return array
 */
function gamipress_ld_recountable_activity_triggers( $recountable_activity_triggers ) {

    // LearnDash
    $recountable_activity_triggers[__( 'LearnDash', 'gamipress-learndash-integration' )] = array(
        'ld_quizzes'    => __( 'Recount quizzes completed', 'gamipress-learndash-integration' ),
        'ld_topics'     => __( 'Recount topics completed', 'gamipress-learndash-integration' ),
        'ld_lessons'    => __( 'Recount lessons completed', 'gamipress-learndash-integration' ),
        'ld_courses'    => __( 'Recount courses completed', 'gamipress-learndash-integration' ),
    );

    return $recountable_activity_triggers;

}
add_filter( 'gamipress_recountable_activity_triggers', 'gamipress_ld_recountable_activity_triggers' );

/**
 * Recount quizzes completed
 *
 * @since   1.0.9
 * @updated 1.1.0 Added $loop parameter
 *
 * @param array $response
 * @param int   $loop
 *
 * @return array $response
 */
function gamipress_ld_activity_recount_quizzes( $response, $loop ) {

    global $wpdb;

    // Set a limit of 100 users
    $limit = 100;
    $offset = ( $loop !== 0 ? $limit * ( $loop - 1 ) : 0 );

    // Meta where information resides
    $meta_key = '_sfwd-quizzes';

    // Get all stored users count
    $users_count = absint( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->users} AS u LEFT JOIN {$wpdb->usermeta} AS um ON um.user_id = u.ID AND um.meta_key = '{$meta_key}' WHERE um.meta_key IS NOT NULL" ) );

    // On first loop send an informational text
    if( $loop === 0 && $users_count > $limit ) {
        $response['run_again'] = true;
        $response['message'] = sprintf( __( '%d users found, recounting...', 'gamipress' ), $users_count );

        // Return early to inform
        return $response;
    }

    // Get all stored users
    $users = $wpdb->get_results( "SELECT u.ID FROM {$wpdb->users} AS u LEFT JOIN {$wpdb->usermeta} AS um ON um.user_id = u.ID AND um.meta_key = '{$meta_key}' WHERE um.meta_key IS NOT NULL LIMIT {$offset}, {$limit}" );

    foreach( $users as $user ) {

        // Get all user quizzes completed
        $quizzes = get_user_meta( $user->ID, $meta_key, true );

        foreach( $quizzes as $quiz ) {

            $quiz['course'] = get_post( $quiz['course'] );

            gamipress_ld_complete_quiz( $quiz, $user );
        }
    }

    $recounted_users = $limit * ( $loop + 1 );

    // Check remaining posts
    if( $recounted_users < $users_count ) {
        $response['run_again'] = true;
        $response['message'] = sprintf( __( '%d remaining users to finish recount', 'gamipress' ), ( $users_count - $recounted_users ) );
    }

    return $response;

}
add_filter( 'gamipress_activity_recount_ld_quizzes', 'gamipress_ld_activity_recount_quizzes', 10, 2 );

/**
 * Recount topics completed
 *
 * @since   1.0.9
 * @updated 1.1.0 Added $loop parameter
 *
 * @param array $response
 * @param int   $loop
 *
 * @return array $response
 */
function gamipress_ld_activity_recount_topics( $response, $loop ) {

    global $wpdb;

    // Set a limit of 100 users
    $limit = 100;
    $offset = ( $loop !== 0 ? $limit * ( $loop - 1 ) : 0 );

    // Meta where information resides
    $meta_key = '_sfwd-course_progress';

    // Get all stored users count
    $users_count = absint( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->users} AS u LEFT JOIN {$wpdb->usermeta} AS um ON um.user_id = u.ID AND um.meta_key = '{$meta_key}' WHERE um.meta_key IS NOT NULL" ) );

    // On first loop send an informational text
    if( $loop === 0 && $users_count > $limit ) {
        $response['run_again'] = true;
        $response['message'] = sprintf( __( '%d users found, recounting...', 'gamipress' ), $users_count );

        // Return early to inform
        return $response;
    }

    // Get all stored users
    $users = $wpdb->get_results( "SELECT u.ID FROM {$wpdb->users} AS u LEFT JOIN {$wpdb->usermeta} AS um ON um.user_id = u.ID AND um.meta_key = '{$meta_key}' WHERE um.meta_key IS NOT NULL LIMIT {$offset}, {$limit}" );

    foreach( $users as $user ) {

        // Get all user courses completed
        $courses = get_user_meta( $user->ID, $meta_key, true );

        foreach( $courses as $course_id => $course ) {

            // Loop all topics completed (topics are separated in lessons)
            foreach( $course['topics'] as $lesson_id => $topics ) {

                // Loop all lesson topics completed
                foreach( $topics as $topic_id => $completed ) {

                    if( $completed ) {

                        $args = array(
                            'topic' => get_post( $topic_id ),
                            'user' => $user,
                            'lesson' => get_post( $lesson_id ),
                            'course' => get_post( $course_id ),
                        );

                        gamipress_ld_complete_topic( $args );

                    }

                }

            }
        }
    }

    $recounted_users = $limit * ( $loop + 1 );

    // Check remaining posts
    if( $recounted_users < $users_count ) {
        $response['run_again'] = true;
        $response['message'] = sprintf( __( '%d remaining users to finish recount', 'gamipress' ), ( $users_count - $recounted_users ) );
    }

    return $response;

}
add_filter( 'gamipress_activity_recount_ld_topics', 'gamipress_ld_activity_recount_topics', 10, 2 );

/**
 * Recount lessons completed
 *
 * @since   1.0.9
 * @updated 1.1.0 Added $loop parameter
 *
 * @param array $response
 * @param int   $loop
 *
 * @return array $response
 */
function gamipress_ld_activity_recount_lessons( $response, $loop ) {

    global $wpdb;

    // Set a limit of 100 users
    $limit = 100;
    $offset = ( $loop !== 0 ? $limit * ( $loop - 1 ) : 0 );

    // Meta where information resides
    $meta_key = '_sfwd-course_progress';

    // Get all stored users count
    $users_count = absint( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->users} AS u LEFT JOIN {$wpdb->usermeta} AS um ON um.user_id = u.ID AND um.meta_key = '{$meta_key}' WHERE um.meta_key IS NOT NULL" ) );

    // On first loop send an informational text
    if( $loop === 0 && $users_count > $limit ) {
        $response['run_again'] = true;
        $response['message'] = sprintf( __( '%d users found, recounting...', 'gamipress' ), $users_count );

        // Return early to inform
        return $response;
    }

    // Get all stored users
    $users = $wpdb->get_results( "SELECT u.ID FROM {$wpdb->users} AS u LEFT JOIN {$wpdb->usermeta} AS um ON um.user_id = u.ID AND um.meta_key = '{$meta_key}' WHERE um.meta_key IS NOT NULL LIMIT {$offset}, {$limit}" );

    foreach( $users as $user ) {

        // Get all user courses completed
        $courses = get_user_meta( $user->ID, $meta_key, true );

        foreach( $courses as $course_id => $course ) {

            // Loop all lessons completed
            foreach( $course['lessons'] as $lesson_id => $completed ) {

                if( $completed ) {

                    $args = array(
                        'user' => $user,
                        'lesson' => get_post( $lesson_id ),
                        'course' => get_post( $course_id ),
                    );

                    gamipress_ld_complete_lesson( $args );

                }

            }
        }
    }

    $recounted_users = $limit * ( $loop + 1 );

    // Check remaining posts
    if( $recounted_users < $users_count ) {
        $response['run_again'] = true;
        $response['message'] = sprintf( __( '%d remaining users to finish recount', 'gamipress' ), ( $users_count - $recounted_users ) );
    }

    return $response;

}
add_filter( 'gamipress_activity_recount_ld_lessons', 'gamipress_ld_activity_recount_lessons', 10, 2 );

/**
 * Recount courses completed
 *
 * @since   1.0.9
 * @updated 1.1.0 Added $loop parameter
 *
 * @param array $response
 * @param int   $loop
 *
 * @return array $response
 */
function gamipress_ld_activity_recount_courses( $response, $loop ) {

    global $wpdb;

    // Set a limit of 100 users
    $limit = 100;
    $offset = ( $loop !== 0 ? $limit * ( $loop - 1 ) : 0 );

    // Meta where information resides
    $meta_key = '_sfwd-course_progress';

    // Get all stored users count
    $users_count = absint( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->users} AS u LEFT JOIN {$wpdb->usermeta} AS um ON um.user_id = u.ID AND um.meta_key = '{$meta_key}' WHERE um.meta_key IS NOT NULL" ) );

    // On first loop send an informational text
    if( $loop === 0 && $users_count > $limit ) {
        $response['run_again'] = true;
        $response['message'] = sprintf( __( '%d users found, recounting...', 'gamipress' ), $users_count );

        // Return early to inform
        return $response;
    }

    // Get all stored users
    $users = $wpdb->get_results( "SELECT u.ID FROM {$wpdb->users} AS u LEFT JOIN {$wpdb->usermeta} AS um ON um.user_id = u.ID AND um.meta_key = '{$meta_key}' WHERE um.meta_key IS NOT NULL LIMIT {$offset}, {$limit}" );

    foreach( $users as $user ) {

        // Get all user courses completed
        $courses = get_user_meta( $user->ID, $meta_key, true );

        foreach( $courses as $course_id => $course ) {

            $completed = get_user_meta( $user->ID, 'course_completed_' . $course_id, true );

            if( $completed ) {

                $args = array(
                    'user' => $user,
                    'course' => get_post( $course_id ),
                );

                gamipress_ld_complete_course( $args );

            }

        }
    }

    $recounted_users = $limit * ( $loop + 1 );

    // Check remaining posts
    if( $recounted_users < $users_count ) {
        $response['run_again'] = true;
        $response['message'] = sprintf( __( '%d remaining users to finish recount', 'gamipress' ), ( $users_count - $recounted_users ) );
    }

    return $response;

}
add_filter( 'gamipress_activity_recount_ld_courses', 'gamipress_ld_activity_recount_courses', 10, 2 );