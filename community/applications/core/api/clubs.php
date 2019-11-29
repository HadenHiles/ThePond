<?php
/**
 * @brief		Clubs API
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		7 June 2018
 */

namespace IPS\core\api;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * @brief	Clubs API
 */
class _clubs extends \IPS\Api\Controller
{
	/**
	 * GET /core/clubs
	 * Get list of clubs
	 *
	 * @apiparam	int		page			Page number
	 * @apiparam	int		perPage			Number of results per page - defaults to 25
	 * @note		For requests using an OAuth Access Token for a particular member, only clubs the authorized user can view will be included
	 * @return		\IPS\Api\PaginatedResponse<IPS\Member\Club>
	 */
	public function GETindex()
	{
		$page		= isset( \IPS\Request::i()->page ) ? \IPS\Request::i()->page : 1;
		$perPage	= isset( \IPS\Request::i()->perPage ) ? \IPS\Request::i()->perPage : 25;
		$clubs		= \IPS\Member\Club::clubs( $this->member ?: NULL, array( ( $page - 1 ) * $perPage, $perPage ), 'created' );

		/* Return */
		return new \IPS\Api\PaginatedResponse(
			200,
			$clubs,
			$page,
			'IPS\Member\Club',
			$clubs->count( TRUE ),
			$this->member,
			$perPage
		);
	}

