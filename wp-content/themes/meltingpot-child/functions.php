<?php
// Set to allow cors from cdn
header("Access-Control-Allow-Origin: *");

/**
* Fix sql strict mode
*/
add_action( 'init', 'mysql_set_sql_mode_traditional', -1);

function mysql_set_sql_mode_traditional() {
    global $wpdb;
    $wpdb->query("SET SESSION sql_mode = ''");
}

/**
* Manually trigger supervisord hook running on thepond DO droplet any time that elementor data is updated or a buddypress avater is uploaded
* - this is required so that styles will be applied
* TODO: figure out a way to automatically invalidate the cache of modified files
*/
add_action( 'elementor/editor/after_save', 'update_the_pond_cdn');
add_action( 'bp_before_profile_edit_cover_image', 'update_the_pond_cdn');
add_action( 'bp_after_profile_avatar_upload_content', 'update_the_pond_cdn');
function update_the_pond_cdn () {
  // sleep for 5 seconds
  sleep(5);

  // Push all changes in uploads folder to ThePondCDN
  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => "http://178.128.232.27:9000/hooks/updatecdn",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
  ));

  $response = curl_exec($curl);

  curl_close($curl);
}

add_action( 'wp_enqueue_scripts', 'child_theme_enqueue_styles' );
function child_theme_enqueue_styles() {

    $parent_style = 'parent-style';

    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'style',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style ),
        wp_get_theme()->get('Version')
    );
}

/**
 * Join posts and postmeta tables
 *
 * http://codex.wordpress.org/Plugin_API/Filter_Reference/posts_join
 */
function cf_search_join( $join ) {
    global $wpdb;

    if ( is_search() ) {
        $join .=' LEFT JOIN '.$wpdb->postmeta. ' ON '. $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
    }

    return $join;
}
add_filter('posts_join', 'cf_search_join' );

function cf_search_where( $where ) {
    global $pagenow, $wpdb;

    if ( is_search() ) {
        $where = preg_replace(
            "/\(\s*".$wpdb->posts.".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
            "(".$wpdb->posts.".post_title LIKE $1) OR (".$wpdb->postmeta.".meta_value LIKE $1)", $where );
    }

    return $where;
}
add_filter( 'posts_where', 'cf_search_where' );

function cf_search_distinct( $where ) {
    global $wpdb;

    if ( is_search() ) {
        return "DISTINCT";
    }

    return $where;
}
add_filter( 'posts_distinct', 'cf_search_distinct' );

/* Include shortcodes */
require_once('shortcodes.php');

/* Include scripts */
require_once('scripts.php');

