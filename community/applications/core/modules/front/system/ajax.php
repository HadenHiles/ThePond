<?php
/**
 * @brief		AJAX actions
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		04 Apr 2013
 */

namespace IPS\core\modules\front\system;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * AJAX actions
 */
class _ajax extends \IPS\Dispatcher\Controller
{
	/**
	 * Find Member
	 *
	 * @return	void
	 */
	public function findMember()
	{
		$results = array();
		
		$input = str_replace( array( '%', '_' ), array( '\%', '\_' ), mb_strtolower( \IPS\Request::i()->input ) );
		
		$where = array( "name LIKE CONCAT(?, '%')" );
		$binds = array( $input );
		if ( \IPS\Dispatcher::i()->controllerLocation === 'admin' )
		{
			$where[] = "email LIKE CONCAT(?, '%')";
			$binds[] = $input;
			
			if ( \is_numeric( \IPS\Request::i()->input ) )
			{
				$where[] = "member_id=?";
				$binds[] = \intval( \IPS\Request::i()->input );
			}
		}
				
		/* Build the array item for this member after constructing a record */
		/* The value should be just the name so that it's inserted into the input properly, but for display, we wrap it in the group *fix */
		foreach ( \IPS\Db::i()->select( '*', 'core_members', array_merge( array( implode( ' OR ', $where ) ), $binds ), 'LENGTH(name) ASC', array( 0, 20 ) ) as $row )
		{
			$member = \IPS\Member::constructFromData( $row );
			
			$results[] = array(
				'id'	=> 	$member->member_id,
				'value' => 	$member->name,
				'name'	=> 	\IPS\Dispatcher::i()->controllerLocation == 'admin' ? $member->group['prefix'] . htmlspecialchars( $member->name, ENT_DISALLOWED | ENT_QUOTES, 'UTF-8', FALSE ) . $member->group['suffix'] : htmlspecialchars( $member->name, ENT_DISALLOWED | ENT_QUOTES, 'UTF-8', FALSE ),
				'extra'	=> 	\IPS\Dispatcher::i()->controllerLocation == 'admin' ? htmlspecialchars( $member->email, ENT_DISALLOWED | ENT_QUOTES, 'UTF-8', FALSE ) : $member->groupName,
				'photo'	=> 	(string) $member->photo,
			);
		}
				
		\IPS\Output::i()->json( $results );
	}

	/**
	 * Returns size and download count of an array of attachments
	 *
	 * @return	void
	 */
	public function attachmentInfo()
	{
		$toReturn = array();
		$member = \IPS\Member::loggedIn();
		$loadedExtensions = array();

		/* Get attachments */
		$attachments = \IPS\Db::i()->select( '*', 'core_attachments', array( \IPS\Db::i()->in( 'attach_id', \array_keys( \IPS\Request::i()->attachIDs ) ) ) );

		foreach( $attachments as $attachment )
		{
			$permission = FALSE;			

			if( $member->member_id )
			{
				if ( $member->member_id == $attachment['attach_member_id'] )
				{
					$permission	= TRUE;
				}
			}

			if( $permission !== TRUE )
			{
				foreach ( \IPS\Db::i()->select( '*', 'core_attachments_map', array( 'attachment_id=?', $attachment['attach_id'] ) ) as $map )
				{
					if ( !isset( $loadedExtensions[ $map['location_key'] ] ) )
					{
						$exploded = explode( '_', $map['location_key'] );
						try
						{
							$extensions = \IPS\Application::load( $exploded[0] )->extensions( 'core', 'EditorLocations' );
							if ( isset( $extensions[ $exploded[1] ] ) )
							{
								$loadedExtensions[ $map['location_key'] ] = $extensions[ $exploded[1] ];
							}
						}
						catch ( \OutOfRangeException $e ) { }
					}

					if ( isset( $loadedExtensions[ $map['location_key'] ] ) )
					{
						try
						{
							if ( $loadedExtensions[ $map['location_key'] ]->attachmentPermissionCheck( $member, $map['id1'], $map['id2'], $map['id3'], $attachment ) )
							{
								$permission = TRUE;
								break;
							}
						}
						catch ( \OutOfRangeException $e ) { }
					}
				}
			}

			/* Permission check */
			if ( $permission )
			{
				$toReturn[ $attachment['attach_id'] ] = array(
					'size' => \IPS\Output\Plugin\Filesize::humanReadableFilesize( $attachment['attach_filesize'], FALSE, TRUE ),
					'downloads' => \IPS\Member::loggedIn()->language()->formatNumber( $attachment['attach_hits'] )
				);
			}
		}

		\IPS\Output::i()->json( $toReturn );
	}
	
