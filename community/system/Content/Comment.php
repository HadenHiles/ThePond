<?php
/**
 * @brief		Content Comment Model
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		8 Jul 2013
 */

namespace IPS\Content;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Content Comment Model
 */
abstract class _Comment extends \IPS\Content
{
	/**
	 * @brief	[Content\Comment]	Comment Template
	 */
	public static $commentTemplate = array( array( 'global', 'core', 'front' ), 'commentContainer' );
	
	/**
	 * @brief	[Content\Comment]	Form Template
	 */
	public static $formTemplate = array( array( 'forms', 'core', 'front' ), 'commentTemplate' );
	
	/**
	 * @brief	[Content\Comment]	The ignore type
	 */
	public static $ignoreType = 'topics';
	
	/**
	 * @brief	[Content\Item]	Sharelink HTML
	 */
	protected $sharelinks = array();
	
	/**
	 * Create comment
	 *
	 * @param	\IPS\Content\Item		$item				The content item just created
	 * @param	string					$comment			The comment
	 * @param	bool					$first				Is the first comment?
	 * @param	string					$guestName			If author is a guest, the name to use
	 * @param	bool|NULL				$incrementPostCount	Increment post count? If NULL, will use static::incrementPostCount()
	 * @param	\IPS\Member|NULL		$member				The author of this comment. If NULL, uses currently logged in member.
	 * @param	\IPS\DateTime|NULL		$time				The time
	 * @param	string|NULL				$ipAddress			The IP address or NULL to detect automatically
	 * @param	int|NULL				$hiddenStatus		NULL to set automatically or override: 0 = unhidden; 1 = hidden, pending moderator approval; -1 = hidden (as if hidden by a moderator)
	 * @return	static
	 */
	public static function create( $item, $comment, $first=FALSE, $guestName=NULL, $incrementPostCount=NULL, $member=NULL, \IPS\DateTime $time=NULL, $ipAddress=NULL, $hiddenStatus=NULL )
	{
		if ( $member === NULL )
		{
			$member = \IPS\Member::loggedIn();
		}

		/* Create the object */
		$obj = new static;
		foreach ( array( 'item', 'date', 'author', 'author_name', 'content', 'ip_address', 'first', 'approved', 'hidden' ) as $k )
		{
			if ( isset( static::$databaseColumnMap[ $k ] ) )
			{
				$val = NULL;
				switch ( $k )
				{
					case 'item':
						$idColumn = $item::$databaseColumnId;
						$val = $item->$idColumn;
						break;
					
					case 'date':
						$val = ( $time ) ? $time->getTimestamp() : time();
						break;
					
					case 'author':
						$val = (int) $member->member_id;
						break;
						
					case 'author_name':
						$val = ( $member->member_id ) ? $member->name : ( $guestName ?: '' );
						break;
						
					case 'content':
						$val = $comment;
						break;
						
					case 'ip_address':
						$val = $ipAddress ?: \IPS\Request::i()->ipAddress();
						break;
					
					case 'first':
						$val = $first;
						break;
						
					case 'approved':
						if ( $first ) // If this is the first post within an item, don't mark it hidden, otherwise the count of unapproved comments/items will include both the comment and the item when really only the item is hidden
						{
							$val = TRUE;
						}
						elseif ( $hiddenStatus === NULL )
						{
							$permissionCheckFunction = \in_array( 'IPS\Content\Review', class_parents( \get_called_class() ) ) ? 'canReview' : 'canComment';
							if ( !$member->member_id and !$item->$permissionCheckFunction( $member, FALSE ) )
							{
								$val = -3;
							}
							elseif ( \in_array( 'IPS\Content\Review', class_parents( \get_called_class() ) ) )
							{
								$val = $item->moderateNewReviews( $member ) ? 0 : 1;
							}
							else
							{
								$val = $item->moderateNewComments( $member ) ? 0 : 1;
							}
						}
						else
						{
							switch ( $hiddenStatus )
							{
								case 0:
									$val = 1;
									break;
								case 1:
									$val = 0;
									break;
								case -1:
									$val = -1;
									break;
							}
						}
						break;
					
					case 'hidden':
						if ( $first )
						{
							$val = FALSE; // If this is the first post within an item, don't mark it hidden, otherwise the count of unapproved comments/items will include both the comment and the item when really only the item is hidden
						}
						elseif ( $item->approvedButHidden() )
						{
							$val = 2;
						}
						elseif ( $hiddenStatus === NULL )
						{
							$permissionCheckFunction = \in_array( 'IPS\Content\Review', class_parents( \get_called_class() ) ) ? 'canReview' : 'canComment';
							if ( !$member->member_id and !$item->$permissionCheckFunction( $member, FALSE ) )
							{
								$val = -3;
							}
							elseif ( \in_array( 'IPS\Content\Review', class_parents( \get_called_class() ) ) )
							{
								$val = $item->moderateNewReviews( $member ) ? 1 : 0;
							}
							else
							{
								$val = $item->moderateNewComments( $member ) ? 1 : 0;
							}
						}
						else
						{
							$val = $hiddenStatus;
						}
						break;
				}
				
				foreach ( \is_array( static::$databaseColumnMap[ $k ] ) ? static::$databaseColumnMap[ $k ] : array( static::$databaseColumnMap[ $k ] ) as $column )
				{
					$obj->$column = $val;
				}
			}
		}

		/* Check if profanity filters should mod-queue this comment */
		$obj->checkProfanityFilters( $first );

		/* Save the comment */
		$obj->save();
		
		/* Increment post count */
		try
		{
			if ( !$obj->hidden() and ( $incrementPostCount === TRUE or ( $incrementPostCount === NULL and static::incrementPostCount( $item->container() ) ) ) )
			{
				$obj->author()->member_posts++;
			}
		}
		catch( \BadMethodCallException $e ) { }
		

		/* Update member's last post and daily post limits */
		if( $obj->author()->member_id )
		{
			$obj->author()->member_last_post = time();
			
			/* Update posts per day limits */
			if ( $obj->author()->group['g_ppd_limit'] )
			{
				$current = $obj->author()->members_day_posts;
				
				$current[0] += 1;
				if ( $current[1] == 0 )
				{
					$current[1] = \IPS\DateTime::create()->getTimestamp();
				}
				
				$obj->author()->members_day_posts = $current;
			}
			
			$obj->author()->save();
		}
		
		/* Send notifications */
		if ( !\in_array( 'IPS\Content\Review', class_parents( \get_called_class() ) ) )
		{
			if ( !$obj->hidden() and ( !$first or !$item::$firstCommentRequired ) )
			{
				$obj->sendNotifications();
			}
			else if( $obj->hidden() === 1 )
			{
				$obj->sendUnapprovedNotification();
			}
		}
		
		/* Update item */
		$obj->postCreate();

		/* Add to search index */
		if ( $obj instanceof \IPS\Content\Searchable )
		{
			\IPS\Content\Search\Index::i()->index( $obj );
		}

		/* Return */
		return $obj;
	}
	
