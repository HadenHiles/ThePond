<?php
/**
 * @brief		Notification Class
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		23 Apr 2013
 */

namespace IPS;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Notification Class
 */
class _Notification
{
	/**
	 * @brief	Default Configuration
	 */
	protected static $defaultConfiguration = NULL;
	
	/**
	 * Get default configuration
	 *
	 * @return	array
	 */
	public static function defaultConfiguration()
	{
		if ( static::$defaultConfiguration === NULL )
		{
			static::$defaultConfiguration = iterator_to_array( \IPS\Db::i()->select( '*', 'core_notification_defaults' )->setKeyField('notification_key') );
			
			foreach( \IPS\Application::allExtensions( 'core', 'Notifications' ) as $group => $class )
			{
				$configuration = $class->getConfiguration( NULL );
				if ( !empty( $configuration ) )
				{
					foreach ( $configuration as $key => $data )
					{
						if ( !isset( static::$defaultConfiguration[ $key ] ) )
						{
							/* Row isn't in DB, add it */
							\IPS\Db::i()->insert( 'core_notification_defaults', array(
								'notification_key' => $key,
								'default'		   => implode( ',', $data['default'] ),
								'disabled'		   => implode( ',', $data['disabled'] )
							) );
							
							static::$defaultConfiguration[ $key ] = array_merge( $data, array( 'editable' => TRUE ) );
						}
						else
						{
							static::$defaultConfiguration[ $key ]['default'] = array_filter( explode( ',', static::$defaultConfiguration[ $key ]['default'] ) );
							static::$defaultConfiguration[ $key ]['disabled'] = array_filter( explode( ',', static::$defaultConfiguration[ $key ]['disabled'] ) );
						}
					}
				}
			}
		}
		return static::$defaultConfiguration;
	}
	
	/**
	 * Build Matrix
	 *
	 * @param	\IPS\Member	$member	The member
	 * @return	\IPS\Helpers\Form\Matrix
	 */
	public static function buildMatrix( \IPS\Member $member )
	{
		$matrix = new \IPS\Helpers\Form\Matrix();
		$matrix->manageable = FALSE;
		$matrix->langPrefix = FALSE;
		$matrix->columns = array(
			'label'		=> function( $key, $value, $data )
			{
				if ( mb_substr( $key, 0, -7 ) === 'new_likes' )
				{
					if ( \IPS\Content\Reaction::isLikeMode() )
					{
						$return = 'notifications__new_likes_like';
					}
					else
					{
						$return = 'notifications__new_likes_rep';
					}
				}
				else
				{
					$return = 'notifications__' . mb_substr( $key, 0, -7 );
				}
				
				return \IPS\Theme::i()->getTemplate( 'members', 'core', 'global' )->notificationLabel( $return, $data );
			},
			'member_notifications_inline'	=> function( $key, $value, $data )
			{
				return new \IPS\Helpers\Form\YesNo( $key, \in_array( 'inline', ( \is_array( $data['selected'] ) ) ? $data['selected'] : array() ), FALSE, array( 'disabled' => ( !$data['editable'] OR \in_array( 'inline', $data['disabled'] ) ), 'tooltip' => ( !$data['editable'] OR \in_array( 'inline', $data['disabled'] ) ) ? \IPS\Member::loggedIn()->language()->addToStack('admin_notification_disabled') : NULL ) );
			},
			'member_notifications_email'	=> function( $key, $value, $data )
			{
				return new \IPS\Helpers\Form\YesNo( $key, \in_array( 'email', ( \is_array( $data['selected'] ) ) ? $data['selected'] : array() ), FALSE, array( 'disabled' => ( !$data['editable'] OR \in_array( 'email', $data['disabled'] ) ), 'tooltip' => ( !$data['editable'] OR \in_array( 'email', $data['disabled'] ) ) ? \IPS\Member::loggedIn()->language()->addToStack('admin_notification_disabled') : NULL ) );
			},
		);
		
		/* Add rows */
		$defaultConfiguration = static::defaultConfiguration();
		$memberConfiguration = $member->notificationsConfiguration();
		foreach( \IPS\Application::allExtensions( 'core', 'Notifications' ) as $group => $class )
		{
			$configuration = $class->getConfiguration( $member );
			if ( !empty( $configuration ) )
			{
				$lang = "notifications__{$group}";
				$header = \IPS\Member::loggedIn()->language()->addToStack( $lang );
				$matrix->rows[] = $header;
				
				foreach ( $configuration as $key => $data )
				{
					$matrix->rows[ $key ] = array( 'selected' => ( !empty( $memberConfiguration[ $key ] ) ) ? $memberConfiguration[ $key ] : NULL, 'disabled' => $defaultConfiguration[ $key ]['disabled'], 'icon' => isset( $data['icon'] ) ? $data['icon'] : NULL, 'editable' => $defaultConfiguration[ $key ]['editable'] );
				}
			}
		}
		
		return $matrix;
	}
	
