<?php
/**
 * Listeners
 *
 * @package GamiPress\LearnDash\Listeners
 * @since 1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Complete quiz
 *
 * @since 1.0.0
 *
 * @param array $quiz_data array(
 *      'course' => WP_Post,
 *      'quiz' => WP_Post,
 *      'pass' => integer,
 *      'percentage' => integer,
 * )
 * @param WP_User $current_user
 */
function gamipress_ld_complete_quiz( $quiz_data, $current_user ) {

    $course_id = $quiz_data['course'] instanceof WP_Post ? absint( $quiz_data['course']->ID ) : 0;
    $score = absint( $quiz_data['percentage'] );

    // Complete any quiz
    do_action( 'gamipress_ld_complete_quiz', $quiz_data['quiz']->ID, $current_user->ID, $course_id, $quiz_data );

    // Complete specific quiz
    do_action( 'gamipress_ld_complete_specific_quiz', $quiz_data['quiz']->ID, $current_user->ID, $course_id, $quiz_data );

    // Minimum grade events

    // Complete any quiz with a minimum percent grade
    do_action( 'gamipress_ld_complete_quiz_grade', $quiz_data['quiz']->ID, $current_user->ID, $course_id, $score, $quiz_data );

    // Complete specific quiz with a minimum percent grade
    do_action( 'gamipress_ld_complete_specific_quiz_grade', $quiz_data['quiz']->ID, $current_user->ID, $course_id, $score, $quiz_data );

    // Maximum grade events

    // Complete any quiz with a maximum percent grade
    do_action( 'gamipress_ld_complete_quiz_max_grade', $quiz_data['quiz']->ID, $current_user->ID, $course_id, $score, $quiz_data );

    // Complete specific quiz with a maximum percent grade
    do_action( 'gamipress_ld_complete_specific_quiz_max_grade', $quiz_data['quiz']->ID, $current_user->ID, $course_id, $score, $quiz_data );

    // Between grade events

    // Complete any quiz on a range of percent grade
    do_action( 'gamipress_ld_complete_quiz_between_grade', $quiz_data['quiz']->ID, $current_user->ID, $course_id, $score, $quiz_data );

    // Complete specific quiz on a range of percent grade
    do_action( 'gamipress_ld_complete_specific_quiz_between_grade', $quiz_data['quiz']->ID, $current_user->ID, $course_id, $score, $quiz_data );

    // If user has successfully passed the quiz
    if( $quiz_data['pass'] ) {

        // Pass any quiz
        do_action( 'gamipress_ld_pass_quiz', $quiz_data['quiz']->ID, $current_user->ID, $course_id, $quiz_data );

        // Pass specific quiz
        do_action( 'gamipress_ld_pass_specific_quiz', $quiz_data['quiz']->ID, $current_user->ID, $course_id, $quiz_data );

    } else {
        // User has failed the quiz

        // Fail any quiz
        do_action( 'gamipress_ld_fail_quiz', $quiz_data['quiz']->ID, $current_user->ID, $course_id, $quiz_data );

        // Fail specific quiz
        do_action( 'gamipress_ld_fail_specific_quiz', $quiz_data['quiz']->ID, $current_user->ID, $course_id, $quiz_data );

    }

}
add_action( 'learndash_quiz_completed', 'gamipress_ld_complete_quiz', 10, 2 );

/**
 * Complete quiz on a specific course
 *
 * @since 1.1.2
 *
 * @param array $quiz_data array(
 *      'course' => WP_Post,
 *      'quiz' => WP_Post,
 *      'pass' => integer,
 *      'percentage' => integer,
 * )
 * @param WP_User $current_user
 */
function gamipress_ld_complete_quiz_specific_course( $quiz_data, $current_user ) {

    $course_id = $quiz_data['course'] instanceof WP_Post ? absint( $quiz_data['course']->ID ) : 0;
    $score = absint( $quiz_data['percentage'] );

    if( $course_id === 0 ) {
        return;
    }

    // Complete any quiz of a specific course
    do_action( 'gamipress_ld_complete_quiz_specific_course', $quiz_data['quiz']->ID, $current_user->ID, $course_id, $quiz_data );

    // Minimum grade events

    // Complete any quiz of a specific course with a minimum percent grade
    do_action( 'gamipress_ld_complete_quiz_specific_course_grade', $quiz_data['quiz']->ID, $current_user->ID, $course_id, $score, $quiz_data );

    // Maximum grade events

    // Complete any quiz of a specific course with a maximum percent grade
    do_action( 'gamipress_ld_complete_quiz_specific_course_max_grade', $quiz_data['quiz']->ID, $current_user->ID, $course_id, $score, $quiz_data );

    // Between grade events

    // Complete any quiz of a specific course on a range of percent grade
    do_action( 'gamipress_ld_complete_quiz_specific_course_between_grade', $quiz_data['quiz']->ID, $current_user->ID, $course_id, $score, $quiz_data );

    // If user has successfully passed the quiz
    if( $quiz_data['pass'] ) {

        // Pass any quiz of a specific course
        do_action( 'gamipress_ld_pass_quiz_specific_course', $quiz_data['quiz']->ID, $current_user->ID, $course_id, $quiz_data );

    } else {

        // Fail any quiz of a specific course
        do_action( 'gamipress_ld_fail_quiz_specific_course', $quiz_data['quiz']->ID, $current_user->ID, $course_id, $quiz_data );

    }

}
add_action( 'learndash_quiz_completed', 'gamipress_ld_complete_quiz_specific_course', 10, 2 );

