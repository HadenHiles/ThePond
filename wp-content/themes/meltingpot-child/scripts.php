<?php
wp_enqueue_scripts( 'wp-utils' );

wp_enqueue_script( 'jquery', get_template_directory_uri() . '/js/vendor/jquery.min.js', array(), null, true);
wp_enqueue_style( 'datatables', get_template_directory_uri() . '/bootstrap-4.5.0/DataTables/datatables.min.css');
wp_enqueue_script( 'datatables', get_template_directory_uri() . '/bootstrap-4.5.0/DataTables/datatables.min.js', array(), null, true);
wp_enqueue_script( 'popper', get_template_directory_uri() . '/bootstrap-4.5.0/dist/js/popper.min.js', array(), null, true);
wp_enqueue_style( 'bootstrap', get_template_directory_uri() . '/bootstrap-4.5.0/dist/css/bootstrap.min.css');
wp_enqueue_script( 'bootstrap', get_template_directory_uri() . '/bootstrap-4.5.0/dist/js/bootstrap.min.js', array('jquery'), null, true);
wp_enqueue_script( 'datatables-responsive', get_template_directory_uri() . '/bootstrap-4.5.0/DataTables/Responsive-2.2.5/js/dataTables.responsive.min.js', array(), null, true);
wp_enqueue_script( 'datatables-fixedheader', get_template_directory_uri() . '/bootstrap-4.5.0/DataTables/FixedHeader-3.1.7/js/dataTables.fixedHeader.min.js', array(), null, true);
wp_enqueue_script( 'filterizr', get_template_directory_uri() . '/js/jquery.filterizr.min.js', array(), null, true);

wp_enqueue_script( 'firebase_app', '/wp-content/plugins/miniorange-firebase-authentication-enterprise/admin/js/firebase-app.js', null, null, true);
wp_enqueue_script( 'firebase_auth', '/wp-content/plugins/miniorange-firebase-authentication-enterprise/admin/js/firebase-auth.js', null, null, true);
wp_enqueue_script( 'firebase_firestore', '/wp-content/plugins/miniorange-firebase-authentication-enterprise/admin/js/firebase-firestore.js', null, null, true);
wp_enqueue_script( 'firebase_hth', '/wp-content/themes/meltingpot-child/js/firebase.js', array('jquery', 'firebase_app', 'firebase_auth', 'firebase_firestore'), null, true);
wp_enqueue_script( 'main', get_template_directory_uri() . '/js/main.js', array('jquery'), null, true);
?>
