<?php
/**
 * @brief		File Handler: Amazon S3
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		12 Jul 2013
 */

namespace IPS\File;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * File Handler: Amazon S3
 */
class _Amazon extends \IPS\File
{
	/**
	 * An array of ( configuration_id => array( ext, etx ) ) extensions that require gzip versions storing
	 * Looks up $storageExtensions of extension classes
	 */
	protected static $gzipExtensions = array();
	
	/* !ACP Configuration */
	
	/**
	 * Settings
	 *
	 * @param	array	$configuration		Configuration if editing a setting, or array() if creating a setting.
	 * @return	array
	 */
	public static function settings( $configuration=array() )
	{
		$default = ( isset( $configuration['custom_url'] ) and ! empty( $configuration['custom_url'] ) ) ? TRUE : FALSE;
		
		return array(
			'bucket'		=> 'Text',
			'endpoint'		=> array( 'type' => 'Text', 'default' => 's3.amazonaws.com' ),
			'bucket_path'   => 'Text',
			'access_key'	=> 'Text',
			'secret_key'	=> 'Text',
			'toggle'	 => array( 'type' => 'YesNo', 'default' => $default, 'options' => array(
				'togglesOn' => array( 'Amazon_custom_url' )
			) ),
			'custom_url' => array( 'type' => 'Text', 'default' => '' )
		);
	}

	/**
	 * @brief	Temporarily stored endpoint - when testing settings, we may need to update it automagically
	 */
	protected static $updatedEndpoint	= NULL;
	
	/**
	 * Test Settings
	 *
	 * @param	array	$values	The submitted values
	 * @return	void
	 * @throws	\LogicException
	 */
	public static function testSettings( &$values )
	{
		$values['bucket_path'] = trim( $values['bucket_path'], '/' );
		$values['bucket'] = trim( $values['bucket'], '/' );
		
		$filename = md5( mt_rand() ) . '.ips.txt';
		
		try
		{
			$response = static::makeRequest( "test/{$filename}", 'PUT', $values, NULL, "OK" );
		}
		catch ( \IPS\Http\Request\Exception $e )
		{
			throw new \DomainException( \IPS\Member::loggedIn()->language()->addToStack( 'file_storage_test_error_amazon_unreachable', FALSE, array( 'sprintf' => array( $values['bucket'] ) ) ) );
		}
		
		if ( $response->httpResponseCode != 200 AND $response->httpResponseCode != 307 )
		{
			throw new \DomainException( \IPS\Member::loggedIn()->language()->addToStack( 'file_storage_test_error_amazon', FALSE, array( 'sprintf' => array( $values['bucket'], $response->httpResponseCode ) ) ) );
		}

		$response = static::makeRequest( "test/{$filename}", 'DELETE', $values, NULL );
		
		if ( $response->httpResponseCode == 403 )
		{
			throw new \DomainException( \IPS\Member::loggedIn()->language()->addToStack( 'file_storage_test_error_amazon_d403', FALSE, array( 'sprintf' => array( $values['bucket'], $response->httpResponseCode ) ) ) );
		}

		if( static::$updatedEndpoint !== NULL )
		{
			$values['endpoint']	= static::$updatedEndpoint;
			static::$updatedEndpoint = NULL;
		}
		
		if ( ! $values['toggle'] )
		{
			$values['custom_url'] = NULL;
		}
		
		if ( ! empty( $values['custom_url'] ) )
		{
			if ( mb_substr( $values['custom_url'], 0, 2 ) !== '//' AND mb_substr( $values['custom_url'], 0, 4 ) !== 'http' )
			{
				$values['custom_url'] = '//' . $values['custom_url'];
			}
			
			$test = $values['custom_url'];
			
			if ( mb_substr( $test, 0, 2 ) === '//' )
			{
				$test = 'http:' . $test;
			}
			
			if ( filter_var( $test, FILTER_VALIDATE_URL ) === false )
			{
				throw new \DomainException( \IPS\Member::loggedIn()->language()->addToStack( 'url_is_not_real', FALSE, array( 'sprintf' => array( $values['custom_url'] ) ) ) );
			}
		}
	}
	
