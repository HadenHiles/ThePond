<?php
/**
 * @brief		Init for UTF8 Conversion
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		4 Sept 2013
 */

namespace IPSUtf8;

/**
 * Class to contain Invision Community autoloader and exception handler
 */
class IPSUtf8
{
	/**
	 * Initiate Invision Community UTF8 Converter, autoloader and exception handler
	 *
	 * @return	void
	 */
	public static function init()
	{
		/* Set timezone */
		date_default_timezone_set( 'UTC' );
		
		/* Set default MB internal encoding */
		mb_internal_encoding('UTF-8');
		
		/* Constants */
		\define( 'THIS_PATH', __DIR__ );
		
		$bits	= explode( DIRECTORY_SEPARATOR, THIS_PATH );
		$bit	= array_pop( $bits );
		$bit2	= array_pop( $bits );

		\define( 'ROOT_PATH', preg_replace( '#' . preg_quote( DIRECTORY_SEPARATOR, '#' ) . $bit2 . preg_quote( DIRECTORY_SEPARATOR, '#' ) . $bit . '$#', '', THIS_PATH ) );

		\define( 'IS_CLI', ( PHP_SAPI == 'cli' || empty( $_SERVER['REMOTE_ADDR'] ) ) ? true : false );
		
		/* Set Options - deliberately do not use root constants */
		if ( file_exists( './constants.php' ) )
		{
			@include_once( './constants.php' );
		}
		
		foreach( static::defaultConstants() AS $k => $v )
		{
			if ( !\defined( $k ) )
			{
				\define( $k, $v );
			}
		}

		@set_time_limit(0);
		
		/* Set autoloader */
		spl_autoload_register( '\IPSUtf8\IPSUtf8::autoloader', true, true );
		
		set_error_handler( '\IPSUtf8\IPSUtf8::errorHandler' );
		set_exception_handler( '\IPSUtf8\IPSUtf8::exceptionHandler' );
	}
	
	/**
	 * Default Constants
	 *
	 * @return	array
	 */
	public static function defaultConstants()
	{
		return array(
			'IPB_FIREWALL'				=> 1,		/* This is a workaround for an old exploit to prevent upgrade issues */
			'IPB_LOCK'					=> TRUE,	/* Lock conversion to only IP.Board Databases */
			'BYPASS_SAFETY_LOCK'		=> FALSE,
			'FORCE_CONVERT'				=> FALSE,	/* Enforce conversion */
			'FORCE_CONVERT_CHARSET'		=> NULL,
			'FORCE_CONVERT_METHOD'		=> NULL,
			'SOURCE_DB_CHARSET'			=> NULL,	/* If set, will force the source database connection to use this charset */
			'UTF8_INSERT_ONLY'			=> FALSE,	/* If true, and sql_charset is present and set to utf8 in conf_global.php, then the "fast" method will simply insert without converting. Useful for mixed collation databases where the utf8 tables are real utf8 */
			'FORCE_MULTI_CONVERT'		=> FALSE,	/* Forces the converter to use CONVERT(CONVERT(CONVERT(x AS <SOURCE_DB_CHARSET>) AS BINARY) AS UTF8) which is needed in some random old data sets */
			'LOG'						=> FALSE,	/* Log Conversion Data */
			'SOURCE_CHARSET_OVERRIDE'	=> NULL,	/* Force the converter to consider this value the value of IPB3's gb_char_set. Note that these are DOCUMENT Character Sets, not MySQL (so ISO-8859-1 for latin1, etc.) */
			'MULTI_NO_CMP'				=> FALSE,	/* When using the multi convert option, set this to true to not check the result of the conversion against the same data collated to utf8, which can cause an error in some situations. */
		);
	}
	
	/**
	 * Error Handler
	 *
	 * @param	int		$errno		Error number
	 * @param	errstr	$errstr		Error message
	 * @param	string	$errfile	File
	 * @param	int		$errline	Line
	 * @param	array	$trace		Backtract
	 * @return	void
	 */
	public static function errorHandler( $errno, $errstr, $errfile, $errline, $trace=NULL )
	{
		/* We don't care about these in production */
		if ( \in_array( $errno, array( E_WARNING, E_NOTICE, E_STRICT, E_DEPRECATED ) ) )
		{
			return;
		}
		
		throw new \ErrorException( $errstr, $errno, 0, $errfile, $errline );
	}
	
