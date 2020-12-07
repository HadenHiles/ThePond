<?php
/*
*   CORS
*/
add_filter('allowed_http_origins', 'add_allowed_origins', 10, 1);
function add_allowed_origins($origins) {
    $origins[] = "https://www.facebook.com";
    return $origins;
}

/* For setting the user's facebook_id meta value before showing them their secret phrase */
function update_user_facebook_id() {
    $user_id = get_current_user_id();
    $facebook_id = $_POST['facebook_id'];

    $userMeta = update_user_meta($user_id, 'facebook_id', $facebook_id);
    $response['success'] = true;
    $response['user_meta'] = $userMeta;

    send_res($response);
}
add_action('wp_ajax_update_user_facebook_id', 'update_user_facebook_id');

/* Validate that a phrase exists and is owned by a member with an active memberpress subscription */
function validate_facebook_group_phrase() {
    $phrase = $_POST['phrase'];
    $response = array("subscriptions" => array(), "error" => null, "valid" => false);

    if (empty($phrase)) {
        $response['error'] = "Missing post parameters [email, phrase]";
        $response['valid'] = false;
    }

    /* Lookup the secret phrase from the database for that user */
    try {
        global $wpdb;
        $table_name = $wpdb->prefix . "facebook_group_secret_phrases";
        $query =    "SELECT id, phrase, `user_id`, created FROM $table_name
                    WHERE phrase = %s
                    ORDER BY created DESC
                    LIMIT 1";
        $latestPhraseQuery =    "SELECT id, phrase, `user_id`, created FROM $table_name
                    WHERE `user_id` = %d
                    ORDER BY created DESC
                    LIMIT 1";

        $result = $wpdb->get_results($wpdb->prepare($query, $phrase));
        $latestPhraseResult = $wpdb->get_results($wpdb->prepare($latestPhraseQuery, $result[0]->user_id));

        if ($result[0]->phrase == $latestPhraseResult[0]->phrase) {
            $response['valid'] = true;
        } else {
            $response['error'] = "The phrase: $phrase has expired";
            $response['valid'] = false;
        }

        $wp_user = get_user_by('id', $latestPhraseResult[0]->user_id);

        if (empty($wp_user)) {
            $response['error'] = "No user exists with the phrase: $phrase";
            $response['valid'] = false;
        }

        try {
            /* Check for active memberpress subscriptions */
            $mpUser = new MeprUser($wp_user->id);
            $activeSubscriptions = $mpUser->active_product_subscriptions("transactions");

            $subs = array();
            foreach ($activeSubscriptions as $s) {
                $sub = array(
                    "id" => $s->product_id,
                    "price" => $s->amount,
                    "created_at" => $s->created_at,
                    "expires_at" => $s->expires_at,
                    "transaction_type" => $s->txn_type,
                    "transaction_num" => $s->trans_num,
                    "gateway" => $s->gateway,
                    "status" => $s->status
                );

                $subs[] = $sub;
            }

            $hasActiveMembership = !empty($activeSubscriptions);

            if ($hasActiveMembership) {
                $response['subscriptions'] = $subs;
            } else {
                throw new Exception("No active subscriptions for user $wp_user->user_email", 1);
            }

            $response['user'] = $wp_user;
            send_res($response);
        } catch (Exception $e) {
            $response['valid'] = false;
            send_res($response, $e);
        }
    } catch (Exception $e) {
        $response['valid'] = false;
        send_res($response, $e);
    }
}
add_action('wp_ajax_validate_facebook_group_phrase', 'validate_facebook_group_phrase');
add_action('wp_ajax_nopriv_validate_facebook_group_phrase', 'validate_facebook_group_phrase');

