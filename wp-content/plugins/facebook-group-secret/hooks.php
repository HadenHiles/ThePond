<?php
function create_facebook_phrases_table() {
    global $table_prefix, $wpdb;
    $tblname = 'facebook_group_secret_phrases';
    $wp_track_table = $table_prefix . "$tblname";

    #Check to see if the table exists already, if not, then create it
    if ($wpdb->get_var("show tables like '$wp_track_table'") != $wp_track_table) {
        $sql = "CREATE TABLE `$wp_track_table` ( ";
        $sql .= "  `id` int(11) NOT NULL auto_increment, ";
        $sql .= "  `user_id` int(128) NOT NULL, ";
        $sql .= "  `phrase` VARCHAR(255) NOT NULL, ";
        $sql .= "  `timestamp` DATETIME DEFAULT NOW(), ";
        $sql .= "  `used` BOOLEAN DEFAULT FALSE, ";
        $sql .= "  PRIMARY KEY `id` (`id`) ";
        $sql .= ") AUTO_INCREMENT=1 ; ";
        require_once(ABSPATH . '/wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

register_activation_hook(FB_GROUP_SECRET_PLUGIN_FILE_URL, 'create_facebook_phrases_table');

function fb_group_generate_phrase() {
    try {
        $user_id = get_current_user_id();

        global $wpdb;
        $table_name = $wpdb->prefix . "facebook_group_secret_phrases";

        $query =    "SELECT id, phrase, `timestamp`, used FROM $table_name
                    WHERE `user_id` = %d
                    AND used IS FALSE
                    ORDER BY `timestamp` DESC
                    LIMIT 1";

        $results = $wpdb->get_results($wpdb->prepare($query, $user_id));

        var_dump($results);
        exit();

        if (sizeOf($results) == 0) {
            /* Default the phrase */
            $bytes = random_bytes(4); // 4 random characters
            $rand = bin2hex($bytes); // Ensure alphanumeric
            $phrase = $rand;
            /* Now lets generate the bomb ass phrase */
            $jsonWords = file_get_contents('./words.json'); // Read JSON file
            $jsonWords = json_decode($jsonWords, true); // Decode JSON
            $animals = $jsonWords['animals'];
            $things = $jsonWords['things'];
            $slang = $jsonWords['slang'];

            $wpdb->insert(
                $table_name,
                array(
                    'id' => null,
                    'user_id' => $user_id,
                    'phrase' => $phrase
                ),
                array(
                    '%d',
                    '%d',
                    '%f'
                )
            );
        }
    } catch (Exception $e) {
    }
}

/* When the user logs in check if they need a secret facebook group phrase */
add_action('facebook_group_generate_phrase', 'fb_group_generate_phrase', 10, 2);

/* Memberpress account navigation tab */
function mepr_add_facebook_tab($action) {
    $facebookTabOpen = (isset($_GET['action']) && $_GET['action'] == 'facebook') ? 'mepr-active-nav-tab' : '';
?>
    <span class="mepr-nav-item facebook <?php echo $facebookTabOpen; ?>">
        <a href="/account/?action=facebook">Facebook</a>
    </span>
    <?php
}
add_action('mepr_account_nav', 'mepr_add_facebook_tab');

/* Memberpress account navigation content */
function mepr_add_facebook_tab_content($action) {
    if ($action == 'facebook') {
        do_action("facebook_group_generate_phrase");
        ?>
        <div id="custom-support-form">
            <form action="" method="post">
                <label for="email">Email:</label><br />
                <input type="text" name="email" id="email" />

                <br /><br />

                <label for="phrase">Phrase:</label><br />
                <input type="text" name="phrase" id="phrase" />
            </form>
        </div>
        <?php
    }
}
add_action('mepr_account_nav_content', 'mepr_add_facebook_tab_content');
