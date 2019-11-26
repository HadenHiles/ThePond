<?php
/**
 * @brief		Abstract Content Model
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		3 Oct 2013
 */

namespace IPS;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Abstract Content Model
 */
abstract class _Content extends \IPS\Patterns\ActiveRecord
{
	/**
	 * @brief	[Content\Comment]	Database Column Map
	 */
	protected static $databaseColumnMap = array();
	
	/**
	 * @brief	[Content]	Key for hide reasons
	 */
	public static $hideLogKey = NULL;
	
	/**
	 * @brief	[Content\Comment]	Language prefix for forms
	 */
	public static $formLangPrefix = '';

	/**
	 * @brief	Include In Sitemap
	 */
	public static $includeInSitemap = TRUE;
	
	/**
	 * @brief	Reputation Store
	 */
	protected $reputation;
	
	/**
	 * @brief	Can this content be moderated normally from the front-end (will be FALSE for things like Pages and Commerce Products)
	 */
	public static $canBeModeratedFromFrontend = TRUE;
	
	/**
	 * Should posting this increment the poster's post count?
	 *
	 * @param	\IPS\Node\Model|NULL	$container	Container
	 * @return	void
	 */
	public static function incrementPostCount( \IPS\Node\Model $container = NULL )
	{
		return TRUE;
	}

	/**
	 * Post count for member
	 *
	 * @param	\IPS\Member	$member	The memner
	 * @return	int
	 */
	public static function memberPostCount( \IPS\Member $member )
	{
		return ( isset( static::$databaseColumnMap['author'] ) and static::incrementPostCount() ) ? \IPS\Db::i()->select( 'COUNT(*)', static::$databaseTable, array( static::$databasePrefix . static::$databaseColumnMap['author'] . '=?', $member->member_id ) )->first() : 0;
	}

	/**
	 * Post count for member
	 *
	 * @param	\IPS\Member	$member	The memner
	 * @return	int
	 */
	public static function rawMemberPostCount( \IPS\Member $member )
	{
		return static::memberPostCount( $member );
	}
	
	/**
	 * Members with most contributions
	 *
	 * @param	int	$count	The number of results to return
	 * @return	array
	 */
	public static function mostContributions( $count = 5 )
	{
		if( !isset( static::$databaseColumnMap['author'] ) )
		{
			return array( 'counts' => NULL, 'members' => NULL );
		}

		$where = array();
		if( isset( static::$databaseColumnMap['approved'] ) )
		{
			$approvedColumn = static::$databasePrefix . static::$databaseColumnMap['approved'];
			$where[] = array( "{$approvedColumn} = 1" );
		}
		if( isset( static::$databaseColumnMap['hidden'] ) )
		{
			$hiddenColumn = static::$databasePrefix . static::$databaseColumnMap['hidden'];
			$where[] = array( "{$hiddenColumn} = 0" );
		}

		$authorColumn = static::$databasePrefix . static::$databaseColumnMap['author'];
		$members = \IPS\Db::i()->select( "count(*) as sum, {$authorColumn}", static::$databaseTable, $where, 'sum DESC', array( 0, $count ), array( static::$databasePrefix . static::$databaseColumnMap['author'] ) );

		$contributors = array();
		$counts = array();
		foreach ( $members as $member )
		{
			$contributors[] = $member[ $authorColumn ];
			$counts[ $member[ $authorColumn ] ] = $member[ 'sum' ];
		}

		if ( empty( $contributors ) )
		{
			return array( 'counts' => NULL, 'members' => NULL );
		}

		return array( 'counts' => $counts, 'members' => new \IPS\Patterns\ActiveRecordIterator( \IPS\Db::i()->select( '*', 'core_members', array( \IPS\Db::i()->in( 'member_id', $contributors ) ), "FIND_IN_SET( member_id, '" . implode( ",", $contributors) . "' )" ), 'IPS\Member' ) );
	}
		
	/**
	 * Load and check permissions
	 *
	 * @param	mixed				$id		ID
	 * @param	\IPS\Member|NULL	$member	Member, or NULL for logged in member
	 * @return	static
	 * @throws	\OutOfRangeException
	 */
	public static function loadAndCheckPerms( $id, \IPS\Member $member = NULL )
	{
		$obj = static::load( $id );
		
		$member = $member ?: \IPS\Member::loggedIn();
		if ( !$obj->canView( $member ) )
		{
			throw new \OutOfRangeException;
		}

		return $obj;
	}
	
	/**
	 * Construct ActiveRecord from database row
	 *
	 * @param	array	$data							Row from database table
	 * @param	bool	$updateMultitonStoreIfExists	Replace current object in multiton store if it already exists there?
	 * @return	static
	 */
	public static function constructFromData( $data, $updateMultitonStoreIfExists = TRUE )
    {
	    if ( isset( $data[ static::$databaseTable ] ) and \is_array( $data[ static::$databaseTable ] ) )
	    {
	        /* Add author data to multiton store to prevent ->author() running another query later */
	        if ( isset( $data['author'] ) and \is_array( $data['author'] ) )
	        {
	           	$author = \IPS\Member::constructFromData( $data['author'], FALSE );

	            if ( isset( $data['author_pfields'] ) )
	            {
		            unset( $data['author_pfields']['member_id'] );
					$author->contentProfileFields( $data['author_pfields'] );
	            }
	        }

	        /* Load content */
	        $obj = parent::constructFromData( $data[ static::$databaseTable ], $updateMultitonStoreIfExists );

			/* Add reputation if it was passed*/
			if ( isset( $data['core_reputation_index'] ) and \is_array( $data['core_reputation_index'] ) )
			{
				$obj->_data = array_merge( $obj->_data, $data['core_reputation_index'] );
			}

			/* Return */
			return $obj;
		}
		else
		{
			return parent::constructFromData( $data, $updateMultitonStoreIfExists );
		}
    }

    /**
     * @brief	Cached social groups
     */
    protected static $_cachedSocialGroups = array();
    
    /**
	 * Get WHERE clause for Social Group considerations for getItemsWithPermission
	 *
	 * @param	string		$socialGroupColumn	The column which contains the social group ID
	 * @param	\IPS\Member	$member				The member (NULL to use currently logged in member)
	 * @return	string
	 */
	public static function socialGroupGetItemsWithPermissionWhere( $socialGroupColumn, $member )
	{			
		$socialGroups = array();
		
		$member = $member ?: \IPS\Member::loggedIn();
		if ( $member->member_id )
		{
			if( !array_key_exists( $member->member_id, static::$_cachedSocialGroups ) )
			{
				static::$_cachedSocialGroups[ $member->member_id ] = iterator_to_array( \IPS\Db::i()->select( 'group_id', 'core_sys_social_group_members', array( 'member_id=?', $member->member_id ) ) );
			}

			$socialGroups = static::$_cachedSocialGroups[ $member->member_id ];
		}

		if ( \count( $socialGroups ) )
		{
			return $socialGroupColumn . '=0 OR ( ' . \IPS\Db::i()->in( $socialGroupColumn, $socialGroups ) . ' )';
		}
		else
		{
			return $socialGroupColumn . '=0';
		}
	}

	/**
	 * Check the request for legacy parameters we may need to redirect to
	 *
	 * @return	NULL|\IPS\Http\Url
	 */
	public function checkForLegacyParameters()
	{
		$paramsToSet	= array();
		$paramsToUnset	= array();

		/* st=20 needs to go to page=2 (or whatever the comments per page setting is set to) */
		if( isset( \IPS\Request::i()->st ) )
		{
			$commentsPerPage = static::getCommentsPerPage();

			$paramsToSet['page']	= floor( \intval( \IPS\Request::i()->st ) / $commentsPerPage ) + 1;
			$paramsToUnset[]		= 'st';
		}

		/* Did we have any? */
		if( \count( $paramsToSet ) )
		{
			$url = $this->url();

			if( \count( $paramsToUnset ) )
			{
				$url = $url->stripQueryString( $paramsToUnset );
			}

			$url = $url->setQueryString( $paramsToSet );

			return $url;
		}

		return NULL;
	}

	/**
	 * Get mapped value
	 *
	 * @param	string	$key	date,content,ip_address,first
	 * @return	mixed
	 */
	public function mapped( $key )
	{
		if ( isset( static::$databaseColumnMap[ $key ] ) )
		{
			$field = static::$databaseColumnMap[ $key ];
			
			if ( \is_array( $field ) )
			{
				$field = array_pop( $field );
			}
			
			return $this->$field;
		}
		return NULL;
	}
	
	/**
	 * Get author
	 *
	 * @return	\IPS\Member
	 */
	public function author()
	{
		if ( $this->mapped('author') or !isset( static::$databaseColumnMap['author_name'] ) or !$this->mapped('author_name') )
		{
			return \IPS\Member::load( $this->mapped('author') );
		}
		else
		{
			$guest = new \IPS\Member;
			$guest->name = $this->mapped('author_name');
			return $guest;
		}
	}
	
	/**
	 * Returns the content
	 *
	 * @return	string
	 */
	public function content()
	{
		return $this->mapped('content');
	}

	/**
	 * Text for use with data-ipsTruncate
	 * Returns the post with paragraphs turned into line breaks
	 *
	 * @param	bool		$oneLine	If TRUE, will use spaces instead of line breaks. Useful if using a single line display.
	 * @param	int|null	$length		If supplied, and $oneLine is set to TRUE, the returned content will be truncated to this length
	 * @return	string
	 * @note	For now we are removing all HTML. If we decide to change this to remove specific tags in future, we can use \IPS\Text\Parser::removeElements( $this->content() )
	 */
	public function truncated( $oneLine=FALSE, $length=500 )
	{	
		/* Specifically remove quotes, any scripts (which someone with HTML posting allowed may have legitimately enabled, and spoilers (to prevent contents from being revealed) */
		$text = \IPS\Text\Parser::removeElements( $this->content(), array( 'blockquote', 'script', 'div[class=ipsSpoiler]' ) );
		
		/* Convert headers and paragraphs into line breaks or just spaces */
		$text = str_replace( array( '</p>', '</h1>', '</h2>', '</h3>', '</h4>', '</h5>', '</h6>' ), ( $oneLine ? ' ' : '<br>' ), $text );

		if( $oneLine === TRUE )
		{
			$text = str_replace( '<br>', ' ', $text );
		}

		/* Add a space at the end of list items to prevent two list items from running into each other */
		$text = str_replace( '</li>', ' </li>', $text );
		
		/* Remove all HTML apart from <br>s*/
		$text = strip_tags( $text, ( $oneLine === TRUE ) ? NULL : '<br>' );
		
		/* Remove any <br>s from the start so there isn't just blank space at the top, but maintaining <br>s elsewhere */
		$text = preg_replace( '/^(\s|<br>|' . \chr(0xC2) . \chr(0xA0) . ')+/', '', $text );

		/* Truncate to length, if appropriate */
		if( $oneLine === TRUE AND $length > 0 )
		{
			$text = mb_substr( $text, 0, $length );
		}
		
		/* Return */
		return $text;
	}
	
