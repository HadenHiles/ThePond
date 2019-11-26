(function( $ ) {

    // ----------------------------------
    // Reset Data Tool
    // ----------------------------------

    var reset_data_dialog = $("#reset-data-dialog");

    reset_data_dialog.dialog({
        dialogClass   : 'wp-dialog',
        modal         : true,
        autoOpen      : false,
        closeOnEscape : true,
        draggable     : false,
        width         : 500,
        buttons       : [
            {
                text: "Yes, delete it permanently",
                class: "button-primary reset-data-button",
                click: function() {
                    $('.reset-data-button').prop('disabled', true);

                    $('.reset-data-button').parent().parent().prepend('<span id="reset-data-response"><span class="spinner is-active" style="float: none;"></span></span>');

                    var items = [];

                    $('.cmb2-id-data-to-reset input:checked').each(function() {
                        items.push($(this).val());
                    });

                    $.post(
                        ajaxurl,
                        {
                            action: 'gamipress_reset_data_tool',
                            items: items
                        },
                        function( response ) {

                            if( response.success === false ) {
                                $('#reset-data-response').css({color:'#a00'});
                            }

                            $('#reset-data-response').html(response.data);

                            if( response.success === true ) {

                                setTimeout(function() {
                                    $('.cmb2-id-data-to-reset input:checked').each(function() {
                                        $(this).prop( 'checked', false );
                                    });

                                    $('#reset-data-response').remove();

                                    reset_data_dialog.dialog( "close" );
                                }, 5000);
                            }

                            $('.reset-data-button').prop('disabled', false);
                        }
                    );
                }
            },
            {
                text: "Cancel",
                class: "cancel-reset-data-button",
                click: function() {
                    $( this ).dialog( "close" );
                }
            }

        ]
    });

    $("#reset_data").click(function(e) {
        e.preventDefault();

        $('#reset-data-warning').remove();

        var checked_options = $('.cmb2-id-data-to-reset input:checked');

        if( checked_options.length ) {

            var reminder_html = '';

            checked_options.each(function() {
                reminder_html += '<li>' + $(this).next().text() + '</li>'
            });

            // Add a reminder with data to be removed
            $('#reset-data-reminder').html('<ul>' + reminder_html + '</ul>');

            // Open our dialog
            reset_data_dialog.dialog('open');

            // Remove the initial jQuery UI Dialog auto focus
            $('.ui-dialog :button').blur();
        } else {
            $(this).parent().prepend('<p id="reset-data-warning" class="cmb2-metabox-description" style="color: #a00;">You need to choose at least one option.</p>');
        }
    });

    $('.cmb2-id-data-to-reset').on('change', 'input', function() {

        $('#reset-data-warning').remove();

        var checked_option = $(this).val();

        if( checked_option === 'achievement_types' ) {
            $('.cmb2-id-data-to-reset input[value="achievements"], .cmb2-id-data-to-reset input[value="steps"]').prop( 'checked', $(this).prop( 'checked' ) );
        } else if( checked_option === 'achievements' ) {
            $('.cmb2-id-data-to-reset input[value="steps"]').prop( 'checked', $(this).prop( 'checked' ) );
        } else if( checked_option === 'points_types' ) {
            $('.cmb2-id-data-to-reset input[value="points_awards"]').prop( 'checked', $(this).prop( 'checked' ) );
            $('.cmb2-id-data-to-reset input[value="points_deducts"]').prop( 'checked', $(this).prop( 'checked' ) );
        } else if( checked_option === 'rank_types' ) {
            $('.cmb2-id-data-to-reset input[value="ranks"], .cmb2-id-data-to-reset input[value="rank_requirements"]').prop( 'checked', $(this).prop( 'checked' ) );
        } else if( checked_option === 'ranks' ) {
            $('.cmb2-id-data-to-reset input[value="rank_requirements"]').prop( 'checked', $(this).prop( 'checked' ) );
        } else if( checked_option === 'earnings' ) {
            $('.cmb2-id-data-to-reset input[value="earned_points"], .cmb2-id-data-to-reset input[value="earned_achievements"], .cmb2-id-data-to-reset input[value="earned_ranks"]').prop( 'checked', $(this).prop( 'checked' ) );
        }

    });

    // ----------------------------------
    // Export Achievements, Points and Ranks Tool
    // ----------------------------------

    var to_export = [];

    // Function to handle the export process
    function gamipress_run_export_tool( type, loop ) {

        var button_element = $('#export_' + type );
        var response_element = $('#export-' + type + '-response');
        var data;

        if( loop === undefined ) {
            loop = 0;
        }

        // Disable the export button
        button_element.prop('disabled', true);

        // Check if response element exists
        if( ! response_element.length ) {
            button_element.parent().append('<span id="export-' + type + '-response" style="display: inline-block; padding: 5px 0 0 8px;"></span>');

            response_element = $('#export-' + type + '-response');
        }

        if( ! response_element.find('.spinner').length ) {
            // Show the spinner
            response_element.html('<span class="spinner is-active" style="float: none; margin: 0;"></span>');
        }

        // Setup request data per type
        switch( type ) {
            case 'achievements':
                // Achievements data
                var achievement_types = [];

                $('input[name="export_achievements_achievement_types[]"]:checked').each(function() {
                    achievement_types.push( $(this).val() );
                });

                data = {
                    action: 'gamipress_import_export_achievements_tool_export',
                    achievement_types: achievement_types,
                    user_field: $('#export_achievements_user_field').val(),
                    achievement_field: $('#export_achievements_achievement_field').val(),
                    loop: loop
                };
                break;
            case 'points':
                // Points data
                var points_types = [];

                $('input[name="export_points_points_types[]"]:checked').each(function() {
                    points_types.push( $(this).val() );
                });

                data = {
                    action: 'gamipress_import_export_points_tool_export',
                    points_types: points_types,
                    user_field: $('#export_points_user_field').val(),
                    loop: loop
                };
                break;
            case 'ranks':
                // Ranks data
                var rank_types = [];

                $('input[name="export_ranks_rank_types[]"]:checked').each(function() {
                    rank_types.push( $(this).val() );
                });

                data = {
                    action: 'gamipress_import_export_ranks_tool_export',
                    rank_types: rank_types,
                    user_field: $('#export_ranks_user_field').val(),
                    rank_field: $('#export_ranks_rank_field').val(),
                    loop: loop
                };
                break;
        }

        $.post(
            ajaxurl,
            data,
            function( response ) {

                if( response.data.items !== undefined ) {
                    // Concat received items
                    to_export = to_export.concat( response.data.items );
                }

                // Run again utility
                if( response.data.run_again !== undefined && response.data.run_again && response.success === true ) {

                    if( ! response_element.find('#export-' + type + '-response-message').length ) {
                        response_element.append('<span id="export-' + type + '-response-message" style="padding-left: 5px;"></span>');
                    }

                    response_element.find('#export-' + type + '-response-message').html( response.data.message );

                    loop++;

                    // Run again passing the next loop index
                    gamipress_run_export_tool( type, loop );

                    return;
                }

                if( response.success === false ) {
                    response_element.css({color:'#a00'});
                }

                response_element.html( ( response.data.message !== undefined ? response.data.message : response.data ) );

                // Enable the export button
                button_element.prop('disabled', false);

                if( to_export.length ) {
                    // Download the CSV with the data
                    gamipress_download_csv( to_export, 'gamipress-user-' + type + '-export' );
                }
            }
        ).fail(function() {

            response_element.html('The server has returned an internal error.');

            // Enable the export button
            button_element.prop('disabled', false);
        });

    }

    $('#export_achievements, #export_points, #export_ranks').click(function(e) {
        e.preventDefault();

        var $this = $(this);
        var type;
        var error = '';

        switch( $this.attr('id') ) {
            case 'export_achievements':
                // Achievements export
                type = 'achievements';

                // Check achievement types
                if( ! $('input[name="export_achievements_achievement_types[]"]:checked').length ) {
                    error = 'You need to choose at least 1 achievement type to export.';
                }
                break;
            case 'export_points':
                // Points export
                type = 'points';

                // Check points types
                if( ! $('input[name="export_points_points_types[]"]:checked').length ) {
                    error = 'You need to choose at least 1 points type to export.';
                }
                break;
            case 'export_ranks':
                // Ranks export
                type = 'ranks';

                // Check rank types
                if( ! $('input[name="export_ranks_rank_types[]"]:checked').length ) {
                    error = 'You need to choose at least 1 rank type to export.';
                }
                break;
        }

        // Remove error messages
        $('#export-' + type + '-warning').remove();

        // If there is any error, show it to the user
        if( error !== '' ) {
            $this.parent().prepend('<p id="export-' + type + '-warning" class="cmb2-metabox-description" style="color: #a00;">' + error + '</p>');
            return false;
        }

        // Reset the data to export
        to_export = [];

        gamipress_run_export_tool( type );

    });

    // ----------------------------------
    // Import Achievements, Points and Ranks Tool
    // ----------------------------------

    $('#import_achievements, #import_points, #import_ranks').click(function(e) {
        e.preventDefault();

        var $this = $(this);
        var type;

        switch( $this.attr('id') ) {
            case 'import_achievements':
                // Achievements import
                type = 'achievements';
                break;
            case 'import_points':
                // Points import
                type = 'points';
                break;
            case 'import_ranks':
                // Ranks import
                type = 'ranks';
                break;
        }

        // Remove error messages
        $('#import-' + type + '-warning').remove();

        // Remove old responses
        $('#import-' + type + '-response').remove();

        // Check if CSV file has been chosen
        if( $('#import_' + type + '_file')[0].files[0] === undefined ) {
            $this.parent().prepend('<p id="import-' + type + '-warning" class="cmb2-metabox-description" style="color: #a00;">You need to choose a CSV file to import.</p>');
            return false;
        }

        // Setup the form data to send
        var form_data = new FormData();
        form_data.append( 'action', 'gamipress_import_export_' + type + '_tool_import' );
        form_data.append( 'file', $('#import_' + type + '_file')[0].files[0] );

        // Disable the button
        $this.prop('disabled', true);

        // Show the spinner
        $this.parent().prepend('<p id="import-' + type + '-response" class="cmb2-metabox-description"><span class="spinner is-active" style="float: none;"></span></p>');

        $.ajax({
            url: ajaxurl,
            method: 'post',
            cache: false,
            contentType: false,
            processData: false,
            data: form_data,
            success: function(response) {

                if( response.success === false ) {
                    // Set a red color to the response to let user known that something is going wrong
                    $('#import-' + type + '-response').css({color:'#a00'});
                }

                // Update the response content
                $('#import-' + type + '-response').html( response.data );

                // Re-enable the button
                $this.prop('disabled', false);

            }
        });

    });

    // ----------------------------------
    // Download Achievements, Points and Ranks CSV Template
    // ----------------------------------

    $('#download_achievements_csv_template, #download_points_csv_template, #download_ranks_csv_template').click(function(e) {
        e.preventDefault();

        var type, sample_data;

        switch( $(this).attr('id') ) {
            case 'download_achievements_csv_template':
                // Achievements sample data
                type = 'achievements';
                sample_data = [
                    {
                        user: 'User (ID, username or email)',
                        achievements: 'Achievements (Comma-separated list of IDs, titles and/or slugs)',
                        notes: 'Notes (This column won\'t be processed by the tool)',
                    },
                    {
                        user: gamipress_admin_tools.user_id,
                        achievements: '1,2,3',
                        notes: 'Awarding by user ID and passing the achievements IDs',
                    },
                    {
                        user: gamipress_admin_tools.user_name,
                        achievements: 'Test Badge,Custom Quest,Super Achievement',
                        notes: 'Awarding by username and passing the achievements titles',
                    },
                    {
                        user: gamipress_admin_tools.user_email,
                        achievements: 'test-badge,custom-quest,super-achievement',
                        notes: 'Awarding by email and passing the achievements slugs',
                    },
                    {
                        user: gamipress_admin_tools.user_id,
                        achievements: '-1,-Test Badge,-test-badge',
                        notes: 'Adding a negative sign will revoke the achievements',
                    },
                ];
                break;
            case 'download_points_csv_template':
                // Points sample data
                type = 'points';
                sample_data = [
                    {
                        user: 'User (ID, username or email)',
                        points: 'Points',
                        points_type: 'Points Type (slug)',
                        log: 'Log Description (Optional)',
                        notes: 'Notes (This column won\'t be processed by the tool)',
                    },
                    {
                        user: gamipress_admin_tools.user_id,
                        points: '100',
                        points_type: 'credits',
                        log: '100 credits awarded through the user\'s ID',
                        notes: 'Awarding points by user ID',
                    },
                    {
                        user: gamipress_admin_tools.user_name,
                        points: '1000',
                        points_type: 'coins',
                        log: '1,000 coins awarded through the user\'s username',
                        notes: 'Awarding points by username',
                    },
                    {
                        user: gamipress_admin_tools.user_email,
                        points: '50',
                        points_type: 'gems',
                        log: '50 gems awarded through the user\'s email',
                        notes: 'Awarding points by email',
                    },
                    {
                        user: gamipress_admin_tools.user_email,
                        points: '-50',
                        points_type: 'gems',
                        log: '50 gems deducted through the user\'s email',
                        notes: 'Adding a negative sign will deduct the points',
                    },
                ];
                break;
            case 'download_ranks_csv_template':
                // Ranks sample data
                type = 'ranks';
                sample_data = [
                    {
                        user: 'User (ID, username or email)',
                        rank: 'Rank (ID, title or slug of rank to assign to the user)',
                        notes: 'Notes (This column won\'t be processed by the tool)',
                    },
                    {
                        user: gamipress_admin_tools.user_id,
                        rank: '1',
                        notes: 'Setting rank by user ID and passing the rank ID',
                    },
                    {
                        user: gamipress_admin_tools.user_name,
                        rank: 'Test Rank',
                        notes: 'Setting rank by username and passing the rank title',
                    },
                    {
                        user: gamipress_admin_tools.user_email,
                        rank: 'test-rank',
                        notes: 'Setting rank by email and passing the rank slug',
                    },
                    {
                        user: gamipress_admin_tools.user_id,
                        rank: '-1',
                        notes: 'Adding a negative sign will revoke the rank to the user and will try to assign the previous one (following the priority order)',
                    },
                ];
                break;
        }

        if( Array.isArray( sample_data ) ) {
            gamipress_download_csv( sample_data, 'gamipress-' + type + '-csv-template' );
        }

    });

    // ----------------------------------
    // Export Setup Tool
    // ----------------------------------

    $('.cmb2-id-export-setup-options').on('change', 'input', function() {

        $('#export-setup-warning').remove();

        var checked_option = $(this).val();
        var type = '';

        if( checked_option === 'all-points-types' ) {

            $('.cmb2-id-export-setup-options input[value$="-points-type"]').prop( 'checked', $(this).prop( 'checked' ) );
            $('.cmb2-id-export-setup-options input[value$="-points-awards"]').prop( 'checked', $(this).prop( 'checked' ) );
            $('.cmb2-id-export-setup-options input[value$="-points-deducts"]').prop( 'checked', $(this).prop( 'checked' ) );

        } else if( checked_option.endsWith( '-points-type' ) ) {

            type = checked_option.replace( '-points-type', '' );
            $('.cmb2-id-export-setup-options input[value="' + type + '-points-awards"]').prop( 'checked', $(this).prop( 'checked' ) );
            $('.cmb2-id-export-setup-options input[value="' + type + '-points-deducts"]').prop( 'checked', $(this).prop( 'checked' ) );

        } else if( checked_option === 'all-achievement-types' ) {

            $('.cmb2-id-export-setup-options input[value$="-achievement-type"]').prop( 'checked', $(this).prop( 'checked' ) );
            $('.cmb2-id-export-setup-options input[value$="-achievements"]').prop( 'checked', $(this).prop( 'checked' ) );
            $('.cmb2-id-export-setup-options input[value$="-steps"]').prop( 'checked', $(this).prop( 'checked' ) );

        } else if( checked_option.endsWith( '-achievement-type' ) ) {

            type = checked_option.replace( '-achievement-type', '' );
            $('.cmb2-id-export-setup-options input[value="' + type + '-achievements"]').prop( 'checked', $(this).prop( 'checked' ) );
            $('.cmb2-id-export-setup-options input[value="' + type + '-steps"]').prop( 'checked', $(this).prop( 'checked' ) );

        } else if( checked_option.endsWith( '-achievements' ) ) {

            type = checked_option.replace( '-achievements', '' );
            $('.cmb2-id-export-setup-options input[value="' + type + '-steps"]').prop( 'checked', $(this).prop( 'checked' ) );

        } else if( checked_option === 'all-rank-types' ) {

            $('.cmb2-id-export-setup-options input[value$="-rank-type"]').prop( 'checked', $(this).prop( 'checked' ) );
            $('.cmb2-id-export-setup-options input[value$="-ranks"]').prop( 'checked', $(this).prop( 'checked' ) );
            $('.cmb2-id-export-setup-options input[value$="-rank-requirements"]').prop( 'checked', $(this).prop( 'checked' ) );

        } else if( checked_option.endsWith( '-rank-type' ) ) {

            type = checked_option.replace( '-rank-type', '' );
            $('.cmb2-id-export-setup-options input[value="' + type + '-ranks"]').prop( 'checked', $(this).prop( 'checked' ) );
            $('.cmb2-id-export-setup-options input[value="' + type + '-rank-requirements"]').prop( 'checked', $(this).prop( 'checked' ) );

        } else if( checked_option.endsWith( '-ranks' ) ) {

            type = checked_option.replace( '-ranks', '' );
            $('.cmb2-id-export-setup-options input[value="' + type + '-rank-requirements"]').prop( 'checked', $(this).prop( 'checked' ) );
        }

    });

    $("#export_setup").click(function(e) {
        e.preventDefault();

        $('#export-setup-warning').remove();

        var checked_options = $('.cmb2-id-export-setup-options input:checked');

        if( checked_options.length === 0 ) {
            $(this).parent().prepend('<p id="export-setup-warning" class="cmb2-metabox-description" style="color: #a00;">You need to choose at least one option to export.</p>');
            return;
        }

        $('.export-setup-button').prop('disabled', true);

        $('.export-setup-button').parent().parent().prepend('<span id="export-setup-response"><span class="spinner is-active" style="float: none;"></span></span>');

        var items = [];

        $('.cmb2-id-export-setup-options input:checked').each(function() {
            items.push($(this).val());
        });

        $.post(
            ajaxurl,
            {
                action: 'gamipress_export_setup_tool',
                items: items
            },
            function( response ) {

                if( response.success === false ) {
                    $('#export-setup-response').css({color:'#a00'});
                }

                $('#export-setup-response').html( ( response.data.message !== undefined ? response.data.message : response.data ) );

                if( response.success === true ) {

                    gamipress_download_file( response.data.setup, 'setup-export', 'txt', 'text/plain' );

                    setTimeout(function() {
                        $('#export-setup-response').remove();
                    }, 5000);
                }

                $('.export-setup-button').prop('disabled', false);
            }
        );

    });

    // ----------------------------------
    // Import Setup Tool
    // ----------------------------------

    $('#import_setup').click(function(e) {
        e.preventDefault();

        $('#import-setup-warning').remove();

        if( $('#import_setup_file')[0].files[0] === undefined ) {
            $(this).parent().prepend('<p id="import-setup-warning" class="cmb2-metabox-description" style="color: #a00;">You need to choose a configuration file to import.</p>');
            return false;
        }

        var $this = $(this);
        var form_data = new FormData();
        form_data.append( 'action', 'gamipress_import_setup_tool' );
        form_data.append( 'file', $('#import_setup_file')[0].files[0] );

        // Disable the button
        $this.prop('disabled', true);

        // Show the spinner
        $this.parent().append('<span id="import-setup-response"><span class="spinner is-active" style="float: none;"></span></span>');

        $.ajax({
            url: ajaxurl,
            method: 'post',
            cache: false,
            contentType: false,
            processData: false,
            data: form_data,
            success: function(response) {

                if( response.success === false ) {
                    $('#import-setup-response').css({color:'#a00'});
                }

                $('#import-setup-response').html(response.data);

                if( response.success === true ) {

                    setTimeout(function() {
                        $('#import-setup-response').remove();
                    }, 5000);
                }

                $this.prop('disabled', false);

            }
        });
    });

    // ----------------------------------
    // Import Settings Tool
    // ----------------------------------

    $('#import_settings').click(function(e) {
        e.preventDefault();

        $('#import-settings-warning').remove();

        if( $('#import_settings_file')[0].files[0] === undefined ) {
            $(this).parent().prepend('<p id="import-settings-warning" class="cmb2-metabox-description" style="color: #a00;">You need to choose a configuration file to import.</p>');
            return false;
        }

        var $this = $(this);
        var form_data = new FormData();
        form_data.append( 'action', 'gamipress_import_settings_tool' );
        form_data.append( 'file', $('#import_settings_file')[0].files[0] );

        // Disable the button
        $this.prop('disabled', true);

        // Show the spinner
        $this.parent().append('<span id="import-settings-response"><span class="spinner is-active" style="float: none;"></span></span>');

        $.ajax({
            url: ajaxurl,
            method: 'post',
            cache: false,
            contentType: false,
            processData: false,
            data: form_data,
            success: function(response) {

                if( response.success === false ) {
                    $('#import-settings-response').css({color:'#a00'});
                }

                $('#import-settings-response').html(response.data);

                if( response.success === true ) {

                    setTimeout(function() {
                        $('#import-settings-response').remove();
                    }, 5000);
                }

                $this.prop('disabled', false);

            }
        });

    });

    // ----------------------------------
    // Recount Activity Tool
    // ----------------------------------

    function gamipress_run_recount_activity_tool( loop ) {

        if( loop === undefined ) {
            loop = 0;
        }

        var button = $("#recount_activity");
        var activity = $('#activity_to_recount').val();

        $.post(
            ajaxurl,
            {
                action: 'gamipress_recount_activity_tool',
                activity: activity,
                loop: loop // Used on run again utility to let know to the tool in which loop we are now
            },
            function( response ) {

                // Run again utility
                if( response.data.run_again !== undefined && response.data.run_again && response.success === true ) {

                    var running_selector = '#recount-activity-response #running-' + activity;

                    if( ! $(running_selector).length ) {
                        $('#recount-activity-response').append( '<span id="running-' + activity + '"></span>' );
                    }

                    $(running_selector).html( response.data.message );

                    loop++;

                    // Run again passing the next loop index
                    gamipress_run_recount_activity_tool( loop );

                    return;
                }

                $('#recount-activity-notice').remove();

                if( response.success === false ) {
                    $('#recount-activity-response').css({color:'#a00'});
                }

                $('#recount-activity-response').html(response.data);

                if( response.success === true ) {

                    setTimeout(function() {
                        $('#recount-activity-response').remove();
                    }, 5000);
                }

                // Enable the button and the activity select
                button.prop('disabled', false);
                $('#activity_to_recount').prop('disabled', false);
            }
        ).fail(function() {

            $('#recount-activity-response').html('The server has returned an internal error.');

            setTimeout(function() {
                $('#recount-activity-notice').remove();
                $('#recount-activity-response').remove();
            }, 5000);

            // Enable the button and the activity select
            button.prop('disabled', false);
            $('#activity_to_recount').prop('disabled', false);
        });
    }

    $("#recount_activity").click(function(e) {
        e.preventDefault();

        $('#recount-activity-warning').remove();

        if( $('#activity_to_recount').val() === '' ) {
            $(this).parent().prepend('<p id="recount-activity-warning" class="cmb2-metabox-description" style="color: #a00;">You need to choose an activity to recount.</p>');
            return false;
        }

        var $this = $(this);

        // Disable the button and the activity select
        $this.prop('disabled', true);
        $('#activity_to_recount').prop('disabled', true);

        // Show a notice to let know to the user that process could take a while
        $this.parent().prepend('<p id="recount-activity-notice" class="cmb2-metabox-description">' + gamipress_admin_tools.recount_activity_notice + '</p>');

        if( ! $('#recount-activity-response').length ) {
            $this.parent().append('<span id="recount-activity-response"></span>');
        }

        // Show the spinner
        $('#recount-activity-response').html('<span class="spinner is-active" style="float: none;"></span>');

        // Make the ajax request
        gamipress_run_recount_activity_tool();
    });

    // ----------------------------------
    // Bulk Awards/Revokes Tool
    // ----------------------------------

    // Award to all users
    $('#bulk-awards, #bulk-revokes').on('change',
        '#bulk_award_points_all_users, #bulk_award_achievements_all_users, #bulk_award_rank_all_users, '
        + '#bulk_revoke_points_all_users, #bulk_revoke_achievements_all_users, #bulk_revoke_rank_all_users'
        , function() {

        var users_target = $('#' + $(this).attr('id').replace('_all', '')).closest('.cmb-row');
        var roles_target = $('#' + $(this).attr('id').replace('all_users', 'roles')).closest('.cmb-row');

        if( $(this).prop('checked') ) {
            users_target.slideUp(250).addClass('cmb2-tab-ignore');
            roles_target.slideUp(250).addClass('cmb2-tab-ignore');
        } else {
            users_target.slideDown(250).removeClass('cmb2-tab-ignore');
            roles_target.slideDown(250).removeClass('cmb2-tab-ignore');
        }

    });

    function gamipress_run_bulk_tool( button, loop ) {

        // Initialize loop
        if( loop === undefined )
            loop = 0;

        var response_id = button.attr('id').replace('_button', '_response');
        var active_tab = button.closest('.cmb-tabs-wrap').find('.cmb-tab.active');
        var action = ( button.attr('id').indexOf('bulk_award_') !== -1 ? 'bulk_award' : 'bulk_revoke' );
        var data;

        if( action === 'bulk_award' ) {
            data = {
                action: 'gamipress_bulk_awards_tool',
                bulk_award: button.attr('id').replace('bulk_award_', '').replace('_button', ''),
                loop: loop
            };
        } else if( action === 'bulk_revoke' ) {
            data = {
                action: 'gamipress_bulk_revokes_tool',
                bulk_revoke: button.attr('id').replace('bulk_revoke_', '').replace('_button', ''),
                loop: loop
            };
        }


        // Loop all fields to build the request data
        $(active_tab.data('fields')).find('input, select, textarea').each(function() {

            if( $(this).attr('type') === 'checkbox' ) {
                // Checkboxes are sent just when checked
                if( $(this).prop('checked') ) {
                    data[$(this).attr('name')] = $(this).val();
                }
            } else {
                data[$(this).attr('name')] = $(this).val();
            }

        });

        // Disable the button
        button.prop('disabled', true);

        if( ! $('#' + response_id).length ) {
            button.parent().append('<span id="' + response_id + '" style="display: inline-block; padding: 5px 0 0 8px;"></span>');
        }

        if( ! $('#' + response_id).find('.spinner').length ) {
            // Show the spinner
            $('#' + response_id).html('<span class="spinner is-active" style="float: none; margin: 0;"></span>');
        }

        $.post(
            ajaxurl,
            data,
            function( response ) {

                // Run again utility
                if( response.data.run_again !== undefined && response.data.run_again && response.success === true ) {

                    if( ! $('#' + response_id).find('#' + response_id + '-message').length ) {
                        $('#' + response_id).append('<span id="' + response_id + '-message" style="padding-left: 5px;"></span>');
                    }

                    $('#' + response_id).find('#' + response_id + '-message').html(response.data.message);

                    loop++;

                    // Run again passing the next loop index
                    gamipress_run_bulk_tool( button, loop );

                    return;
                }

                if( response.success === false ) {
                    $('#' + response_id).css({color:'#a00'});
                }

                $('#' + response_id).html(response.data);

                if( response.success !== false ) {
                    loop++;
                }

                // Enable the button
                button.prop('disabled', false);
            }
        ).fail(function() {

            $('#' + response_id).html('The server has returned an internal error.');

            // Enable the button
            button.prop('disabled', false);
        });

    }

    $('#bulk_award_points_button, #bulk_award_achievements_button, #bulk_award_rank_button, '
        + '#bulk_revoke_points_button, #bulk_revoke_achievements_button, #bulk_revoke_rank_button').click(function(e) {
        e.preventDefault();

        gamipress_run_bulk_tool( $(this) );
    });

})( jQuery );