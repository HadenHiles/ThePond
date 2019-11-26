<?php
/**
 * @brief		Front Session Handler
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		11 Mar 2013
 */

namespace IPS\Session;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Front Session Handler
 */
class _Front extends \IPS\Session
{
	const LOGIN_TYPE_MEMBER = 0;
	const LOGIN_TYPE_ANONYMOUS = 1;
	const LOGIN_TYPE_GUEST = 2;
	const LOGIN_TYPE_SPIDER = 3;
	const LOGIN_TYPE_INCOMPLETE = 4;
	
	/**
	 * Guess if the user is logged in
	 *
	 * This is a lightweight check that does not rely on other classes. It is only intended
	 * to be used by the guest caching mechanism so that it can check if the user is logged
	 * in before other classes are initiated.
	 *
	 * This method MUST NOT be used for other purposes as it IS NOT COMPLETELY ACCURATE.
	 *
	 * @return	bool
	 */
	public static function loggedIn()
	{
		return isset( \IPS\Request::i()->cookie['member_id'] ) and \IPS\Request::i()->cookie['member_id'];
	}
	
	/**
	 * @brief	Session Data
	 */
	protected $data	= array();
	
	/**
	 * @brief	Needs saving?
	 */
	protected $save	= TRUE;
	
	/**
	 * @brief	No write guest session?
	 */
	protected $noWriteGuestSession = FALSE;
	
	/**
	 * Open Session
	 *
	 * @param	string	$savePath	Save path
	 * @param	string	$sessionName Session Name
	 * @return	void
	 */
	public function open( $savePath, $sessionName )
	{
		return TRUE;
	}
	
