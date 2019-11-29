<?php
/**
 * @brief		Tag Usage
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		24 Jan 2018
 */

namespace IPS\core\modules\admin\activitystats;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Tag Usage
 */
class _tags extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'tagsusage_manage' );
		parent::execute();
	}

	/**
	 * Show a graph of tag usage
	 *
	 * @return	void
	 */
	protected function manage()
	{
		$chart = new \IPS\Helpers\Chart\Database( \IPS\Http\Url::internal( "app=core&module=activitystats&controller=tags" ), 'core_tags', 'tag_added', '', array( 
				'isStacked'			=> FALSE,
				'backgroundColor' 	=> '#ffffff',
				'hAxis'				=> array( 'gridlines' => array( 'color' => '#f5f5f5' ) ),
				'lineWidth'			=> 1,
				'areaOpacity'		=> 0.4
			), 
			'AreaChart',
			'daily',
			array( 'start' => \IPS\DateTime::create()->sub( new \DateInterval( 'P1M' ) ), 'end' => 0 )
		);
		$chart->groupBy			= 'tag_text';
		$chart->availableTypes	= array( 'AreaChart', 'ColumnChart', 'BarChart', 'PieChart' );

		$where = $chart->where;
		$where[] = array( "tag_added>?", 0 );
		if ( $chart->start )
		{
			$where[] = array( "tag_added>?", $chart->start->getTimestamp() );
		}
		if ( $chart->end )
		{
			$where[] = array( "tag_added<?", $chart->end->getTimestamp() );
		}

		foreach( \IPS\Db::i()->select( 'tag_text', 'core_tags', $where, NULL, NULL, array( 'tag_text' ) ) as $tag )
		{
			$chart->addSeries( $tag, 'number', 'COUNT(*)', TRUE, $tag );
		}

		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('menu__core_activitystats_tags');
		\IPS\Output::i()->output = (string) $chart;
	}
}