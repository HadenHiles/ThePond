<?php
/**
 * @brief		Digg share link
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		11 Sept 2013
 * @see			<a href='http://ftllc.com/articles/social-media-buttons.php'>Digg button</a> (This was the best I could find...Digg has removed most of their button documentation it seems)
 */

namespace IPS\Content\ShareServices;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Digg share link
 * @note	Digg does not provide any method to control the locale/language
 * @note	Digg does not provide any method to control the URL or specify any title
 */
class _Digg extends \IPS\Content\ShareServices
{
	/**
	 * Return the HTML code to show the share link
	 *
	 * @return	string
	 */
	public function __toString()
	{
		return \IPS\Theme::i()->getTemplate( 'sharelinks', 'core' )->digg( \IPS\Http\Url::external( 'http://digg.com/submit' )->setQueryString( 'url', (string) $this->url ) );
	}
}