	/**
	 * Read Session
	 *
	 * @param	string	$sessionId	Session ID
	 * @return	string
	 */
	public function read( $sessionId )
	{
		$this->sessionId = $sessionId;
		
		/* Get user agent info */
		$this->userAgent = \IPS\Http\Useragent::parse();
		
		if ( ! static::loggedIn() and isset( \IPS\Request::i()->cookie['guestTime'] ) and ( !isset( \IPS\Request::i()->cookie['noCache'] ) or !\IPS\Request::i()->cookie['noCache'] ) and time() < ( \IPS\Request::i()->cookie['guestTime'] + \IPS\CACHE_PAGE_TIMEOUT ) and \IPS\Request::i()->requestMethod() == 'GET' )
		{
			$this->sessionData = $this->setNoWriteGuestSession();
			return (string) $this->sessionData['data'];
		}

		$session = \IPS\Session\Store::i()->loadSession( $this->sessionId );
		
		/* Only use sessions with matching IP address */
		if( \IPS\Settings::i()->match_ipaddress and $session['ip_address'] != \IPS\Request::i()->ipAddress() )
		{
			$session = NULL;
		}

		/* We match bots by browser and IP address, so reset our session ID if we found a matching bot row */
		if( $session AND $session['uagent_type'] == 'search' AND $session['id'] != $this->sessionId )
		{
			$this->sessionId = $session['id'];
		}

		/* Store this so plugins can access */
		$this->sessionData	= $session;

		/* Got one? */
		if ( $session )
		{
			/* If this is a guest and the "running time" on this is less than the guest page cache, or if a member and less than 15 seconds ago, we don't need a database write */
			if ( ( !$session['member_id'] and $session['running_time'] < ( time() - \IPS\CACHE_PAGE_TIMEOUT ) ) or ( $session['member_id'] and $session['running_time'] < ( time() - 15 ) ) )
			{
				$this->save = TRUE;
			}
			else
			{
				$this->save = FALSE;
			}
						
			/* Set member */
			try
			{
				$this->member = \IPS\Member::load( (int) $session['member_id'] );
			}
			catch ( \OutOfRangeException $e )
			{
				$this->member = new \IPS\Member;
			}
		}
		/* We might be able to get the member from a cookie */
		else
		{
			$this->member = new \IPS\Member;
		}

		/* If we don't have a member, check the cookies */
		$device = NULL;
		if ( !$this->member->member_id and isset( \IPS\Request::i()->cookie['device_key'] ) and isset( \IPS\Request::i()->cookie['member_id'] ) and isset( \IPS\Request::i()->cookie['login_key'] ) )
		{
			/* Get the member we're trying to authenticate against - do not process cookie-based login if the account is locked */
			$member = \IPS\Member::load( (int) \IPS\Request::i()->cookie['member_id'] );
			if ( $member->member_id and $member->unlockTime() === FALSE )
			{
				/* Load and authenticate device device data */
				try
				{
					/* Authenticate */
					$device = \IPS\Member\Device::loadAndAuthenticate( \IPS\Request::i()->cookie['device_key'], $member, \IPS\Request::i()->cookie['login_key'] );
					
					/* Refresh the device key cookie */
					\IPS\Request::i()->setCookie( 'device_key', \IPS\Request::i()->cookie['device_key'], ( new \IPS\DateTime )->add( new \DateInterval( 'P1Y' ) ) );
					
					/* Set member in session */
					$this->member = $member;
					
					/* Update device */
					$device->updateAfterAuthentication( TRUE, NULL, FALSE );
				}
				/* If the device_key/login_key combination wasn't valid, this may be someone trying to bruteforce... */
				catch ( \OutOfRangeException $e )
				{
					/* ... so log it as a failed login */
					$failedLogins = \is_array( $member->failed_logins ) ? $member->failed_logins : array();
					$failedLogins[ \IPS\Request::i()->ipAddress() ][] = time();
					$member->failed_logins = $failedLogins;
					$member->save();
					
					/* Then set us as a guest and clear out those cookies */
					$this->member = new \IPS\Member;
					\IPS\Request::i()->clearLoginCookies();
				}
			}
			// If the member no longer exists, or the account is locked, set us as a guest and clear out those cookies
			else
			{
				$this->member = new \IPS\Member;
				\IPS\Request::i()->clearLoginCookies();
			}
		}
								
		/* Work out the type */
		if ( $this->member->member_id )
		{
			if ( ( $session and $session['login_type'] === static::LOGIN_TYPE_ANONYMOUS ) or ( $device and $device->anonymous ) OR $this->member->group['g_hide_online_list'] )
			{
				$type = static::LOGIN_TYPE_ANONYMOUS;
			}
			else if ( !$this->member->name or !$this->member->email )
			{
				$type = static::LOGIN_TYPE_INCOMPLETE;
			}
			else
			{
				$type = static::LOGIN_TYPE_MEMBER;
			}

			/* Need to update member row? */
			if ( $type === static::LOGIN_TYPE_ANONYMOUS AND ! $this->member->members_bitoptions['is_anon'] )
			{
				$this->member->members_bitoptions['is_anon'] = TRUE;
				$this->member->save();
			}
			if ( $type !== static::LOGIN_TYPE_ANONYMOUS AND $this->member->members_bitoptions['is_anon'] )
			{
				$this->member->members_bitoptions['is_anon'] = FALSE;
				$this->member->save();
			}
		}
		else
		{
			\IPS\Request::i()->setCookie( 'guestTime', time() );
			
			$type = $this->userAgent->spider ? static::LOGIN_TYPE_SPIDER : static::LOGIN_TYPE_GUEST;
		}

		/* Set data */
		$this->data = array(
			'id'						=> $this->sessionId,
			'member_name'				=> $this->member->member_id ? $this->member->name : '',
			'seo_name'					=> $this->member->member_id ? ( $this->member->members_seo_name ?: '' ) : '',
			'member_id'					=> $this->member->member_id ?: 0,
			'ip_address'				=> \IPS\Request::i()->ipAddress(),
			'browser'					=> isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '',
			/* We do not want ajax calls to update running time as this affects appearance of being online. If no session exists, we do not want ajax polling to trigger an online list hit so we set running time for time - 31 minutes as
			   online lists look for running times less than 30 minutes. */
			'running_time'				=> ( \IPS\Request::i()->isAjax() ) ? ( $session ? $session['running_time'] : time() - 1860 ) : time(), 
			'login_type'				=> $type,
			'member_group'				=> ( $this->member->member_id ) ? $this->member->member_group_id : \IPS\Settings::i()->guest_group,
			'current_appcomponent'		=> ( \IPS\Request::i()->isAjax() ) ? ( $session ? $session['current_appcomponent'] : '' ) : '',
			'current_module'			=> ( \IPS\Request::i()->isAjax() ) ? ( $session ? $session['current_module'] : '' ) : '',
			'current_controller'		=> ( \IPS\Request::i()->isAjax() ) ? ( $session ? $session['current_controller'] : NULL ) : NULL,
			'current_id'				=> ( \IPS\Request::i()->isAjax() ) ? ( $session ? $session['current_id'] : NULL ) : \intval( \IPS\Request::i()->id ),
			'uagent_key'				=> $this->userAgent->browser ?: '',
			'uagent_version'			=> $this->userAgent->browserVersion ?: '',
			'uagent_type'				=> $this->userAgent->spider ? 'search' : 'browser',
			'search_thread_id'			=> $session ? \intval( $session['search_thread_id'] ) : 0,
			'search_thread_time'		=> $session ? $session['search_thread_time'] : 0,
			'data'						=> $session ? $session['data'] : '',
			'location_url'				=> $session ? $session['location_url'] : NULL,
			'location_lang'				=> $session ? $session['location_lang'] : NULL,
			'location_data'				=> $session ? $session['location_data'] : NULL,
			'location_permissions'		=> $session ? $session['location_permissions'] : NULL,
			'theme_id'					=> $session ? $session['theme_id'] : 0,
			'in_editor'					=> ( \IPS\Request::i()->isAjax() ) ? ( $session ? $session['in_editor'] : 0 ) : 0,
			
		);

		/* Is this a spider? */
		if( $this->userAgent->spider )
		{
			/* Is this Facebook? Do we need to treat them as a user of a different group? */
			if( $this->userAgent->spider == 'facebook' )
			{
				if( \IPS\core\ShareLinks\Service::load( 'facebook', 'share_key' )->enabled )
				{
					if( $this->userAgent->facebookIpVerified( \IPS\Request::i()->ipAddress() ) AND \IPS\Settings::i()->fbc_bot_group != \IPS\Settings::i()->guest_group )
					{
						$this->member->member_group_id	= \IPS\Settings::i()->fbc_bot_group;
					}
				}
			}
		}

		/* Session read() method MUST return a string, or this can result in PHP errors */
		return (string) $this->data['data'];
	}

