<?php
/**
 * @brief		Dispatcher (CLI)
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		IPS Tools
 * @since		4 Sept 2013
 */

namespace IPSUtf8\Dispatcher;

/**
 * CLI Dispatcher
 */
class Cli extends \IPSUtf8\Dispatcher
{
	/**
	 * Run
	 */
	public function run()
	{
		$obj = new \IPSUtf8\modules\cli\cli;
		$obj->execute();
	}
	
	/**
	 * Init
	 */
	public function init()
	{
		
	}

	
}
