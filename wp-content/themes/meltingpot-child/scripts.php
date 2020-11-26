<?php
wp_enqueue_scripts('wp-utils');

wp_enqueue_script('jquery', get_template_directory_uri() . '/js/vendor/jquery.min.js', array(), null, true);
wp_enqueue_style('datatables', get_template_directory_uri() . '/bootstrap-4.5.0/DataTables/datatables.min.css');
wp_enqueue_script('datatables', get_template_directory_uri() . '/bootstrap-4.5.0/DataTables/datatables.min.js', array(), null, true);
wp_enqueue_script('popper', get_template_directory_uri() . '/bootstrap-4.5.0/dist/js/popper.min.js', array(), null, true);
wp_enqueue_style('bootstrap', get_template_directory_uri() . '/bootstrap-4.5.0/dist/css/bootstrap.min.css');
wp_enqueue_script('bootstrap', get_template_directory_uri() . '/bootstrap-4.5.0/dist/js/bootstrap.min.js', array('jquery'), null, true);
wp_enqueue_script('datatables-responsive', get_template_directory_uri() . '/bootstrap-4.5.0/DataTables/Responsive-2.2.5/js/dataTables.responsive.min.js', array(), null, true);
wp_enqueue_script('datatables-fixedheader', get_template_directory_uri() . '/bootstrap-4.5.0/DataTables/FixedHeader-3.1.7/js/dataTables.fixedHeader.min.js', array(), null, true);
wp_enqueue_script('filterizr', get_template_directory_uri() . '/js/jquery.filterizr.min.js', array(), null, true);

wp_enqueue_script('main', get_template_directory_uri() . '/js/main.js', array('jquery'), null, true);

// add_action('wp_enqueue_scripts', 'enqueue_firebase');
function enqueue_firebase() {
    if (!wp_script_is( '/wp-content/plugins/miniorange-firebase-authentication-enterprise/admin/js/firebase-app.js', 'enqueued')) {
        wp_enqueue_script('mo_firebase_app_script', '/wp-content/plugins/miniorange-firebase-authentication-enterprise/admin/js/firebase-app.js', null, null, true);
    }
    if (!wp_script_is( '/wp-content/plugins/miniorange-firebase-authentication-enterprise/admin/js/firebase-auth.js', 'enqueued')) {
        wp_enqueue_script('mo_firebase_auth_script', '/wp-content/plugins/miniorange-firebase-authentication-enterprise/admin/js/firebase-auth.js', array('mo_firebase_app_script'), null, true);
    }
    if (!wp_script_is( '/wp-content/plugins/miniorange-firebase-authentication-enterprise/admin/js/firebase-firestore.js', 'enqueued')) {
        wp_enqueue_script('mo_firebase_firestore_script', '/wp-content/plugins/miniorange-firebase-authentication-enterprise/admin/js/firebase-firestore.js', array('mo_firebase_app_script'), null, true);
    }
    
    // Should only enqueue this on pages where it's needed
    wp_enqueue_script('firebase_hth', '/wp-content/themes/meltingpot-child/js/firebase.js', array('jquery', 'mo_firebase_app_script', 'mo_firebase_auth_script', 'mo_firebase_firestore_script'), null, true);
}
