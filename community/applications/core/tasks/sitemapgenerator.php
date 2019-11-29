<?php
/**
 * @brief		Task to generate sitemaps
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		30 Aug 2013
 */

namespace IPS\core\tasks;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Task to generate sitemaps
 */
class _sitemapgenerator extends \IPS\Task
{
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		try
		{
			$generator	= new \IPS\Sitemap;
			$generator->buildNextSitemap();
		}
		catch( \Exception $e )
		{
			\IPS\Log::log( $e, 'sitemap_generator' );
			return $e->getMessage();
		}

		if( \count( $generator->log ) )
		{
			return $generator->log;
		}
		else
		{
			return null;
		}
	}
}