// ACF field group for key lesson takeaways
if( function_exists('acf_add_local_field_group') ):
    acf_add_local_field_group(array(
        'key' => 'group_5f0349af20871',
        'title' => 'Key Lesson Takeaways',
        'fields' => array(
            array(
                'key' => 'field_5f034a0a394ce',
                'label' => 'Tips for success',
                'name' => 'tips_for_success',
                'type' => 'repeater',
                'instructions' => '3+ of the most important info/advice',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'collapsed' => 'field_5f034af4394d0',
                'min' => 0,
                'max' => 0,
                'layout' => 'block',
                'button_label' => 'Add Tip',
                'sub_fields' => array(
                    array(
                        'key' => 'field_5f034af4394d0',
                        'label' => 'Title',
                        'name' => 'title',
                        'type' => 'text',
                        'instructions' => '',
                        'required' => 1,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                        'maxlength' => '',
                    ),
                    array(
                        'key' => 'field_5f034a67394cf',
                        'label' => 'Content',
                        'name' => 'content',
                        'type' => 'wysiwyg',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'tabs' => 'all',
                        'toolbar' => 'full',
                        'media_upload' => 1,
                        'delay' => 1,
                    ),
                ),
            ),
            array(
                'key' => 'field_5f0357274bc4e',
                'label' => 'Common Mistakes',
                'name' => 'common_mistakes',
                'type' => 'repeater',
                'instructions' => '3+ of the most common mistakes',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'collapsed' => 'field_5f0357274bc4f',
                'min' => 0,
                'max' => 0,
                'layout' => 'block',
                'button_label' => 'Add Mistake',
                'sub_fields' => array(
                    array(
                        'key' => 'field_5f0357274bc4f',
                        'label' => 'Title',
                        'name' => 'title',
                        'type' => 'text',
                        'instructions' => '',
                        'required' => 1,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                        'maxlength' => '',
                    ),
                    array(
                        'key' => 'field_5f0357274bc50',
                        'label' => 'Content',
                        'name' => 'content',
                        'type' => 'wysiwyg',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'tabs' => 'all',
                        'toolbar' => 'full',
                        'media_upload' => 1,
                        'delay' => 1,
                    ),
                ),
            ),
            array(
                'key' => 'field_5f0357784bc51',
                'label' => 'Quick Tips',
                'name' => 'quick_tips',
                'type' => 'repeater',
                'instructions' => 'Any extra info or advice',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'collapsed' => 'field_5f0357784bc52',
                'min' => 0,
                'max' => 0,
                'layout' => 'block',
                'button_label' => 'Add Quick Tip',
                'sub_fields' => array(
                    array(
                        'key' => 'field_5f0357784bc52',
                        'label' => 'Title',
                        'name' => 'title',
                        'type' => 'text',
                        'instructions' => '',
                        'required' => 1,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                        'maxlength' => '',
                    ),
                    array(
                        'key' => 'field_5f0357784bc53',
                        'label' => 'Content',
                        'name' => 'content',
                        'type' => 'wysiwyg',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'tabs' => 'all',
                        'toolbar' => 'full',
                        'media_upload' => 1,
                        'delay' => 1,
                    ),
                ),
            ),
            array(
                'key' => 'field_5f035df9e89bf',
                'label' => 'What To Practice',
                'name' => 'what_to_practice',
                'type' => 'repeater',
                'instructions' => 'Any extra info or advice',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'collapsed' => 'field_you_should_be_able_to_item',
                'min' => 0,
                'max' => 0,
                'layout' => 'block',
                'button_label' => 'Add something to practice',
                'sub_fields' => array(
                    array(
                        'key' => 'field_you_should_be_able_to_item',
                        'label' => 'Title',
                        'name' => 'title',
                        'type' => 'text',
                        'instructions' => '',
                        'required' => 1,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                        'maxlength' => '',
                    ),
                    array(
                        'key' => 'field_5f035df9e89c1',
                        'label' => 'Content',
                        'name' => 'content',
                        'type' => 'wysiwyg',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'tabs' => 'all',
                        'toolbar' => 'full',
                        'media_upload' => 1,
                        'delay' => 1,
                    ),
                ),
            ),
            array(
                'key' => 'field_you_should_be_able_to',
                'label' => 'You Should Be Able To',
                'name' => 'you_should_be_able_to',
                'type' => 'repeater',
                'instructions' => 'Expected outcomes/goals of this lesson',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'collapsed' => 'field_you_should_be_able_to_title',
                'min' => 0,
                'max' => 0,
                'layout' => 'block',
                'button_label' => 'Add Outcome/Goal',
                'sub_fields' => array(
                    array(
                        'key' => 'field_you_should_be_able_to_title',
                        'label' => 'Title',
                        'name' => 'title',
                        'type' => 'text',
                        'instructions' => '',
                        'required' => 1,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                        'maxlength' => '',
                    ),
                    array(
                        'key' => 'field_you_should_be_able_to_content',
                        'label' => 'Content',
                        'name' => 'content',
                        'type' => 'wysiwyg',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'tabs' => 'all',
                        'toolbar' => 'full',
                        'media_upload' => 1,
                        'delay' => 1,
                    ),
                ),
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'sfwd-lessons',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'acf_after_title',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
    ));
endif;