	/**
	 * Set Session Member
	 *
	 * @param	\IPS\Member	$member	Member object
	 * @return	void
	 */
	public function setMember( $member )
	{
		parent::setMember( $member );

		/* Make sure session handler saves during write() */
		$this->save = TRUE;
	}

	/**
	 * Write Session
	 *
	 * @param	string	$sessionId	Session ID
	 * @param	string	$data		Session Data
	 * @return	bool
	 */
	public function write( $sessionId, $data )
	{
		if ( $this->noWriteGuestSession and empty( $_SESSION['forcedWrite'] ) )
		{
			return TRUE;
		}
		
		if ( !isset( $this->data['data'] ) or $data !== $this->data['data'] or $this->data['member_id'] != $this->member->member_id )
		{
			$this->save = TRUE;
		}
		
		/* Don't update if instant notifications are checking to reduce overhead on the session table */
		if ( \IPS\Request::i()->isAjax() and isset( \IPS\Request::i()->app ) and \IPS\Request::i()->app === 'core' and isset( \IPS\Request::i()->controller ) and \IPS\Request::i()->controller === 'ajax' and isset( \IPS\Request::i()->do ) and \IPS\Request::i()->do === 'instantNotifications' )
		{
			$this->save = FALSE;
		}

		$this->data['member_name']	= $this->member->member_id ? $this->member->name : '';
		$this->data['member_id']	= $this->member->member_id ?: NULL;
		$this->data['data']			= $data;
		$this->setLocationData();

		if ( $this->save === TRUE and ( !empty( \IPS\Request::i()->cookie ) or $this->userAgent->spider or $this->member->member_id ) ) // If a guest and cookies are disabled we do not write to database to prevent duplicate sessions unless it's a search engine, which we deal with separately
		{
			\IPS\Session\Store::i()->updateSession( $this->data );
		}
		
		return TRUE;
	}
	
	/**
	 * Do not update sessions
	 *
	 * @return void
	 */
	public function noUpdate()
	{
		$this->save = FALSE;
	}
	
	/**
	 * @brief	Stored engine
	 */
	protected static $engine = NULL;
		
	/**
	 * Clear sessions - abstracted so it can be called externally without initiating a session
	 *
	 * @param	int		$timeout	Sessions older than the number of seconds provided will be deleted
	 * @return void
	 */
	public static function clearSessions( $timeout )
	{
		/* Cannot change this from a static method as it is called on garbage collection */
		\IPS\Session\Store::i()->clearSessions( $timeout );
	}
	
	/**
	 * Set the search start
	 *
	 * @return	void
	 */
	public function startSearch()
	{
		$this->data['search_thread_id']		= \IPS\Db::i()->thread_id;
		$this->data['search_thread_time']	= time();
	}