	/**
	 * GET /core/clubs/{id}
	 * Get specific club
	 *
	 * @param		int		$id			ID Number
	 * @throws		1C386/1	INVALID_ID	The club does not exist or the authorized user does not have permission to view it
	 * @return		\IPS\Member\Club
	 */
	public function GETitem( $id )
	{
		try
		{
			$club = \IPS\Member\Club::loadAndCheckPerms( $id );

			return new \IPS\Api\Response( 200, $club->apiOutput( $this->member ) );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '1C386/1', 404 );
		}
	}

	/**
	 * POST /core/clubs
	 * Create a club
	 *
	 * @reqapiparam	string		name				The club name
	 * @reqapiparam	int			owner				The club owner (if not using an OAuth Access Token for a particular member)
	 * @apiparam	string		about				Information about the club
	 * @apiparam	string		type				Club type (one of public, open, private, readonly or closed). Defaults to open.
	 * @apiparam	bool		approved			0|1 Whether the club is approved or not (if not using an OAuth Access Token for a particular member)
	 * @apiparam	bool		featured			0|1 Whether the club is featured or not (if not using an OAuth Access Token for a particular member)
	 * @apiparam	float		lat					Latitude of the club
	 * @apiparam	float		long				Longitude of the club
	 * @apiparam	\IPS\nexus\Money		joiningFee	Cost to join the club (Nexus must be installed, paid clubs must be enabled, and the owner must be allowed to create paid clubs)
	 * @apiparam	\IPS\nexus\Purchase\RenewalTerm		renewalTerm	Renewal term for the club (joiningFee must be set)
	 * @return		\IPS\Member\Club
	 * @note		For requests using an OAuth Access Token for a particular member, the authorized user will be the club owner, otherwise you must pass an owner parameter with a valid member ID to set the club owner
	 * @throws		1C386/3	OWNER_REQUIRED	An owner for the club is required. For requests NOT using an OAuth Access Token for a particular member, you must supply a member ID for the owner property
	 * @throws		1C386/4	NAME_REQUIRED	A name is required for the club
	 * @throws		1C386/J	CANNOT_CREATE	The authorized member or supplied owner cannot create the type of club requested
	 * @throws		1C386/K	CLUB_LIMIT_REACHED	The authorized member or supplied owner has reached the maximum number of clubs they are allowed to create based on group restrictions
	 */
	public function POSTindex()
	{
		/* We need an owner */
		if( !$this->member AND !\IPS\Request::i()->owner )
		{
			throw new \IPS\Api\Exception( 'OWNER_REQUIRED', '1C386/3', 400 );
		}

		$owner = $this->member ?: \IPS\Member::load( \IPS\Request::i()->owner );

		if( !\IPS\Request::i()->name )
		{
			throw new \IPS\Api\Exception( 'NAME_REQUIRED', '1C386/4', 400 );
		}

		$availableTypes = array();

		foreach ( explode( ',', $owner->group['g_create_clubs'] ) as $type )
		{
			if ( $type !== '' )
			{
				$availableTypes[ $type ] = 'club_type_' . $type;
			}
		}

		/* Default club type to 'open' if not specified */
		if( !isset( \IPS\Request::i()->type ) OR !\IPS\Request::i()->type )
		{
			\IPS\Request::i()->type = 'open';
		}

		if ( !$availableTypes OR !\in_array( \IPS\Request::i()->type, array_keys( $availableTypes ) ) )
		{
			throw new \IPS\Api\Exception( 'CANNOT_CREATE', '1C386/J', 403 );
		}
		
		if ( $owner->group['g_club_limit'] )
		{
			if ( \IPS\Db::i()->select( 'COUNT(*)', 'core_clubs', array( 'owner=?', $owner->member_id ) )->first() >= $owner->group['g_club_limit'] )
			{
				throw new \IPS\Api\Exception( 'CLUB_LIMIT_REACHED', '1C386/K', 403 );
			}
		}

		$club = new \IPS\Member\Club;
		$club->owner	= $owner;

		$this->setClubProperties( $club );

		$club->save();

		return new \IPS\Api\Response( 201, $club->apiOutput( $this->member ) );
	}

	/**
	 * POST /core/clubs/{id}
	 * Edit a club
	 *
	 * @reqapiparam	string		name				The club name
	 * @apiparam	string		about				Information about the club
	 * @apiparam	string		type				Club type (one of public, open, private, readonly or closed). Defaults to open.
	 * @apiparam	bool		approved			0|1 Whether the club is approved or not (if not using an OAuth Access Token for a particular member)
	 * @apiparam	bool		featured			0|1 Whether the club is featured or not (if not using an OAuth Access Token for a particular member)
	 * @apiparam	float		lat					Latitude of the club
	 * @apiparam	float		long				Longitude of the club
	 * @apiparam	\IPS\nexus\Money		joiningFee	Cost to join the club (Nexus must be installed, paid clubs must be enabled, and the owner must be allowed to create paid clubs)
	 * @apiparam	\IPS\nexus\Purchase\RenewalTerm		renewalTerm	Renewal term for the club (joiningFee must be set)
	 * @param		int		$id			ID Number
	 * @return		\IPS\Member\Club
	 * @throws		1C386/5	INVALID_ID	The club ID was invalid or the authorized member does not have permission to edit it
	 * @throws		1C386/L	CANNOT_CREATE	The authorized member or supplied owner cannot create the type of club requested
	 */
	public function POSTitem( $id )
	{
		try
		{
			$club = \IPS\Member\Club::load( $id );

			if( $this->member AND !$club->canEdit( $this->member ) )
			{
				throw new \OutOfRangeException;
			}
		}
		catch( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '1C386/5', 404 );
		}

		/* Make sure the club type is allowed */
		if( isset( \IPS\Request::i()->type ) )
		{
			$owner = $this->member ?: $club->owner;

			$availableTypes = array();

			foreach ( explode( ',', $owner->group['g_create_clubs'] ) as $type )
			{
				if ( $type !== '' )
				{
					$availableTypes[ $type ] = 'club_type_' . $type;
				}
			}

			if ( !$availableTypes OR !\in_array( \IPS\Request::i()->type, array_keys( $availableTypes ) ) )
			{
				throw new \IPS\Api\Exception( 'CANNOT_CREATE', '1C386/L', 403 );
			}
		}

		$this->setClubProperties( $club );

		$club->save();

		return new \IPS\Api\Response( 200, $club->apiOutput( $this->member ) );
	}

	/**
	 * Set common club properties
	 *
	 * @param	\IPS\Member\Club	$club	The club object to set the properties on
	 * @return	void
	 */
	protected function setClubProperties( $club )
	{
		if( \IPS\Request::i()->name )
		{
			$club->name		= \IPS\Request::i()->name;
		}

		if( isset( \IPS\Request::i()->about ) )
		{
			$club->about	= \IPS\Request::i()->about;
		}

		if( isset( \IPS\Request::i()->type ) AND \in_array( \IPS\Request::i()->type, array( 'open', 'public', 'readonly', 'closed', 'private' ) ) )
		{
			$club->type	= \IPS\Request::i()->type;
		}

		if( !$this->member AND isset( \IPS\Request::i()->approved ) )
		{
			$club->approved	= \IPS\Request::i()->approved;
		}
		else
		{
			$club->approved = \IPS\Settings::i()->clubs_require_approval ? 0 : 1;
		}

		if( !$this->member AND isset( \IPS\Request::i()->featured ) )
		{
			$club->featured	= \IPS\Request::i()->featured;
		}

		if( \IPS\Settings::i()->clubs_locations AND isset( \IPS\Request::i()->lat ) AND isset( \IPS\Request::i()->long ) )
		{
			try
			{
				$location = \IPS\GeoLocation::getByLatLong( \IPS\Request::i()->lat, \IPS\Request::i()->long );

				$club->location_json	= json_encode( $location );
				$club->location_lat		= $location->lat;
				$club->location_long	= $location->long;
			}
			catch( \Exception $e ){}
		}

		if( isset( \IPS\Request::i()->joiningFee ) AND \IPS\Request::i()->joiningFee )
		{
			if ( $club->owner->member_id AND \IPS\Application::appIsEnabled( 'nexus' ) and \IPS\Settings::i()->clubs_paid_on and $club->owner->group['gbw_paid_clubs'] )
			{
				$club->fee = json_encode( \IPS\Request::i()->joiningFee );

				if ( isset( \IPS\Request::i()->renewalTerm ) AND \IPS\Request::i()->renewalTerm )
				{						
					$this->renewal_term = \IPS\Request::i()->renewalTerm['term'];
					$this->renewal_units = \IPS\Request::i()->renewalTerm['unit'];
					$this->renewal_price = json_encode( \IPS\Request::i()->renewalTerm['cost'] );
				}
				else
				{
					$this->renewal_term = 0;
					$this->renewal_units = NULL;
					$this->renewal_price = NULL;
				}
			}
		}
	}

	/**
	 * DELETE /core/clubs/{id}
	 * Delete a club
	 *
	 * @apiclientonly
	 * @param		int		$id			ID Number
	 * @return		null
	 * @throws		1C386/2	INVALID_ID	The club does not exist
	 */
	public function DELETEitem( $id )
	{
		try
		{
			$club = \IPS\Member\Club::load( $id );
		}
		catch ( \Exception $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '1C386/2', 404 );
		}

		/* Get nodes and queue for deletion */
		$nodes = $club->nodes();

		foreach( $nodes as $data )
		{
			try
			{
				$class	= $data['node_class'];
				$node	= $class::load( $data['node_id'] );

				$nodesToQueue = array( $node );
				$nodeToCheck = $node;
				while( $nodeToCheck->hasChildren( NULL ) )
				{
					foreach ( $nodeToCheck->children( NULL ) as $nodeToCheck )
					{
						$nodesToQueue[] = $nodeToCheck;
					}
				}
				
				foreach ( $nodesToQueue as $_node )
				{					
					\IPS\Task::queue( 'core', 'DeleteOrMoveContent', array( 'class' => $class, 'id' => $_node->_id, 'deleteWhenDone' => TRUE, 'additional' => array() ) );
				}
			}
			catch( \Exception $e ){}
		}

		/* Now delete the club and associated data */
		$club->delete();
		\IPS\Db::i()->delete( 'core_clubs_memberships', array( 'club_id=?', $club->id ) );
		\IPS\Db::i()->delete( 'core_clubs_node_map', array( 'club_id=?', $club->id ) );
		\IPS\Db::i()->delete( 'core_clubs_fieldvalues', array( 'club_id=?', $club->id ) );

		return new \IPS\Api\Response( 200, NULL );
	}

	/**
	 * GET /core/clubs/{id}/members
	 * Get members of a club
	 *
	 * @param		int		$id			ID Number
	 * @throws		1C386/6	INVALID_ID	The club does not exist or the authorized user does not have permission to view it
	 * @throws		1C386/I	NO_MEMBERS_PUBLIC_CLUB	The club is a public club which has no member list
	 * @apiresponse	\IPS\Member		owner		Club owner
	 * @apiresponse	[\IPS\Member]		members		Club members
	 * @apiresponse	[\IPS\Member]		leaders		Club leaders
	 * @apiresponse	[\IPS\Member]		moderators		Club moderators
	 * @return		array
	 */
	public function GETitem_members( $id )
	{
		try
		{
			$club = \IPS\Member\Club::load( $id );

			if( $this->member AND !$club->canView( $this->member ) )
			{
				throw new \OutOfRangeException;
			}
			elseif( $club->type === \IPS\Member\Club::TYPE_PUBLIC )
			{
				throw new \BadMethodCallException;
			}

			$members		= array();
			$leaders		= array();
			$moderators		= array();

			foreach( $club->members( array( 'member', 'moderator', 'leader' ), 250, 'core_clubs_memberships.joined DESC', 2 ) as $member )
			{
				$member = \IPS\Member::constructFromData( $member );

				if( $club->owner != $member )
				{
					if( $club->isLeader( $member ) )
					{
						$leaders[] = $member->apiOutput();
					}
					elseif( $club->isModerator( $member ) )
					{
						$moderators[] = $member->apiOutput();
					}
					else
					{
						$members[] = $member->apiOutput();
					}
				}
			}

			return new \IPS\Api\Response( 200, array( 'owner' => $club->owner ? $club->owner->apiOutput() : NULL, 'members' => $members, 'leaders' => $leaders, 'moderators' => $moderators ) );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '1C386/6', 404 );
		}
		catch( \BadMethodCallException $e )
		{
			throw new \IPS\Api\Exception( 'NO_MEMBERS_PUBLIC_CLUB', '1C386/I', 400 );
		}
	}

	/**
	 * POST /core/clubs/{id}/members
	 * Add a member to a club
	 *
	 * @param		int		$id			ID Number
	 * @apiparam	int		id			Member (ID) to add to the club
	 * @apiparam	string	status		Status of the member being added or updated (member, invited, requested, banned, moderator, leader)
	 * @throws		1C386/7	INVALID_ID	The club does not exist or the authorized user does not have permission to add members to it
	 * @throws		1C386/8	INVALID_MEMBER	The member to be added could not be found or the member cannot join the club
	 * @return		array
	 * @apiresponse	\IPS\Member		owner		Club owner
	 * @apiresponse	[\IPS\Member]		members		Club members
	 * @apiresponse	[\IPS\Member]		leaders		Club leaders
	 * @apiresponse	[\IPS\Member]		moderators		Club moderators
	 * @note		If the member already exists they will be updated. This can be used to ban a member from a club or promote a member to a leader, for instance.
	 * @note		This method bypasses the normal club joining routines and the member will be immediately added with the status supplied, including for paid clubs.
	 * @note		If using an OAuth access token then the current authorized user will be added, unless the current authorized user is a leader and the id parameter is supplied. If not using an OAuth access token the id parameter is required.
	 */
	public function POSTitem_members( $id )
	{
		try
		{
			$club = \IPS\Member\Club::load( $id );

			if( $this->member AND $club->isLeader( $this->member ) AND isset( \IPS\Request::i()->id ) )
			{
				$member = \IPS\Member::load( \IPS\Request::i()->id );
			}
			else
			{
				$member = $this->member ?: \IPS\Member::load( \IPS\Request::i()->id );
			}

			if( !$club->canJoin( $member ) )
			{
				$memberStatus = $member->member_id ? $club->memberStatus( $member ) : NULL;

				if ( !$member->member_id OR !\in_array( $memberStatus, array( \IPS\Member\Club::STATUS_MEMBER, \IPS\Member\Club::STATUS_MODERATOR, \IPS\Member\Club::STATUS_LEADER, \IPS\Member\Club::STATUS_REQUESTED, \IPS\Member\Club::STATUS_DECLINED, \IPS\Member\Club::STATUS_EXPIRED, \IPS\Member\Club::STATUS_EXPIRED_MODERATOR ) ) )
				{
					throw new \IPS\Api\Exception( 'INVALID_MEMBER', '1C386/8', 404 );
				}
			}

			$currentStatus = $club->memberStatus( $member );

			if( $this->member AND $member == $this->member )
			{
				/* If this is an open club, or the member was invited, or they have mod access anyway go ahead and add them */
				if ( \in_array( $currentStatus, array( \IPS\Member\Club::STATUS_INVITED, \IPS\Member\Club::STATUS_INVITED_BYPASSING_PAYMENT, \IPS\Member\Club::STATUS_WAITING_PAYMENT ) ) or $club->type === \IPS\Member\Club::TYPE_OPEN or $member->modPermission('can_access_all_clubs') )
				{
					/* Unless they have to pay */
					if ( $club->isPaid() and $currentStatus !== \IPS\Member\Club::STATUS_INVITED_BYPASSING_PAYMENT )
					{
						if ( $club->joiningFee() )
						{
							$club->generateInvoice( \IPS\nexus\Customer::load( $member->member_id ) );
						}
					}
					else
					{
						$club->addMember( $member, \IPS\Member\Club::STATUS_MEMBER, TRUE, ( $this->member AND $this->member != $member ) ? $this->member : NULL, NULL, TRUE );
						$club->recountMembers();
					}
				}
				/* Otherwise, add the request */
				else
				{
					$club->addMember( $member, \IPS\Member\Club::STATUS_REQUESTED, TRUE, ( $this->member AND $this->member != $member ) ? $this->member : NULL );
				}
			}
			else
			{
				$newStatus = \IPS\Request::i()->status ?: 'member';

				if( !\in_array( $newStatus, array( 'member', 'invited', 'requested', 'banned', 'moderator', 'leader' ) ) )
				{
					$newStatus = 'member';
				}

				$club->addMember( $member, $newStatus, TRUE, ( $this->member AND $this->member != $member ) ? $this->member : NULL );
				$club->recountMembers();
			}

			return $this->GETitem_members( $id );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '1C386/7', 404 );
		}
	}

	/**
	 * DELETE /core/clubs/{id}/members/{member}
	 * Remove a member from a club
	 *
	 * @param		int		$id			ID Number
	 * @param		int		$memberId	Member (ID) to remove
	 * @throws		1C386/A	INVALID_ID	The club does not exist or the current authorized member is not a leader of the club
	 * @throws		1C386/9	INVALID_MEMBER	The member to be deleted could not be found
	 * @apiresponse	\IPS\Member		owner		Club owner
	 * @apiresponse	[\IPS\Member]		members		Club members
	 * @apiresponse	[\IPS\Member]		leaders		Club leaders
	 * @apiresponse	[\IPS\Member]		moderators		Club moderators
	 * @return		array
	 */
	public function DELETEitem_members( $id, $memberId = 0 )
	{
		try
		{
			$club = \IPS\Member\Club::load( $id );

			if( $this->member AND !$club->isLeader( $this->member ) )
			{
				throw new \OutOfRangeException;
			}

			$member = \IPS\Member::load( $memberId );

			if( !$member->member_id )
			{
				throw new \IPS\Api\Exception( 'INVALID_MEMBER', '1C386/9', 404 );
			}

			$club->removeMember( $member );
			$club->recountMembers();

			/* Cancel purchase */
			if ( \IPS\Application::appIsEnabled('nexus') and \IPS\Settings::i()->clubs_paid_on )
			{
				foreach ( \IPS\core\extensions\nexus\Item\ClubMembership::getPurchases( \IPS\nexus\Customer::load( $member->member_id ), $club->id ) as $purchase )
				{
					$purchase->cancelled = TRUE;
					$purchase->member->log( 'purchase', array( 'type' => 'cancel', 'id' => $purchase->id, 'name' => $purchase->name, 'by' => 'api' ) );
					$purchase->can_reactivate = FALSE;
					$purchase->save();
				}
			}

			return $this->GETitem_members( $id );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '1C386/A', 404 );
		}
	}

	/**
	 * GET /core/clubs/contenttypes
	 * Get content types that can be created in clubs
	 *
	 * @apiparam	int			memberId	Restrict the returned types to only those that can be created by the supplied member ID
	 * @note	For requests using an OAuth Access Token for a particular member, the memberId parameter is ignored and the authorized member is checked
	 * @apiresponse	array	contentTypes	Available content types
	 * @return		array
	 */
	public function GETcontenttypes()
	{
		$member = $this->member ?: ( ( isset( \IPS\Request::i()->memberId ) ) ? \IPS\Member::load( \IPS\Request::i()->memberId ) : NULL );

		return new \IPS\Api\Response( 200, array( 'contentTypes' => \IPS\Member\Club::availableNodeTypes( $member ) ) );
	}

	/**
	 * GET /core/clubs/{id}/nodes
	 * Get nodes belonging to a particular club
	 *
	 * @param		int		$id			ID Number
	 * @throws		1C386/6	INVALID_ID	The club does not exist or the authorized user does not have permission to view it
	 * @apiresponse	[\IPS\Node\Model]		nodes		Club nodes
	 * @return		array
	 */
	public function GETitem_nodes( $id )
	{
		try
		{
			$club = \IPS\Member\Club::load( $id );

			if( $this->member AND !$club->canView( $this->member ) )
			{
				throw new \OutOfRangeException;
			}

			/* Format in a useful manner */
			$nodes = array();

			foreach( $club->nodes() as $node )
			{
				$class = $node['node_class'];
				$node = $class::load( $node['node_id'] );

				if( $node->canView( $this->member ?: NULL ) )
				{
					$nodes[] = $node->apiOutput();
				}
			}

			return new \IPS\Api\Response( 200, array( 'nodes' => $nodes ) );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '1C386/B', 404 );
		}
	}

	/**
	 * DELETE /core/clubs/{id}/nodes
	 * Delete a node from a club
	 *
	 * @param		int		$id			ID Number
	 * @reqapiparam	int		id			Node (ID) to remove
	 * @reqapiparam	string	class		Node (class) to remove
	 * @throws		1C386/H	INVALID_ID	The club does not exist or the current authorized member is not a leader of the club
	 * @throws		1C386/G	INVALID_NODE	The node to be deleted could not be found or the authorized user does not have permission to delete it
	 * @return		null
	 */
	public function DELETEitem_nodes( $id )
	{
		try
		{
			$club = \IPS\Member\Club::load( $id );

			if( $this->member AND !$club->isLeader( $this->member ) )
			{
				throw new \OutOfRangeException;
			}

			try
			{
				$class = \IPS\Request::i()->class;
				$node = $class::load( \IPS\Request::i()->id );

				if ( !$node->club() or $node->club()->id !== $club->id )
				{
					throw new \OutOfRangeException;
				}

				/* Permission check */
				if( $this->member )
				{
					$itemClass = $node::$contentItemClass;
					if ( !$node->modPermission( 'delete', $this->member ) and $itemClass::contentCount( $node, TRUE, TRUE, TRUE, 1 ) )
					{
						throw new \OutOfRangeException;
					}
				}
			}
			catch( \OutOfRangeException $e )
			{
				throw new \IPS\Api\Exception( 'INVALID_NODE', '1C386/G', 404 );
			}

			\IPS\Db::i()->delete( 'core_clubs_node_map', array( 'club_id=? AND node_class=? AND node_id=?', $club->id, $class, $node->_id ) );
			$node->deleteOrMoveFormSubmit( array() );

			return new \IPS\Api\Response( 200, NULL );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '1C386/H', 404 );
		}
	}
}