	/**
	 * Delete Record
	 *
	 * @return	void
	 */
	public function delete()
	{
		$idColumn = static::$databaseColumnId;
		
		if ( \IPS\IPS::classUsesTrait( $this, 'IPS\Content\Reactable' ) )
		{
			\IPS\Db::i()->delete( 'core_reputation_index', array( 'app=? AND type=? AND type_id=?', static::$application, $this->reactionType(), $this->$idColumn ) );
		}
		
		if ( \IPS\IPS::classUsesTrait( $this, 'IPS\Content\Reportable' ) )
		{
			$this->deleteReport();
		}

		/* Remove any entries in the promotions table */
		\IPS\Db::i()->delete( 'core_social_promote', array( 'promote_class=? AND promote_class_id=?', \get_class( $this ), $this->$idColumn ) );
		
		\IPS\Db::i()->delete( 'core_deletion_log', array( "dellog_content_class=? AND dellog_content_id=?", \get_class( $this ), $this->$idColumn ) );

		if ( static::$hideLogKey )
		{
			$idColumn = static::$databaseColumnId;
			\IPS\Db::i()->delete('core_soft_delete_log', array('sdl_obj_id=? AND sdl_obj_key=?', $this->$idColumn, static::$hideLogKey));
		}
		
		parent::delete();

		$this->expireWidgetCaches();
		$this->adjustSessions();
	}

	/**
	 * Is this a future entry?
	 *
	 * @return bool
	 */
	public function isFutureDate()
	{
		if ( $this instanceof \IPS\Content\FuturePublishing )
		{
			if ( isset( static::$databaseColumnMap['is_future_entry'] ) and isset( static::$databaseColumnMap['future_date'] ) )
			{
				$column = static::$databaseColumnMap['future_date'];
				if ( $this->$column > time() )
				{
					return TRUE;
				}
			}
		}

		return FALSE;
	}

	/**
	 * Return the tooltip blurb for future entries
	 *
	 * @return string
	 */
	public function futureDateBlurb()
	{
		$column = static::$databaseColumnMap['future_date'];
		$time   = \IPS\DateTime::ts( $this->$column );
		return  \IPS\Member::loggedIn()->language()->addToStack("content_future_date_blurb", FALSE, array( 'sprintf' => array( $time->localeDate(), $time->localeTime() ) ) );
	}
	
	/**
	 * Check comment against profanity filters
	 *
	 * @note	We do not save here, as saving is done in user-land code after
	 * @param	bool	$first	Is this the first comment?
	 * @param	bool	$edit	Are we editing or merging (true) or is this a new comment (false)?
	 * @param	string|NULL			$content	The content to check - useful for if the content needs to be checked first, before it gets saved to the database.
	 * @param	string|NULL|bool	$title		The title of the content to check, or NULL to check the current title, or FALSE to not check at all.
	 * @return	bool	Whether to send unapproved notifications
	 */
	public function checkProfanityFilters( $first=FALSE, $edit=TRUE, $content=NULL, $title=NULL )
	{
		/* Set our content */
		$content = $content ?: $this->content();
		
		/* We need our item */
		$item = $this;
		if ( $this instanceof \IPS\Content\Comment )
		{
			$item = $this->item();
			
			if ( $item::$firstCommentRequired AND $first AND $title !== FALSE )
			{
				$title = $title ?: $item->mapped('title');
			}
		}
		else
		{
			if ( $title !== FALSE )
			{
				$title = $title ?: $this->mapped('title');
			}
		}

		/* And the author */
		$member = $this->author();

		/* Pass this through our profanity filters to see if it needs to be mod queued */
		$sendNotifications = FALSE;
		
		/* Do we need to recount item comments and container comments? */
		$recount = FALSE;
		
		try
		{
			$hiddenByFilter = FALSE;
			/* If this is a content item that doesn't require a first comment, or a comment that isn't the first comment, this just pass the content as it stands. */
			if ( !$this->hidden() AND ( ( $this instanceof \IPS\Content\Item ) OR ( $this instanceof \IPS\Content\Comment AND ( !$item::$firstCommentRequired OR ( $item::$firstCommentRequired AND !$first ) ) ) ) AND !$member->group['g_bypass_badwords'] )
			{
				$hiddenByFilter = \IPS\core\Profanity::hiddenByFilters( $content );
				
				if ( $title AND !$hiddenByFilter )
				{
					$hiddenByFilter = \IPS\core\Profanity::hiddenByFilters( $title );
				}
			}
			/* If the comment is hidden (probably requires moderator approval) and this is the first post in the item that requires a comment (i.e. a post in a topic), then we need to set the item as requiring approval instead. */
			elseif ( $item instanceof \IPS\Content\Hideable AND !$member->group['g_bypass_badwords'] AND ( $item::$firstCommentRequired and $first ) )
			{
				$itemHiddenByFilter = \IPS\core\Profanity::hiddenByFilters( $content );
				
				if ( $title AND !$itemHiddenByFilter )
				{
					$itemHiddenByFilter = \IPS\core\Profanity::hiddenByFilters( $title );
				}
				
				if ( isset( $item::$databaseColumnMap['approved'] ) and $itemHiddenByFilter )
				{
					/* 'approved' is easy, clear and concise */
					$column = $item::$databaseColumnMap['approved'];
					$item->$column = 0;
					$item->save();
					$recount = TRUE;
				}
				elseif ( isset( $item::$databaseColumnMap['hidden'] ) and $itemHiddenByFilter )
				{
					/* 'hidden' is backwards */
					$column = $item::$databaseColumnMap['hidden'];
					$item->$column = 1;
					$item->save();
					$recount = TRUE;
				}
				
				if( $edit === TRUE )
				{
					$sendNotifications = TRUE;
				}
			}
		}
		catch( \BadMethodCallException $e ) { }
		
		if ( $hiddenByFilter === TRUE )
		{
			$recount = TRUE;
			if ( isset( static::$databaseColumnMap['approved'] ) )
			{
				/* 'approved' is easy, clear and concise */
				$column = static::$databaseColumnMap['approved'];
				$this->$column = 0;
			}
			else if ( isset( static::$databaseColumnMap['hidden'] ) )
			{
				/* 'hidden' is backwards */
				$column = static::$databaseColumnMap['hidden'];
				$this->$column = 1;
			}

			$sendNotifications = TRUE;
		}
		
		if ( $recount === TRUE )
		{
			$item->resyncCommentCounts();
			$item->resyncLastComment();
			$item->save();
			
			if ( $container = $item->containerWrapper() )
			{
				$container->resetCommentCounts();
				$container->save();
			}
		}


		return $sendNotifications;
	}
	
	/**
	 * Content is hidden?
	 *
	 * @return	int
	 *	@li -3 is a post made by a guest using the "post before register" feature
	 *	@li -2 is pending deletion
	 * 	@li	-1 is hidden having been hidden by a moderator
	 * 	@li	0 is unhidden
	 *	@li	1 is hidden needing approval
	 * @note	The actual column may also contain 2 which means the item is hidden because the parent is hidden, but it is not hidden in itself. This method will return -1 in that case.
	 *
	 * @note    A piece of content (item and comment) can have an alias for hidden OR approved.
	 *          With hidden: 0=not hidden, 1=hidden (needs moderator approval), -1=hidden by moderator, 2=parent item is hidden, -2=pending deletion, -3=guest post before register
	 *          With approved: 1=not hidden, 0=hidden (needs moderator approval), -1=hidden by moderator, -2=pending deletion, -3=guest post before register
	 *
	 *          User posting has moderator approval set: When adding an unapproved ITEM (approved=0, hidden=1) you should *not* increment container()->_comments but you should update container()->_unapprovedItems
	 *          User posting has moderator approval set: When adding an unapproved COMMENT (approved=0, hidden=1) you should *not* increment item()->num_comments in item or container()->_comments but you should update item()->unapproved_comments and container()->_unapprovedComments
	 *
	 *          User post is hidden by moderator (approved=-1, hidden=0) you should decrement item()->num_comments and decrement container()->_comments but *not* increment item()->unapproved_comments or container()->_unapprovedComments
	 *          User item is hidden by a moderator (approved=-1, hidden=0) you should decrement container()->comments and subtract comment count from container()->_comments, but *not* increment container()->_unapprovedComments
	 *
	 *          Moderator hides item (approved=-1, hidden=-1) you should substract num_comments from container()->_comments. Comments inside item are flagged as approved=-1, hidden=2 but item()->num_comments should not be substracted from
	 *
	 *          Comments with a hidden value of 2 should increase item()->num_comments but not container()->_comments
	 * @throws	\RuntimeException
	 */
	public function hidden()
	{
		if ( $this instanceof \IPS\Content\Hideable )
		{
			if ( isset( static::$databaseColumnMap['hidden'] ) )
			{
				$column = static::$databaseColumnMap['hidden'];
				return ( $this->$column == 2 ) ? -1 : \intval( $this->$column );
			}
			elseif ( isset( static::$databaseColumnMap['approved'] ) )
			{
				$column = static::$databaseColumnMap['approved'];
				if ( $this->$column == -2 or $this->$column == -3 )
				{
					return \intval( $this->$column );
				}
				return $this->$column == -1 ? \intval( $this->$column ) : \intval( !$this->$column );
			}
			else
			{
				throw new \RuntimeException;
			}
		}
		
		return 0;
	}
	
	/**
	 * Can see moderation tools
	 *
	 * @note	This is used generally to control if the user has permission to see multi-mod tools. Individual content items may have specific permissions
	 * @param	\IPS\Member|NULL	$member	The member to check for or NULL for the currently logged in member
	 * @param	\IPS\Node\Model|NULL		$container	The container
	 * @return	bool
	 */
	public static function canSeeMultiModTools( \IPS\Member $member = NULL, \IPS\Node\Model $container = NULL )
	{
		return static::modPermission( 'pin', $member, $container ) or static::modPermission( 'unpin', $member, $container ) or static::modPermission( 'feature', $member, $container ) or static::modPermission( 'unfeature', $member, $container ) or static::modPermission( 'edit', $member, $container ) or static::modPermission( 'hide', $member, $container ) or static::modPermission( 'unhide', $member, $container ) or static::modPermission( 'delete', $member, $container );
	}

