<?php
/**
 * @brief		ACP Dashboard
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		2 July 2013
 */

namespace IPS\core\modules\admin\overview;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * ACP Dashboard
 */
class _dashboard extends \IPS\Dispatcher\Controller
{
	/**
	 * Show the ACP dashboard
	 *
	 * @return	void
	 */
	protected function manage()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'view_dashboard' );
		
		\IPS\Output::i()->jsFiles = array_merge( \IPS\Output::i()->jsFiles, \IPS\Output::i()->js('admin_dashboard.js', 'core') );
		\IPS\Output::i()->cssFiles = array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( 'system/dashboard.css', 'core', 'admin' ) );

		/* Figure out which blocks we should show */
		$toShow	= $this->current( TRUE );
		
		/* Now grab dashboard extensions */
		$blocks	= array();
		$info	= array();
		foreach ( \IPS\Application::allExtensions( 'core', 'Dashboard', TRUE, 'core' ) as $key => $extension )
		{
			if ( !method_exists( $extension, 'canView' ) or $extension->canView() )
			{
				$info[ $key ]	= array(
							'name'	=> \IPS\Member::loggedIn()->language()->addToStack('block_' . $key ),
							'key'	=> $key,
							'app'	=> \substr( $key, 0, \strpos( $key, '_' ) )
				);

				if( method_exists( $extension, 'getBlock' ) )
				{
					foreach( $toShow as $row )
					{
						if( \in_array( $key, $row ) )
						{
							$blocks[ $key ]	= $extension->getBlock();
							break;
						}
					}
				}
			}
		}

		/* Determine if there are any new features to show */
		$latestFeatureId	= \IPS\Application::load( 'core' )->newFeature();
		$features			= array();
		try
		{
			$latestSeenFeature	= \IPS\Db::i()->select( 'feature_id', 'core_members_feature_seen', array( 'member_id=?', \IPS\Member::loggedIn()->member_id ) )->first();
		}
		catch( \UnderflowException $e )
		{
			$latestSeenFeature	= 0;
		}
		if( $latestFeatureId AND ( !$latestSeenFeature OR $latestSeenFeature < $latestFeatureId ) )
		{
			try
			{
				$features = json_encode( \IPS\Http\Url::ips('newFeatures')->setQueryString( array( 'since' => (int) $latestSeenFeature ) )->request()->get()->decodeJson() );

				/* Reset our last feature ID information so this doesn't show on subsequent page loads */
				\IPS\Db::i()->replace( 'core_members_feature_seen', array( 'member_id' => \IPS\Member::loggedIn()->member_id, 'feature_id' => $latestFeatureId ) );
			}
			catch( \RuntimeException $e ){}
		}
		
		/* Display */
		\IPS\Output::i()->title		= \IPS\Member::loggedIn()->language()->addToStack('dashboard');
		\IPS\Output::i()->customHeader = \IPS\Theme::i()->getTemplate( 'dashboard' )->dashboardHeader( $info, $blocks );
		\IPS\Output::i()->output	= \IPS\Theme::i()->getTemplate( 'dashboard' )->dashboard( $features, $toShow, $blocks, $info );
	}

	/**
	 * Reset the latest features we've seen so that we can see them again
	 *
	 * @return void
	 */
	public function whatsNew()
	{
		\IPS\Db::i()->delete( 'core_members_feature_seen', array( 'member_id=?', \IPS\Member::loggedIn()->member_id ) );

		\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=core&module=overview&controller=dashboard" ) );
	}

	/**
	 * Return a json-encoded array of the current blocks to show
	 *
	 * @param	bool	$return	Flag to indicate if the array should be returned instead of output
	 * @return	void
	 */
	public function current( $return=FALSE )
	{
		if( \IPS\Settings::i()->acp_dashboard_blocks )
		{
			$blocks = json_decode( \IPS\Settings::i()->acp_dashboard_blocks, TRUE );
		}
		else
		{
			$blocks = array();
		}

		$toShow	= isset( $blocks[ \IPS\Member::loggedIn()->member_id ] ) ? $blocks[ \IPS\Member::loggedIn()->member_id ] : array();

		if( !$toShow OR !isset( $toShow['main'] ) OR !isset( $toShow['side'] ) )
		{
			$toShow	= array(
				'main'		=> array( 'core_Registrations', 'core_BackgroundQueue' ),
				'side'		=> array( 'core_AdminNotes', 'core_OnlineUsers' ),
				'collapsed'	=> array( 'core_BackgroundQueue' ),
			);

			$blocks[ \IPS\Member::loggedIn()->member_id ]	= $toShow;

			\IPS\Settings::i()->changeValues( array( 'acp_dashboard_blocks' => json_encode( $blocks ) ) );
		}
		/* Upon initial upgrade to 4.3 the key won't exist, so apply to bg queue by default */
		elseif( !array_key_exists( 'collapsed', $toShow ) )
		{
			$toShow['collapsed']	= array( 'core_BackgroundQueue' );
		}

		if( $return === TRUE )
		{
			return $toShow;
		}

		\IPS\Output::i()->output		= json_encode( $toShow );
	}

	/**
	 * Return an individual block's HTML
	 *
	 * @return	void
	 */
	public function getBlock()
	{
		$output		= '';

		/* Loop through the dashboard extensions in the specified application */
		foreach( \IPS\Application::load( \IPS\Request::i()->appKey )->extensions( 'core', 'Dashboard', 'core' ) as $key => $_extension )
		{
			if( \IPS\Request::i()->appKey . '_' . $key == \IPS\Request::i()->blockKey )
			{
				if( method_exists( $_extension, 'getBlock' ) )
				{
					$output	= $_extension->getBlock();
				}

				break;
			}
		}

		\IPS\Output::i()->output	= $output;
	}

	/**
	 * Update our current block configuration/order
	 *
	 * @return	void
	 * @note	When submitted via AJAX, the array should be json-encoded
	 */
	public function update()
	{
		if( \IPS\Settings::i()->acp_dashboard_blocks )
		{
			$blocks = json_decode( \IPS\Settings::i()->acp_dashboard_blocks, TRUE );
		}
		else
		{
			$blocks = array();
		}

		$saveBlocks = \IPS\Request::i()->blocks;
		
		foreach( array( 'main', 'side', 'collapsed' ) as $saveKey )
		{
			if( !isset( $saveBlocks[ $saveKey ] ) )
			{
				$saveBlocks[ $saveKey ]	= array();
			}
		}
		
		$blocks[ \IPS\Member::loggedIn()->member_id ] = $saveBlocks;

		\IPS\Settings::i()->changeValues( array( 'acp_dashboard_blocks' => json_encode( $blocks ) ) );

		if( \IPS\Request::i()->isAjax() )
		{
			\IPS\Output::i()->output = 1;
			return;
		}

		\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=core&module=overview&controller=dashboard" ), 'saved' );
	}	
}