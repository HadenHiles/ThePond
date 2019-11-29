<?php
/**
 * @brief		activeUsers Widget
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		19 Nov 2013
 */

namespace IPS\core\widgets;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * activeUsers Widget
 */
class _activeUsers extends \IPS\Widget
{
	/**
	 * @brief	Widget Key
	 */
	public $key = 'activeUsers';
	
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
		
		$members     = array();
		$memberCount = 0;
				
		/* Build WHERE clause */
		$parts = parse_url( (string) \IPS\Request::i()->url()->setPage() );
		
		if ( \IPS\Settings::i()->htaccess_mod_rewrite )
		{
			$url = $parts['scheme'] . "://" . $parts['host'] . ( isset( $parts['port'] ) ? ':' . $parts['port'] : '' ) . $parts['path'];
		}
		else
		{
			$url = $parts['scheme'] . "://" . $parts['host'] . ( isset( $parts['port'] ) ? ':' . $parts['port'] : '' ) . $parts['path'] . ( isset( $parts['query'] ) ? '?' . $parts['query'] : '' );
		}
		
		$members = \IPS\Session\Store::i()->getOnlineMembersByLocation( \IPS\Dispatcher::i()->application->directory, \IPS\Dispatcher::i()->module->key, \IPS\Dispatcher::i()->controller, \IPS\Request::i()->id, $url );
		
		if ( isset( $members[ \IPS\Member::loggedIn()->member_id ] ) )
		{
			unset( $members[ \IPS\Member::loggedIn()->member_id ] );
		}
		
		$memberCount = \count( $members );
		
		/* If it's on the sidebar (rather than at the bottom), we want to limit it to 60 so we don't take too much space */
		if ( $this->orientation === 'vertical' and \count( $members ) >= 60 )
		{
			$members = \array_slice( $members, 0, 60 );
		}

		if( \IPS\Member::loggedIn()->member_id )
		{
			if( !isset( $members[ \IPS\Member::loggedIn()->member_id ] ) )
			{
				$memberCount++;
			}

			$members = array_merge( array( \IPS\Member::loggedIn()->member_id => array(
				'member_id'			=> \IPS\Member::loggedIn()->member_id,
				'member_name'		=> \IPS\Member::loggedIn()->name,
				'seo_name'			=> \IPS\Member::loggedIn()->members_seo_name,
				'member_group'		=> \IPS\Member::loggedIn()->member_group_id,
				'in_editor'			=> 0
			) ), $members );
		}

		/* Display */
		return $this->output( $members, $memberCount );
	}
}