	/**
	 * Determine if the change in configuration warrants a move process
	 *
	 * @param	array		$configuration	    New Storage configuration
	 * @param	array		$oldConfiguration   Existing Storage Configuration
	 * @return	boolean
	 */
	public static function moveCheck( $configuration, $oldConfiguration )
	{
		foreach( array( 'bucket', 'bucket_path' ) as $field )
		
		if ( $configuration[ $field ] !== $oldConfiguration[ $field ] )
		{
			return TRUE;
		}
		
		return FALSE;
	}

	/**
	 * Display name
	 *
	 * @param	array	$settings	Configuration settings
	 * @return	string
	 */
	public static function displayName( $settings )
	{
		return \IPS\Member::loggedIn()->language()->addToStack( 'filehandler_display_name', FALSE, array( 'sprintf' => array( \IPS\Member::loggedIn()->language()->addToStack('filehandler__Amazon'), $settings['bucket'] ) ) );
	}
	
	/* !File Handling */

	/**
	 * Constructor
	 *
	 * @param	array	$configuration	Storage configuration
	 * @return	void
	 */
	public function __construct( $configuration )
	{
		$this->container = 'monthly_' . date( 'Y' ) . '_' . date( 'm' );
		parent::__construct( $configuration );
	}
	
	/**
	 * Fetch the gzip extensions specific for $this->configurationId
	 *
	 * @return array
	 */
	public function getGzipExtensions()
	{
		if ( $this->storageExtension and ! array_key_exists( $this->storageExtension, static::$gzipExtensions ) )
		{
			static::$gzipExtensions[ $this->storageExtension ] = array();

			if( mb_strpos( $this->storageExtension, '_' ) !== FALSE )
			{
				$bits     = explode( '_', $this->storageExtension );
				$class    = '\IPS\\' . $bits[0] . '\extensions\core\FileStorage\\' . $bits[1];
			
				if ( isset( $class::$storeGzipExtensions ) and \is_array( $class::$storeGzipExtensions ) and \count( $class::$storeGzipExtensions ) )
				{
					static::$gzipExtensions[ $this->storageExtension ] = $class::$storeGzipExtensions;
				}
			}
		}
		
		return $this->storageExtension ? static::$gzipExtensions[ $this->storageExtension ] : array();
	}
	
	/**
	 * Is this a private file?
	 * This means that it is PUT with bucket owner read-only permissions which means it needs a signed URL to download
	 *
	 * @return boolean
	 */
	public function isPrivate()
	{
		if ( $this->storageExtension )
		{
			if ( mb_strpos( $this->storageExtension, '_' ) !== FALSE )
			{
				$bits     = explode( '_', $this->storageExtension );
				$class    = '\IPS\\' . $bits[0] . '\extensions\core\FileStorage\\' . $bits[1];
			
				if ( isset( $class::$isPrivate ) )
				{
					return $class::$isPrivate;
				}
			}
		}
		
		return false;
	}
	
	/**
	 * AWS does not gzip content when serving it, so if we want gzip compressed JS and CSS, we need to store a copy ourselves.
	 *
	 * @return boolean
	 */
	public function needsGzipVersion()
	{
		/* Use filename and not originalFilename so only true .js and .css files are checked, and not renamed uploads */
		return \in_array(  mb_substr( $this->filename, mb_strrpos( $this->filename, '.' ) + 1 ), $this->getGzipExtensions() );
	}
	
	/**
	 * Return the base URL
	 *
	 * @return string
	 */
	public function baseUrl()
	{
		return preg_replace( '#^http(s)?://#', '//', rtrim( ( empty( $this->configuration['custom_url'] ) ) ? static::buildBaseUrl( $this->configuration ) : $this->configuration['custom_url'], '/' ) );
	}
	