	/**
	 * Returns boolean in json indicating whether the supplied username already exists
	 *
	 * @return	void
	 */
	public function usernameExists()
	{
		$result = array( 'result' => 'ok' );
		
		/* The value comes urlencoded so we need to decode so length is correct (and not using a percent-encoded value) */
		$name = urldecode( \IPS\Request::i()->input );
		
		/* Check is valid */
		if ( !$name )
		{
			$result = array( 'result' => 'fail', 'message' => \IPS\Member::loggedIn()->language()->addToStack('form_required') );
		}
		elseif ( mb_strlen( $name ) < \IPS\Settings::i()->min_user_name_length )
		{
			$result = array( 'result' => 'fail', 'message' => \IPS\Member::loggedIn()->language()->addToStack( 'form_minlength', FALSE, array( 'pluralize' => array( \IPS\Settings::i()->min_user_name_length ) ) ) );
		}
		elseif ( mb_strlen( $name ) > \IPS\Settings::i()->max_user_name_length )
		{
			$result = array( 'result' => 'fail', 'message' => \IPS\Member::loggedIn()->language()->addToStack( 'form_maxlength', FALSE, array( 'pluralize' => array( \IPS\Settings::i()->max_user_name_length ) ) ) );
		}
		elseif ( \IPS\Settings::i()->username_characters and !preg_match( '/^[' . str_replace( '\-', '-', preg_quote( \IPS\Settings::i()->username_characters, '/' ) ) . ']*$/iu', $name ) )
		{
			$result = array( 'result' => 'fail', 'message' => \IPS\Member::loggedIn()->language()->addToStack('form_bad_value') );
		}

		/* Check if it exists */
		else if ( $error = \IPS\Login::usernameIsInUse( $name ) )
		{
			if ( \IPS\Member::loggedIn()->isAdmin() )
			{
				$result = array( 'result' => 'fail', 'message' => $error );
			}
			else
			{
				$result = array( 'result' => 'fail', 'message' => \IPS\Member::loggedIn()->language()->addToStack('member_name_exists') );
			}
		}
		
		/* Check it's not banned */
		if ( $result == array( 'result' => 'ok' ) )
		{
			foreach( \IPS\Db::i()->select( 'ban_content', 'core_banfilters', array("ban_type=?", 'name') ) as $bannedName )
			{
				if( preg_match( '/^' . str_replace( '\*', '.*', preg_quote( $bannedName, '/' ) ) . '$/i', $name ) )
				{
					$result = array( 'result' => 'fail', 'message' => \IPS\Member::loggedIn()->language()->addToStack('form_name_banned') );
					break;
				}
			}
		}

		\IPS\Output::i()->json( $result );	
	}

