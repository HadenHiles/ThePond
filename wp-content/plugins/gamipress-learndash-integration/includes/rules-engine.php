<?php
/**
 * Rules Engine
 *
 * @package GamiPress\LearnDash\Rules_Engine
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Checks if an user is allowed to work on a given requirement related to a minimum of score
 *
 * @since  1.0.0
 *
 * @param bool $return          The default return value
 * @param int $user_id          The given user's ID
 * @param int $requirement_id   The given requirement's post ID
 * @param string $trigger       The trigger triggered
 * @param int $site_id          The site id
 * @param array $args           Arguments of this trigger
 *
 * @return bool True if user has access to the requirement, false otherwise
 */
function gamipress_ld_user_has_access_to_achievement( $return = false, $user_id = 0, $requirement_id = 0, $trigger = '', $site_id = 0, $args = array() ) {

    // If we're not working with a requirement, bail here
    if ( ! in_array( get_post_type( $requirement_id ), gamipress_get_requirement_types_slugs() ) )
        return $return;

    // Check if user has access to the achievement ($return will be false if user has exceed the limit or achievement is not published yet)
    if( ! $return )
        return $return;

    // If is minimum score trigger, rules engine needs to check the minimum score
    if( $trigger === 'gamipress_ld_complete_quiz_grade'
        || $trigger === 'gamipress_ld_complete_specific_quiz_grade'
        || $trigger === 'gamipress_ld_complete_quiz_specific_course_grade' ) {

        $score = absint( $args[3] );

        $required_score = absint( get_post_meta( $requirement_id, '_gamipress_ld_score', true ) );

        // True if there is score is bigger than required score
        $return = (bool) ( $score >= $required_score );
    }

    // If is maximum score trigger, rules engine needs to check the maximum score
    if( $trigger === 'gamipress_ld_complete_quiz_max_grade'
        || $trigger === 'gamipress_ld_complete_specific_quiz_max_grade'
        || $trigger === 'gamipress_ld_complete_quiz_specific_course_max_grade' ) {

        $score = absint( $args[3] );

        $required_score = absint( get_post_meta( $requirement_id, '_gamipress_ld_score', true ) );

        // True if there is score is lower than required score
        $return = (bool) ( $score <= $required_score );
    }

    // If is between score trigger, rules engine needs to check the minimum and maximum score allowed
    if( $trigger === 'gamipress_ld_complete_quiz_between_grade'
        || $trigger === 'gamipress_ld_complete_specific_quiz_between_grade'
        || $trigger === 'gamipress_ld_complete_quiz_specific_course_between_grade' ) {

        $score = absint( $args[3] );

        $min_score = absint( get_post_meta( $requirement_id, '_gamipress_ld_min_score', true ) );
        $max_score = absint( get_post_meta( $requirement_id, '_gamipress_ld_max_score', true ) );

        // True if there is score is bigger than min score and lower than max score
        $return = (bool) ( $score >= $min_score && $score <= $max_score );
    }

    // Send back our eligibility
    return $return;
}
add_filter( 'user_has_access_to_achievement', 'gamipress_ld_user_has_access_to_achievement', 10, 6 );