	/**
	 * Load File Data
	 *
	 * @return	void
	 */
	public function load()
	{
		parent::load();
		
		/* Change the public URL to the gzipped version if the browser supports it */
		if ( $this->needsGzipVersion() and ( isset( $_SERVER['HTTP_ACCEPT_ENCODING'] ) and \strpos( $_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip' ) !== false ) )
		{
			$this->url = new \IPS\Http\Url( (string) $this->url . '.gz' );
		}
	}
	
	/**
	 * Save File
	 *
	 * @return	void
	 */
	public function save()
	{
		$this->container = trim( $this->container, '/' );
		$this->url = $this->baseUrl() . ( $this->container ? "/{$this->container}" : '' ) . "/{$this->filename}";
		$path = $this->container ? "{$this->container}/{$this->filename}" : "{$this->filename}";
		$response  = static::makeRequest( $path, 'PUT', $this->configuration, $this->configurationId, (string) $this->contents(), $this->storageExtension, FALSE, $this->isPrivate() );

		if ( $response->httpResponseCode != 200 )
		{
			throw new \IPS\File\Exception( $path, \IPS\File\Exception::CANNOT_WRITE, $this->originalFilename, $this->getExtraMessage( $response, $path ), $this->getErrorInformation( $response ) );
		}
		
		/* Write the gzip version */
		if ( $this->needsGzipVersion() )
		{
			$response  = static::makeRequest( "{$path}.gz", 'PUT', $this->configuration, $this->configurationId, gzencode( (string) $this->contents() ) );
	
			if ( $response->httpResponseCode != 200 )
			{
				throw new \IPS\File\Exception( $path . '.gz', \IPS\File\Exception::CANNOT_WRITE, $this->originalFilename, $this->getExtraMessage( $response, $path . '.gz' ), $this->getErrorInformation( $response ) );
			}
		}
	}
	
	/**
	 * Get Contents
	 *
	 * @param	bool	$refresh	If TRUE, will fetch again
	 * @return	string
	 */
	public function contents( $refresh=FALSE )
	{
		if ( $this->contents === NULL or $refresh === TRUE )
		{
			$response = static::makeRequest( $this->container ? "{$this->container}/{$this->filename}" : "{$this->filename}", 'GET', $this->configuration, $this->configurationId );
			if ( $response->httpResponseCode == 404 )
			{
				throw new \IPS\File\Exception( $this->container ? "{$this->container}/{$this->filename}" : "{$this->filename}", \IPS\File\Exception::DOES_NOT_EXIST, $this->originalFilename, $this->getExtraMessage( $response, $this->container ? "{$this->container}/{$this->filename}" : "{$this->filename}" ), $this->getErrorInformation( $response ) );
			}
			elseif( $response->httpResponseCode == 403 )
			{
				throw new \IPS\File\Exception( $this->container ? "{$this->container}/{$this->filename}" : "{$this->filename}", \IPS\File\Exception::CANNOT_COPY, $this->originalFilename, $this->getExtraMessage( $response, $this->container ? "{$this->container}/{$this->filename}" : "{$this->filename}" ), $this->getErrorInformation( $response ) );
			}
			else
			{
				$this->contents = (string) $response;
			}
		}
		return $this->contents;
	}
	
	/**
	 * Delete
	 *
	 * @return	void
	 */
	public function delete()
	{
		$this->container = trim( $this->container, '/' );
		$path = $this->container ? "{$this->container}/{$this->filename}" : "{$this->filename}";
		
		$debug = array_map( function( $row ) {
			return array_filter( $row, function( $key ) {
				return \in_array( $key, array( 'class', 'function', 'line' ) );
			}, ARRAY_FILTER_USE_KEY );
		}, debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ) );
		
		try
		{
			$response = static::makeRequest( $path, 'DELETE', $this->configuration, $this->configurationId );
			
			/* Log deletion request */
			$this->log( "file_deletion", 'delete', $debug, 'log' );

			if ( $response->httpResponseCode == 204 )
			{
				/* Got a gzip version? */
				if ( $this->needsGzipVersion() )
				{
					static::makeRequest( "{$path}.gz", 'DELETE', $this->configuration, $this->configurationId );
				}
				
				/* Ok */
				return;
			}
			
			if ( $response->httpResponseCode != 200 )
			{
				$this->log( 'COULD_NOT_DELETE_FILE', 'delete', array( $response->httpResponseCode, $response->httpResponseText, $debug ), 'error' );
			}
		}
		catch( \IPS\Http\Request\Exception $e )
		{
			/* If there was a problem deleting the file, don't stop code execution just because of that */
			$this->log( 'HTTP_ERROR_DELETE_FILE', 'delete', array( $e->getCode(), $e->getMessage(), $debug ), 'error' );
		}
	}
	
	/**
	 * Delete Container
	 *
	 * @param	string	$container	Key
	 * @return	void
	 */
	public function deleteContainer( $container )
	{
		$_strip = array( '_strip_querystring' => TRUE, 'bucket_path' => NULL );
	
		if ( $this->configuration['bucket_path'] )
		{
			$container = $this->configuration['bucket_path'] . '/' . $container;
		}
		
		$response = static::makeRequest( "?prefix=" . urlencode( $container . "/" ), 'GET', array_merge( $this->configuration, $_strip ), $this->configurationId );
		
		/* Parse XML document */
		$document = \IPS\Xml\SimpleXML::loadString( $response );
		
		/* Loop over dom document */
		foreach( $document->Contents as $result )
		{
			if ( $this->configuration['bucket_path'] )
			{
				$result->Key = mb_substr( $result->Key, ( mb_strlen( $this->configuration['bucket_path'] ) + 1 ) );
			}
			
			static::makeRequest( $result->Key, 'DELETE', $this->configuration, $this->configurationId );
		}

		/* Log deletion request */
		$realContainer = $this->container;
		$this->container = $container;
		$this->log( "container_deletion", 'delete', NULL, 'log' );
		$this->container = $realContainer;
	}

	/**
	 * @brief Cached filesize
	 */
	protected $_cachedFilesize	= NULL;

	/**
	 * Get filesize (in bytes)
	 *
	 * @return	string
	 */
	public function filesize()
	{
		if( $this->_cachedFilesize !== NULL )
		{
			return $this->_cachedFilesize;
		}

		$this->container = trim( $this->container, '/' );

		$response = static::makeRequest( $this->container ? "{$this->container}/{$this->filename}" : "{$this->filename}", 'HEAD', $this->configuration, $this->configurationId );

		if ( $response->httpResponseCode != 200 OR !isset( $response->httpHeaders['Content-Length'] ) )
		{
			return parent::filesize();
		}
		
		$this->_cachedFilesize = $response->httpHeaders['Content-Length'];

		return $this->_cachedFilesize;
	}

	/* !Amazon Utility Methods */
	
	/**
	 * Generate a temporary download URL the user can be redirected to
	 *
	 * @param	$validForSeconds	int	The number of seconds the link should be valid for
	 * @return	\IPS\Http\Url
	 */
	public function generateTemporaryDownloadUrl( $validForSeconds = 1200 )
	{
		$fileUrl = ( $this->container ? ( rawurlencode( $this->container ) . '/' . rawurlencode( $this->filename ) ) : rawurlencode( $this->filename ) );
		$url = \IPS\Http\Url::external( static::buildBaseUrl( $this->configuration ) . $fileUrl );
		
		$headers = array();
		$queryString = array(
			'X-Amz-Expires'					=> $validForSeconds,
			'response-content-disposition'	=> 'attachment; filename*=UTF-8\'\'' . rawurlencode( $this->originalFilename ),
			'response-content-type'			=> static::getMimeType( $this->originalFilename ) . ";charset=UTF-8"
		);
		$signature = $this->signature( $this->configuration, 'GET', $fileUrl, $headers, $queryString, NULL, TRUE );
		$queryString['X-Amz-Signature'] = $signature;
		
		$url = $url->setQueryString( $queryString );
		
		$response = $url->request()->head();
		if ( $response->httpResponseCode == 400 )
		{	
			$xml = $url->request()->get()->decodeXml();
			if ( !isset( $xml->Region ) )
			{
				throw new \IPS\File\Exception( $fileUrl, \IPS\File\Exception::MISSING_REGION, $this->originalFilename );
			}
			
			$this->configuration['region'] = (string) $xml->Region;
			\IPS\Db::i()->update( 'core_file_storage', array( 'configuration' => json_encode( $this->configuration ) ), array( 'id=?', $this->configurationId ) );
			unset( \IPS\Data\Store::i()->storageConfigurations );
			return $this->generateTemporaryDownloadUrl( $validForSeconds );
		}
				
		return $url;
	}
	
	/**
	 * Sign and make request
	 *
	 * @param	string		$uri				The URI (relative to the bucket)
	 * @param	string		$verb				The HTTP verb to use
	 * @param	array 		$configuration		The configuration for this instance
	 * @param	int			$configurationId	The configuration ID
	 * @param	string|null	$content			The content to send
	 * @param	string|null	$storageExtension	Storage extension
	 * @param	bool		$skipExtraChecks	Skips the endpoint check (to prevent infinite looping)
	 * @param	bool		$isPrivate			This can be set to true to access/store private files (i.e. that are not publicly readable)
	 * @return	\IPS\Http\Response
	 * @throws	\IPS\Http\Request\Exception
	 */
	protected static function makeRequest( $uri, $verb, $configuration, $configurationId, $content=NULL, $storageExtension=NULL, $skipExtraChecks=FALSE, $isPrivate=false )
	{
		/* Amazon requires filename characters to be properly encoded - let's urlencode the filename here */
		$uriPieces	= explode( '/', $uri );
		$filename	= array_pop( $uriPieces );
		$uri		= ltrim( implode( '/', $uriPieces ) . '/' . rawurlencode( $filename ), '/' );
		
		/* Build a request */
		$request = \IPS\Http\Url::external( static::buildBaseUrl( $configuration ) . $uri )->request( \IPS\LONG_REQUEST_TIMEOUT, NULL, FALSE ); # Amazon will send a 301 header code, but no Location header, if we need to try another endpoint
		
		/* When using virtual hosted–style buckets with SSL, the SSL wild card certificate only matches buckets that do not contain periods. To work around this, use HTTP or write your own certificate verification logic. @link http://docs.aws.amazon.com/AmazonS3/latest/dev/BucketRestrictions.html */
		if ( \IPS\Request::i()->isSecure() and mb_strstr( $configuration['bucket'], '.' ) )
		{
			$request->sslCheck( FALSE );
		}
		
		/* Set headers. Make sure the file has the correct mime type, even if it is gzipped */
		$mimeUri = ( mb_substr( $uri, -3 ) === '.gz' ) ? mb_substr( $uri, 0, -3 ) : $uri;
		$headers = array(
			'Content-Type'	=> \IPS\File::getMimeType( $mimeUri ),
			'Content-MD5'	=> base64_encode( md5( $content, TRUE ) ),
			'X-Amz-Acl'		=> ( $isPrivate ? 'bucket-owner-read' : 'public-read' )
		);
		if ( $mimeUri !== $uri )
		{
			$headers['Content-Encoding'] = 'gzip';
		}

		/* If uploading a file, need to specify length and cache control */
		if( mb_strtoupper( $verb ) === 'PUT' )
		{
			$headers['Content-Length']	= \strlen( $content );

			$cacheSeconds = 3600 * 24 * 365;

			/* Custom Cache-Control */
			if( $storageExtension !== NULL AND mb_strpos( $storageExtension, '_' ) !== FALSE )
			{
				$bits     = explode( '_', $storageExtension );
				$class    = '\IPS\\' . $bits[0] . '\extensions\core\FileStorage\\' . $bits[1];

				if ( isset( $class::$cacheControlTtl ) and $class::$cacheControlTtl )
				{
					$cacheSeconds = $class::$cacheControlTtl;
				}
			}
			
			$headers['Cache-Control'] = 'public, max-age=' . $cacheSeconds;
		}
		
		/* We need to strip query string parameters for the signature, but not always (e.g. a subresource such as ?acl needs to be included and multi-
			object delete requests must include the query string params).  Let the callee decide to do this or not. */
		if( isset( $configuration['_strip_querystring'] ) AND $configuration['_strip_querystring'] === TRUE )
		{
			$uri = preg_replace( "/^(.*?)\?.*$/", "$1", $uri );
		}
		
		/* Sign the request */
		$queryString = array();
		$authorization = static::signature( $configuration, $verb, $uri, $headers, $queryString, $content );
		$headers['Authorization'] = $authorization;
		unset( $headers['Host'] );
		$request->setHeaders( $headers );
		
		/* Make the request */
		$verb = mb_strtolower( $verb );
		$response = $request->$verb( $content );

		/* If we are skipping extra checks, return response now */
		if( $skipExtraChecks )
		{
			return $response;
		}
		
		/* Change endpoint if necessary */
		if ( $response->httpResponseCode == 301 )
		{
			$xml = $response->decodeXml();
			if ( isset( $xml->Endpoint ) )
			{
				/* We have an endpoint, but if we called s3.amazonaws.com then it might be wrong. Try to detect the correct one. */
				$configuration['endpoint'] = 's3-us-west-1.amazonaws.com';

				$endpointResponse	= static::makeRequest( $uri, $verb, $configuration, $configurationId, $content, NULL, TRUE );
				$update				= FALSE;

				/* If the response code is 200, we got lucky and that's our endpoint */
				if( $endpointResponse->httpResponseCode == 200 )
				{
					$update = TRUE;
				}
				/* If it's a 301 response, we should be able to pull out the correct endpoint now */
				elseif( $endpointResponse->httpResponseCode == 301 )
				{
					$xml = $endpointResponse->decodeXml();
					if ( isset( $xml->Endpoint ) )
					{
						/* Strip out the bucket from the endpoint */
						$configuration['endpoint'] = preg_replace( '/^' . preg_quote( $configuration['bucket'], '/' ) . '\./', '', (string) $xml->Endpoint );
						$update = TRUE;
					}
				}

				/* If we need to update, do it now and return the result */
				if( $update === TRUE )
				{
					static::$updatedEndpoint	= $configuration['endpoint'];

					if ( $configurationId )
					{
						\IPS\Db::i()->update( 'core_file_storage', array( 'configuration' => json_encode( $configuration ) ), array( "id=?", $configurationId ) );
						unset( \IPS\Data\Store::i()->storageConfigurations );
					}
				}

				return static::makeRequest( $uri, $verb, $configuration, $configurationId, $content );
			}
		}
		
		/* Change region if necessary */
		if ( $response->httpResponseCode == 400 )
		{
			try
			{
				$xml = $response->decodeXml();
				if ( isset( $xml->Region ) )
				{
					$configuration['region'] = (string) $xml->Region;
					if ( $configurationId )
					{
						\IPS\Db::i()->update( 'core_file_storage', array( 'configuration' => json_encode( $configuration ) ), array( 'id=?', $configurationId ) );
						unset( \IPS\Data\Store::i()->storageConfigurations );
					}
					return static::makeRequest( $uri, $verb, $configuration, $configurationId, $content );
				}
			}
			catch ( \Exception $e ) { }
		}

		/* Return */
		return $response;		
	}
	
	/**
	 * Generate a v4 signature
	 *
	 * @param	array 		$configuration					The configuration for this instance
	 * @param	string		$verb							The HTTP verb that will be used in the request
	 * @param	string		$uri							The URI (relative to the bucket)
	 * @param	array		$headers						The request headers as an array
	 * @param	array		$queryString					The query string as an array
	 * @param	string|null	$content						The content to send
	 * @param	bool		$signatureIsForQueryString		If true, signature will be generated for query string. If false, header.
	 * @return	string
	 */
	protected static function signature( $configuration, $verb, $uri, &$headers = array(), &$queryString = array(), $content = NULL, $signatureIsForQueryString=FALSE )
	{
		/* Work out some basic stuff */
		$time = time();
		$region = ( isset( $configuration['region'] ) ? $configuration['region'] : 'us-east-1' );
		$scope = date( 'Ymd', $time ) . '/' . $region . '/s3/aws4_request';
		$contentSha256 = ( $signatureIsForQueryString and !$content ) ? 'UNSIGNED-PAYLOAD' : hash( 'sha256', $content );
				
		/* Figure out the canonical headers and query string */
		$headers['Host'] = ( isset( $configuration['endpoint'] ) ? $configuration['endpoint'] : "s3.amazonaws.com" );
		if ( $signatureIsForQueryString )
		{
			$queryString['X-Amz-Algorithm'] = 'AWS4-HMAC-SHA256';
			$queryString['X-Amz-Content-Sha256'] = $contentSha256;
			$queryString['X-Amz-Credential'] = $configuration['access_key'] . '/' . $scope;
			$queryString['X-Amz-Date'] = gmdate( 'Ymd', $time ) . 'T' . gmdate( 'His', $time ) . 'Z';
			$queryString['X-Amz-SignedHeaders'] = implode( ';', array_map( 'mb_strtolower', array_keys( $headers ) ) );
		}
		else
		{
			$headers['X-Amz-Content-Sha256'] = $contentSha256;
			$headers['X-Amz-Date'] = gmdate( 'Ymd', $time ) . 'T' . gmdate( 'His', $time ) . 'Z';
		}
		ksort( $queryString );
		ksort( $headers );
		$canonicalHeadersAsString = '';
		foreach ( $headers as $k => $v )
		{
			$canonicalHeadersAsString .= mb_strtolower( $k ) . ':' . trim( $v ) . "\n";
		}

		/* Task 1: Create a Canonical Request */
		$canonicalRequest = implode( "\n", array(
			mb_strtoupper( $verb ),
			'/' . $configuration['bucket'] . static::bucketPath( $configuration ) . '/' . ltrim( $uri, '/' ),
			http_build_query( $queryString, '', '&', PHP_QUERY_RFC3986 ),
			$canonicalHeadersAsString,
			implode( ';', array_map( 'mb_strtolower', array_keys( $headers ) ) ),
			$contentSha256
		) );
						
		/* Task 2: Create a String to Sign */
		$stringToSign = implode( "\n", array(
			'AWS4-HMAC-SHA256',
			gmdate( 'Ymd', $time ) . 'T' . gmdate( 'His', $time ) . 'Z',
			$scope,
			hash( 'sha256', $canonicalRequest )
		) );
						
		/* Task 3: Calculate Signature */
		$dateKey = hash_hmac( 'sha256', date( 'Ymd', $time ), 'AWS4' . $configuration['secret_key'], true );
		$dateRegionKey = hash_hmac( 'sha256', $region, $dateKey, true );
		$dateRegionServiceKey = hash_hmac( 'sha256', 's3', $dateRegionKey, true );
		$signingKey = hash_hmac( 'sha256', 'aws4_request', $dateRegionServiceKey, true );
		
		/* Return */
		$signature = hash_hmac( 'sha256', $stringToSign, $signingKey );
		if ( $signatureIsForQueryString )
		{
			return $signature;
		}
		else
		{
			return "AWS4-HMAC-SHA256 Credential={$configuration['access_key']}/{$scope},SignedHeaders=" . implode( ';', array_map( 'mb_strtolower', array_keys( $headers ) ) ) . ",Signature={$signature}";
		}
	}
	
	/**
	 * Build up the base Amazon URL
	 * @param   array   $configuration  Configuration data
	 * @return string
	 */
	public static function buildBaseUrl( $configuration )
	{
		return (
			\IPS\Request::i()->isSecure() ? "https" : "http" ) . "://"
			. ( isset( $configuration['endpoint'] ) ? $configuration['endpoint'] : "s3.amazonaws.com" )
			. "/{$configuration['bucket']}"
			. static::bucketPath( $configuration )
			. '/';
	}
	
	/**
	 * Get bucket path
	 *
	 * @param   array   $configuration  Configuration data
	 * @return	string
	 */
	protected static function bucketPath( $configuration )
	{
		if ( isset( $configuration['bucket_path'] ) AND ! empty( $configuration['bucket_path'] ) )
		{
			$bucketPath = trim( $configuration['bucket_path'], '/' );
			$bucketPath = rawurlencode( $bucketPath ); // The bucket path needs to be mostly url-encoded
			$bucketPath = str_replace( '%2F', '/', $bucketPath ); // Except for slashes because it can be multiple-levels deep
			return "/{$bucketPath}";
		}
		return '';
	}

	/**
	 * Remove orphaned files
	 *
	 * @param	int			$fileIndex		The file offset to start at in a listing
	 * @param	array	$engines	All file storage engine extension objects
	 * @return	array
	 */
	public function removeOrphanedFiles( $fileIndex, $engines )
	{
		/* Start off our results array */
		$results	= array(
			'_done'				=> FALSE,
			'fileIndex'			=> $fileIndex,
		);
				
		$checked	= 0;
		$skipped	= 0;
		$_strip		= array( '_strip_querystring' => TRUE, 'bucket_path' => NULL );

		if( $fileIndex )
		{
			$response	= static::makeRequest( "?marker={$fileIndex}&max-keys=100", 'GET', array_merge( $this->configuration, $_strip ), $this->configurationId );
		}
		else
		{
			$response	= static::makeRequest( "?max-keys=100", 'GET', array_merge( $this->configuration, $_strip ), $this->configurationId );
		}

		/* Parse XML document */
		$document	= \IPS\Xml\SimpleXML::loadString( $response );

		/* Loop over dom document */
		foreach( $document->Contents as $result )
		{
			$checked++;
			
			if ( $this->configuration['bucket_path'] )
			{
				$result->Key = mb_substr( $result->Key, ( mb_strlen( $this->configuration['bucket_path'] ) + 1 ) );
			}
			
			/* Next we will have to loop through each storage engine type and call it to see if the file is valid */
			foreach( $engines as $engine )
			{
				/* If this file is valid for the engine, skip to the next file */
				if( $engine->isValidFile( $result->Key ) )
				{
					continue 2;
				}
			}
			
			/* If we are still here, the file was not valid.  Delete and increment count. */
			$this->logOrphanedFile( $result->Key );

			$_lastKey = $result->Key;
		}

		if( $document->IsTruncated == 'true' AND $checked == 100 )
		{
			$results['fileIndex'] = $_lastKey;
		}

		/* Are we done? */
		if( !$checked OR $checked < 100 )
		{
			$results['_done'] = TRUE;
		}

		return $results;
	}

	/**
	 *  Retrieve any additional error information
	 *
	 * @param	\IPS\Http\Response	$response	The response object
	 * @param	string				$path		File path if available
	 * @return	string|null
	 */
	protected function getExtraMessage( $response, $path='' )
	{
		try
		{
			$xml = $response->decodeXml();
			if ( isset( $xml->Code ) )
			{
				switch( $xml->Code )
				{
					case 'RequestTimeTooSkewed':
						return 's3-RequestTimeTooSkewed';
					break;

					case 'AccessDenied':
					case 'AccountProblem':
					case 'AllAccessDisabled':
					case 'InvalidPayer':
					case 'NotSignedUp':
						return 's3-AccountProblem';
					break;

					case 'EntityTooSmall':
					case 'MissingRequestBodyError':
						return 's3-FileSizeSmall';
					break;

					case 'EntityTooLarge':
					case 'MaxMessageLengthExceeded':
						return 's3-FileSizeLarge';
					break;

					default:
						return 's3-GenericError';
					break;
				}
			}
		}
		catch( \Exception $e ) { }

		return null;
	}

	/**
	 * Get the error code and message from Amazon to log
	 *
	 * @param	\IPS\Http\Response	$response	The response object
	 * @return	string|null
	 */
	protected function getErrorInformation( $response )
	{
		try
		{
			$xml = $response->decodeXml();
			if ( isset( $xml->Code ) and isset( $xml->Message ) )
			{
				return $xml->Code . ': ' . $xml->Message;
			}
		}
		catch( \Exception $e ) { }

		return null;
	}
}