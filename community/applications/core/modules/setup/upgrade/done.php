<?php
/**
 * @brief		Upgrader: Finished Screen
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		20 May 2014
 */
 
namespace IPS\core\modules\setup\upgrade;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Upgrader: Finished Screen
 */
class _done extends \IPS\Dispatcher\Controller
{
	/**
	 * Finished
	 *
	 * @return	void
	 */
	public function manage()
	{
		\IPS\Output::clearJsFiles();
		
		if ( file_exists( \IPS\ROOT_PATH . '/uploads/logs/upgrader_data.cgi' ) )
		{
			@unlink( \IPS\ROOT_PATH . '/uploads/logs/upgrader_data.cgi' );
		}
		
		/* Reset theme maps to make sure bad data hasn't been cached by visits mid-setup */
		foreach( \IPS\Theme::themes() as $id => $set )
		{
			/* Update mappings */
			$set->css_map = array();
			$set->save();
		}

		/* Delete some variables we stored in our session */
		unset( $_SESSION['apps'] );

		if( isset( $_SESSION['upgrade_options'] ) )
		{
			unset( $_SESSION['upgrade_options'] );
		}
		
		if( isset( $_SESSION['sqlFinished'] ) )
		{
			unset( $_SESSION['sqlFinished'] );
		}

		if( isset( $_SESSION['uniqueKey'] ) )
		{
			unset( $_SESSION['uniqueKey'] );
		}

		unset( $_SESSION['key'] );

		/* Clear recent datastore logs to prevent an error message displaying immediately after upgrade */
		\IPS\Db::i()->delete( 'core_log', array( '`category`=? AND `time`>?', 'datastore', \IPS\DateTime::create()->sub( new \DateInterval( 'PT1H' ) )->getTimestamp() ) );
		
		/* Unset settings datastore to prevent any upgrade settings that were overridden becoming persistent */
		\IPS\Settings::i()->clearCache();
		
		/* IPS Cloud Sync */
		\IPS\IPS::resyncIPSCloud('Upgraded community');
		
		/* Remove any new version ACP Notifications */
		\IPS\core\AdminNotification::remove( 'core', 'NewVersion' );
		
		/* And show the complete page - the template handles this step special already so we don't have to output anything */
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('done');
	}
}