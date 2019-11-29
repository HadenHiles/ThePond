<?php
/**
 * Requirements
 *
 * @package GamiPress\LearnDash\Requirements
 * @since 1.0.4
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Add the score field to the requirement object
 *
 * @param $requirement
 * @param $requirement_id
 *
 * @return array
 */
function gamipress_ld_requirement_object( $requirement, $requirement_id ) {

    if( isset( $requirement['trigger_type'] )
        && ( $requirement['trigger_type'] === 'gamipress_ld_complete_quiz_grade'
            || $requirement['trigger_type'] === 'gamipress_ld_complete_specific_quiz_grade'
            || $requirement['trigger_type'] === 'gamipress_ld_complete_quiz_specific_course_grade'
            || $requirement['trigger_type'] === 'gamipress_ld_complete_quiz_max_grade'
            || $requirement['trigger_type'] === 'gamipress_ld_complete_specific_quiz_max_grade'
            || $requirement['trigger_type'] === 'gamipress_ld_complete_quiz_specific_course_max_grade' ) ) {

        // Minimum/Maximum grade percent
        $requirement['ld_score'] = get_post_meta( $requirement_id, '_gamipress_ld_score', true );

    }

    if( isset( $requirement['trigger_type'] )
        && ( $requirement['trigger_type'] === 'gamipress_ld_complete_quiz_between_grade'
            || $requirement['trigger_type'] === 'gamipress_ld_complete_specific_quiz_between_grade'
            || $requirement['trigger_type'] === 'gamipress_ld_complete_quiz_specific_course_between_grade' ) ) {

        // Between grade percent
        $requirement['ld_min_score'] = get_post_meta( $requirement_id, '_gamipress_ld_min_score', true );
        $requirement['ld_max_score'] = get_post_meta( $requirement_id, '_gamipress_ld_max_score', true );

    }

    return $requirement;
}
add_filter( 'gamipress_requirement_object', 'gamipress_ld_requirement_object', 10, 2 );

/**
 * Category field on requirements UI
 *
 * @param $requirement_id
 * @param $post_id
 */
function gamipress_ld_requirement_ui_fields( $requirement_id, $post_id ) {

    $score = absint( get_post_meta( $requirement_id, '_gamipress_ld_score', true ) );
    $min_score = get_post_meta( $requirement_id, '_gamipress_ld_min_score', true );
    $max_score = get_post_meta( $requirement_id, '_gamipress_ld_max_score', true );
    ?>

    <span class="ld-quiz-score"><input type="text" value="<?php echo $score; ?>" size="3" maxlength="3" placeholder="100" />%</span>
    <span class="ld-quiz-min-score"><input type="text" value="<?php echo ( ! empty( $min_score ) ? absint( $min_score ) : '' ); ?>" size="3" maxlength="3" placeholder="Min" />% -</span>
    <span class="ld-quiz-max-score"><input type="text" value="<?php echo ( ! empty( $max_score ) ? absint( $max_score ) : '' ); ?>" size="3" maxlength="3" placeholder="Max" />%</span>

    <?php
}
add_action( 'gamipress_requirement_ui_html_after_achievement_post', 'gamipress_ld_requirement_ui_fields', 10, 2 );

/**
 * Custom handler to save the score on requirements UI
 *
 * @param $requirement_id
 * @param $requirement
 */
function gamipress_ld_ajax_update_requirement( $requirement_id, $requirement ) {

    if( isset( $requirement['trigger_type'] )
        && ( $requirement['trigger_type'] === 'gamipress_ld_complete_quiz_grade'
            || $requirement['trigger_type'] === 'gamipress_ld_complete_specific_quiz_grade'
            || $requirement['trigger_type'] === 'gamipress_ld_complete_quiz_specific_course_grade'
            || $requirement['trigger_type'] === 'gamipress_ld_complete_quiz_max_grade'
            || $requirement['trigger_type'] === 'gamipress_ld_complete_specific_quiz_max_grade'
            || $requirement['trigger_type'] === 'gamipress_ld_complete_quiz_specific_course_max_grade' ) ) {

        // Save the score field
        update_post_meta( $requirement_id, '_gamipress_ld_score', $requirement['ld_score'] );

    }

    if( isset( $requirement['trigger_type'] )
        && ( $requirement['trigger_type'] === 'gamipress_ld_complete_quiz_between_grade'
            || $requirement['trigger_type'] === 'gamipress_ld_complete_specific_quiz_between_grade'
            || $requirement['trigger_type'] === 'gamipress_ld_complete_quiz_specific_course_between_grade' ) ) {

        // Between grade percent
        update_post_meta( $requirement_id, '_gamipress_ld_min_score', $requirement['ld_min_score'] );
        update_post_meta( $requirement_id, '_gamipress_ld_max_score', $requirement['ld_max_score'] );

    }
}
add_action( 'gamipress_ajax_update_requirement', 'gamipress_ld_ajax_update_requirement', 10, 2 );