	/**
	 * Save Matrix
	 *
	 * @param	\IPS\Member	$member	The member
	 * @param	array		$values	Values from matrix
	 * @return	void
	 */
	public static function saveMatrix( \IPS\Member $member, array $values )
	{
		/* Remove the current preferences */
		\IPS\Db::i()->delete( 'core_notification_preferences', array( 'member_id=?', $member->member_id ) );
		
		/* Get the default configuration so we know what is forced enabled by the admin */
		$defaults = static::defaultConfiguration();

		/* Now loop over the notifications and set preferences */
		$insert = array();
		foreach ( $values['notifications'] as $k => $v )
		{
			$pref = array();
			if ( $v['member_notifications_inline'] )
			{
				$pref[] = 'inline';
			}
			else if( !$defaults[ $k ]['editable'] AND \in_array( 'inline', $defaults[ $k ]['default'] ) )
			{
				$pref[] = 'inline';
			}
			if ( $v['member_notifications_email'] )
			{
				$pref[] = 'email';
			}
			else if( !$defaults[ $k ]['editable'] AND \in_array( 'email', $defaults[ $k ]['default'] ) )
			{
				$pref[] = 'email';
			}
			
			$insert[] = array(
				'member_id'			=> $member->member_id,
				'notification_key'	=> $k,
				'preference'		=> implode( ',', $pref )
			);
		}

		\IPS\Db::i()->insert( 'core_notification_preferences', $insert );
	}
	
	/**
	 * @brief	Application
	 */
	protected $app;
	
	/**
	 * @brief	Notification key
	 */
	protected $key;
	
	/**
	 * @brief	Email template key
	 * @note	Typically this is "notification__{key}"
	 */
	protected $emailKey;

	/**
	 * @brief	Item
	 */
	protected $item;
		
	/**
	 * @brief	An \IPS\Notification\Recipients object which contains \IPS\Member objects and replacements to use for that member in the notification content.
	 * @code
	 	$notification->recipients->attach( $member, array( 'foo' => 'bar' ) );
	 	$notification->recipients->attach( $member2, array( 'foo' => 'baz' ) );
	 * @endcode
	 */
	public $recipients;
	
	/**
	 * @brief	Data for notification emails
	 */
	protected $emailParams = array();
	
	/**
	 * @brief	Extra data to save with inline notifications
	 */
	protected $inlineExtra = array();
	
	/**
	 * @brief	Unsubscribe Type
	 */
	public $unsubscribeType = 'notification';

	/**
	 * @brief	Allow merging of notifications
	 */
	protected $allowMerging = TRUE;

	/**
	 * Constructor
	 *
	 * @param	\IPS\Application	$app			The application the notification belongs to
	 * @param	string				$key			Notification key
	 * @param	object|NULL			$item			The thing the notification is about
	 * @param	array				$emailParams	Data for notification emails
	 * @param	array				$inlineExtra	Extra data to save with inline notifications. Use sparingly: only in cases where it is not possible to obtain the same data later. Will be merged for duplicate notifications.
	 * @param	bool				$allowMerging	Allow two identical notification types to be merged
	 * @param	string|NULL			$emailKey		Custom email template to use, or NULL to use default
	 * @return	void
	 */
	public function __construct( \IPS\Application $app, $key, $item=NULL, $emailParams=array(), $inlineExtra=array(), $allowMerging=TRUE, $emailKey=NULL )
	{
		$this->app			= $app;
		$this->key			= $key;
		$this->item			= $item;
		$this->recipients	= new \IPS\Notification\Recipients;
		$this->emailParams	= $emailParams;
		$this->inlineExtra	= $inlineExtra;
		$this->allowMerging = $allowMerging;
		$this->emailKey		= ( $emailKey === NULL ) ? 'notification_' . $this->key : $emailKey;
	}
	