	/**
	 * Return a list of groups that cannot see this item
	 *
	 * @return 	NULL|Array
	 */
	public function cannotViewGroups()
	{
		$groups = array();
		foreach( \IPS\Member\Group::groups() as $group )
		{
			if ( $this instanceof \IPS\Content\Comment )
			{
				if ( ! $this->item()->can( 'view', $group ) )
				{
					$groups[] = $group->name;
				}
			}
			else
			{
				if ( ! $this->can( 'view', $group, FALSE ) )
				{
					$groups[] = $group->name;
				}
			}
		}

		return \count( $groups ) ? $groups : NULL;
	}
	
	/**
	 * Check Moderator Permission
	 *
	 * @param	string						$type		'edit', 'hide', 'unhide', 'delete', etc.
	 * @param	\IPS\Member|NULL			$member		The member to check for or NULL for the currently logged in member
	 * @param	\IPS\Node\Model|NULL		$container	The container
	 * @return	bool
	 */
	public static function modPermission( $type, \IPS\Member $member = NULL, \IPS\Node\Model $container = NULL )
	{
		/* Compatibility checks */
		if ( ( $type == 'hide' or $type == 'unhide' ) and !\in_array( 'IPS\Content\Hideable', class_implements( \get_called_class() ) ) )
		{
			return FALSE;
		}
		if ( ( $type == 'pin' or $type == 'unpin' ) and !\in_array( 'IPS\Content\Pinnable', class_implements( \get_called_class() ) ) )
		{
			return FALSE;
		}
		if ( ( $type == 'feature' or $type == 'unfeature' ) and !\in_array( 'IPS\Content\Featurable', class_implements( \get_called_class() ) ) )
		{
			return FALSE;
		}
		if ( ( $type == 'future_publish' ) and !\in_array( 'IPS\Content\FuturePublishing', class_implements( \get_called_class() ) ) )
		{
			return FALSE;
		}

		/* If this is called from a gateway script, i.e. email piping, just return false as we are a "guest" */
		if( $member === NULL AND !\IPS\Dispatcher::hasInstance() )
		{
			return FALSE;
		}
		
		/* Load Member */
		$member = $member ?: \IPS\Member::loggedIn();

		/* Global permission */
		if ( $member->modPermission( "can_{$type}_content" ) )
		{
			return TRUE;
		}
		/* Per-container permission */
		elseif ( $container )
		{
			return $container->modPermission( $type, $member, static::getContainerModPermissionClass() ?: \get_called_class() );
		}
		
		/* Still here? return false */
		return FALSE;
	}

	/**
	 * Get the content to use for mod permission checks
	 *
	 * @return	string|NULL
	 * @note	By default we will return NULL and the container check will execute against Node::$contentItemClass, however
	 *	in some situations we may need to override this (i.e. for Gallery Albums)
	 */
	protected static function getContainerModPermissionClass()
	{
		return NULL;
	}
		
	/**
	 * Do Moderator Action
	 *
	 * @param	string				$action	The action
	 * @param	\IPS\Member|NULL	$member	The member doing the action (NULL for currently logged in member)
	 * @param	string|NULL			$reason	Reason (for hides)
	 * @param	bool				$immediately Delete Immediately
	 * @return	void
	 * @throws	\OutOfRangeException|\InvalidArgumentException|\RuntimeException
	 */
	public function modAction( $action, \IPS\Member $member = NULL, $reason = NULL, $immediately = FALSE )
	{
		if( $action === 'approve' )
		{
			$action	= 'unhide';
		}

		/* Check it's a valid action */
		if ( !\in_array( $action, array( 'pin', 'unpin', 'feature', 'unfeature', 'hide', 'unhide', 'move', 'lock', 'unlock', 'delete', 'publish', 'restore', 'restoreAsHidden' ) ) )
		{
			throw new \InvalidArgumentException;
		}
		
		/* And that we can do it */
		$toCheck = $action;
		if ( $action == 'restoreAsHidden' )
		{
			$toCheck = 'restore';
		}
		
		$methodName = 'can' . mb_ucfirst( $toCheck );
		if ( !$this->$methodName( $member ) )
		{
			throw new \OutOfRangeException;
		}
		
		/* Log */
		\IPS\Session::i()->modLog( 'modlog__action_' . $action, array( static::$title => TRUE, $this->url()->__toString() => FALSE, $this->mapped('title') ?: ( method_exists( $this, 'item' ) ? $this->item()->mapped('title') : NULL ) => FALSE ), ( $this instanceof \IPS\Content\Item ) ? $this : $this->item() );
		
		/* These ones just need a property setting */
		if ( \in_array( $action, array( 'pin', 'unpin', 'feature', 'unfeature', 'lock', 'unlock' ) ) )
		{
			$val = TRUE;
			switch ( $action )
			{
				case 'unpin':
					$val = FALSE;
				case 'pin':
					$column = static::$databaseColumnMap['pinned'];
					break;
				
				case 'unfeature':
					$val = FALSE;
				case 'feature':
					$column = static::$databaseColumnMap['featured'];
					break;
				
				case 'unlock':
					$val = FALSE;
				case 'lock':
					if ( isset( static::$databaseColumnMap['locked'] ) )
					{
						$column = static::$databaseColumnMap['locked'];
					}
					else
					{
						$val = $val ? 'closed' : 'open';
						$column = static::$databaseColumnMap['status'];
					}
					break;
			}
			$this->$column = $val;
			$this->save();

			return;
		}
		
		/* Hide is a tiny bit more complicated */
		elseif ( $action === 'hide' )
		{
			$this->hide( $member, $reason );
			return;
		}
		elseif ( $action === 'unhide' )
		{
			$this->unhide( $member );
			return;
		}
		
		/* Delete is just a method */
		elseif ( $action === 'delete' )
		{
			/* If we are retaining content for a period of time, we need to just hide it instead for deleting later - this only works, though, with items that implement \IPS\Content\Hideable */
			if ( \IPS\Settings::i()->dellog_retention_period AND ( $this instanceof \IPS\Content\Hideable ) AND $immediately === FALSE )
			{
				$this->logDelete( $member );
				return;
			}
			
			$idColumn = static::$databaseColumnId;
			$this->delete();
			return;
		}
		
		/* Restore is just a method */
		elseif ( $action === 'restore' )
		{
			$this->restore();
			return;
		}
		
		/* Restore As Hidden is just a method */
		elseif ( $action === 'restoreAsHidden' )
		{
			$this->restore( TRUE );
			return;
		}

		/* Publish is just a method */
		elseif ( $action === 'publish' )
		{
			$this->publish();
			return;
		}

		/* Move is just a method */
		elseif ( $action === 'move' )
		{
			$args	= \func_get_args();
			$this->move( $args[2][0], $args[2][1] );
			return;
		}
	}
	
	/**
	 * Log for deletion later
	 *
	 * @param	\IPS\Member|NULL 	$member	The member, NULL for currently logged in, or FALSE for no member
	 * @return	void
	 */
	public function logDelete( $member = NULL )
	{
		if( $member === NULL )
		{
			$member = \IPS\Member::loggedIn();
		}
		
		/* Log it! */
		$log = new \IPS\core\DeletionLog;
		$log->setContentAndMember( $this, $member );
		$log->save();
		
		if ( isset( static::$databaseColumnMap['hidden'] ) )
		{
			$column = static::$databaseColumnMap['hidden'];
		}
		else if ( isset( static::$databaseColumnMap['approved'] ) )
		{
			$column = static::$databaseColumnMap['approved'];
		}
		
		$this->$column = -2;
		$this->save();
		
		if ( $this instanceof \IPS\Content\Comment )
		{
			$item = $this->item();
			
			/* Update last comment stuff */
			$item->resyncLastComment();

			/* Update last review stuff */
			$item->resyncLastReview();

			/* Update number of comments */
			$item->resyncCommentCounts();

			/* Update number of reviews */
			$item->resyncReviewCounts();

			/* Save*/
			$item->save();
		}
		
		if ( $this instanceof \IPS\Content\Tags )
		{
			\IPS\Db::i()->update( 'core_tags_perms', array( 'tag_perm_visible' => 0 ), array( 'tag_perm_aai_lookup=?', $this->tagAAIKey() ) );
		}
		
		try
		{
			if ( $this->container() )
			{
				$this->container()->resetCommentCounts();
				$this->container()->setLastComment();
				$this->container()->setLastReview();
				$this->container()->save();
			}
		}
		catch( \BadMethodCallException $e ) {}
		catch( \OutOfRangeException $e ) {}
	}
	
	/**
	 * Restore Content
	 *
	 * @param	bool	$hidden	Restore as hidden?
	 * @return	void
	 */
	public function restore( $hidden = FALSE )
	{
		try
		{
			$idColumn = static::$databaseColumnId;
			$log = \IPS\core\DeletionLog::constructFromData( \IPS\Db::i()->select( '*', 'core_deletion_log', array( "dellog_content_class=? AND dellog_content_id=?", \get_class( $this ), $this->$idColumn ) )->first() );
		}
		catch( \UnderflowException $e )
		{
			/* There's no deletion log record, but this shouldn't stop us from restoring */
		}
		
		/* Restoring as hidden? */
		if ( $hidden )
		{
			if ( isset( static::$databaseColumnMap['hidden'] ) )
			{
				$column = static::$databaseColumnMap['hidden'];
			}
			else if ( isset( static::$databaseColumnMap['approved'] ) )
			{
				$column = static::$databaseColumnMap['approved'];
			}
			
			$this->$column = -1;
		}
		else
		{
			if ( isset( static::$databaseColumnMap['hidden'] ) )
			{
				$column = static::$databaseColumnMap['hidden'];
				$this->$column = 0;
			}
			else if ( isset( static::$databaseColumnMap['approved'] ) )
			{
				$column = static::$databaseColumnMap['approved'];
				$this->$column = 1;
			}
		}
		
		if ( $this instanceof \IPS\Content\Tags AND !$hidden )
		{
			\IPS\Db::i()->update( 'core_tags_perms', array( 'tag_perm_visible' => 1 ), array( 'tag_perm_aai_lookup=?', $this->tagAAIKey() ) );
		}

		/* Save the changes */
		$this->save();

		/* Reindex the now hidden content - if this is a content item with comments or reviews, then make sure to do those too. */
		if ( $this instanceof \IPS\Content\Item AND ( isset( static::$commentClass ) OR isset( static::$reviewClass ) ) )
		{
			\IPS\Content\Search\Index::i()->index( ( static::$firstCommentRequired ) ? $this->firstComment() : $this );
			\IPS\Content\Search\Index::i()->indexSingleItem( $this );
		}
		else
		{
			/* Either this is a comment / review, or the item doesn't support comments or reviews, so we can just reindex it now. */
			\IPS\Content\Search\Index::i()->index( $this );
		}
		
		/* Delete the log */
		if ( isset( $log ) )
		{
			$log->delete();
		}

		/* Recount the container counters */
		if( $this->container() )
		{
			$this->container()->resetCommentCounts();
			$this->container()->setLastComment();
			$this->container()->setLastReview();
			$this->container()->save();
		}
	}
	
