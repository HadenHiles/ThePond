<?php
/**
 * @brief		Redis Engine Class
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		11 Sept 2017
 */

namespace IPS;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Redis Cache Class
 */
class _Redis
{
	/**
	 * @brief	Multiton Store
	 */
	protected static $multitons = array();
	
	/**
	 * @brief	Connections Store
	 */
	protected static $connections = array();
	
	/**
	 * @brief	Default expiration for keys in seconds
	 */
	protected static $ttl = 604800; #7 days
	
	/**
	 * @brief Log what redis is up to
	 */
	public static $log = array();
	
	/**
	 * @brief Log what redis is up to
	 */
	public $prefix = NULL;
	
	/**
	 * @brief Unpack the config once
	 */
	protected static $config = NULL;

	/**
	 * @brief Can we encrypt?
	 */
	protected $canEncrypt	= FALSE;
	
	/**
	 * Writes made
	 */
	protected static $writes = array();
	
	/**
	 * Get instance
	 *
	 * @param	NULL|array	$configuration	Configuration to use (NULL to use \IPS\REDIS_CONFIG or \IPS\CACHE_CONFIG)
	 * @param	NULL|string	$identifier		Identifier (to support multiple instances)
	 * @return	\Redis
	 */
	public static function i( $configuration=NULL, $identifier=NULL )
	{
		if ( static::$config === NULL )
		{
			$config = ( \defined( '\IPS\REDIS_CONFIG' ) and \IPS\REDIS_CONFIG !== NULL ) ? \IPS\REDIS_CONFIG : \IPS\CACHE_CONFIG;
			static::$config = $configuration ?: json_decode( $config, true );
		}

		$identifier = $identifier ? $identifier : '_MAIN';
		
		if ( ! isset( static::$multitons[ $identifier ] ) )
		{
			static::$multitons[ $identifier ] = new self;
			
			/* Set the prefix with the most obvious comment in the world */
			static::$multitons[ $identifier ]->prefix = \IPS\SUITE_UNIQUE_KEY . '_';
		}
		
		/* Return */
		return static::$multitons[ $identifier ];
	}

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->canEncrypt = \IPS\REDIS_ENCRYPT === TRUE and \function_exists( 'openssl_encrypt' ) and \in_array( 'aes-256-ctr', openssl_get_cipher_methods() );
	}
	
		
	/**
	 * Destructor
	 *
	 * @return void
	 */
	public function __destruct()
	{
		if ( \IPS\DEBUG_LOG and \count( static::$writes ) > 50 )
		{ 
			$slice = implode( ', ', \array_slice( static::$writes, 0, 50 ) );
			\IPS\Log::debug( "Large number of Redis writes: " . $slice, 'redis_writes' );
		}
	}
	
	/**
	 * @brief	Chosen reader for this session
	 */
	protected static $reader = NULL;
	
	/**
	 * Connect to Redis
	 *
	 * @param	string|NULL	$identifier		Identifier
	 * @return	\Redis
	 * @throws	\RedisException	If connection cannot be established
	 * @throws	\BadMethodCallException	If Redis PHP extension is not installed
	 */
	public function connection( $identifier=NULL )
	{
		if ( ! class_exists('Redis') )
		{
			throw new \BadMethodCallException;
		}
		
		$useConfig = NULL;
		if ( isset( static::$config['write'] ) )
		{
			/* We have multiple servers for read and one for write */
			if ( $identifier === 'write' )
			{
				$useConfig = static::$config['write'];
			}
			else if ( $identifier === 'read' )
			{
				if ( static::$reader === NULL )
				{
					static::$reader = rand( 0, \count( static::$config['read'] ) - 1 );
				}
				
				$useConfig = static::$config['read'][ static::$reader ];
				$identifier = 'read' . static::$reader;
			}
			else
			{
				/* Set up the writer first as the default server */
				$identifier = 'write';
				$useConfig = static::$config['write'];
			}
		}
		else
		{
			/* We have only passed through one server */
			$identifier = 'single';
			$useConfig = static::$config;
		}

		if ( ! isset( static::$connections[ $identifier ] ) )
		{
			try
			{
				static::$connections[ $identifier ] = new \Redis;

				/* PHP Redis uses many PHP internals to connect, and these can throw ErrorException when they fail but we want a consistent exception */
				if( @static::$connections[ $identifier ]->connect( $useConfig['server'], $useConfig['port'], 2 ) === FALSE )
				{
					unset( static::$connections[ $identifier ] );
					throw new \RedisException('CANNOT_CONNECT');
				}
				else
				{
					if( isset( $useConfig['password'] ) and $useConfig['password'] )
					{
						if( static::$connections[ $identifier ]->auth( $useConfig['password'] ) === FALSE )
						{
							unset( static::$connections[ $identifier ] );
							throw new \RedisException;
						}
					}
				}
				
				if( static::$connections[ $identifier ] !== NULL )
				{
					static::$connections[ $identifier ]->setOption( \Redis::OPT_SERIALIZER, \Redis::SERIALIZER_NONE );
					static::$connections[ $identifier ]->setOption( \Redis::OPT_PREFIX, $this->prefix );
				}
				
				/* If connection times out, connect can return TRUE and we won't know until our next attempt to talk to the server,
					so we should ping now to verify we were able to connect successfully */
				static::$connections[ $identifier ]->ping();
				
				if ( \IPS\REDIS_LOG )
				{
					static::$log[ sprintf( '%.4f', microtime(true) ) ] = array( 'redis', "Redis connected (" . $identifier . ' ' . $useConfig['server'] . ")"  );
				}
				
				if ( \count( static::$connections ) === 1 )
				{
					register_shutdown_function( function( $object ){
						try
						{
							/* First we have to make sure sessions have written */
							if( \IPS\Session\Store::i() instanceof \IPS\Session\Store\Redis )
							{
								session_write_close();
							}

							foreach( static::$connections as $key => $connection )
							{
								$connection->close();
							}
							
							/* Reset stored connections so they can be re-connected correctly if tasks run after this shutdown proceses */
							static::$connections = array();
						}
						catch( \RedisException $e ){}
					}, $this );
				}

			}
			catch( \RedisException $e )
			{
				/* Unset this connection */
				if ( isset( static::$connections[ $identifier ] ) )
				{
					unset( static::$connections[ $identifier ] );
				}
				
				$this->resetConnection( $e );
			}
		}

		if( !isset( static::$connections[ $identifier ] ) )
		{
			throw new \RedisException('CANNOT_CONNECT');
		}
		
		return static::$connections[ $identifier ];
	}
	
	/**
	 * Call methods
	 *
	 * @param	string	$method	Method
	 * @param	mixed	$args	Arguments
	 * @return	mixed
	 */
	public function __call( $method, $args )
	{
		if ( method_exists( 'Redis', $method ) )
		{
			$type = ( \stristr( $method, 'get' ) or \stristr( $method, 'RevRange' ) ) ? 'read' : 'write';
			$return = $this->connection( $type )->$method( ...$args );
			
			if ( $type === 'write' and \count( $args ) )
			{
				static::$writes[] = $args[0];
			}
			
			if ( \IPS\REDIS_LOG and \count( $args ) )
			{
				if ( preg_match( '#^[a-f0-9]{32}_str__#', $args[0] ) )
				{
					$args[0] =  preg_replace( '#^[a-f0-9]{32}_str__#', '[DATASTORE] ', $args[0] );
				}
				
				static::$log[ sprintf( '%.4f', microtime(true) ) ] = array( 'redis', "({$type}) {$method} " . $args[0], json_encode( $args ) );
			}
			
			return $return;
		}	
	}
	
	/**
	 * Add one or more members to a sorted set or update its score if it already exists
	 * Overloaded here so it can add a TTL to prevent permanent keys
	 *
	 * @param	string		$key	Key
	 * @param	float		$score	Score
	 * @param	string		$value	Value
	 * @param	int|NULL	$ttl	TTL in seconds
	 * @return	1 if the element is added. 0 otherwise.
	 */
	public function zAdd( $key, $score, $value, $ttl=NULL )
	{
		$return = $this->connection('write')->zAdd( $this->key( $key ), $score, $value );
		
		$this->connection('write')->expire( $this->key( $key ), ( $ttl ? $ttl : static::$ttl ) );
		
		if ( \IPS\REDIS_LOG )
		{
			static::$log[ sprintf( '%.4f', microtime(true) ) ] = array( 'redis', "(write) zAdd " . $key . " = " . $return, json_encode( $value ) );
		}
		
		return $return;
	}
	
	/**
	 * Fills in a whole hash. Non-string values are converted to string, using the standard (string) cast. NULL values are stored as empty strings.
	 * Overloaded here so it can add a TTL to prevent permanent keys
	 *
	 * @param	string		$key	Key
	 * @param	array		$value	Value
	 * @param	int|NULL	$ttl	TTL in seconds
	 * @return	boolean
	 */
	public function hMSet( $key, $value, $ttl=NULL )
	{
		$return = $this->connection('write')->hMSet( $this->key( $key ), $value );
		
		$this->connection('write')->expire( $this->key( $key ), ( $ttl ? $ttl : static::$ttl ) );
		
		if ( \IPS\REDIS_LOG )
		{
			static::$log[ sprintf( '%.4f', microtime(true) ) ] = array( 'redis', "(write) hMSet " . $key . " = " . $return, json_encode( $value ) );
		}
		
		return $return;
	}
	
	/**
	 * Set the string value in argument as value of the key, with a time to live
	 * Overloaded here so it can be logged
	 *
	 * @param	string		$key	Key
	 * @param	int|NULL	$ttl	TTL in seconds
	 * @param	string		$value	Value
	 * @return	boolean
	 */
	public function setEx( $key, $ttl, $value )
	{
		$return = $this->connection('write')->setEx( $this->key( $key ), $ttl, $value );
		
		if ( \IPS\REDIS_LOG )
		{
			static::$log[ sprintf( '%.4f', microtime(true) ) ] = array( 'redis', "(write) setEx " . $key . "  = " . $return, json_encode( $value ) );
		}
		
		return $return;
	}
	
	/**
	 * Sort the elements in a list, set or sorted set.
	 * Overloaded here so we can adjust the key
	 *
	 * @param	string		$key		Key
	 * @param	array		$options	Options: array(key => value, ...) - optional
	 * @return	array
	 */
	public function sort( $key, $options=array() )
	{
		$return = $this->connection('write')->sort( $this->key( $key ), $options );
		
		if ( isset( $options['store'] ) )
		{
			$this->connection('write')->expire( $this->key( $options['store'] ), ( isset( $options['ttl'] ) and $options['ttl'] ) ? $options['ttl'] : static::$ttl );
		}
		
		if ( \IPS\REDIS_LOG )
		{
			static::$log[ sprintf( '%.4f', microtime(true) ) ] = array( 'redis', "(read) sort " . $key, json_encode( $return ) );
		}
		
		return $return;
	}
	
	/**
	 * Returns the whole hash, as an array of strings indexed by strings.
	 * Overloaded here so it can be logged
	 *
	 * @param	string		$key	Key
	 * @return	array
	 */
	public function hGetAll( $key )
	{
		/* Make sure we read */
		$return = $this->connection('read')->hGetAll( $this->key( $key ) );
		
		if ( \IPS\REDIS_LOG )
		{
			static::$log[ sprintf( '%.4f', microtime(true) ) ] = array( 'redis', "(read) hGetAll " . $key, json_encode( $return ) );
		}
		
		return $return;
	}
	
	/**
	 * Increments the score of a member from a sorted set by a given amount.
	 * Overloaded here so it can be logged and a ttl set
	 *
	 * @param	string		$key	Key
	 * @param	int			$inc	Value to increment
	 * @param	string		$value	Value
	 * @param	int|NULL	$ttl	TTL in seconds
	 * @return	boolean
	 */
	public function zIncrBy( $key, $inc, $value, $ttl=NULL )
	{
		$return = $this->connection('write')->zIncrBy( $this->key( $key ), $inc, $value );
		$this->connection('write')->expire( $this->key( $key ), ( $ttl ? $ttl : static::$ttl ) );
		
		if ( \IPS\REDIS_LOG )
		{
			static::$log[ sprintf( '%.4f', microtime(true) ) ] = array( 'redis', "(write) zIncrBy " . $key . "  = " . $return, json_encode( $value ) );
		}
		
		return $return;
	}
	
	/**
	 * Strip prefixes from keys as PHP redis will handle this
	 *
	 * @param	string	$key	Key
	 * @return	string
	 */
	protected function key( $key )
	{
		if ( $this->prefix )
		{
			if ( mb_substr( $key, 0, mb_strlen( $this->prefix ) ) == $this->prefix )
			{
				return str_replace( $this->prefix, '', $key );
			}
		}
		
		return $key;
	}

	/**
	 * @brief	Cached encryption key
	 */
	protected $encryptionKey = NULL;
	
	/**
	 * Encryption key
	 *
	 * @return	string
	 */
	protected function _encryptionKey()
	{
		if( $this->encryptionKey !== NULL )
		{
			return $this->encryptionKey;
		}

		$this->encryptionKey = \IPS\Settings::i()->sql_pass;
		if ( \function_exists( 'openssl_digest' ) and \in_array( 'sha256', openssl_get_md_methods() ) )
		{
			$this->encryptionKey = openssl_digest( $this->encryptionKey, 'sha256', TRUE );
		}

		return $this->encryptionKey;
	}
		
	/**
	 * Encode
	 *
	 * @param	mixed	$value	Value
	 * @return	string
	 */
	public function encode( $value )
	{
		$value = json_encode( $value );

		if ( $this->canEncrypt )
		{
			$iv = \IPS\Login::generateRandomString( 16 );
			$value = $iv . openssl_encrypt( $value, 'aes-256-ctr', $this->_encryptionKey(), OPENSSL_RAW_DATA, $iv );
		}
		
		return $value;
	}
	
	/**
	 * Decode
	 *
	 * @param	mixed	$value	Value
	 * @return	mixed
	 */
	public function decode( $value )
	{
		if ( $this->canEncrypt )
		{
			$value = @openssl_decrypt( \substr( $value, 16 ), 'aes-256-ctr', $this->_encryptionKey(), OPENSSL_RAW_DATA, \substr( $value, 0, 16 ) );
		}
		
		return json_decode( $value, TRUE );
	}
	
	/**
	 * Reset connection
	 *
	 * @param	\RedisException|NULL	$e	If this was called as a result of an exception, log that to the debug log
	 * @return void
	 */
	public function resetConnection( \RedisException $e = NULL )
	{
		if ( $e !== NULL )
		{
			\IPS\Log::debug( $e, 'redis_exception' );
		}

		static::$multitons = array();
		
		if ( \IPS\REDIS_LOG )
		{
			static::$log[ microtime() ] = array( 'redis', "Redis connections reset" );
		}
	}
	
	/**
	 * Is Redis working?
	 *
	 * @return	bool
	 */
	public function test()
	{
		return ( \count( static::$connections ) ) ? TRUE : FALSE;
	}
	
	/**
	 * Debug method to fetch all keys.
	 *
	 * @warning	Never use this in production, as it can expose sensitive data!
	 * @param	string	$pattern		Pattern (* to fetch all)
	 * @param	boolean	$keyNamesOnly	Return names only
	 * @return	array
	 */
	public function debugGetKeys( $pattern='*', $keyNamesOnly=FALSE )
	{
		$this->connection('write')->setOption( \Redis::OPT_SCAN, \Redis::SCAN_RETRY );
		
		$return = array();
		$iterator = NULL;
		while( $keys = $this->connection('write')->scan( $iterator, $this->prefix . $this->key( $pattern ) ) )
		{
			if ( $keyNamesOnly)
			{
				$return = array_merge( $return, $keys );
			}
			else
			{
				foreach( $keys as $key )
				{
					$key = $this->key( $key );
					$type = $this->connection('write')->type( $key );
					$ttl = \IPS\Redis::i()->ttl( $key );

					switch( $type )
					{
						case \Redis::REDIS_STRING:
							$return[ $key . ' (TTL: ' . $ttl . ')' ] = \IPS\Redis::i()->decode( $this->connection('write')->get( $key ) );
						break;
						case \Redis::REDIS_ZSET:
							$return[ $key . ' (TTL: ' . $ttl . ')' ] = $this->connection('write')->zRange( $key, 0, -1, TRUE );
						break;
						case \Redis::REDIS_HASH:
							$return[ $key . ' (TTL: ' . $ttl . ')' ] = $this->connection('write')->hGetAll( $key );
							if ( isset( $return[ $key . ' (TTL: ' . $ttl . ')' ]['data'] ) )
							{
								$return[ $key . ' (TTL: ' . $ttl . ')' ]['data'] = \IPS\Redis::i()->decode( $return[ $key . ' (TTL: ' . $ttl . ')' ]['data'] );
							}
						break;
						case \Redis::REDIS_LIST:
							$return[ $key . ' (TTL: ' . $ttl . ')' ] = $this->connection('write')->lRange( $key, 0, -1 );
						break;
					}
				}
			}
		}
		
		return $return;
	}
}