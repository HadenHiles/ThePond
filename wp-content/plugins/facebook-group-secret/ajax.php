<?php
function validate_facebook_group_phrase() {
    $email = $_POST['email'];
    $phrase = $_POST['phrase'];
    $response = array("subscriptions" => array(), "error" => null, "valid" => false);

    if (empty($email) || empty($phrase)) {
        $response['error'] = "Missing post parameters [email, phrase]";
        $response['valid'] = false;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['error'] = "Invalid email address";
        $response['valid'] = false;
    }

    $wp_user = get_user_by('login', $email);
    if (empty($wp_user)) {
        $wp_user = get_user_by('email', $email);
    }

    if (empty($wp_user)) {
        $response['error'] = "No user exists for the email: $email";
        $response['valid'] = false;
    }

    /* Lookup the secret phrase from the database for that user */
    try {
        global $wpdb;
        $table_name = $wpdb->prefix . "facebook_group_secret_phrases";
        $query =    "SELECT id, phrase, `timestamp`, used FROM $table_name
                    WHERE phrase = %s
                    AND `user_id` = %d
                    AND used IS FALSE
                    ORDER BY `timestamp` DESC
                    LIMIT 1";

        $results = $wpdb->get_results($wpdb->prepare($query, $phrase, $wp_user->id));

        $response['valid'] = sizeof($results) == 1;
    } catch (Exception $e) {
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
            throw new Exception("No active subscriptions for user $email", 1);
        }

        send_res($response);
    } catch (Exception $e) {
        $response['valid'] = false;
        send_res($response, $e);
    }
}

add_action('wp_ajax_validate_facebook_group_phrase', 'validate_facebook_group_phrase');

function get_facebook_group_phrase() {
    try {
        $user_id = $_POST['user_id'];

        if ($user_id != get_current_user_id()) {
            throw new Exception('You don\'t have permission to view this phrase');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . "facebook_group_secret_phrases";
        $query =    "SELECT id, phrase, `timestamp`, used FROM $table_name
                    WHERE `user_id` = %d
                    AND used IS FALSE
                    ORDER BY `timestamp` DESC
                    LIMIT 1";

        $results = $wpdb->get_results($wpdb->prepare($query, $user_id));

        send_res($results);
    } catch (Exception $e) {
        send_res(null, $e);
    }
}

add_action('wp_ajax_get_facebook_group_phrase', 'get_facebook_group_phrase');

function generate_facebook_group_phrase() {
    try {
        $user_id = !empty($_POST['user_id']) ? $_POST['user_id'] : get_current_user_id();

        if ($user_id != get_current_user_id()) {
            throw new Exception('You don\'t have permission to generate a phrase for this user');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . "facebook_group_secret_phrases";

        $query =    "SELECT id, phrase, `timestamp`, used FROM $table_name
                    WHERE `user_id` = %d
                    AND used IS FALSE
                    ORDER BY `timestamp` DESC
                    LIMIT 1";

        $results = $wpdb->get_results($wpdb->prepare($query, $user_id));

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

            if ($wpdb->insert(
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
            )) {
                send_res(array('success' => true));
            } else {
                throw new Exception('Failed to generate new phrase');
            }
        }
    } catch (Exception $e) {
        send_res(null, $e);
    }
}

add_action('wp_ajax_generate_facebook_group_phrase', 'generate_facebook_group_phrase');

/**
 * Send a formatted json response to the client
 */
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
