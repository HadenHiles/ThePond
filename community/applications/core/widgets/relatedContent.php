<?php
/**
 * @brief		Related Content Widget
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		28 Apr 2014
 * @note		This widget is designed to be enabled on a page that displays a content item (e.g. a topic) to show related content based on tags
 */

namespace IPS\core\widgets;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Related Content Widget
 */
class _relatedContent extends \IPS\Widget
{
	/**
	 * @brief	Widget Key
	 */
	public $key = 'relatedContent';
	
	/**
	 * @brief	App
	 */
	public $app = 'core';
	
	/**
	 * @brief	Plugin
	 */
	public $plugin = '';

	/**
	 * Specify widget configuration
	 *
	 * @param	null|\IPS\Helpers\Form	$form	Form object
	 * @return	\IPS\Helpers\Form
	 */
	public function configuration( &$form=null )
 	{
		$form = parent::configuration( $form );
 		
		$form->add( new \IPS\Helpers\Form\Number( 'toshow', isset( $this->configuration['toshow'] ) ? $this->configuration['toshow'] : 5, TRUE ) );
		
		return $form;
 	}
 	
	/**
	 * Render a widget
	 *
	 * @return	string
	 */
	public function render()
	{
		if( !( \IPS\Dispatcher::i()->dispatcherController instanceof \IPS\Content\Controller ) )
		{
			return '';
		}

		$limit = isset ( $this->configuration['toshow'] ) ? $this->configuration['toshow'] : 5;

		$related	= \IPS\Dispatcher::i()->dispatcherController->getSimilarContent( $limit );

		if( $related === NULL or !\count( $related ) )
		{
			return '';
		}

		return $this->output( $related );
	}
}