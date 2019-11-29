<?php
/**
 * @brief		OAuth Server Generate Access Token Endpoint
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		29 Apr 2017
 */

\define('REPORT_EXCEPTIONS', TRUE);
require '../../init.php';

class oAuthServerTokenRequest
{
	/**
	 * @brief	Client
	 */
	public $client;
	
	/**
	 * Init
	 *
	 * @param	string		$clientId		Client ID
	 * @param	string		$clientSecret	Client Secret
	 * @return	void
	 */
	public static function init( $clientId, $clientSecret )
	{
		$obj = new static;
		
		/* Get the client */
		try
		{
			$obj->client = \IPS\Api\OAuthClient::load( $clientId );
			if ( !$obj->client->enabled )
			{
				throw new \OutOfRangeException;
			}
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Login\Handler\OAuth2\Exception( 'invalid_client' );
		}
		
		/* Validate the secret */
		if ( $obj->client->client_secret )
		{
			$bruteForce = $obj->client->brute_force ? json_decode( $obj->client->brute_force, TRUE ) : array();
			
			if ( isset( $bruteForce[ \IPS\Request::i()->ipAddress() ] ) and $bruteForce[ \IPS\Request::i()->ipAddress() ] >= 3 )
			{
				throw new \IPS\Login\Handler\OAuth2\Exception( 'invalid_client', "blocked for too many authentication failures" );
			}
			
			if ( password_verify( $clientSecret, $obj->client->client_secret ) )
			{
				if ( isset( $bruteForce[ \IPS\Request::i()->ipAddress() ] ) )
				{
					unset( $bruteForce[ \IPS\Request::i()->ipAddress() ] );
					$obj->client->brute_force = json_encode( $bruteForce );
					$obj->client->save();
				}
			}
			else
			{			
				if ( !isset( $bruteForce[ \IPS\Request::i()->ipAddress() ] ) )
				{
					$bruteForce[ \IPS\Request::i()->ipAddress() ] = 0;
				}
				$bruteForce[ \IPS\Request::i()->ipAddress() ]++;
				$obj->client->brute_force = json_encode( $bruteForce );
				$obj->client->save();
				
				throw new \IPS\Login\Handler\OAuth2\Exception( 'invalid_client' );
			}
		}
		
		return $obj;
	}
	
	/**
	 * Validate the grant type
	 *
	 * @param	string	$grantType	The Authorization Code
	 * @return	string
	 */
	public function grantType( $grantType )
	{
		if ( !\in_array( $grantType, array( 'authorization_code', 'implicit', 'client_credentials', 'password', 'refresh_token' ) ) )
		{
			throw new \IPS\Login\Handler\OAuth2\Exception( 'unsupported_grant_type' );
		}
		
		if ( $grantType === 'refresh_token' )
		{
			if ( !$this->client->use_refresh_tokens )
			{
				throw new \IPS\Login\Handler\OAuth2\Exception( 'unsupported_grant_type' );
			}
		}
		elseif ( !\in_array( $grantType, explode( ',', $this->client->grant_types ) ) )
		{
			throw new \IPS\Login\Handler\OAuth2\Exception( 'unauthorized_client' );
		}
		
		return $grantType;
	}

	
	/**
	 * Validate Authorization Code
	 *
	 * @param	string	$authorizationCode	The Authorization Code
	 * @return	array|NULL
	 */
	public function validateAuthorizationCode( $authorizationCode, $redirectUri = NULL )
	{
		try
		{
			$authorizationCode = \IPS\Db::i()->select( '*', 'core_oauth_server_authorization_codes', array( 'client_id=? AND code=?', $this->client->client_id, $authorizationCode ) )->first();
			
			/* If it's expired, delete it and do not validate */
			if ( $authorizationCode['expires'] < time() )
			{
				\IPS\Db::i()->delete( 'core_oauth_server_authorization_codes', array( 'client_id=? AND code=?', $authorizationCode['client_id'], $authorizationCode['code'] ) );
				return;
			}
			
			/* If it's already been used, this should be treated as an attack: revoke any access tokens already generated and do not validate */
			if ( $authorizationCode['used'] )
			{
				\IPS\Db::i()->delete( 'core_oauth_server_access_tokens', array( 'client_id=? AND authorization_code=?', $this->client->client_id, $authorizationCode['code'] ) );
				return;
			}
			
			/* If the redirect URI does not match, do not validate */	
			if ( $redirectUri !== $authorizationCode['redirect_uri'] )
			{
				return;
			}
			
			/* Check we're not banned */
			$member = \IPS\Member::load( $authorizationCode['member_id'] );
			if ( $member->isBanned() )
			{
				return;
			}
			
			/* Mark it used */
			\IPS\Db::i()->update( 'core_oauth_server_authorization_codes', array( 'used' => 1 ), array( 'client_id=? AND code=?', $authorizationCode['client_id'], $authorizationCode['code'] ) );
			
			/* Return access token */
			return $this->client->generateAccessToken( $member, $authorizationCode['scope'] ? json_decode( $authorizationCode['scope'] ) : NULL, 'authorization_code', FALSE, $authorizationCode['code'] );
		}
		catch ( \UnderflowException $e )
		{
			return;
		}
	}
	
