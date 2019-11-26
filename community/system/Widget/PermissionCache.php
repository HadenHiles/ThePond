<?php
/**
 * @brief		Widget PermissionCache Class: Used for widgets whose output depends on
 * 				the permissions of the user viewing
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		15 Nov 2013
 */

namespace IPS\Widget;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Widget PermissionCache Class
 */
class _PermissionCache extends \IPS\Widget
{
	/**
	 * @brief	cacheKey
	 */
	public $cacheKey = "";
	
	/**
	 * Constructor
	 *
	 * @param	String				$uniqueKey				Unique key for this specific instance
	 * @param	array				$configuration			Widget custom configuration
	 * @param	null|string|array	$access					Array/JSON string of executable apps (core=sidebar only, content=IP.Content only, etc)
	 * @param	null|string			$orientation			Orientation (top, bottom, right, left)
	 * @return	void
	 */
	public function __construct( $uniqueKey, array $configuration, $access=null, $orientation=null )
	{
		parent::__construct( $uniqueKey, $configuration, $access, $orientation );
		
		if( !$this->cacheKey )
		{
			/* We want to use the user's permissions array which will validate social groups, clubs and groups. But, we need to remove the individual member entry */
			$member = \IPS\Member::loggedIn() ?: new \IPS\Member;
			$permissions = $member->permissionArray();

			foreach( $permissions as $key => $entry )
			{
				if( mb_substr( $entry, 0, 1 ) === 'm' )
				{
					unset( $permissions[ $key ] );
					break;
				}
			}

			/* We sort to ensure the array is always in the same order */
			sort( $permissions );

			/* For permissions based cache we need to store once per language and permission config */
			$this->cacheKey = "widget_{$this->key}_" . $this->uniqueKey . '_' . md5( json_encode( $this->configuration ) . "_" . $member->language()->id . "_" . $member->skin . "_" . json_encode( $permissions ) . "_" . $this->orientation );
		}
	}
}