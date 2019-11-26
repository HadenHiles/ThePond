<?php
/**
 * @brief		Sitemap generator Class
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		29 Aug 2013
 */

namespace IPS;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Sitemap generator class
 */
class _Sitemap
{
	/**
	 * @brief	Maximum number of entries to include per file
	 */
	const MAX_PER_FILE = 500;
	
	/**
	 * @brief	Count options
	 */
	public static $counts		= array( 0 => 0, 100 => 100, 500 => 500, 1000 => 1000, 5000 => 5000, 10000 => 10000 );

	/**
	 * @brief	Priority options
	 */
	public static $priorities	= array( '1.0' => '1.0', '0.9' => '0.9', '0.8' => '0.8', '0.7' => '0.7', '0.6' => '0.6', '0.5' => '0.5', '0.4' => '0.4', '0.3' => '0.3', '0.2' => '0.2', '0.1' => '0.1' );

	/**
	 * @brief	"Log" entries for this execution
	 */
	public $log	= array();

	/**
	 * @brief	URL to our sitemap index file
	 */
	public $sitemapUrl	= '';

	/**
	 * Constructor
	 *
	 * @return	void
	 */
	public function __construct()
	{
		/* Figure out the sitemap URL */
		$this->sitemapUrl	= ( \IPS\Settings::i()->sitemap_url ) ? rtrim( \IPS\Settings::i()->sitemap_url, '/' ) : rtrim( \IPS\Settings::i()->base_url, '/' ) . '/sitemap.php';
	}

	/**
	 * Build the sitemap index file
	 *
	 * @return	void
	 */
	public function buildNextSitemap()
	{
		/* Figure out supported sitemap files */
		$files		= array();
		$extensions	= \IPS\Application::allExtensions( 'core', 'Sitemap', new \IPS\Member, 'core' );
		foreach ( $extensions as $extension )
		{
			$files	= array_merge( $files, $extension->getFilenames() );
		}
		
		/* Delete any that aren't supported */
		\IPS\Db::i()->delete( 'core_sitemap', \IPS\Db::i()->in( 'sitemap', $files, TRUE ) );

		/* Now figure out which one hasn't run in the longest period of time. */
		$sitemapsNotBuilt = array_diff( $files, iterator_to_array( \IPS\Db::i()->select( 'sitemap', 'core_sitemap' ) ) );
		if ( \count( $sitemapsNotBuilt ) )
		{
			$toBuild = array_shift( $sitemapsNotBuilt );
		}
		else
		{
			try
			{
				$toBuild = \IPS\Db::i()->select( 'sitemap', 'core_sitemap', NULL, 'updated ASC', 1 )->first();
			}
			catch ( \UnderflowException $e ) { }
		}
		
		/* Do it */
		if( $toBuild )
		{
			/* Call the plugin to generate this sitemap file */
			foreach( $extensions as $extension )
			{
				if( \in_array( $toBuild, $extension->getFilenames() ) )
				{
					$extension->generateSitemap( $toBuild, $this );
				}
			}

			/* And ping search engines */
			$this->pingSearchEngines();
		}
	}