	/**
	 * Returns boolean in json indicating whether the supplied email already exists
	 *
	 * @return	void
	 */
	public function emailExists()
	{
		$result = array( 'result' => 'ok' );
		
		/* The value comes urlencoded so we need to decode so length is correct (and not using a percent-encoded value) */
		$email = urldecode( \IPS\Request::i()->input );
		
		/* Check is valid */
		if ( !$email )
		{
			$result = array( 'result' => 'fail', 'message' => \IPS\Member::loggedIn()->language()->addToStack('form_required') );
		}
		elseif ( filter_var( $email, FILTER_VALIDATE_EMAIL ) === FALSE )
		{
			$result = array( 'result' => 'fail', 'message' => \IPS\Member::loggedIn()->language()->addToStack('form_bad_value') );
		}

		/* Check if it exists */
		else if ( $error = \IPS\Login::emailIsInUse( $email ) )
		{
			if ( \IPS\Member::loggedIn()->isAdmin() )
			{
				$result = array( 'result' => 'fail', 'message' => $error );
			}
			else
			{
				$result = array( 'result' => 'fail', 'message' => \IPS\Member::loggedIn()->language()->addToStack('member_email_exists') );
			}
		}

		/* Check it's not banned */
		if ( $result == array( 'result' => 'ok' ) )
		{
			foreach( \IPS\Db::i()->select( 'ban_content', 'core_banfilters', array("ban_type=?", 'email') ) as $bannedEmail )
			{
				if( preg_match( '/^' . str_replace( '\*', '.*', preg_quote( $bannedEmail, '/' ) ) . '$/i', $email ) )
				{
					$result = array( 'result' => 'fail', 'message' => \IPS\Member::loggedIn()->language()->addToStack('form_email_banned') );
					break;
				}
			}
		}

		/* Check it's an allowed domain */
		if ( \IPS\Settings::i()->allowed_reg_email !== '' AND $allowedEmailDomains = explode( ',', \IPS\Settings::i()->allowed_reg_email )  )
		{
			$matched = FALSE;
			foreach ( $allowedEmailDomains AS $domain )
			{
				if( \mb_stripos( $email, "@" . $domain ) !== FALSE )
				{
					$matched = TRUE;
					break;
				}
			}

			if ( \count( $allowedEmailDomains ) AND !$matched )
			{
				$result = array( 'result' => 'fail', 'message' => $email );
			}
		}


		\IPS\Output::i()->json( $result );	
	}

	/**
	 * Get state/region list for country
	 *
	 * @return	void
	 */
	public function states()
	{
		$states = array();
		if ( array_key_exists( \IPS\Request::i()->country, \IPS\GeoLocation::$states ) )
		{
			$states = \IPS\GeoLocation::$states[ \IPS\Request::i()->country ];
		}
		
		\IPS\Output::i()->json( $states );
	}
	
