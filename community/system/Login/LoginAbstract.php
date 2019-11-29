<?php
/**
 * @brief		4.2 and below Backwards Compatibilty Login Handler
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - 2016 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Community Suite
 * @since		16 May 2017
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\Login;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * 4.2 and below Backwards Compatibilty Login Handler
 *
 * @deprecated	This class exists only to provide backwards compatibility for login handlers created before 4.3
 */
abstract class _LoginAbstract extends \IPS\Login\Handler
{	
	/**
	 * Get title
	 *
	 * @return	string
	 */
	public static function getTitle()
	{
		return 'login_handler_' . mb_substr( \get_called_class(), 10 );
	}
	
	/**
	 * Get type
	 *
	 * @return	int
	 */
	public function type()
	{
		if ( method_exists( $this, 'loginForm' ) )
		{
			return \IPS\Login::TYPE_BUTTON;
		}
		else
		{
			return \IPS\Login::TYPE_USERNAME_PASSWORD;
		}
	}
	
	/**
	 * Get auth type
	 *
	 * @return	int
	 */
	public function authType()
	{
		return $this->authTypes;
	}
	
	/**
	 * Get button
	 *
	 * @return	string
	 */
	public function button()
	{
		$destination = NULL;
		if ( isset( \IPS\Request::i()->ref ) )
		{
			try
			{
				$url = \IPS\Http\Url::createFromString( base64_decode( \IPS\Request::i()->ref ) );
				if ( $url instanceof \IPS\Http\Url\Internal and !$url->openRedirect() )
				{
					$destination = $url;
				}
			}
			catch ( \Exception $e ) { }
		}

		try
		{
			return $this->loginForm( \IPS\Request::i()->url(), FALSE, $destination );
		}
		catch ( \Exception $exception )
		{
			\IPS\Log::log( $exception, 'login_handler' );
		}
	}
	
	/**
	 * Authenticate
	 *
	 * @param	\IPS\Login	$login				The login object
	 * @param	string		$usernameOrEmail		The username or email address provided by the user
	 * @param	object		$password			The plaintext password provided by the user, wrapped in an object that can be cast to a string so it doesn't show in any logs
	 * @return	\IPS\Member
	 * @throws	\IPS\Login\Exception
	 */
	public function authenticateUsernamePassword( \IPS\Login $login, $usernameOrEmail, $password )
	{
		return $this->authenticate( array( 'auth' => $usernameOrEmail, 'password' => (string) $password ) );
	}
	
	/**
	 * Authenticate
	 *
	 * @param	\IPS\Login	$login				The login object
	 * @return	\IPS\Member
	 * @throws	\IPS\Login\Exception
	 */
	public function authenticateButton( \IPS\Login $login )
	{
		return $this->authenticate( $login->url );
	}
	
	/**
	 * Create an account from login - checks registration is enabled, the name/email doesn't already exists and calls the spam service
	 *
	 * @param	\IPS\Member|NULL	$member				The existing member, if one exists
	 * @param	array				$memberProperties	Any properties to set on the member (whether registering or not) such as IDs from third-party services
	 * @param	string|NULL			$name				The desired username. If not provided, not allowed, or another existing user has this name, it will be left blank and the user prompted to provide it.
	 * @param	string|NULL			$email				The user's email address. If it matches an existing account, an \IPS\Login\Exception object will be thrown so the user can be prompted to link those accounts. If not provided, it will be left blank and the user prompted to provide it.
	 * @param	mixed				$details			If $email matches an existing account, this is wgat will later be provided to link() - include any data you will need to link the accounts later
	 * @param	array|NULL			$profileSync		If creating a new account, the default profile sync settings for this provider
	 * @param	string|NULL			$profileSyncClass	If $profileSync is enabled, the profile sync service with a name matching this login handler will be used. Provide an alternative classname to override (e.g. for Windows login, the login handler class is Live, but the profile sync class is Microsoft)
	 * @return	\IPS\Member
	 * @throws	\IPS\Login\Exception	If email address matches (\IPS\Login\Exception::MERGE_SOCIAL_ACCOUNT), registration is disabled (IPS\Login\Exception::REGISTRATION_DISABLED) or the spam service denies registration (\IPS\Login\Exception::REGISTRATION_DENIED_BY_SPAM_SERVICE)
	 */
	protected function createOrUpdateAccount( $member, $memberProperties=array(), $name=NULL, $email=NULL, $details=NULL, $profileSync=NULL, $profileSyncClass=NULL )
	{
		/* Create an account */
		if ( !$member or !$member->member_id )
		{
			try
			{
				$member = $this->createAccount( $name, $email );
			}
			catch ( \IPS\Login\Exception $e )
			{
				if ( $details )
				{
					$e->details = $details;
				}
				throw $e;
			}
			
			/* Member properties */
			foreach ( $memberProperties as $k => $v )
			{
				$member->$k = $v;
			}
			$member->save();
		}
		
		/* Or just update? */
		else
		{
			foreach ( $memberProperties as $k => $v )
			{
				$member->$k = $v;

				if( $k == 'members_pass_hash' or $k == 'email' )
				{
					$member->invalidateSessionsAndLogins( \IPS\Session::i()->id );
				}
			}
			$member->save();
		}
		
		/* Return */
		return $member;
	}
	
	/**
	 * Can this handler process a password change for a member? 
	 *
	 * @param	\IPS\Member	$member		The member
	 * @return	bool
	 */
	public function canChangePassword( \IPS\Member $member )
	{
		return method_exists( $this, 'canChange' ) ? $this->canChange( 'password', $member ) : FALSE;
	}
	
	/**
	 * Link Account
	 *
	 * @param	\IPS\Member	$member		The member
	 * @param	mixed		$details	Details as they were passed to the exception
	 * @return	void
	 */
	public function completeLink( \IPS\Member $member, $details )
	{
		static::link( $member, $details );
	}
}