	/**
	 * Join profile fields when loading comments?
	 */
	public static $joinProfileFields = FALSE;
	
	/**
	 * Joins (when loading comments)
	 *
	 * @param	\IPS\Content\Item	$item			The item
	 * @return	array
	 */
	public static function joins( \IPS\Content\Item $item )
	{
		$return = array();
		
		/* Author */
		$authorColumn = static::$databasePrefix . static::$databaseColumnMap['author'];
		$return['author'] = array(
			'select'	=> 'author.*',
			'from'		=> array( 'core_members', 'author' ),
			'where'		=> array( 'author.member_id = ' . static::$databaseTable . '.' . $authorColumn )
		);
		
		/* Author profile fields */
		if ( static::$joinProfileFields and \IPS\core\ProfileFields\Field::fieldsForContentView() )
		{
			$return['author_pfields'] = array(
				'select'	=> 'author_pfields.*',
				'from'		=> array( 'core_pfields_content', 'author_pfields' ),
				'where'		=> array( 'author_pfields.member_id=author.member_id' )
			);
		}
				
		return $return;
	}
	
	/**
	 * Do stuff after creating (abstracted as comments and reviews need to do different things)
	 *
	 * @return	void
	 */
	public function postCreate()
	{
		$item = $this->item();
		
		$item->resyncCommentCounts();
			
		if( isset( static::$databaseColumnMap['date'] ) )
		{
			if( \is_array( static::$databaseColumnMap['date'] ) )
			{
				$postDateColumn = static::$databaseColumnMap['date'][0];
			}
			else
			{
				$postDateColumn = static::$databaseColumnMap['date'];
			}
		}

		if ( !$this->hidden() or $item->approvedButHidden() )
		{
			if ( isset( $item::$databaseColumnMap['last_comment'] ) )
			{
				$lastCommentField = $item::$databaseColumnMap['last_comment'];
				if ( \is_array( $lastCommentField ) )
				{
					foreach ( $lastCommentField as $column )
					{
						$item->$column = ( isset( $postDateColumn ) ) ? $this->$postDateColumn : time();
					}
				}
				else
				{
					$item->$lastCommentField = ( isset( $postDateColumn ) ) ? $this->$postDateColumn : time();
				}
			}
			if ( isset( $item::$databaseColumnMap['last_comment_by'] ) )
			{
				$lastCommentByField = $item::$databaseColumnMap['last_comment_by'];
				$item->$lastCommentByField = (int) $this->author()->member_id;
			}
			if ( isset( $item::$databaseColumnMap['last_comment_name'] ) )
			{
				$lastCommentNameField = $item::$databaseColumnMap['last_comment_name'];
				$item->$lastCommentNameField = $this->mapped('author_name');
			}
			
			$item->save();
			
			if ( !$item->hidden() and ! $item->approvedButHidden() and $item->containerWrapper() and $item->container()->_comments !== NULL )
			{
				$item->container()->_comments = ( $item->container()->_comments + 1 );
				$item->container()->setLastComment( $this );
				$item->container()->save();
			}
		}
		else
		{
			$item->save();

			if ( $item->containerWrapper() AND !$item->approvedButHidden() AND $this->hidden() == 1 AND $item->container()->_unapprovedComments !== NULL )
			{
				$item->container()->_unapprovedComments = $item->container()->_unapprovedComments + 1;
				$item->container()->save();
			}
		}

		/* Are we tracking keywords? */
		$this->checkKeywords( $this->content() );
	}

	/**
	 * @brief	Value to set for the 'tab' parameter when redirecting to the comment (via _find())
	 */
	public static $tabParameter	= array( 'tab' => 'comments' );

	/**
	 * Get URL
	 *
	 * @param	string|NULL		$action		Action
	 * @return	\IPS\Http\Url
	 */
	public function url( $action='find' )
	{
		$idColumn = static::$databaseColumnId;
		
		return $this->item()->url()->setQueryString( array(
			'do'		=> $action . 'Comment',
			'comment'	=> $this->$idColumn
		) );
	}

	/**
	 * Get containing item
	 *
	 * @return	\IPS\Content\Item
	 */
	public function item()
	{
		$itemClass = static::$itemClass;
		return $itemClass::load( $this->mapped( 'item' ) );
	}
	
	/**
	 * Is first message?
	 *
	 * @return	bool
	 */
	public function isFirst()
	{
		if ( isset( static::$databaseColumnMap['first'] ) )
		{
			if ( $this->mapped('first') )
			{
				return TRUE;
			}
		}
		return FALSE;
	}
	
	/**
	 * Get permission index ID
	 *
	 * @return	int|NULL
	 */
	public function permId()
	{
		return $this->item()->permId();
	}
	
	/**
	 * Can view?
	 *
	 * @param	\IPS\Member|NULL	$member	The member to check for or NULL for the currently logged in member
	 * @return	bool
	 */
	public function canView( $member=NULL )
	{
		if( $member === NULL )
		{
			$member	= \IPS\Member::loggedIn();
		}
				
		if ( $this instanceof \IPS\Content\Hideable and $this->hidden() and !$this->item()->canViewHiddenComments( $member ) and ( $this->hidden() !== 1 or $this->author() !== $member ) )
		{
			return FALSE;
		}

		return $this->item()->canView( $member );
	}
	
	/**
	 * Can edit?
	 *
	 * @param	\IPS\Member|NULL	$member	The member to check for (NULL for currently logged in member)
	 * @return	bool
	 */
	public function canEdit( $member=NULL )
	{
		$member = $member ?: \IPS\Member::loggedIn();

		/* Are we restricted from posting or have an unacknowledged warning? */
		if ( $member->restrict_post or ( $member->members_bitoptions['unacknowledged_warnings'] and \IPS\Settings::i()->warn_on and \IPS\Settings::i()->warnings_acknowledge ) )
		{
			return FALSE;
		}

		if ( $member->member_id )
		{
			$item = $this->item();
			
			/* Do we have moderator permission to edit stuff in the container? */
			if ( static::modPermission( 'edit', $member, $item->containerWrapper() ) )
			{
				return TRUE;
			}
			
			/* Can the member edit their own content? */
			if ( $member->member_id == $this->author()->member_id and ( $member->group['g_edit_posts'] == '1' or \in_array( \get_class( $item ), explode( ',', $member->group['g_edit_posts'] ) ) ) and ( !( $item instanceof \IPS\Content\Lockable ) or !$item->locked() ) )
			{
				if ( !$member->group['g_edit_cutoff'] )
				{
					return TRUE;
				}
				else
				{
					if( \IPS\DateTime::ts( $this->mapped('date') )->add( new \DateInterval( "PT{$member->group['g_edit_cutoff']}M" ) ) > \IPS\DateTime::create() )
					{
						return TRUE;
					}
				}
			}
		}
		
		return FALSE;
	}
	