	/**
	 * Set the search end
	 *
	 * @return	void
	 */
	public function endSearch()
	{
		$this->data['search_thread_id']		= 0;
		$this->data['search_thread_time']	= 0;
	}
	
	/**
	 * Set a theme ID
	 *
	 * @param	int		$themeId		The theme id, of course
	 * @return	void
	 */
	public function setTheme( $themeId )
	{
		if( !\IPS\Dispatcher::hasInstance() OR \IPS\Request::i()->isAjax() )
		{
			return;
		}
		
		$this->data['theme_id'] = $themeId;
		
		$this->save = TRUE;
	}
	
	/**
	 * Get the theme ID
	 *
	 * @return	int
	 */
	public function getTheme()
	{
		if ( isset( $this->data['theme_id'] ) and $this->data['theme_id'] )
		{
			return $this->data['theme_id'];
		}
		
		return NULL;
	}
	
	/**
	 * Set basic location data
	 *
	 * @return	void
	 */
	public function setLocationData()
	{
		if( !\IPS\Dispatcher::hasInstance() OR \IPS\Request::i()->isAjax() )
		{
			return;
		}

		$this->data['current_appcomponent']	= \IPS\Dispatcher::i()->application ? \IPS\Dispatcher::i()->application->directory : '';
		$this->data['current_module']		= \IPS\Dispatcher::i()->module ? \IPS\Dispatcher::i()->module->key : '';
		$this->data['current_controller']	= \IPS\Dispatcher::i()->controller;
		$this->data['current_id']			= \intval( \IPS\Request::i()->id );
	}
	
	/**
	 * Set user as editing
	 *
	 * @return	void
	 */
	public function setUsingEditor()
	{
		$this->data['in_editor'] = time();
	}
	
	/**
	 * Set the session location
	 *
	 * @param	\IPS\Http\Url	$url		URL
	 * @param	array			$groupIds	Permission data
	 * @param	string			$lang		Language string
	 * @param	array			$data		Language data. Keys are the words, value is a boolean indicating if it's a language key (TRUE) or should be displayed as-is (FALSE)
	 * @return	void
	 */
	public function setLocation( \IPS\Http\Url $url, $groupIds, $lang, $data=array() )
	{
		if( !\IPS\Dispatcher::hasInstance() OR \IPS\Request::i()->isAjax() )
		{
			return;
		}

		$this->data['location_url'] = (string) $url;
		$this->data['location_lang'] = $lang;
		$this->data['location_data'] = json_encode( $data );
        $this->data['current_id'] = \intval( \IPS\Request::i()->id );
		
		if ( !$this->data['current_appcomponent'] )
		{
			$this->setLocationData();
		}
		
		/* Some places use 0 to mean no permission at all but this is lost in the code below */
		if ( $groupIds === 0 )
		{
			$groupIds = (string) $groupIds;
		}		
	
		$groupIds = \is_string( $groupIds ) ? explode( ',', $groupIds ) : ( $groupIds ?: NULL );
				
		$app = \IPS\Application::load( $this->data['current_appcomponent'] );
		if ( !$app->enabled )
		{			
			$groupIds = $groupIds ? array_intersect( $groupIds, explode( ',', $app->disabled_groups ) ) : explode( ',', $app->disabled_groups );
		}
		
		$modulePermissions = \IPS\Application\Module::get( $this->data['current_appcomponent'], $this->data['current_module'], 'front' )->permissions();
		if ( $modulePermissions['perm_view'] !== '*' )
		{
			$groupIds = $groupIds ? array_intersect( $groupIds, explode( ',', $modulePermissions['perm_view'] ) ) : explode( ',', $modulePermissions['perm_view'] );
		}

		$this->data['location_permissions'] = ( $groupIds !== NULL ) ? ( \is_string( $groupIds ) ? $groupIds : implode( ',', $groupIds ) ) : NULL;

		$this->save = TRUE;
	}
	
