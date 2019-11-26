//<?php

/**
 * WordPress SSO - Register Hook
 *
 * @author		Stuart Silvester
 * @copyright	2017 - Stuart Silvester
 * @link		http://ipb.silvesterwebdesigns.com
 */

class hook5 extends _HOOK_CLASS_
{
	/**
	 * Register
	 *
	 * @return	void
	 */
	protected function manage()
	{
		try
		{
			if( !empty( \IPS\Settings::i()->wordpress_url ) )
			{
				try
				{
					$apiResponse = \IPS\Http\Url::external( rtrim( \IPS\Settings::i()->wordpress_url, '/' ) . '/wp_api.php' )
												->setQueryString( [ 'api_key' => \IPS\Settings::i()->wordpress_api_key, 'type' => 'register', 'redirect' => $_SERVER['HTTP_REFERER'] ?: \IPS\Settings::i()->base_url ] )
												->request()
												->get();
	
					if( in_array( $apiResponse->httpResponseCode, array( '404', '401', '403' ) ) )
					{
						throw new \Exception( 'invalid_request' );
					}
	
					$api = $apiResponse->decodeJson();
				}
				catch( \Exception $e )
				{
					/* Redirect to register - Fallback to default WP URL */
					\IPS\Output::i()->redirect( \IPS\Http\Url::external( rtrim( \IPS\Settings::i()->wordpress_url, '/' ) . '/wp-login.php' ) )->setQueryString( array( 'action' => 'register', 'redirect_to' => $_SERVER['HTTP_REFERER'] ?: \IPS\Settings::i()->base_url ) );
					exit;
				}
	
				/* Redirect to register url */
				\IPS\Output::i()->redirect( \IPS\Http\Url::external( $api['url'] ) );
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