	/**
	 * Can hide?
	 *
	 * @param	\IPS\Member|NULL	$member	The member to check for (NULL for currently logged in member)
	 * @return	bool
	 */
	public function canHide( $member=NULL )
	{
		$member = $member ?: \IPS\Member::loggedIn();

		return ( !$this->isFirst() and ( static::modPermission( 'hide', $member, $this->item()->containerWrapper() ) or ( $member->member_id and $member->member_id == $this->author()->member_id and ( $member->group['g_hide_own_posts'] == '1' or \in_array( \get_class( $this->item() ), explode( ',', $member->group['g_hide_own_posts'] ) ) ) ) ) );
	}
	
	/**
	 * Can unhide?
	 *
	 * @param	\IPS\Member|NULL	$member	The member to check for (NULL for currently logged in member)
	 * @return  boolean
	 */
	public function canUnhide( $member=NULL )
	{
		$member = $member ?: \IPS\Member::loggedIn();

		$hiddenByItem = FALSE;
		if ( isset( static::$databaseColumnMap['hidden'] ) )
		{
			$column = static::$databaseColumnMap['hidden'];
			$hiddenByItem = (boolean) ( $this->$column === 2 );
		}

		return ( !$this->isFirst() and ! $hiddenByItem and static::modPermission( 'unhide', $member, $this->item()->containerWrapper() ) );
	}
	
	/**
	 * Can delete?
	 *
	 * @param	\IPS\Member|NULL	$member	The member to check for (NULL for currently logged in member)
	 * @return	bool
	 */
	public function canDelete( $member=NULL )
	{
		$member = $member ?: \IPS\Member::loggedIn();

		return ( !$this->isFirst() and ( static::modPermission( 'delete', $member, $this->item()->containerWrapper() ) or ( $member->member_id and $member->member_id == $this->author()->member_id and ( $member->group['g_delete_own_posts'] == '1' or \in_array( \get_class( $this->item() ), explode( ',', $member->group['g_delete_own_posts'] ) ) ) ) ) );
	}
	
	/**
	 * Can split this comment off?
	 *
	 * @param	\IPS\Member|NULL	$member	The member to check for (NULL for currently logged in member)
	 * @return	bool
	 */
	public function canSplit( $member=NULL )
	{
		$itemClass = static::$itemClass;

		if ( $itemClass::$firstCommentRequired )
		{
			if ( !$this->isFirst() )
			{
				$member = $member ?: \IPS\Member::loggedIn();
				return $itemClass::modPermission( 'split_merge', $member, $this->item()->containerWrapper() );
			}
		}
		return FALSE;
	}

	/**
	 * Search Index Permissions
	 *
	 * @return	string	Comma-delimited values or '*'
	 * 	@li			Number indicates a group
	 *	@li			Number prepended by "m" indicates a member
	 *	@li			Number prepended by "s" indicates a social group
	 */
	public function searchIndexPermissions()
	{
		try
		{
			return $this->item()->searchIndexPermissions();
		}
		catch ( \BadMethodCallException $e )
		{
			return '*';
		}
	}
	
	/**
	 * Should this comment be ignored?
	 *
	 * @param	\IPS\Member|null	$member	The member to check for - NULL for currently logged in member
	 * @return	bool
	 */
	public function isIgnored( $member=NULL )
	{
		if ( !\IPS\Settings::i()->ignore_system_on )
		{
			return FALSE;
		}
		
		if ( $member === NULL )
		{
			$member = \IPS\Member::loggedIn();
		}
				
		return $member->isIgnoring( $this->author(), static::$ignoreType );
	}
	
	/**
	 * Get date line
	 *
	 * @return	string
	 */
	public function dateLine()
	{
		if( $this->mapped('first') )
		{
			return \IPS\Member::loggedIn()->language()->addToStack( static::$formLangPrefix . 'date_started', FALSE, array( 'htmlsprintf' => array( \IPS\DateTime::ts( $this->mapped('date') )->html( FALSE ) ) ) );
		}
		else
		{
			return \IPS\Member::loggedIn()->language()->addToStack( static::$formLangPrefix . 'date_replied', FALSE, array( 'htmlsprintf' => array( \IPS\DateTime::ts( $this->mapped('date') )->html( FALSE ) ) ) );
		}
	}
	
	/**
	 * Edit Comment Contents - Note: does not add edit log
	 *
	 * @param	string	$newContent	New content
	 * @return	string|NULL
	 */
	public function editContents( $newContent )
	{
		/* Check if profanity filters should mod-queue this comment */
		$sendNotifications = $this->checkProfanityFilters( $this->isFirst(), TRUE, $newContent );
		
		/* Do it */
		$valueField = static::$databaseColumnMap['content'];
		$oldValue = $this->$valueField;
		$this->$valueField = $newContent;
		$this->save();
		
		/* Send any new mention/quote notifications */
		$this->sendAfterEditNotifications( $oldValue );
		
		/* Reindex */
		if ( $this instanceof \IPS\Content\Searchable )
		{
			\IPS\Content\Search\Index::i()->index( $this );
		}

		/* Send notifications */
		if ( $sendNotifications AND !\in_array( 'IPS\Content\Review', class_parents( \get_called_class() ) ) )
		{
			if( $this->hidden() === 1 )
			{
				$this->sendUnapprovedNotification();
			}
		}
	}
	
	/**
	 * Get edit line
	 *
	 * @return	string|NULL
	 */
	public function editLine()
	{
		if ( $this instanceof \IPS\Content\EditHistory and $this->mapped('edit_time') and ( $this->mapped('edit_show') or \IPS\Member::loggedIn()->modPermission('can_view_editlog') ) and \IPS\Settings::i()->edit_log )
		{
			return \IPS\Theme::i()->getTemplate( 'global', 'core' )->commentEditLine( $this, ( isset( static::$databaseColumnMap['edit_reason'] ) and $this->mapped('edit_reason') ) );
		}
		return NULL;
	}
	
	/**
	 * Get edit history
	 *
	 * @param	bool	$staff		Set true for moderators who have permission to view the full log which will show edits not made by the author and private edits
	 * @return	\IPS\Db\Select
	 */
	public function editHistory( $staff=FALSE )
	{
		$idColumn = static::$databaseColumnId;
		$where = array( array( 'class=? AND comment_id=?', \get_called_class(), $this->$idColumn ) );
		if ( !$staff )
		{
			$where[] = array( '`member`=? AND public=1', $this->author()->member_id );
		}
		return \IPS\Db::i()->select( '*', 'core_edit_history', $where, 'time DESC' );
	}
	
	/**
	 * Get HTML
	 *
	 * @return	string
	 */
	public function html()
	{
		$template = static::$commentTemplate[1];

		return \IPS\Theme::i()->getTemplate( static::$commentTemplate[0][0], static::$commentTemplate[0][1], ( isset( static::$commentTemplate[0][2] ) ) ? static::$commentTemplate[0][2] : NULL )->$template( $this->item(), $this );
	}
		
