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
        $sql .= "  `facebook_id` VARCHAR(255), ";
        $sql .= "  `owner_facebook_id` VARCHAR(255), ";
        $sql .= "  `created` DATETIME DEFAULT NOW(), ";
        $sql .= "  `used` DATETIME DEFAULT NOW(), ";
        $sql .= "  PRIMARY KEY `id` (`id`) ";
        $sql .= ") AUTO_INCREMENT=1 ; ";
        require_once(ABSPATH . '/wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

register_activation_hook(FB_GROUP_SECRET_PLUGIN_FILE_URL, 'create_facebook_phrases_table');

function fb_group_get_phrase() {
    $user_id = get_current_user_id();
    global $wpdb;
    $table_name = $wpdb->prefix . "facebook_group_secret_phrases";
    $query =    "SELECT id, `user_id`, phrase, `created` FROM $table_name
                WHERE `user_id` = %d
                ORDER BY `created` DESC
                LIMIT 1";

    $results = $wpdb->get_results($wpdb->prepare($query, $user_id));

    return $results[0];
}

function fb_group_generate_phrase() {
    $user_id = get_current_user_id();
    global $wpdb;
    $table_name = $wpdb->prefix . "facebook_group_secret_phrases";

    $query =    "SELECT id, `user_id`, phrase FROM $table_name
                WHERE `user_id` = %d
                LIMIT 1";

    $results = $wpdb->get_results($wpdb->prepare($query, $user_id));

    // Only run if the user doesn't already have a secret phrase
    if (sizeof($results) == 0) {
        /* Default the phrase just in case */
        $bytes = random_bytes(5); // Random characters
        $rand = bin2hex($bytes); // Ensure alphanumeric
        $phrase = $rand;

        $iterations = 0;
        do {
            /* Now lets generate the bomb ass phrase */
            $jsonWords = array(
                'animals' =>
                array(
                    0 => 'Anteater',
                    1 => 'Ape',
                    2 => 'Argali',
                    3 => 'Baboon',
                    4 => 'Badger',
                    5 => 'Bald Eagle',
                    6 => 'Basilisk',
                    7 => 'Bat',
                    8 => 'Bighorn',
                    9 => 'Blue Crab',
                    10 => 'Budgerigar',
                    11 => 'Bull',
                    12 => 'Bunny',
                    13 => 'Capybara',
                    14 => 'Cat',
                    15 => 'Chameleon',
                    16 => 'Cheetah',
                    17 => 'Chimpanzee',
                    18 => 'Chipmunk',
                    19 => 'Coati',
                    20 => 'Cougar',
                    21 => 'Cow',
                    22 => 'Dingo',
                    23 => 'Doe',
                    24 => 'Dog',
                    25 => 'Donkey',
                    26 => 'Dromedary',
                    27 => 'Duckbill Platypus',
                    28 => 'Dung Beetle',
                    29 => 'Eagle Owl',
                    30 => 'Elephant',
                    31 => 'Ewe',
                    32 => 'Ferret',
                    33 => 'Finch',
                    34 => 'Fish',
                    35 => 'Fox',
                    36 => 'Frog',
                    37 => 'Gazelle',
                    38 => 'Gopher',
                    39 => 'Gorilla',
                    40 => 'Ground Hog',
                    41 => 'Guanaco',
                    42 => 'Guinea Pig',
                    43 => 'Hare',
                    44 => 'Hartebeest',
                    45 => 'Hog',
                    46 => 'Impala',
                    47 => 'Jaguar',
                    48 => 'Kangaroo',
                    49 => 'Koala',
                    50 => 'Lemur',
                    51 => 'Lion',
                    52 => 'Lizard',
                    53 => 'Lovebird',
                    54 => 'Marmoset',
                    55 => 'Marten',
                    56 => 'Meerkat',
                    57 => 'Mole',
                    58 => 'Mongoose',
                    59 => 'Moose',
                    60 => 'Mountain Goat',
                    61 => 'Mule',
                    62 => 'Musk Deer',
                    63 => 'Musk-Ox',
                    64 => 'Mustang',
                    65 => 'Octopus',
                    66 => 'Opossum',
                    67 => 'Otter',
                    68 => 'Panda',
                    69 => 'Parakeet',
                    70 => 'Peccary',
                    71 => 'Pig',
                    72 => 'Pony',
                    73 => 'Porpoise',
                    74 => 'Prairie Dog',
                    75 => 'Pronghorn',
                    76 => 'Puma',
                    77 => 'Puppy',
                    78 => 'Rabbit',
                    79 => 'Raccoon',
                    80 => 'Ram',
                    81 => 'Rat',
                    82 => 'Rhinoceros',
                    83 => 'Rooster',
                    84 => 'Sheep',
                    85 => 'Skunk',
                    86 => 'Snake',
                    87 => 'Snowy Owl',
                    88 => 'Starfish',
                    89 => 'Toad',
                    90 => 'Vicuna',
                    91 => 'Waterbuck',
                    92 => 'Whale',
                    93 => 'Wombat',
                    94 => 'Woodchuck',
                    95 => 'Yak',
                ),
                'things' =>
                array(
                    0 => 'Soda Can',
                    1 => 'Thread',
                    2 => 'Cell Phone',
                    3 => 'Grid Paper',
                    4 => 'Toe Ring',
                    5 => 'Purse',
                    6 => 'Buckel',
                    7 => 'Thermometer',
                    8 => 'Ipod',
                    9 => 'Paint Brush',
                    10 => 'Outlet',
                    11 => 'Zipper',
                    12 => 'Flag',
                    13 => 'Toothbrush',
                    14 => 'Model Car',
                    15 => 'Candy Wrapper',
                    16 => 'Glass',
                    17 => 'Key Chain',
                    18 => 'Lamp Shade',
                    19 => 'Car',
                    20 => 'Street Lights',
                    21 => 'Glasses',
                    22 => 'Paper',
                    23 => 'Sticky Note',
                    24 => 'Keys',
                    25 => 'Pants',
                    26 => 'Hair Tie',
                    27 => 'Sharpie',
                    28 => 'Clock',
                    29 => 'Photo Album',
                    30 => 'Eraser',
                    31 => 'Water Bottle',
                    32 => 'Charger',
                    33 => 'Blanket',
                    34 => 'Controller',
                    35 => 'Lace',
                    36 => 'Bed',
                    37 => 'Money',
                    38 => 'Desk',
                    39 => 'Wallet',
                    40 => 'Pen',
                    41 => 'Shampoo',
                    42 => 'Window',
                    43 => 'Shawl',
                    44 => 'Leg Warmers',
                    45 => 'Candle',
                    46 => 'Glow Stick',
                    47 => 'Teddies',
                    48 => 'Soy Sauce Packet',
                    49 => 'Bottle Cap',
                    50 => 'Eye Liner',
                    51 => 'Sponge',
                    52 => 'Stockings',
                    53 => 'Piano',
                    54 => 'Picture Frame',
                    55 => 'Blouse',
                    56 => 'Soap',
                    57 => 'Bread',
                    58 => 'Pillow',
                    59 => 'Keyboard',
                    60 => 'Shovel',
                    61 => 'Coasters',
                    62 => 'Hanger',
                    63 => 'Spoon',
                    64 => 'Screw',
                    65 => 'Tire Swing',
                    66 => 'Table',
                    67 => 'Watch',
                    68 => 'Bookmark',
                    69 => 'Deodorant',
                    70 => 'Magnet',
                    71 => 'Bag',
                    72 => 'Sidewalk',
                    73 => 'Clay Pot',
                    74 => 'Twister',
                    75 => 'Stop Sign',
                    76 => 'Scotch Tape',
                    77 => 'Socks',
                    78 => 'Rug',
                    79 => 'Helmet',
                    80 => 'Cookie Jar',
                    81 => 'Drawer',
                    82 => 'Camera',
                    83 => 'Sketch Pad',
                    84 => 'Computer',
                    85 => 'Lotion',
                    86 => 'Brocolli',
                    87 => 'Face Wash',
                    88 => 'Cinder Block',
                    89 => 'Floor',
                    90 => 'Door',
                    91 => 'Sailboat',
                    92 => 'Clamp',
                    93 => 'Thermostat',
                    94 => 'Fork',
                    95 => 'Mop',
                    96 => 'Twezzers',
                    97 => 'Washing Machine',
                    98 => 'Conditioner',
                    99 => 'Television',
                ),
                'slang' =>
                array(
                    0 => '10 Ply',
                    1 => '5-hole',
                    2 => 'Airball',
                    3 => 'Apple',
                    4 => 'Appy',
                    5 => 'Bar down',
                    6 => 'Bardownski',
                    7 => 'Barn',
                    8 => 'Beaking',
                    9 => 'Beaut',
                    10 => 'Beauty',
                    11 => 'Big city slams',
                    12 => 'Big shooter',
                    13 => 'Billet',
                    14 => 'Biscuit',
                    15 => 'Boat',
                    16 => 'Bomber',
                    17 => 'Caesar',
                    18 => 'Calvins',
                    19 => 'Cannon',
                    20 => 'Celly',
                    21 => 'Cheddar',
                    22 => 'Cheese',
                    23 => 'Chel',
                    24 => 'Chirping',
                    25 => 'Clap bomb',
                    26 => 'Clapper',
                    27 => 'Coast to coast',
                    28 => 'Crush',
                    29 => 'Dangles',
                    30 => 'Darts',
                    31 => 'De-gens',
                    32 => 'Dinger',
                    33 => 'Dip',
                    34 => 'Donk',
                    35 => 'Donkey Juice',
                    36 => 'Donnybrook',
                    37 => 'Dust',
                    38 => 'Duster',
                    39 => 'Electric lettuce',
                    40 => 'Ferda',
                    41 => 'Flow',
                    42 => 'Gainer',
                    43 => 'Gains',
                    44 => 'Geno',
                    45 => 'Gordie Howe Hat Trick',
                    46 => 'Great day for hay',
                    47 => 'Grocerystick',
                    48 => 'Gutty',
                    49 => 'Headmanning',
                    50 => 'Hoover',
                    51 => 'Horn',
                    52 => 'Hundo P',
                    53 => 'Hundy P',
                    54 => 'Jill strap',
                    55 => 'Lacrosstitute',
                    56 => 'Leafs',
                    57 => 'Leg Day',
                    58 => 'Legion',
                    59 => 'Lettuce',
                    60 => 'Lights lamp',
                    61 => 'Mitts',
                    62 => 'Mix a batch',
                    63 => 'Nappy',
                    64 => 'Oh bother',
                    65 => 'Op Shop',
                    66 => 'Pantene Pro',
                    67 => 'Pert near',
                    68 => 'Pheasant',
                    69 => 'Pitter patter',
                    70 => 'Plug',
                    71 => 'Plus-minus',
                    72 => 'Praccy',
                    73 => 'Puck bunny',
                    74 => 'Pump the brakes',
                    75 => 'Puppers',
                    76 => 'Pylon',
                    77 => 'Rip',
                    78 => 'Rippers',
                    79 => 'Roadie',
                    80 => 'Rocket',
                    81 => 'Sally',
                    82 => 'Sando',
                    83 => 'Sauce',
                    84 => 'Scheddy',
                    85 => 'Schmelt',
                    86 => 'Schneef',
                    87 => 'Scoots',
                    88 => 'Scrap',
                    89 => 'Ship',
                    90 => 'Shirt tucker',
                    91 => 'Sled',
                    92 => 'Snapper',
                    93 => 'Snappy',
                    94 => 'Snipe',
                    95 => 'Snow',
                    96 => 'Spare parts',
                    97 => 'Spit',
                    98 => 'Spitter',
                    99 => 'Squeezer',
                    100 => 'Suey',
                    101 => 'Sweater',
                    102 => 'Takedown',
                    103 => 'Tape-to-tape',
                    104 => 'Tarp',
                    105 => 'Tendy',
                    106 => 'The Rez',
                    107 => 'The Show',
                    108 => 'Throwing hip',
                    109 => 'Tilly',
                    110 => 'Tilly Time',
                    111 => 'Tilt',
                    112 => 'Toe-curling',
                    113 => 'Top Cheddar',
                    114 => 'Vender Bender',
                    115 => 'Wheel',
                    116 => 'Yonny',
                    117 => 'Zoomer',
                ),
            );
            $animals = $jsonWords['animals'];
            $things = $jsonWords['things'];
            $slangs = $jsonWords['slang'];

            $animalOrThing = rand(0, 1) == 0
                ? str_replace(' ', '', $animals[rand(0, sizeof($animals))])
                : str_replace(' ', '', $things[rand(0, sizeof($things))]);

            $slang = str_replace(' ', '', $slangs[rand(0, sizeof($slangs))]);
            $digits = 2;
            $twoDigitNum = rand(pow(10, $digits - 1), pow(10, $digits) - 1);

            if (strlen($slang) >= 13) {
                $phrase = $slang . $twoDigitNum;
            } else if ((strlen($slang) + strlen($animalOrThing)) < 13 && strlen($slang) <= 6) {
                $newSlang = str_replace(' ', '', $slangs[rand(0, sizeof($slangs))]);

                $phrase = strlen($newSlang) >= strlen($animalOrThing)
                    ? $newSlang . $animalOrThing . $twoDigitNum
                    : $animalOrThing . $newSlang . $twoDigitNum;

                if (strlen($newSlang) + strlen($animalOrThing) + 2 < 11) {
                    $phrase .= rand(pow(10, $digits - 1), pow(10, $digits) - 1);
                }
            } else if ((strlen($slang) + strlen($animalOrThing)) < 13 && strlen($animalOrThing) <= 6) {
                $newAnimalOrThing = str_replace(' ', '', $animals[rand(0, sizeof($animals))]);

                $phrase = strlen($slang) >= strlen($newAnimalOrThing)
                    ? $slang . $newAnimalOrThing . $twoDigitNum
                    : $newAnimalOrThing . $slang . $twoDigitNum;

                if (strlen($slang) + strlen($newAnimalOrThing) + 2 < 11) {
                    $phrase .= rand(pow(10, $digits - 1), pow(10, $digits) - 1);
                }
            } else {
                $phrase = strlen($slang) >= strlen($animalOrThing)
                    ? $slang . $animalOrThing . $twoDigitNum
                    : $animalOrThing . $slang . $twoDigitNum;
            }

            global $wpdb;
            $table_name = $wpdb->prefix . "facebook_group_secret_phrases";

            $query =    "SELECT id, phrase FROM $table_name
                    WHERE phrase = %s
                    LIMIT 1";

            $results = $wpdb->get_results($wpdb->prepare($query, $phrase));

            $iterations++;
        } while (sizeof($results) != 0 || $iterations >= 10);

        $wpdb->insert(
            $table_name,
            array(
                'id' => null,
                'user_id' => $user_id,
                'phrase' => $phrase,
                'owner_facebook_id' => get_user_meta($user_id, 'facebook_id', true),
                'used' => ''
            ),
            array(
                '%d',
                '%d',
                '%s',
                '%s'
            )
        );
    }
}

/* When the user logs in check if they need a secret facebook group phrase */
add_action('facebook_group_generate_phrase', 'fb_group_generate_phrase', 10, 2);

function init_facebook_id() {
    $user_id = get_current_user_id();
    add_user_meta($user_id, 'facebook_id', null);
}

add_action('wp_login', 'init_facebook_id');

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
        echo do_shortcode("[facebook_secret_phrase_shortcode]");
    }
}
add_action('mepr_account_nav_content', 'mepr_add_facebook_tab_content');