	/**
	 * Can restore?*
	 *
	 * @param	\IPS\Member|NULL	$member	The member, or currently logged in member
	 * @return	bool
	 */
	public function canRestore( $member=NULL )
	{
		$member = $member ?: \IPS\Member::loggedIn();
		return $member->modPermission('can_manage_deleted_content');
	}
	
	/**
	 * Give class a chance to inspect and manipulate search engine filters for streams
	 *
	 * @param	array 						$filters	Filters to be used for activity stream
	 * @param	\IPS\Content\Search\Query	$query		Search query object
	 * @return	void
	 */
	public static function searchEngineFiltering( &$filters, &$query )
	{
		/* Intentionally left blank but child classes can override */
	}
	
	/**
	 * Hide
	 *
	 * @param	\IPS\Member|NULL|FALSE	$member	The member doing the action (NULL for currently logged in member, FALSE for no member)
	 * @param	string					$reason	Reason
	 * @return	void
	 */
	public function hide( $member, $reason = NULL )
	{
		if ( isset( static::$databaseColumnMap['hidden'] ) )
		{
			$column = static::$databaseColumnMap['hidden'];
		}
		elseif ( isset( static::$databaseColumnMap['approved'] ) )
		{
			$column = static::$databaseColumnMap['approved'];
		}
		else
		{
			throw new \RuntimeException;
		}

		/* Already hidden? */
		if( $this->$column == -1 )
		{
			return;
		}

		$this->$column = -1;
		$this->save();
		$this->onHide( $member );
		
		if ( static::$hideLogKey )
		{
			$idColumn = static::$databaseColumnId;
			\IPS\Db::i()->delete( 'core_soft_delete_log', array( 'sdl_obj_id=? AND sdl_obj_key=?', $this->$idColumn, static::$hideLogKey ) );
			\IPS\Db::i()->insert( 'core_soft_delete_log', array(
				'sdl_obj_id'		=> $this->$idColumn,
				'sdl_obj_key'		=> static::$hideLogKey,
				'sdl_obj_member_id'	=> $member === FALSE ? 0 : \intval( $member ? $member->member_id : \IPS\Member::loggedIn()->member_id ),
				'sdl_obj_date'		=> time(),
				'sdl_obj_reason'	=> $reason,
				
			) );
		}
		
		if ( $this instanceof \IPS\Content\Tags )
		{
			\IPS\Db::i()->update( 'core_tags_perms', array( 'tag_perm_visible' => 0 ), array( 'tag_perm_aai_lookup=?', $this->tagAAIKey() ) );
		}

        /* Update search index */
        if ( $this instanceof \IPS\Content\Searchable )
        {
            \IPS\Content\Search\Index::i()->index( $this );
        }

		$this->expireWidgetCaches();
		$this->adjustSessions();
	}
	
	/**
	 * Unhide
	 *
	 * @param	\IPS\Member|NULL|FALSE	$member	The member doing the action (NULL for currently logged in member, FALSE for no member)
	 * @return	void
	 */
	public function unhide( $member )
	{
		/* If we're approving, we have to do extra stuff */
		$approving	= FALSE;
		$pbr		= FALSE;
		if ( $this->hidden() === 1 )
		{
			$approving = TRUE;
			if ( isset( static::$databaseColumnMap['approved_by'] ) and $member !== FALSE )
			{
				$column = static::$databaseColumnMap['approved_by'];
				$this->$column = $member ? $member->member_id : \IPS\Member::loggedIn()->member_id;
			}
			if ( isset( static::$databaseColumnMap['approved_date'] ) )
			{
				$column = static::$databaseColumnMap['approved_date'];
				$this->$column = time();
			}
		}
		elseif( $this->hidden() === -3 )
		{
			$pbr = TRUE;
		}

		/* Now do the actual stuff */
		if ( isset( static::$databaseColumnMap['hidden'] ) )
		{
			$column = static::$databaseColumnMap['hidden'];

			/* Already approved? */
			if( $this->$column == 0 )
			{
				return;
			}

			$this->$column = 0;
		}
		elseif ( isset( static::$databaseColumnMap['approved'] ) )
		{
			$column = static::$databaseColumnMap['approved'];

			/* Already approved? */
			if( $this->$column == 1 )
			{
				return;
			}

			$this->$column = 1;
		}
		else
		{
			throw new \RuntimeException;
		}
		$this->save();
		$this->onUnhide( ( $approving OR ( $pbr AND $this->hidden() === 0 ) ), $member );

		if ( static::$hideLogKey )
		{
			$idColumn = static::$databaseColumnId;
			\IPS\Db::i()->delete('core_soft_delete_log', array('sdl_obj_id=? AND sdl_obj_key=?', $this->$idColumn, static::$hideLogKey));
		}

		/* And update the tags perm cache */
		if ( $this instanceof \IPS\Content\Tags )
		{
			\IPS\Db::i()->update( 'core_tags_perms', array( 'tag_perm_visible' => 1 ), array( 'tag_perm_aai_lookup=?', $this->tagAAIKey() ) );
		}
		
		/* Update search index */
		if ( $this instanceof \IPS\Content\Searchable )
		{
			\IPS\Content\Search\Index::i()->index( $this );
		}
		
		/* Update report center stuff */
		if ( \IPS\IPS::classUsesTrait( $this, 'IPS\Content\Reportable' ) )
		{
			$this->moderated( 'unhide' );
		}
		
		/* Send notifications if necessary */
		if ( $approving )
		{
			$this->sendApprovedNotification();
		}

		$this->expireWidgetCaches();
		$this->adjustSessions();
	}

	/**
	 * @brief	Hidden blurb cache
	 */
	protected $hiddenBlurb	= NULL;

	/**
	 * Blurb for when/why/by whom this content was hidden
	 *
	 * @return	string
	 */
	public function hiddenBlurb()
	{
		if ( !( $this instanceof \IPS\Content\Hideable ) or !static::$hideLogKey )
		{
			throw new \BadMethodCallException;
		}
		
		if( $this->hiddenBlurb === NULL )
		{
			try
			{
				$idColumn = static::$databaseColumnId;
				$log = \IPS\Db::i()->select( '*', 'core_soft_delete_log', array( 'sdl_obj_id=? AND sdl_obj_key=?', $this->$idColumn, static::$hideLogKey ) )->first();
				
				if ( $log['sdl_obj_member_id'] )
				{
					$this->hiddenBlurb = \IPS\Member::loggedIn()->language()->addToStack('hidden_blurb', FALSE, array( 'sprintf' => array( \IPS\Member::load( $log['sdl_obj_member_id'] )->name, \IPS\DateTime::ts( $log['sdl_obj_date'] )->relative(),  $log['sdl_obj_reason'] ?: \IPS\Member::loggedIn()->language()->addToStack('hidden_no_reason') ) ) );
				}
				else
				{
					$this->hiddenBlurb = \IPS\Member::loggedIn()->language()->addToStack('hidden_blurb_no_member', FALSE, array( 'sprintf' => array( \IPS\DateTime::ts( $log['sdl_obj_date'] )->relative(), $log['sdl_obj_reason'] ?: \IPS\Member::loggedIn()->language()->addToStack('hidden_no_reason') ) ) );
				}
			
			}
			catch ( \UnderflowException $e )
			{
				$this->hiddenBlurb = \IPS\Member::loggedIn()->language()->addToStack('hidden');
			}
		}

		return $this->hiddenBlurb;
	}
	
	/**
	 * Blurb for when/why/by whom this content was deleted
	 *
	 * @return	string
	 * @throws \BadMethodCallException
	 */
	public function deletedBlurb()
	{
		if ( !( $this instanceof \IPS\Content\Hideable ) )
		{
			throw new \BadMethodCallException;
		}
		
		try
		{
			$idColumn = static::$databaseColumnId;
			$log = \IPS\core\DeletionLog::constructFromData( \IPS\Db::i()->select( '*', 'core_deletion_log', array( "dellog_content_class=? AND dellog_content_id=?", \get_class( $this ), $this->$idColumn ) )->first() );
			if( $log->_deleted_by )
			{
				return \IPS\Member::loggedIn()->language()->addToStack( 'deletion_blurb', FALSE, array( 'sprintf' => array( $log->_deleted_by->name, $log->deleted_date->fullYearLocaleDate(), $log->deletion_date->fullYearLocaleDate() ) ) );
			}
			else
			{
				return \IPS\Member::loggedIn()->language()->addToStack( 'deletion_blurb_no_member', FALSE, array( 'sprintf' => array( $log->deletion_date->fullYearLocaleDate() ) ) );
			}
		}
		catch( \UnderflowException $e )
		{
			return \IPS\Member::loggedIn()->language()->addToStack('deleted');
		}
	}
	
	/**
	 * Can promote this comment/item?
	 *
	 * @param	\IPS\Member|NULL	$member	The member to check for (NULL for currently logged in member)
	 * @return	boolean
	 */
	public function canPromoteToSocialMedia( $member=NULL )
	{
		return \IPS\core\Promote::canPromote( $member );
	}

	/**
	 * @brief	Have we already reported?
	 */
	protected $alreadyReported = NULL;
	
