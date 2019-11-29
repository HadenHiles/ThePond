<?php
/**
 * @brief		Dashboard extension: Registrations
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		23 Jul 2013
 */

namespace IPS\core\extensions\core\Dashboard;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * @brief	Dashboard extension: Registrations
 */
class _Registrations
{
	/**
	* Can the current user view this dashboard item?
	*
	* @return	bool
	*/
	public function canView()
	{
		return \IPS\Member::loggedIn()->hasAcpRestriction( 'core' , 'members', 'registrations_manage' );
	}

	/**
	 * Return the block to show on the dashboard
	 *
	 * @return	string
	 */
	public function getBlock()
	{
		/* We can use the registration stats controller for this */
		$chart	= new \IPS\Helpers\Chart\Database( 
			\IPS\Http\Url::internal( 'app=core&module=stats&controller=registrationstats' ), 
			'core_members', 
			'joined', 
			'', 
			array(
				'isStacked' => FALSE,
				'backgroundColor' 	=> '#ffffff',
				'colors'			=> array( '#10967e' ),
				'hAxis'				=> array( 'gridlines' => array( 'color' => '#f5f5f5' ) ),
				'lineWidth'			=> 1,
				'areaOpacity'		=> 0.4
			), 
			'ColumnChart', 
			'weekly',
			array( 'start' => \IPS\DateTime::create()->sub( new \DateInterval( 'P30D' ) ), 'end' => \IPS\DateTime::ts( time() ) ) 
		);
		$chart->addSeries( \IPS\Member::loggedIn()->language()->addToStack('stats_new_registrations'), 'number', 'COUNT(*)', FALSE );
		$chart->availableTypes = array( 'AreaChart', 'ColumnChart', 'BarChart' );

		/* fetch only successful registered members ; if this needs to be changed, please review the other areas where we have the name<>? AND email<>? condition */
		$chart->where[] = array( 'completed=?', true );
		
		/* Output */
		return \IPS\Theme::i()->getTemplate( 'dashboard' )->registrations( $chart );
	}
}