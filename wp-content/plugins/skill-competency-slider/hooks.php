<?php
function create_skill_competency_ratings_table() {
    global $table_prefix, $wpdb;

    $tblname = 'skill_competency_ratings';
    $wp_track_table = $table_prefix . "$tblname";

    #Check to see if the table exists already, if not, then create it
    if($wpdb->get_var( "show tables like '$wp_track_table'" ) != $wp_track_table) {
        $sql = "CREATE TABLE `$wp_track_table` ( ";
        $sql .= "  `id` int(11) NOT NULL auto_increment, ";
        $sql .= "  `skill_id` int(128) NOT NULL, ";
        $sql .= "  `user_id` int(128) NOT NULL, ";
        $sql .= "  `percentage` DECIMAL(8,4) NOT NULL, ";
        $sql .= "  `rgb` VARCHAR(100) NOT NULL, ";
        $sql .= "  `date` DATETIME DEFAULT NOW(), ";
        $sql .= "  PRIMARY KEY `id` (`id`) ";
        $sql .= ") AUTO_INCREMENT=1 ; ";
        require_once(ABSPATH . '/wp-admin/includes/upgrade.php' );
        dbDelta($sql);
    }
}

register_activation_hook(SKILL_COMPETENCY_PLUGIN_FILE_URL, 'create_skill_competency_ratings_table' );
?>