	/**
	 * Can report?
	 *
	 * @param	\IPS\Member|NULL	$member	The member to check for (NULL for currently logged in member)
	 * @return	TRUE|string			TRUE or a language string for why not
	 * @note	This requires a few queries, so don't run a check in every template
	 */
	public function canReport( $member=NULL )
	{
		$member = $member ?: \IPS\Member::loggedIn();
		
		/* Is this type of comment reportabe? */
		if ( !( \IPS\IPS::classUsesTrait( $this, 'IPS\Content\Reportable' ) ) )
		{
			return 'generic_error';
		}
		
		/* Can the member report content? */
		if ( $member->group['g_can_report'] != '1' AND !\in_array( \get_class( $this ), explode( ',', $member->group['g_can_report'] ) ) )
		{
			return 'no_module_permission';
		}
		
		/* Can they view this? */
		if ( !$this->canView() )
		{
			return 'no_module_permission';
		}

		/* Have they already subitted a report? */
		if( $this->alreadyReported === TRUE )
		{
			return 'report_err_already_reported';
		}
		elseif( $this->alreadyReported === NULL )
		{
			/* Have we already prefetched it? */
			if ( ! isset( $this->reportData ) )
			{
				try
				{
					$idColumn = static::$databaseColumnId;
					$report = \IPS\Db::i()->select( 'id', 'core_rc_index', array( 'class=? AND content_id=?', \get_called_class(), $this->$idColumn ) )->first();
					$this->reportData = \IPS\Db::i()->select( '*', 'core_rc_reports', array( 'rid=? AND report_by=?', $report, $member->member_id ) )->first();
				}
				catch( \UnderflowException $e ){}
			}
			
			/* Check again */
			if ( isset( $this->reportData ) AND \is_array( $this->reportData ) )
			{
				if ( \IPS\Settings::i()->automoderation_report_again_mins )
				{ 
					if ( ( ( time() - $this->reportData['date_reported'] ) / 60 ) > \IPS\Settings::i()->automoderation_report_again_mins )
					{
						return TRUE;
					}
				}
				
				$this->alreadyReported = TRUE;
				return 'report_err_already_reported';
			}
			
			$this->alreadyReported = FALSE;
		}
		
		return TRUE;
	}

	/**
	 * Can report or revoke report?
	 * This method will return TRUE if the link to report content should be shown (which can occur even if you have already reported if you have permission to revoke your report)
	 *
	 * @param	\IPS\Member|NULL	$member	The member to check for (NULL for currently logged in member)
	 * @return	bool
	 * @note	This requires a few queries, so don't run a check in every template
	 */
	public function canReportOrRevoke( $member=NULL )
	{
		/* If we are allowed to report, then we can return TRUE. */
		if( $this->canReport( $member ) === TRUE )
		{
			return TRUE;
		}
		/* If we have already reported but automatic moderation is enabled, show the link so the user can revoke their report. */
		elseif( $this->alreadyReported === TRUE AND \IPS\Settings::i()->automoderation_enabled )
		{
			return TRUE;
		}

		return FALSE;
	}
	
	/**
	 * Report
	 *
	 * @param	string	$reportContent	Report content message from member
	 * @param	int		$reportType		Report type (see constants in \IPS\core\Reports\Report
	 * @return	\IPS\core\Reports\Report
	 * @throws	\UnexpectedValueException	If there is a permission error - you should only call this method after checking canReport
	 */
	public function report( $reportContent, $reportType=1 )
	{
		/* Permission check */
		if ( $this->canReport() !== TRUE )
		{
			throw new \UnexpectedValueException;
		}
		
		/* Find or create an index */
		$idColumn = static::$databaseColumnId;
		try
		{
			$index = \IPS\core\Reports\Report::load( $this->$idColumn, 'content_id', array( 'class=?', \get_called_class() ) );
			$index->num_reports = $index->num_reports + 1;
		}
		catch ( \OutOfRangeException $e )
		{
			$index = new \IPS\core\Reports\Report;
			$index->class = \get_called_class();
			$index->content_id = $this->$idColumn;
			$index->perm_id = $this->permId();
			$index->first_report_by = (int) \IPS\Member::loggedIn()->member_id;
			$index->first_report_date = time();
			$index->last_updated = time();
			$index->author = (int) $this->author()->member_id;
			$index->num_reports = 1;
			$index->num_comments = 0;
			$index->auto_moderation_exempt = 0;
		}

		/* Only set this to a new report if it is not already under review */
		if( $index->status != 2 )
		{
			$index->status = 1;
		}

		$index->save();

		/* Create a report */
		$reportInsert = array(
			'rid'			=> $index->id,
			'report'		=> $reportContent,
			'report_by'		=> (int) \IPS\Member::loggedIn()->member_id,
			'date_reported'	=> time(),
			'ip_address'	=> \IPS\Request::i()->ipAddress(),
			'report_type'	=> \IPS\Member::loggedIn()->member_id ? $reportType : 0
		);
		
		$insertID = \IPS\Db::i()->insert( 'core_rc_reports', $reportInsert );
		$reportInsert['id'] = $insertID;
		
		/* Run automatic moderation */
		$index->runAutomaticModeration();
		
		/* Send notification to mods */
		$moderators = array( 'm' => array(), 'g' => array() );
		foreach ( \IPS\Db::i()->select( '*', 'core_moderators' ) as $mod )
		{
			$canView = FALSE;
			if ( $mod['perms'] == '*' )
			{
				$canView = TRUE;
			}
			if ( $canView === FALSE )
			{
				$perms = json_decode( $mod['perms'], TRUE );
				
				if ( isset( $perms['can_view_reports'] ) AND $perms['can_view_reports'] === TRUE )
				{
					$canView = TRUE;
				}
			}
			if ( $canView === TRUE )
			{
				$moderators[ $mod['type'] ][] = $mod['id'];
			}
		}
		$notification = new \IPS\Notification( \IPS\Application::load('core'), 'report_center', $index, array( $index, $reportInsert, $this ) );
		foreach ( \IPS\Db::i()->select( '*', 'core_members', ( \count( $moderators['m'] ) ? \IPS\Db::i()->in( 'member_id', $moderators['m'] ) . ' OR ' : '' ) . \IPS\Db::i()->in( 'member_group_id', $moderators['g'] ) . ' OR ' . \IPS\Db::i()->findInSet( 'mgroup_others', $moderators['g'] ) ) as $member )
		{
			$notification->recipients->attach( \IPS\Member::constructFromData( $member ) );
		}
		$notification->send();
		
		/* Set flag so future calls to report methods return correct value */
		$this->alreadyReported = TRUE;
		$this->reportData = $reportInsert;

		/* Return */
		return $index;
	}
	
	/**
	 * Change IP Address
	 * @param	string		$ip		The new IP address
	 *
	 * @return void
	 */
	public function changeIpAddress( $ip )
	{
		if ( isset( static::$databaseColumnMap['ip_address'] ) )
		{
			$col = static::$databaseColumnMap['ip_address'];
			$this->$col = (string) $ip;
			$this->save();
		}
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

		foreach ( array( 'author', 'author_name', 'edit_member_name' ) as $k )
		{
			if ( isset( static::$databaseColumnMap[ $k ] ) )
			{
				$col = static::$databaseColumnMap[ $k ];
				switch ( $k )
				{
					case 'author':
						$this->$col = $newAuthor->member_id ? $newAuthor->member_id : 0;
						break;
					
					case 'author_name':
					case 'edit_member_name':
						/* Real name will contain the custom guest name if available or '' if not */
						$this->$col = $newAuthor->member_id ? $newAuthor->name : $newAuthor->real_name;
						break;
				}
			}
		}
		$this->save();

		if ( \IPS\Dispatcher::hasInstance() and \IPS\Dispatcher::i()->controllerLocation == 'front' and $log )
		{
			\IPS\Session::i()->modLog( 'modlog__action_changeauthor', array( static::$title => TRUE, $this->url()->__toString() => FALSE, $this->mapped('title') ?: ( method_exists( $this, 'item' ) ? $this->item()->mapped('title') : NULL ) => FALSE ), ( $this instanceof \IPS\Content\Item ) ? $this : $this->item() );
		}
	}
	
	/**
	 * Get HTML for search result display
	 *
	 * @param	array		$indexData		Data from the search index
	 * @param	array		$authorData		Basic data about the author. Only includes columns returned by \IPS\Member::columnsForPhoto()
	 * @param	array		$itemData		Basic data about the item. Only includes columns returned by item::basicDataColumns()
	 * @param	array|NULL	$containerData	Basic data about the container. Only includes columns returned by container::basicDataColumns()
	 * @param	array		$reputationData	Array of people who have given reputation and the reputation they gave
	 * @param	int|NULL	$reviewRating	If this is a review, the rating
	 * @param	bool		$iPostedIn		If the user has posted in the item
	 * @param	string		$view			'expanded' or 'condensed'
	 * @param	bool		$asItem	Displaying results as items?
	 * @param	bool		$canIgnoreComments	Can ignore comments in the result stream? Activity stream can, but search results cannot.
	 * @param	array		$template	Optional custom template
	 * @param	array		$reactions	Reaction Data
	 * @return	string
	 */
	public static function searchResult( array $indexData, array $authorData, array $itemData, array $containerData = NULL, array $reputationData, $reviewRating, $iPostedIn, $view, $asItem, $canIgnoreComments=FALSE, $template=NULL, $reactions=array() )
	{
		/* Item details */
		$itemClass = $indexData['index_class'];
		if ( \in_array( 'IPS\Content\Comment', class_parents( \get_called_class() ) ) )
		{
			$itemClass = static::$itemClass;
			$unread = $itemClass::unreadFromData( NULL, $indexData['index_date_updated'], $indexData['index_date_created'], $indexData['index_item_id'], $indexData['index_container_id'], FALSE );
		}
		else
		{
			$unread = static::unreadFromData( NULL, $indexData['index_date_updated'], $indexData['index_date_created'], $indexData['index_item_id'], $indexData['index_container_id'], FALSE );
		}
		$itemUrl = $itemClass::urlFromIndexData( $indexData, $itemData );
		
		/* Object URL */
		$indefiniteArticle = static::_indefiniteArticle( $containerData );
		$definiteArticle = static::_definiteArticle( $containerData );
		$definiteArticleUc = static::_definiteArticle( $containerData, NULL, array( 'ucfirst' => TRUE ) );
		if ( \in_array( 'IPS\Content\Comment', class_parents( \get_called_class() ) ) )
		{
			if ( \in_array( 'IPS\Content\Review', class_parents( \get_called_class() ) ) )
			{
				$objectUrl = $itemUrl->setQueryString( array( 'do' => 'findReview', 'review' => $indexData['index_object_id'] ) );
				$showRepUrl = $itemUrl->setQueryString( array( 'do' => 'showReactionsReview', 'review' => $indexData['index_object_id'] ) );
			}
			else
			{
				$objectUrl = $itemUrl->setQueryString( array( 'do' => 'findComment', 'comment' => $indexData['index_object_id'] ) );
				$showRepUrl = $itemUrl->setQueryString( array( 'do' => 'showReactionsComment', 'comment' => $indexData['index_object_id'] ) );
			}
			
			$indefiniteArticle = $itemClass::_indefiniteArticle( $containerData );
			$definiteArticle = $itemClass::_definiteArticle( $containerData );
			$definiteArticleUc = $itemClass::_definiteArticle( $containerData, NULL, array( 'ucfirst' => TRUE ) );
		}
		else
		{
			$objectUrl = $itemUrl;
			$showRepUrl = $itemUrl->setQueryString( 'do', 'showReactions' );
		}
		$articles = array( 'indefinite' => $indefiniteArticle, 'definite' => $definiteArticle, 'definite_uc' => $definiteArticleUc );
		
		/* Container details */
		$containerUrl = NULL;
		$containerTitle = NULL;
		if ( isset( $itemClass::$containerNodeClass ) )
		{
			$containerClass	= $itemClass::$containerNodeClass;
			$containerTitle	= $containerClass::titleFromIndexData( $indexData, $itemData, $containerData );
			$containerUrl	= $containerClass::urlFromIndexData( $indexData, $itemData, $containerData );
		}
				
		/* Reputation - if we are showing the total value, then we need to load them up and total up all of the values */
		if ( \IPS\Settings::i()->reaction_count_display == 'count' )
		{
			$repCount = 0;
			foreach( $reputationData AS $memberId => $reactionId )
			{
				try
				{
					$repCount += \IPS\Content\Reaction::load( $reactionId )->value;
				}
				catch( \OutOfRangeException $e ) {}
			}
		}
		else
		{
			$repCount = \count( $reputationData );
		}
		
		/* Snippet */
		$snippet = static::searchResultSnippet( $indexData, $authorData, $itemData, $containerData, $reputationData, $reviewRating, $view );
		
		if ( $template === NULL )
		{
			$template = array( \IPS\Theme::i()->getTemplate( 'system', 'core', 'front' ), 'searchResult' );
		}
		
		/* Return */
		return $template( $indexData, $articles, $authorData, $itemData, $unread, $asItem ? $itemUrl : $objectUrl, $itemUrl, $containerUrl, $containerTitle, $repCount, $showRepUrl, $snippet, $iPostedIn, $view, $canIgnoreComments, $reactions );
	}
	
