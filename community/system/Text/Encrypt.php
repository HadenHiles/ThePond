<?php
/**
 * @brief		Encrypt Text
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		17 Apr 2014
 */

namespace IPS\Text;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Encrypted
 */
class _Encrypt
{
	/**
	 * Get Key
	 *
	 * @return	void
	 */
	public static function key()
	{
		return \IPS\TEXT_ENCRYPTION_KEY ?: md5( \IPS\Settings::i()->sql_pass . \IPS\Settings::i()->sql_database );
	}
	
	/**
	 * @brief	Cipher
	 */
	public $cipher;
	
	/**
	 * Constructor
	 *
	 * @return	void
	 */
	public function __construct()
	{
		require_once \IPS\ROOT_PATH . '/system/3rd_party/AES/AES.php';
	}
	
	/**
	 * From plaintext
	 *
	 * @param	string	$plaintext	Plaintext
	 * @return	static
	 */
	public static function fromPlaintext( $plaintext )
	{
		$obj = new static;
		$obj->cipher = \AesCtr::encrypt( $plaintext, static::key(), 256 );
		return $obj;
	}
	
	/**
	 * From plaintext
	 *
	 * @param	string	$cipher	Cipher
	 * @return	static
	 */
	public static function fromCipher( $cipher )
	{
		$obj = new static;
		$obj->cipher = $cipher;
		return $obj;
	}
	
	/**
	 * From tag
	 *
	 * @param	string	$tag	Tag
	 * @return	static
	 */
	public static function fromTag( $tag )
	{
		if ( preg_match( '/^\[\!AES\[(.+?)\]\]/', $tag, $matches ) )
		{
			return static::fromCipher( $matches[1] );
		}
		else
		{
			return static::fromPlaintext( $tag );
		}
	}
	
	/**
	 * Wrap in a tag to use later with fromTag
	 * Used for legacy areas which previously stored unencrypted values
	 *
	 * @return	string
	 */
	public function tag()
	{
		return '[!AES[' . $this->cipher . ']]';
	}
	
	/**
	 * Decript
	 *
	 * @return	string
	 */
	public function decrypt()
	{
		return \AesCtr::decrypt( $this->cipher, static::key(), 256 );
	}
}