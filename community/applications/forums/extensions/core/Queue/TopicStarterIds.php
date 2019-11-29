<?php
/**
 * @brief		Background Task
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @subpackage	Forums
 * @since		04 Jan 2016
 */

namespace IPS\forums\extensions\core\Queue;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Background Task
 */
class _TopicStarterIds
{
	/**
	 * @brief Number of topics to build per cycle
	 */
	public $perCycle	= \IPS\REBUILD_QUICK;
	
	/**
	 * Parse data before queuing
	 *
	 * @param	array	$data
	 * @return	array
	 */
	public function preQueueData( $data )
	{
		try
		{			
			$data['count']		= \IPS\Db::i()->select( 'COUNT(*)', 'forums_topics' )->first();
		}
		catch( \Exception $ex )
		{
			throw new \OutOfRangeException;
		}
		
		if( $data['count'] == 0 )
		{
			return null;
		}

		return $data;
	}

	/**
	 * Run Background Task
	 *
	 * @param	mixed						$data	Data as it was passed to \IPS\Task::queue()
	 * @param	int							$offset	Offset
	 * @return	int							New offset
	 * @throws	\IPS\Task\Queue\OutOfRangeException	Indicates offset doesn't exist and thus task is complete
	 */
	public function run( &$data, $offset )
	{
		$count = 0;

		foreach( \IPS\Db::i()->select( '*', 'forums_topics', array(), 'tid ASC', array( $offset, $this->perCycle ) ) as $topic )
		{
			$count++;

			try
			{
				$firstPost = \IPS\Db::i()->select( 'author_id', 'forums_posts', array( 'new_topic=? AND topic_id=?', 1, $topic['tid'] ), 'pid ASC', array( 0, 1 ) )->first();

				\IPS\Db::i()->update( 'forums_topics', array( 'starter_id' => $firstPost ), array( 'tid=?', $topic['tid'] ) );
			}
			catch( \UnderflowException $e )
			{
				continue;
			}
		}

		if( !$count )
		{
			throw new \IPS\Task\Queue\OutOfRangeException;
		}

		return ( $offset + $count );
	}
	
	/**
	 * Get Progress
	 *
	 * @param	mixed					$data	Data as it was passed to \IPS\Task::queue()
	 * @param	int						$offset	Offset
	 * @return	array( 'text' => 'Doing something...', 'complete' => 50 )	Text explaining task and percentage complete
	 * @throws	\OutOfRangeException	Indicates offset doesn't exist and thus task is complete
	 */
	public function getProgress( $data, $offset )
	{
		return array( 'text' => \IPS\Member::loggedIn()->language()->addToStack('rebuilding_starter_ids'), 'complete' => round( 100 / $data['count'] * $offset, 2 ) );
	}	
}