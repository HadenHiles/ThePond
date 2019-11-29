<?php
/**
 * @brief		Admin CP Login
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		13 Mar 2013
 */

namespace IPS\core\modules\admin\system;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Admin CP Login
 */
class _login extends \IPS\Dispatcher\Controller
{
	/**
	 * Log In
	 *
	 * @return	void
	 */
	protected function manage()
	{
		/* Do we have an unfinished upgrade? */
		if ( !\IPS\IN_DEV and ( \IPS\RECOVERY_MODE !== TRUE OR !isset( \IPS\Request::i()->noWarning ) ) and \IPS\Settings::i()->setup_in_progress )
		{
			include( \IPS\ROOT_PATH . '/' . \IPS\CP_DIRECTORY . '/upgrade/upgradeStarted.php' );
			session_abort();
			exit;
		}

		/* Do we have an upgrade available to install? */
		if ( !\IPS\IN_DEV and !isset( \IPS\Request::i()->noWarning ) )
		{
			if( \IPS\Application::load('core')->long_version < \IPS\Application::getAvailableVersion('core') and \IPS\Application::load('core')->version != \IPS\Application::getAvailableVersion( 'core', TRUE ) )
			{
				include( \IPS\ROOT_PATH . '/' . \IPS\CP_DIRECTORY . '/upgrade/upgradeAvailable.php' );
				session_abort();
				exit;
			}
		}

		/* Init login class */
		$url = \IPS\Http\Url::internal( "app=core&module=system&controller=login", 'admin' );
		if ( isset( \IPS\Request::i()->noWarning ) )
		{
			$url = $url->setQueryString( 'noWarning', 1 );
		}
		if ( isset( \IPS\Request::i()->ref ) )
		{
			$url = $url->setQueryString( 'ref', \IPS\Request::i()->ref );
		}
		$login = new \IPS\Login( $url, \IPS\Login::LOGIN_ACP );
		
		/* Authenticate */
		$error = NULL;
		try
		{
			/* If we were successful... */
			if ( $success = $login->authenticate() )
			{
				/* Check we can actually access the ACP */
				if ( $success->member->isAdmin() )
				{
					/* If we need to do two-factor authentication, do that */
					if ( $success->mfa( 'AuthenticateAdmin' ) )
					{
						$_SESSION['processing2FA'] = array( 'memberId' => $success->member->member_id );
						
						$url = \IPS\Http\Url::internal( 'app=core&module=system&controller=login&do=mfa', 'admin', 'login' );
						if ( isset( \IPS\Request::i()->ref ) )
						{
							$url = $url->setQueryString( 'ref', \IPS\Request::i()->ref );
						}
						if ( isset( \IPS\Request::i()->auth ) )
						{
							$url = $url->setQueryString( 'auth', \IPS\Request::i()->auth );
						}
						\IPS\Output::i()->redirect( $url );
					}

					$success->device->updateAfterAuthentication( FALSE, $success->handler, FALSE, FALSE );
					/* Otherwise go ahead */
					$this->_doLogin( $success->member );
				}
				/* ... otherwise show an error */
				else
				{
					$error = 'no_access_cp';
					$this->log( 'fail' );
				}
			}
		}
		catch ( \IPS\Login\Exception $e )
		{
			$error = $e->getMessage();
			$this->log( 'fail' );
		}
		
		/* Have we been sent here because of an IP address mismatch? */
		if ( \is_null( $error ) AND isset( \IPS\Request::i()->error ) )
		{
			switch( \IPS\Request::i()->error )
			{
				case 'BAD_IP':
					$error = \IPS\Member::loggedIn()->language()->addToStack( 'cp_bad_ip' );
				break;
				
				case 'NO_ACPACCESS':
					$error = \IPS\Member::loggedIn()->language()->addToStack( 'no_access_cp' );
				break;
			}
		}

		/* Display Login Form */
		\IPS\Output::i()->jsFiles = array_merge( \IPS\Output::i()->jsFiles, \IPS\Output::i()->js( 'admin_system.js', 'core', 'admin' ) );
		\IPS\Output::i()->cssFiles = array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( 'system/login.css', 'core', 'admin' ) );
		\IPS\Output::i()->sendOutput( \IPS\Theme::i()->getTemplate( 'system' )->login( $login, $error, FALSE ) );
	}
	
	/**
	 * MFA
	 *
	 * @param	string	$mfaOutput	If coming from _doLogin, the existing MFA output
	 * @return	void
	 */
	protected function mfa( $mfaOutput=NULL )
	{
		/* Have we logged in? */
		$member = NULL;
		if ( isset( $_SESSION['processing2FA']  ) )
		{
			$member = \IPS\Member::load( $_SESSION['processing2FA']['memberId'] );
		}
		if ( !$member AND !$member->member_id )
		{
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( '', 'admin' ) );
		}
		
		/* Set the referer in the URL */
		$url = \IPS\Http\Url::internal( 'app=core&module=system&controller=login&do=mfa', 'admin', 'login' );
		if ( isset( \IPS\Request::i()->ref ) )
		{
			$url = $url->setQueryString( 'ref', \IPS\Request::i()->ref );
		}
		if ( isset( \IPS\Request::i()->auth ) )
		{
			$url = $url->setQueryString( 'auth', \IPS\Request::i()->auth );
		}
		
		/* Have we already done 2FA? */
		$output = $mfaOutput ?: \IPS\MFA\MFAHandler::accessToArea( 'core', 'AuthenticateAdmin', $url, $member );
		if ( !$output )
		{			
			$this->_doLogin( $member, TRUE );
		}
		
		/* Nope, displau the 2FA form over the login page */
		\IPS\Output::i()->cssFiles = array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( 'system/login.css', 'core', 'admin' ) );
		\IPS\Output::i()->sendOutput( \IPS\Theme::i()->getTemplate( 'system' )->mfaLogin( $output ) );
	}
	
	/**
	 * Process log in
	 *
	 * @param	\IPS\Member		$member			The member
	 * @param	bool			$bypass2FA		If true, will not perform 2FA check
	 * @return	void
	 */
	protected function _doLogin( $member, $bypass2FA = FALSE )
	{
		/* Check if we need to send any ACP notifications */
		\IPS\core\extensions\core\AdminNotifications\ConfigurationError::runChecksAndSendNotifications();
		
		/* Set the referer in the URL */
		$url = \IPS\Http\Url::internal( 'app=core&module=system&controller=login&do=mfa', 'admin', 'login' );
		if ( isset( \IPS\Request::i()->ref ) )
		{
			$url = $url->setQueryString( 'ref', \IPS\Request::i()->ref );
		}
		if ( isset( \IPS\Request::i()->auth ) )
		{
			$url = $url->setQueryString( 'auth', \IPS\Request::i()->auth );
		}

		/* Do we need to do 2FA? */
		if ( !$bypass2FA and $output = \IPS\MFA\MFAHandler::accessToArea( 'core', 'AuthenticateAdmin', $url, $member ) )
		{
			$_SESSION['processing2FA'] = array( 'memberId' => $member->member_id );

			return $this->mfa( $output );
		}
		
		/* Set the member */
		\IPS\Session::i()->setMember( $member );
		
		/* Log */
		$this->log( 'ok' );

		/* Clean out any existing session ID in the URL */
		$queryString = array();
		if( isset( \IPS\Request::i()->ref ) )
		{
			$_queryString = array();
			parse_str( preg_replace( "/adsess=([a-zA-Z0-9]+)(?:&|$)/", '', base64_decode( \IPS\Request::i()->ref ) ), $_queryString );
			foreach ( $_queryString as $k => $v )
			{
				if ( \in_array( $k, array( 'app', 'module', 'controller', 'id' ) ) )
				{
					$queryString[ $k ] = $v;
				}
			}
		}
		
		/* Set the cookie */
		\IPS\Request::i()->setCookie( 'acp_login_key', \IPS\Session::i()->cookieKey );
		
		/* Boink - if we're in recovery mode, go there */
		if ( \IPS\RECOVERY_MODE === TRUE )
		{
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=core&module=support&controller=recovery" ), '', 303 );
		}
		else
		{
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( http_build_query( $queryString, '', '&' ) ), '', 303 );
		}
	}
		
	/**
	 * Log Out
	 *
	 * @return void
	 */
	protected function logout()
	{
		session_destroy();
		\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=core&module=system&controller=login&_fromLogout=1" )->csrf() );
	}
	
	/**
	 * Log
	 *
	 * @param	string	$status	Status ['fail','ok']
	 * @return void
	 */
	protected function log( $status )
	{
		/* Generate request details */
		foreach( \IPS\Request::i() as $k => $v )
		{
			if ( $k == 'password' AND mb_strlen( $v ) > 1 )
			{
				$v = $v ? ( (mb_strlen( $v ) - 1) > 0 ? str_repeat( '*', mb_strlen( $v ) - 1 ) : '' ) . mb_substr( $v, -1, 1 ) : '';
			}
			$request[ $k ] = $v;
		}
		
		$save = array(
			'admin_ip_address'		=> \IPS\Request::i()->ipAddress(),
			'admin_username'		=> \IPS\Request::i()->auth ?: '',
			'admin_time'			=> time(),
			'admin_success'			=> ( $status == 'ok' ) ? 1 : 0,
			'admin_request'	=> json_encode( $request ),
		);
		
		\IPS\Db::i()->insert( 'core_admin_login_logs', $save );
	}
	
}