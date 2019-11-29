<?php
/**
 * @brief		Keyword Tracking
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		27 Mar 2017
 */

namespace IPS\core\modules\admin\activitystats;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Keyword Tracking
 */
class _keywords extends \IPS\Dispatcher\Controller
{	
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'keywords_manage' );
		parent::execute();
	}
	
	/**
	 * Manage
	 *
	 * @return	void
	 */
	protected function manage()
	{
		/* Show button to adjust settings */
		\IPS\Output::i()->sidebar['actions']['settings'] = array(
			'icon'		=> 'cog',
			'primary'	=> TRUE,
			'title'		=> 'manage_keywords',
			'link'		=> \IPS\Http\Url::internal( 'app=core&module=activitystats&controller=keywords&do=settings' ),
			'data'		=> array( 'ipsDialog' => '', 'ipsDialog-title' => \IPS\Member::loggedIn()->language()->addToStack('settings') )
		);

		/* Determine minimum date */
		$minimumDate = NULL;

		if( \IPS\Settings::i()->stats_keywords_prune )
		{
			$minimumDate = \IPS\DateTime::create()->sub( new \DateInterval( 'P' . \IPS\Settings::i()->stats_keywords_prune . 'D' ) );
		}

		/* Draw a chart */
		$options = json_decode( \IPS\Settings::i()->stats_keywords, true );

		if( !\is_array( $options ) )
		{
			$options = array();
		}

		$chart = new \IPS\Helpers\Chart\Database( 
			\IPS\Http\Url::internal( 'app=core&module=activitystats&controller=keywords' ), 
			'core_statistics', 
			'time', 
			'', 
			array( 
				'isStacked' => TRUE,
				'backgroundColor' 	=> '#ffffff',
				'colors'			=> array( '#10967e', '#ea7963', '#de6470', '#6b9dde', '#b09be4', '#eec766', '#9fc973', '#e291bf', '#55c1a6', '#5fb9da' ),
				'hAxis'				=> array( 'gridlines' => array( 'color' => '#f5f5f5' ) ),
				'lineWidth'			=> 1,
				'areaOpacity'		=> 0.4
			), 
			'LineChart', 
			'daily', 
			array( 'start' => \IPS\DateTime::create()->sub( new \DateInterval( 'P90D' ) ), 'end' => \IPS\DateTime::ts( time() ) ),
			array(),
			'',
			$minimumDate
		);
		$chart->where	= array( array( 'type=?', 'keyword' ) );
		$chart->groupBy	= 'value_4';

		if ( \is_array( $options ) )
		{
			foreach( $options as $k => $v )
			{
				$chart->addSeries( $v, 'number', 'COUNT(*)' );
			}
		}


		$chart->title = \IPS\Member::loggedIn()->language()->addToStack('keyword_usage_chart');
		$chart->availableTypes = array( 'AreaChart', 'ColumnChart', 'BarChart' );

		\IPS\Output::i()->output	= (string) $chart;

		if( \IPS\Request::i()->noheader AND \IPS\Request::i()->isAjax() )
		{
			return;
		}

		/* Create the table */
		$table = new \IPS\Helpers\Table\Db( 'core_statistics', \IPS\Http\Url::internal( 'app=core&module=activitystats&controller=keywords' ), array( array( 'type=?', 'keyword' ) ) );
		$table->langPrefix = 'keywordstats_';
		$table->quickSearch = 'value_4';

		/* Columns we need */
		$table->include = array( 'value_4', 'extra_data', 'author', 'time' );
		$table->mainColumn = 'value_4';
		$table->noSort	= array( 'extra_data' );

		$table->sortBy = $table->sortBy ?: 'time';
		$table->sortDirection = $table->sortDirection ?: 'desc';

		/* Custom parsers */
		$table->parsers = array(
			'time'			=> function( $val, $row )
			{
				return \IPS\DateTime::ts( $val )->localeDate();
			},
			'author'		=> function( $val, $row )
			{
				$data = json_decode( $row['extra_data'], TRUE );

				try
				{
					$class	= $data['class'];

					/* Check that the class exists */
					if( !class_exists( $class ) )
					{
						throw new \InvalidArgumentException;
					}

					$item	= $class::load( $data['id'] );

					return $item->author()->link();
				}
				catch( \Exception $e )
				{
					return \IPS\Member::loggedIn()->language()->addToStack( 'unknown' );
				}
			},
			'extra_data'	=> function( $val, $row )
			{
				$data = json_decode( $val, TRUE );

				try
				{
					$class	= $data['class'];

					/* Check that the class exists */
					if( !class_exists( $class ) )
					{
						throw new \InvalidArgumentException;
					}

					$item	= $class::load( $data['id'] );

					return \IPS\Theme::i()->getTemplate( 'global', 'core', 'global' )->basicUrl( $item->url(), TRUE, ( $item instanceof \IPS\Content\Comment ) ? $item->item()->mapped('title') : $item->mapped('title'), TRUE );
				}
				catch( \Exception $e )
				{
					return \IPS\Member::loggedIn()->language()->addToStack( 'content_deleted' );
				}
			},
		);

		/* Display */
		\IPS\Output::i()->title		= \IPS\Member::loggedIn()->language()->addToStack('menu__core_activitystats_keywords');
		\IPS\Output::i()->output	.= \IPS\Theme::i()->getTemplate( 'global', 'core' )->block( 'title', (string) $table, TRUE, 'ipsPad ipsSpacer_top' );
	}

	/**
	 * Prune Settings
	 *
	 * @return	void
	 */
	protected function settings()
	{
		$form = new \IPS\Helpers\Form;
		$form->add( new \IPS\Helpers\Form\Stack( 'stats_keywords', \IPS\Settings::i()->stats_keywords ? json_decode( \IPS\Settings::i()->stats_keywords, true ) : array(), FALSE, array( 'stackFieldType' => 'Text' ), NULL, NULL, NULL, 'stats_keywords' ) );
		$form->add( new \IPS\Helpers\Form\Interval( 'stats_keywords_prune', \IPS\Settings::i()->stats_keywords_prune, FALSE, array( 'valueAs' => \IPS\Helpers\Form\Interval::DAYS, 'unlimited' => 0, 'unlimitedLang' => 'never' ), NULL, \IPS\Member::loggedIn()->language()->addToStack('after'), NULL ) );
	
		if ( $values = $form->values() )
		{
			$values['stats_keywords'] = json_encode( array_unique( $values['stats_keywords'] ) );

			$form->saveAsSettings( $values );
			\IPS\Session::i()->log( 'acplog__statskeywords_settings' );
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=core&module=activitystats&controller=keywords' ), 'saved' );
		}
	
		\IPS\Output::i()->title		= \IPS\Member::loggedIn()->language()->addToStack('settings');
		\IPS\Output::i()->output 	= \IPS\Theme::i()->getTemplate('global')->block( 'settings', $form, FALSE );
	}
}