<?php
/**
 * @brief		Notification Settings
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		23 Apr 2013
 */

namespace IPS\core\modules\admin\membersettings;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Notification Settings
 */
class _notifications extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'notifications_manage' );
		parent::execute();
	}

	/**
	 * Manage
	 *
	 * @return	void
	 */
	protected function manage()
	{
		/* Init Matrix */
		$matrix = new \IPS\Helpers\Form\Matrix;
		$matrix->manageable = FALSE;
		$matrix->langPrefix = 'notificationsettings_';
		$matrix->classes = array( 'cMemberNotifications' );
		
		/* Populate columns */
		$matrix->columns = array(
			'label'		=> function( $key, $value, $data )
			{
				if ( mb_substr( $key, 0, -7 ) === 'new_likes' )
				{
					if ( \IPS\Content\Reaction::isLikeMode() )
					{
						return 'notifications__new_likes_like';
					}
					else
					{
						return 'notifications__new_likes_rep';
					}
				}
				
				return $value;
			},
			'default'	=> function( $key, $value, $data )
			{
				return new \IPS\Helpers\Form\CheckboxSet( $key, $value, FALSE, array( 'options' => array( 'email' => 'member_notifications_email', 'inline' => 'member_notifications_inline' ), 'multiple' => TRUE ) );
			},
			'disabled'	=> function( $key, $value, $data )
			{
				return new \IPS\Helpers\Form\CheckboxSet( $key, $value, FALSE, array( 'options' => array( 'email' => 'member_notifications_email', 'inline' => 'member_notifications_inline' ), 'multiple' => TRUE ) );
			},
			'editable'	=> function( $key, $value, $data )
			{
				return new \IPS\Helpers\Form\YesNo( $key, $value );
			},
		);
		
		/* Populate rows */
		$current = iterator_to_array( \IPS\Db::i()->select( '*', 'core_notification_defaults' )->setKeyField( 'notification_key' ) );
		foreach( \IPS\Application::allExtensions( 'core', 'Notifications', FALSE ) as $group => $class )
		{
			$configuration = $class->getConfiguration( NULL );
			if ( !empty( $configuration ) )
			{
				$lang = "notifications__{$group}";
				$header = \IPS\Member::loggedIn()->language()->addToStack( $lang );
				$matrix->rows[] = $header;
				
				foreach ( $configuration as $key => $data )
				{
					$matrix->rows[ $key ] = array(
						'label'		=> "notifications__{$key}",
						'default'	=> isset( $current[ $key ] ) ? explode( ',', $current[ $key ]['default'] ) : $data['default'],
						'disabled'	=> isset( $current[ $key ] ) ? explode( ',', $current[ $key ]['disabled'] ) : $data['disabled'],
						'editable'	=> isset( $current[ $key ] ) ? $current[ $key ]['editable'] : TRUE,
					);
				}
			}
		}
		
		/* Handle submissions */
		if ( $values = $matrix->values() )
		{
			\IPS\Db::i()->delete( 'core_notification_defaults' );
			
			$inserts = array();
			foreach ( $values as $k => $data )
			{
				$inserts[] = array(
					'notification_key'	=> $k,
					'default'			=> implode( ',', $data['default'] ),
					'disabled'			=> implode( ',', $data['disabled'] ),
					'editable'			=> $data['editable']
				);
			}
			
			if( \count( $inserts ) )
			{
					\IPS\Db::i()->insert( 'core_notification_defaults', $inserts );
			}
			
			\IPS\Session::i()->log( 'acplog__notifications_edited' );
		}
		
		/* Add a button for settings */
		\IPS\Output::i()->sidebar['actions'] = array(
			'settings'	=> array(
				'title'		=> 'notificationsettings',
				'icon'		=> 'cog',
				'link'		=> \IPS\Http\Url::internal( 'app=core&module=membersettings&controller=notifications&do=settings' ),
				'data'		=> array( 'ipsDialog' => '', 'ipsDialog-title' => \IPS\Member::loggedIn()->language()->addToStack('notificationsettings') )
			),
		);
		
		/* Display */
		\IPS\Output::i()->cssFiles = array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( 'members/notifications.css', 'core', 'admin' ) );
		\IPS\Output::i()->title		= \IPS\Member::loggedIn()->language()->addToStack('notifications');
		\IPS\Output::i()->output	= \IPS\Theme::i()->getTemplate( 'global' )->block( 'notifications', $matrix );
	}
	
	/**
	 * Profile Settings
	 *
	 * @return	void
	 */
	protected function settings()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'profiles_manage' );

		$form = new \IPS\Helpers\Form;

		$form->addMessage( 'auto_follow_update_all' );
		$form->addHeader('auto_follow_preferences');
		$form->add( new \IPS\Helpers\Form\YesNo( 'auto_follow_new_content', \IPS\Settings::i()->auto_follow_new_content, FALSE, array(), NULL, NULL, NULL, 'auto_follow_new_content' ) );
		$form->add( new \IPS\Helpers\Form\YesNo( 'auto_follow_replied_to', \IPS\Settings::i()->auto_follow_replied_to, FALSE, array(), NULL, NULL, NULL, 'auto_follow_replied_to' ) );
		$form->addHeader('notification_preferences');
		$form->add( new \IPS\Helpers\Form\YesNo( 'notification_prefs_popup', \IPS\Settings::i()->notification_prefs_popup, FALSE, array(), NULL, NULL, NULL, 'notification_prefs_popup' ) );
		$form->add( new \IPS\Helpers\Form\YesNo( 'notification_prefs_one_per_view', \IPS\Settings::i()->notification_prefs_one_per_view, FALSE, array(), NULL, NULL, NULL, 'notification_prefs_one_per_view' ) );
		$form->add( new \IPS\Helpers\Form\YesNo( 'notification_prefs_sound', \IPS\Settings::i()->notification_prefs_sound, FALSE, array(), NULL, NULL, NULL, 'notification_prefs_sound' ) );
		$form->addHeader('notification_pruning');
		$form->add( new \IPS\Helpers\Form\Interval( 'subs_autoprune', \IPS\Settings::i()->subs_autoprune, FALSE, array( 'valueAs' => \IPS\Helpers\Form\Interval::DAYS, 'unlimited' => 0, 'unlimitedLang' => 'never' ), NULL, \IPS\Member::loggedIn()->language()->addToStack('after'), NULL ) );
		$form->add( new \IPS\Helpers\Form\Interval( 'prune_notifications', \IPS\Settings::i()->prune_notifications, FALSE, array( 'valueAs' => \IPS\Helpers\Form\Interval::DAYS, 'unlimited' => 0, 'unlimitedLang' => 'never' ), NULL, \IPS\Member::loggedIn()->language()->addToStack('after') ) );

		if ( $values = $form->values() )
		{
			$form->saveAsSettings();

			\IPS\Session::i()->log( 'acplog__notification_settings' );
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=core&module=membersettings&controller=notifications' ), 'saved' );
		}
		
		\IPS\Output::i()->title		= \IPS\Member::loggedIn()->language()->addToStack('notificationsettings');
		\IPS\Output::i()->output 	= \IPS\Theme::i()->getTemplate('global')->block( 'notificationsettings', $form, FALSE );
	}
	/**
	 * Member Auto Follow Preferences
	 *
	 * @return	void
	 */
	protected function updateMemberFollowPrefs()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'profiles_manage' );

		$autoTrack = json_encode( array(
			'content'	=> \IPS\Settings::i()->auto_follow_new_content ? 1 : 0,
			'comments'	=> \IPS\Settings::i()->auto_follow_replied_to ? 1 : 0,
			'method'	=> 'immediate'
		)	);

		$bitOptions = array();
		$bitOptions[] = ( \IPS\Settings::i()->notification_prefs_popup ) ? "members_bitoptions2 = members_bitoptions2 | 1" : "members_bitoptions2 = members_bitoptions2 &~ 1";
		$bitOptions[] = ( \IPS\Settings::i()->notification_prefs_one_per_view ) ? "members_bitoptions2 = members_bitoptions2 | 128" : "members_bitoptions2 = members_bitoptions2 &~ 128";
		$bitOptions[] = ( \IPS\Settings::i()->notification_prefs_sound )  ? "members_bitoptions2 = members_bitoptions2 &~ 256" : "members_bitoptions2 = members_bitoptions2 | 256";
		
		\IPS\Db::i()->update( 'core_members', array( 'auto_track' => $autoTrack ) );
		\IPS\Db::i()->update( 'core_members', implode( ", ", $bitOptions ) );

		\IPS\Session::i()->log( 'acplog__notification_settings_existing' );

		\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=core&module=membersettings&controller=notifications' ), 'saved' );
	}
}