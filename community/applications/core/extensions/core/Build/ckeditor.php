<?php
/**
 * @brief		Build CKEditor for release
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		19 Apr 2013
 */

namespace IPS\core\extensions\core\Build;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Build CKEditor for release
 */
class _Ckeditor
{
	/**
	 * Build
	 *
	 * @return	void
	 * @throws	\RuntimeException
	 * @note	You can define JAVA_PATH in constants.php if you need to specify the path to your java executable
	 */
	public function build()
	{
		/* This is just to prevent CKEditor being build with every commit while we're doing deployment testing (which takes ages so it causes the deploy server to report a fail and usually isn't necessary) */
		if ( \defined( 'BUILD_TOOL_SCRIPT' ) )
		{
			return;
		}
		
		$path = \IPS\ROOT_PATH;

		$_javaPath	= '';

		if( \defined('\IPS\JAVA_PATH') AND \IPS\JAVA_PATH )
		{
			$_javaPath	= \IPS\JAVA_PATH;
		}

		/* This can take a while.... */
		set_time_limit(0);
		
		if( mb_strtolower( mb_substr( PHP_OS, 0, 3 ) ) === 'win' )
		{
			\exec( "rmdir \"{$path}/applications/core/interface/ckeditor/ckeditor\" /S /Q" );
		}
		else
		{
			\exec( "rm -R \"{$path}/applications/core/interface/ckeditor/ckeditor\" 2>&1" );
		}

		if ( is_dir( "{$path}/applications/core/interface/ckeditor/ckeditor" ) )
		{
			throw new \UnexpectedValueException( \IPS\Member::loggedIn()->language()->addToStack('dev_build_ckeditor_exists_err', FALSE, array( 'sprintf' => array( "{$path}/applications/core/interface/ckeditor/ckeditor" ) ) ) );
		}
		
		$command = "{$_javaPath}java -jar \"{$path}/applications/core/dev/ckbuilder.jar\" --build \"{$path}/applications/core/dev/ckeditor\" \"{$path}/applications/core/interface/ckeditor\" --build-config \"{$path}/applications/core/dev/ckeditor/build-config.js\" --no-zip --no-tar --overwrite 2>&1";
		$output	= array();
		\exec( $command, $output );

		if( mb_strtolower( mb_substr( PHP_OS, 0, 3 ) ) === 'win' )
		{
			\exec( "rmdir \"{$path}/applications/core/interface/ckeditor/ckeditor/samples\" /S /Q" );
		}
		else
		{
			\exec( "rm -R \"{$path}/applications/core/interface/ckeditor/ckeditor/samples\"" );
		}

		if( \in_array( 'Release process completed:', $output ) )
		{
			$this->finish();
			return;
		}


		throw new \UnexpectedValueException( \IPS\Member::loggedIn()->language()->addToStack('dev_build_ckeditor_err', FALSE, array( 'sprintf' => array( $command ) ) ) );
	}
	
	/**
	 * Finish Build
	 *
	 * @return	void
	 */
	protected function finish()
	{

	}
}