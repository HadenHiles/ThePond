<?php
/**
 * @brief		Daily Cleanup Task
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		27 Aug 2013
 */

namespace IPS\core\tasks;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Daily Cleanup Task
 */
class _cleanup extends \IPS\Task
{
	/**
	 * Execute
	 *
	 * @return	mixed	Message to log or NULL
	 * @throws	\RuntimeException
	 */
	public function execute()
	{
		/* Delete old password / security answer reset requests */
		\IPS\Db::i()->delete( 'core_validating', array( '( lost_pass=1 OR forgot_security=1 ) AND email_sent < ?', \IPS\DateTime::create()->sub( new \DateInterval( 'P1D' ) )->getTimestamp() ) );
		
				
		/* Delete old validating members */
		if ( \IPS\Settings::i()->validate_day_prune )
		{
			$select = \IPS\Db::i()->select( 'core_validating.member_id, core_members.member_posts', 'core_validating', array( 'core_validating.new_reg=1 AND core_validating.coppa_user<>1 AND core_validating.entry_date<? AND core_validating.lost_pass<>1 AND core_validating.user_verified=0 AND core_members.member_posts < 1 AND core_validating.do_not_delete=0', \IPS\DateTime::create()->sub( new \DateInterval( 'P' . \IPS\Settings::i()->validate_day_prune . 'D' ) )->getTimestamp() ) )->join( 'core_members', 'core_members.member_id=core_validating.member_id' );

			foreach ( $select as $row )
			{
				$member = \IPS\Member::load( $row['member_id'] );

				if( $member->member_id )
				{
					$member->delete();
				}
				else
				{
					\IPS\Db::i()->delete( 'core_validating', array( 'member_id=?', $row['member_id'] ) );
				}
			}
		}

		/* Delete file system logs */
		if( \IPS\Settings::i()->file_log_pruning )
		{
			\IPS\Db::i()->delete( 'core_file_logs', array( 'log_date < ?', \IPS\DateTime::create()->sub( new \DateInterval( 'P' . \IPS\Settings::i()->file_log_pruning . 'D' ) )->getTimestamp() ) );
		}

		/* Delete edit history past prune date */
		if( \IPS\Settings::i()->edit_log_prune > 0 )
		{
			\IPS\Db::i()->delete( 'core_edit_history', array( 'time < ?', \IPS\DateTime::create()->sub( new \DateInterval( 'P' . \IPS\Settings::i()->edit_log_prune . 'D' ) )->getTimestamp() ) );
		}

		/* Delete task logs older than the prune-since date */
		if( \IPS\Settings::i()->prune_log_tasks )
		{
			\IPS\Db::i()->delete( 'core_tasks_log', array( 'time < ?', \IPS\DateTime::create()->sub( new \DateInterval( 'P' . \IPS\Settings::i()->prune_log_tasks . 'D' ) )->getTimestamp() ) );
		}

		/* Delete email error logs older than the prune-since date */
		if( \IPS\Settings::i()->prune_log_email_error )
		{
			\IPS\Db::i()->delete( 'core_mail_error_logs', array( 'mlog_date < ?', \IPS\DateTime::create()->sub( new \DateInterval( 'P' . \IPS\Settings::i()->prune_log_email_error . 'D' ) )->getTimestamp() ) );

			/* If we don't have any logs left, remove any notifications */
			if( \IPS\Db::i()->select( 'count(mlog_id)', 'core_mail_error_logs' )->first() === 0 )
			{
				\IPS\core\AdminNotification::remove( 'core', 'ConfigurationError', 'failedMail' );
			}
		}
		
		/* ...and admin logs */
		if( \IPS\Settings::i()->prune_log_admin )
		{
			\IPS\Db::i()->delete( 'core_admin_logs', array( 'ctime < ?', \IPS\DateTime::create()->sub( new \DateInterval( 'P' . \IPS\Settings::i()->prune_log_admin . 'D' ) )->getTimestamp() ) );
		}

		/* ...and moderators logs */
		if( \IPS\Settings::i()->prune_log_moderator )
		{
			\IPS\Db::i()->delete( 'core_moderator_logs', array( 'ctime < ?', \IPS\DateTime::create()->sub( new \DateInterval( 'P' . \IPS\Settings::i()->prune_log_moderator . 'D' ) )->getTimestamp() ) );
		}
		
		/* ...and error logs */
		if( \IPS\Settings::i()->prune_log_error )
		{
			\IPS\Db::i()->delete( 'core_error_logs', array( 'log_date < ?', \IPS\DateTime::create()->sub( new \DateInterval( 'P' . \IPS\Settings::i()->prune_log_error . 'D' ) )->getTimestamp() ) );

			/* If we don't have any logs left, remove any notifications */
			if( \IPS\Db::i()->select( 'count(log_id)', 'core_error_logs' )->first() === 0 )
			{
				\IPS\core\AdminNotification::remove( 'core', 'Error' );
			}
		}
		
		/* ...and spam service logs */
		if( \IPS\Settings::i()->prune_log_spam )
		{
			\IPS\Db::i()->delete( 'core_spam_service_log', array( 'log_date < ?', \IPS\DateTime::create()->sub( new \DateInterval( 'P' . \IPS\Settings::i()->prune_log_spam . 'D' ) )->getTimestamp() ) );
		}
		
		/* ...and admin login logs */
		if( \IPS\Settings::i()->prune_log_adminlogin )
		{
			\IPS\Db::i()->delete( 'core_admin_login_logs', array( 'admin_time < ?', \IPS\DateTime::create()->sub( new \DateInterval( 'P' . \IPS\Settings::i()->prune_log_adminlogin . 'D' ) )->getTimestamp() ) );
		}

		/* ...and statistics */
		if( \IPS\Settings::i()->stats_online_users_prune )
		{
			\IPS\Db::i()->delete( 'core_statistics', array( 'type=? AND time < ?', 'online_users', \IPS\DateTime::create()->sub( new \DateInterval( 'P' . \IPS\Settings::i()->stats_online_users_prune . 'D' ) )->getTimestamp() ) );
		}

		if( \IPS\Settings::i()->stats_keywords_prune )
		{
			\IPS\Db::i()->delete( 'core_statistics', array( 'type=? AND time < ?', 'keyword', \IPS\DateTime::create()->sub( new \DateInterval( 'P' . \IPS\Settings::i()->stats_keywords_prune . 'D' ) )->getTimestamp() ) );
		}

		if( \IPS\Settings::i()->prune_log_emailstats > 0 )
		{
			\IPS\Db::i()->delete( 'core_statistics', array( "type IN('emails_sent','email_views','email_clicks') AND time < ?", \IPS\DateTime::create()->sub( new \DateInterval( 'P' . \IPS\Settings::i()->prune_log_emailstats . 'D' ) )->getTimestamp() ) );
		}
		
		/* ...and geoip cache */
		\IPS\Db::i()->delete( 'core_geoip_cache', array( 'date < ?', \IPS\DateTime::create()->sub( new \DateInterval( 'PT12H' ) )->getTimestamp() ) );

		/* ...and API logs */
		if( \IPS\Settings::i()->api_log_prune )
		{
			\IPS\Db::i()->delete( 'core_api_logs', array( 'date < ?', \IPS\DateTime::create()->sub( new \DateInterval( 'P' . \IPS\Settings::i()->api_log_prune . 'D' ) )->getTimestamp() ) );
		}

		/* ...and member history */
		if( \IPS\Settings::i()->prune_member_history )
		{
			\IPS\Db::i()->delete( 'core_member_history', array( 'log_date < ?', \IPS\DateTime::create()->sub( new \DateInterval( 'P' . \IPS\Settings::i()->prune_member_history . 'D' ) )->getTimestamp(), 0 ) );
		}
		
		/* ...and promote response logs */
		\IPS\Db::i()->delete( 'core_social_promote_content', array( 'response_date < ?', \IPS\DateTime::create()->sub( new \DateInterval( 'P6M' ) )->getTimestamp() ) );
		
		/* Delete old notifications */
		if ( \IPS\Settings::i()->prune_notifications )
		{
			$memberIds	= array();

			foreach( \IPS\Db::i()->select( '`member`', 'core_notifications', array( 'sent_time < ?', \IPS\DateTime::create()->sub( new \DateInterval( 'P' . \IPS\Settings::i()->prune_notifications . 'D' ) )->getTimestamp() ) ) as $member )
			{
				$memberIds[ $member ]	= $member;
			}

			\IPS\Db::i()->delete( 'core_notifications', array( 'sent_time < ?', \IPS\DateTime::create()->sub( new \DateInterval( 'P' . \IPS\Settings::i()->prune_notifications . 'D' ) )->getTimestamp() ) );

			foreach( $memberIds as $member )
			{
				\IPS\Member::load( $member )->recountNotifications();
			}
		}
		
		/* Cleanup followers */
		if ( \IPS\Settings::i()->subs_autoprune )
		{
			$followIds = array();
			foreach( \IPS\Content::routedClasses( FALSE, FALSE, TRUE ) AS $class )
			{
				if ( \in_array( 'IPS\Content\Followable', class_implements( $class ) ) AND ( isset( $class::$databaseColumnMap['last_comment'] ) OR isset( $class::$databaseColumnMap['last_review'] ) ) )
				{
					if ( isset( $class::$application ) ) # Sanity check
					{
						$application = $class::$application;
					}
					else
					{
						$exploded = explode( '\\', $class );
						$application = mb_strtolower( $exploded[1] );
					}

					$area = mb_strtolower( mb_substr( $class, mb_strrpos( $class, '\\' ) + 1 ) );

					$where = array();
					$where[] = array( 'core_follow.follow_app=? AND core_follow.follow_area=?', $application, $area );

					if ( isset( $class::$databaseColumnMap['last_comment'] ) )
					{
						$lastPostColumn = $class::$databaseColumnMap['last_comment'];
						if ( \is_array( $lastPostColumn ) )
						{
							$lastPostColumn = $lastPostColumn[0];
						}
						$lastPostColumn = $class::$databasePrefix . $lastPostColumn;

						$where[] = array( $class::$databaseTable . '.' . $lastPostColumn . '<?', \IPS\DateTime::create()->sub( new \DateInterval( 'P' . \IPS\Settings::i()->subs_autoprune . 'D' ) )->getTimestamp() );
					}

					if ( isset( $class::$databaseColumnMap['last_review'] ) )
					{
						$lastReviewColumn = $class::$databaseColumnMap['last_review'];
						if ( \is_array( $lastReviewColumn ) )
						{
							$lastReviewColumn = $lastReviewColumn[0];
						}
						$lastReviewColumn = $class::$databasePrefix . $lastReviewColumn;

						$where[] = array( $class::$databaseTable . '.' . $lastReviewColumn . '<?', \IPS\DateTime::create()->sub( new \DateInterval( 'P' . \IPS\Settings::i()->subs_autoprune . 'D' ) )->getTimestamp() );
					}
					
					$select = \IPS\Db::i()->select( 'follow_id', 'core_follow', $where, NULL, array( 0, 1000 ) )
						->join( $class::$databaseTable, 'core_follow.follow_rel_id=' . $class::$databaseTable . '.' . $class::$databasePrefix . $class::$databaseColumnId );
					foreach( $select AS $follow )
					{
						if ( !\in_array( $follow, $followIds ) )
						{
							$followIds[] = $follow;
						}
					}
				}
			}
			
			if ( \count( $followIds ) )
			{
				\IPS\Db::i()->delete( 'core_follow', array( \IPS\Db::i()->in( 'follow_id', $followIds ) ) );
			}
		}
				
		/* Delete moved links */
		if ( \IPS\Settings::i()->topic_redirect_prune )
		{
			foreach ( \IPS\Content::routedClasses( FALSE, FALSE, TRUE ) as $class )
			{
				if ( isset( $class::$databaseColumnMap['moved_on'] ) )
				{
					foreach ( new \IPS\Patterns\ActiveRecordIterator( \IPS\Db::i()->select( '*', $class::$databaseTable, array( $class::$databasePrefix . $class::$databaseColumnMap['moved_on'] . '>0 AND ' . $class::$databasePrefix . $class::$databaseColumnMap['moved_on'] . '<?', \IPS\DateTime::create()->sub( new \DateInterval( 'P' . \IPS\Settings::i()->topic_redirect_prune . 'D' ) )->getTimestamp() ), $class::$databasePrefix . $class::$databaseColumnId, 100 ), $class ) as $item )
					{
						$item->delete();
					}
				}
			}
		}
		
		/* Remove warnings points */
		foreach ( new \IPS\Patterns\ActiveRecordIterator( \IPS\Db::i()->select( '*', 'core_members_warn_logs', array( 'wl_expire_date>0 AND wl_expire_date<?', time() ), 'wl_date ASC', 25 ), 'IPS\core\Warnings\Warning' ) as $warning )
		{
			$member = \IPS\Member::load( $warning->member );
			$member->warn_level -= $warning->points;
			$member->save();
			
			$warning->expire_date = 0;
			$warning->save();
		}
		
		/* Remove widgets */
		if ( \IPS\Application::appIsEnabled('cms') )
		{
			\IPS\cms\Widget::emptyTrash();
		}
		else
		{
			\IPS\Widget::emptyTrash();
		}
		
		/* Reset expired "moderate content till.." timestamps */
		\IPS\Db::i()->update( 'core_members', array( 'mod_posts' => 0 ), array( 'mod_posts != -1 and mod_posts <?', time() ) );


		/* Set expired announcements inactive */
		\IPS\Db::i()->update( 'core_announcements', array( 'announce_active' => 0 ), array( 'announce_active = 1 and announce_end > 0 and announce_end <?', time() ) );
		
		/* Delete old Google Authenticator code uses */
		\IPS\Db::i()->delete( 'core_googleauth_used_codes', array( 'time < ?', \IPS\DateTime::create()->sub( new \DateInterval( 'PT1M' ) )->getTimestamp() ) );

		/* Close open polls that need closing */
		\IPS\Db::i()->update( 'core_polls', array( 'poll_closed' => 1 ), array( 'poll_closed=? AND poll_close_date>? AND poll_close_date<?', 0, -1, time() ) );
		
		/* Delete expired oAuth Authorization Codes */
		\IPS\Db::i()->delete( 'core_oauth_server_authorization_codes', array( 'expires<?', time() ) );
		\IPS\Db::i()->delete( 'core_oauth_server_access_tokens', array( 'refresh_token_expires<?', time() ) );
		\IPS\Db::i()->delete( 'core_oauth_authorize_prompts', array( 'timestamp<?', ( time() - 300 ) ) );
		
		/* Delete any unfinished "Post before register" posts */
		if ( \IPS\Settings::i()->post_before_registering )
		{
			foreach ( \IPS\Db::i()->select( '*', 'core_post_before_registering', array( "`member` IS NULL AND followup IS NOT NULL AND followup<" . ( time() - ( 86400 * 6 ) ) ), 'followup ASC' ) as $row )
			{
				$class = $row['class'];
				try
				{
					$class::load( $row['id'] )->delete();
				}
				catch ( \OutOfRangeException $e ) { }
				
				\IPS\Db::i()->delete( 'core_post_before_registering', array( 'class=? AND id=?', $row['class'], $row['id'] ) );
			}
		}

		/* Truncate the output caches table */
		\IPS\Output\Cache::i()->deleteExpired( TRUE );

		return NULL;
	}
}