	/**
	 * Get snippet HTML for search result display
	 *
	 * @param	array		$indexData		Data from the search index
	 * @param	array		$authorData		Basic data about the author. Only includes columns returned by \IPS\Member::columnsForPhoto()
	 * @param	array		$itemData		Basic data about the item. Only includes columns returned by item::basicDataColumns()
	 * @param	array|NULL	$containerData	Basic data about the container. Only includes columns returned by container::basicDataColumns()
	 * @param	array		$reputationData	Array of people who have given reputation and the reputation they gave
	 * @param	int|NULL	$reviewRating	If this is a review, the rating
	 * @param	string		$view			'expanded' or 'condensed'
	 * @return	callable
	 */
	public static function searchResultSnippet( array $indexData, array $authorData, array $itemData, array $containerData = NULL, array $reputationData, $reviewRating, $view )
	{		
		return $view == 'expanded' ? \IPS\Theme::i()->getTemplate( 'system', 'core', 'front' )->searchResultSnippet( $indexData ) : '';
	}

	/**
	 * Return the language string key to use in search results
	 *
	 * @note Normally we show "(user) posted a (thing) in (area)" but sometimes this may not be accurate, so this is abstracted to allow
	 *	content classes the ability to override
	 * @param	array 		$authorData		Author data
	 * @param	array 		$articles		Articles language strings
	 * @param	array 		$indexData		Search index data
	 * @param	array 		$itemData		Data about the item
	 * @return	string
	 */
	public static function searchResultSummaryLanguage( $authorData, $articles, $indexData, $itemData )
	{
		$authorTemplate = \IPS\Theme::i()->getTemplate( 'global', 'core', 'front' )->userLinkFromData( $authorData['member_id'], $authorData['name'], $authorData['members_seo_name'], $authorData['member_group_id'] ?? \IPS\Settings::i()->guest_group );

		if( \in_array( 'IPS\Content\Comment', class_parents( $indexData['index_class'] ) ) )
		{
			if( isset( $itemData['author'] ) )
			{
				$itemAuthorTemplate = \IPS\Theme::i()->getTemplate( 'global', 'core', 'front' )->userLinkFromData( $itemData['author']['member_id'], $itemData['author']['name'], $itemData['author']['members_seo_name'], $itemData['author']['member_group_id'] ?? \IPS\Settings::i()->guest_group );
			}

			if( \in_array( 'IPS\Content\Review', class_parents( $indexData['index_class'] ) ) )
			{
				if( isset( $itemData['author'] ) )
				{
					return \IPS\Member::loggedIn()->language()->addToStack( "user_other_activity_review", FALSE, array( 'sprintf' => array( $articles['definite'] ), 'htmlsprintf' => array( $authorTemplate, $itemAuthorTemplate ) ) );
				}
				else
				{
					return \IPS\Member::loggedIn()->language()->addToStack( "user_own_activity_review", FALSE, array( 'sprintf' => array( $articles['indefinite'] ), 'htmlsprintf' => array( $authorTemplate ) ) );
				}
			}
			else
			{
				if( static::$firstCommentRequired )
				{
					if( $indexData['index_title'] )
					{
						return \IPS\Member::loggedIn()->language()->addToStack( "user_own_activity_item", FALSE, array( 'sprintf' => array( $articles['indefinite'] ), 'htmlsprintf' => array( $authorTemplate ) ) );
					}
					else
					{
						if( isset( $itemData['author'] ) )
						{
							return \IPS\Member::loggedIn()->language()->addToStack( "user_other_activity_reply", FALSE, array( 'sprintf' => array( $articles['definite'] ), 'htmlsprintf' => array( $authorTemplate, $itemAuthorTemplate ) ) );
						}
						else
						{
							return \IPS\Member::loggedIn()->language()->addToStack( "user_own_activity_reply", FALSE, array( 'sprintf' => array( $articles['indefinite'] ), 'htmlsprintf' => array( $authorTemplate ) ) );
						}
					}
				}
				else
				{
					if( isset( $itemData['author'] ) )
					{
						return \IPS\Member::loggedIn()->language()->addToStack( "user_other_activity_comment", FALSE, array( 'sprintf' => array( $articles['definite'] ), 'htmlsprintf' => array( $authorTemplate, $itemAuthorTemplate ) ) );
					}
					else
					{
						return \IPS\Member::loggedIn()->language()->addToStack( "user_own_activity_comment", FALSE, array( 'sprintf' => array( $articles['indefinite'] ), 'htmlsprintf' => array( $authorTemplate ) ) );
					}
				}
			}
		}
		else
		{
			if ( isset( static::$databaseColumnMap['author'] ) )
			{
				return \IPS\Member::loggedIn()->language()->addToStack( "user_own_activity_item", FALSE, array( 'sprintf' => array( $articles['indefinite'] ), 'htmlsprintf' => array( $authorTemplate ) ) );
			}
			else
			{
				return \IPS\Member::loggedIn()->language()->addToStack( "generic_activity_item", FALSE, array( 'sprintf' => array( $articles['definite_uc'] ) ) );
			}
		}
	}

	/**
	 * @brief	Return a classname applied to the search result block
	 */
	public static $searchResultClassName = '';

	/**
	 * Return the filters that are available for selecting table rows
	 *
	 * @return	array
	 */
	public static function getTableFilters()
	{
		$return = array();
		
		if ( \in_array( 'IPS\Content\Hideable', class_implements( \get_called_class() ) ) )
		{
			$return[] = 'hidden';
			$return[] = 'unhidden';
			$return[] = 'unapproved';
		}
				
		return $return;
	}
	
	/**
	 * Get content table states
	 *
	 * @return string
	 */
	public function tableStates()
	{
		$return	= array();

		if ( $this instanceof \IPS\Content\Hideable )
		{
			switch ( $this->hidden() )
			{
				case -1:
					$return[] = 'hidden';
					break;
				case 0:
					$return[] = 'unhidden';
					break;
				case 1:
					$return[] = 'unapproved';
					break;
			}
		}
		
		return implode( ' ', $return );
		
	}
	
	/**
	 * Prune IP addresses from content
	 *
	 * @param	int		$days 		Remove from content posted older than DAYS ago
	 * @return	void
	 */
	public static function pruneIpAddresses( $days=0 )
	{
		if ( $days and isset( static::$databaseColumnMap['ip_address'] ) and isset( static::$databaseColumnMap['date'] ) )
		{
			$time = time() - ( 86400 * $days );
			\IPS\Db::i()->update( static::$databaseTable, array( static::$databasePrefix . static::$databaseColumnMap['ip_address'] => '' ), array( static::$databasePrefix . static::$databaseColumnMap['date'] . ' <= ' . $time ) );
		}
	}
	
	/**
	 * Log a row in core_post_before_registering
	 *
	 * @param	string	$guestEmail	Guest email address
	 * @param	string	$key		User's existing post_before_register cookie value
	 * @return	string	The new key, if one wasn't provided
	 */
	public function _logPostBeforeRegistering( $guestEmail, $key = NULL )
	{
		$key = $key ?: \IPS\Login::generateRandomString();
		
		$idColumn = static::$databaseColumnId;
		\IPS\Db::i()->insert( 'core_post_before_registering', array(
			'email'		=> $guestEmail,
			'class'		=> \get_class( $this ),
			'id'		=> $this->$idColumn,
			'timestamp'	=> time(),
			'secret'	=> $key,
			'language'	=> \IPS\Member::loggedIn()->language()->id
		) );
		
		return $key;
	}
	
	/**
	 * Get content for an email
	 *
	 * @param	\IPS\Email	$email			The email
	 * @param	string		$type			'html' or 'plaintext'
	 * @param	bool		$includeLinks	Whether or not to include links
	 * @param	bool		$includeAuthor	Whether or not to include the author
	 * @return	string
	 */
	public function emailContent( \IPS\Email $email, $type, $includeLinks=TRUE, $includeAuthor=TRUE )
	{
		return \IPS\Email::template( 'core', '_genericContent', $type, array( $this, $includeLinks, $includeAuthor, $email ) );
	}
	
	/**
	 * Get a count of the database table
	 *
	 * @return int
	 */
	public static function databaseTableCount()
	{
		$key = 'tbl_cnt_' . static::$databaseTable;
		$fetchAgain = FALSE;
		
		if ( ! isset( \IPS\Data\Store::i()->$key ) )
		{
			$fetchAgain = TRUE;
		}
		else
		{
			/* Just check daily */
			$data = \IPS\Data\Store::i()->$key;
			
			if ( $data['time'] < time() - 86400 )
			{
				$fetchAgain = TRUE;
			}
		}
		
		if ( $fetchAgain )
		{
			\IPS\Data\Store::i()->$key = array( 'time' => time(), 'count' => \IPS\Db::i()->select( 'COUNT(*)', static::$databaseTable )->first() );
		}
		
		$data = \IPS\Data\Store::i()->$key;
		return $data['count'];
	}
	
	/* !Follow */

	/**
	 * @brief	Follow publicly
	 */
	const FOLLOW_PUBLIC = 1;

