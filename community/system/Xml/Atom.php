<?php
/**
 * @brief		Class for managing Atom documents
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		5 Feb 2014
 */

namespace IPS\Xml;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Class for managing Atom documents
 */
class _Atom extends SimpleXML
{
	/**
	 * Get title
	 *
	 * @return	string
	 */
	public function title()
	{
		return $this->title;
	}
	
	/**
	 * Get articles
	 *
	 * @param	mixed	$guidKey	In previous versions, we encoded a key with the GUID. For legacy purposes, this can be passed here.
	 * @return	array
	 */
	public function articles( $guidKey=NULL )
	{
		$articles = array();
		
		foreach ( $this->entry as $item )
		{
			$link = NULL;
			if ( isset( $item->link ) )
			{
				/* Links are object nodes with attributes we need to parse - try to find the right link */
				$linkToCheck = NULL;
				foreach( $item->link as $_link )
				{
					$attributes = $_link->attributes();

					if( !isset( $attributes['rel'] ) OR ( isset( $attributes['rel'] ) AND $attributes['rel'] == 'alternate' ) )
					{
						$linkToCheck = $attributes['href'];
						break;
					}
				}

				if( $linkToCheck )
				{
					try
					{
						$link = \IPS\Http\Url::external( $linkToCheck );
					}
					catch ( \Exception $e ) {  }
				}
			}
			
			$articles[ md5( $guidKey . ( (string) $item->id ) ) ] = array(
				'title'		=> ( (string) $item->title ) ?: ( mb_substr( strip_tags( $item->content ), 0, 47 ) . '...' ),
				'content'	=> isset( $item->content ) ? (string) $item->content : (string) $item->title,
				'date'		=> \IPS\DateTime::ts( strtotime( $item->updated ) ),
				'link'		=> $link
			);
		}
		return $articles;
	}
}