(function( $ ) {

    // Listen for our change to our trigger type selectors
    $('.requirements-list').on( 'change', '.select-trigger-type', function() {

        // Grab our selected trigger type and achievement selector
        var trigger_type = $(this).val();
        var score_input = $(this).siblings('.ld-quiz-score');
        var min_score_input = $(this).siblings('.ld-quiz-min-score');
        var max_score_input = $(this).siblings('.ld-quiz-max-score');

        // Toggle score field visibility
        if(
            trigger_type === 'gamipress_ld_complete_quiz_grade'
            || trigger_type === 'gamipress_ld_complete_specific_quiz_grade'
            || trigger_type === 'gamipress_ld_complete_quiz_specific_course_grade'
            || trigger_type === 'gamipress_ld_complete_quiz_max_grade'
            || trigger_type === 'gamipress_ld_complete_specific_quiz_max_grade'
            || trigger_type === 'gamipress_ld_complete_quiz_specific_course_max_grade'
        ) {
            score_input.show();
        } else {
            score_input.hide();
        }

        // Toggle min and max score fields visibility
        if(
            trigger_type === 'gamipress_ld_complete_quiz_between_grade'
            || trigger_type === 'gamipress_ld_complete_specific_quiz_between_grade'
            || trigger_type === 'gamipress_ld_complete_quiz_specific_course_between_grade'
        ) {
            min_score_input.show();
            max_score_input.show();
        } else {
            min_score_input.hide();
            max_score_input.hide();
        }

    });

    // Loop requirement list items to show/hide score input on initial load
    $('.requirements-list li').each(function() {

        // Grab our selected trigger type and achievement selector
        var trigger_type = $(this).find('.select-trigger-type').val();
        var score_input = $(this).find('.ld-quiz-score');
        var min_score_input = $(this).find('.ld-quiz-min-score');
        var max_score_input = $(this).find('.ld-quiz-max-score');

        // Toggle score field visibility
        if(
            trigger_type === 'gamipress_ld_complete_quiz_grade'
            || trigger_type === 'gamipress_ld_complete_specific_quiz_grade'
            || trigger_type === 'gamipress_ld_complete_quiz_max_grade'
            || trigger_type === 'gamipress_ld_complete_specific_quiz_max_grade'
        ) {
            score_input.show();
        } else {
            score_input.hide();
        }

        // Toggle min and max score fields visibility
        if(
            trigger_type === 'gamipress_ld_complete_quiz_between_grade'
            || trigger_type === 'gamipress_ld_complete_specific_quiz_between_grade'
            || trigger_type === 'gamipress_ld_complete_quiz_specific_course_between_grade'
        ) {
            min_score_input.show();
            max_score_input.show();
        } else {
            min_score_input.hide();
            max_score_input.hide();
        }

    });

    $('.requirements-list').on( 'update_requirement_data', '.requirement-row', function(e, requirement_details, requirement) {

        // Add score field
        if(
            requirement_details.trigger_type === 'gamipress_ld_complete_quiz_grade'
            || requirement_details.trigger_type === 'gamipress_ld_complete_specific_quiz_grade'
            || requirement_details.trigger_type === 'gamipress_ld_complete_quiz_specific_course_grade'
            || requirement_details.trigger_type === 'gamipress_ld_complete_quiz_max_grade'
            || requirement_details.trigger_type === 'gamipress_ld_complete_specific_quiz_max_grade'
            || requirement_details.trigger_type === 'gamipress_ld_complete_quiz_specific_course_max_grade'
        ) {
            requirement_details.ld_score = requirement.find( '.ld-quiz-score input' ).val();
        }

        // Add min and max score fields
        if(
            requirement_details.trigger_type === 'gamipress_ld_complete_quiz_between_grade'
            || requirement_details.trigger_type === 'gamipress_ld_complete_specific_quiz_between_grade'
            || requirement_details.trigger_type === 'gamipress_ld_complete_quiz_specific_course_between_grade'
        ) {
            requirement_details.ld_min_score = requirement.find( '.ld-quiz-min-score input' ).val();
            requirement_details.ld_max_score = requirement.find( '.ld-quiz-max-score input' ).val();
        }
    });

})( jQuery );