	/**
	 * Send Notification
	 *
	 * @param	array	$sentTo		Members who have already received a notification and how (same format as the return value) to prevent duplicates
	 * @return	array	The members that were notified and how they were notified
	 */
	public function send( $sentTo = array() )
	{
		/* Make a placeholder for emails - we'll need to generate one per language */
		$emails = array();
		$emailRecipients = array();
		$thingsBeingFollowed = array();

		/* First, loop over the members so we can load their notification preferences en-masse */
		$membersForNotifications = array();

		foreach ( $this->recipients as $member )
		{						
			/* Let's not send notifications to deleted members, banned members or spammers */
			if ( $member === NULL or !$member->member_id or $member->isBanned() or $member->members_bitoptions['bw_is_spammer'] )
			{
				continue;
			}

			$membersForNotifications[ $member->member_id ] = $member;
		}

		if( \count( $membersForNotifications ) )
		{
			/* Fill in any that may not have customized their preferences */
			foreach( $membersForNotifications as $member )
			{
				if( $member->notificationsConfiguration === NULL )
				{
					$member->notificationsConfiguration = array();
				}
			}

			/* Get all preferences at once */
			$preferenceSet = array();

			foreach (
				\IPS\Db::i()->select(
					'd.*, p.preference, p.member_id',
					array( 'core_notification_defaults', 'd' )
				)->join(
					array( 'core_notification_preferences', 'p' ),
					array( 'd.notification_key=p.notification_key AND p.member_id IN(' . implode( ',', array_keys( $membersForNotifications ) ) . ')' )
				)
				as $row
			) {
				if( !\in_array( $row['notification_key'], $preferenceSet ) )
				{
					foreach( $membersForNotifications as $member )
					{
						$member->notificationsConfiguration[ $row['notification_key'] ] = explode( ',', $row['default'] );
					}

					$preferenceSet[] = $row['notification_key'];
				}

				if ( $row['preference'] !== NULL AND $row['editable'] )
				{
					$membersForNotifications[ $row['member_id'] ]->notificationsConfiguration[ $row['notification_key'] ] = array_diff( explode( ',', $row['preference'] ), explode( ',', $row['disabled'] ) );
				}
			}
		}
		
		/* Loop recipients */
		foreach ( $this->recipients as $member )
		{						
			/* Let's not send notifications to deleted members, banned members or spammers */
			if ( $member === NULL or !$member->member_id or $member->isBanned() or $member->members_bitoptions['bw_is_spammer'] )
			{
				continue;
			}
			
			/* If there's an item, check the user has permission to view it and is not ignoring */
			if ( $this->item )
			{
				/* Permission check */
				$item = $this->item;
				if ( $item instanceof \IPS\Content\Item )
				{
					$application = \IPS\Application::load( $item::$application );
					if ( !$application->canAccess( $member ) )
					{
						continue;
					}

					/* Skip if member is ignoring the item author but only if this is a new content item.
					If a member is following content they should still receive reply notifications regardless of author */
					if ( $this->key == "new_content" and $member->isIgnoring( $item->author(), 'topics' ) )
					{
						continue;
					}
				}
				
				/* Not ignoring the comment this is about */
				foreach( $this->emailParams AS $param )
				{
					if ( $param instanceof \IPS\Content\Comment OR $param instanceof \IPS\Content\Review )
					{
						if ( $member->isIgnoring( $param->author(), 'topics' ) )
						{
							continue 2;
						}
					}
					
					if ( $param instanceof \IPS\Member )
					{
						if ( $member->isIgnoring( $param, 'topics' ) )
						{
							continue 2;
						}
					}
				}
			}
			
			/* Work out how the user wants to receive this notification */
			$notificationPreferences = $member->notificationsConfiguration();
			$info = $this->recipients->getInfo();
			if ( $info['follow_app'] === 'core' and $info['follow_area'] === 'member' )
			{
				$keyToCheck = 'follower_content';
			}
			else
			{
				$keyToCheck = $this->key;
				if ( $this->key === 'new_content_bulk' )
				{
					$keyToCheck = 'new_content';
				}
				if ( $this->key === 'unapproved_content_bulk' )
				{
					$keyToCheck = 'unapproved_content';
				}
			}
			
			/* They want to receive an email (we don't send until the end once we've collated all the emails to send) */
			if ( isset( $notificationPreferences[ $keyToCheck ] ) AND \in_array( 'email', $notificationPreferences[ $keyToCheck ] ) and ( !isset( $sentTo[ $member->member_id ] ) or !\in_array( 'email', $sentTo[ $member->member_id ] ) ) )
			{
				$language = $member->language()->id;

				if ( !isset( $emails[ $language ] ) )
				{
					$email = \IPS\Email::buildFromTemplate( $this->app->directory, $this->emailKey, $this->emailParams, \IPS\Email::TYPE_LIST );
					
					if ( $info )
					{
						$email->setUnsubscribe( 'core', 'unsubscribeFollow', array( $this->key ) );
					}
					else
					{
						$email->setUnsubscribe( 'core', 'unsubscribeNotification', array( $this->key ) );
					}
					
					$emails[ $language ] = $email;
				}
				
				$unsubscribeBlurb = NULL;
				$unfollowLink = NULL;
				$okToEmail = TRUE;
				
				if ( $info )
				{
					if ( !isset( $thingsBeingFollowed[ $info['follow_app'] ][ $info['follow_area'] ][ $info['follow_rel_id'] ] ) )
					{
						if ( $info['follow_app'] === 'core' and $info['follow_area'] === 'member' )
						{
							$thingsBeingFollowed[ $info['follow_app'] ][ $info['follow_area'] ][ $info['follow_rel_id'] ] = \IPS\Member::load( $info['follow_rel_id'] );
						}
						else
						{
							$classname = 'IPS\\' . $info['follow_app'] . '\\' . mb_ucfirst( $info['follow_area'] );
							$thingsBeingFollowed[ $info['follow_app'] ][ $info['follow_area'] ][ $info['follow_rel_id'] ] = $classname::load( $info['follow_rel_id'] );

							/* Set some parameters so the best advertisement possible can be loaded later */
							$email->setAdvertisementParameters( $classname, $info['follow_rel_id'] );
						}
					}
				
					$thingBeingFollowed = $thingsBeingFollowed[ $info['follow_app'] ][ $info['follow_area'] ][ $info['follow_rel_id'] ];
					if ( $thingBeingFollowed instanceof \IPS\Member )
					{
						$unsubscribeBlurb = $member->language()->addToStack( 'unsubscribe_blurb_follow_member', FALSE, array( 'htmlsprintf' => array( $thingBeingFollowed->name ) ) );
					}
					elseif ( $thingBeingFollowed instanceof \IPS\Node\Model )
					{
						$unsubscribeBlurb	= $member->language()->addToStack( 'unsubscribe_blurb_follow', FALSE, array( 'htmlsprintf' => array( $member->language()->addToStack( $thingBeingFollowed::$nodeTitle . '_sg' ), $thingBeingFollowed->getTitleForLanguage( $member->language() ) ) ) );
					}
					else
					{
						$unsubscribeBlurb	= $member->language()->addToStack( 'unsubscribe_blurb_follow', FALSE, array( 'htmlsprintf' => array( $member->language()->addToStack( $thingBeingFollowed::$title ), $thingBeingFollowed->mapped('title') ) ) );
					}
					
					$guestKey = md5( $info['follow_app'] . ';' . $info['follow_area'] . ';' . $info['follow_rel_id'] . ';' . $info['follow_member_id'] . ';' . $info['follow_added'] ) . '-' . md5( $member->email . ';' . $member->ip_address . ';' . $member->joined->getTimestamp() );
					$unfollowLink = \IPS\Http\Url::internal( "app=core&module=system&controller=notifications&do=unfollowFromEmail&follow_app={$info['follow_app']}&follow_area={$info['follow_area']}&follow_id={$info['follow_rel_id']}&gkey={$guestKey}", 'front' );

					/* If we are tracking email views/clicks, add the tracking info to this URL as the email handler won't be able to */
					if( \IPS\Settings::i()->prune_log_emailstats != 0 )
					{
						$unfollowLink = $unfollowLink->makeSafeForAcp()->setQueryString( array( 'email' => 1, 'type' => $this->emailKey ) );
					}

					$unfollowLink = (string) $unfollowLink;
	
					if ( $member->members_bitoptions['email_notifications_once'] and max( $member->last_activity, $member->last_visit ) < $info['follow_notify_sent'] )
					{
						$okToEmail = FALSE;
					}
				}
				
				if ( $okToEmail )
				{
					$emailRecipients[ $language ][ $member->email ] = array(
						'member_name'		=> $member->name,
						'unsubscribe_blurb'	=> $unsubscribeBlurb,
						'unfollow_link'		=> $unfollowLink
					);
				}
				
				$sentTo[ $member->member_id ][] = 'email';
			}

			/* They want to receive an inline notification... (ignore for report center which is treated special and the 'inline' notification
				preference actually instead controls whether the bubble should be shown on the report center icon at the top or not) */
			if ( $this->key != 'report_center' and isset( $notificationPreferences[ $keyToCheck ] ) and \in_array( 'inline', $notificationPreferences[ $keyToCheck ] ) and ( !isset( $sentTo[ $member->member_id ] ) or !\in_array( 'inline', $sentTo[ $member->member_id ] ) ) )
			{
				if ( $this->item AND $this->allowMerging )
				{
					try
					{
						$item = $this->item;
						$idColumn = $item::$databaseColumnId;
						$notification = \IPS\Notification\Inline::constructFromData( \IPS\Db::i()->select( '*', 'core_notifications', array( 'notification_key=? AND item_class=? AND item_id=? AND `member`=? AND read_time IS NULL', $this->key, \get_class( $this->item ), $item->$idColumn, $member->member_id ) )->first() );
						
						$notification->member = $member;
						$notification->updated_time = time();
						$notification->extra = array_merge( $notification->extra, $this->inlineExtra );
						$notification->save();
						
						continue;
					}
					catch ( \UnderflowException $e ) { }
				}

				$notification = new \IPS\Notification\Inline;
				$notification->member = $member;
				$notification->notification_app = $this->app;
				$notification->notification_key = $this->key;
				if ( $this->item )
				{
					$notification->item = $this->item;
				}
				$notification->member_data = $info;
				
				foreach( $this->emailParams AS $param )
				{
					if ( $param instanceof \IPS\Content )
					{
						$subIdColumn = $param::$databaseColumnId;
						$notification->item_sub_class	= \get_class( $param );
						$notification->item_sub_id		= $param->$subIdColumn;

						/*
						 * If this is a grouped comment or review, set the sent time to the same time as the comment just in case there is a slight delay
						 */
						if ( ( $param instanceof \IPS\Content\Comment OR $param instanceof \IPS\Content\Review ) && \in_array( $this->key, array( 'new_comment', 'new_review', 'quote', 'new_likes' ) ) )
						{
							if ( $this->key === 'new_likes' and $this->emailParams[1] instanceof \IPS\Member )
							{
								/* Reset the time to the time of the rep to prevent a slight delay from missing this notification */
								try
								{
									$where = $param->getReactionWhereClause();
									$where[] = array( 'member_id = ?', $this->emailParams[1]->member_id );
									
									$notification->sent_time = \IPS\Db::i()->select( 'rep_date', 'core_reputation_index', $where )->join( 'core_reactions', 'reaction=reaction_id' )->first();
								}
								catch( \Exception $ex ) { }
							}
							else
							{
								$notification->sent_time = $param->mapped('date');
							}
						}
					}
				}

				$notification->extra = $this->inlineExtra;
				
				$notification->save();
				
				$sentTo[ $member->member_id ][] = 'inline';
			}
		}

		/* Send any emails */
		$this->sendEmails( $emails, $emailRecipients );
		
		/* And return */
		return $sentTo;
	}

	/**
	 * Send emails
	 *
	 * @param	array 	$emails				Emails to send
	 * @param	array 	$emailRecipients	Email recipients
	 * @return	void
	 */
	protected function sendEmails( $emails, $emailRecipients )
	{
		foreach ( $emails as $languageId => $email )
		{
			if ( !empty( $emailRecipients[ $languageId ] ) )
			{
				$email->mergeAndSend( $emailRecipients[ $languageId ], NULL, NULL, array(), \IPS\Lang::load( $languageId ) );
			}
		}
	}
}
