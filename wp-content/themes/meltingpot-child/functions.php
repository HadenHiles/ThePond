<?php
// Set to allow cors from cdn
header("Access-Control-Allow-Origin: *");

/**
 * Fix sql strict mode
 */
add_action('init', 'mysql_set_sql_mode_traditional', -1);

function mysql_set_sql_mode_traditional() {
  global $wpdb;
  $wpdb->query("SET SESSION sql_mode = ''");
}

/**
 * Manually trigger supervisord hook running on thepond DO droplet any time that elementor data is updated or a buddypress avater is uploaded
 * - this is required so that styles will be applied
 * TODO: figure out a way to automatically invalidate the cache of modified files
 */
add_action('elementor/editor/after_save', 'update_the_pond_cdn');
add_action('bp_before_profile_edit_cover_image', 'update_the_pond_cdn');
add_action('bp_after_profile_avatar_upload_content', 'update_the_pond_cdn');
function update_the_pond_cdn() {
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

add_action('wp_enqueue_scripts', 'child_theme_enqueue_styles');
function child_theme_enqueue_styles() {

  $parent_style = 'parent-style';

  wp_enqueue_style($parent_style, get_template_directory_uri() . '/style.css');
  wp_enqueue_style(
    'style',
    get_stylesheet_directory_uri() . '/style.css',
    array($parent_style),
    wp_get_theme()->get('Version')
  );
}

/**
 * Join posts and postmeta tables
 *
 * http://codex.wordpress.org/Plugin_API/Filter_Reference/posts_join
 */
function cf_search_join($join) {
  global $wpdb;

  if (is_search()) {
    $join .= ' LEFT JOIN ' . $wpdb->postmeta . ' ON ' . $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
  }

  return $join;
}
add_filter('posts_join', 'cf_search_join');

function cf_search_where($where) {
  global $pagenow, $wpdb;

  if (is_search()) {
    $where = preg_replace(
      "/\(\s*" . $wpdb->posts . ".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
      "(" . $wpdb->posts . ".post_title LIKE $1) OR (" . $wpdb->postmeta . ".meta_value LIKE $1)",
      $where
    );
  }

  return $where;
}
add_filter('posts_where', 'cf_search_where');

function cf_search_distinct($where) {
  global $wpdb;

  if (is_search()) {
    return "DISTINCT";
  }

  return $where;
}
add_filter('posts_distinct', 'cf_search_distinct');

/* Memberpress account tabs */
function mepr_add_tabs($user) {
?>
  <span class="mepr-nav-item avatar">
    <!-- KEEPS THE USER ON THE ACCOUNT PAGE -->
    <a href="/account?action=avatar">Avatar</a>
  </span>
  <?php
}
add_action('mepr_account_nav', 'mepr_add_tabs');

function mepr_add_tabs_content($action) {
  $useFbAvatar = get_user_meta(get_current_user_id(), 'use_firebase_avatar');
  $useFbAvatar = $useFbAvatar[0];
  $fbAvatar = get_user_meta(get_current_user_id(), 'avatar_url');
  if ($action == 'avatar') {
    if ($useFbAvatar && !empty($fbAvatar[0])) {
  ?>
      <img alt='avatar' src='<?= $fbAvatar[0] ?>' height='100' width='100' />
      <a href="/account?action=custom_avatar" class="BTN">Change</a>
      <?php
    } else {
      echo do_shortcode('[avatar_upload]');
      if (!empty($fbAvatar[0])) {
      ?>
        <br />
        <br />
        <a href="/account?action=custom_avatar" class="BTN">Use Social Avatar</a>
<?php
      }
    }
  } else if ($action == 'custom_avatar') {
    update_user_meta(get_current_user_id(), 'use_firebase_avatar', $useFbAvatar ? false : true);
    header('Location: /account?action=avatar');
  }
}
add_action('mepr_account_nav_content', 'mepr_add_tabs_content');

function modify_profile_url($url, $user_id, $scheme) {
  // Makes the link to http://example.com/custom-profile
  $url = site_url('/account?action=avatar');
  return $url;
}
add_filter('edit_profile_url', 'modify_profile_url', 10, 3);

/**
 * Enable excerpts for LearnDash Courses/Lessons and Skills/Skill Examples
 */
function add_custom_excerpts() {
  add_post_type_support('sfwd-courses', 'excerpt');
  add_post_type_support('sfwd-lessons', 'excerpt');
  add_post_type_support('skill', 'excerpt');
  add_post_type_support('skill-examples', 'excerpt');
}
add_action('init', 'add_custom_excerpts');

// FIREBASE ACTIONS
add_action('mo_firebase_user_attributes', 'set_firebase_user_attributes', 10, 2);

function set_firebase_user_attributes($user, $user_attributes) {
  // $user : WP user belonging to Firebase jwt token
  // $user_attributes : contains response received from Firebase
  $user_id = $user->ID;

  // Code to store user attributes goes here
  add_user_meta($user_id, 'mo_firebase_jwt_data', $user_attributes);
}

/* Get the Firebase user object via MiniOrange hook */
add_action('mo_firebase_auth_get_social_user', 'get_fb_user', 100, 1);
function get_fb_user($user) {
  $u = get_user_by('login', $user['email']);
  $user_id = $u->ID;
  $currentAvatar = get_user_meta($user_id, 'avatar_url');

  if (!$currentAvatar || $currentAvatar != $user['photoURL']) {
    $useFbAvatar = get_user_meta($user_id, 'use_firebase_avatar');
    if (empty($useFbAvatar) || $useFbAvatar[0] == true) {
      // Set the user's avatar post meta
      update_user_meta($user_id, 'avatar_url', $user['photoURL']);
      update_user_meta($user_id, 'use_firebase_avatar', true);
    }
  }
}

// Use firebase photoUrl if 
function firebase_user_avatar($avatar, $id_or_email, $size, $default, $alt) {
  $user = false;

  if (is_numeric($id_or_email)) {
    $id = (int) $id_or_email;
    $user = get_user_by('id', $id);
  } elseif (is_object($id_or_email)) {
    if (!empty($id_or_email->user_id)) {
      $id = (int) $id_or_email->user_id;
      $user = get_user_by('id', $id);
    }
  } else {
    $user = get_user_by('email', $id_or_email);
  }

  if ($user && is_object($user)) {
    $fbAvatar = get_user_meta($user->data->ID, 'avatar_url');
    $useFbAvatar = get_user_meta($user->data->ID, 'use_firebase_avatar');
    if ($useFbAvatar[0] && !empty($fbAvatar[0])) {
      $avatar = $fbAvatar[0];
      $avatar = "<img alt='{$alt}' src='{$avatar}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
    }
  }

  return $avatar;
}
add_filter('get_wp_user_avatar', 'firebase_user_avatar', 1, 5);
add_filter('get_avatar', 'firebase_user_avatar', 1, 5);

/**
 * Check if email exists or not
 */
function user_email_exists() {
  $email = $_POST['email'];
  wp_send_json(
    array(
      'exists' => email_exists($email)
    ),
    200
  );
}
add_action('wp_ajax_user_email_exists', 'user_email_exists');
add_action('wp_ajax_nopriv_user_email_exists', 'user_email_exists');

/* Shortcodes */
require_once('shortcodes.php');

/* Scripts */
require_once('scripts.php');

// Taxonomies
require_once('taxonomies.php');

// ACF
require_once('acf.php');

// CPT
require_once('cpt.php');

// Classes
require_once('HthMeprUser.class.php');