	/**
	 * @brief	Follow anonymously
	 */
	const FOLLOW_ANONYMOUS = 2;

	/**
	 * @brief	Number of notifications to process per batch
	 */
	const NOTIFICATIONS_PER_BATCH = \IPS\NOTIFICATIONS_PER_BATCH;
	
	/**
	 * Send notifications
	 *
	 * @return	void
	 */
	public function sendNotifications()
	{		
		/* Send quote and mention notifications */
		$sentTo = $this->sendQuoteAndMentionNotifications();
		
		/* How many followers? */
		try
		{
			$count = $this->notificationRecipients( NULL, NULL, TRUE );
		}
		catch ( \BadMethodCallException $e )
		{
			return;
		}
		
		/* Queue if there's lots, or just send them */
		if ( $count > static::NOTIFICATIONS_PER_BATCH )
		{
			$idColumn = $this::$databaseColumnId;
			\IPS\Task::queue( 'core', 'Follow', array( 'class' => \get_class( $this ), 'item' => $this->$idColumn, 'sentTo' => $sentTo, 'followerCount' => $count ), 2 );
		}
		else
		{
			$this->sendNotificationsBatch( 0, $sentTo );
		}
	}
	
	/**
	 * Send notifications batch
	 *
	 * @param	int				$offset		Current offset
	 * @param	array			$sentTo		Members who have already received a notification and how - e.g. array( 1 => array( 'inline', 'email' )
	 * @param	string|NULL		$extra		Additional data
	 * @return	int|null		New offset or NULL if complete
	 */
	public function sendNotificationsBatch( $offset=0, &$sentTo=array(), $extra=NULL )
	{
		/* Check authors spam status */
		if( $this->author()->members_bitoptions['bw_is_spammer'] )
		{
			/* Author is flagged as spammer, don't send notifications */
			return NULL;
		}

		$followIds = array();
		$followers = iterator_to_array( $this->notificationRecipients( array( $offset, static::NOTIFICATIONS_PER_BATCH ), $extra ) );

		if( !\count( $followers ) )
		{
			return NULL;
		}

		/* Send notification */
		$notification = $this->createNotification( $extra );
		$notification->unsubscribeType = 'follow';
		foreach ( $followers as $follower )
		{
			$member = \IPS\Member::load( $follower['follow_member_id'] );
			if ( $member != $this->author() and $this->canView( $member ) )
			{
				$followIds[] = $follower['follow_id'];
				$notification->recipients->attach( $member, $follower );
			}
		}

		/* Log that we sent it */
		if( \count( $followIds ) )
		{
			\IPS\Db::i()->update( 'core_follow', array( 'follow_notify_sent' => time() ), \IPS\Db::i()->in( 'follow_id', $followIds ) );
		}

		$sentTo = $notification->send( $sentTo );
		
		/* Update the queue */
		return $offset + static::NOTIFICATIONS_PER_BATCH;
	}
	
	/**
	 * Send Approved Notification
	 *
	 * @return	void
	 */
	public function sendApprovedNotification()
	{
		$this->sendNotifications();
	}
	
	/**
	 * Send Unapproved Notification
	 *
	 * @return	void
	 */
	public function sendUnapprovedNotification()
	{
		$moderators = array( 'g' => array(), 'm' => array() );
		foreach( \IPS\Db::i()->select( '*', 'core_moderators' ) AS $mod )
		{
			$canView = FALSE;
			$canApprove = FALSE;
			if ( $mod['perms'] == '*' )
			{
				$canView = TRUE;
				$canApprove = TRUE;
			}
			else
			{
				$perms = json_decode( $mod['perms'], TRUE );
								
				foreach ( array( 'canView' => 'can_view_hidden_', 'canApprove' => 'can_unhide_' ) as $varKey => $modPermKey )
				{
					if ( isset( $perms[ $modPermKey . 'content' ] ) AND $perms[ $modPermKey . 'content' ] )
					{
						$$varKey = TRUE;
					}
					else
					{						
						try
						{
							$container = ( $this instanceof \IPS\Content\Comment ) ? $this->item()->container() : $this->container();
							$containerClass = \get_class( $container );
							$title = static::$title;
							if
							(
								isset( $containerClass::$modPerm )
								and
								(
									$perms[ $containerClass::$modPerm ] === -1
									or
									(
										\is_array( $perms[ $containerClass::$modPerm ] )
										and
										\in_array( $container->_id, $perms[ $containerClass::$modPerm ] )
									)
								)
								and
								$perms["{$modPermKey}{$title}"]
							)
							{
								$$varKey = TRUE;
							}
						}
						catch ( \BadMethodCallException $e ) { }
					}
				}
			}
			if ( $canView === TRUE and $canApprove === TRUE )
			{
				$moderators[ $mod['type'] ][] = $mod['id'];
			}
		}
						
		$notification = new \IPS\Notification( \IPS\Application::load('core'), 'unapproved_content', $this, array( $this, $this->author() ) );
		foreach ( \IPS\Db::i()->select( '*', 'core_members', ( \count( $moderators['m'] ) ? \IPS\Db::i()->in( 'member_id', $moderators['m'] ) . ' OR ' : '' ) . \IPS\Db::i()->in( 'member_group_id', $moderators['g'] ) . ' OR ' . \IPS\Db::i()->findInSet( 'mgroup_others', $moderators['g'] ) ) as $member )
		{
            /* We don't need to notify the author of the content */
            if( $this->author()->member_id != $member['member_id'] )
            {
                $notification->recipients->attach(\IPS\Member::constructFromData($member));
            }
		}
		$notification->send();
	}
	
	/**
	 * Send the notifications after the content has been edited (for any new quotes or mentiones)
	 *
	 * @param	string	$oldContent	The content before the edit
	 * @return	void
	 */
	public function sendAfterEditNotifications( $oldContent )
	{				
		$existingData = static::_getQuoteAndMentionIdsFromContent( $oldContent );
		$this->sendQuoteAndMentionNotifications( array_unique( array_merge( $existingData['quotes'], $existingData['mentions'] ) ) );
	}
		
	/**
	 * Send quote and mention notifications
	 *
	 * @param	array	$exclude		An array of member IDs *not* to send notifications to
	 * @return	array	The members that were notified and how they were notified
	 */
	protected function sendQuoteAndMentionNotifications( $exclude=array() )
	{
		return $this->_sendQuoteAndMentionNotifications( static::_getQuoteAndMentionIdsFromContent( $this->content() ), $exclude );
	}
	
	/**
	 * Send quote and mention notifications from data
	 *
	 * @param	array	$data		array( 'quotes' => array( ... member IDs ... ), 'mentions' => array( ... member IDs ... ), 'embeds' => array( ... member IDs ... ) )
	 * @param	array	$exclude	An array of member IDs *not* to send notifications to
	 * @return	array	The members that were notified and how they were notified
	 */
	protected function _sendQuoteAndMentionNotifications( $data, $exclude=array() )
	{
		/* Init */
		$sentTo = array();
		
		/* Quotes */
		$data['quotes'] = array_filter( $data['quotes'], function( $v ) use ( $exclude )
		{
			return !\in_array( $v, $exclude );
		} );
		if ( !empty( $data['quotes'] ) )
		{
			$notification = new \IPS\Notification( \IPS\Application::load( 'core' ), 'quote', ( $this instanceof \IPS\Content\Item ) ? $this : $this->item(), array( $this ), array( $this->author()->member_id ), FALSE );
			foreach ( $data['quotes'] as $quote )
			{
				$member = \IPS\Member::load( $quote );
				if ( $member->member_id and $member != $this->author() and $this->canView( $member ) and !$member->isIgnoring( $this->author(), 'posts' ) )
				{
					$notification->recipients->attach( $member );
				}
			}
			$sentTo = $notification->send( $sentTo );
		}
		
		/* Mentions */
		$data['mentions'] = array_filter( $data['mentions'], function( $v ) use ( $exclude )
		{
			return !\in_array( $v, $exclude );
		} );
		if ( !empty( $data['mentions'] ) )
		{
			$notification = new \IPS\Notification( \IPS\Application::load( 'core' ), 'mention', ( $this instanceof \IPS\Content\Item ) ? $this : $this->item(), array( $this ), array( $this->author()->member_id ), FALSE );
			foreach ( $data['mentions'] as $mention )
			{
				$member = \IPS\Member::load( $mention );
				if ( $member->member_id AND $member != $this->author() and $this->canView( $member ) and !$member->isIgnoring( $this->author(), 'mentions' ) )
				{
					$notification->recipients->attach( $member );
				}
			}
			$sentTo = $notification->send( $sentTo );
		}

		/* Embeds */
		$data['embeds'] = array_filter( $data['embeds'], function( $v ) use ( $exclude )
		{
			return !\in_array( $v, $exclude );
		} );
		if ( !empty( $data['embeds'] ) )
		{
			$notification = new \IPS\Notification( \IPS\Application::load( 'core' ), 'embed', ( $this instanceof \IPS\Content\Item ) ? $this : $this->item(), array( $this ), array( $this->author()->member_id ), FALSE );
			foreach ( $data['embeds'] as $embed )
			{
				$member = \IPS\Member::load( $embed );
				if ( $member->member_id AND $member != $this->author() and $this->canView( $member ) and !$member->isIgnoring( $this->author(), 'posts' ) )
				{
					$notification->recipients->attach( $member );
				}
			}
			$sentTo = $notification->send( $sentTo );
		}
	
		/* Return */
		return $sentTo;
	}
	
	/**
	 * Get quote and mention notifications
	 *
	 * @param	string	$content	The content
	 * @return	array	array( 'quotes' => array( ... member IDs ... ), 'mentions' => array( ... member IDs ... ), 'embeds' => array( ... member IDs ... )  )
	 */
	protected static function _getQuoteAndMentionIdsFromContent( $content )
	{
		$return = array( 'quotes' => array(), 'mentions' => array(), 'embeds' => array() );
		
		$document = new \IPS\Xml\DOMDocument( '1.0', 'UTF-8' );
		if ( @$document->loadHTML( \IPS\Xml\DOMDocument::wrapHtml( '<div>' . $content . '</div>' ) ) !== FALSE )
		{
			/* Quotes */
			foreach( $document->getElementsByTagName('blockquote') as $quote )
			{
				if ( $quote->getAttribute('data-ipsquote-userid') and (int) $quote->getAttribute('data-ipsquote-userid') > 0 )
				{
					$return['quotes'][] = $quote->getAttribute('data-ipsquote-userid');
				}
			}
			
			/* Mentions */
			foreach( $document->getElementsByTagName('a') as $link )
			{
				if ( $link->getAttribute('data-mentionid') )
				{					
					if ( !preg_match( '/\/blockquote(\[\d*\])?\//', $link->getNodePath() ) )
					{
						$return['mentions'][] = $link->getAttribute('data-mentionid');
					}
				}
			}

			/* Embeds */
			foreach( $document->getElementsByTagName('iframe') as $embed )
			{
				if ( $embed->getAttribute('data-embedauthorid') )
				{
					if ( $embed->getAttribute('data-embedauthorid') and (int) $embed->getAttribute('data-embedauthorid') > 0 )
					{
						$return['embeds'][] = $embed->getAttribute('data-embedauthorid');
					}
				}
			}
		}
		
		return $return;
	}
	