// Return the user's current secret phrase
function get_facebook_group_phrase() {
    try {
        $user_id = $_POST['user_id'];

        if ($user_id != get_current_user_id()) {
            throw new Exception('You don\'t have permission to view this phrase');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . "facebook_group_secret_phrases";
        $query =    "SELECT id, phrase, `created` FROM $table_name
                    WHERE `user_id` = %d
                    ORDER BY `created` DESC
                    LIMIT 1";

        $results = $wpdb->get_results($wpdb->prepare($query, $user_id));

        send_res($results);
    } catch (Exception $e) {
        send_res(null, $e);
    }
}
add_action('wp_ajax_get_facebook_group_phrase', 'get_facebook_group_phrase');

// Generate a new phrase for the user and cleanup any unused phrases
function generate_facebook_group_phrase() {
    try {
        $user_id = !empty($_POST['user_id']) ? intval($_POST['user_id']) : get_current_user_id();

        if ($user_id != get_current_user_id()) {
            throw new Exception('You don\'t have permission to generate a phrase for this user');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . "facebook_group_secret_phrases";

        /* Default the phrase just in case */
        $bytes = random_bytes(5); // Random characters
        $rand = bin2hex($bytes); // Ensure alphanumeric
        $phrase = $rand;

        $iterations = 0;
        global $wpdb;
        $table_name = $wpdb->prefix . "facebook_group_secret_phrases";

        $query =    "SELECT id, phrase FROM $table_name
                    WHERE phrase = %s
                    LIMIT 1";
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

            $results = $wpdb->get_results($wpdb->prepare($query, $phrase));

            $iterations++;
        } while (sizeof($results) != 0 || $iterations >= 10);

        if ($wpdb->insert(
            $table_name,
            array(
                'id' => null,
                'user_id' => $user_id,
                'phrase' => $phrase,
                'owner_facebook_id' => get_user_meta($user_id, 'facebook_id', true),
                'used' => null
            ),
            array(
                '%d',
                '%d',
                '%s',
                '%s'
            )
        )) {
            cleanup_unused_phrases($user_id, $phrase);
            send_res(array('success' => true, 'phrase' => $phrase));
        } else {
            throw new Exception('Failed to generate new phrase');
        }
    } catch (Exception $e) {
        send_res(null, $e);
    }
}
add_action('wp_ajax_generate_facebook_group_phrase', 'generate_facebook_group_phrase');

// Record a phrase that has been used
function use_phrase() {
    $phrase = $_POST['phrase'];
    $facebook_id = $_POST['facebook_id'];
    $response = array("error" => null);

    if (empty($phrase) || empty($facebook_id)) {
        $response['error'] = "Missing post parameters [phrase, facebook_id]";
        send_res($response);
    }

    /* Lookup the secret phrase from the database for that user */
    try {
        global $wpdb;
        $table_name = $wpdb->prefix . "facebook_group_secret_phrases";
        $query =    "SELECT id, phrase, `user_id`, owner_facebook_id, created FROM $table_name
                    WHERE phrase = %s
                    AND owner_facebook_id IS NOT NULL
                    ORDER BY created DESC
                    LIMIT 1";

        $results = $wpdb->get_results($wpdb->prepare($query, $phrase));

        if (sizeof($results) > 0 && $results[0]->phrase == $phrase) {
            if ($results[0]->owner_facebook_id == $facebook_id) {
                if ($wpdb->update(
                    $table_name,
                    // Data
                    array(
                        'facebook_id' => $facebook_id,
                        'used' => current_time('mysql')
                    ),
                    // Where
                    array(
                        'id' => $results[0]->id
                    )
                )) {
                    $response['success'] = true;
                } else {
                    throw new Exception('Failed to update phrase for phrase owner');
                }
            } else {
                if ($wpdb->insert(
                    $table_name,
                    array(
                        'id' => null,
                        'user_id' => $results[0]->user_id,
                        'phrase' => $phrase,
                        'facebook_id' => $facebook_id,
                        'created' => $results[0]->created,
                        'owner_facebook_id' => $results[0]->owner_facebook_id
                    ),
                    array(
                        '%d',
                        '%d',
                        '%s',
                        '%s',
                        '%s',
                        '%s'
                    )
                )) {
                    $response['success'] = true;
                } else {
                    throw new Exception('Failed to insert new phrase');
                }
            }
        } else {
            throw new Exception('Invalid phrase');
        }

        send_res($response);
    } catch (Exception $e) {
        send_res($response, $e);
    }
}
add_action('wp_ajax_use_phrase', 'use_phrase');
add_action('wp_ajax_nopriv_use_phrase', 'use_phrase');

/*
*   Utility functions
*/

// Utility function for removing any unused phrases (don't have a facebook_id)
function cleanup_unused_phrases($user_id, $latestPhrase) {
    global $wpdb;
    $table_name = $wpdb->prefix . "facebook_group_secret_phrases";

    $query =    "SELECT id, phrase, facebook_id FROM $table_name
                    WHERE `user_id` = %d
                    AND phrase <>%s
                    AND facebook_id IS NULL";

    $results = $wpdb->get_results($wpdb->prepare($query, $user_id, $latestPhrase));

    foreach ($results as $result) {
        $wpdb->delete($table_name, array('id' => $result->id));
    }
}

// Send a formatted json response to the client
function send_res($data, Exception $e = null) {
    if (empty($e)) {
        wp_send_json(
            array(
                'data' => $data
            ),
            200
        );
    } else {
        wp_send_json(
            array(
                'error' => array(
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                )
            ),
            $e->getCode()
        );
    }
}
