<?php
if ( ( class_exists( 'LearnDash_Shortcodes_Section' ) ) && ( !class_exists( 'LearnDash_Shortcodes_Section_ld_propanel' ) ) ) {
	class LearnDash_Shortcodes_Section_ld_propanel extends LearnDash_Shortcodes_Section {

		function __construct( $fields_args = array() ) {
			$this->fields_args = $fields_args;

			$this->shortcodes_section_key 			= 	'ld_propanel';
			$this->shortcodes_section_title 		= 	__( 'ProPanel', 'ld_propanel' );
			$this->shortcodes_section_type			=	1;
			$this->shortcodes_section_description	=	__( 'This shortcode displays widgets from ProPanel.', 'ld_propanel' );
			
			parent::__construct(); 
		}
		
		function init_shortcodes_section_fields() {
			$this->shortcodes_option_fields = array(
				'widget' => array(
					'id'			=>	$this->shortcodes_section_key . '_widget',
					'name'  		=> 	'widget', 
					'type'  		=> 	'select',
					'label' 		=> 	__( 'Widget', 'ld_propanel' ),
					'help_text'		=>	__( 'Select which ProPanel widget to dislay', 'ld_propanel' ),
					'value' 		=> 	'',
					'options'		=>	array(
											'link'				=>	__('Link to ProPanel Full Page', 'ld_propanel' ),
											'overview'			=>	__('Overview Widget', 'ld_propanel' ),
											'filtering'			=>	__('Filtering Widget', 'ld_propanel' ),
											'reporting'			=>	__('Reporting Widget', 'ld_propanel' ),
											'activity'			=>	__('Activity Widget', 'ld_propanel' ),
											'progress_chart'	=>	__('Progress Chart Widget', 'ld_propanel' ),
										)
				),
				'filter_groups' => array(
					'id'			=>	$this->shortcodes_section_key . '_filter_groups',
					'name'  		=> 	'filter_groups', 
					'type'  		=> 	'number',
					'label' 		=> 	__( 'Filter Groups', 'learndash'),
					'help_text'		=>	__( 'Filter Widget by Group ID', 'ld_propanel' ),
					'value' 		=> 	'',
				),

				'filter_courses' => array(
					'id'			=>	$this->shortcodes_section_key . '_filter_courses',
					'name'  		=> 	'filter_courses', 
					'type'  		=> 	'number',
					'label' 		=> 	__( 'Filter Courses', 'learndash'),
					'help_text'		=>	__( 'Filter Widget by Course ID', 'ld_propanel' ),
					'value' 		=> 	'',
				),

				'filter_users' => array(
					'id'			=>	$this->shortcodes_section_key . '_filter_users',
					'name'  		=> 	'filter_users', 
					'type'  		=> 	'number',
					'label' 		=> 	__( 'Filter Users', 'learndash'),
					'help_text'		=>	__( 'Filter Widget by User ID', 'ld_propanel' ),
					'value' 		=> 	'',
				),

				'filter_status' => array(
					'id'			=>	$this->shortcodes_section_key . '_filter_status',
					'name'  		=> 	'filter_status', 
					'type'  		=> 	'select',
					'label' 		=> 	__( 'Filter Course Status', 'learndash'),
					'help_text'		=>	__( 'Filter Widget by Course Status', 'ld_propanel' ),
					'value' 		=> 	'',
					//'attrs'			=>	array( 'multiple' => 'multiple' ),
					'options'		=>	array(
											''				=>	__('All Statuses', 'ld_propanel' ), 
											'not-started'	=>	__('Not Started', 'ld_propanel' ), 
											'in-progress'	=>	__('In Progress', 'ld_propanel' ), 
											'completed'		=>	__('Completed', 'ld_propanel' )
										)
					
				),

				'display_chart' => array(
					'id'			=>	$this->shortcodes_section_key . '_display_chart',
					'name'  		=> 	'display_chart', 
					'type'  		=> 	'select',
					'label' 		=> 	__( 'Display Chart', 'learndash'),
					'help_text'		=>	__( 'Display Chart Orientation', 'ld_propanel' ),
					'value' 		=> 	'',
					'options'		=>	array(
											''				=>	__('Stacked (default)', 'ld_propanel' ), 
											'side-by-side'	=>	__('Side by Side', 'ld_propanel' ), 
										)
					
				),
				'per_page' => array(
					'id'			=>	$this->shortcodes_section_key . '_per_page',
					'name'  		=> 	'per_page', 
					'type'  		=> 	'number',
					'label' 		=> 	__( 'Per Page', 'learndash'),
					'help_text'		=>	__( 'Pagination for Widget output', 'ld_propanel' ),
					'value' 		=> 	'',
				),
			);
		
			$this->shortcodes_option_fields = apply_filters( 'learndash_settings_fields', $this->shortcodes_option_fields, $this->shortcodes_section_key );
			
			parent::init_shortcodes_section_fields();
		}
		
		function show_shortcodes_section_footer_extra() {
			?>
			<script>
				jQuery(document).ready(function() {
					if ( jQuery( 'form#learndash_shortcodes_form_ld_propanel select#ld_propanel_widget' ).length) {
						jQuery( 'form#learndash_shortcodes_form_ld_propanel select#ld_propanel_widget').change( function() {
							var selected_widget = jQuery(this).val();
							
							jQuery( 'form#learndash_shortcodes_form_ld_propanel input#ld_propanel_filter_groups').val('');
							jQuery( 'form#learndash_shortcodes_form_ld_propanel input#ld_propanel_filter_courses').val('');
							jQuery( 'form#learndash_shortcodes_form_ld_propanel input#ld_propanel_filter_users').val('');
							jQuery( 'form#learndash_shortcodes_form_ld_propanel select#ld_propanel_filter_status').val('');
							jQuery( 'form#learndash_shortcodes_form_ld_propanel select#ld_propanel_display_chart').val('');
							jQuery( 'form#learndash_shortcodes_form_ld_propanel input#ld_propanel_per_page').val('');

							if ( ( selected_widget == 'reporting' ) || ( selected_widget == 'activity' ) || ( selected_widget == 'progress_chart' ) ) {
								jQuery( 'form#learndash_shortcodes_form_ld_propanel #ld_propanel_filter_groups_field').slideDown();
								jQuery( 'form#learndash_shortcodes_form_ld_propanel #ld_propanel_filter_courses_field').slideDown();
								jQuery( 'form#learndash_shortcodes_form_ld_propanel #ld_propanel_filter_users_field').slideDown();

								if ( selected_widget == 'progress_chart' ) {
									jQuery( 'form#learndash_shortcodes_form_ld_propanel #ld_propanel_display_chart_field').slideDown();
									jQuery( 'form#learndash_shortcodes_form_ld_propanel #ld_propanel_filter_status_field').hide();
									jQuery( 'form#learndash_shortcodes_form_ld_propanel #ld_propanel_per_page_field').hide();
								} else {
									jQuery( 'form#learndash_shortcodes_form_ld_propanel #ld_propanel_display_chart_field').hide();
									jQuery( 'form#learndash_shortcodes_form_ld_propanel #ld_propanel_filter_status_field').slideDown();
									jQuery( 'form#learndash_shortcodes_form_ld_propanel #ld_propanel_per_page_field').slideDown();
								}
								
								if ( ( selected_widget == 'progress_chart' ) || ( selected_widget == 'reporting' ) ) {
									if ( !jQuery( 'form#learndash_shortcodes_form_ld_propanel p#required-message').length ) {
										jQuery( '<p id="required-message" style="color: red;"><?php _e('When using the "reporting" or "progress_chart" widget shortcodes. A selection from the Group, Course or User filters is required unless also using the "filtering" widget shortcode on the same page.', 'ld_propanel') ?></p>' ).insertBefore( 'form#learndash_shortcodes_form_ld_propanel .learndash_shortcodes_section' );
									}
									jQuery( 'form#learndash_shortcodes_form_ld_propanel p#required-message').show();
								} else {
									if ( jQuery( 'form#learndash_shortcodes_form_ld_propanel p#required-message').length ) {
										jQuery( 'form#learndash_shortcodes_form_ld_propanel p#required-message').hide();
									}
								}
								
							} else {
								jQuery( 'form#learndash_shortcodes_form_ld_propanel #ld_propanel_filter_groups_field').hide();
								jQuery( 'form#learndash_shortcodes_form_ld_propanel #ld_propanel_filter_courses_field').hide();
								jQuery( 'form#learndash_shortcodes_form_ld_propanel #ld_propanel_filter_users_field').hide();
								jQuery( 'form#learndash_shortcodes_form_ld_propanel #ld_propanel_filter_status_field').hide();
								jQuery( 'form#learndash_shortcodes_form_ld_propanel #ld_propanel_display_chart_field').hide();
								jQuery( 'form#learndash_shortcodes_form_ld_propanel #ld_propanel_per_page_field').hide();

								if ( jQuery( 'form#learndash_shortcodes_form_ld_propanel p#required-message').length ) {
									jQuery( 'form#learndash_shortcodes_form_ld_propanel p#required-message').hide();
								}
							}
						});		
						jQuery( 'form#learndash_shortcodes_form_ld_propanel select#ld_propanel_widget').change();
					} 
				});
			</script>
			<?php
		}
	}
}