	/**
	 * Exception Handler
	 *
	 * @param	\Exception	$exception	Exception class
	 * @return	void
	 */
	public static function exceptionHandler( $exception )
	{
		/* Send a diagnostics report if we can */
		$trace = $exception->getTrace();
		if ( isset( $trace[0]['class'] ) )
		{
			self::diagnostics( $exception, $trace[0]['class'] );
		}
				
		/* And display an error screen */
		try
		{
			if ( IS_CLI )
			{
				print "\nError: " . $exception->getMessage() . "\nFile: " . str_replace( THIS_PATH, '', $exception->getFile() ) . "\nLine: " . $exception->getLine() . "\n";
			}
			else
			{
				/* @todo Elaborate on this for browser */
				print "\nError: " . $exception->getMessage() . "\nFile: " . str_replace( THIS_PATH, '', $exception->getFile() ) . "\nLine: " . $exception->getLine() . "\n"; 
			}
		}
		catch ( \Exception $e )
		{
			if( isset( $_SERVER['SERVER_PROTOCOL'] ) and \strstr( $_SERVER['SERVER_PROTOCOL'], '/1.0' ) !== false )
			{
				header( "HTTP/1.0 500 Internal Server Error" );
			}
			else
			{
				header( "HTTP/1.1 500 Internal Server Error" );
			}
		
			if ( IS_CLI )
			{
				print "\nError: " . $e->getMessage() . "\nString: " . $e->getString() . "\nFile: " . str_replace( THIS_PATH, '', $e->getFile() ) . "\nLine: " . $e->getLine() . "\n";
			}
			else
			{
				/* @todo Elaborate on this for browser */
				print "\nError: " . $e->getMessage() . "\nString: " . $e->getString() . "\nFile: " . str_replace( THIS_PATH, '', $e->getFile() ) . "\nLine: " . $e->getLine() . "\n"; 
			}
		}
		exit;
	}
	
	/**
	 * Diagnostics Reporting
	 *
	 * @param	\Exception	$exception	Exception
	 * @param	string		$class 		Class that caused the exception
	 * @return	void
	 */
	public static function diagnostics( $exception, $class )
	{
		try
		{
			@file_put_contents( THIS_PATH . '/tmp/error_' . date('Y-m-d') . '.cgi', "\n" . str_repeat( '-', 48 ) . "\n" . date('r') . "\n" . var_export( $exception, true ), FILE_APPEND );
		}
		catch ( \Exception $e ) {}
	}
	
	/**
	 * Autoloader
	 *
	 * @param	string	$classname	Class to load
	 * @return	void
	 */
	public static function autoloader( $classname )
	{			
		/* Separate by namespace */
		$bits = explode( '\\', $classname );
		$vendorName = array_shift( $bits );
								
		/* Work out what namespace we're in */
		$class = array_pop( $bits );
		$namespace = empty( $bits ) ? 'IPSUtf8' : ( 'IPSUtf8\\' . implode( '\\', $bits ) );
		
		if( !class_exists( "{$namespace}\\{$class}", FALSE ) )
		{
			/* Locate file */
			$path = THIS_PATH . '/';
			$sourcesDirSet = FALSE;
			
			foreach ( array_merge( $bits, array( $class ) ) as $i => $bit )
			{
				if( preg_match( "/^[a-z0-9]/", $bit ) )
				{
					if( $i === 0 )
					{
						$sourcesDirSet = TRUE;
					}
				}
				else if( $sourcesDirSet === FALSE )
				{
					$path .= 'system/';
					
					$sourcesDirSet = TRUE;
				}
			
				$path .= "{$bit}/";
			}
							
			/* Load it */
			$path = \substr( $path, 0, -1 ) . '.php';
			
			if( !file_exists( $path ) )
			{
				$path = \substr( $path, 0, -4 ) . \substr( $path, \strrpos( $path, '/' ) );
				
				if ( !file_exists( $path ) )
				{
					return FALSE;
				}
			}
			
			require_once( $path );
			
			/* Is it an interface? */
			if ( interface_exists( "{$namespace}\\{$class}", FALSE ) )
			{
				return;
			}
				
			/* Doesn't exist? */
			if( !class_exists( "{$namespace}\\{$class}", FALSE ) )
			{
				trigger_error( "Class {$classname} could not be loaded. Ensure it is in the correct namespace.", E_USER_ERROR );
			}
		}
	}

}

/* Init */
IPSUtf8::init();

/* Custom mb_ucfirst() function - eval'd so we can put into global namespace */
eval( '
function mb_ucfirst()
{
	$text = \func_get_arg( 0 );
	return mb_strtoupper( mb_substr( $text, 0, 1 ) ) . mb_substr( $text, 1 );
}
');