	/**
	 * Build a sitemap file and store it
	 *
	 * @param	string	$filename	Filename
	 * @param	array	$entries	The entries to add.  Each entry should be an array with at least the key 'url'. Optional keys 'lastmod', 'priority' and 'changefreq' are also supported.
	 * @param	int		$lastId		The last ID we built. This is used for content items to allow us to more efficiently fetch the next batch of items to build.
	 * @param	array	$namespaces	Array of additional namespaces to define
	 * @return	void
	 */
	public function buildSitemapFile( $filename, $entries, $lastId=0, $namespaces=array() )
	{
		/* Start XML Document, set encoding, and create the namespaced index element */
		$xmlWriter	= new \XMLWriter();
		$xmlWriter->openMemory();
		$xmlWriter->setIndent( TRUE );

		$xmlWriter->startDocument( '1.0', 'UTF-8' );
		$xmlWriter->startElementNS( NULL, 'urlset', "http://www.sitemaps.org/schemas/sitemap/0.9" );

		if( \count( $namespaces ) )
		{
			foreach( $namespaces as $prefix => $urn )
			{
				$xmlWriter->writeAttributeNS( 'xmlns', $prefix, NULL, $urn );
			}
		}

		$defaultLanguage = \IPS\Lang::load( \IPS\Lang::defaultLanguage() );

		if( \count( $entries ) )
		{
			foreach( $entries as $entry )
			{
				$xmlWriter->startElement( 'url' );

				$xmlWriter->startElement( 'loc' );
				$xmlWriter->text( preg_replace( '/^' . preg_quote( \IPS\Settings::i()->base_url, '/' ) . '/', '{base_url}', $entry['url'] ) );
				$xmlWriter->endElement();

				if( isset( $entry['lastmod'] ) AND $entry['lastmod'] )
				{
					$xmlWriter->startElement( 'lastmod' );
					$xmlWriter->text( ( $entry['lastmod'] instanceof \IPS\DateTime ) ? $entry['lastmod']->format( 'c', $defaultLanguage ) : \IPS\DateTime::ts( $entry['lastmod'] )->format( 'c', $defaultLanguage ) );
					$xmlWriter->endElement();
				}

				if( isset( $entry['priority'] ) AND $entry['priority'] )
				{
					$xmlWriter->startElement( 'priority' );
					$xmlWriter->text( $entry['priority'] );
					$xmlWriter->endElement();
				}

				if( isset( $entry['changefreq'] ) AND $entry['changefreq'] )
				{
					$xmlWriter->startElement( 'changefreq' );
					$xmlWriter->text( $entry['changefreq'] );
					$xmlWriter->endElement();
				}

				if( \count( $namespaces ) )
				{
					foreach( $entry as $key => $value )
					{
						foreach( $namespaces as $prefix => $urn )
						{
							if( mb_strpos( $key, $prefix . ':' ) === 0 )
							{
								$pieces = explode( ':', $key );
								$xmlWriter->startElementNS( $pieces[0], $pieces[1], NULL );

								foreach( $value as $k => $v )
								{
									$pieces = explode( ':', $k );
									$xmlWriter->startElementNS( $pieces[0], $pieces[1], NULL );

									if( \is_array( $v ) )
									{
										foreach( $v as $index => $value )
										{
											if( $index !== 0 )
											{
												$xmlWriter->writeAttribute( $index, $value );
											}
										}

										$xmlWriter->text( $v[0] );
									}
									else
									{
										$xmlWriter->text( $v );
									}

									$xmlWriter->endElement();
								}

								$xmlWriter->endElement();
							}
						}
					}
				}

				$xmlWriter->endElement();
			}

			/* End the XML document */
			$xmlWriter->endElement();
			$xmlWriter->endDocument();
			$content = $xmlWriter->outputMemory( TRUE );
		}
		else
		{
			$content = NULL;
		}

		/* Store */
		\IPS\Db::i()->replace( 'core_sitemap', array(
			'sitemap'	=> $filename,
			'data'		=> $content,
			'updated'	=> time(),
			'last_id'	=> $lastId
		) );
	}

	/**
	 * Ping search engines to notify of updates
	 *
	 * @return	void
	 * @note	Yahoo! search is being replaced by Bing and ask.com/moreover.com no longer accept sitemap submissions
	 */
	public function pingSearchEngines()
	{
		/* Search engines don't want to receive more than one ping in a 24 hour period */
		if( \IPS\Settings::i()->sitemap_last_ping > time() - 86400 )
		{
			$this->log[] = 'sitemap_ping_already24';
			return;
		}
		
		/* Update last ping time before the requests are made so if they time out, it won't attempt to try again for 24 hrs */
		\IPS\Settings::i()->changeValues( array( 'sitemap_last_ping' => time() ) );

		/* Ping Google */
		$response	= \IPS\Http\Url::external( "http://www.google.com/webmasters/tools/ping?sitemap=" . urlencode( $this->sitemapUrl ) )->request()->get();
		if( $response->httpResponseCode != 200 )
		{
			$this->log[] = 'sitemap_ping_google_fail';
		}
		else
		{
			$this->log[] = 'sitemap_ping_google_success';
		}

		/* Ping Bing */
		$response	= \IPS\Http\Url::external( "http://www.bing.com/ping?sitemap=" . urlencode( $this->sitemapUrl ) )->request()->get();

		if( $response->httpResponseCode != 200 )
		{
			$this->log[] = 'sitemap_ping_bing_fail';
		}
		else
		{
			$this->log[] = 'sitemap_ping_bing_success';
		}
	}
}