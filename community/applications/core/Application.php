<?php
/**
 * @brief		Core Application Class
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		18 Feb 2013
 */
 
namespace IPS\core;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Core Application Class
 */
class _Application extends \IPS\Application
{
	/**
	 * @brief	Cached advertisement count
	 */
	protected $advertisements	= NULL;
	
	/**
	 * @brief	Cached clubs pending approval count
	 */
	protected $clubs = NULL;

	/**
	 * ACP Menu Numbers
	 *
	 * @param	array	$queryString	Query String
	 * @return	int
	 */
	public function acpMenuNumber( $queryString )
	{
		parse_str( $queryString, $queryString );
		switch ( $queryString['controller'] )
		{
			case 'advertisements':
				if( $this->advertisements === NULL )
				{
					$this->advertisements	= \IPS\Db::i()->select( 'COUNT(*)', 'core_advertisements', array( 'ad_active=-1' ) )->first();
				}
				return $this->advertisements;
			
			case 'clubs':
				if( $this->clubs === NULL )
				{
					$this->clubs	= \IPS\Db::i()->select( 'COUNT(*)', 'core_clubs', array( 'approved=0' ) )->first();
				}
				return $this->clubs;
		}
		
		return 0;
	}
	
	/**
	 * Returns the ACP Menu JSON for this application.
	 *
	 * @return array
	 */
	public function acpMenu()
	{
		$menu = parent::acpMenu();
		
		if ( \IPS\DEMO_MODE )
		{
			unset( $menu['support'] );
		}
				
		return $menu;
	}
	
	/**
	 * Return the custom badge for each row
	 *
	 * @return	NULL|array		Null for no badge, or an array of badge data (0 => CSS class type, 1 => language string, 2 => optional raw HTML to show instead of language string)
	 */
	public function get__badge()
	{
		$return = parent::get__badge();
		
		if ( $return )
		{
			$availableUpgrade = $this->availableUpgrade( TRUE, FALSE );
			$return[2] = \IPS\Theme::i()->getTemplate( 'global', 'core' )->updatebadge( $availableUpgrade['version'], \IPS\Http\Url::internal( 'app=core&module=system&controller=upgrade', 'admin' ), (string) \IPS\DateTime::ts( $availableUpgrade['released'] )->localeDate(), FALSE );
		}
		
		return $return;
	}

	/**
	 * [Node] Get Icon for tree
	 *
	 * @note	Return the class for the icon (e.g. 'globe')
	 * @return	string|null
	 */
	protected function get__icon()
	{
		return 'cogs';
	}

	/**
	 * Install Other
	 *
	 * @return	void
	 */
	public function installOther()
	{
		/* Save installed domain to spam defense whitelist */
		$domain = rtrim( str_replace( 'www.', '', parse_url( \IPS\Settings::i()->base_url, PHP_URL_HOST ) ), '/' );
		\IPS\Db::i()->insert( 'core_spam_whitelist', array( 'whitelist_type' => 'domain', 'whitelist_content' => $domain, 'whitelist_date' => time(), 'whitelist_reason' => 'Invision Community Domain' ) );
	}
	
	/**
	 * Can view page even when user is a guest when guests cannot access the site
	 *
	 * @param	\IPS\Application\Module	$module			The module
	 * @param	string					$controller		The controller
	 * @param	string|NULL				$do				To "do" parameter
	 * @return	bool
	 */
	public function allowGuestAccess( \IPS\Application\Module $module, $controller, $do )
	{
		return (
			$module->key == 'system'
			and
			\in_array( $controller, array( 'login', 'register', 'lostpass', 'terms', 'ajax', 'privacy', 'editor', 'language', 'theme', 'redirect', 'guidelines', 'announcement', 'metatags' ) )
		)
		or
		( 
			$module->key == 'contact' and $controller == 'contact'
		);
	}
	
