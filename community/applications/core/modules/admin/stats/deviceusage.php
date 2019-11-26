<?php
/**
 * @brief		Device usage
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		10 Jan 2018
 */

namespace IPS\core\modules\admin\stats;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Device usage
 */
class _deviceusage extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'deviceusage_manage' );
		parent::execute();
	}

	/**
	 * Device usage chart
	 *
	 * @return	void
	 */
	protected function manage()
	{
		/* Show button to adjust settings */
		\IPS\Output::i()->sidebar['actions']['settings'] = array(
			'icon'		=> 'cog',
			'title'		=> 'prunesettings',
			'link'		=> \IPS\Http\Url::internal( 'app=core&module=stats&controller=deviceusage&do=settings' ),
			'data'		=> array( 'ipsDialog' => '', 'ipsDialog-title' => \IPS\Member::loggedIn()->language()->addToStack('prunesettings') )
		);

		/* Determine minimum date */
		$minimumDate = NULL;

		if( \IPS\Settings::i()->stats_device_usage_prune )
		{
			$minimumDate = \IPS\DateTime::create()->sub( new \DateInterval( 'P' . \IPS\Settings::i()->stats_device_usage_prune . 'D' ) );
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

		$chart	= new \IPS\Helpers\Chart\Database( \IPS\Http\Url::internal( 'app=core&module=stats&controller=deviceusage' ), 'core_statistics', 'time', '', array( 
			'isStacked' => FALSE,
			'backgroundColor' 	=> '#ffffff',
			'colors'			=> array( '#10967e', '#ea7963', '#de6470', '#6b9dde' ),
			'hAxis'				=> array( 'gridlines' => array( 'color' => '#f5f5f5' ) ),
			'lineWidth'			=> 1,
			'areaOpacity'		=> 0.4
		 ), 'AreaChart', 'hourly' );

		$chart->where[]	= array( 'type=?', 'devices' );
		$chart->title = \IPS\Member::loggedIn()->language()->addToStack('stats_deviceusage_title');
		$chart->availableTypes = array( 'AreaChart', 'ColumnChart', 'BarChart' );
		$chart->enableHourly	= TRUE;

		$chart->addSeries( \IPS\Member::loggedIn()->language()->addToStack('stats_devices_mobiles'), 'number', 'SUM(value_1)', TRUE );
		$chart->addSeries( \IPS\Member::loggedIn()->language()->addToStack('stats_devices_tablets'), 'number', 'SUM(value_2)', TRUE );
		$chart->addSeries( \IPS\Member::loggedIn()->language()->addToStack('stats_devices_consoles'), 'number', 'SUM(value_3)', TRUE );
		$chart->addSeries( \IPS\Member::loggedIn()->language()->addToStack('stats_devices_desktops'), 'number', 'SUM(value_4)', TRUE );

		\IPS\Output::i()->title		= \IPS\Member::loggedIn()->language()->addToStack('menu__core_stats_deviceusage');
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
		$form->add( new \IPS\Helpers\Form\Interval( 'stats_device_usage_prune', \IPS\Settings::i()->stats_device_usage_prune, FALSE, array( 'valueAs' => \IPS\Helpers\Form\Interval::DAYS, 'unlimited' => 0, 'unlimitedLang' => 'never' ), NULL, \IPS\Member::loggedIn()->language()->addToStack('after'), NULL, 'prune_log_moderator' ) );
	
		if ( $values = $form->values() )
		{
			$form->saveAsSettings();
			\IPS\Session::i()->log( 'acplog__statsonlineusers_settings' );
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=core&module=stats&controller=deviceusage' ), 'saved' );
		}
	
		\IPS\Output::i()->title		= \IPS\Member::loggedIn()->language()->addToStack('prunesettings');
		\IPS\Output::i()->output 	= \IPS\Theme::i()->getTemplate('global')->block( 'prunesettings', $form, FALSE );
	}
}