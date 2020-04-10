<?php
 
/**
 * Head Hook
 *
 * @since 1.0.0
 */
function of_head() { do_action( 'of_head' ); }

/**
 * Add default options upon activation else DB does not exist
 *
 * @since 1.0.0
 */
function of_option_setup()	
{
	global $of_options, $options_machine;
	$options_machine = new Options_Machine($of_options);
		
	if (!of_get_options())
	{
		of_save_options($options_machine->Defaults);
	}
}

/**
 * Change activation message
 *
 * @since 1.0.0
 */
function optionsframework_admin_message() { 
	
	//Tweaked the message on theme activate
	?>
    <script type="text/javascript">
    jQuery(function(){
    	
        var message = '<p>This theme comes with an <a href="<?php echo admin_url('admin.php?page=optionsframework'); ?>">options panel</a> to configure settings. This theme also supports widgets, please visit the <a href="<?php echo admin_url('widgets.php'); ?>">widgets settings page</a> to configure them.</p>';
    	jQuery('.themes-php #message2').html(message);
    
    });
    </script>
    <?php
	
}

/**
 * Get header classes
 *
 * @since 1.0.0
 */
function of_get_header_classes_array() 
{
	global $of_options;
	
	foreach ($of_options as $value) 
	{
		if ($value['type'] == 'heading')
			$hooks[] = str_replace(' ','',strtolower($value['name']));	
	}
	
	return $hooks;
}

/**
 * Get options from the database and process them with the load filter hook.
 *
 * @author Jonah Dahlquist
 * @since 1.4.0
 * @return array
 */
function of_get_options($key = null, $data = null) {
	if ($key != null) { // Get one specific value
		//$data = get_theme_mod($key, $data);
	} else { // Get all values
		$data = get_option(OPTIONS);
	}

	return $data;
}

/**
 * Save options to the database after processing them
 *
 * @param $data Options array to save
 * @author Jonah Dahlquist
 * @since 1.4.0
 * @uses update_option()
 * @return void
 */

function of_save_options($data, $key = null) {
	global $smof_data, $theme_name;
    if (empty($data)) {
        return;
    }

	$data_from_db = $data;

	if(defined('ICL_SITEPRESS_VERSION') && defined('ICL_LANGUAGE_CODE')) {
		global $sitepress;
		$languages = icl_get_languages('skip_missing=1');
		if($_SERVER['HTTP_REFERER']) {
			$parse_referer = parse_url($_SERVER['HTTP_REFERER']);
			wp_parse_str($parse_referer['query'], $parse_query);
			if( isset( $parse_query['lang'] ) && $parse_query['lang'] == 'all' ) {
				foreach($data as $posted_key => $posted_data) {
					if($data_from_db[$posted_key] != $posted_data) {
						$data[$posted_key] = $posted_data;
					}
				}
				foreach($languages as $language) {
					$language_name = '';
					if($language['language_code'] != 'all') {
						$language_name = '_'.$language['language_code'];
					}
					if( $language['language_code'] == 'en' ) {
						$language_name = '';
					}

					$options_name = $theme_name.'_options'.$language_name;
					update_option($options_name, $data);
				}
			} elseif( isset( $parse_query['lang'] ) && $parse_query['lang'] && $parse_query['lang'] != 'all' && $parse_query['lang'] != 'en' ) {
				$language_name = '_' . $parse_query['lang'];
				$options_name = $theme_name.'_options'.$language_name;
				update_option($options_name, $data);
			} elseif( isset( $_POST['wpml'] ) && $_POST['wpml'] != 'all' && $_POST['wpml'] != 'en' ) {
				$language_name = '_' . $_POST['wpml'];
				$options_name = $theme_name.'_options'.$language_name;
				update_option($options_name, $data);
			} else {
				update_option(OPTIONS, $data);
			}
		} else {
			update_option(OPTIONS, $data);
		}
	} else {
		update_option(OPTIONS, $data);
	}

}


/**
 * For use in themes
 *
 * @since forever
 */

$data = of_get_options();
$smof_data = of_get_options();
$data = $smof_data;