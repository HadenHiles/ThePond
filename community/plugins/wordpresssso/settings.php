//<?php

/**
 * WordPress SSO Settings
 *
 * @author		Stuart Silvester
 * @copyright	2017 - Stuart Silvester
 * @link		http://ipb.silvesterwebdesigns.com
 */

$form->addTab( 'wordpress_configuration' );

/* Validator method for URL */
$urlTest = function( $value )
{
	try
	{
		$response = \IPS\Http\Url::external( rtrim( $value, '/' ) . '/wp_api.php' )
									->setQueryString( 'type', 'test' )
									->request()
									->get();

		if( parse_url( $value, PHP_URL_SCHEME ) === 'https' AND parse_url( \IPS\Settings::i()->base_url, PHP_URL_SCHEME ) === 'http' )
		{
			throw new \InvalidArgumentException( 'wordpress_url_https' );
		}

		if( !in_array( $response->httpResponseCode, array( '200', '401' ) ) )
		{
			throw new \InvalidArgumentException( 'wordpress_api_notfound' );
		}
	}
	catch( \IPS\Http\Request\Exception $e )
	{
		throw new \InvalidArgumentException( 'wordpress_url_connection_err' );
	}
};

/* Validator method for API Key */
$keyTest = function( $value )
{
	try
	{
		$response = \IPS\Http\Url::external( rtrim( \IPS\Request::i()->wordpress_url, '/' ) . '/wp_api.php' )
									->setQueryString( array( 'type' => 'test', 'api_key' => $value ) )
									->request()
									->get();

		if( !in_array( $response->httpResponseCode, array( '200' ) ) )
		{
			throw new \InvalidArgumentException( 'wordpress_api_key_wrong' );
		}
	}
	catch( \IPS\Http\Request\Exception $e )
	{
		throw new \InvalidArgumentException( 'wordpress_url_connection_err' );
	}
};

$form->add( new \IPS\Helpers\Form\Url( 'wordpress_url', \IPS\Settings::i()->wordpress_url, TRUE, array(), $urlTest ) );
$form->add( new \IPS\Helpers\Form\Text( 'wordpress_api_key', \IPS\Settings::i()->wordpress_api_key ?: md5( uniqid() ), TRUE, array(), $keyTest ) );

/* Don't show the mapping untill the settings are configured */
if( \IPS\Settings::i()->wordpress_url and \IPS\Settings::i()->wordpress_url )
{
	try
	{
		/* Fetch WordPress Roles */
		$wpRoles = \IPS\Http\Url::external( rtrim( \IPS\Settings::i()->wordpress_url, '/' ) . '/wp_api.php' )
										->setQueryString( array( 'type' => 'roles', 'api_key' => \IPS\Settings::i()->wordpress_api_key ) )
										->request()->get()->decodeJson( TRUE );

		if( !$wpRoles )
		{
			throw new \RuntimeException;
		}

		$form->addTab( 'wordpress_roleMap' );
		$form->addMessage( 'wordpress_roleMap_desc', 'ipsMessage ipsMessage_info' );

		/* Unset Admin */
		unset( $wpRoles['administrator'] );

		/* Group Sync Matrix */
		$groups = \IPS\Member\Group::groups();
		$groupList = array();
		foreach ( $groups as $group )
		{
			/* Don't map to Administrators */
			if( $group->g_access_cp or $group->g_id == \IPS\Settings::i()->guest_group )
			{
				continue;
			}

			$groupList[ $group->g_id ] = $group->name;
		}

		$matrix = new \IPS\Helpers\Form\Matrix( 'matrix' );
		$matrix->langPrefix = 'wordpress_';
		$matrix->columns = array(
			'role_remote'	=> function( $key, $value, $data ) use ( $wpRoles )
			{
				return new \IPS\Helpers\Form\Select( $key, $value ?: NULL, TRUE, array( 'options' => $wpRoles ) );
			},
			'group_local'	=> function( $key, $value, $data ) use ( $groupList )
			{
				return new \IPS\Helpers\Form\Select( $key, $value ?: \IPS\Settings::i()->member_group, TRUE, array( 'options' => $groupList ) );
			}
		);

		/* Populate the default Matrix rows */
		$warnings = '';
		if ( $groupLinks = json_decode( \IPS\Settings::i()->wordpress_group_map, TRUE ) )
		{
			foreach ( $groupLinks as $remote => $local )
			{
				$matrix->rows[] = array(
					'role_remote'	=> $remote,
					'group_local'	=> $local
				);

				/* Check that the group exists */
				if ( !isset( $groups[ $local ] ) )
				{
					$warnings .= \IPS\Theme::i()->getTemplate( 'global', 'core', 'global' )->message( \IPS\Member::loggedIn()->language()->addToStack( 'wordpress_invalid_group', FALSE, array( 'sprintf' => array( $remote ) ) ), 'warning' );
				}
			}
		}

		/* Add Warning notices */
		if( $warnings )
		{
			$form->addHtml( $warnings );
		}

		$form->addMatrix( 'wordpressMatrix', $matrix );
		$form->addMessage( 'wordpress_no_admins', 'ipsMessage ipsMessage_warning' );

		$form->addHeader( 'wordpress_secondary_header' );
		$form->addMessage( 'wordpress_secondary_warning', 'ipsMessage ipsMessage_error' );
		$form->add( new \IPS\Helpers\Form\YesNo( 'wordpress_secondary_groups', \IPS\Settings::i()->wordpress_secondary_groups, TRUE ) );
	}
	catch( \RuntimeException $e ) { }
}

/* Save Settings */
if ( $values = $form->values() )
{
	$groupValues = array();
	if ( isset( $values['wordpressMatrix'] ) AND is_array( $values['wordpressMatrix'] ) )
	{
		foreach ( $values['wordpressMatrix'] as $group )
		{
			$groupValues[ $group['role_remote'] ] = $group['group_local'];
		}
		$values['wordpress_group_map'] = json_encode( $groupValues );
	}

	$form->saveAsSettings( $values );
	return TRUE;
}

return $form;