	/**
	 * Users to receive immediate notifications
	 *
	 * @param	int|array		$limit		LIMIT clause
	 * @param	string|NULL		$extra		Additional data
	 * @param	boolean			$countOnly	Just return the count
	 * @return \IPS\Db\Select
	 */
	public function notificationRecipients( $limit=array( 0, 25 ), $extra=NULL, $countOnly=FALSE )
	{
		/* Do we only want the count? */
		if( $countOnly )
		{
			$count	= 0;
			$count	+= $this->author()->followersCount( 3, array( 'immediate' ), $this->mapped('date') );
			$count	+= $this->item()->followersCount( 3, array( 'immediate' ), $this->mapped('date') );

			return $count;
		}

		$memberFollowers = $this->author()->followers( 3, array( 'immediate' ), $this->mapped('date'), NULL );
		
		if( $memberFollowers !== NULL )
		{
			$unions	= array( 
				$this->item()->followers( 3, array( 'immediate' ), $this->mapped('date'), NULL ),
				$memberFollowers
			);

			return \IPS\Db::i()->union( $unions, 'follow_added', $limit );
		}
		else
		{
			return $this->item()->followers( static::FOLLOW_PUBLIC + static::FOLLOW_ANONYMOUS, array( 'immediate' ), $this->mapped('date'), $limit, 'follow_added' );
		}		
	}
	
	/**
	 * Create Notification
	 *
	 * @param	string|NULL		$extra		Additional data
	 * @return	\IPS\Notification
	 */
	protected function createNotification( $extra=NULL )
	{
		return new \IPS\Notification( \IPS\Application::load( 'core' ), 'new_comment', $this->item(), array( $this ) );
	}
	
	/**
	 * Syncing to run when hiding
	 *
	 * @param	\IPS\Member|NULL|FALSE	$member	The member doing the action (NULL for currently logged in member, FALSE for no member)
	 * @return	void
	 */
	public function onHide( $member )
	{
		$item = $this->item();
		
		/* Remove any notifications */
		$idColumn = static::$databaseColumnId;
		\IPS\Db::i()->delete( 'core_notifications', array( 'item_sub_class=? AND item_sub_id=?', (string) \get_called_class(), (int) $this->$idColumn ) );
		
		$item->resyncCommentCounts();
		$item->resyncLastComment();
		$item->save();

		/* We have to do this *after* updating the last comment data for the item, because that uses the cached data from the item (i.e. topic) */
		try
		{
			if ( $item->container()->_comments !== NULL )
			{
				$item->container()->setLastComment();
				$item->container()->resetCommentCounts();
				$item->container()->save();
			}
		} catch ( \BadMethodCallException $e ) {}
	}
	
	/**
	 * Syncing to run when unhiding
	 *
	 * @param	bool					$approving	If true, is being approved for the first time
	 * @param	\IPS\Member|NULL|FALSE	$member	The member doing the action (NULL for currently logged in member, FALSE for no member)
	 * @return	void
	 */
	public function onUnhide( $approving, $member )
	{
		$item = $this->item();

		if ( $approving )
		{
			/* We should only do this if it is an actual account, and not a guest. */
			if ( $this->author()->member_id )
			{
				try
				{
					if ( static::incrementPostCount( $item->container() ) )
					{
						$this->author()->member_posts++;
						$this->author()->save();
					}
				}
				catch( \BadMethodCallException $e ) { }
			}
			
		}
		
		$item->resyncCommentCounts();
		$item->resyncLastComment();
		$item->save();

		/* We have to do this *after* updating the last comment data for the item, because that uses the cached data from the item (i.e. topic) */
		try
		{
			if ( $item->container()->_comments !== NULL )
			{
				$item->container()->setLastComment();
				$item->container()->resetCommentCounts();
				$item->container()->save();
			}
		} catch ( \BadMethodCallException $e ) {}
	}
	
	/**
	 * Move Comment to another item
	 *
	 * @param	\IPS\Content\Item	$item	The item to move this comment to
	 * @param	bool				$skip	Skip rebuilding new/old content item data (used for multimod where we can do it in one go after)
	 * @return	void
	 */
	public function move( \IPS\Content\Item $item, $skip=FALSE )
	{
		$oldItem = $this->item();
		
		$idColumn = $item::$databaseColumnId;
		$itemColumn = static::$databaseColumnMap['item'];
		$commentIdColumn = static::$databaseColumnId;
		$this->$itemColumn = $item->$idColumn;
		$this->save();
		
		/* The new item needs to re-claim any attachments associated with this comment */
		\IPS\Db::i()->update( 'core_attachments_map', array( 'id1' => $item->$idColumn ), array( "location_key=? AND id1=? AND id2=?", $oldItem::$application . '_' . mb_ucfirst( $oldItem::$module ), $oldItem->$idColumn, $this->$commentIdColumn ) );

		/* Update notifications */
		\IPS\Db::i()->update( 'core_notifications', array( 'item_id' => $item->$idColumn ), array( 'item_class=? and item_id=? and item_sub_class=? and item_sub_id=?', (string) \get_class( $item ), $oldItem->$idColumn, (string) \get_called_class(), $this->$commentIdColumn ) );
		
		/* Update reputation */
		if ( \IPS\IPS::classUsesTrait( $this, 'IPS\Content\Reactable' ) )
		{
			\IPS\Db::i()->update( 'core_reputation_index', array( 'item_id' => $item->$idColumn ), array( 'class_type_id_hash=?', md5( \get_class( $this ) . ':' . $oldItem->$idColumn ) ) );
		}
		
		if( $skip === FALSE )
		{
			$oldItem->rebuildFirstAndLastCommentData();
			$item->rebuildFirstAndLastCommentData();

			/* Add to search index */
			if ( $this instanceof \IPS\Content\Searchable )
			{
				\IPS\Content\Search\Index::i()->index( $this );
			}
		}
	}
	
	/**
	 * Get container
	 *
	 * @return	\IPS\Node\Model
	 * @note	Certain functionality requires a valid container but some areas do not use this functionality (e.g. messenger)
	 * @note	Some functionality refers to calls to the container when managing comments (e.g. deleting a comment and decrementing content counts). In this instance, load the parent items container.
	 * @throws	\OutOfRangeException|\BadMethodCallException
	 */
	public function container()
	{
		$container = NULL;
		
		try
		{
			$container = $this->item()->container();
		}
		catch( \BadMethodCallException $e ) {}
		
		return $container;
	}
			
