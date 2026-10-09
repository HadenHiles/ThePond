<?php
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
  $useFbAvatar = !empty($useFbAvatar[0]);
  $fbAvatar = get_user_meta(get_current_user_id(), 'avatar_url');
  if ($action == 'avatar') {
    if ($useFbAvatar && !empty($fbAvatar[0])) {
  ?>
      <img alt='avatar' src='<?= $fbAvatar[0] ?>' height='100' width='100' />
      <a href="<?php echo esc_url(wp_nonce_url(home_url('/account/?action=custom_avatar'), 'pond-avatar-preference')); ?>" class="BTN">Change</a>
      <?php
    } else {
      echo do_shortcode('[avatar_upload]');
      if (!empty($fbAvatar[0])) {
      ?>
        <br />
        <br />
        <a href="<?php echo esc_url(wp_nonce_url(home_url('/account/?action=custom_avatar'), 'pond-avatar-preference')); ?>" class="BTN">Use Social Avatar</a>
<?php
      }
    }
  }
}
add_action('mepr_account_nav_content', 'mepr_add_tabs_content');

add_action('template_redirect', 'thepond_portal_avatar_preference');
function thepond_portal_avatar_preference() {
  if (!is_user_logged_in() || !is_page('account') || !isset($_GET['action']) || $_GET['action'] !== 'custom_avatar') {
    return;
  }
  check_admin_referer('pond-avatar-preference');
  $use_avatar = get_user_meta(get_current_user_id(), 'use_firebase_avatar', true);
  update_user_meta(get_current_user_id(), 'use_firebase_avatar', !$use_avatar);
  wp_safe_redirect(home_url('/account/?action=avatar'));
  exit;
}

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
  $u = get_user_by('email', $user['email']);
  if (!$u) {
    return;
  }
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
    if (!empty($useFbAvatar[0]) && !empty($fbAvatar[0])) {
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
