<?php
/**
* @brief		Image Proxy
* @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
* @copyright	(c) Invision Power Services, Inc.
* @license		https://www.invisioncommunity.com/legal/standards/
* @package		Invision Community
* @since		29 Jun 2015
* @version		SVN_VERSION_NUMBER
*/

/* Init */
define('REPORT_EXCEPTIONS', TRUE);
require_once str_replace( 'applications/core/interface/imageproxy/imageproxy.php', '', str_replace( '\\', '/', __FILE__ ) ) . 'init.php';
$url = \IPS\Request::i()->img;

/* Check for a valid key */
if ( !\IPS\Login::compareHashes( hash_hmac( "sha256", $url, \IPS\Settings::i()->site_secret_key ), (string) \IPS\Request::i()->key ) AND !\IPS\Login::compareHashes( hash_hmac( "sha256", $url, \IPS\Settings::i()->site_secret_key . 'i' ), (string) \IPS\Request::i()->key ) )
{
	\IPS\Output::i()->sendOutput( NULL, 403 );
}


/* Check the cache */
try
{
	$cacheEntry = \IPS\Db::i()->select( '*', 'core_image_proxy', array( 'md5_url=?', md5( $url ) ) )->first();

	/* If we have a cache entry, but it is over an hour old and the location is NULL, try to refetch */
	if( $cacheEntry['location'] === NULL AND $cacheEntry['cache_time'] < time() - 3600 )
	{
		\IPS\Db::i()->delete( 'core_image_proxy', array( 'md5_url=?', $cacheEntry['md5_url'] ) );
		throw new \UnderflowException;
	}
	
	if ( $cacheEntry['location'] )
	{
		/* Set the cache expiration time */
		$cacheExpires = new \DateTime;  // Use of \DateTime is intentional, do not replace with \IPS\DateTime
		$cacheExpires->setTimestamp( (int) $cacheEntry['cache_time'] );
		$cacheExpires->add( new \DateInterval( ( \IPS\Settings::i()->image_proxy_cache_period ) ? sprintf( 'P%dD', \IPS\Settings::i()->image_proxy_cache_period ) : 'P1Y' ) );

		$file = \IPS\File::get( 'core_Imageproxycache', $cacheEntry['location'] );
	}
	else
	{
		\IPS\Output::i()->sendOutput( NULL, 404 );
	}
}

/* Not in cache - fetch and store */
catch ( \UnderflowException $e )
{
	/* If the image proxy is disabled and the image isn't already stored, 301. This prevents images being stored when image proxy is disabled */
	if( !\IPS\Settings::i()->remote_image_proxy )
	{
		\IPS\Output::i()->redirect( \IPS\Http\Url::external( mb_substr( $url, 0, 2 ) === '//' ? "http:{$url}" : $url ) );
	}

	/* Set the cache expiration time */
	$cacheExpires = new \DateTime;  // Use of \DateTime is intentional, do not replace with \IPS\DateTime
	$cacheExpires->add( new \DateInterval( ( \IPS\Settings::i()->image_proxy_cache_period ) ? sprintf( 'P%dD', \IPS\Settings::i()->image_proxy_cache_period ) : 'P1Y' ) );

	/* First, let's store a placeholder row that we will later update - this prevents being able to DOS the server if the image is crazy */
	\IPS\Db::i()->replace( 'core_image_proxy', array(
		'md5_url'		=> md5( $url ),
		'location'		=> NULL,
		'cache_time'	=> time(),
	) );

	try
	{
		$output = \IPS\Http\Url::external( mb_substr( $url, 0, 2 ) === '//' ? "http:{$url}" : $url )->request( null, null, 5, true )->get();

		$extension	= mb_strtolower( mb_substr( $url, mb_strrpos( $url, '.' ) + 1 ) );

		/* If it's not an SVG, we need to check it's a valid image */
		if( $extension !== 'svg' )
		{
			$image = \IPS\Image::create( (string) $output );
			$imageExtension = $image->type;
		}
		else
		{
			$imageExtension = $extension;
		}
	}
	catch ( \Exception $e )
	{
		\IPS\Output::i()->sendOutput( NULL, 502 );
	}
	
	/* Work out an appropriate filename */
	if ( \in_array( $extension, \IPS\Image::$imageExtensions ) )
	{
		$filename = mb_substr( $url, mb_strrpos( $url, '/' ) + 1 );
		if ( mb_strlen( $filename ) > 200 )
		{
			$filename = mb_substr( $filename, 0, 150 ) . '.' . $extension;
		}
	}
	else
	{
		$filename = md5( mt_rand() ) . '.' . $imageExtension;
	}

	/* Cache */
	try
	{
		if( $imageExtension === 'svg' )
		{
			$file		= \IPS\File::create( 'core_Imageproxycache', $filename, (string) $output, 'imageproxy' );
			$location	= (string) $file;
		}
		else
		{
			$thumbDims	= \IPS\Settings::i()->attachment_image_size ? explode( 'x', \IPS\Settings::i()->attachment_image_size ) : array( 1000, 750 );
			$image->resizeToMax( $thumbDims[0], $thumbDims[1] );
			$imageData	= (string) $image;
			unset( $image, $output );
			$file		= \IPS\File::create( 'core_Imageproxycache', $filename, $imageData, 'imageproxy' );
			$location	= (string) $file;
		}
	}
	catch( \DomainException $e )
	{
		\IPS\Log::log( $e, 'imageproxy' );
		$location	= NULL;
	}

	\IPS\Db::i()->replace( 'core_image_proxy', array(
		'md5_url'		=> md5( $url ),
		'location'		=> $location,
		'cache_time'	=> time(),
	) );

	if( $location === NULL )
	{
		\IPS\Output::i()->sendOutput( NULL, 404 );
	}
}

try
{
	/* Output */
	\IPS\Output::i()->sendOutput( $file->contents(), 200, \IPS\File::getMimeType( $file->originalFilename ), array(
		'cache-control' => 'public, max-age=' . max( ( $cacheExpires->getTimestamp() - time() ), 0 ) . ', must-revalidate',
		'expires' => $cacheExpires->format( 'D, d M Y H:i:s \G\M\T' ),
		'pragma'  => 'public',
		/* Add a CSP to ensure an SVG image cannot execute javascript if viewed directly */
		'Content-Security-Policy' => "default-src 'none'; sandbox",
		'X-Content-Security-Policy' => "default-src 'none'; sandbox", // This is just for IE11
	) );
}
catch ( \RuntimeException $e )
{
	\IPS\Log::debug( "Failed fetching proxy image", 'imageProxy' );
}