	/**
	 * Delete Comment
	 *
	 * @return	void
	 */
	public function delete()
	{
		/* Remove from search index first */
		if ( $this instanceof \IPS\Content\Searchable )
		{
			\IPS\Content\Search\Index::i()->removeFromSearchIndex( $this );
		}

		/* Init */
		$idColumn = static::$databaseColumnId;
		$itemClass = static::$itemClass;
		$itemIdColumn = $itemClass::$databaseColumnId;

		/* It is possible to delete a comment that is orphaned, so let's try to protect against that */
		try
		{
			$item	= $this->item();
			$itemId	= $this->item()->$itemIdColumn;
		}
		catch( \OutOfRangeException $e )
		{
			$item	= NULL;
			$itemId	= $this->mapped('item');
		}

		/* Remove featured comment associations */
		if( $this->isFeatured() AND $item )
		{
			\IPS\Application::load('core')->extensions( 'core', 'MetaData' )['FeaturedComments']->unfeatureComment( $item, $this );
		}
		
		/* Unclaim attachments */
		\IPS\File::unclaimAttachments( $itemClass::$application . '_' . mb_ucfirst( $itemClass::$module ), $itemId, $this->$idColumn );
		
		/* Reduce the number of comment/reviews count on the item but only if the item is unapproved or visible 
		 * - hidden as opposed to unapproved items do not get included in either of the unapproved_comments/num_comments columns */
		if( $this->hidden() !== -1 AND $this->hidden() !== -2 AND $this->hidden() !== -3 ) 
		{
			$columnName = ( $this->hidden() === 1 ) ? 'unapproved_comments' : 'num_comments';
			if ( \in_array( 'IPS\Content\Review', class_parents( \get_called_class() ) ) )
			{
				$columnName = ( $this->hidden() === 1 ) ? 'unapproved_reviews' : 'num_reviews';
			}
			if ( isset( $itemClass::$databaseColumnMap[$columnName] ) AND $item !== NULL )
			{
				$column = $itemClass::$databaseColumnMap[$columnName];

				if ( $item->$column > 0 )
				{
					$item->$column--;
					$item->save();
				}
			}
		}
		else if ( $this->hidden() === -1 )
		{
			if ( \in_array( 'IPS\Content\Review', class_parents( \get_called_class() ) ) )
			{
				if( isset( $itemClass::$databaseColumnMap['hidden_reviews'] ) AND $item !== NULL )
				{
					$column = $itemClass::$databaseColumnMap['hidden_reviews'];

					if ( $item->$column > 0 )
					{
						$item->$column--;
						$item->save();
					}
				}
			}
			else
			{
				if( isset( $itemClass::$databaseColumnMap['hidden_comments'] ) AND $item !== NULL )
				{
					$column = $itemClass::$databaseColumnMap['hidden_comments'];

					if ( $item->$column > 0 )
					{
						$item->$column--;
						$item->save();
					}
				}
			}
		}
		
		/* Delete any notifications telling people about this */
		$memberIds	= array();

		foreach( \IPS\Db::i()->select( '`member`', 'core_notifications', array( 'item_sub_class=? AND item_sub_id=?', (string) \get_called_class(), (int) $this->$idColumn ) ) as $member )
		{
			$memberIds[ $member ]	= $member;
		}

		\IPS\Db::i()->delete( 'core_notifications', array( 'item_sub_class=? AND item_sub_id=?', (string) \get_called_class(), (int) $this->$idColumn ) );

		foreach( $memberIds as $member )
		{
			\IPS\Member::load( $member )->recountNotifications();
		}

		/* Actually delete */
		parent::delete();
		
		/* Update last comment/review data for container and item */
		try
		{
			if ( $item !== NULL AND \in_array( 'IPS\Content\Review', class_parents( \get_called_class() ) ) )
			{
				if ( $item->container()->_reviews !== NULL )
				{
					$item->container()->_reviews = ( $item->container()->_reviews - 1 );
					$item->resyncLastReview();
					$item->save();
					$item->container()->setLastReview();
				}
				if ( $item->container()->_unapprovedReviews !== NULL )
				{
					$item->container()->_unapprovedReviews = ( $item->container()->_unapprovedReviews > 0 ) ? ( $item->container()->_unapprovedReviews - 1 ) : 0;
				}
				$item->container()->save();
			}
			else if ( $item !== NULL AND $item->container() !== NULL )
			{
				if ( $item->container()->_comments !== NULL )
				{
					if ( !$this->hidden() AND $this->hidden() !== -2 )
					{
						$item->container()->_comments = ( $item->container()->_comments > 0 ) ? ( $item->container()->_comments - 1 ) : 0;
					}
					
					$item->resyncLastComment();
					$item->save();
					$item->container()->setLastComment();
				}
				if ( $item->container()->_unapprovedComments !== NULL and $this->hidden() === 1 )
				{
					$item->container()->_unapprovedComments = ( $item->container()->_unapprovedComments > 0 ) ? ( $item->container()->_unapprovedComments - 1 ) : 0;
				}
				$item->container()->save();
			}
		}
		catch ( \BadMethodCallException $e ) {}
	}
	
	/**
	 * Change Author
	 *
	 * @param	\IPS\Member	$newAuthor	The new author
	 * @param	bool		$log		If TRUE, action will be logged to moderator log
	 * @return	void
	 */
	public function changeAuthor( \IPS\Member $newAuthor, $log=TRUE )
	{
		$oldAuthor = $this->author();

		/* If we delete a member, then change author, the old author returns 0 as does the new author as the
		   member row is deleted before the task is run */
		if( $newAuthor->member_id and ( $oldAuthor->member_id == $newAuthor->member_id ) )
		{
			return;
		}
		
		/* Update the row */
		parent::changeAuthor( $newAuthor, $log );
		
		/* Adjust post counts */
		if ( static::incrementPostCount( $this->item()->containerWrapper() ) AND ( $oldAuthor->member_id OR ( $this->hidden() === 0 AND $this->item()->hidden === 0 ) ) )
		{
			if( $oldAuthor->member_id )
			{
				$oldAuthor->member_posts--;
				$oldAuthor->save();
			}
			
			if( $newAuthor->member_id )
			{
				$newAuthor->member_posts++;
				$newAuthor->save();
			}
		}
		
		/* Last comment */
		$this->item()->resyncLastComment();
		$this->item()->resyncLastReview();
		$this->item()->save();
		if ( $container = $this->item()->containerWrapper() )
		{
			$container->setLastComment();
			$container->setLastReview();
			$container->save();
		}

		/* Update search index */
		if ( $this instanceof \IPS\Content\Searchable )
		{
			\IPS\Content\Search\Index::i()->index( $this );
		}
	}
	
	/**
	 * Get template for content tables
	 *
	 * @return	callable
	 */
	public static function contentTableTemplate()
	{
		return array( \IPS\Theme::i()->getTemplate( 'tables', 'core', 'front' ), 'commentRows' );
	}
	
	/**
	 * Get content for header in content tables
	 *
	 * @return	callable
	 */
	public function contentTableHeader()
	{
		return \IPS\Theme::i()->getTemplate( 'global', static::$application )->commentTableHeader( $this, $this->item() );
	}

	/**
	 * @brief Cached containers we can access
	 */
	protected static $permissionSelect	= array();