	/**
	 * Top Contributors
	 *
	 * @return	void
	 */
	public function topContributors()
	{
		/* How many? */
		$limit = \intval( ( isset( \IPS\Request::i()->limit ) and \IPS\Request::i()->limit <= 25 ) ? \IPS\Request::i()->limit : 5 );
		
		/* What timeframe? */
		$where = array( array( 'member_received > 0' ) );
		$timeframe = 'all';
		if ( isset( \IPS\Request::i()->time ) and \IPS\Request::i()->time != 'all' )
		{
			switch ( \IPS\Request::i()->time )
			{
				case 'week':
					$where[] = array( 'rep_date>' . \IPS\DateTime::create()->sub( new \DateInterval( 'P1W' ) )->getTimestamp() );
					$timeframe = 'week';
					break;
				case 'month':
					$where[] = array( 'rep_date>' . \IPS\DateTime::create()->sub( new \DateInterval( 'P1M' ) )->getTimestamp() );
					$timeframe = 'month';
					break;
				case 'year':
					$where[] = array( 'rep_date>' . \IPS\DateTime::create()->sub( new \DateInterval( 'P1Y' ) )->getTimestamp() );
					$timeframe = 'year';
					break;
			}

			$innerQuery = \IPS\Db::i()->select( 'core_reputation_index.member_received as themember, SUM(rep_rating) as rep', 'core_reputation_index', $where, NULL, NULL, 'themember' );
            $topContributors = iterator_to_array( \IPS\Db::i()->select( 'themember, rep', array( $innerQuery, 'in' ), NULL, 'rep DESC', $limit )->setKeyField('themember')->setValueField('rep') );
        }
        else
        {
            $topContributors = iterator_to_array( \IPS\Db::i()->select( 'member_id as themember, pp_reputation_points as rep', 'core_members', array( 'pp_reputation_points > 0' ), 'rep DESC', $limit )->setKeyField('themember')->setValueField('rep') );
        }

		/* Load their data */	
		foreach ( \IPS\Db::i()->select( '*', 'core_members', \IPS\Db::i()->in( 'member_id', array_keys( $topContributors ) ) ) as $member )
		{
			\IPS\Member::constructFromData( $member );
		}
		
		/* Render */
		$output = \IPS\Theme::i()->getTemplate( 'widgets' )->topContributorRows( $topContributors, $timeframe, \IPS\Request::i()->orientation );
		if ( \IPS\Request::i()->isAjax() )
		{
			\IPS\Output::i()->sendOutput( $output );
		}
		else
		{
			\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'block_topContributors' );
			\IPS\Output::i()->output = $output;
		}
	}
	
	/**
	 * Menu Preview
	 *
	 * @return	void
	 */
	public function menuPreview()
	{
		if ( isset( \IPS\Request::i()->theme ) )
		{
			\IPS\Theme::switchTheme( \IPS\Request::i()->theme, FALSE );
		}
		
		$preview = \IPS\Theme::i()->getTemplate( 'global', 'core', 'front' )->navBar( TRUE );
		\IPS\Output::i()->cssFiles = array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( 'system/menumanager.css', 'core', 'admin' ) );
		\IPS\Output::i()->sendOutput( \IPS\Theme::i()->getTemplate( 'applications', 'core', 'admin' )->menuPreviewWrapper( $preview ) );
	}
	
	/**
	 * Instant Notifications
	 *
	 * @return	void
	 */
	public function instantNotifications()
	{
		/* If auto-polling isn't enabled, kill the polling now */
		if ( !\IPS\Settings::i()->auto_polling_enabled )
		{
			\IPS\Output::i()->json( array( 'error' => 'auto_polling_disabled' ) );
			return;
		}

		/* Get the initial counts */
		$return = array( 'notifications' => array( 'count' => \IPS\Member::loggedIn()->notification_cnt, 'data' => array() ), 'messages' => array( 'count' => \IPS\Member::loggedIn()->msg_count_new, 'data' => array() ) );
		
		/* If there's new notifications, get the actual data */
		if ( \IPS\Request::i()->notifications < $return['notifications']['count'] )
		{
			$notificationsDifference = $return['notifications']['count'] - \IPS\Request::i()->notifications;

			/* Cap at 200 to prevent DOSing the server when there are like 1000+ notifications to send */
			if( $notificationsDifference > 200 )
			{
				$notificationsDifference = 200;
			}

			foreach ( new \IPS\Patterns\ActiveRecordIterator( \IPS\Db::i()->select( '*', 'core_notifications', array( '`member`=? AND ( read_time IS NULL OR read_time<? )', \IPS\Member::loggedIn()->member_id, time() ), 'updated_time DESC', $notificationsDifference ), 'IPS\Notification\Inline' ) as $notification )
			{
				/* It is possible that the content has been removed after the iterator has started but before we fetch the data */
				try
				{
					$data = $notification->getData();
				}
				catch( \OutOfRangeException $e )
				{
					continue;
				}
								
				$return['notifications']['data'][] = array(
					'id'			=> $notification->id,
					'title'			=> htmlspecialchars( $data['title'], ENT_DISALLOWED | ENT_QUOTES, 'UTF-8', FALSE ),
					'url'			=> (string) $data['url'],
					'content'		=> isset( $data['content'] ) ? htmlspecialchars( $data['content'], ENT_DISALLOWED, 'UTF-8', FALSE ) : NULL,
					'date'			=> $notification->updated_time->getTimestamp(),
					'author_photo'	=> $data['author'] ? $data['author']->photo : NULL
				);
			}
		}
		
		/* If there's new messages, get the actual data */
		if ( !\IPS\Member::loggedIn()->members_disable_pm and \IPS\Member::loggedIn()->canAccessModule( \IPS\Application\Module::get( 'core', 'messaging' ) ) )
		{
			if ( \IPS\Request::i()->messages < $return['messages']['count'] )
			{
				$messagesDifference = $return['messages']['count'] - (int) \IPS\Request::i()->messages;

				foreach ( \IPS\Db::i()->select( 'map_topic_id', 'core_message_topic_user_map', array( 'map_user_id=? AND map_user_active=1 AND map_has_unread=1 AND map_ignore_notification=0', \IPS\Member::loggedIn()->member_id ), 'map_last_topic_reply DESC', $messagesDifference ) as $conversationId )
				{
					$conversation	= \IPS\core\Messenger\Conversation::load( $conversationId );
					$message		= $conversation->comments( 1, 0, 'date', 'desc' );

					if( $message )
					{
						$return['messages']['data'][] = array(
							'id'			=> $conversation->id,
							'title'			=> htmlspecialchars( $conversation->title, ENT_DISALLOWED | ENT_QUOTES, 'UTF-8', FALSE ),
							'url'			=> (string) $conversation->url()->setQueryString( 'latest', 1 ),
							'message'		=> $message->truncated(),
							'date'			=> $message->mapped('date'),
							'author_photo'	=> (string) $message->author()->photo
						);
					}
					else
					{
						\IPS\Log::log( "Private conversation {$conversation->id} titled {$conversation->title} has no messages", 'orphaned_data' );
					}
				}
			}
		}
		
		/* And return */
		\IPS\Output::i()->json( $return );
	}

	/**
	 * Returns score in json indicating the strength of a password
	 *
	 * @return	void
	 */
	public function passwordStrength()
	{
		/* The value comes urlencoded so we need to decode so length is correct (and not using a percent-encoded value) */
		$password = urldecode( \IPS\Request::i()->input );

		require_once \IPS\ROOT_PATH . "/system/3rd_party/phpass/phpass.php";

		$phpass = new \PasswordStrength();

		$response = array( 'result' => 'ok', 'score' => $phpass->classify( $password ), 'granular' => $phpass->calculate( $password ) );

		\IPS\Output::i()->json( $response );
	}
	
	/**
	 * Show information about chart timezones
	 *
	 * @return	void
	 */
	public function chartTimezones()
	{
		$mysqlTimezone = \IPS\Db::i()->query( "SELECT TIMEDIFF( NOW(), CONVERT_TZ( NOW(), @@session.time_zone, '+00:00' ) );" )->fetch_row()[0];
		if ( preg_match( '/^(-?)(\d{2}):00:00/', $mysqlTimezone, $matches ) )
		{
			$mysqlTimezone = "GMT" . ( ( $matches[2] == 0 ) ? '' : ( ( $matches[1] ?: '+' ) . \intval( $matches[2] ) ) );
		}
		
		\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'global', 'core', 'global' )->chartTimezoneInfo( $mysqlTimezone );
	}
	
	/**
	 * Dismiss ACP Notification
	 *
	 * @return	void
	 */
	public function dismissAcpNotification()
	{
		\IPS\Session::i()->csrfCheck();
		
		if ( \IPS\Member::loggedIn()->isAdmin() )
		{
			\IPS\core\AdminNotification::dismissNotification( \IPS\Request::i()->id );
		}
		
		\IPS\Output::i()->json( array( 'status' => 'OK' ) );
	}

	/**
	 * Find suggested tags
	 *
	 * @return	void
	 */
	public function findTags()
	{
		$results = array();
		
		$input = mb_strtolower( \IPS\Request::i()->input );

		/* First, get the admin-defined tags */
		$definedTags = array();

		if( isset( \IPS\Request::i()->class ) )
		{
			$class = \IPS\Request::i()->class;
			$containerClass = $class::$containerNodeClass;

			try
			{
				$container = $containerClass::load( \IPS\Request::i()->container );
			}
			catch( \OutOfRangeException $e )
			{
				$container = NULL;
			}

			if( $definedTags = $class::definedTags( $container ) )
			{
				foreach( $definedTags as $tag )
				{
					/* Only include tags that match the input term */
					if( mb_stripos( $tag, $input ) !== FALSE )
					{
						$results[] = array(
							'value' 		=> $tag,
							'html'			=> $tag,
							'recommended'	=> true
						);
					}
				}
			}
		}
		
		/* Then look for used tags */
		$where = array(
			array( "tag_text LIKE CONCAT(?, '%')", $input ),
			array( 'tag_perm_visible=?', 1 ),
			array( '(' . \IPS\Db::i()->findInSet( 'tag_perm_text', \IPS\Member::loggedIn()->groups ) . ' OR ' . 'tag_perm_text=? )', '*' ),
		);
	
		foreach ( \IPS\Db::i()->select( 'tag_text', 'core_tags', $where, 'LENGTH(tag_text) ASC', array( 0, 20 ), 'tag_text' )->join( 'core_tags_perms', array( 'tag_perm_aai_lookup=tag_aai_lookup' ) ) as $tag )
		{
			if( !\in_array( $tag, $definedTags ) )
			{
				$results[] = array(
					'value'			=> $tag,
					'html'			=> $tag,
					'recommended'	=> false
				);
			}
		}
				
		\IPS\Output::i()->json( $results );
	}
}