	/**
	 * Can view page even when site is offline
	 *
	 * @param	\IPS\Application\Module	$module			The module
	 * @param	string					$controller		The controller
	 * @param	string|NULL				$do				To "do" parameter
	 * @return	bool
	 */
	public function allowOfflineAccess( \IPS\Application\Module $module, $controller, $do )
	{
		return (
			$module->key == 'system'
			and
			(
				\in_array( $controller, array(
					'login', // Because you can login when offline
					'embed', // Because the offline message can contain embedded media
					'lostpass',
					'register',
					'announcement' // Announcements can be useful when the site is offline
				) )
				or
				(
					$controller === 'ajax' and 
						( $do === 'states' OR  // Makes sure address input still works within the ACP otherwise the form to turn site back online is broken
						$do === 'passwordStrength' ) // Makes sure the password strength meter still works because it is used in the AdminCP and registration
				)
			)
		);
	}
	
	/**
	 * Default front navigation
	 *
	 * @code
	 	
	 	// Each item...
	 	array(
			'key'		=> 'Example',		// The extension key
			'app'		=> 'core',			// [Optional] The extension application. If ommitted, uses this application	
			'config'	=> array(...),		// [Optional] The configuration for the menu item
			'title'		=> 'SomeLangKey',	// [Optional] If provided, the value of this language key will be copied to menu_item_X
			'children'	=> array(...),		// [Optional] Array of child menu items for this item. Each has the same format.
		)
	 	
	 	return array(
		 	'rootTabs' 		=> array(), // These go in the top row
		 	'browseTabs'	=> array(),	// These go under the Browse tab on a new install or when restoring the default configuraiton; or in the top row if installing the app later (when the Browse tab may not exist)
		 	'browseTabsEnd'	=> array(),	// These go under the Browse tab after all other items on a new install or when restoring the default configuraiton; or in the top row if installing the app later (when the Browse tab may not exist)
		 	'activityTabs'	=> array(),	// These go under the Activity tab on a new install or when restoring the default configuraiton; or in the top row if installing the app later (when the Activity tab may not exist)
		)
	 * @endcode
	 * @return array
	 */
	public function defaultFrontNavigation()
	{
		$activityTabs = array(
			array( 'key' => 'AllActivity' ),
			array( 'key' => 'YourActivityStreams' ),
		);
		
		foreach ( array( 1, 2 ) as $k )
		{
			try
			{
				\IPS\core\Stream::load( $k );
				$activityTabs[] = array(
					'key'		=> 'YourActivityStreamsItem',
					'config'	=> array( 'menu_stream_id' => $k )
				);
			}
			catch ( \Exception $e ) { }
		}

		$activityTabs[] = array( 'key' => 'Search' );
		$activityTabs[] = array( 'key' => 'Promoted' );
		
		return array(
			'rootTabs'		=> array(),
			'browseTabs'	=> array(
				array( 'key' => 'Clubs' )
			),
			'browseTabsEnd'	=> array(
				array( 'key' => 'Guidelines' ),
				array( 'key' => 'StaffDirectory' ),
				array( 'key' => 'OnlineUsers' ),
				array( 'key' => 'Leaderboard' )
			),
			'activityTabs'	=> $activityTabs
		);
	}