	/**
	 * Get comments based on some arbitrary parameters
	 *
	 * @param	array		$where					Where clause
	 * @param	string		$order					MySQL ORDER BY clause (NULL to order by date)
	 * @param	int|array	$limit					Limit clause
	 * @param	string|NULL	$permissionKey			A key which has a value in the permission map (either of the container or of this class) matching a column ID in core_permission_index, or NULL to ignore permissions
	 * @param	mixed		$includeHiddenComments	Include hidden comments? NULL to detect if currently logged in member has permission, -1 to return public content only, TRUE to return unapproved content and FALSE to only return unapproved content the viewing member submitted
	 * @param	int			$queryFlags				Select bitwise flags
	 * @param	\IPS\Member	$member					The member (NULL to use currently logged in member)
	 * @param	bool		$joinContainer			If true, will join container data (set to TRUE if your $where clause depends on this data)
	 * @param	bool		$joinComments			If true, will join comment data (set to TRUE if your $where clause depends on this data)
	 * @param	bool		$joinReviews			If true, will join review data (set to TRUE if your $where clause depends on this data)
	 * @param	bool		$countOnly				If true will return the count
	 * @param	array|null	$joins					Additional arbitrary joins for the query
	 * @return	array|NULL|\IPS\Content\Comment		If $limit is 1, will return \IPS\Content\Comment or NULL for no results. For any other number, will return an array.
	 */
	public static function getItemsWithPermission( $where=array(), $order=NULL, $limit=10, $permissionKey='read', $includeHiddenComments=\IPS\Content\Hideable::FILTER_AUTOMATIC, $queryFlags=0, \IPS\Member $member=NULL, $joinContainer=FALSE, $joinComments=FALSE, $joinReviews=FALSE, $countOnly=FALSE, $joins=NULL )
	{
		/* Get the item class - we need it later */
		$itemClass	= static::$itemClass;
		
		$itemWhere = array();
		$containerWhere = array();
		
		/* Queries are always more efficient when the WHERE clause is added to the ON */
		if ( \is_array( $where ) )
		{
			foreach( $where as $key => $value )
			{
				if ( $key ==='item' )
				{
					$itemWhere = array_merge( $itemWhere, $value );
					
					unset( $where[ $key ] );
				}
				
				if ( $key === 'container' )
				{
					$containerWhere = array_merge( $containerWhere, $value );
					unset( $where[ $key ] );
				}
			}
		}
		
		/* Work out the order */
		$order = $order ?: ( static::$databasePrefix . static::$databaseColumnMap['date'] . ' DESC' );
		
		/* Exclude hidden comments */
		if( $includeHiddenComments === \IPS\Content\Hideable::FILTER_AUTOMATIC )
		{
			if( static::modPermission( 'view_hidden', $member ) )
			{
				$includeHiddenComments = \IPS\Content\Hideable::FILTER_SHOW_HIDDEN;
			}
			else
			{
				$includeHiddenComments = \IPS\Content\Hideable::FILTER_OWN_HIDDEN;
			}
		}
		
		if ( \in_array( 'IPS\Content\Hideable', class_implements( \get_called_class() ) ) and $includeHiddenComments === \IPS\Content\Hideable::FILTER_ONLY_HIDDEN )
		{
			/* If we can't view hidden stuff, just return an empty array now */
			if( !static::modPermission( 'view_hidden', $member ) )
			{
				return array();
			}

			if ( isset( static::$databaseColumnMap['approved'] ) )
			{
				$where[] = array( static::$databasePrefix . static::$databaseColumnMap['approved'] . '=?', 0 );
			}
			elseif ( isset( static::$databaseColumnMap['hidden'] ) )
			{
				$where[] = array( static::$databasePrefix . static::$databaseColumnMap['hidden'] . '=?', 1 );
			}
		}
		elseif ( \in_array( 'IPS\Content\Hideable', class_implements( \get_called_class() ) ) and ( $includeHiddenComments === \IPS\Content\Hideable::FILTER_OWN_HIDDEN OR $includeHiddenComments === \IPS\Content\Hideable::FILTER_PUBLIC_ONLY ) )
		{
			if ( isset( static::$databaseColumnMap['approved'] ) )
			{
				$where[] = array( static::$databasePrefix . static::$databaseColumnMap['approved'] . '=?', 1 );
			}
			elseif ( isset( static::$databaseColumnMap['hidden'] ) )
			{
				$where[] = array( static::$databasePrefix . static::$databaseColumnMap['hidden'] . '=?', 0 );
			}
		}
		
		/* Exclude hidden items. We don't check FILTER_ONLY_HIDDEN because we should return hidden comments in both approved and unapproved topics. */
		if ( \in_array( 'IPS\Content\Hideable', class_implements( $itemClass ) ) and ( $includeHiddenComments === \IPS\Content\Hideable::FILTER_OWN_HIDDEN OR $includeHiddenComments === \IPS\Content\Hideable::FILTER_PUBLIC_ONLY ) )
		{
			$member = $member ?: \IPS\Member::loggedIn();
			$authorCol = $itemClass::$databaseTable . '.' . $itemClass::$databasePrefix . $itemClass::$databaseColumnMap['author'];
			if ( isset( $itemClass::$databaseColumnMap['approved'] ) )
			{
				$col = $itemClass::$databaseTable . '.' . $itemClass::$databasePrefix . $itemClass::$databaseColumnMap['approved'];
				if ( $member->member_id AND $includeHiddenComments !== \IPS\Content\Hideable::FILTER_PUBLIC_ONLY )
				{
					$itemWhere[] = array( "( {$col}=1 OR ( {$col}=0 AND {$authorCol}=" . $member->member_id . " ) )" );
				}
				else
				{
					$itemWhere[] = array( "{$col}=1" );
				}
			}
			elseif ( isset( $itemClass::$databaseColumnMap['hidden'] ) )
			{
				$col = $itemClass::$databaseTable . '.' . $itemClass::$databasePrefix . $itemClass::$databaseColumnMap['hidden'];
				if ( $member->member_id AND $includeHiddenComments !== \IPS\Content\Hideable::FILTER_PUBLIC_ONLY )
				{
					$itemWhere[] = array( "( {$col}=0 OR ( {$col}=1 AND {$authorCol}=" . $member->member_id . " ) )" );
				}
				else
				{
					$itemWhere[] = array( "{$col}=0" );
				}
			}
		}
        else
        {
            /* Legacy items pending deletion in 3.x at time of upgrade may still exist */
            $col	= null;

            if ( isset( $itemClass::$databaseColumnMap['approved'] ) )
            {
                $col = $itemClass::$databaseTable . '.' . $itemClass::$databasePrefix . $itemClass::$databaseColumnMap['approved'];
            }
            else if( isset( $itemClass::$databaseColumnMap['hidden'] ) )
            {
                $col = $itemClass::$databaseTable . '.' . $itemClass::$databasePrefix . $itemClass::$databaseColumnMap['hidden'];
            }

            if( $col )
            {
            	$itemWhere[] = array( "{$col} < 2" );
            }
        }
        
        /* No matter if we can or cannot view hidden items, we do not want these to show: -2 is queued for deletion and -3 is posted before register */
        if ( isset( static::$databaseColumnMap['hidden'] ) )
        {
	        $col = static::$databaseTable . '.' . static::$databasePrefix . static::$databaseColumnMap['hidden'];
	        $where[] = array( "{$col}!=-2 AND {$col} !=-3" );
        }
        else if ( isset( static::$databaseColumnMap['approved'] ) )
        {
	        $col = static::$databaseTable . '.' . static::$databasePrefix . static::$databaseColumnMap['approved'];
	        $where[] = array( "{$col}!=-2 AND {$col} !=-3" );
        }

        /* We also need to check the item for soft delete and post before register */
        if( \in_array( 'IPS\Content\Hideable', class_implements( $itemClass ) ) )
		{
			/* No matter if we can or cannot view hidden items, we do not want these to show: -2 is queued for deletion and -3 is posted before register */
			if ( isset( $itemClass::$databaseColumnMap['hidden'] ) )
			{
				$col = $itemClass::$databaseTable . '.' . $itemClass::$databasePrefix . $itemClass::$databaseColumnMap['hidden'];
				$itemWhere[] = array( "{$col}!=-2 AND {$col} !=-3" );
			}
			else if ( isset( $itemClass::$databaseColumnMap['approved'] ) )
			{
				$col = $itemClass::$databaseTable . '.' . $itemClass::$databasePrefix . $itemClass::$databaseColumnMap['approved'];
				$itemWhere[] = array( "{$col}!=-2 AND {$col} !=-3" );
			}
		}

        if ( $joinContainer AND isset( $itemClass::$containerNodeClass ) )
		{
			$containerClass = $itemClass::$containerNodeClass;
			if( $joins !== NULL )
			{
				array_unshift( $joins, array(
					'from'	=> 	$containerClass::$databaseTable,
					'where'	=> array_merge( array( array( $itemClass::$databaseTable . '.' . $itemClass::$databasePrefix . $itemClass::$databaseColumnMap['container'] . '=' . $containerClass::$databaseTable . '.' . $containerClass::$databasePrefix . $containerClass::$databaseColumnId ) ), $containerWhere )
				) );
			}
			else
			{
				$joins = array(
					array(
						'from'	=> 	$containerClass::$databaseTable,
						'where'	=> array_merge( array( array( $itemClass::$databaseTable . '.' . $itemClass::$databasePrefix . $itemClass::$databaseColumnMap['container'] . '=' . $containerClass::$databaseTable . '.' . $containerClass::$databasePrefix . $containerClass::$databaseColumnId ) ), $containerWhere )
					)
				);
			}
		}
        
		/* Build the select clause */
		if( $countOnly )
		{
			if ( \in_array( 'IPS\Content\Permissions', class_implements( $itemClass ) ) AND $permissionKey !== NULL )
			{
				$member = $member ?: \IPS\Member::loggedIn();
				
				$containerClass = $itemClass::$containerNodeClass;

				$select = \IPS\Db::i()->select( 'COUNT(*) as cnt', static::$databaseTable, $where, NULL, NULL, NULL, NULL, $queryFlags )
					->join( $itemClass::$databaseTable, array_merge( array( array( static::$databaseTable . "." . static::$databasePrefix . static::$databaseColumnMap['item'] . "=" . $itemClass::$databaseTable . "." . $itemClass::$databasePrefix . $itemClass::$databaseColumnId ) ), $itemWhere ), 'STRAIGHT_JOIN' )
					->join( 'core_permission_index', array( "core_permission_index.app=? AND core_permission_index.perm_type=? AND core_permission_index.perm_type_id=" . $itemClass::$databaseTable . "." . $itemClass::$databasePrefix . $itemClass::$databaseColumnMap['container'] . ' AND (' . \IPS\Db::i()->findInSet( 'perm_' . $containerClass::$permissionMap[ $permissionKey ], $member->groups ) . ' OR ' . 'perm_' . $containerClass::$permissionMap[ $permissionKey ] . '=? )', $containerClass::$permApp, $containerClass::$permType, '*' ), 'STRAIGHT_JOIN' );
			}
			else
			{
				$select = \IPS\Db::i()->select( 'COUNT(*) as cnt', static::$databaseTable, $where, NULL, NULL, NULL, NULL, $queryFlags )
					->join( $itemClass::$databaseTable, array_merge( array( array( static::$databaseTable . "." . static::$databasePrefix . static::$databaseColumnMap['item'] . "=" . $itemClass::$databaseTable . "." . $itemClass::$databasePrefix . $itemClass::$databaseColumnId ) ), $itemWhere ), 'STRAIGHT_JOIN' );
			}

			if ( $joins !== NULL AND \count( $joins ) )
			{
				foreach( $joins as $join )
				{
					$select->join( $join['from'], ( isset( $join['where'] ) ? $join['where'] : null ), ( isset( $join['type'] ) ) ? $join['type'] : 'LEFT' );
				}
			}
			return $select->first();
		}

		$selectClause = static::$databaseTable . '.*';
		if ( $joins !== NULL AND \count( $joins ) )
		{
			foreach( $joins as $join )
			{
				if ( isset( $join['select'] ) )
				{
					$selectClause .= ', ' . $join['select'];
				}
			}
		}
		
		if ( \in_array( 'IPS\Content\Permissions', class_implements( $itemClass ) ) AND $permissionKey !== NULL )
		{
			$containerClass = $itemClass::$containerNodeClass;

			$member = $member ?: \IPS\Member::loggedIn();
			$categories	= array();
			$lookupKey	= md5( $containerClass::$permApp . $containerClass::$permType . $permissionKey . json_encode( $member->groups ) );

			if( !isset( static::$permissionSelect[ $lookupKey ] ) )
			{
				static::$permissionSelect[ $lookupKey ] = array();
				$permQuery = \IPS\Db::i()->select( 'perm_type_id', 'core_permission_index', array( "core_permission_index.app='" . $containerClass::$permApp . "' AND core_permission_index.perm_type='" . $containerClass::$permType . "' AND (" . \IPS\Db::i()->findInSet( 'perm_' . $containerClass::$permissionMap[ $permissionKey ], $member->permissionArray() ) . ' OR ' . 'perm_' . $containerClass::$permissionMap[ $permissionKey ] . "='*' )" ) );
				
				if ( \count( $containerWhere ) )
				{
					$permQuery->join( $containerClass::$databaseTable, array_merge( $containerWhere, array( 'core_permission_index.perm_type_id=' . $containerClass::$databaseTable . '.' . $containerClass::$databasePrefix . $containerClass::$databaseColumnId ) ), 'STRAIGHT_JOIN' );
				}

				foreach( $permQuery as $result )
				{
					static::$permissionSelect[ $lookupKey ][] = $result;
				}
			}

			$categories = static::$permissionSelect[ $lookupKey ];

			if( \count( $categories ) )
			{
				$where[]	= array( $itemClass::$databaseTable . "." . $itemClass::$databasePrefix . $itemClass::$databaseColumnMap['container'] . ' IN(' . implode( ',', $categories ) . ')' );
			}
			else
			{
				$where[]	= array( $itemClass::$databaseTable . "." . $itemClass::$databasePrefix . $itemClass::$databaseColumnMap['container'] . '=0' );
			}

			$selectClause .= ', ' . $itemClass::$databaseTable . '.*';

			$select = \IPS\Db::i()->select( $selectClause, static::$databaseTable, $where, $order, $limit, NULL, NULL, $queryFlags )
				->join( $itemClass::$databaseTable, array_merge( array( array( static::$databaseTable . "." . static::$databasePrefix . static::$databaseColumnMap['item'] . "=" . $itemClass::$databaseTable . "." . $itemClass::$databasePrefix . $itemClass::$databaseColumnId ) ), $itemWhere ), 'STRAIGHT_JOIN' );
		}
		else
		{
			$select = \IPS\Db::i()->select( $selectClause, static::$databaseTable, $where, $order, $limit, NULL, NULL, $queryFlags )
				->join( $itemClass::$databaseTable, array_merge( array( array( static::$databaseTable . "." . static::$databasePrefix . static::$databaseColumnMap['item'] . "=" . $itemClass::$databaseTable . "." . $itemClass::$databasePrefix . $itemClass::$databaseColumnId ) ), $itemWhere ), 'STRAIGHT_JOIN' );
		}
						
		if ( $joins !== NULL AND \count( $joins ) )
		{
			foreach( $joins as $join )
			{
				$select->join( $join['from'], ( isset( $join['where'] ) ? $join['where'] : null ), ( isset( $join['type'] ) ) ? $join['type'] : 'LEFT' );
			}
		}
				
		/* Return */
		return new \IPS\Patterns\ActiveRecordIterator( $select, \get_called_class() );
	}
	
