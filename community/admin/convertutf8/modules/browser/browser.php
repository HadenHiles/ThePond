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
class browser extends \IPSUtf8\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function manage()
	{
		$output = NULL;

		switch( \IPSUtf8\Session::i()->status )
		{
			default:
				$this->welcome();
			break;
			case 'completed':
				$this->completed();
			break;
		}
	}

	/**
	 * Process
	 *
	 * @return	void
	 */
	public function process()
	{
		if ( isset( \IPSUtf8\Request::i()->use_utf8mb4 ) )
		{
			$sessionData = \IPSUtf8\Session::i()->json;

			$sessionData['use_utf8mb4'] = ( ! empty( \IPSUtf8\Request::i()->use_utf8mb4 ) ) ? 1 : 0;

			\IPSUtf8\Session::i()->json = $sessionData;
			\IPSUtf8\Session::i()->save();
		}

		\IPSUtf8\Convert::i()->process( 100 );

		$json    = \IPSUtf8\Session::i()->json;
		$percent = round( ($json['convertedCount'] / \IPSUtf8\Convert::i()->getTotalRowsToConvert()) * 100, 2 );
		$msg     = "Processing " . \IPSUtf8\Session::i()->current_table . '<br>Row: ' . \IPSUtf8\Session::i()->current_row . ' of ' . \IPSUtf8\Session::i()->tables[ \IPSUtf8\Session::i()->current_table ]['count'] . ' - Total: ' . $percent . '%';

		if ( ! \IPSUtf8\Request::i()->isAjax() )
		{
			\IPSUtf8\Output\Browser::i()->output = Template::process( \IPSUtf8\Session::i()->status, $percent, $msg );
		}
		else
		{
			\IPSUtf8\Output\Browser::i()->sendOutput( json_encode( array( \IPSUtf8\Session::i()->status, $percent, $msg ) ), 200, 'application/json' );
		}
	}

	/**
	 * Completed
	 *
	 * @return	void
	 */
	public function reset()
	{
		\IPSUtf8\Session::i()->reset();
		return $this->welcome();
	}

	/**
	 * Completed
	 *
	 * @return	void
	 */
	public function completed()
	{
		\IPSUtf8\Convert::i()->finish();
		\IPSUtf8\Output\Browser::i()->output = Template::completed( \IPSUtf8\Session::i()->timeTaken( true ) );
	}

	/**
	 * We're finished! (In a good way)
	 *
	 * @return	void
	 */
	public function finish()
	{
		\IPSUtf8\Convert::i()->renameTables();
		\IPSUtf8\Session::i()->status         = null;
		\IPSUtf8\Session::i()->completed_json = array();
		\IPSUtf8\Session::i()->reset();
		
		/* Attempt to automatically update conf_global.php */
		$updated	= FALSE;

		$INFO	= array();
		include( ROOT_PATH . '/conf_global.php' );

		$sessionData	= \IPSUtf8\Session::i()->json;
		$sql_charset	= ( array_key_exists( 'utf8mb4', $sessionData['charSets'] ) ) ? 'utf8mb4' : 'utf8';

		if( $INFO['sql_charset'] == $sql_charset )
		{
			$updated = TRUE;
		}
		else
		{
			if ( file_exists( ROOT_PATH . '/conf_global.php' ) AND is_writable( ROOT_PATH . '/conf_global.php' ) )
			{
				$INFO['sql_charset']	= $sql_charset;
				
				if ( $sql_charset == 'utf8mb4' )
				{
					$INFO['sql_utf8mb4'] = true;
				}

				\file_put_contents( ROOT_PATH . '/conf_global.php', "<?php\n\n" . '$INFO = ' . var_export( $INFO, TRUE ) . ';' );
				$updated	= TRUE;
			}
		}
		
		\IPSUtf8\Output\Browser::i()->output = Template::finished( $updated );
	}

	/**
	 * Welcome page
	 *
	 * @return	void
	 */
	public function welcome()
	{
		if ( ! is_dir( THIS_PATH . '/tmp' ) )
		{
			@mkdir( THIS_PATH . '/tmp' );
			@chmod( THIS_PATH . '/tmp', 0777 );
		}

		if ( ! is_writable( THIS_PATH . '/tmp' ) )
		{
			\IPSUtf8\Output\Browser::i()->error("Please ensure that '" . THIS_PATH . '/tmp' . "' is writable.");
			exit();
		}

		if ( IPB_LOCK and ! \IPSUtf8\Session::i()->is_ipb )
		{
			\IPSUtf8\Output\Browser::i()->error("Cannot locate the IP.Board database tables. Please check to ensure the SQL Prefix if set, is correct in 'conf_global.php'.");
			exit();
		}

		if ( ! \count( \IPSUtf8\Session::i()->tables ) )
		{
			\IPSUtf8\Output\Browser::i()->error("No tables found for processing. Please check to ensure the correct database name and prefix are being used.");
			exit();
		}

		$json    = \IPSUtf8\Session::i()->json;
		$percent = ( \IPSUtf8\Convert::i()->getTotalRowsToConvert() ) ? round( ($json['convertedCount'] / \IPSUtf8\Convert::i()->getTotalRowsToConvert() ) * 100, 2 ) : 100;

		$isUtf8     = (boolean) ( \IPSUtf8\Convert::i()->databaseIsUtf8() );
		$processing = (boolean) ( \IPSUtf8\Session::i()->status === 'processing' );

		\IPSUtf8\Output\Browser::i()->output = Template::welcome( $isUtf8, \IPSUtf8\Session::i()->status, $percent );
	}
}