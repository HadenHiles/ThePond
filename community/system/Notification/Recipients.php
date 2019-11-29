<?php
/**
 * @brief		Data Structure for storing notification recipieints - basically a non-unique SplObjectStorage
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		8 Feb 2017
 */

namespace IPS\Notification;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Data Structure for storing notification recipieints - basically a non-unique SplObjectStorage
 */
class _Recipients implements \Countable, \Iterator, \ArrayAccess
{
	/**
	 * @brief	Recipients
	 */
	protected $recipients = array();
	
	/**
	 * @brief	Count
	 */
	protected $count = 0;
	
	/**
	 * @brief	Current position
	 */
	protected $position = 0;
	
	/**
	 * [SplObjectStorage] Add a recipient
	 *
	 * @param	\IPS\Member	$member		The member object
	 * @param	array|null	$followData	If the notification is about something being followed, the appropriate row from core_follow
	 * @return	void
	 */
	public function attach( \IPS\Member $member, $followData = NULL )
	{
		$this->recipients[] = array( 'member' => $member, 'followData' => $followData );
		$this->count++;
	}
	
	/**
	 * [SplObjectStorage] Remove a recipient
	 *
	 * @param	\IPS\Member	$member		The member object
	 * @return	void
	 */
	public function detach( \IPS\Member $member, $followData = NULL )
	{
		foreach ( $this->recipients as $k => $data )
		{
			if ( $data['member']->member_id == $member->member_id )
			{
				unset( $this->recipients[ $k ] );
				$this->count--;
			}
		}
	}
	
	/**
	 * [Countable] Get a count
	 *
	 * @return	int
	 */
	public function count()
	{
		return $this->count;
	}
	
	/**
	 * [Iterator] Get current
	 *
	 * @return	\IPS\Member
	 */
	public function current()
	{
		return $this->recipients[ $this->position ]['member'];
	}
	
	/**
	 * Get current's info
	 *
	 * @return	array|null
	 */
	public function getInfo()
	{
		return $this->recipients[ $this->position ]['followData'];
	}
	
	/**
	 * [Iterator] Get key
	 *
	 * @return	int
	 */
	public function key()
	{
		return $this->position;
	}
	
	/**
	 * [Iterator] Go to next
	 *
	 * @return	void
	 */
	public function next()
	{
		$this->position++;
	}
	
	/**
	 * [Iterator] Rewind
	 *
	 * @return	void
	 */
	public function rewind()
	{
		$this->position = 0;
	}
	
	/**
	 * [Iterator] Is valid?
	 *
	 * @return	bool
	 */
	public function valid()
	{
		return $this->position < $this->count;
	}
	
	/**
	 * [ArrayAccess] Offset exists?
	 *
	 * @param	mixed	$offset	The offset
	 * @return	bool
	 */
	public function offsetExists( $offset )
	{
		return $offset < $this->count;
	}
	
	/**
	 * [ArrayAccess] Get offset
	 *
	 * @param	mixed	$offset	The offset
	 * @return	\IPS\Member
	 */
	public function offsetGet( $offset )
	{
		return $this->recipients[ $offset ]['member'];
	}
	
	/**
	 * [ArrayAccess] Set offset
	 *
	 * @param	mixed	$offset	The offset
	 * @param	mixed	$value	The value
	 * @return	bool
	 */
	public function offsetSet( $offset, $value )
	{
		$this->recipients[] = array( 'member' => $value, 'followData' => NULL );
		
		if ( $offset > $this->count )
		{
			$this->count++;
		}
	}
	
	/**
	 * [ArrayAccess] Unset offset
	 *
	 * @param	mixed	$offset	The offset
	 * @return	void
	 */
	public function offsetUnset( $offset )
	{
		if ( isset( $this->recipients[ $offset ] ) )
		{
			unset( $this->recipients[ $offset ] );
			$this->count--;
		}
	}
}