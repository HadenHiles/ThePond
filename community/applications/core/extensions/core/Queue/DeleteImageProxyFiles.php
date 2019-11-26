<?php
/**
 * @brief		Background Task
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		11 Sep 2017
 */

namespace IPS\core\extensions\core\Queue;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Background Task
 */
class _DeleteImageProxyFiles
{
	/**
	 * Parse data before queuing
	 *
	 * @param	array	$data
	 * @return	array
	 */
	public function preQueueData( $data )
	{
		/* Don't run if the image proxy is enabled */
		if ( $data['status'] )
		{
			return NULL;
		}

		$data['total'] = \IPS\Db::i()->select( 'count(*)', 'core_image_proxy' )->first();
		$data['deleted'] = 0;

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
	public function run( $data, $offset )
	{
		$select = \IPS\Db::i()->select( 'location', 'core_image_proxy', array(), 'cache_time ASC', \IPS\REBUILD_SLOW );

		$completed	= 0;

		foreach ( $select as $location )
		{
			try
			{
				\IPS\File::get( 'core_Imageproxycache', $location )->delete();
			}
			catch ( \Exception $e ) { }

			\IPS\Db::i()->delete( 'core_image_proxy', array( 'location=?', $location ) );

			$data['deleted']++;
			$completed++;
		}

		if( $completed === 0 )
		{
			throw new \IPS\Task\Queue\OutOfRangeException;
		}

		return $completed;
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
		return array( 'text' => \IPS\Member::loggedIn()->language()->addToStack('deleting_imageproxy_files'), 'complete' => $data['total'] ? ( round( ( 100 / $data['total'] ) * $data['deleted'], 2 ) ) : 100 );
	}
}