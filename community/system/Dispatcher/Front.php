<?php
/**
 * @brief		Front-end Dispatcher
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		18 Feb 2013
 */

namespace IPS\Dispatcher;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Front-end Dispatcher
 */
class _Front extends \IPS\Dispatcher\Standard
{
	/**
	 * Controller Location
	 */
	public $controllerLocation = 'front';
	
	/**
	 * Init
	 *
	 * @return	void
	 */
	public function init()
	{
		/* Get cached page if available */
		$this->checkCached();

		/* Set up in progress? */
		if ( isset( \IPS\Settings::i()->setup_in_progress ) AND \IPS\Settings::i()->setup_in_progress )
		{
			$protocol = '1.0';
			if( isset( $_SERVER['SERVER_PROTOCOL'] ) and \strstr( $_SERVER['SERVER_PROTOCOL'], '/1.0' ) !== false )
			{
				$protocol = '1.1';
			}
			
			if ( \IPS\CIC and ! \IPS\Session\Front::loggedIn() and ! \IPS\Session\Front::i()->userAgent->spider )
			{
				/* The software is unavailable, but the site is up so we do not want to affect our cloud downtime statistics and trigger monitoring alarms
				   if we are not a search engine */
				header( "HTTP/{$protocol} 200 OK" );
			}
			else
			{
				header( "HTTP/{$protocol} 503 Service Unavailable" );
				header( "Retry-After: 300"); #5 minutes
			}
					
			require \IPS\ROOT_PATH . '/' . \IPS\UPGRADING_PAGE;
			exit;
		}

		/* Sync stuff when in developer mode */
		if ( \IPS\IN_DEV )
		{
			 \IPS\Developer::sync();
		}
		
		/* Base CSS */
		static::baseCss();

		/* Base JS */
		static::baseJs();

		/* Check friendly URL and whether it is correct */
		try
		{
			$this->checkUrl();
		}
		catch( \OutOfRangeException $e )
		{
			/* Display a 404 */
			$this->application = \IPS\Application::load('core');
			$this->setDefaultModule();
			if ( \IPS\Member::loggedIn()->isBanned() )
			{
				\IPS\Output::i()->sidebar = FALSE;
				\IPS\Output::i()->bodyClasses[] = 'ipsLayout_minimal';
			}
			\IPS\Output::i()->jsFiles = array_merge( \IPS\Output::i()->jsFiles, \IPS\Output::i()->js( 'app.js' ) );
			\IPS\Output::i()->error( 'requested_route_404', '1S160/2', 404, '' );
		}
		
		/* Perform some legacy URL conversions*/
		static::convertLegacyParameters();

		/* Run global init */
		try
		{
			parent::init();
		}
		catch ( \DomainException $e )
		{
			// If this is a "no permission", and they're validating - show the validating screen instead
			if( $e->getCode() === 6 and \IPS\Member::loggedIn()->member_id and \IPS\Member::loggedIn()->members_bitoptions['validating'] )
			{
				\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=core&module=system&controller=register&do=validating', 'front', 'register' ) );
			}
			// Otherwise show the error
			else
			{
				\IPS\Output::i()->error( $e->getMessage(), '2S100/' . $e->getCode(), $e->getCode() === 4 ? 403 : 404, '' );
			}
		}
		
		/* Enable sidebar by default (controllers can turn it off if needed) */
		\IPS\Output::i()->sidebar['enabled'] = ( \IPS\Request::i()->isAjax() ) ? FALSE : TRUE;
		
		/* Add in RSS Feeds */
		foreach( \IPS\core\Rss::getStore() AS $feed_id => $feed )
		{
			$feed = \IPS\core\Rss::constructFromData( $feed );

			if ( $feed->_enabled AND ( $feed->groups == '*' OR \IPS\Member::loggedIn()->inGroup( $feed->groups ) ) )
			{
				\IPS\Output::i()->rssFeeds[ $feed->_title ] = $feed->url();
			}
		}
		
		/* Are we online? */
		if ( !\IPS\Settings::i()->site_online and !\IPS\Member::loggedIn()->group['g_access_offline'] and $this->controllerLocation == 'front' and !$this->application->allowOfflineAccess( $this->module, $this->controller, \IPS\Request::i()->do ) )
		{
			if ( \IPS\Request::i()->isAjax() )
			{
				\IPS\Output::i()->json( \IPS\Member::loggedIn()->language()->addToStack( 'offline_unavailable', FALSE, array( 'sprintf' => array( \IPS\Settings::i()->board_name ) ) ), 503 );
			}
			
			\IPS\Output::i()->showOffline();
		}
		
		/* Member Ban? */
		$ipBanned = \IPS\Request::i()->ipAddressIsBanned();
		if ( $ipBanned or $banEnd = \IPS\Member::loggedIn()->isBanned() )
		{
			if ( !$ipBanned and !\IPS\Member::loggedIn()->member_id )
			{
				if ( $this->notAllowedBannedPage() )
				{
					$url = \IPS\Http\Url::internal( 'app=core&module=system&controller=login', 'front', 'login' );
					
					if ( \IPS\Request::i()->url() != \IPS\Settings::i()->base_url AND !isset( \IPS\Request::i()->_mfaLogin ) )
					{
						$url = $url->setQueryString( 'ref', base64_encode( \IPS\Request::i()->url() ) );
					}
					else if ( isset( \IPS\Request::i()->_mfaLogin ) )
					{
						$url = $url->setQueryString( '_mfaLogin', 1 );
					}
					
					\IPS\Output::i()->redirect( $url );
				}
			}
			else
			{
				\IPS\Output::i()->sidebar = FALSE;
				\IPS\Output::i()->bodyClasses[] = 'ipsLayout_minimal';
				if( !\in_array( $this->controller, array( 'contact', 'warnings', 'privacy', 'guidelines', 'metatags' ) ) )
				{
					\IPS\Output::i()->showBanned();
				}
			}
		}
		
		/* Do we need more info from the member or do they need to validate? */

		/* This controllers should always be accessible, no matter if the member is awaiting validation or needs to set up the email or name */
		$legalControllers = array( 'privacy', 'contact', 'terms', 'embed', 'metatags' );

		/* Do we need more info from the member or do they need to validate? */
		if( \IPS\Member::loggedIn()->member_id and $this->controller !== 'language' and $this->controller !== 'theme' and $this->controller !== 'ajax' and !\in_array( $this->controller, $legalControllers ) )
		{
			/* Need their name or email... */
			if( ( \IPS\Member::loggedIn()->real_name === '' or !\IPS\Member::loggedIn()->email ) and $this->controller !== 'register' )
			{
				\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=core&module=system&controller=register&do=complete' )->setQueryString( 'ref', base64_encode( \IPS\Request::i()->url() ) ) );
			}
			/* Need them to validate... */
			elseif( \IPS\Member::loggedIn()->members_bitoptions['validating'] and $this->controller !== 'register' and $this->controller !== 'login' and $this->controller != 'redirect' )
			{
				\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=core&module=system&controller=register&do=validating', 'front', 'register' ) );
			}
			/* Need them to reconfirm terms/privacy policy... */
			elseif ( ( \IPS\Member::loggedIn()->members_bitoptions['must_reaccept_privacy'] or \IPS\Member::loggedIn()->members_bitoptions['must_reaccept_terms'] ) and $this->controller !== 'register' and $this->controller !== 'ajax' )
			{
				\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=core&module=system&controller=register&do=reconfirm', 'front', 'register' )->setQueryString( 'ref', base64_encode( \IPS\Request::i()->url() ) ) );
			}
			/* Have required profile actions that need completing */
			else if ( \IPS\Settings::i()->quick_register AND !\IPS\Member::loggedIn()->members_bitoptions['profile_completed'] AND !\in_array( $this->controller, array( 'register', 'login', 'redirect', 'ajax', 'settings' ) ) AND $completion = \IPS\Member::loggedIn()->profileCompletion() AND \count( $completion['required'] ) )
			{
				foreach( $completion['required'] AS $id => $completed )
				{
					if ( $completed === FALSE )
					{
						\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=core&module=system&controller=register&do=finish", 'front', 'register' )->setQueryString( 'ref', base64_encode( \IPS\Request::i()->url() ) ) );
					}
				}
			}
			/* Need to set up MFA... */
			elseif ( !\in_array( $this->controller, array( 'register', 'login', 'redirect', 'ajax', 'settings', 'embed' ) ) )
			{
				$haveAcceptableHandlers = FALSE;
				$haveConfiguredHandler = FALSE;
				foreach ( \IPS\MFA\MFAHandler::handlers() as $key => $handler )
				{
					if ( $handler->isEnabled() and $handler->memberCanUseHandler( \IPS\Member::loggedIn() ) )
					{
						$haveAcceptableHandlers = TRUE;
						if ( $handler->memberHasConfiguredHandler( \IPS\Member::loggedIn() ) )
						{
							$haveConfiguredHandler = TRUE;
							break;
						}
					}
				}
				
				if ( !$haveConfiguredHandler and $haveAcceptableHandlers )
				{
					if ( \IPS\Settings::i()->mfa_required_groups == '*' or \IPS\Member::loggedIn()->inGroup( explode( ',', \IPS\Settings::i()->mfa_required_groups ) ) )
					{
						if ( \IPS\Settings::i()->mfa_required_prompt === 'immediate' )
						{
							\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=core&module=system&controller=settings&do=initialMfa', 'front', 'settings' )->setQueryString( 'ref', base64_encode( \IPS\Request::i()->url() ) ) );
						}
					}
					elseif ( \IPS\Settings::i()->mfa_optional_prompt === 'immediate' and !\IPS\Member::loggedIn()->members_bitoptions['security_questions_opt_out'] )
					{
						\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=core&module=system&controller=settings&do=initialMfa', 'front', 'settings' )->setQueryString( 'ref', base64_encode( \IPS\Request::i()->url() ) ) );
					}
				}
			}			
		}
		
		/* Permission Check */
		try
		{
			if ( !\IPS\Member::loggedIn()->canAccessModule( $this->module ) )
			{
				if ( !\IPS\Member::loggedIn()->member_id and isset( \IPS\Request::i()->_mfaLogin ) )
				{
					\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=core&module=system&controller=login", 'front', 'login' )->setQueryString( '_mfaLogin', 1 ) );
				}
				\IPS\Output::i()->error( ( \IPS\Member::loggedIn()->member_id ? 'no_module_permission' : 'no_module_permission_guest' ), '2S100/2', 403, 'no_module_permission_admin' );
			}
		}
		catch( \InvalidArgumentException $e ) # invalid module
		{
			\IPS\Output::i()->error( 'requested_route_404', '2S160/5', 404, '' );
		}
		
		/* Stuff for output */
		if ( !\IPS\Request::i()->isAjax() )
		{
			/* Base Navigation. We only add the module not the app as most apps don't have a global base (for example, in Nexus, you want "Store" or "Client Area" to be the base). Apps can override themselves in their controllers. */
			foreach( \IPS\Application::applications() as $directory => $application )
			{
				if( $application->default )
				{
					$defaultApplication	= $directory;
					break;
				}
			}

			if( !isset( $defaultApplication ) )
			{
				$defaultApplication = 'core';
			}
			
			if ( $this->module->key != 'system' AND $this->application->directory != $defaultApplication )
			{
				\IPS\Output::i()->breadcrumb['module'] = array( \IPS\Http\Url::internal( 'app=' . $this->application->directory . '&module=' . $this->module->key . '&controller=' . $this->module->default_controller, 'front', array_key_exists( $this->module->key, \IPS\Http\Url::furlDefinition() ) ?  $this->module->key : NULL ), $this->module->_title );
			}
			
			/* Figure out what the global search is */
			foreach ( $this->application->extensions( 'core', 'ContentRouter' ) as $object )
			{
				if ( \count( $object->classes ) === 1 )
				{
					$classes = $object->classes;
					foreach ( $classes as $class )
					{
						if ( is_subclass_of( $class, 'IPS\Content\Searchable' ) and $class::includeInSiteSearch() and $this->module->key == $class::$module )
						{
							$type = mb_strtolower( str_replace( '\\', '_', mb_substr( array_pop( $classes ), 4 ) ) );
							\IPS\Output::i()->defaultSearchOption = array( $type, "{$type}_pl" );
							break;
						}
					}
				}
			}
		}
	}

