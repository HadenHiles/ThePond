//<?php

/**
 * WordPress SSO - Admin Members Hook
 *
 * @author		Stuart Silvester
 * @copyright	2017 - Stuart Silvester
 * @link		http://ipb.silvesterwebdesigns.com
 */

class hook8 extends _HOOK_CLASS_
{
	/**
	 * Edit Member
	 *
	 * @return	void
	 */
	public function edit()
	{
		try
		{
			\IPS\Member::loggedIn()->language()->words['group_desc'] = \IPS\Member::loggedIn()->language()->addToStack('wordpress_member_group_desc');
	
			if( \IPS\Settings::i()->wordpress_secondary_groups )
			{
				\IPS\Member::loggedIn()->language()->words['secondary_groups_desc'] = \IPS\Member::loggedIn()->language()->addToStack('wordpress_member_sgroup_desc');
			}
	
			return parent::edit();
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
	 * Login as member
	 *
	 * @return	void
	 */
	public function login()
	{
		try
		{
			\IPS\Dispatcher::i()->checkAcpPermission( 'member_login' );
	
			\IPS\Output::i()->error( 'wordpress_signin_as_notavailable', '2S100/1', 404, '' );
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