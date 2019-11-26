<?php
/**
 * @brief		whosOnline Widget
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		28 Jul 2014
 */

namespace IPS\core\widgets;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * whosOnline Widget
 */
class _whosOnline extends \IPS\Widget
{
	/**
	 * @brief	Widget Key
	 */
	public $key = 'whosOnline';
	
	/**
	 * @brief	App
	 */
	public $app = 'core';
		
	/**
	 * @brief	Plugin
	 */
	public $plugin = '';

	/**
	 * Render a widget
	 *
	 * @return	string
	 */
	public function render()
	{
		/* Do we have permission? */
		if ( !\IPS\Member::loggedIn()->canAccessModule( \IPS\Application\Module::get( 'core', 'online' ) ) )
		{
			return "";
		}
		
		/* Init */
		$members     = array();
		$anonymous   = 0;
		
		$users = \IPS\Session\Store::i()->getOnlineUsers( \IPS\Session\Store::ONLINE_MEMBERS, 'desc', NULL, NULL, TRUE );
		foreach( $users as $row )
		{
			switch ( $row['login_type'] )
			{
				/* Not-anonymous Member */
				case \IPS\Session\Front::LOGIN_TYPE_MEMBER:
					if ( $row['member_id'] != \IPS\Member::loggedIn()->member_id ) // We add them manually to make sure they go at the top of the list
					{
						if ( $row['member_name'] )
						{
							$members[ $row['member_id'] ] = $row;
						}
					}
					break;
					
				/* Anonymous member */
				case \IPS\Session\Front::LOGIN_TYPE_ANONYMOUS:
					$anonymous += 1;
					break;
			}
		}
		$memberCount = \count( $members );

		/* Get an accurate guest count */
		$guests = \IPS\Session\Store::i()->getOnlineUsers( \IPS\Session\Store::ONLINE_GUESTS | \IPS\Session\Store::ONLINE_COUNT_ONLY, 'desc', NULL, NULL, TRUE );
		
		/* If it's on the sidebar (rather than at the bottom), we want to limit it to 60 so we don't take too much space */
		if ( $this->orientation === 'vertical' and \count( $members ) >= 60 )
		{
			$members = \array_slice( $members, 0, 60 );
		}
		
		/* Add ourselves at the top of the list */
		if( \IPS\Member::loggedIn()->member_id )
		{
			$memberCount++;
						
			$members = array_merge( array( \IPS\Member::loggedIn()->member_id => array(
				'member_id'			=> \IPS\Member::loggedIn()->member_id,
				'member_name'		=> \IPS\Member::loggedIn()->name,
				'seo_name'			=> \IPS\Member::loggedIn()->members_seo_name,
				'member_group'		=> \IPS\Member::loggedIn()->member_group_id
			) ), $members );
		}
		
		/* Display */
		return $this->output( $members, $memberCount, $guests, $anonymous );
	}
}