<?php
/**
 * @brief		todaysBirthdays Widget
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @subpackage	Calendar
 * @since		18 Dec 2013
 */

namespace IPS\calendar\widgets;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * todaysBirthdays Widget
 */
class _todaysBirthdays extends \IPS\Widget
{
	/**
	 * @brief	Widget Key
	 */
	public $key = 'todaysBirthdays';
	
	/**
	 * @brief	App
	 */
	public $app = 'calendar';
		
	/**
	 * @brief	Plugin
	 */
	public $plugin = '';

	/**
	 * Constructor
	 *
	 * @param	string				$uniqueKey				Unique key for this specific instance
	 * @param	array				$configuration			Widget custom configuration
	 * @param	null|string|array	$access					Array/JSON string of executable apps (core=sidebar only, content=IP.Content only, etc)
	 * @param	null|string			$orientation			Orientation (top, bottom, right, left)
	 * @return	void
	 */
	public function __construct( $uniqueKey, array $configuration, $access=null, $orientation=null )
	{
		parent::__construct( $uniqueKey, $configuration, $access, $orientation );

		/* We need to adjust for timezone too */
		$this->cacheKey = "widget_{$this->key}_" . $this->uniqueKey . '_' . md5( json_encode( $configuration ) . "_" . \IPS\Member::loggedIn()->language()->id . "_" . \IPS\Member::loggedIn()->skin . "_" . json_encode( \IPS\Member::loggedIn()->groups ) . "_" . $orientation . \IPS\Member::loggedIn()->timezone );
	}
	
	/**
	 * Specify widget configuration
	 *
	 * @param	null|\IPS\Helpers\Form	$form	Form object
	 * @return	null|\IPS\Helpers\Form
	 */
	public function configuration( &$form=null )
 	{
		$form = parent::configuration( $form );
 		
		$form->add( new \IPS\Helpers\Form\YesNo( 'auto_hide', isset( $this->configuration['auto_hide'] ) ? $this->configuration['auto_hide'] : FALSE, FALSE ) );
		return $form;
 	} 

	/**
	 * Render a widget
	 *
	 * @return	string
	 */
	public function render()
	{
		if( !\IPS\Member::loggedIn()->canAccessModule( \IPS\Application\Module::get( 'calendar', 'calendar' ) ) )
		{
			return '';
		}

		$date		= \IPS\calendar\Date::getDate();
		$birthdays	= $date->getBirthdays( TRUE );
		$totalCount	= $date->getBirthdays( TRUE, TRUE );

		if( !isset( $birthdays[ $date->mon . $date->mday ] ) )
		{
			$birthdays[ $date->mon . $date->mday ]	= array();
		}

		/* Auto hiding? */
		if( !\count( $birthdays[ $date->mon . $date->mday ] ) AND isset( $this->configuration['auto_hide'] ) AND $this->configuration['auto_hide'] )
		{
			return '';
		}

		return $this->output( $birthdays[ $date->mon . $date->mday ], isset( $totalCount[ $date->mon . $date->mday ] ) ? $totalCount[ $date->mon . $date->mday ] : 0, $date );
	}
}