	/**
	 * Validate Password
	 *
	 * @param	string		$username	Username
	 * @param	object		$password	The plaintext password provided by the user, wrapped in an object that can be cast to a string so it doesn't show in any logs
	 * @param	array|null	$scope		Scopes
	 * @return	array|NULL
	 */
	public function validatePassword( $username, $password, $scope )
	{
		$member = NULL;
		$accessToken = NULL;
		$fails = array();
				
		$login = new \IPS\Login();
				
		foreach ( $login->usernamePasswordMethods() as $method )
		{
			try
			{
				$member = $method->authenticateUsernamePassword( $login, $username, $password );
				\IPS\Login::checkIfAccountIsLocked( $member, TRUE );
				
				if ( !$member->isBanned() )
				{
					$accessToken = $this->client->generateAccessToken( $member, $scope, 'password' );
				}
				break;
			}
			catch ( \IPS\Login\Exception $e )
			{
				if ( $e->getCode() === \IPS\Login\Exception::BAD_PASSWORD and $e->member )
				{
					$fails[ $e->member->member_id ] = $e->member;
				}
			}
			catch ( \Exception $e ) { }
		}
				
		foreach ( $fails as $failedMember )
		{
			if ( !$member or $failedMember->member_id != $failedMember->member_id )
			{
				$failedLogins = \is_array( $failedMember->failed_logins ) ? $failedMember->failed_logins : array();
				$failedLogins[ \IPS\Request::i()->ipAddress() ][] = time();
				$failedMember->failed_logins = $failedLogins;
				$failedMember->save();
			}
		}
		
		return $accessToken;
	}
	
	/**
	 * Validate Client Credentials
	 *
	 * @param	array|null	$scope		Scopes
	 * @return	array|NULL
	 */
	public function validateClientCredentials( $scope )
	{
		if ( $this->client->client_secret )
		{
			return $this->client->generateAccessToken( NULL, $scope, 'client_credentials', TRUE );
		}
	}
	
	/**
	 * Validate Refresh Token
	 *
	 * @param	string		$refreshToken	The refresh token
	 * @param	array|null	$scope			Scopes
	 * @return	array|NULL
	 */
	public function validateRefreshToken( $refreshToken, $newScope )
	{
		$accessToken = $this->client->validateRefreshToken( $refreshToken );
		if ( $accessToken )
		{
			$member = NULL;
			if ( $accessToken['member_id'] )
			{
				$member = \IPS\Member::load( $accessToken['member_id'] );
				if ( !$member->member_id or $member->isBanned() )
				{
					return;
				}
			}
			
			$originalScope = $accessToken['scope'] ? json_decode( $accessToken['scope'], TRUE ) : array();
			$scope = $newScope ? array_intersect( $originalScope, $newScope ) : $originalScope;
			
			return $this->client->generateAccessToken( $member, $scope, 'refresh_token', TRUE );
		}
	}
}