	/**
	 * Expire appropriate widget caches automatically
	 *
	 * @return void
	 */
	public function expireWidgetCaches()
	{
		\IPS\Widget::deleteCaches( NULL, static::$application );
	}

	/**
	 * Update "currently viewing" session data after moderator actions that invalidate that data for other users
	 *
	 * @return void
	 */
	public function adjustSessions()
	{
		if( $this instanceof \IPS\Content\Comment )
		{
			$item = $this->item();
		}
		else
		{
			$item = $this;
		}

		/* We have to send a limit even though we want all records because otherwise the Database store does not return all columns */
		foreach( \IPS\Session\Store::i()->getOnlineUsers( 0, 'desc', array( 0, 5000 ), NULL, TRUE ) as $session )
		{
			if( mb_strpos( $session['location_url'], (string) $item->url() ) === 0 )
			{
				$sessionData = $session;
				$sessionData['location_url']			= NULL;
				$sessionData['location_lang']			= NULL;
				$sessionData['location_data']			= json_encode( array() );
				$sessionData['current_id']				= 0;
				$sessionData['location_permissions']	= 0;

				\IPS\Session\Store::i()->updateSession( $sessionData );
			}
		}
	}

	/**
	 * Fetch classes from content router
	 *
	 * @param	bool|\IPS\Member	$member		Check member access
	 * @param	bool				$archived	Include any supported archive classes
	 * @param	bool				$onlyItems	Only include item classes
	 * @return	array
	 */
	public static function routedClasses( $member=FALSE, $archived=FALSE, $onlyItems=FALSE )
	{
		$classes	= array();

		foreach ( \IPS\Application::allExtensions( 'core', 'ContentRouter', $member, NULL, NULL, TRUE ) as $router )
		{
			foreach ( $router->classes as $class )
			{
				$classes[]	= $class;

				if( $onlyItems )
				{
					continue;
				}
				
				if ( !( $member instanceof \IPS\Member ) )
				{
					$member = $member ? \IPS\Member::loggedIn() : NULL;
				}
				
				if ( isset( $class::$commentClass ) and $class::supportsComments( $member ) )
				{
					$classes[]	= $class::$commentClass;
				}

				if ( isset( $class::$reviewClass ) and $class::supportsReviews( $member ) )
				{
					$classes[]	= $class::$reviewClass;
				}

				if( $archived === TRUE AND isset( $class::$archiveClass ) )
				{
					$classes[]	= $class::$archiveClass;
				}
			}
		}

		return $classes;
	}

	/**
	 * Override the HTML parsing enabled flag for rebuilds?
	 *
	 * @note	By default this will return FALSE, but classes can override
	 * @see		\IPS\forums\Topic\Post
	 * @return	bool
	 */
	public function htmlParsingEnforced()
	{
		return FALSE;
	}

	/**
	 * Return any custom multimod actions this content item supports
	 *
	 * @return	array
	 */
	public function customMultimodActions()
	{
		return array();
	}

	/**
	 * Return any available custom multimod actions this content item class supports
	 *
	 * @note	Return in format of EITHER
	 *	@li	array( array( 'action' => ..., 'icon' => ..., 'label' => ... ), ... )
	 *	@li	array( array( 'grouplabel' => ..., 'icon' => ..., 'groupaction' => ..., 'action' => array( array( 'action' => ..., 'label' => ... ), ... ) ) )
	 * @note	For an example, look at \IPS\core\Announcements\Announcement
	 * @return	array
	 */
	public static function availableCustomMultimodActions()
	{
		return array();
	}

	/**
	 * Get HTML for search result display
	 *
	 * @param	NULL|string		$ref		Referrer
	 * @param	\IPS\Node\Model	$container	Container
	 * @param	string			$title		Title
	 * @return	callable
	 */
	public function approvalQueueHtml( $ref=NULL, $container, $title )
	{
		return \IPS\Theme::i()->getTemplate( 'modcp', 'core', 'front' )->approvalQueueItem( $this, $ref, $container, $title );
	}

	/**
	 * Indefinite Article
	 *
	 * @param	\IPS\Lang|NULL	$lang	The language to use, or NULL for the language of the currently logged in member
	 * @return	string
	 */
	public function indefiniteArticle( \IPS\Lang $lang = NULL )
	{
		$container = ( $this instanceof \IPS\Content\Comment ) ? $this->item()->containerWrapper() : $this->containerWrapper();
		return static::_indefiniteArticle( $container ? $container->_data : array(), $lang );
	}
	
	/**
	 * Indefinite Article
	 *
	 * @param	array			$containerData	Container data
	 * @param	\IPS\Lang|NULL	$lang			The language to use, or NULL for the language of the currently logged in member
	 * @return	string
	 */
	public static function _indefiniteArticle( array $containerData = NULL, \IPS\Lang $lang = NULL )
	{
		$lang = $lang ?: \IPS\Member::loggedIn()->language();
		return $lang->addToStack( '__indefart_' . static::$title, FALSE );
	}
	
	/**
	 * Definite Article
	 *
	 * @param	\IPS\Lang|NULL	$lang	The language to use, or NULL for the language of the currently logged in member
	 * @return	string
	 */
	public function definiteArticle( \IPS\Lang $lang = NULL )
	{
		$container = ( $this instanceof \IPS\Content\Comment ) ? $this->item()->containerWrapper() : $this->containerWrapper();
		return static::_definiteArticle( $container ? $container->_data : array(), $lang );
	}
	
	/**
	 * Definite Article
	 *
	 * @param	array			$containerData	Basic data about the container. Only includes columns returned by container::basicDataColumns()
	 * @param	\IPS\Lang|NULL	$lang			The language to use, or NULL for the language of the currently logged in member
	 * @param	array			$options		Options to pass to \IPS\Lang::addToStack
	 * @return	string
	 */
	public static function _definiteArticle( array $containerData = NULL, \IPS\Lang $lang = NULL, $options = array() )
	{
		$lang = $lang ?: \IPS\Member::loggedIn()->language();
		return $lang->addToStack( '__defart_' . static::$title, FALSE, $options );
	}

	/**
	 * Get preview image for share services
	 *
	 * @return	string
	 */
	public function shareImage()
	{
		/* While we now allow multiple share logos now, this deprecated method can only return one */
		$shareLogos = \IPS\Settings::i()->icons_sharer_logo ? json_decode( \IPS\Settings::i()->icons_sharer_logo, true ) : array();

		if( \count( $shareLogos ) )
		{
			try
			{
				return (string) \IPS\File::get( 'core_Icons', $shareLogos[0] )->url->setScheme( ( \IPS\Request::i()->isSecure() ) ? 'https' : 'http' );
			}
			catch( \Exception $e )
			{
				return '';
			}
		}

		return '';
	}

	/**
	 * Log keyword usage, if any
	 *
	 * @param	string		$content	Content/text of submission
	 * @param	string|NULL	$title		Title of submission
	 * @return	void
	 */
	public function checkKeywords( $content, $title=NULL )
	{
		/* Do we have any keywords to track? */
		if( !\IPS\Settings::i()->stats_keywords )
		{
			return;
		}

		/* We need to know the ID */
		$idColumn	= static::$databaseColumnId;

		/* If this is a content item and first comment is required, skip checking the comment */
		if ( $this instanceof \IPS\Content\Comment )
		{
			$itemClass = static::$itemClass;

			if( $itemClass::$firstCommentRequired === TRUE )
			{
				/* During initial post, at this point the firstCommentIdColumn value won't be set, so we check for that or explicitly if this is the first post */
				if( !$this->item()->mapped('first_comment_id') OR $this->$idColumn == $this->item()->mapped('first_comment_id') )
				{
					return;
				}
			}
		}

		$words = preg_split("/[\s]+/", trim( strip_tags( preg_replace( "/<br( \/)?>/", "\n", $content ) ) ), NULL, PREG_SPLIT_NO_EMPTY );

		if( $title !== NULL )
		{
			$titleWords = explode( ' ', $title );
			$words		= array_merge( $words, $titleWords );
		}

		$words = array_unique( $words );

		$keywords = json_decode( \IPS\Settings::i()->stats_keywords, true );

		$extraData	= json_encode( array( 'class' => \get_class( $this ), 'id' => $this->$idColumn ) );

		foreach( $keywords as $keyword )
		{
			if( \in_array( $keyword, $words ) )
			{
				\IPS\Db::i()->insert( 'core_statistics', array( 'time' => time(), 'type' => 'keyword', 'value_4' => $keyword, 'extra_data' => $extraData ) );
			}
		}
	}
	
	/* !Search */
	
	/**
	 * Title for search index
	 *
	 * @return	string
	 */
	public function searchIndexTitle()
	{
		return $this->mapped('title');
	}
	
	/**
	 * Content for search index
	 *
	 * @return	string
	 */
	public function searchIndexContent()
	{
		return $this->mapped('content');
	}

	/**
	 * Create a query to fetch the "top members"
	 *
	 * @note	The intention is to formulate a query that will fetch the members with the most contributions
	 * @param	int		$limit	The number of members to return
	 * @return	\IPS\Db\Select
	 */
	public static function topMembersQuery( $limit )
	{
		$contentWhere = array( array( static::$databasePrefix . static::$databaseColumnMap['author'] . '<>?', 0 ) );
		if ( isset( static::$databaseColumnMap['hidden'] ) )
		{
			$contentWhere[] = array( static::$databasePrefix . static::$databaseColumnMap['hidden'] . '=0' );
		}
		else if ( isset( static::$databaseColumnMap['approved'] ) )
		{
			$contentWhere[] = array( static::$databasePrefix . static::$databaseColumnMap['approved'] . '=1' );
		}
		
		$authorField = static::$databasePrefix . static::$databaseColumnMap['author'];

		return \IPS\Db::i()->select( 'COUNT(*) as count, ' . static::$databaseTable . '.' . $authorField, static::$databaseTable, $contentWhere, 'count DESC', $limit, $authorField );
	}
}