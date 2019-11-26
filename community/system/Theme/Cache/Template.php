<?php
/**
 * @brief		Magic Template Class for cachable templates
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		06 Aug 2013
 */

namespace IPS\Theme\Cache;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Magic Template Class for cachable templates
 */
class _Template extends \IPS\Theme\Template
{
	/**
	 * @brief	Source File
	 */
	protected static $file = NULL;
	
	/**
	 * @brief	Template object
	 */
	protected static $object = NULL;
	
	/**
	 * @brief	Theme object
	 */
	protected static $theme = NULL;
	
	/**
	 * @brief	Class name
	 */
	protected static $className = NULL;
	
	/**
	 * Constructor
	 *
	 * @param	string	$app				Application Key
	 * @param	string	$templateLocation	Template location (admin/public/etc.)
	 * @param	string	$templateName		Template Name
	 * @return	void
	 */
	public function __construct( $app, $templateLocation, $templateName )
	{
		parent::__construct( $app, $templateLocation, $templateName );
		
		static::$theme = \IPS\Theme::$memberTheme;
		
		if ( ! static::$theme->cache_key )
		 {
			 /* This theme has never received a cache key, so lets create one now */
			 static::$theme->resetCacheKey();
		 }
		 
		static::$className = 'class_' . $app . '_' . $templateLocation . '_' . $templateName;
		$key = \strtolower( 'template_' . static::$theme->id . '_' . \IPS\Theme::makeBuiltTemplateLookupHash( $app, $templateLocation, $templateName ) . '_' . \IPS\Theme::cleanGroupName( $templateName ) );
		
		static::$file = \IPS\Settings::i()->theme_disk_cache_path . "/" . $key . ".php";
	}
	
	/**
	 * Does this template bit exist
	 * This checks that it exists and also that the theme key matches
	 *
	 * @return boolean
	 */
	public function exists()
	{
		if ( file_exists( static::$file ) )
		{
			/* For FLOCK to work when setting, we need to flock when reading https://bugs.php.net/bug.php?id=52895 */
			try
			{
				$f = fopen( static::$file, 'r' );
				flock( $f, LOCK_SH );
				include_once( static::$file );
				flock( $f, LOCK_UN );
				fclose( $f );
			}
			catch( \ParseError $e )
			{
				@unlink( static::$file );

				/* Clear zend opcache if enabled */
				if ( \function_exists( 'opcache_invalidate' ) )
				{
					@opcache_invalidate( static::$file );
				}

				return false;
			}
			
			$class = "\\IPS\\Theme\\Cache\\" . \IPS\Theme::overloadHooks( static::$className, "IPS\\Theme\\Cache" );

			if ( class_exists( $class ) )
			{
				static::$object = new $class( $this->app, $this->templateLocation, $this->templateName );

				if ( static::$theme->cache_key != static::$object->cache_key )
				{
					@unlink( static::$file );
					
					/* Clear zend opcache if enabled */
					if ( \function_exists( 'opcache_invalidate' ) )
					{
						@opcache_invalidate( static::$file );
					}
					
					static::$object = NULL;
		
					return false;
				}
				
				return true;
			}
		}
		 
		return false;
	}
	 
	 /**
	 * Return the template object
	 *
	 * @throws \UnderflowException if the template bit does not exist
	 * @return boolean
	 */
	public function get()
	{
		if ( ! static::$object and ! $this->exists() )
		{
			throw new \UnderflowException;
		}
		
		return static::$object;
	}
	 
	 /**
	 * Write the template object
	 * @param	string	$template	PHP $template data to write
	 *
	 * @throws \RuntimeException If the template bit cannot be written
	 * @return void
	 */
	 public function set( $template )
	 {
		if ( ! is_writable( \IPS\Settings::i()->theme_disk_cache_path ) )
		{
			throw new \RuntimeException;
		}
		
		$template = "<?php\n" . $template;
		$result = (bool) @\file_put_contents( static::$file, $template, LOCK_EX );

		/* Sometimes LOCK_EX is unavailable and throws file_put_contents(): Exclusive locks are not supported for this stream.
			While we would prefer an exclusive lock, it would be better to write the file if possible. */
		if( ! $result )
		{
			@unlink( static::$file );
			$result = (bool) @\file_put_contents( static::$file, $template );
		}
		
		/* Clear zend opcache if enabled */
		if ( \function_exists( 'opcache_invalidate' ) )
		{
			@opcache_invalidate( static::$file );
		}
	}
}