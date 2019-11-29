<?php
/**
 * @brief		Delicious share link
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		11 Sept 2013
 * @see			<a href='https://delicious.com/tools'>Delicious button documentation</a>
 */

namespace IPS\Content\ShareServices;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Delicious share link
 * @note	Delicious does not provide any method to control the locale/language
 */
class _Delicious extends \IPS\Content\ShareServices
{
	/**
	 * Return the HTML code to show the share link
	 *
	 * @return	string
	 */
	public function __toString()
	{
		$url = \IPS\Http\Url::external( "https://del.icio.us/save?jump=close&noui=1&v=5" )->setQueryString( 'provider', urlencode( \IPS\Settings::i()->board_name ) );
				
		if( $this->url !== NULL )
		{
			$url = $url->setQueryString( 'url', (string) $this->url );
		}

		if( $this->title !== NULL )
		{
			$url = $url->setQueryString( 'title', $this->title );
		}
		
		return \IPS\Theme::i()->getTemplate( 'sharelinks', 'core' )->delicious( $url );
	}
}