<?php
/**
 * @brief		promote
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		14 Feb 2017
 */

namespace IPS\core\modules\admin\promotion;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * promote
 */
class _promote extends \IPS\Dispatcher\Controller
{
	/**
	 * @brief	Active tab
	 */
	protected $activeTab	= '';
	
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'promote_manage' );
		
		/* Get tab content */
		$this->activeTab = \IPS\Request::i()->tab ?: 'facebook';
		
		parent::execute();
	}

	/**
	 * Promote Settings
	 *
	 * @return	void
	 */
	protected function manage()
	{
		/* Work out output */
		$methodFunction = '_manage' . mb_ucfirst( $this->activeTab );
		$activeTabContents = $this->$methodFunction();
		
		/* If this is an AJAX request, just return it */
		if( \IPS\Request::i()->isAjax() )
		{
			\IPS\Output::i()->output = $activeTabContents;
			return;
		}

		/* Build tab list */
		$tabs = array();
		
		$tabs['facebook'] = 'promote_tab_facebook';
		$tabs['twitter'] = 'promote_tab_twitter';
		$tabs['links'] = 'promote_tab_links';
		$tabs['schedule'] = 'promote_tab_schedule';
		$tabs['permissions'] = 'promote_tab_permissions';
		$tabs['internal'] = 'promote_tab_internal';
		
		/* Buttons for logs */
		if ( \IPS\Member::loggedIn()->hasAcpRestriction( 'core', 'promotion', 'promote_logs' ) )
		{
			\IPS\Output::i()->sidebar['actions']['actionLogs'] = array(
				'title'		=> 'promote_logs',
				'icon'		=> 'search',
				'link'		=> \IPS\Http\Url::internal( 'app=core&module=promotion&controller=promote&do=logs' ),
			);
		}
		
		/* Display */
		if ( $activeTabContents )
		{
			$output = \IPS\Theme::i()->getTemplate( 'forms', 'core' )->blurb( \IPS\Member::loggedIn()->language()->addToStack( 'promote_acp_blurb' ) );
			
			/* Put up a little warning about permissions */
			$enabledServices = \IPS\Db::i()->select( 'count(*)', 'core_social_promote_sharers', array( 'sharer_enabled=1' ) );
			
			if ( $enabledServices and ! \IPS\core\Promote::canPromote() )
			{
				/* We do not have permission to promote, so lets show a notice about that */
				$output .= \IPS\Theme::i()->getTemplate( 'global', 'core', 'global' )->message( 'promote_no_permission_acp', 'info' );
			}

			\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('menu__core_promotion_promote');
			\IPS\Output::i()->output = $output . \IPS\Theme::i()->getTemplate( 'global' )->tabs( $tabs, $this->activeTab, $activeTabContents, \IPS\Http\Url::internal( "app=core&module=promotion&controller=promote" ) );
		}
	}
	
	/**
	 * Manage Links
	 *
	 * @return	string
	 */
	protected function _manageLinks()
	{
		$form = new \IPS\Helpers\Form;
		$form->add( new \IPS\Helpers\Form\YesNo( 'bitly_enabled', \IPS\Settings::i()->bitly_enabled, FALSE, array( 'togglesOn' => array( 'bitly_token' ) ), NULL, NULL, NULL, 'bitly_enabled' ) );
		$form->add( new \IPS\Helpers\Form\Text( 'bitly_token', \IPS\Settings::i()->bitly_token, FALSE, array(), function( $val )
		{
			try
			{
				$response = \IPS\Http\Url::external( "https://api-ssl.bitly.com/v3/shorten" )->setQueryString( array( 'access_token' => $val, 'longUrl' => 'https://www.invisioncommunity.com' ) )->request()->get()->decodeJson();
				
				if ( $response['status_code'] !== 200 )
				{
					throw new \DomainException("bitly_auth_failed");
				}
			}
			catch ( \IPS\Http\Request\Exception $e )
			{
				throw new \DomainException("bitly_auth_failed");
			}
		}, NULL, \IPS\Member::loggedIn()->language()->addToStack('bitly_generate_token'), 'bitly_token' ) );
		
		/* Are we saving? */
		if ( $values = $form->values() )
		{
			$form->saveAsSettings();
			
			\IPS\Session::i()->log( 'acplogs__promote_save', array( "acplogs__promote_changed_links" => true ) );
			
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=core&module=promotion&controller=promote&tab=" . \IPS\Request::i()->tab ), 'saved' );
		}
		
		return $form;
	}
	
	/**
	 * Manage Links
	 *
	 * @return	string
	 */
	protected function _manageSchedule()
	{
		$form = new \IPS\Helpers\Form;
		
		$form->add( new \IPS\Helpers\Form\Stack( 'promote_scheduled', explode( ',', \IPS\Settings::i()->promote_scheduled ), FALSE, array( 'stackFieldType' => 'Text', 'placeholder' => 'HH:MM' ), NULL, NULL, NULL, 'promote_scheduled' ) );
		$form->add( new \IPS\Helpers\Form\Timezone( 'promote_tz', \IPS\Settings::i()->promote_tz, FALSE, array(), NULL, NULL, NULL, 'promote_tz' ) );
		
		/* Are we saving? */
		if ( $values = $form->values() )
		{
			$form->saveAsSettings();
			
			\IPS\Session::i()->log( 'acplogs__promote_save', array( "acplogs__promote_changed_schedule" => true ) );

			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=core&module=promotion&controller=promote&tab=" . \IPS\Request::i()->tab ), 'saved' );
		}
		
		return $form;
	}
	
	/**
	 * Manage Permissions
	 *
	 * @return	string
	 */
	protected function _managePermissions()
	{
		$form = new \IPS\Helpers\Form;
		$groups = array();
		
		foreach( \IPS\Member\Group::groups() as $group )
		{
			if ( $group->g_bitoptions['gbw_promote'] )
			{
				$groups[] = \IPS\Theme::i()->getTemplate( 'promote' )->groupLink( $group->g_id, $group->name );
			}
		}
		
		$output  = \IPS\Theme::i()->getTemplate( 'promote' )->permissionBlurb( $groups );
		$form->add( new \IPS\Helpers\Form\Member( 'promote_members', \IPS\Settings::i()->promote_members ? array_map( array( 'IPS\Member', 'load' ), explode( "\n", \IPS\Settings::i()->promote_members ) ) : NULL, FALSE, array( 'multiple' => NULL ), NULL, NULL, NULL, 'promote_twitter_members' ) );
		
		/* Are we saving? */
		if ( $values = $form->values() )
		{
			$form->saveAsSettings();
			
			\IPS\Session::i()->log( 'acplogs__promote_save', array( "acplogs__promote_changed_permissions" => true ) );
			
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=core&module=promotion&controller=promote&tab=" . \IPS\Request::i()->tab ), 'saved' );
		}

		return $output . $form;
	}
	
	/**
	 * Manage Internal
	 *
	 * @return	string
	 */
	protected function _manageInternal()
	{
		$account = \IPS\core\Promote::getPromoter('Internal')->setMember( \IPS\Member::loggedIn() );
		$output  = \IPS\Theme::i()->getTemplate( 'promote' )->internalBlurb();
		$form = new \IPS\Helpers\Form;
		
		$form->add( new \IPS\Helpers\Form\YesNo( 'promote_internal_enabled', $account->enabled, FALSE, array(), NULL, NULL, NULL, 'promote_internal_enabled' ) );
		
		/* Are we saving? */
		if ( $values = $form->values() )
		{
			$account->enabled = $values['promote_internal_enabled'];
			$account->save();
			
			$form->saveAsSettings( array( 'promote_community_enabled' => $account->enabled ) );
			
			\IPS\Session::i()->log( 'acplogs__promote_save', array( "acplogs__promote_changed_community" => true ) );
			
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=core&module=promotion&controller=promote&tab=" . \IPS\Request::i()->tab ), 'saved' );
		}
		
		return $output . $form;
	}
	
	/**
	 * Manage Twitter
	 *
	 * @return	string
	 */
	protected function _manageTwitter()
	{
		$twitter = \IPS\Login\Handler::findMethod('IPS\Login\Handler\Oauth1\Twitter');
		$account = \IPS\core\Promote::getPromoter('Twitter')->setMember( \IPS\Member::loggedIn() );

		/* Have we set up Twitter yet? */
		if ( !$twitter OR !$twitter->settings['consumer_key'] )
		{
			/* Nope, so lets do that first... */
			return \IPS\Theme::i()->getTemplate( 'promote' )->warningNoTwitterApp();
		}
		
		/* Have we linked to a Twitter account? */
		if ( ! $account->settings['token'] )
		{
			/* Nope, so lets do that now... */
			return \IPS\Theme::i()->getTemplate( 'promote' )->warningNoTwitterUser();
		}
		
		/* Do we have post to page permissions? */
		$response = $account->canPostToPage();
		if ( $response !== TRUE )
		{
			/* Token stores permissions, so wipe it */
			$account->enabled = FALSE;
			$account->saveSettings( array(
				'token' => NULL,
				'secret' => NULL,
				'owner' => NULL,
				'name' => NULL
			) );
			
			return \IPS\Theme::i()->getTemplate( 'promote' )->warningNoTwitterPostToPagePermission( $response );
		}
		
		$form = new \IPS\Helpers\Form;
		
		if ( isset( \IPS\Request::i()->clear ) and \IPS\Request::i()->clear === 'true' )
		{
			/* We want to clear settings */
			$account->enabled = FALSE;
			$account->saveSettings( array(
				'token' => NULL,
				'secret' => NULL,
				'owner' => NULL,
				'name' => NULL
			) );
			
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=core&module=promotion&controller=promote&tab=twitter" ) );
		}
		
		$owner = \IPS\Member::load( $account->settings['owner'] );
		$output = '';
		
		if ( $account->settings['owner'] and $account->settings['name'] )
		{
			try
			{
				$output = \IPS\Theme::i()->getTemplate( 'promote' )->twitterOwnedBy( $owner, $account->settings['name'] );
			}
			catch( \OutOfRangeException $e )
			{
				/* Member no longer exists, so clear settings */
				$account->enabled = FALSE;
				$account->saveSettings( array(
					'token' => NULL,
					'secret' => NULL,
					'owner' => NULL,
					'name' => NULL
				) );
				
				\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=core&module=promotion&controller=promote&tab=twitter" ) );
			}
		}
		
		$form->add( new \IPS\Helpers\Form\YesNo( 'promote_twitter_enabled', $account->enabled, FALSE, array( 'togglesOn' => array() ), NULL, NULL, NULL, 'promote_twitter_enabled' ) );
		$form->add( new \IPS\Helpers\Form\Stack( 'promote_twitter_tags', $account->settings['tags'], FALSE, array( 'stackFieldType' => 'Text' ), NULL, NULL, NULL, 'promote_twitter_tags' ) );
		$form->add( new \IPS\Helpers\Form\Radio( 'promote_twitter_tags_method', $account->settings['tags_method'], FALSE, array( 'options' => array(
			'fill' => 'promote_twitter_tags_method_fill',
			'trim' => 'promote_twitter_tags_method_trim'
		) ), NULL, NULL, NULL, 'promote_twitter_tags_method' ) );

		/* Are we saving? */
		if ( $values = $form->values() )
		{
			if ( \is_array( $values['promote_twitter_tags'] ) )
			{
				array_walk( $values['promote_twitter_tags'], function( &$value )
				{
					$value = str_replace( '#', '', $value );
				} );
			}
			
			$account->enabled = $values['promote_twitter_enabled'];
			$save = array(
				'tags' => $values['promote_twitter_tags'],
				'tags_method' => $values['promote_twitter_tags_method']
			);
			
			$account->saveSettings( $save );
			
			\IPS\Session::i()->log( 'acplogs__promote_save', array( "acplogs__promote_changed_twitter" => true ) );
			
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=core&module=promotion&controller=promote&tab=" . \IPS\Request::i()->tab ), 'saved' );
		}

		return $output . $form;
	}
	
	/**
	 * Manage Facebook
	 *
	 * @return	string
	 */
	protected function _manageFacebook()
	{
		$facebook = \IPS\Login\Handler::findMethod('IPS\Login\Handler\Oauth2\Facebook');
		$account = \IPS\core\Promote::getPromoter('Facebook')->setMember( \IPS\Member::loggedIn() );
		
		if ( isset( \IPS\Request::i()->clear ) and \IPS\Request::i()->clear === 'true' )
		{
			/* We want to clear settings */
			$account->enabled = FALSE;
			$account->saveSettings( array(
				'token' => NULL,
				'owner' => NULL,
				'page_name' => NULL,
				'enabled' => NULL,
				'page' => NULL,
				'group' => NULL,
				'member_token' => NULL,
				'shareable' => NULL
			) );
			
			\IPS\Settings::i()->changeValues( array( 'promote_facebook_auth' => '' ) );
			
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=core&module=promotion&controller=promote&tab=facebook" ) );
		}
		
		/* We are using the default app here */
		if ( \is_numeric( \IPS\Settings::i()->promote_facebook_auth ) )
		{
			/* Have we set up Facebook yet? */
			if ( !$facebook OR ! $facebook->settings['client_id'] )
			{
				/* Nope, so lets do that first... */
				return \IPS\Theme::i()->getTemplate( 'promote' )->warningNoFacebookApp();
			}
		}
		else
		{
			/* We are using our custom app here */
			$facebook->settings = json_decode( \IPS\Settings::i()->promote_facebook_auth, TRUE );
		}
		
		/* promote_facebook_auth: value 0 means not set up, value 1 means using default, JSON means using custom */
		if ( ! \IPS\Settings::i()->promote_facebook_auth )
		{
			$form = new \IPS\Helpers\Form;
			$json = array();
			
			if ( ! \is_int( \IPS\Settings::i()->promote_facebook_auth ) )
			{
				$json = json_decode( \IPS\Settings::i()->promote_facebook_auth, TRUE );
			}
			
			$form->add( new \IPS\Helpers\Form\Radio( 'promote_facebook_auth_choose', ( \IPS\Settings::i()->promote_facebook_auth === 0 or \IPS\Settings::i()->promote_facebook_auth === 1 ) ? 1 : 0, FALSE, array( 'options' => array(
				0 => 'promote_facebook_auth_choose_default',
				1 => 'promote_facebook_auth_choose_custom'
			),
			'toggles' => array(
				1 => array( 'promote_facebook_auth_id', 'promote_facebook_auth_secret' )
			) ), NULL, NULL, NULL, 'promote_facebook_auth_choose' ) );
			
			$form->add( new \IPS\Helpers\Form\Text( 'promote_facebook_auth_id', ( isset( $json['client_id'] ) ? $json['client_id'] : NULL ), TRUE, array(), NULL, NULL, NULL, 'promote_facebook_auth_id' ) );
			$form->add( new \IPS\Helpers\Form\Text( 'promote_facebook_auth_secret', ( isset( $json['client_secret'] ) ? $json['client_secret'] : NULL ), TRUE, array(), NULL, NULL, NULL, 'promote_facebook_auth_secret' ) );
			
			\IPS\Member::loggedIn()->language()->words['promote_facebook_auth_id_desc'] = \IPS\Member::loggedIn()->language()->addToStack( 'login_handler_Facebook_info', FALSE, array( 'sprintf' => array( (string) \IPS\Http\Url::internal( 'oauth/callback/', 'none' ) ) ) );
			
			/* Are we saving? */
			if ( $values = $form->values() )
			{ 
				/* Default to .. default */
				$auth = 1;
				
				if ( $values['promote_facebook_auth_choose'] == 1 )
				{
					$auth = array(
						'client_id' => $values['promote_facebook_auth_id'],
						'client_secret' => $values['promote_facebook_auth_secret']
					);
				}
				
				\IPS\Settings::i()->changeValues( array( 'promote_facebook_auth' => json_encode( $auth ) ) );
				\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=core&module=promotion&controller=promote&tab=" . \IPS\Request::i()->tab ), 'saved' );
			}
			
			return $form;
		}
				
		/* Have we synchronized our user account with Facebook? */
		if ( ! $account->isConnected( \IPS\Member::loggedIn() ) )
		{
			/* Nope, so lets do that now... */
			return \IPS\Theme::i()->getTemplate( 'promote' )->warningNoFacebookUser( $facebook, \is_numeric( \IPS\Settings::i()->promote_facebook_auth ) );
		}
		
		/* Do we have post to page permissions? */
		$response = $account->canPostToPage();
		if ( $response !== TRUE )
		{
			/* Nope, so lets do that now... */
			return \IPS\Theme::i()->getTemplate( 'promote' )->warningNoFacebookPostToPagePermission( $response, $facebook );
		}
		
		$form = new \IPS\Helpers\Form;
			
		/* We're all set, so show the form! */
		$pages = $facebook->getPages( \IPS\Member::loggedIn() );
		$options = array();
		$shareable = array();
		
		foreach( $pages as $id => $data )
		{
			$options[ 'p_' . $id ] = \IPS\Member::loggedIn()->language()->addToStack( 'promote_facebook_page_prefix', NULL, array( 'sprintf' => array( $data[0] ) ) );
		}
		
		$groups = $facebook->getGroups( \IPS\Member::loggedIn() );
		foreach( $groups as $id => $data )
		{
			$options[ 'g_' . $id ] = \IPS\Member::loggedIn()->language()->addToStack( 'promote_facebook_group_prefix', NULL, array( 'sprintf' => array( $data[0] ) ) );
		}
		
		$output = '';
		$owner = \IPS\Member::load( $account->settings['owner'] );

		if ( isset( $account->settings['shareable'] ) )
		{
			if( isset( $account->settings['shareable']['pages'] ) )
			{
				foreach( $account->settings['shareable']['pages'] as $id => $data )
				{
					$shareable[] = 'p_' . $id;
				}
			}
			
			if( isset( $account->settings['shareable']['groups'] ) )
			{
				foreach( $account->settings['shareable']['groups'] as $id => $data )
				{
					$shareable[] = 'g_' . $id;
				}
			}
		}
		
		if ( $account->settings['owner'] )
		{
			try
			{
				$output = \IPS\Theme::i()->getTemplate( 'promote' )->facebookOwnedBy( $owner, $account->settings['page'], $account->settings['page_name'] );
			}
			catch( \OutOfRangeException $e )
			{
				/* Member no longer exists, so clear settings */
				$account->enabled = FALSE;
				$account->saveSettings( array(
					'token' => NULL,
					'owner' => NULL,
					'page_name' => NULL
				) );
				
				\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=core&module=promotion&controller=promote&tab=facebook" ) );
			}
		}
		
		$form->add( new \IPS\Helpers\Form\YesNo( 'promote_facebook_enabled', $account->enabled, FALSE, array( 'togglesOn' => array( 'promote_facebook_shareable', 'promote_facebook_members', 'promote_facebook_tags' ) ), NULL, NULL, NULL, 'promote_facebook_enabled' ) );
		
		if ( ! $account->settings['owner'] or ( \IPS\Member::loggedIn()->member_id == $account->settings['owner'] ) )
		{
			$form->add( new \IPS\Helpers\Form\CheckboxSet( 'promote_facebook_shareable', $shareable, FALSE, array( 'options' => $options ), NULL, NULL, NULL, 'promote_facebook_shareable' ) );
		}
		
		$form->add( new \IPS\Helpers\Form\Stack( 'promote_facebook_tags', $account->settings['tags'], FALSE, array( 'stackFieldType' => 'Text' ), NULL, NULL, NULL, 'promote_facebook_tags' ) );

		/* Are we saving? */
		if ( $values = $form->values() )
		{ 
			if ( \is_array( $values['promote_facebook_tags'] ) )
			{
				array_walk( $values['promote_facebook_tags'], function( &$value )
				{
					$value = str_replace( '#', '', $value );
				} );
			}
			
			$account->enabled = $values['promote_facebook_enabled'];
			
			/* If we have page data, then we are the page owner and can have extra special things */
			if ( isset( $values['promote_facebook_shareable'] ) )
			{
				$shareable = array( 'pages' => array(), 'groups' => array() );
				foreach( $values['promote_facebook_shareable'] as $id )
				{
					$realId = mb_substr( $id, 2 );
					
					/* Page or group? */
					if ( mb_substr( $id, 0, 2 ) == 'p_' )
					{
						$shareable['pages'][ $realId ] = array(
							'token' => $facebook->exchangeToken( $pages[ $realId ][1] ),
							'name'  => $pages[ $realId ][0]
						);
					}
					else
					{
						$shareable['groups'][ $realId ] = array(
							'name'  => $groups[ $realId ][0]
						);
					}
				}
				
				$save['shareable'] = $shareable;
				$save['owner'] = \IPS\Member::loggedIn()->member_id;
				try
				{
					$save['token'] = \IPS\Db::i()->select( 'token_access_token', 'core_login_links', array( 'token_login_method=? and token_member=?', $facebook->id, \IPS\Member::loggedIn()->member_id ) )->first();
				}
				catch( \UnderflowException $e )
				{
					$save['token'] = NULL;
				}
				
				$save['tags'] = $values['promote_facebook_tags'];
			}
		
			$account->saveSettings( $save );
			
			\IPS\Session::i()->log( 'acplogs__promote_save', array( "acplogs__promote_changed_facebook" => true ) );
			
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=core&module=promotion&controller=promote&tab=" . \IPS\Request::i()->tab ), 'saved' );
		}
		
		return $output . $form;
	}
	
	/**
	 * Response Logs
	 *
	 * @return	void
	 */
	protected function logs()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'restrictions_acploginlogs' );
		
		/* Create the table */
		$table = new \IPS\Helpers\Table\Db( 'core_social_promote_content', \IPS\Http\Url::internal( 'app=core&module=promotion&controller=promote&do=logs' ), array( array( "response_promote_key != 'internal'" ) ) );
		$table->langPrefix = 'promote_logs_';
		$table->sortBy	= $table->sortBy ?: 'response_date';
		$table->sortDirection	= $table->sortDirection ?: 'DESC';
		$table->include = array( 'response_promote_id', 'response_promote_key', 'response_json', 'response_failed', 'response_date' );
		$table->widths['response_json'] = '40';
		
		/* Search */
		$table->quickSearch = 'response_json';
		
		/* Filters */
		$table->filters = array(
			'promote_logs_success'		=> 'response_failed = 0',
			'promote_logs_unsuccessful'	=> 'response_failed = 1',
		);
		
		/* Custom parsers */
		$table->parsers = array(
			'response_date'	=> function( $val, $row )
			{
				return \IPS\DateTime::ts( $val );
			},
			'response_failed'	=> function( $val, $row )
			{
				return ( ! $val ) ? "<i class='fa fa-check'></i>" : "<i class='fa fa-times'></i>";
			},
			'response_promote_id' => function( $val, $row )
			{
				try
				{
					$promote = \IPS\core\Promote::load( $val );
					
					return htmlspecialchars( $promote->objectTitle, ENT_QUOTES | ENT_DISALLOWED, 'UTF-8', FALSE ) . ' (' . $val . ')';
					
				}
				catch ( \Exception $e ) { }
			},
			'response_promote_key'	=> function( $val, $row )
			{
				return \ucfirst( $val );
			},
			'response_json' => function( $val, $row )
			{
				$val = json_decode( $val, true );
				return '<div data-ipstruncate data-ipstruncate-type="hide" data-ipstruncate-size="3 lines" class="ipsType_break">' . var_export( $val, true ) . "</div>";
			}
		);

		/* Display */
		\IPS\Output::i()->breadcrumb[] = array( \IPS\Http\Url::internal( 'app=core&module=promotion&controller=promote' ), \IPS\Member::loggedIn()->language()->addToStack( 'menu__core_promotion_promote' ) );
		\IPS\Output::i()->title		= \IPS\Member::loggedIn()->language()->addToStack('promote_logs');
		\IPS\Output::i()->output	= (string) $table;
	}
	
	/**
	 * Reschedule things
	 *
	 * @return void
	 */
	protected function reschedule()
	{
		\IPS\core\Promote::rescheduleQueue();
		
		\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=core&module=promotion&controller=promote&tab=schedule" ), 'promote_rescheduled_done' );
	}

}