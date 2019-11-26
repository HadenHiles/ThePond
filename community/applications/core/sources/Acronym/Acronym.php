<?php
/**
 * @brief		Acronym Model
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		10 Sept 2013
 */

namespace IPS\core;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Acronym Model
 */
class _Acronym extends \IPS\Patterns\ActiveRecord
{
	/**
	 * @brief	Database Table
	 */
	public static $databaseTable = 'core_acronyms';
	
	/**
	 * @brief	Database Prefix
	 */
	public static $databasePrefix = '';
	
	/**
	 * @brief	Multiton Store
	 */
	protected static $multitons;
		
	/**
	 * @brief	[ActiveRecord] ID Database Column
	 */
	public static $databaseColumnId = 'a_id';
	
	/**
	 * Display Form
	 *
	 * @param	static|NULL	$acronym	Acronym we are currently editing
	 * @return	\IPS\Helpers\Form
	 */
	public static function form( $acronym )
	{
		/* Build form */
		$form = new \IPS\Helpers\Form();
	
		$form->add( new \IPS\Helpers\Form\Text( 'acronym_a_short', ( $acronym ) ? $acronym->a_short : NULL, TRUE ) );
		$form->add( new \IPS\Helpers\Form\Text( 'acronym_a_long', ( $acronym ) ? $acronym->a_long : NULL, TRUE ) );
		$form->add( new \IPS\Helpers\Form\YesNo( 'acronym_a_casesensitive', ( $acronym ) ? $acronym->a_casesensitive : NULL, FALSE, array(), NULL, NULL, NULL, 'acronym_a_casesensitive' ) );
	
		return $form;
	}
	
	/**
	 * Create from form
	 *
	 * @param	array	$values	Values from form
	 * @param	static	$current	The acronym we are currently editing
	 * @return	\IPS\core\Acronym
	 */
	public static function createFromForm( $values, $current )
	{
		if( $current )
		{
			$obj = static::load( $current->a_id );
		}
		else
		{
			$obj = new static;
		}
		
		$obj->a_short = $values['acronym_a_short'];
		$obj->a_long = $values['acronym_a_long'];
		$obj->a_casesensitive = $values['acronym_a_casesensitive'];

		$obj->save();
	
		return $obj;
	}
}