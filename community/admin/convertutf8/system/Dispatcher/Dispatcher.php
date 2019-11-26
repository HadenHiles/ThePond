<?php
/**
 * @brief		Dispatcher
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		IPS Tools
 * @since		4 Sept 2013
 */

namespace IPSUtf8;

/**
 * Dispatcher
 */
abstract class Dispatcher
{
	/**
	 * @brief	Singleton Instance
	 */
	protected static $instance = NULL;
	
	/**
	 * @brief	Controller
	 */
	public $controller = 'browser';
	
	/**
	 * @brief	Controller
	 */
	public $controllerObj = NULL;
	
	/**
	 * Get instance
	 *
	 * @return	\IPS\Dispatcher
	 */
	public static function i()
	{
		if( self::$instance === NULL )
		{
			$classname = \get_called_class();
			if ( $classname === 'IPSUtf8\Dispatcher' )
			{
				self::$instance = new \IPSUtf8\Dispatcher\Browser;
			}
			else
			{
				self::$instance = new $classname;
			}
			
			self::$instance->init();
		}
		
		return self::$instance;
	}
	
	/**
	 * Run the dispatcher
	 */
	public function run()
	{
		
	}
	
	/**
	 * Init
	 */
	public function init()
	{
		
	}
	
}