	/**
	  * Check whether the URL we visited is correct and route appropriately
	  *
	  * @return void
	  */
	protected function checkUrl()
	{
		/* Handle friendly URLs */
		if ( \IPS\Settings::i()->use_friendly_urls )
		{
			$url = \IPS\Request::i()->url();
			
			/* Redirect to the "correct" friendly URL if there is one */
			if ( !\IPS\Request::i()->isAjax() and mb_strtolower( $_SERVER['REQUEST_METHOD'] ) == 'get' and !\IPS\ENFORCE_ACCESS )
			{
				$correctUrl = NULL;
				
				/* If it's already a friendly URL, we need to check the SEO title is valid. If it isn't, we redirect iof "Force Friendly URLs" is enabled */
				if ( $url instanceof \IPS\Http\Url\Friendly or ( $url instanceof \IPS\Http\Url\Internal and \IPS\Settings::i()->seo_r_on ) )
				{
					$correctUrl = $url->correctFriendlyUrl();
				}
				
				/* If they are accessing "index.php/whatever", we want "index.php?/whatever */
				if ( !( $correctUrl instanceof \IPS\Http\Url ) and $url instanceof \IPS\Http\Url\Internal and mb_strpos( $url->data[ \IPS\Http\Url::COMPONENT_PATH ], '/index.php/' ) !== FALSE )
				{
					$pathFromBaseUrl = ltrim( mb_substr( $url->data[ \IPS\Http\Url::COMPONENT_PATH ], mb_strlen( \IPS\Http\Url::internal('')->data[ \IPS\Http\Url::COMPONENT_PATH ] ) ), '/' );
					if ( mb_substr( $pathFromBaseUrl, 0, 10 ) === 'index.php/' )
					{
						$correctUrl = \IPS\Http\Url\Friendly::friendlyUrlFromComponent( 0, trim( mb_substr( $pathFromBaseUrl, 10 ), '/' ), $url->queryString );
					}
				}

				/* Redirect to the correct URL if we got one */
				if ( $correctUrl instanceof \IPS\Http\Url )
				{
					if( $correctUrl->seoPagination and \in_array( 'page', array_keys( $url->hiddenQueryString ) ) )
					{
						$correctUrl = $correctUrl->setPage( 'page', $url->hiddenQueryString['page'] );
					}
					\IPS\Output::i()->redirect( $correctUrl, NULL, 301 );
				}

				/* Check pagination */
				if ( $url instanceof \IPS\Http\Url\Friendly and $url->seoPagination and \in_array( 'page', array_keys( $url->queryString ) ) )
				{
					\IPS\Output::i()->redirect( $url->setPage( 'page', $url->queryString['page'] )->stripQueryString('page'), NULL, 301 );
				}
			}
			
			/* If the accessed URL is friendly, set the "real" query string properties */
			if ( $url instanceof \IPS\Http\Url\Friendly )
			{
				foreach ( ( $url->queryString + $url->hiddenQueryString ) as $k => $v )
				{
					if( $k == 'module' )
					{
						$this->_module	= NULL;
					}
					else if( $k == 'controller' )
					{
						$this->_controller	= NULL;
					}
					
					/* If this is a POST request, and this key has already been populated, do not overwrite it as this allows form input to be ignored and the query string data used */
					if ( \IPS\Request::i()->requestMethod() == 'POST' and isset( \IPS\Request::i()->$k ) )
					{
						continue;
					}
					
					\IPS\Request::i()->$k = $v;
				}
			}
			/* Otherwise if it's not a recognised URL, show a 404 */
			elseif ( !( $url instanceof \IPS\Http\Url\Internal ) or $url->base !== 'front' )
			{
				/* Call the parent first in case we need to redirect to https, and so the correct locale, etc. is set */
				try
				{
					parent::init();
				}
				catch ( \Exception $e ) { }
				
				throw new \OutOfRangeException;
			}
		}
	}

