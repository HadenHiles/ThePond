<?php

/************
 * Elementor
 ************/
 
 add_action( 'elementor/widget/before_render_content', 'ld_course_grid_elementor_content' );	
 function ld_course_grid_elementor_content( $element ) {
 	$data = $element->get_raw_data();

 	if ( isset( $data['settings']['shortcode'] ) ) {
 		$key = 'shortcode';
 	} elseif( isset( $data['settings']['editor'] ) ) {
 		$key = 'editor';
 	}

 	if ( ! empty( $key ) ) {
 		$new_shortcode = preg_replace( '/(.*\[ld_\w+_list.*)/', '<div id="ld_course_list">$1</div>', $data['settings'][ $key ] );

		if ( preg_match( '/(.*\[ld_\w+_list.*)/', $data['settings'][ $key ] ) ) {
			global $ld_course_grid_assets_needed;
			$ld_course_grid_assets_needed = true;
		}

		$element->set_settings( $key, $new_shortcode );
 	}
 }