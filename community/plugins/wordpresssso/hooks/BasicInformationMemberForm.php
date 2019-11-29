//<?php

/**
 * WordPress SSO - MemberForm Hook
 *
 * @author		Stuart Silvester
 * @copyright	2017 - Stuart Silvester
 * @link		http://ipb.silvesterwebdesigns.com
 */

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class hook2 extends _HOOK_CLASS_
{
	/**
	 * Action Buttons
	 *
	 * @param	\IPS\Member	$member	The Member
	 * @return	array
	 */
	public function actionButtons( $member )
	{
		try
		{
			$result = parent::actionButtons( $member );
	
			if( !isset( $result['actions']['menu'] ) )
			{
				return $result;
			}
	
			foreach( $result['actions']['menu'] as $k => $v )
			{
				/* If it's sign in as */
				if( $v['icon'] == 'key' )
				{
					unset( $result['actions']['menu'] [$k] );
				}
			}
	
			return $result;
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