add_shortcode('facebook_secret_phrase_shortcode', 'facebook_secret_phrase');
function facebook_secret_phrase($atts = [], $content = null, $tag = '') {
    $user_id = get_current_user_id();

    // Check if they have an active membership first
    $meprUser = new MeprUser($user_id);
    $activeSubscriptions = $meprUser->active_product_subscriptions('ids');

    if (empty($activeSubscriptions)) {
        if (!headers_sent()) {
            header('location: /account/');
        }
        return '';
    }

    do_action("facebook_group_generate_phrase");
    $phraseRow = fb_group_get_phrase();
    $phrase = $phraseRow ? $phraseRow->phrase : '';
    $group_url = 'https://www.facebook.com/groups/thepond.howtohockey';
    $base = plugin_dir_path(__FILE__) . 'assets/';
    $url = plugin_dir_url(__FILE__) . 'assets/';

    wp_enqueue_style('facebook-group-secret', $url . 'facebook-group.css', array(), filemtime($base . 'facebook-group.css'));
    wp_enqueue_script('facebook-group-secret', $url . 'facebook-group.js', array('jquery'), filemtime($base . 'facebook-group.js'), true);
    wp_localize_script('facebook-group-secret', 'fbGroupSecret', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'generating' => __('Generating a new phrase…', 'facebook-group-secret'),
        'generated' => __('New phrase ready. Copy it before requesting to join.', 'facebook-group-secret'),
        'failed' => __('We couldn’t generate a new phrase. Please try again.', 'facebook-group-secret'),
        'copied' => __('Phrase copied to your clipboard.', 'facebook-group-secret'),
        'copyFailed' => __('Copy failed. Select the phrase and copy it manually.', 'facebook-group-secret'),
    ));

    $facebook_logo = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M24 12.07C24 5.41 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.04V9.41c0-3.02 1.8-4.7 4.54-4.7 1.31 0 2.68.24 2.68.24v2.97h-1.5c-1.5 0-1.96.93-1.96 1.89v2.26h3.32l-.53 3.5h-2.8V24C19.62 23.1 24 18.1 24 12.07"/></svg>';

    ob_start();
    ?>
    <div class="fb-group-card" data-fb-group>
        <div class="fb-group-card__cover">
            <span class="fb-group-card__logo"><?php echo $facebook_logo; ?></span>
            <div class="fb-group-card__heading">
                <span class="fb-group-card__eyebrow"><?php esc_html_e('Private group · Members only', 'facebook-group-secret'); ?></span>
                <h2 class="fb-group-card__title"><?php esc_html_e('The Pond Facebook Group', 'facebook-group-secret'); ?></h2>
            </div>
            <a class="fb-group-card__view" href="<?php echo esc_url($group_url); ?>" target="_blank" rel="noopener"><?php esc_html_e('View group', 'facebook-group-secret'); ?></a>
        </div>
        <div class="fb-group-card__body">
            <div class="fb-group-card__intro">
                <p><?php esc_html_e('Share clips, ask questions, and get feedback from coaches and other Pond members.', 'facebook-group-secret'); ?></p>
                <ol class="fb-group-card__steps">
                    <li><?php esc_html_e('Copy your secret phrase', 'facebook-group-secret'); ?></li>
                    <li><?php esc_html_e('Request to join the group', 'facebook-group-secret'); ?></li>
                    <li><?php esc_html_e('Paste it in the membership question', 'facebook-group-secret'); ?></li>
                </ol>
            </div>
            <form action="" method="post" id="secret-phrase-form" class="fb-group-card__phrase" onsubmit="return false;">
                <label for="phrase"><?php esc_html_e('Your secret phrase', 'facebook-group-secret'); ?></label>
                <div class="fb-group-card__field">
                    <input type="text" name="phrase" id="phrase" value="<?php echo esc_attr($phrase); ?>" readonly />
                    <button type="button" id="copyButton" class="fb-group-card__icon" aria-label="<?php esc_attr_e('Copy secret phrase', 'facebook-group-secret'); ?>" title="<?php esc_attr_e('Copy', 'facebook-group-secret'); ?>">
                        <svg class="fb-icon-copy" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="9" y="9" width="11" height="11" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><path d="M5 15V6a2 2 0 0 1 2-2h9" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        <svg class="fb-icon-check" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m5 12.5 4.5 4.5L19 7.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                    <button type="button" id="generate-new-phrase" class="fb-group-card__icon" aria-label="<?php esc_attr_e('Generate a new secret phrase', 'facebook-group-secret'); ?>" title="<?php esc_attr_e('New phrase', 'facebook-group-secret'); ?>">
                        <svg class="fb-icon-refresh" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 12a8 8 0 1 1-2.34-5.66M20 4v5h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                </div>
                <p class="fb-group-card__status" role="status" aria-live="polite"></p>
                <a href="<?php echo esc_url($group_url); ?>" target="_blank" rel="noopener" class="fb-group-card__join">
                    <?php echo $facebook_logo; ?>
                    <span><?php esc_html_e('Request to join on Facebook', 'facebook-group-secret'); ?></span>
                </a>
            </form>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
?>