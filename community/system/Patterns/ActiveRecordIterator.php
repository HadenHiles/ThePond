<?php
/**
 * @brief		ActiveRecord IteratorIterator
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		21 Nov 2013
 */

namespace IPS\Patterns;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * ActiveRecord IteratorIterator
 */
class _ActiveRecordIterator extends \IteratorIterator implements \Countable
{
	/**
	 * @brief	Classname
	 */
	public $classname;
		
	/**
	 * Constructor
	 *
	 * @param	\Traversable $iterator			The iterator
	 * @param	string		$classname			The classname
	 * @return	void
	 */
	public function __construct( \Traversable $iterator, $classname )
	{
		$this->classname = $classname;
		return parent::__construct( $iterator );
	}
	
	/**
	 * Get current
	 *
	 * @return	\IPS\Patterns\ActiveRecord
	 */
	public function current()
	{
		$classname = $this->classname;
		return $classname::constructFromData( parent::current() );
	}
	
	/**
	 * Get count
	 *
	 * @param	bool	$allRows	If TRUE, will get the number of rows ignoring the limit. In order for this to work, the query must have been ran with SQL_CALC_FOUND_ROWS
	 * @return	int
	 */
	public function count( $allRows = FALSE )
	{
		return (int) $this->getInnerIterator()->count( $allRows );
	}
}