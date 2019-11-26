<?php

/**
 * WordPress SSO API for IPS4
 *
 * @author		Stuart Silvester
 * @copyright	2017 - Stuart Silvester
 * @link		http://ipb.silvesterwebdesigns.com
 */

/*
 * Enter your API KEY here, you can find this in the IPS4 AdminCP > Plugins > Edit WordPress SSO
 */
$apiKey = '62ddafd16b3d1435d59727c7d6c0d919';

/* -------------------------------- *
 *  DO NOT EDIT ANYTHING BELOW HERE *
 * -------------------------------- */

if( !isset( $_GET['api_key'] ) or ( isset( $_GET['api_key'] ) and !hash_compare( (string) $_GET['api_key'], $apiKey ) ) )
{
	header('HTTP/1.1 401 Unauthorized');
	echo 'API key not provided or incorrect.';
	exit;
}

/* Get WordPress */
include_once( 'wp-load.php' );

switch( $_GET['type'] )
{
	/* Verify user Cookie */
	case 'userinfo':	
		/* Check the cookie is valid */
		if( !$id = wp_validate_auth_cookie( '', 'logged_in' ) )
		{
			header('HTTP/1.1 403 Forbidden');
			echo 'The Cookie does not appear to be valid.';
			exit;
		}

		/* Load user */
		if( !$user = get_user_by( 'id', $id ) )
		{
			header('HTTP/1.1 404 Not Found');
			echo 'The user could not be located.';
			exit;
		}

		/* Output API object */
		echo json_encode(
							array(
								'user_id' => $user->ID,
								'display_name' => $user->display_name,
								'email'	=> $user->user_email,
								'role' => count( $user->roles ) ? $user->roles : FALSE
							)
						);
		break;

	/* Return WordPress Roles */
	case 'roles':
		echo json_encode( wp_roles()->get_names() );
		break;

	/* Login URL */
	case 'login':
		echo json_encode( [ 'url' => wp_login_url( validateUrl( $_GET['redirect'] ) ) ] );
		break;

	/* Register URL */
	case 'register':
		echo json_encode( [ 'url' => wp_registration_url( validateUrl( $_GET['redirect'] ) ) ] );
		break;

	/* Logout URL */
	case 'logout':
		echo json_encode( [ 'url' => wp_logout_url( validateUrl( $_GET['redirect'] ) ) ] );
		break;

	/* No type defined */
	default:
		header('HTTP/1.1 404 Not Found');
		echo 'No type defined.';
		break;

	/* Test API connectivity */
	case 'test':
		echo 'OK';
		break;
}

/**
 * HashCompare courtesy of http://uk1.php.net/manual/en/function.hash-hmac.php#111435
 * 
 * @param	string		hash to test
 * @param	string		expected hash
 * @return	boolean
 */
function hash_compare( $a, $b )
{
	if ( !is_string( $a ) || !is_string( $b  ) )
	{
		return false;
	}

	$len = strlen( $a );
	if ( $len !== strlen( $b ) )
	{
		return false;
	}

	$status = 0;
	for ( $i = 0; $i < $len; $i++ )
	{
		$status |= ord( $a[$i] ) ^ ord( $b[$i] );
	}
	return $status === 0;
}

/**
 * Validate URL
 *
 * @param	string				url
 * @return	boolean|string
 */
function validateUrl( $url )
{
	$data = filter_var( $url, FILTER_VALIDATE_URL );
	if( $data === FALSE )
	{
		return NULL;
	}
	return $data;
}

exit;