/* Handle it */
try
{
	/* TLS only */
	if ( \IPS\OAUTH_REQUIRES_HTTPS and !\IPS\Request::i()->isSecure() )
	{
		throw new \IPS\Login\Handler\OAuth2\Exception( 'invalid_request', "request must be made with https" );
	}
	
	/* POST only */
	if ( \IPS\Request::i()->requestMethod() !== 'POST' )
	{
		throw new \IPS\Login\Handler\OAuth2\Exception( 'invalid_request', "request must be a POST request" );
	}
	
	/* Check we are not IP banned */
	$ipBanned = \IPS\Request::i()->ipAddressIsBanned();
	if ( $ipBanned )
	{
		throw new \IPS\Login\Handler\OAuth2\Exception( 'invalid_client', "IP Address banned" );
	}
	
	/* Get the client id and secret */
	$clientId = NULL;
	$clientSecret = NULL;
	if ( isset( $_POST['client_id'] ) )
	{
		$clientId = $_POST['client_id'];
		$clientSecret = isset( $_POST['client_secret'] ) ? $_POST['client_secret'] : NULL;
	}
	elseif ( isset( $_SERVER['PHP_AUTH_USER'] ) )
	{
		$clientId = $_SERVER['PHP_AUTH_USER'];
		$clientSecret = isset( $_SERVER['PHP_AUTH_PW'] ) ? $_SERVER['PHP_AUTH_PW'] : NULL;
	}
	else
	{
		foreach ( $_SERVER as $k => $v )
		{
			if ( mb_substr( $k, -18 ) == 'HTTP_AUTHORIZATION' )
			{
				$exploded = explode( ':', base64_decode( mb_substr( $v, 6 ) ) );
				if ( isset( $exploded[0] ) and isset( $exploded[1] ) )
				{
					$clientId = $exploded[0];
					$clientSecret = isset( $exploded[1] ) ? $exploded[1] : NULL;
				}
			}
		}
	}

	/* Initiate request */
	$request = oAuthServerTokenRequest::init( $clientId, $clientSecret );
	
	/* Validate grant */
	$accessToken = NULL;
	switch ( $request->grantType( \IPS\Request::i()->grant_type ) )
	{
		case 'authorization_code':
			$accessToken = $request->validateAuthorizationCode( \IPS\Request::i()->code, \IPS\Request::i()->redirect_uri );
			break;
		case 'password':
			$accessToken = $request->validatePassword( \IPS\Request::i()->username, \IPS\Request::i()->protect('password'), isset( \IPS\Request::i()->scope ) ? explode( ' ', \IPS\Request::i()->scope ) : NULL );
			break;
		case 'client_credentials':
			$accessToken = $request->validateClientCredentials( isset( \IPS\Request::i()->scope ) ? explode( ' ', \IPS\Request::i()->scope ) : NULL );
			break;
		case 'refresh_token':
			$accessToken = $request->validateRefreshToken( \IPS\Request::i()->refresh_token, isset( \IPS\Request::i()->scope ) ? explode( ' ', \IPS\Request::i()->scope ) : NULL );
			break;
	}
	
	/* Return */
	if ( $accessToken )
	{		
		$response = array( 'access_token' => $accessToken['access_token'], 'token_type' => 'bearer' );
		if ( $accessToken['access_token_expires'] )
		{
			$response['expires_in'] = $accessToken['access_token_expires'] - time();
		}
		if ( $accessToken['refresh_token'] )
		{
			$response['refresh_token'] = $accessToken['refresh_token'];
		}
		if ( $accessToken['scope'] )
		{ 	
			$response['scope'] = implode( ' ', json_decode( $accessToken['scope'], TRUE ) );
		}
		
		\IPS\Output::i()->sendOutput( json_encode( $response ), 200, 'application/json', array( 'Cache-Control' => 'no-store', 'Pragma' => 'no-cache' ), FALSE, FALSE, FALSE );
	}
	else
	{
		throw new \IPS\Login\Handler\OAuth2\Exception( 'invalid_grant', 400 );
	}
}
catch ( \IPS\Login\Handler\OAuth2\Exception $e )
{
	$response = array( 'error' => $e->getMessage() );
	if ( $e->description )
	{
		$response['error_description'] = $e->description;
	}
	\IPS\Output::i()->sendOutput( json_encode( $response ), $e->getMessage() === 'invalid_client' ? 401 : 400, 'application/json', array(), FALSE, FALSE, FALSE );
}
catch ( Exception $e )
{
	\IPS\Output::i()->sendOutput( json_encode( array( 'error' => 'server_error', 'error_description' => $e->getMessage() ) ), 500, 'application/json', array(), FALSE, FALSE, FALSE );
}