	/**
	 * Warning Reference Key
	 *
	 * @return	string
	 */
	public function warningRef()
	{
		/* If the member cannot warn, return NULL so we're not adding ugly parameters to the profile URL unnecessarily */
		if ( !\IPS\Member::loggedIn()->modPermission('mod_can_warn') )
		{
			return NULL;
		}
		
		$itemClass = static::$itemClass;
		$idColumn = static::$databaseColumnId;
		return base64_encode( json_encode( array( 'app' => $itemClass::$application, 'module' => $itemClass::$module . '-comment' , 'id_1' => $this->mapped('item'), 'id_2' => $this->$idColumn ) ) );
	}
	
	/**
	 * Get attachment IDs
	 *
	 * @return	array
	 */
	public function attachmentIds()
	{
		$item = $this->item();
		$idColumn = $item::$databaseColumnId;
		$commentIdColumn = static::$databaseColumnId;
		return array( $this->item()->$idColumn, $this->$commentIdColumn ); 
	}
	
	/**
	 * Returns the content images
	 *
	 * @param	int|null	$limit		Number of attachments to fetch, or NULL for all
	 *
	 * @return	array|NULL
	 * @throws	\BadMethodCallException
	 */
	public function contentImages( $limit = NULL )
	{
		$idColumn = static::$databaseColumnId;
		$item = $this->item();
		$attachments = array();
		$itemIdColumn = $item::$databaseColumnId;
		$internal = \IPS\Db::i()->select( 'attachment_id', 'core_attachments_map', array( 'location_key=? and id1=? and id2=?', $item::$application . '_' . mb_ucfirst( $item::$module ), $item->$itemIdColumn, $this->$idColumn ) );
		
		foreach( \IPS\Db::i()->select( '*', 'core_attachments', array( array( 'attach_id IN(?)', $internal ), array( 'attach_is_image=1' ) ), 'attach_id ASC', $limit ) as $row )
		{
			$attachments[] = array( 'core_Attachment' => $row['attach_location'] );
		}

		/* IS there a club with a cover photo? */
		if ( \IPS\IPS::classUsesTrait( $item->container(), 'IPS\Content\ClubContainer' ) and $club = $item->container()->club() )
		{
			$attachments[] = array( 'core_Clubs' => $club->cover_photo );
		}
			
		return \count( $attachments ) ? $attachments : NULL;
	}
	
