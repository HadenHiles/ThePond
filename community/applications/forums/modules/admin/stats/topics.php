<?php
/**
 * @brief		topics
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @subpackage	Forums
 * @since		18 Aug 2014
 */

namespace IPS\forums\modules\admin\stats;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * topics
 */
class _topics extends \IPS\Dispatcher\Controller
{
	/**
	 * Manage
	 *
	 * @return	void
	 */
	protected function manage()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'topics_manage' );

		$tabs		= array( 'total' => 'stats_topics_tab_total', 'byforum' => 'stats_topics_tab_byforum' );
		$activeTab	= ( isset( \IPS\Request::i()->tab ) and array_key_exists( \IPS\Request::i()->tab, $tabs ) ) ? \IPS\Request::i()->tab : 'total';

		if ( $activeTab === 'total' )
		{
			$chart = new \IPS\Helpers\Chart\Database( \IPS\Http\Url::internal( "app=forums&module=stats&controller=topics" ), 'forums_topics', 'start_date', '', array( 
					'isStacked' => FALSE,
					'backgroundColor' 	=> '#ffffff',
					'colors'			=> array( '#10967e' ),
					'hAxis'				=> array( 'gridlines' => array( 'color' => '#f5f5f5' ) ),
					'lineWidth'			=> 1,
					'areaOpacity'		=> 0.4
				),
				'AreaChart'
			);

			$chart->addSeries( \IPS\Member::loggedIn()->language()->addToStack( 'stats_new_topics' ), 'number', 'COUNT(*)', FALSE );
			$chart->title = \IPS\Member::loggedIn()->language()->addToStack( 'stats_topics_title' );
			$chart->availableTypes = array( 'AreaChart', 'ColumnChart', 'BarChart' );
		}
		else
		{
			$chart = new \IPS\Helpers\Chart\Database( \IPS\Http\Url::internal( "app=forums&module=stats&controller=topics&tab=" . $activeTab ), 'forums_topics', 'start_date', '', array( 
					'isStacked' => FALSE,
					'backgroundColor' 	=> '#ffffff',
					'hAxis'				=> array( 'gridlines' => array( 'color' => '#f5f5f5' ) ),
					'lineWidth'			=> 1,
					'areaOpacity'		=> 0.4,
					'chartArea'			=> array( 'width' => '70%', 'left' => '5%' ),
					'height'			=> 400,
					'trendlines'		=> array( 0 => array( 'type' => 'exponential', 'color' => '#000', 'lineWidth' => 2, 'opacity' => 0.3 ) )
				),
				'ColumnChart',
				'monthly',
				array( 'start' => ( new \IPS\DateTime )->sub( new \DateInterval('P90D') ), 'end' => new \IPS\DateTime ),
				array(),
				'byforum' 
			);

			$chart->title = \IPS\Member::loggedIn()->language()->addToStack( 'stats_topics_title_byforum' );
			$chart->availableTypes = array( 'ColumnChart' );

			$chart->groupBy = 'forum_id';

			foreach( \IPS\Db::i()->select( '*', 'forums_forums', array( 'topics>?', 0 ) ) as $forum )
			{
				$chart->addSeries( \IPS\Member::loggedIn()->language()->addToStack( 'forums_forum_' . $forum['id'] ), 'number', 'COUNT(*)', TRUE, $forum['id'] );
			}
		}

		if ( \IPS\Request::i()->isAjax() )
		{
			\IPS\Output::i()->output = (string) $chart;
		}
		else
		{	
			\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('menu__forums_stats_topics');
			\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'global', 'core' )->tabs( $tabs, $activeTab, (string) $chart, \IPS\Http\Url::internal( "app=forums&module=stats&controller=topics" ) );
		}
	}
}