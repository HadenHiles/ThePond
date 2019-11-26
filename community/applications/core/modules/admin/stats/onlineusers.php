<?php
/**
 * @brief		onlineusers
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		23 Mar 2017
 */

namespace IPS\core\modules\admin\stats;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * onlineusers
 */
class _onlineusers extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'onlineusers_manage' );
		parent::execute();
	}

	/**
	 * Online users activity chart
	 *
	 * @return	void
	 */
	protected function manage()
	{
		/* Show button to adjust settings */
		\IPS\Output::i()->sidebar['actions']['settings'] = array(
			'icon'		=> 'cog',
			'title'		=> 'prunesettings',
			'link'		=> \IPS\Http\Url::internal( 'app=core&module=stats&controller=onlineusers&do=settings' ),
			'data'		=> array( 'ipsDialog' => '', 'ipsDialog-title' => \IPS\Member::loggedIn()->language()->addToStack('prunesettings') )
		);

		/* Determine minimum date */
		$minimumDate = NULL;

		if( \IPS\Settings::i()->stats_online_users_prune )
		{
			$minimumDate = \IPS\DateTime::create()->sub( new \DateInterval( 'P' . \IPS\Settings::i()->stats_online_users_prune . 'D' ) );
		}

		/* We can't retrieve any stats prior to the new tracking being implemented */
		try
		{
			$oldestLog = \IPS\Db::i()->select( 'MIN(time)', 'core_statistics', array( 'type=?', 'online_users' ) )->first();

			if( !$minimumDate OR $oldestLog < $minimumDate->getTimestamp() )
			{
				$minimumDate = \IPS\DateTime::ts( $oldestLog );
			}
		}
		catch( \UnderflowException $e )
		{
			/* We have nothing tracked, set minimum date to today */
			$minimumDate = \IPS\DateTime::create();
		}

		$chart = new \IPS\Helpers\Chart\Callback( 
			\IPS\Http\Url::internal( 'app=core&module=stats&controller=onlineusers' ), 
			array( $this, 'getResults' ),
			'', 
			array( 
				'isStacked' => TRUE,
				'backgroundColor' 	=> '#ffffff',
				'colors'			=> array( '#10967e', '#ea7963' ),
				'hAxis'				=> array( 'gridlines' => array( 'color' => '#f5f5f5' ) ),
				'lineWidth'			=> 1,
				'areaOpacity'		=> 0.4
			), 
			'AreaChart', 
			'none',
			array( 'start' => \IPS\DateTime::ts( time() - ( 60 * 60 * 24 * 30 ) ), 'end' => \IPS\DateTime::create() ),
			'',
			$minimumDate
		);

		$chart->addSeries( \IPS\Member::loggedIn()->language()->addToStack('members'), 'number' );
		$chart->addSeries( \IPS\Member::loggedIn()->language()->addToStack('guests'), 'number' );

		$chart->title = \IPS\Member::loggedIn()->language()->addToStack('stats_onlineusers_title');
		$chart->availableTypes	= array( 'AreaChart', 'ColumnChart', 'BarChart' );
		$chart->showIntervals	= FALSE;

		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('menu__core_stats_onlineusers');
		\IPS\Output::i()->output	= (string) $chart;
	}

	/**
	 * Prune Settings
	 *
	 * @return	void
	 */
	protected function settings()
	{
		$form = new \IPS\Helpers\Form;
		$form->add( new \IPS\Helpers\Form\Interval( 'stats_online_users_prune', \IPS\Settings::i()->stats_online_users_prune, FALSE, array( 'valueAs' => \IPS\Helpers\Form\Interval::DAYS, 'unlimited' => 0, 'unlimitedLang' => 'never' ), NULL, \IPS\Member::loggedIn()->language()->addToStack('after'), NULL, 'prune_log_moderator' ) );
	
		if ( $values = $form->values() )
		{
			$form->saveAsSettings();
			\IPS\Session::i()->log( 'acplog__statsonlineusers_settings' );
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=core&module=stats&controller=onlineusers' ), 'saved' );
		}
	
		\IPS\Output::i()->title		= \IPS\Member::loggedIn()->language()->addToStack('prunesettings');
		\IPS\Output::i()->output 	= \IPS\Theme::i()->getTemplate('global')->block( 'prunesettings', $form, FALSE );
	}

	/**
	 * Fetch the results
	 *
	 * @param	\IPS\Helpers\Chart\Callback	$chart	Chart object
	 * @return	array
	 */
	public function getResults( $chart )
	{
		$where = array( array( 'type=?', 'online_users' ), array( "time>?", 0 ) );

		if ( $chart->start )
		{
			$where[] = array( "time>?", $chart->start->getTimestamp() );
		}
		if ( $chart->end )
		{
			$where[] = array( "time<?", $chart->end->getTimestamp() );
		}

		$results = array();

		foreach( \IPS\Db::i()->select( '*', 'core_statistics', $where, 'time ASC' ) as $row )
		{
			if( !isset( $results[ $row['time'] ] ) )
			{
				$results[ $row['time'] ] = array( 
					'time' => $row['time'], 
					\IPS\Member::loggedIn()->language()->get('members') => 0,
					\IPS\Member::loggedIn()->language()->get('guests') => 0
				);
			}

			if( $row['value_4'] == 'members' )
			{
				$results[ $row['time'] ][ \IPS\Member::loggedIn()->language()->get('members') ] = $row['value_1'];
			}

			if( $row['value_4'] == 'guests' )
			{
				$results[ $row['time'] ][ \IPS\Member::loggedIn()->language()->get('guests') ] = $row['value_1'];
			}
		}

		return $results;
	}
}