/**
 * Complete topic
 *
 * @since 1.0.0
 *
 * @param array $args array(
 *      'user' => WP_User,
 *      'course' => WP_Post,
 *      'lesson' => WP_Post,
 *      'topic' => WP_Post,
 *      'progress' => array,
 * )
 */
function gamipress_ld_complete_topic( $args ) {

    $course_id = $args['course'] instanceof WP_Post ? absint( $args['course']->ID ) : 0;

    // Complete any topic
    do_action( 'gamipress_ld_complete_topic', $args['topic']->ID, $args['user']->ID, $args['lesson']->ID, $course_id, $args );

    // Complete specific topic
    do_action( 'gamipress_ld_complete_specific_topic', $args['topic']->ID, $args['user']->ID, $args['lesson']->ID, $course_id, $args );

    if( $course_id !== 0 ) {
        // Complete any topic of a specific course
        do_action( 'gamipress_ld_complete_topic_specific_course', $args['topic']->ID, $args['user']->ID, $args['lesson']->ID, $args['course']->ID, $args );
    }

}
add_action( 'learndash_topic_completed', 'gamipress_ld_complete_topic' );

/**
 * Complete lesson
 *
 * @since 1.0.0
 *
 * @param array $args array(
 *      'user' => WP_User,
 *      'course' => WP_Post,
 *      'lesson' => WP_Post,
 *      'progress' => array,
 * )
 */
function gamipress_ld_complete_lesson( $args ) {

    $course_id = $args['course'] instanceof WP_Post ? absint( $args['course']->ID ) : 0;

    // Complete any lesson
    do_action( 'gamipress_ld_complete_lesson', $args['lesson']->ID, $args['user']->ID, $course_id, $args );

    // Complete specific lesson
    do_action( 'gamipress_ld_complete_specific_lesson', $args['lesson']->ID, $args['user']->ID, $course_id, $args );

    if( $course_id !== 0 ) {
        // Complete any lesson of a specific course
        do_action( 'gamipress_ld_complete_lesson_specific_course', $args['lesson']->ID, $args['user']->ID, $course_id, $args );
    }

}
add_action( 'learndash_lesson_completed', 'gamipress_ld_complete_lesson' );

/**
 * Complete course
 *
 * @since 1.0.0
 *
 * @param array $args array(
 *      'user' => WP_User,
 *      'course' => WP_Post,
 *      'progress' => array,
 * )
 */
function gamipress_ld_complete_course( $args ) {

    // Complete any course
    do_action( 'gamipress_ld_complete_course', $args['course']->ID, $args['user']->ID, $args );

    // Complete specific course
    do_action( 'gamipress_ld_complete_specific_course', $args['course']->ID, $args['user']->ID, $args );

}
add_action( 'learndash_course_completed', 'gamipress_ld_complete_course' );

/**
 * Assignment uploaded
 *
 * @since 1.1.3
 *
 * @param int 		$assignment_id 	    Newly created assignment post ID which the assignment is uploaded to
 * @param array 	$assignment_meta    Assignment meta data: array(
 *      'user_id' => int,
 *      'lesson_id' => int,
 *      'course_id' => int
 * )
 */
function gamipress_ld_assignment_upload( $assignment_id, $assignment_meta ) {

    // Upload an assignment
    do_action( 'gamipress_ld_assignment_upload', $assignment_id, $assignment_meta['user_id'], $assignment_meta['lesson_id'], $assignment_meta['course_id'], $assignment_meta );

    // Upload an assignment to a specific lesson
    do_action( 'gamipress_ld_assignment_upload_specific_lesson', $assignment_id, $assignment_meta['user_id'], $assignment_meta['lesson_id'], $assignment_meta['course_id'], $assignment_meta );

    // Upload an assignment to a specific course
    do_action( 'gamipress_ld_assignment_upload_specific_course', $assignment_id, $assignment_meta['user_id'], $assignment_meta['lesson_id'], $assignment_meta['course_id'], $assignment_meta );

}
add_action( 'learndash_assignment_uploaded', 'gamipress_ld_assignment_upload', 10, 2 );

/**
 * Assignment approved
 *
 * @since 1.1.3
 *
 * @param int 		$assignment_id 	    Newly created assignment post ID which the assignment is uploaded to
 */
function gamipress_ld_approve_assignment( $assignment_id ) {

    $assignment = get_post( $assignment_id );

    if( $assignment ) {

        $lesson_id = get_post_meta( $assignment_id, 'lesson_id', true );
        $course_id = get_post_meta( $assignment_id, 'course_id', true );

        // Approve an assignment
        do_action( 'gamipress_ld_approve_assignment', $assignment_id, $assignment->post_author, $lesson_id, $course_id );

        // Approve an assignment of a specific lesson
        do_action( 'gamipress_ld_approve_assignment_specific_lesson', $assignment_id, $assignment->post_author, $lesson_id, $course_id );

        // Approve an assignment of a specific course
        do_action( 'gamipress_ld_approve_assignment_specific_course', $assignment_id, $assignment->post_author, $lesson_id, $course_id );

    }

}
add_action( 'learndash_assignment_approved', 'gamipress_ld_approve_assignment' );