	/**
	 * Define that the page should load even if the user is banned and not logged in
	 *
	 * @return	bool
	 */
	protected function notAllowedBannedPage()
	{
		return !\IPS\Member::loggedIn()->group['g_view_board'] and !$this->application->allowGuestAccess( $this->module, $this->controller, \IPS\Request::i()->do );
	}

	/**
	 * Check cache for this page
	 *
	 * @return	void
	 */
	protected function checkCached()
	{
		/* If this is a guest and there's a full cached page, we can serve that */
		if( mb_strtolower( $_SERVER['REQUEST_METHOD'] ) == 'get' and !isset( \IPS\Request::i()->csrfKey ) and !isset( \IPS\Request::i()->_mfaLogin ) and !\IPS\Session\Front::loggedIn() and !isset( \IPS\Request::i()->cookie['noCache'] ) and !\IPS\Request::i()->ipAddressIsBanned() and \IPS\CACHE_PAGE_TIMEOUT )
		{
			/* Which language? */
			if ( isset( \IPS\Request::i()->cookie['language'] ) )
			{
				$language = \IPS\Request::i()->cookie['language'];
			}
			else
			{
				/* HTTP_ACCEPT_LANGUAGE is only available via http, thus trying to enable FURLs fails as we send out a socket request to test .htaccess */
				$language = ( isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) ? \IPS\Lang::autoDetectLanguage( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) : NULL;
				
				if ( $language === NULL )
				{
					$language = \IPS\Lang::defaultLanguage();
				}
			}
			
			/* Which theme? */
			$theme = isset( \IPS\Request::i()->cookie['theme'] ) ? \IPS\Request::i()->cookie['theme'] : '';
			
			/* Make sure a session is initiated */
			\IPS\Session\Front::i();
			
			/* Get cache */
			try
			{
				$cache = \IPS\Output\Cache::i()->get( 'page_' . md5( (int) \IPS\Request::i()->isSecure() . ( !empty( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'] ) . '/' . $_SERVER['REQUEST_URI'] ) . "_{$language}_{$theme}", TRUE );
				$sessionId = isset( \IPS\Request::i()->cookie['IPSSessionFront'] ) ? \IPS\Request::i()->cookie['IPSSessionFront'] : \IPS\Session::i()->id;
				
				/* RSS feeds can be shown cached, all other pages contain a cache buster once logged in */
				$cache['meta']['httpHeaders'] += \IPS\Output::getCacheHeaders( $cache['meta']['lastUpdated'], \IPS\CACHE_PAGE_TIMEOUT );

				if( isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) AND $_SERVER['HTTP_IF_MODIFIED_SINCE'] == $cache['meta']['httpHeaders']['Last-Modified'] )
				{
					\IPS\Output::i()->sendOutput( NULL, 304, $cache['meta']['contentType'], $cache['meta']['httpHeaders'], FALSE, TRUE, FALSE, FALSE );
				}
				
				\IPS\Output::i()->sendOutput( str_replace( '{{csrfKey}}', md5( \IPS\SUITE_UNIQUE_KEY . '&& 0&' . $sessionId ), $cache['output'] ), $cache['meta']['code'], $cache['meta']['contentType'], $cache['meta']['httpHeaders'], FALSE, TRUE );

			}
			catch ( \OutOfRangeException $e ) {}
		}
	}

	/**
	 * Perform some legacy URL parameter conversions
	 *
	 * @return	void
	 */
	public static function convertLegacyParameters()
	{
		foreach( \IPS\Application::applications() as $directory => $application )
		{
			if ( $application->_enabled )
			{
				if( method_exists( $application, 'convertLegacyParameters' ) )
				{
					$application->convertLegacyParameters();
				}
			}
		}
	}

	/**
	 * Finish
	 *
	 * @return	void
	 */
	public function finish()
	{
		/* Sidebar Widgets */
		if( !\IPS\Request::i()->isAjax() )
		{
			$widgets = array();
			
			if ( ! isset( \IPS\Output::i()->sidebar['widgets'] ) OR ! \is_array( \IPS\Output::i()->sidebar['widgets'] ) )
			{
				\IPS\Output::i()->sidebar['widgets'] = array();
			}
			
			try
			{
				$widgetConfig = \IPS\Db::i()->select( '*', 'core_widget_areas', array( 'app=? AND module=? AND controller=?', $this->application->directory, $this->module->key, $this->controller ) );
				foreach( $widgetConfig as $area )
				{
					$widgets[ $area['area'] ] = json_decode( $area['widgets'], TRUE );
				}
			}
			catch ( \UnderflowException $e ) {}
			
			if ( \IPS\Output::i()->allowDefaultWidgets )
			{
				foreach( \IPS\Widget::appDefaults( $this->application ) as $widget )
				{
					/* If another app has already defined this area, don't overwrite it */
					if ( isset( $widgets[ $widget['default_area'] ] ) )
					{
						continue;
					}
	
					$widget['unique']	= $widget['key'];
					
					$widgets[ $widget['default_area'] ][] = $widget;
				}
			}
					
			if( \count( $widgets ) )
			{
				if ( ( \IPS\Data\Cache::i() instanceof \IPS\Data\Cache\None ) and ! \IPS\Theme::isUsingTemplateDiskCache() )
				{
					$templateLoad = array();
					foreach ( $widgets as $areaKey => $area )
					{
						foreach ( $area as $widget )
						{
							if ( isset( $widget['app'] ) and $widget['app'] )
							{
								$templateLoad[] = array( $widget['app'], 'front', 'widgets' );
								$templateLoad[] = 'template_' . \IPS\Theme::i()->id . '_' . \IPS\Theme::makeBuiltTemplateLookupHash( $widget['app'], 'front', 'widgets' ) . '_widgets';
							}
						}
					}
	
					if( \count( $templateLoad ) )
					{
						\IPS\Data\Store::i()->loadIntoMemory( $templateLoad );
					}
				}
				
				$widgetObjects = array();
				$storeLoad = array();
				foreach ( $widgets as $areaKey => $area )
				{
					foreach ( $area as $widget )
					{
						try
						{
							$appOrPlugin = isset( $widget['plugin'] ) ? \IPS\Plugin::load( $widget['plugin'] ) : \IPS\Application::load( $widget['app'] );

							if( !$appOrPlugin->enabled )
							{
								continue;
							}
							
							$_widget = \IPS\Widget::load( $appOrPlugin, $widget['key'], ( ! empty($widget['unique'] ) ? $widget['unique'] : mt_rand() ), ( isset( $widget['configuration'] ) ) ? $widget['configuration'] : array(), ( isset( $widget['restrict'] ) ? $widget['restrict'] : null ), ( $areaKey == 'sidebar' ) ? 'vertical' : 'horizontal' );
							if ( ( \IPS\Data\Cache::i() instanceof \IPS\Data\Cache\None ) and isset( $_widget->cacheKey ) )
							{
								$storeLoad[] = $_widget->cacheKey;
							}
							$widgetObjects[ $areaKey ][] = $_widget;
						}
						catch ( \Exception $e )
						{
							\IPS\Log::log( $e, 'dispatcher' );
						}
					}
				}

				if( ( \IPS\Data\Cache::i() instanceof \IPS\Data\Cache\None ) and \count( $storeLoad ) )
				{
					\IPS\Data\Store::i()->loadIntoMemory( $storeLoad );
				}
				
				foreach ( $widgetObjects as $areaKey => $_widgets )
				{
					foreach ( $_widgets as $_widget )
					{
						\IPS\Output::i()->sidebar['widgets'][ $areaKey ][] = $_widget;
					}
				}
			}
		}
		
		/* Meta tags */
		\IPS\Output::i()->buildMetaTags();
		
		/* Check MFA */
		$this->checkMfa();
		
		/* Finish */
		parent::finish();
	}

	/**
	 * Check MFA to see if we need to supply a code
	 *
	 * @param	boolean	$return	Return any HTML (true) or add to Output (false)
	 *
	 * @return void
	 */
	public function checkMfa( $return=FALSE )
	{
		/* MFA Login? */
		if ( isset( \IPS\Request::i()->_mfaLogin ) and isset( $_SESSION['processing2FA'] ) and $member = \IPS\Member::load( $_SESSION['processing2FA']['memberId'] ) and $member->member_id )
		{
			$device = \IPS\Member\Device::loadOrCreate( $member, FALSE );
			if ( $output = \IPS\MFA\MFAHandler::accessToArea( 'core', $device->known ? 'AuthenticateFrontKnown' : 'AuthenticateFront', \IPS\Request::i()->url(), $member ) )
			{
				if ( $return )
				{
					return $output;
				}
				
				\IPS\Output::i()->output .= $output;
			}
			else
			{
				\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=core&module=system&controller=login&do=mfa', 'front', 'login' ) );
			}
		}
	}

	/**
	 * Output the basic javascript files every page needs
	 *
	 * @return void
	 */
	protected static function baseJs()
	{
		parent::baseJs();

		/* Stuff for output */
		if ( !\IPS\Request::i()->isAjax() )
		{
			\IPS\Output::i()->globalControllers[] = 'core.front.core.app';
			\IPS\Output::i()->jsFiles = array_merge( \IPS\Output::i()->jsFiles, \IPS\Output::i()->js( 'front.js' ) );

			if ( \IPS\Member::loggedIn()->members_bitoptions['bw_using_skin_gen'] AND ( isset( \IPS\Request::i()->cookie['vseThemeId'] ) AND \IPS\Request::i()->cookie['vseThemeId'] ) and \IPS\Member::loggedIn()->isAdmin() and \IPS\Member::loggedIn()->hasAcpRestriction( 'core', 'customization', 'theme_easy_editor' ) )
			{
				\IPS\Output::i()->jsFiles = array_merge( \IPS\Output::i()->jsFiles, \IPS\Output::i()->js( 'front_vse.js', 'core', 'front' ) );
				\IPS\Output::i()->jsFiles = array_merge( \IPS\Output::i()->jsFiles, \IPS\Output::i()->js( 'vse/vsedata.js', 'core', 'interface' ) );
				\IPS\Output::i()->globalControllers[] = 'core.front.vse.window';
			}

			/* Can we edit widget layouts? */
			if( \IPS\Member::loggedIn()->modPermission('can_manage_sidebar') )
			{
				\IPS\Output::i()->globalControllers[] = 'core.front.widgets.manager';
				\IPS\Output::i()->jsFiles = array_merge( \IPS\Output::i()->jsFiles, \IPS\Output::i()->js( 'front_widgets.js', 'core', 'front' ) );
			}

			/* Are we editing meta tags? */
			if( isset( $_SESSION['live_meta_tags'] ) and $_SESSION['live_meta_tags'] and \IPS\Member::loggedIn()->isAdmin() )
			{
				\IPS\Output::i()->jsFiles = array_merge( \IPS\Output::i()->jsFiles, \IPS\Output::i()->js( 'front_system.js', 'core', 'front' ) );
			}
		}
	}

	/**
	 * Base CSS
	 *
	 * @return	void
	 */
	public static function baseCss()
	{
		parent::baseCss();

		/* Stuff for output */
		if ( !\IPS\Request::i()->isAjax() )
		{
			\IPS\Output::i()->cssFiles = array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( 'core.css', 'core', 'front' ) );
			if ( \IPS\Output::i()->responsive and \IPS\Theme::i()->settings['responsive'] )
			{
				\IPS\Output::i()->cssFiles = array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( 'core_responsive.css', 'core', 'front' ) );
			}
			
			if ( \IPS\Member::loggedIn()->members_bitoptions['bw_using_skin_gen'] AND ( isset( \IPS\Request::i()->cookie['vseThemeId'] ) AND \IPS\Request::i()->cookie['vseThemeId'] ) and \IPS\Member::loggedIn()->isAdmin() and \IPS\Member::loggedIn()->hasAcpRestriction( 'core', 'customization', 'theme_easy_editor' ) )
			{
				\IPS\Output::i()->cssFiles = array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( 'styles/vse.css', 'core', 'front' ) );
			}

			/* Are we editing meta tags? */
			if( isset( $_SESSION['live_meta_tags'] ) and $_SESSION['live_meta_tags'] and \IPS\Member::loggedIn()->isAdmin() )
			{
				\IPS\Output::i()->cssFiles = array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( 'styles/meta_tags.css', 'core', 'front' ) );
			}
			
			/* Query log? */
			if ( \IPS\QUERY_LOG )
			{
				\IPS\Output::i()->cssFiles = array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( 'styles/query_log.css', 'core', 'front' ) );
			}
			if ( \IPS\CACHING_LOG or \IPS\REDIS_LOG )
			{
				\IPS\Output::i()->cssFiles = array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( 'styles/caching_log.css', 'core', 'front' ) );
			}
		}
	}
}
