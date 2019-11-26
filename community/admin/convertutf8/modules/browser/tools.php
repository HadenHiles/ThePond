<?php
/**
 * @brief		Web conversion process
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		9 Sept 2013
 */

namespace IPSUtf8\modules\browser;
use \IPSUtf8\Output\Browser\Template;

/**
 * Web Conversion process
 */
class tools extends \IPSUtf8\Dispatcher\Controller
{	
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function manage( $msg=null )
	{
		$isUtf8 = (boolean) ( \IPSUtf8\Convert::i()->database_charset == 'utf8' OR \IPSUtf8\Session::i()->current_charset == 'utf-8' );
		\IPSUtf8\Output\Browser::i()->output = Template::tools( $isUtf8, $msg );
	}
	
	/**
	 * Check and repair collation
	 *
	 * @return	void
	 */
	public function collation()
	{
		if( ( version_compare( \IPSUtf8\Db::i()->server_info, '5.5.3', '>=' ) AND \IPSUtf8\Db::i('utf8')->set_charset('utf8mb4') !== FALSE ) )
		{
			$sessionData = \IPSUtf8\Session::i()->json;
			$sessionData['use_utf8mb4'] = 1;

			\IPSUtf8\Session::i()->json = $sessionData;
			\IPSUtf8\Session::i()->save();
		}

		\IPSUtf8\Convert::i()->fixCollation();
		
		$this->manage( "Collation checked and fixed where appropriate" );
	}
}