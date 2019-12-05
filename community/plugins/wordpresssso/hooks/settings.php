//<?php

/**
 * WordPress SSO - Account Settings Hook
 *
 * @author		Stuart Silvester
 * @copyright	2017 - Stuart Silvester
 * @link		http://ipb.silvesterwebdesigns.com
 */

class hook4 extends _HOOK_CLASS_
{
	/**
	 * Register
	 *
	 * @return	void
	 */
	protected function _email()
	{
		try
		{
			if( !empty( \IPS\Settings::i()->wordpress_url ) )
			{
				\IPS\Output::i()->redirect( \IPS\Http\Url::external( rtrim( \IPS\Settings::i()->wordpress_url, '/' ) . '/wp-admin/profile.php' ) );
				exit;
			}
	
			return parent::_email();
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
	 * Password
	 *
	 * @return	void
	 */
	protected function _password()
	{
		try
		{
			if( !empty( \IPS\Settings::i()->wordpress_url ) )
			{
				\IPS\Output::i()->redirect( \IPS\Http\Url::external( rtrim( \IPS\Settings::i()->wordpress_url, '/' ) . '/wp-admin/profile.php' ) );
				exit;
			}
	
			return parent::_password();
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
	 * Username
	 *
	 * @return	void
	 */
	protected function _username()
	{
		try
		{
			if( !empty( \IPS\Settings::i()->wordpress_url ) )
			{
				\IPS\Output::i()->redirect( \IPS\Http\Url::external( rtrim( \IPS\Settings::i()->wordpress_url, '/' ) . '/wp-admin/profile.php' ) );
				exit;
			}
	
			return parent::_username();
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