<?php
/**
 * @brief		Conversion module
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		IPS Tools
 * @since		4 Sept 2013
 */

namespace IPSUtf8\Text\Module;

/**
 * Native MB Conversion class
 */
class Recode extends \IPSUtf8\Text\Charset
{
	/**
	 * Converts a text string from its current charset to a destination charset using recode_string
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

		if ( \function_exists( 'recode_string' ) )
		{
			$text = recode_string( $from . '..' . $to, $string );
		}
		else
		{
			static::$errors[] = "NO_RECODE_FUNCTION";
		}
		
		return $text ? $text : $string;
	}
}