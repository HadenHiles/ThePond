<?php
/**
 * @brief		Wrapper class for managing DOMDocument objects
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		5 July 2016
 */

namespace IPS\Xml;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Wrapper class for managing DOMDocument objects
 */
class _DOMDocument extends \DOMDocument
{
	/**
	 * Load XML from a file
	 *
	 * @param	string	$filename	The filename
	 * @param	int		$options	Bitmask of LIBXML_* constants
	 * @return	bool
	 */
	public function load( $filename, $options=0 )
	{
		return static::loadXML( file_get_contents( $filename ), $options );
	}

	/**
	 * Load HTML from a string 
	 *
	 * @param	string	$source		The HTML to open
	 * @param	int		$options	Bitmask of LIBXML_* constants
	 * @return	bool
	 * @note	We are disabling the entity loader after opening the content to prevent XXE
	 */
	public function loadHTML( $source, $options=0 )
	{
		libxml_use_internal_errors( TRUE );
		
		/* Turn off external entity loader to prevent XXE */
		$entityLoaderValue = libxml_disable_entity_loader( TRUE );
		
		/* Load it */
		$opened = parent::loadHTML( $source, $options );
		
		/* Turn external entity loader back to what it was before so we're not messing with other
			PHP scripts on this server */
		libxml_disable_entity_loader( $entityLoaderValue );
		
		/* Return */
		return $opened;
	}

	/**
	 * Load HTML from a file
	 *
	 * @param	string	$filename	The filename
	 * @param	int		$options	Bitmask of LIBXML_* constants
	 * @return	bool
	 * @note	We are disabling the entity loader after opening the content to prevent XXE
	 */
	public function loadHTMLFile( $filename, $options=0 )
	{		
		return static::loadHTML( file_get_contents( $filename ), $options );
	}

	/**
	 * Load XML from a string 
	 *
	 * @param	string	$source		The HTML to open
	 * @param	int		$options	Bitmask of LIBXML_* constants
	 * @return	bool
	 */
	public function loadXML( $source, $options=0 )
	{
		libxml_use_internal_errors( TRUE );
		
		/* Turn off external entity loader to prevent XXE */
		$entityLoaderValue = libxml_disable_entity_loader( TRUE );
		
		/* Load it */
		$opened = parent::loadXML( $source, $options );
		
		/* Turn external entity loader back to what it was before so we're not messing with other
			PHP scripts on this server */
		libxml_disable_entity_loader( $entityLoaderValue );

		/* Return */
		return $opened;
	}

	/**
	 * Prefix HTML content with certain HTML to force DOMDocument to treat the content as UTF-8-encoded HTML
	 *
	 * @param	string	$content	HTML content to prefix
	 * @return	string
	 */
	static public function wrapHtml( $content )
	{
		return "<!DOCTYPE html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'/></head>" . $content;
	}
}