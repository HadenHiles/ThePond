//<?php

/**
 * WordPress SSO - IPS4
 *
 * @author		Stuart Silvester
 * @copyright	2017 - Stuart Silvester
 * @link		http://ipb.silvesterwebdesigns.com
 */

class hook1 extends _HOOK_CLASS_
{
	/**
	 * Add our own column to default fields
	 */
	public function __construct()
	{
		try
		{
			static::$databaseIdFields = array_merge( static::$databaseIdFields, array( 'wordpress_id' ) );
	
			parent::__construct();
		}
		catch ( \RuntimeException $e )
		{
			if ( method_exists( get_parent_class(), __FUNCTION__ ) )
			{
				return call_user_func_array( 'parent::' . __FUNCTION__, func_get_args() );
			}
			else
			{
				throw $e;
			}
		}
	}

	/**
	 * Member Sync
	 *
	 * @param	string	$method	Method
	 * @param	array	$params	Additional parameters to pass
	 * @return	void
	 */
	public function memberSync( $method, $params=array() )
	{
		try
		{
			/* Let normal class do its thing */
			call_user_func_array( 'parent::memberSync', func_get_args() );
	
			if( $method == 'onLogout' AND !empty( \IPS\Settings::i()->wordpress_url ) )
			{
				try
				{
					$apiResponse = \IPS\Http\Url::external( rtrim( \IPS\Settings::i()->wordpress_url, '/' ) . '/wp_api.php' )
												->setQueryString( [ 'api_key' => \IPS\Settings::i()->wordpress_api_key, 'type' => 'logout', 'redirect' => \IPS\Http\Url::internal( '' ) ] )
												->request()
												->setHeaders( array( 'Cookie' => \IPS\Session\Front::$wpCookie . '=' . $_COOKIE[ \IPS\Session\Front::$wpCookie ] ) )
												->get();
	
					if( in_array( $apiResponse->httpResponseCode, array( '404', '401', '403' ) ) )
					{
						throw new \Exception( 'invalid_request' );
					}
	
					$api = $apiResponse->decodeJson();
				}
				catch( \Exception $e )
				{
					/* Redirect to Logout */
					\IPS\Output::i()->redirect( \IPS\Http\Url::external( rtrim( \IPS\Settings::i()->wordpress_url, '/' ) . '/wp-login.php' )->setQueryString( 'action', 'logout' ) );
					exit;
				}
	
				/* Redirect to Logout */
				\IPS\Output::i()->redirect( \IPS\Http\Url::external( str_replace( '&amp;', '&', $api['url'] ) ) );
				exit;
			}
		}
		catch ( \RuntimeException $e )
		{
			if ( method_exists( get_parent_class(), __FUNCTION__ ) )
			{
				return call_user_func_array( 'parent::' . __FUNCTION__, func_get_args() );
			}
			else
			{
				throw $e;
			}
		}
	}
}