	/**
	 * Perform some legacy URL parameter conversions
	 *
	 * @return	void
	 */
	public function convertLegacyParameters()
	{
		/* Convert &section= to &controller= */
		if ( isset( \IPS\Request::i()->section ) AND !isset( \IPS\Request::i()->controller ) )
		{
			\IPS\Request::i()->controller = \IPS\Request::i()->section;
		}

		/* Convert &showuser= */
		if ( isset( \IPS\Request::i()->showuser ) and \is_numeric( \IPS\Request::i()->showuser ) )
		{
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=core&module=members&controller=profile&id=' . \IPS\Request::i()->showuser ) );
		}
		
		/* Redirect ?app=core&module=attach&section=attach&attach_rel_module=post&attach_id= */
		if ( isset( \IPS\Request::i()->app ) AND \IPS\Request::i()->app == 'core' AND isset( \IPS\Request::i()->controller ) AND \IPS\Request::i()->controller == 'attach' AND isset( \IPS\Request::i()->attach_id ) AND \is_numeric( \IPS\Request::i()->attach_id ) )
		{
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "applications/core/interface/file/attachment.php?id=" . \IPS\Request::i()->attach_id, 'none' ) );
		}

		/* redirect vnc to new streams */
		if( isset( \IPS\Request::i()->app ) AND \IPS\Request::i()->app == 'core' AND  isset( \IPS\Request::i()->controller ) AND \IPS\Request::i()->controller == 'vnc' )
		{
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=core&module=discover&controller=streams' ) );
		}

		/* redirect 4.0 activity page to streams */
		if( isset( \IPS\Request::i()->app ) AND \IPS\Request::i()->app == 'core' AND isset( \IPS\Request::i()->module ) AND (\IPS\Request::i()->module == 'activity' ) AND isset( \IPS\Request::i()->controller ) AND \IPS\Request::i()->controller == 'activity' )
		{
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=core&module=discover&controller=streams' ) );
		}

		/* redirect old message link */
		if( isset( \IPS\Request::i()->app ) AND \IPS\Request::i()->app == 'members' AND isset( \IPS\Request::i()->module ) AND ( \IPS\Request::i()->module == 'messaging' ) AND \IPS\Request::i()->controller == 'view' AND isset( \IPS\Request::i()->topicID ) )
		{
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=core&module=messaging&controller=messenger&id=' . \IPS\Request::i()->topicID, 'front', 'messenger_convo' ) );
		}

		/* redirect old messenger link */
		if( isset( \IPS\Request::i()->app ) AND \IPS\Request::i()->app == 'members' AND isset( \IPS\Request::i()->module ) AND ( \IPS\Request::i()->module == 'messaging' ) )
		{
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=core&module=messaging&controller=messenger', 'front', 'messaging' ) );
		}

		/* redirect old messenger link */
		if( isset( \IPS\Request::i()->module ) AND \IPS\Request::i()->module == 'global' AND isset( \IPS\Request::i()->controller ) AND (\IPS\Request::i()->controller == 'register' ) )
		{
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=core&module=system&controller=register', 'front', 'register' ) );
		}

		/* redirect old reports */
		if( isset( \IPS\Request::i()->app ) AND \IPS\Request::i()->app == 'core' AND
			isset( \IPS\Request::i()->module ) AND (\IPS\Request::i()->module == 'reports' ) AND
			isset( \IPS\Request::i()->do ) AND ( \IPS\Request::i()->do == 'show_report' )  )
		{
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=core&module=modcp&controller=modcp&tab=reports&action=view&id=' . \IPS\Request::i()->rid , 'front', 'modcp_report' ) );
		}
	}
	
	/**
	 * Get any third parties this app uses for the privacy policy
	 *
	 * @return array( title => language bit, description => language bit, privacyUrl => privacy policy URL )
	 */
	public function privacyPolicyThirdParties()
	{
		/* Apps can overload this */
		$subprocessors = array();
			
		/* Analytics */
		if ( \IPS\Settings::i()->ipbseo_ga_provider == 'ga' )
		{
			$subprocessors[] = array(
				'title' => \IPS\Member::loggedIn()->language()->addToStack('enhancements__core_GoogleAnalytics'),
				'description' => \IPS\Member::loggedIn()->language()->addToStack('pp_desc_GoogleAnalytics'),
				'privacyUrl' => 'https://www.google.com/intl/en/policies/privacy/'
			);
		}
		else if ( \IPS\Settings::i()->ipbseo_ga_provider == 'piwik' )
		{
			$subprocessors[] = array(
				'title' => \IPS\Member::loggedIn()->language()->addToStack('analytics_provider_piwik'),
				'description' => \IPS\Member::loggedIn()->language()->addToStack('pp_desc_Piwik'),
				'privacyUrl' => 'https://matomo.org/privacy-policy/'
			);
		}
		
		/* Facebook Pixel */
		$fb = new \IPS\core\extensions\core\CommunityEnhancements\FacebookPixel();
		if ( $fb->enabled )
		{
			$subprocessors[] = array(
				'title' => \IPS\Member::loggedIn()->language()->addToStack('enhancements__core_FacebookPixel'),
				'description' => \IPS\Member::loggedIn()->language()->addToStack('pp_desc_FacebookPixel'),
				'privacyUrl' => 'https://www.facebook.com/about/privacy/'
			);
		}
		
		/* IPS Spam defense */
		if ( \IPS\Settings::i()->spam_service_enabled )
		{
			$subprocessors[] = array(
				'title' => \IPS\Member::loggedIn()->language()->addToStack('enhancements__core_SpamMonitoring'),
				'description' => \IPS\Member::loggedIn()->language()->addToStack('pp_desc_SpamMonitoring'),
				'privacyUrl' => 'https://invisioncommunity.com/legal/privacy'
			);
		}
		
		/* Send Grid */
		$sendgrid = new \IPS\core\extensions\core\CommunityEnhancements\Sendgrid();
		if ( $sendgrid->enabled )
		{
			$subprocessors[] = array(
				'title' => \IPS\Member::loggedIn()->language()->addToStack('enhancements__core_Sendgrid'),
				'description' => \IPS\Member::loggedIn()->language()->addToStack('pp_desc_SendGrid'),
				'privacyUrl' => 'https://sendgrid.com/policies/privacy/'
			);
		}
		
		/* Captcha */
		if ( \IPS\Settings::i()->bot_antispam_type !== 'none' )
		{
			switch ( \IPS\Settings::i()->bot_antispam_type )
			{
				case 'recaptcha2':
					$subprocessors[] = array(
						'title' => \IPS\Member::loggedIn()->language()->addToStack('captcha_type_recaptcha2'),
						'description' => \IPS\Member::loggedIn()->language()->addToStack('pp_desc_captcha'),
						'privacyUrl' => 'https://www.google.com/policies/privacy/'
					);
					break;
				case 'invisible':
					$subprocessors[] = array(
						'title' => \IPS\Member::loggedIn()->language()->addToStack('captcha_type_invisible'),
						'description' => \IPS\Member::loggedIn()->language()->addToStack('pp_desc_captcha'),
						'privacyUrl' => 'https://www.google.com/policies/privacy/'
					);
					break;
				case 'keycaptcha':
					$subprocessors[] = array(
						'title' => \IPS\Member::loggedIn()->language()->addToStack('captcha_type_keycaptcha'),
						'description' => \IPS\Member::loggedIn()->language()->addToStack('pp_desc_captcha'),
						'privacyUrl' => 'https://www.keycaptcha.com'
					);
					break;
			}
		}
		
		return $subprocessors;
				
	}
	
	/**
	 * Get any settings that are uploads
	 *
	 * @return	array
	 */
	public function uploadSettings()
	{
		/* Apps can overload this */
		return array( 'email_logo' );
	}

	/**
	 * Imports an IN_DEV email template into the database
	 *
	 * @param	string		$path			Path to file
	 * @param	object		$file			DirectoryIterator File Object
	 * @param	string|null	$namePrefix		Name prefix
	 * @return  array
	 */
	protected function _buildEmailTemplateFromInDev( $path, $file, $namePrefix='' )
	{
		$return = parent::_buildEmailTemplateFromInDev( $path, $file, $namePrefix );

		/* Make sure that the email wrapper is pinned to the top of the list */
		if( $file->getFilename() == 'emailWrapper.phtml' )
		{
			$return['template_pinned'] = 1;
		}

		return $return;
	}
}