	/**
	 * Get the session location
	 * 
	 * @param	array			$row		Row from sessions
	 * @return	string|null
	 */
	public static function getLocation( $row )
	{
		$location = NULL;

		if( !$row['location_lang'] )
		{
			return $location;
		}

		try
		{
			if ( $row['location_permissions'] === NULL or $row['location_permissions'] === '*' or \IPS\Member::loggedIn()->inGroup( explode( ',', $row['location_permissions'] ), TRUE ) )
			{
				$sprintf = array();
				$data = json_decode( $row['location_data'], TRUE );

				if ( !empty( $data ) )
				{
					foreach ( $data as $key => $parse )
					{
						$value		= htmlspecialchars( $parse ? \IPS\Member::loggedIn()->language()->get( $key ) : $key, ENT_DISALLOWED, 'UTF-8', FALSE );
						$sprintf[]	= $value;
					}
				}

				$location = \IPS\Member::loggedIn()->language()->addToStack( htmlspecialchars( $row['location_lang'], ENT_DISALLOWED, 'UTF-8', FALSE ), FALSE, array( 'htmlsprintf' => $sprintf ) );

				$location	= "<a href='" . htmlspecialchars( $row['location_url'], ENT_DISALLOWED, 'UTF-8', FALSE ) . "'>" . $location . "</a>";
			}
		}
		catch ( \UnderflowException $e ){ }
		
		return $location;
	}
	
	/**
	 * Set the session "login_type"
	 *
	 * @param	int		$type	Type as defined by the class constants
	 * @return	void
	 */
	public function setType( $type )
	{
		switch ( $type )
		{
			case static::LOGIN_TYPE_MEMBER:
			case static::LOGIN_TYPE_ANONYMOUS:
			case static::LOGIN_TYPE_GUEST:
			case static::LOGIN_TYPE_SPIDER:
			case static::LOGIN_TYPE_INCOMPLETE:
				$this->data['login_type'] = $type;
			break;
			default:
				throw new \OutOfRangeException();
			break;
		}
	}
	
	/**
	 * Set the session as anonymous
	 *
	 * @return	void
	 */
	public function setAnon()
	{
		$this->setType( static::LOGIN_TYPE_ANONYMOUS );
	}
	
	/**
	 * Set the session as anonymous
	 *
	 * @return	void
	 */
	public function getAnon()
	{
		return (bool) $this->data['login_type'] == static::LOGIN_TYPE_ANONYMOUS;
	}
	
	/**
	 * Close Session
	 *
	 * @return	bool
	 */
	public function close()
	{
		return TRUE;
	}
	
	/**
	 * Destroy Session
	 *
	 * @param	string	$sessionId	Session ID
	 * @return	bool
	 */
	public function destroy( $sessionId )
	{
		if ( isset( $_SESSION['wizardKey'] ) )
		{
			$dataKey = $_SESSION['wizardKey'];
			unset( \IPS\Data\Store::i()->$dataKey );
		}
		
		\IPS\Session\Store::i()->deleteSession( $sessionId );
		return TRUE;
	}
	
	/**
	 * Garbage Collection
	 *
	 * @param	int		$lifetime	Unix timestamp of the oldest session to keep
	 * @return	bool
	 */
	public function gc( $lifetime )
	{
		static::clearSessions( $lifetime );
		return TRUE;
	}
	
	/**
	 * Sets up a session that doesn't require a DB read or write
	 *
	 * @return array
	 */
	protected function setNoWriteGuestSession()
	{
		$this->noWriteGuestSession = TRUE;
		
		$this->member = new \IPS\Member;
		
		/* Set data */
		$this->data = array(
			'id'						=> $this->sessionId,
			'member_name'				=> '',
			'seo_name'					=> '',
			'member_id'					=> 0,
			'ip_address'				=> \IPS\Request::i()->ipAddress(),
			'browser'					=> isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '',
			'running_time'				=> time(), 
			'login_type'				=> $this->userAgent->spider ? static::LOGIN_TYPE_SPIDER : static::LOGIN_TYPE_GUEST,
			'member_group'				=> \IPS\Settings::i()->guest_group,
			'current_appcomponent'		=> '',
			'current_module'			=> '',
			'current_controller'		=> NULL,
			'current_id'				=> \intval( \IPS\Request::i()->id ),
			'uagent_key'				=> $this->userAgent->browser ?: '',
			'uagent_version'			=> $this->userAgent->browserVersion ?: '',
			'uagent_type'				=> $this->userAgent->spider ? 'search' : 'browser',
			'search_thread_id'			=> 0,
			'search_thread_time'		=> 0,
			'data'						=> '',
			'location_url'				=> NULL,
			'location_lang'				=> NULL,
			'location_data'				=> NULL,
			'location_permissions'		=> NULL,
			'theme_id'					=> isset( \IPS\Request::i()->cookie['theme'] ) ? \IPS\Request::i()->cookie['theme'] : 0,
			'in_editor'					=> 0,
		);

		return $this->data;
	}
}