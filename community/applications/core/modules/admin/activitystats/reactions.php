<?php
/**
 * @brief		reactions
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		19 Jan 2018
 */

namespace IPS\core\modules\admin\activitystats;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * reactions
 */
class _reactions extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'reactionsstats_manage' );
		parent::execute();
	}

	/**
	 * Reaction statistics
	 *
	 * @return	void
	 */
	protected function manage()
	{
		$tabs		= array(
			'type'		=> 'stats_reactions_by_type',
			'app'		=> 'stats_reactions_by_app',
			'list'		=> 'stats_reactions_top_content',
			'givers'	=> 'stats_reactions_top_givers',
			'getters'	=> 'stats_reactions_top_receivers',
		);
		$activeTab	= ( isset( \IPS\Request::i()->tab ) and array_key_exists( \IPS\Request::i()->tab, $tabs ) ) ? \IPS\Request::i()->tab : 'type';

		if ( $activeTab === 'type' OR $activeTab === 'app' )
		{
			$chart = new \IPS\Helpers\Chart\Database( \IPS\Http\Url::internal( "app=core&module=activitystats&controller=reactions&tab=" . $activeTab ), 'core_reputation_index', 'rep_date', '', array( 
					'isStacked'			=> FALSE,
					'backgroundColor' 	=> '#ffffff',
					'colors'			=> array( '#10967e', '#ea7963', '#de6470', '#6b9dde', '#b09be4', '#eec766', '#9fc973', '#e291bf', '#55c1a6', '#5fb9da' ),
					'hAxis'				=> array( 'gridlines' => array( 'color' => '#f5f5f5' ) ),
					'lineWidth'			=> 1,
					'areaOpacity'		=> 0.4
				), 
				'PieChart',
				'monthly',
				array( 'start' => 0, 'end' => 0 ),
				array(),
				$activeTab
			);

			if ( $activeTab === 'type' )
			{
				$chart->groupBy = 'reaction';

				foreach( \IPS\Content\Reaction::roots() as $reaction )
				{
					$chart->addSeries( $reaction->_title, 'number', 'COUNT(*)', TRUE, $reaction->id );
				}
			}
			else
			{
				$chart->groupBy = 'app';

				foreach( \IPS\Application::roots() as $app )
				{
					$chart->addSeries( $app->_title, 'number', 'COUNT(*)', TRUE, $app->directory );
				}
			}

			$chart->availableTypes = array( 'AreaChart', 'ColumnChart', 'BarChart', 'PieChart' );
		}
		else
		{
			/* Database table helpers don't support grouping, so we will manually fetch the results and then let the table helper display */
			$contentWhere	= array();
			$idToRatingMap	= array();

			if( $activeTab == 'list' )
			{
				$query = \IPS\Db::i()->select( 'MAX(id) as id, SUM(rep_rating) as rating', 'core_reputation_index', NULL, 'rating DESC', array( 0, 20 ), array( 'app', 'type', 'type_id' ) );
			}
			elseif( $activeTab == 'givers' )
			{
				$query = \IPS\Db::i()->select( 'MAX(id) as id, SUM(rep_rating) as rating', 'core_reputation_index', NULL, 'rating DESC', array( 0, 20 ), array( 'member_id' ) );
			}
			else
			{
				$query = \IPS\Db::i()->select( 'MAX(id) as id, SUM(rep_rating) as rating', 'core_reputation_index', NULL, 'rating DESC', array( 0, 20 ), array( 'member_received' ) );
			}

			foreach( $query as $reputation )
			{
				$contentWhere[]						= $reputation['id'];
				$idToRatingMap[ $reputation['id'] ]	= $reputation['rating'];
			}

			/* Create the table */
			$chart = new \IPS\Helpers\Table\Db( 'core_reputation_index', \IPS\Http\Url::internal( 'app=core&module=activitystats&controller=reactions&type=list' ), array( \IPS\Db::i()->in( 'id', $contentWhere ) ) );
			$chart->quickSearch = NULL;

			/* Columns we need */
			if( $activeTab == 'list' )
			{
				$chart->langPrefix = 'reactstats_';
				$chart->include = array( 'type_id', 'rep_date', 'member_id', 'rating' );
				$chart->mainColumn = 'type_id';
			}
			else
			{
				$chart->langPrefix = 'reactstatsm_';
				$column = ( $activeTab == 'givers' ) ? 'member_id' : 'member_received';
				$chart->include = array( $column, 'rep_date', 'rating' );
				$chart->mainColumn = $column;
			}

			$chart->noSort	= array( 'member_id', 'type_id', 'rep_date', 'rating' );

			$chart->sortBy = $chart->sortBy ?: 'rating';
			$chart->sortDirection = $chart->sortDirection ?: 'desc';

			/* Custom parsers */
			$chart->parsers = array(
				'rep_date'		=> function( $val, $row )
				{
					return \IPS\DateTime::ts( $val )->localeDate();
				},
				'member_id'		=> function( $val, $row )
				{
					return \IPS\Member::load( $val )->link();
				},
				'member_received'	=> function( $val, $row )
				{
					return \IPS\Member::load( $val )->link();
				},
				'rating'		=> function( $val, $row ) use ( $idToRatingMap )
				{
					/* Return the reputation total */
					return $idToRatingMap[ $row['id'] ];
				},
				'type_id'		=> function( $val, $row )
				{
					$class = $row['rep_class'];

					try
					{
						$item = $class::load( $row['type_id'] );

						return \IPS\Theme::i()->getTemplate( 'activitystats' )->contentCell( $item );
					}
					catch( \Exception $e )
					{
						return \IPS\Member::loggedIn()->language()->addToStack( 'unavailable' );
					}
				},
			);
		}

		if ( \IPS\Request::i()->isAjax() )
		{
			\IPS\Output::i()->output = (string) $chart;
		}
		else
		{	
			\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('menu__core_activitystats_reactions');
			\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'global', 'core' )->tabs( $tabs, $activeTab, (string) $chart, \IPS\Http\Url::internal( "app=core&module=activitystats&controller=reactions" ), 'tab', '', 'ipsPad' );
		}
	}
}