//<?php

/**
 * WordPress SSO - Login Hook
 *
 * @author		Stuart Silvester
 * @copyright	2017 - Stuart Silvester
 * @link		http://ipb.silvesterwebdesigns.com
 */

class hook6 extends _HOOK_CLASS_
{
	/**
	 * Log In
	 *
	 * @return	void
	 */
	protected function manage()
	{
		try
		{
			if( !empty( \IPS\Settings::i()->wordpress_url ) )
			{
				if( defined( 'WP_SSO_LOGIN_URL' ) )
				{
					$url = WP_SSO_LOGIN_URL;
				}
				else
				{
					$redirect = isset( \IPS\Request::i()->ref ) ? base64_decode( \IPS\Request::i()->ref ) : ( $_SERVER['HTTP_REFERER'] ?: \IPS\Settings::i()->base_url );
	
					try
					{
						$apiResponse = \IPS\Http\Url::external( rtrim( \IPS\Settings::i()->wordpress_url, '/' ) . '/wp_api.php' )
													->setQueryString( [ 'api_key' => \IPS\Settings::i()->wordpress_api_key, 'type' => 'login', 'redirect' => $redirect ] )
													->request()
													->get();
	
						if( in_array( $apiResponse->httpResponseCode, array( '404', '401', '403' ) ) )
						{
							throw new \Exception( 'invalid_request' );
						}
	
						$api = $apiResponse->decodeJson();
						$url = $api['url'];
					}
					catch( \Exception $e )
					{
						/* Redirect to login - Fallback to default WP URL */
						\IPS\Output::i()->redirect( \IPS\Http\Url::external( \IPS\Http\Url::external( rtrim( \IPS\Settings::i()->wordpress_url, '/' ) . '/wp-login.php' ) )->setQueryString( 'redirect_to', $redirect ) );
						exit;
					}
				}
	
				/* Redirect to login URL */
				\IPS\Output::i()->redirect( \IPS\Http\Url::external( $url ) );
				exit;
			}
	
			parent::manage();
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