	/**
	 * @brief	Existing warning
	 */
	public $warning;
		
	/**
	 * Can Share
	 *
	 * @return	boolean
	 */
	public function canShare()
	{
		return ( $this->canView( \IPS\Member::load( 0 ) ) and \in_array( 'IPS\Content\Shareable', class_implements( \get_called_class() ) ) );
	}
	
	/**
	 * Return sharelinks for this item
	 *
	 * @return array
	 */
	public function sharelinks()
	{
		if( !\count( $this->sharelinks ) )
		{
			if ( $this instanceof Shareable and $this->canShare() )
			{
				$idColumn = static::$databaseColumnId;
				$shareUrl = $this->url( 'find' )->setQueryString( 'comment', $this->$idColumn );
				
				$this->sharelinks = \IPS\core\ShareLinks\Service::getAllServices( $shareUrl, $this->item()->mapped('title'), NULL, $this->item() );
			}
		}

		return $this->sharelinks;
	}

	/**
	 * Addition where needed for fetching comments
	 *
	 * @return	array|NULL
	 */
	public static function commentWhere()
	{
		return NULL;
	}
	
	/**
	 * Is a featured comment?
	 *
	 * @return	bool
	 * @note This is a wrapper for the extension so content items can extend and apply their own logic
	 */
	public function isFeatured()
	{
		return \IPS\Application::load('core')->extensions( 'core', 'MetaData' )['FeaturedComments']->isFeatured( $this );
	}
	
	/**
	 * Get output for API
	 *
	 * @param	\IPS\Member|NULL	$authorizedMember	The member making the API request or NULL for API Key / client_credentials
	 * @return	array
	 * @apiresponse	int			id			ID number
	 * @apiresponse	int			item_id		The ID number of the item this belongs to
	 * @apiresponse	\IPS\Member	author		Author
	 * @apiresponse	datetime	date		Date
	 * @apiresponse	string		content		The content
	 * @apiresponse	bool		hidden		Is hidden?
	 * @apiresponse	string		url			URL to content
	 */
	public function apiOutput( \IPS\Member $authorizedMember = NULL )
	{
		$idColumn = static::$databaseColumnId;
		$itemColumn = static::$databaseColumnMap['item'];
		return array(
			'id'		=> $this->$idColumn,
			'item_id'	=> $this->$itemColumn,
			'author'	=> $this->author()->apiOutput( $authorizedMember ),
			'date'		=> \IPS\DateTime::ts( $this->mapped('date') )->rfc3339(),
			'content'	=> \IPS\Text\Parser::removeLazyLoad( $this->content() ),
			'hidden'	=> (bool) $this->hidden(),
			'url'		=> (string) $this->url()
		);
	}
	
	/* !Embeddable */
	
	/**
	 * Get content for embed
	 *
	 * @param	array	$params	Additional parameters to add to URL
	 * @return	string
	 */
	public function embedContent( $params )
	{
		return \IPS\Theme::i()->getTemplate( 'global', 'core' )->embedComment( $this->item(), $this, $this->url()->setQueryString( $params ), $this->item()->embedImage() );
	}
}