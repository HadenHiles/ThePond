<?php
/**
 * @brief		Messenger Stats
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		3 June 2013
 */

namespace IPS\core\modules\admin\messengerstats;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Messenger Stats
 */
class _pmstats extends \IPS\Dispatcher\Controller
{
	/**
	 * Manage Members
	 *
	 * @return	void
	 */
	protected function manage()
	{
		/* Check permission */
		\IPS\Dispatcher::i()->checkAcpPermission( 'messages_manage', 'core', 'members' );
		
		$chart = new \IPS\Helpers\Chart\Database( \IPS\Http\Url::internal( 'app=core&module=messengerstats&controller=pmstats' ), 'core_message_posts', 'msg_date', '', array( 
			'isStacked' => TRUE,
			'backgroundColor' 	=> '#ffffff',
			'colors'			=> array( '#10967e', '#ea7963' ),
			'hAxis'				=> array( 'gridlines' => array( 'color' => '#f5f5f5' ) ),
			'lineWidth'			=> 1,
			'areaOpacity'		=> 0.4
		) );
		$chart->addSeries( \IPS\Member::loggedIn()->language()->addToStack('new_conversations'), 'number', 'SUM(msg_is_first_post)' );
		$chart->addSeries( \IPS\Member::loggedIn()->language()->addToStack('mt_replies'), 'number', '( count(*) - SUM(msg_is_first_post) )' );
		$chart->title = \IPS\Member::loggedIn()->language()->addToStack('stats_messages_title');
		$chart->availableTypes = array( 'AreaChart', 'ColumnChart', 'BarChart' );
	
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('menu__core_messengerstats_pmstats');
		\IPS\Output::i()->output = (string) $chart;
	}
}