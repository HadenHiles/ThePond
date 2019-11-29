//<?php

/**
 * WordPress SSO - IPS4
 * 
 * @author		Stuart Silvester
 * @copyright	2017 - Stuart Silvester
 * @link		http://ipb.silvesterwebdesigns.com
 */

class hook3 extends _HOOK_CLASS_
{
	/**
	 * @brief	API data storage
	 */
	public $wpData = [];

	/**
	 * @brief	Cookie name storage
	 */
	public static $wpCookie = null;

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
		try
		{
			if( parent::loggedIn() )
			{
				return TRUE;
			}
	
			if( static::_getWordPressCookie() )
			{
				return TRUE;
			}
	
			return FALSE;
		}
		catch ( \RuntimeException $e )
		{
			if ( method_exists( get_parent_class(), __FUNCTION__ ) )
			{
				return call_user_func_array( 'parent::' . __FUNCTION__, func_get_args() );
			}
			else
			{
				throw $e;
			}
		}
	}

	/**
	 * Read Session
	 *
	 * @param	string	$sessionId	Session ID
	 * @return	string
	 */
	public function read( $sessionId )
	{
		try
		{
			/* Let normal class do its thing */
			$result = call_user_func_array( 'parent::read', func_get_args() );
	
			/* WordPress Cookie */
			$cookieName = $this->_getWordPressCookie();
	
			/* Check for the Cookie, log out if logged in here, but not on master */
			if( $this->member->member_id AND !$cookieName )
			{
				$this->_logout();
				return '';
			}
			/* This is a guest session, no WP data to log them in */
			elseif( !$cookieName )
			{
				return $result;
			}
	
			/* Check the cookie is the one we're expecting */
			if( !$this->_compareWordPressCookie( $cookieName ) )
			{
				/* Log out but purposefully continue so the new member can be logged in */
				$this->_logout();
			}
	
			/* Check the IPS4 session is a valid member */
			if( $this->member->member_id )
			{
				return $result;
			}
	
			/* If we've sent a request in the last 30s, don't send one again */
			if( isset( \IPS\Request::i()->cookies[ $cookieName . '_ips4_request'] ) )
			{
				return $result;
			}
	
			/* Fetch member data from WP API */
			$this->wpData = $this->_getWordPressData( $cookieName );
	
			if( !is_array( $this->wpData ) OR isset( \IPS\Request::i()->cookies['spammer'] ) )
			{
				/* Set cookie so we don't send lots of requests */
				\IPS\Request::i()->setCookie( $cookieName . '_ips4_request', 1, \IPS\DateTime::create()->add( new \DateInterval( 'PT30S' ) ) );
	
				$this->_logout();
				return '';
			}
	
			/* Try loading */
			$this->member = \IPS\Member::load( $this->wpData['user_id'], 'wordpress_id' );
	
			if( !$this->member->member_id )
			{
				$this->member = \IPS\Member::load( $this->wpData['email'], 'email' );
			}
	
			/* Get the member group ID */
			$memberGroups = $this->_getMemberGroups();
	
			/* Check display name is correct */
			if( !empty( $this->wpData['display_name'] ) AND $this->member->name != $this->wpData['display_name'] )
			{
				$this->member->name	= $this->wpData['display_name'];
			}
	
			/* Check email address is correct */
			if( $this->member->email != $this->wpData['email'] )
			{
				$this->member->email = $this->wpData['email'];
			}
	
			/* Check the member has a WordPress ID for future look ups */
			if( !$this->member->wordpress_id AND $this->wpData['user_id'] )
			{
				$this->member->wordpress_id	= $this->wpData['user_id'];
			}
	
			/* Check existing group ID (ignore admins) */
			$primaryGroupId = array_shift( $memberGroups );
			if( $this->member->member_group_id != $primaryGroupId AND !$this->member->isAdmin() )
			{
				$this->member->member_group_id = $primaryGroupId;
			}
	
			/* If enabled, assign the secondary groups */
			if( \IPS\Settings::i()->wordpress_secondary_groups )
			{
				$this->member->mgroup_others = implode( ',', $memberGroups );
			}
	
			/* Spam Service Check */
			if( !$this->member->member_id AND \IPS\Settings::i()->spam_service_enabled AND $this->member->email )
			{
				if( $this->member->spamService() == 4 )
				{
					\IPS\Request::i()->setCookie( 'spammer', 1, \IPS\DateTime::create()->add( new \DateInterval( 'P7D' ) ) );
					return $result;
				}
			}
	
			/* Save any changes to the member object */
			$this->member->wordpress_cookie = md5( $_COOKIE[ $cookieName ] );
			$this->member->save();
	
		/* At this point, we're logged in. - We cannot call setMember here because it will not work with PHP 7.1
		 * -- We cannot call session_regenerate_id() from within the session read.
		 */
			$_SESSION['forcedWrite'] = time();
	
			/* Make sure session handler saves during write() */
			$this->save = TRUE;
	
			/* For 4.2 we need to do some device management stuff */
			if( \IPS\Application::load( 'core' )->long_version >= 101100 )
			{
				\IPS\Member\Device::loadOrCreate( $this->member )->updateAfterAuthentication( NULL );
			}
	
			/* Reset any logged in member */
			\IPS\Member::$loggedInMember = $this->member;
	
			/* Session read() method MUST return a string, or this can result in PHP errors */
			return $result;
		}
		catch ( \RuntimeException $e )
		{
			if ( method_exists( get_parent_class(), __FUNCTION__ ) )
			{
				return call_user_func_array( 'parent::' . __FUNCTION__, func_get_args() );
			}
			else
			{
				throw $e;
			}
		}
	}

	/**
	 * Get users member group id based on role mapping
	 *
	 * @return	array		Array of IPS4 group ids
	 */
	protected function _getMemberGroups()
	{
		try
		{
			$groups = array();
			$groupMap = json_decode( \IPS\Settings::i()->wordpress_group_map, TRUE );
	
			/* Make sure the role response is in the expected format. - Some customised APIs may not return an array */
			if( $this->wpData['role'] AND !is_array( $this->wpData['role'] ) )
			{
				$this->wpData['role'] = array( $this->wpData['role'] );
			}
	
			/* Any assigned roles? */
			$roles = ( $this->wpData['role'] AND count( $this->wpData['role'] ) ) ?  $this->wpData['role'] : array();
	
			/* Group map */
			foreach( $roles as $role )
			{
				$lowerRole = \mb_strtolower( $role );
				if( isset( $groupMap[ $lowerRole ] ) )
				{
					try
					{
						/* Yep, that's a real group */
						$groups[] = \IPS\Member\Group::load( $groupMap[ $lowerRole ] )->g_id;
					}
					catch( \UnderflowException $e ) { }
				}
			}
	
			/* Still here? default member group for you */
			if( !count( $groups ) )
			{
				return array( \IPS\Settings::i()->member_group );
			}
	
			return $groups;
		}
		catch ( \RuntimeException $e )
		{
			if ( method_exists( get_parent_class(), __FUNCTION__ ) )
			{
				return call_user_func_array( 'parent::' . __FUNCTION__, func_get_args() );
			}
			else
			{
				throw $e;
			}
		}
	}

	/**
	 * Compare WP cookie hash to verify the cookie is for the same member.
	 *
	 * @param	string		$cookie		WP Cookie contents
	 * @return	boolean
	 */
	protected function _compareWordPressCookie( $cookie )
	{
		try
		{
			if( !$this->member->wordpress_cookie )
			{
				return FALSE;
			}
			elseif( !\IPS\Login::compareHashes( $this->member->wordpress_cookie, md5( $_COOKIE[ $cookie ] ) ) )
			{
				return FALSE;
			}
	
			return TRUE;
		}
		catch ( \RuntimeException $e )
		{
			if ( method_exists( get_parent_class(), __FUNCTION__ ) )
			{
				return call_user_func_array( 'parent::' . __FUNCTION__, func_get_args() );
			}
			else
			{
				throw $e;
			}
		}
	}

	/**
	 * Fetch cookie name
	 *
	 * @return	boolean|string		FALSE or name of cookie
	 */
	protected static function _getWordPressCookie()
	{
		try
		{
			if( static::$wpCookie !== NULL )
			{
				return static::$wpCookie;
			}
	
			if( count( $_COOKIE ) )
			{
				foreach( $_COOKIE as $k => $v )
				{
					if( \substr( $k, 0, 19 ) === 'wordpress_logged_in' )
					{
						return static::$wpCookie = $k;
					}
				}
			}
	
			/* No cookie was found */
			return static::$wpCookie = FALSE;
		}
		catch ( \RuntimeException $e )
		{
			if ( method_exists( get_parent_class(), __FUNCTION__ ) )
			{
				return call_user_func_array( 'parent::' . __FUNCTION__, func_get_args() );
			}
			else
			{
				throw $e;
			}
		}
	}

	/**
	 * Fetch data from our WordPress API
	 *
	 * @param	string				$cookie
	 * @return	boolean|array
	 */
	protected function _getWordPressData( $cookie )
	{
		try
		{
			try
			{
				return \IPS\Http\Url::external( rtrim( \IPS\Settings::i()->wordpress_url, '/' ) . '/wp_api.php' )
									->setQueryString( [ 'api_key' => \IPS\Settings::i()->wordpress_api_key, 'type' => 'userinfo' ] )
									->request()
									->setHeaders( array( 'Cookie' => $cookie . '=' . $_COOKIE[ $cookie ] ) )
									->get()
									->decodeJson();
			}
			catch( \Exception $ex )
			{
				return FALSE;
			}
		}
		catch ( \RuntimeException $e )
		{
			if ( method_exists( get_parent_class(), __FUNCTION__ ) )
			{
				return call_user_func_array( 'parent::' . __FUNCTION__, func_get_args() );
			}
			else
			{
				throw $e;
			}
		}
	}

	/**
	 * Log the member out of IPS4
	 *
	 * @return	void
	 */
	protected function _logout()
	{
		try
		{
			$this->member = new \IPS\Member;
	
			\IPS\Request::i()->setCookie( 'member_id', NULL );
			\IPS\Request::i()->setCookie( 'pass_hash', NULL );
	
			/* Set data */
			$this->data = array_merge(
										$this->data,
										array(
											'member_name'				=> $this->member->name,
											'seo_name'					=> $this->member->members_seo_name,
											'member_id'					=> $this->member->member_id,
											'member_group'				=> \IPS\Settings::i()->guest_group,
									)
			);
		}
		catch ( \RuntimeException $e )
		{
			if ( method_exists( get_parent_class(), __FUNCTION__ ) )
			{
				return call_user_func_array( 'parent::' . __FUNCTION__, func_get_args() );
			}
			else
			{
				throw $e;
			}
		}
	}
}