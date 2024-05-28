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
        do_shortcode("[facebook_secret_phrase_shortcode]");
    }
}
add_action('mepr_account_nav_content', 'mepr_add_facebook_tab_content');

add_shortcode('facebook_secret_phrase_shortcode', 'facebook_secret_phrase');
function facebook_secret_phrase($atts = [], $content = null, $tag = '') {
    $user_id = get_current_user_id();

    // Check if they have an active membership first
    $meprUser = new MeprUser($user_id);
    $subscriptions = $meprUser->active_product_subscriptions('ids', false, false);
    $activeSubscriptions = $meprUser->active_product_subscriptions('ids');

    if (empty($activeSubscriptions)) {
        header('location: /account/');
    } else {
        // Don't make user's login with facebook to view their secret code - not worth the hassle
        // Check for existing facebook id in user meta
        // $facebookId = get_user_meta($user_id, 'facebook_id', true);
        // if (!empty($facebookId)) {
        do_action("facebook_group_generate_phrase");
        $phraseRow = fb_group_get_phrase();

    ?>
        <div>
            <form action="" method="post" id="secret-phrase-form">
                <div style="float: left; width: 300px; max-width: 100%; padding: 0 10px;">
                    <small>Your Secret Phrase:</small>
                    <br />
                    <div class="copy-able-input" style="display: flex; position: relative; padding-right: 40px;">
                        <input type="text" name="phrase" id="phrase" value="<?= $phraseRow->phrase ?>" readonly />
                        <a href="" id="copyButton" style="position: absolute; right: 48px; top: 10px; color: #777;"><i class="fa fa-clipboard"></i></a>
                        <a href="" id="generate-new-phrase" style="position: absolute; right: 5px; top: 10px;"><i class="fas fa-sync"></i></a>
                    </div>
                    <br />
                    <label for="phrase">Provide this phrase when you request to join the private <a href="https://www.facebook.com/groups/thepond.howtohockey" target="_blank">Facebook group</a></label>
                </div>
                <div style="float: left; padding: 23px 0;" class="secret-phrase-wrapper">
                    <a href="https://www.facebook.com/groups/thepond.howtohockey" target="_blank" class="BTN" style="background: #3b5998;"><i class="fab fa-facebook" style="margin-right: 5px;"></i> Go To Facebook Group</a>
                </div>
            </form>
        </div>
        <script type="text/javascript">
            (($) => {
                $('#generate-new-phrase').click((e) => {
                    e.preventDefault();

                    $('#generate-new-phrase').attr('disabled', true);
                    $('#generate-new-phrase svg').addClass('fa-spin');

                    var data = {
                        action: 'generate_facebook_group_phrase',
                        user_id: <?= get_current_user_id() ?>
                    };

                    $.ajax({
                        url: "/wp-admin/admin-ajax.php",
                        type: 'POST',
                        data: data,
                        success: function(response) {
                            success = response.data.success;

                            if (success) {
                                setTimeout(() => {
                                    $('#phrase').val(response.data.phrase);
                                }, 1000);
                            }
                        },
                        complete: function() {
                            setTimeout(() => {
                                $('#generate-new-phrase').attr('disabled', false);
                                $('#generate-new-phrase svg').removeClass('fa-spin');
                            }, 1000);
                        },
                        error: function(xhr, status, error) {
                            console.log(error);
                        }
                    });
                });

                $('#copyButton').click((e) => {
                    e.preventDefault();
                    copyToClipboard('phrase');
                });
            })(jQuery);

            function copyToClipboard(id) {
                var elem = document.getElementById(id);

                jQuery('#copyButton').css("color", "green");
                jQuery('#copyButton').html("<span style='color: green; font-size: 12px;'>Copied</span>");

                setTimeout(() => {
                    jQuery('#copyButton').css("color", "#777");
                    jQuery('#copyButton').html('<i class="fa fa-clipboard"></i>');
                }, 3000);

                // create hidden text element, if it doesn't already exist
                var targetId = "_hiddenCopyText_";
                var isInput = elem.tagName === "INPUT" || elem.tagName === "TEXTAREA";
                var origSelectionStart, origSelectionEnd;
                if (isInput) {
                    // can just use the original source element for the selection and copy
                    target = elem;
                    origSelectionStart = elem.selectionStart;
                    origSelectionEnd = elem.selectionEnd;
                } else {
                    // must use a temporary form element for the selection and copy
                    target = document.getElementById(targetId);
                    if (!target) {
                        var target = document.createElement("textarea");
                        target.style.position = "absolute";
                        target.style.left = "-9999px";
                        target.style.top = "0";
                        target.id = targetId;
                        document.body.appendChild(target);
                    }
                    target.textContent = elem.textContent;
                }
                // select the content
                var currentFocus = document.activeElement;
                target.focus();
                target.setSelectionRange(0, target.value.length);

                // copy the selection
                var succeed;
                try {
                    succeed = document.execCommand("copy");
                } catch (e) {
                    succeed = false;
                }
                // restore original focus
                if (currentFocus && typeof currentFocus.focus === "function") {
                    currentFocus.focus();
                }

                if (isInput) {
                    // restore prior selection
                    elem.setSelectionRange(origSelectionStart, origSelectionEnd);
                } else {
                    // clear temporary content
                    target.textContent = "";
                }
                return succeed;
            }
        </script>
        <?php
        // } 
        // else {
        //     
        ?>
        // <div style="float: left; padding: 23px 0;" class="secret-phrase-wrapper">
            // <p>To view your secret phrase and join the private <a href="https://www.facebook.com/groups/thepond.howtohockey" target="_blank">Facebook group</a>, please login with your Facebook account.</p>
            // <a href="" id="facebook-login-btn" class="BTN" style="background: #3b5998;"><i class="fab fa-facebook" style="margin-right: 5px;"></i> Login with Facebook</a>
            // </div>

        // <script type="text/javascript">
            //         (function ($) {
            //             $(document).ready(function () {
            //                 var a_key = "AIzaSyCoSWim4GptSro0gly6dN8dClVQMcxeCbA";
            //                 var pid = "the-pond-app";
            //                 var firebaseConfig = {
            //                     apiKey: a_key,
            //                     authDomain: pid + '.firebaseapp.com',
            //                     databaseURL: 'https://' + pid + '.firebaseio.com',
            //                     projectId: pid,
            //                     storageBucket: ''
            //                 };

            //                 // Initialize Firebase
            //                 if (!firebase.apps.length) {
            //                     firebase.initializeApp(firebaseConfig);
            //                     var auth = firebase.auth();
            //                     var provider = new firebase.auth.FacebookAuthProvider();
            //                     provider.setCustomParameters({
            //                         'display': 'popup'
            //                     });

            //                     $('#facebook-login-btn').click((e) => {
            //                         e.preventDefault();

            //                         firebase.auth().signInWithPopup(provider).then(function(result) {
            //                             // This gives you a Facebook Access Token. You can use it to access the Facebook API.
            //                             var token = result.credential.accessToken;
            //                             getFacebookUserId(token);
            //                         }).catch(function(error) {
            //                             var token = error.credential.accessToken;
            //                             getFacebookUserId(token);
            //                         });
            //                     });

            //                     function getFacebookUserId(accessToken) {
            //                         $.ajax({
            //                             url: `https://graph.facebook.com/me?access_token=` + accessToken, // this will point to admin-ajax.php
            //                             type: 'GET',
            //                             success: function(response) {
            //                                 var id = response.id;

            //                                 $.ajax({
            //                                     url: "/wp-admin/admin-ajax.php",
            //                                     type: "POST",
            //                                     data: {
            //                                         'action': 'update_user_facebook_id',
            //                                         'facebook_id': id
            //                                     },
            //                                     complete: function () {
            //                                         window.location.reload();
            //                                     },
            //                                     error: function(xhr, status, error) {
            //                                         console.log(error);
            //                                     }
            //                                 })
            //                             },
            //                             complete: function() {
            //                             },
            //                             error: function(xhr, status, error) {
            //                                 console.log(error);
            //                             }
            //                         });
            //                     }
            //                 }
            //             });
            //         })(jQuery);
            //     
        </script>
        // <?
            // }
        }
    }

    // Override the Learndash Profile Template so we can display the secret phrase code in the member dashboard
    function replacement_learndash_templates($filepath, $name, $args, $echo, $return_file_path) {
        if ($name == 'profile') {
            $filepath = plugin_dir_path(__FILE__) . 'learndash/profile.php';
        }

        return $filepath;
    }
    add_filter('learndash_template', 'replacement_learndash_templates', 90, 5);
            ?>