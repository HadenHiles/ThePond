<?php
/**
 * @brief		Conversion module
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - SVN_YYYY Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Tools
 * @since		4 Sept 2013
 * @version		SVN_VERSION_NUMBER
 */

namespace IPSUtf8\Text\Module;

/**
 * Native MB Conversion class
 */
class Uconverter extends \IPSUtf8\Text\Charset
{
	/**
	 * @brief	The class
	 */
	protected $_class = NULL;
	
	/**
	 * Converts a text string from its current charset to a destination charset using iconv
	 *
	 * @param	string		Text string
	 * @param	string		Text string char set (original)
	 * @param	string		Desired character set (destination)
	 * @return	@e string
	 */
	public function convert( $string, $from, $to='UTF-8' )
	{
		if ( static::needsConverting( $string, $from, $to ) === false )
		{
			return $string;
		}
		
		$text = NULL;

		if ( class_exists( 'UConverter' ) )
		{
			try
			{
				if ( $this->_class === NULL )
				{
					$this->_class = new \UConverter( $to, $from );
				}
				$text = $this->_class->convert( $string );
			}
			catch( \Exception $e )
			{
				static::$errors[] = $e->getMessage();
			}
			catch( \Throwable $e )
			{
				static::$errors[] = $e->getMessage();
			}
		}
		else
		{
			static::$errors[]	= "NO_UCONVERTER_CLASS";
		}
		
		return $text ? $text : $string;
	}
}
