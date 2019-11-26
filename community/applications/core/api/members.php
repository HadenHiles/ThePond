<?php
/**
 * @brief		Members API
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		3 Dec 2015
 */

namespace IPS\core\api;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * @brief	Members API
 */
class _members extends \IPS\Api\Controller
{
	/**
	 * GET /core/members
	 * Get list of members
	 *
	 * @apiparam	string	sortBy		What to sort by. Can be 'joined', 'name', 'last_activity' or leave unspecified for ID
	 * @apiparam	string	sortDir		Sort direction. Can be 'asc' or 'desc' - defaults to 'asc'
	 * @apiparam	string	name		(Partial) user name to search for
	 * @apiparam	string	email		(Partial) Email address to search for
	 * @apiparam	int|array	group		Group ID or IDs to search for
	 * @apiparam	int		activity_after		Find members that have been active since unix timestamp
	 * @apiparam	int		activity_before		Find members that have been active before unix timestamp
	 * @apiparam	int		page		Page number
	 * @apiparam	int		perPage		Number of results per page - defaults to 25
	 * @return		\IPS\Api\PaginatedResponse<IPS\Member>
	 */
	public function GETindex()
	{
		/* Where clause */
		$where = array( array( 'core_members.email<>?', '' ) );

		/* Are we searching? */
		if( isset( \IPS\Request::i()->name ) )
		{
			$where[] = \IPS\Db::i()->like( 'name', \IPS\Request::i()->name );
		}

		if( isset( \IPS\Request::i()->email ) )
		{
			$where[] = \IPS\Db::i()->like( 'email', \IPS\Request::i()->email );
		}
		
		if( isset( \IPS\Request::i()->activity_after ) )
		{
			$where[] = array( 'last_activity > ?', \IPS\Request::i()->activity_after );
		}
		
		if( isset( \IPS\Request::i()->activity_before ) )
		{
			$where[] = array( 'last_activity < ?', \IPS\Request::i()->activity_before );
		}

		if( isset( \IPS\Request::i()->group ) )
		{
			if( \is_array( \IPS\Request::i()->group ) )
			{
				$groups = array_map( function( $value ){ return \intval( $value ); }, \IPS\Request::i()->group );
				$where[] = array( "(member_group_id IN(" . implode( ',', $groups ) . ") OR " . \IPS\Db::i()->findInSet( 'mgroup_others', $groups ) . ")" );
			}
			elseif( \IPS\Request::i()->group )
			{
				$where[] = array( "(member_group_id=" . \intval( \IPS\Request::i()->group ) . ") OR (" . \IPS\Db::i()->findInSet( 'mgroup_others', array( \intval( \IPS\Request::i()->group ) ) ) . ")" );
			}
		}

		/* Sort */
		$sortBy = ( isset( \IPS\Request::i()->sortBy ) and \in_array( \IPS\Request::i()->sortBy, array( 'name', 'joined', 'last_activity' ) ) ) ? \IPS\Request::i()->sortBy : 'member_id';
		$sortDir = ( isset( \IPS\Request::i()->sortDir ) and \in_array( mb_strtolower( \IPS\Request::i()->sortDir ), array( 'asc', 'desc' ) ) ) ? \IPS\Request::i()->sortDir : 'asc';

		/* Return */
		return new \IPS\Api\PaginatedResponse(
			200,
			\IPS\Db::i()->select( '*', 'core_members', $where, "{$sortBy} {$sortDir}" ),
			isset( \IPS\Request::i()->page ) ? \IPS\Request::i()->page : 1,
			'IPS\Member',
			\IPS\Db::i()->select( 'COUNT(*)', 'core_members', $where )->first(),
			$this->member,
			isset( \IPS\Request::i()->perPage ) ? \IPS\Request::i()->perPage : NULL
		);
	}
	
	/**
	 * GET /core/members/{id}
	 * Get information about a specific member
	 *
	 * @param		int		$id			ID Number
	 * @apiparam	array	otherFields	An array of additional non-standard fields to return via the REST API
	 * @throws		1C292/2	INVALID_ID	The member ID does not exist
	 * @return		\IPS\Member
	 */
	public function GETitem( $id )
	{
		try
		{
			$member = \IPS\Member::load( $id );
			if ( !$member->member_id )
			{
				throw new \OutOfRangeException;
			}
			
			return new \IPS\Api\Response( 200, $member->apiOutput( $this->member, ( isset( \IPS\Request::i()->otherFields ) ) ? \IPS\Request::i()->otherFields : NULL ) );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '1C292/2', 404 );
		}
	}

	/**
	 * Create or update member
	 *
	 * @param	\IPS\Member	$member			The member
	 * @throws		1C292/4	USERNAME_EXISTS	The username provided is already in use
	 * @throws		1C292/5	EMAIL_EXISTS	The email address provided is already in use
	 * @throws		1C292/6	INVALID_GROUP	The group ID provided is not valid
	 * @return		\IPS\Member
	 */
	protected function _createOrUpdate( $member )
	{
		if ( isset( \IPS\Request::i()->name ) and \IPS\Request::i()->name != $member->name )
		{
			$existingUsername = \IPS\Member::load( \IPS\Request::i()->name, 'name' );
			if ( !$existingUsername->member_id )
			{				
				$member->logHistory( 'core', 'display_name', array( 'old' => $member->name, 'new' => \IPS\Request::i()->name, 'by' => 'api' ) );
				$member->name = \IPS\Request::i()->name;
			}
			else
			{
				throw new \IPS\Api\Exception( 'USERNAME_EXISTS', '1C292/4', 403 );
			}
		}

		if ( isset( \IPS\Request::i()->email ) and \IPS\Request::i()->email != $member->email )
		{
			$existingEmail = \IPS\Member::load( \IPS\Request::i()->email, 'email' );
			if ( !$existingEmail->member_id )
			{
				$member->logHistory( 'core', 'email_change', array( 'old' => $member->name, 'new' => \IPS\Request::i()->name, 'by' => 'api' ) );
				$member->email = \IPS\Request::i()->email;
				$member->invalidateSessionsAndLogins();
			}
			else
			{
				throw new \IPS\Api\Exception( 'EMAIL_EXISTS', '1C292/5', 403 );
			}
		}

		if ( isset( \IPS\Request::i()->group ) )
		{
			try
			{
				$group = \IPS\Member\Group::load( \IPS\Request::i()->group );
				$member->member_group_id = $group->g_id;
			}
			catch ( \OutOfRangeException $e )
			{
				throw new \IPS\Api\Exception( 'INVALID_GROUP', '1C292/6', 403 );
			}
		}

		if( isset( \IPS\Request::i()->secondaryGroups ) AND \is_array( \IPS\Request::i()->secondaryGroups ) )
		{
			foreach( \IPS\Request::i()->secondaryGroups as $groupId )
			{
				try
				{
					$group = \IPS\Member\Group::load( $groupId );
				}
				catch ( \OutOfRangeException $e )
				{
					throw new \IPS\Api\Exception( 'INVALID_GROUP', '1C292/7', 403 );
				}
			}

			$member->mgroup_others = implode( ',', \IPS\Request::i()->secondaryGroups );
		}
		elseif( isset( \IPS\Request::i()->secondaryGroups ) AND \IPS\Request::i()->secondaryGroups == '' )
		{
			$member->mgroup_others = '';
		}

		if( isset( \IPS\Request::i()->registrationIpAddress ) AND filter_var( \IPS\Request::i()->registrationIpAddress, FILTER_VALIDATE_IP ) )
		{
			$member->ip_address	= \IPS\Request::i()->registrationIpAddress;
		}

		if( isset( \IPS\Request::i()->rawProperties ) AND \is_array( \IPS\Request::i()->rawProperties ) )
		{
			foreach( \IPS\Request::i()->rawProperties as $property => $value )
			{
				$member->$property	= $value;
			}
		}

		if ( isset( \IPS\Request::i()->password ) )
		{
			/* Setting the password for the just created member shouldn't be logged to the member history and shouldn't fire the onPassChange Sync call */
			$logPasswordChange = TRUE;
			if ( $member->member_id )
			{
				$logPasswordChange = FALSE;
			}
			$member->setLocalPassword( \IPS\Request::i()->protect('password') );
			$member->save();

			if ( $logPasswordChange )
			{
				$member->memberSync( 'onPassChange', array( \IPS\Request::i()->protect('password') ) );
				$member->logHistory( 'core', 'password_change', 'api' );
			}

			$member->invalidateSessionsAndLogins();
		}
		else
		{
			$member->save();
		}

		/* Validation stuff */
		if( isset( \IPS\Request::i()->validated ) )
		{
			/* If the member is currently validating and we are setting the validated flag to true, then complete the validation */
			if( \IPS\Request::i()->validated == 1 AND $member->members_bitoptions['validating'] )
			{
				$member->validationComplete();
			}
			/* If the member is not currently validating, and we set the validated flag to false AND validation is enabled, mark the member validating */
			elseif( \IPS\Request::i()->validated == 0 AND !$member->members_bitoptions['validating'] AND \IPS\Settings::i()->reg_auth_type != 'none' )
			{
				$member->postRegistration();
			}
		}

		/* Any custom fields? */
		if( isset( \IPS\Request::i()->customFields ) )
		{
			/* Profile Fields */
			try
			{
				$profileFields = \IPS\Db::i()->select( '*', 'core_pfields_content', array( 'member_id=?', $member->member_id ) )->first();
			}
			catch( \UnderflowException $e )
			{
				$profileFields	= array();
			}

			/* If \IPS\Db::i()->select()->first() has only one column, then the contents of that column is returned. We do not want this here. */
			if ( !\is_array( $profileFields ) )
			{
				$profileFields = array();
			}

			$profileFields['member_id'] = $member->member_id;

			foreach ( \IPS\Request::i()->customFields as $k => $v )
			{
				$profileFields[ 'field_' . $k ] = $v;
			}

			\IPS\Db::i()->replace( 'core_pfields_content', $profileFields );

			$member->changedCustomFields = $profileFields;
			$member->save();
		}

		return $member;
	}

	/**
	 * POST /core/members
	 * Create a member. Requires the standard login handler to be enabled
	 *
	 * @apiclientonly
	 * @apiparam	string	name			Username
	 * @apiparam	string	email			Email address
	 * @apiparam	string	password		Password (standard login handler only)
	 * @apiparam	int		group			Group ID number
	 * @apiparam	string	registrationIpAddress		IP Address
	 * @apiparam	array	secondaryGroups	Secondary group IDs, or empty value to reset secondary groups
	 * @apiparam	object	customFields	Array of custom fields as fieldId => fieldValue
	 * @apiparam	int		validated		Flag to indicate if the account is validated (1) or not (0)
	 * @apiparam	array	rawProperties	Key => value object of member properties to set. Note that values will be set exactly as supplied without validation. USE AT YOUR OWN RISK.
	 * @throws		1C292/4	USERNAME_EXISTS			The username provided is already in use
	 * @throws		1C292/5	EMAIL_EXISTS			The email address provided is already in use
	 * @throws		1C292/6	INVALID_GROUP			The group ID provided is not valid
	 * @throws		1C292/7	INVALID_GROUP			A secondary group ID provided is not valid
	 * @throws		1C292/8	NO_USERNAME_OR_EMAIL	No Username or Email Address was provided for the account
	 * @throws		1C292/9	NO_PASSWORD				No password was provided for the account
	 * @return		\IPS\Member
	 */
	public function POSTindex()
	{
		/* One of these must be provided to ensure user can log in. */
		if ( !isset( \IPS\Request::i()->name ) AND !isset( \IPS\Request::i()->email ) )
		{
			throw new \IPS\Api\Exception( 'NO_USERNAME_OR_EMAIL', '1C292/8', 403 );
		}

		/* This is required as there is no other way to allow the account to be authenticated when it is created via the API */
		if ( !isset( \IPS\Request::i()->password ) )
		{
			throw new \IPS\Api\Exception( 'NO_PASSWORD', '1C292/9', 403 );
		}

		$member = new \IPS\Member;
		$member->member_group_id = \IPS\Settings::i()->member_group;
		$member->members_bitoptions['created_externally'] = TRUE;
		
		$member = $this->_createOrUpdate( $member );

		return new \IPS\Api\Response( 201, $member->apiOutput( $this->member ) );
	}

	/**
	 * POST /core/members/{id}
	 * Edit a member
	 *
	 * @apiclientonly
	 * @apiparam	string	name			Username
	 * @apiparam	string	email			Email address
	 * @apiparam	string	password		Password (standard login handler only)
	 * @apiparam	int		group			Group ID number
	 * @apiparam	string	registrationIpAddress		IP Address
	 * @apiparam	array	secondaryGroups	Secondary group IDs, or empty value to reset secondary groups
	 * @apiparam	object	customFields	Array of custom fields as fieldId => fieldValue
	 * @apiparam	int		validated		Flag to indicate if the account is validated (1) or not (0)
	 * @apiparam	array	rawProperties	Key => value object of member properties to set. Note that values will be set exactly as supplied without validation. USE AT YOUR OWN RISK.
	 * @param		int		$id			ID Number
	 * @throws		2C292/7	INVALID_ID	The member ID does not exist
	 * @throws		1C292/4	USERNAME_EXISTS	The username provided is already in use
	 * @throws		1C292/5	EMAIL_EXISTS	The email address provided is already in use
	 * @throws		1C292/6	INVALID_GROUP	The group ID provided is not valid
	 * @throws		1C292/7	INVALID_GROUP	A secondary group ID provided is not valid
	 * @return		\IPS\Member
	 */
	public function POSTitem( $id )
	{
		try
		{
			$member = \IPS\Member::load( $id );
			if ( !$member->member_id )
			{
				throw new \OutOfRangeException;
			}
			
			$oldPrimaryGroup = $member->member_group_id;
			$oldSecondaryGroups = array_unique( array_filter( explode( ',', $member->mgroup_others ) ) );
			$member = $this->_createOrUpdate( $member );
			
			if ( $oldPrimaryGroup != $member->member_group_id )
			{
				$member->logHistory( 'core', 'group', array( 'type' => 'primary', 'by' => 'api', 'apiKey' => $this->apiKey ? $this->apiKey->id : NULL, 'client' => $this->client ? $this->client->client_id : NULL, 'old' => $oldPrimaryGroup, 'new' => $member->member_group_id ), $this->member ?: FALSE );
			}
			$newSecondaryGroups = array_unique( array_filter( explode( ',', $member->mgroup_others ) ) );
			if ( array_diff( $oldSecondaryGroups, $newSecondaryGroups ) or array_diff( $newSecondaryGroups, $oldSecondaryGroups ) )
			{
				$member->logHistory( 'core', 'group', array( 'type' => 'secondary', 'by' => 'api', 'apiKey' => $this->apiKey ? $this->apiKey->id : NULL, 'client' => $this->client ? $this->client->client_id : NULL, 'old' => $oldSecondaryGroups, 'new' => $newSecondaryGroups ), $this->member ?: FALSE );
			}

			return new \IPS\Api\Response( 200, $member->apiOutput( $this->member ) );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '2C292/7', 404 );
		}
	}
	
	/**
	 * DELETE /core/members/{id}
	 * Deletes a member
	 *
	 * @apiclientonly
	 * @param		int		$id			ID Number
	 * @throws		1C292/2	INVALID_ID	The member ID does not exist
	 * @return		void
	 */
	public function DELETEitem( $id )
	{
		try
		{
			$member = \IPS\Member::load( $id );
			if ( !$member->member_id )
			{
				throw new \OutOfRangeException;
			}
			
			$member->delete();
			
			return new \IPS\Api\Response( 200, NULL );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '1C292/2', 404 );
		}
	}

	/**
	 * GET /core/members/{id}/follows
	 * Get list of items a member is following
	 *
	 * @param		int		$id			ID Number
	 * @apiparam	int		page		Page number
	 * @apiparam	int		perPage		Number of results per page - defaults to 25
	 * @return		\IPS\Api\PaginatedResponse<IPS\core\Followed\Follow>
	 * @throws		2C292/F	NO_PERMISSION	The authorized user does not have permission to view the follows
	 * @throws		2C292/I	INVALID_ID		The member could not be found
	 */
	public function GETitem_follows( $id )
	{
		try
		{
			/* Load member */
			$member = \IPS\Member::load( $id );
			if( !$member->member_id )
			{
				throw new \OutOfRangeException;
			}

			/* We can only adjust follows for ourself, if we are an authorized member */
			if ( $this->member and $member->member_id != $this->member->member_id )
			{
				throw new \IPS\Api\Exception( 'NO_PERMISSION', '2C292/F', 403 );
			}

			/* Return */
			return new \IPS\Api\PaginatedResponse(
				200,
				\IPS\Db::i()->select( '*', 'core_follow', array( 'follow_member_id=?', $member->member_id ), "follow_added ASC" ),
				isset( \IPS\Request::i()->page ) ? \IPS\Request::i()->page : 1,
				'IPS\core\Followed\Follow',
				\IPS\Db::i()->select( 'COUNT(*)', 'core_follow', array( 'follow_member_id=?', $member->member_id ) )->first(),
				$this->member,
				isset( \IPS\Request::i()->perPage ) ? \IPS\Request::i()->perPage : NULL
			);
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '2C292/I', 404 );
		}
	}

	/**
	 * POST /core/members/{id}/follows
	 * Store a new follow for the member
	 *
	 * @param		int		$id			ID Number
	 * @reqapiparam	string	followApp	Application of the content to follow
	 * @reqapiparam	string	followArea	Area of the content to follow
	 * @reqapiparam	int		followId	ID of the content to follow
	 * @apiparam	bool	followAnon	Whether or not to follow anonymously
	 * @apiparam	bool	followNotify	Whether or not to receive notifications
	 * @apiparam	string	followType		Type of notification to receive (immediate=send a notification immediately, daily=daily notification digest, weekly=weekly notification digest)
	 * @return		\IPS\core\Followed\Follow
	 * @throws		2C292/G	NO_PERMISSION	The authorized user does not have permission to view the follows
	 * @throws		2C292/H	INVALID_ID		The member could not be found
	 * @throws		2C292/J	INVALID_CONTENT	The app, area or content ID could not be found
	 */
	public function POSTitem_follows( $id )
	{
		try
		{
			/* Load member */
			$member = \IPS\Member::load( $id );
			if( !$member->member_id )
			{
				throw new \OutOfRangeException;
			}

			/* We can only adjust follows for ourself, if we are an authorized member */
			if ( $this->member and $member->member_id != $this->member->member_id )
			{
				throw new \IPS\Api\Exception( 'NO_PERMISSION', '2C292/G', 403 );
			}

			/* Make sure follow app/area/id is valid (Phil I'm looking at you) */
			try
			{
				$classToFollow	= 'IPS\\' . \IPS\Request::i()->followApp . '\\' . mb_ucfirst( \IPS\Request::i()->followArea );

				if( !class_exists( $classToFollow ) )
				{
					throw new \OutOfRangeException;
				}

				$thingToFollow	= $classToFollow::load( \IPS\Request::i()->followId );
			}
			catch( \Exception $e )
			{
				throw new \IPS\Api\Exception( 'INVALID_CONTENT', '2C292/J', 404 );
			}

			/* If we are already following this, update instead of insert */
			try
			{
				$follow = \IPS\core\Followed\Follow::load( md5( \IPS\Request::i()->followApp . ';' . \IPS\Request::i()->followArea . ';' . \IPS\Request::i()->followId . ';' . $member->member_id ) );
			}
			catch( \OutOfRangeException $e )
			{
				$follow = new \IPS\core\Followed\Follow;
				$follow->member_id	= $member->member_id;
				$follow->app		= \IPS\Request::i()->followApp;
				$follow->area		= \IPS\Request::i()->followArea;
				$follow->rel_id		= \IPS\Request::i()->followId;
			}

			$follow->is_anon	= ( isset( \IPS\Request::i()->followAnon ) ) ? (int) \IPS\Request::i()->followAnon : 0;
			$follow->notify_do	= ( isset( \IPS\Request::i()->followType ) AND \IPS\Request::i()->followType == 'none' ) ? 0 : ( ( isset( \IPS\Request::i()->followNotify ) ) ? (int) \IPS\Request::i()->followNotify : 1 );
			$follow->notify_freq	= ( isset( \IPS\Request::i()->followType ) AND \in_array( \IPS\Request::i()->followType, array( 'none', 'immediate', 'daily', 'weekly' ) ) ) ? \IPS\Request::i()->followType : 'immediate';
			$follow->save();

			/* If we're following a club, follow all nodes in the club automatically */
			if( $follow->app == 'core' and $follow->area == 'club' )
			{
				$thing = \IPS\Member\Club::loadAndCheckPerms( $follow->rel_id );
				
				foreach ( $thing->nodes() as $node )
				{
					$itemClass = $node['node_class']::$contentItemClass;
					$followApp = $itemClass::$application;
					$followArea = mb_strtolower( mb_substr( $node['node_class'], mb_strrpos( $node['node_class'], '\\' ) + 1 ) );

					/* If we are already following this, update instead of insert */
					try
					{
						$nodeFollow = \IPS\core\Followed\Follow::load( md5( $followApp . ';' . $followArea . ';' . $node['node_id'] . ';' . $member->member_id ) );
					}
					catch( \OutOfRangeException $e )
					{
						$nodeFollow = new \IPS\core\Followed\Follow;
						$nodeFollow->member_id	= $member->member_id;
						$nodeFollow->app		= $followApp;
						$nodeFollow->area		= $followArea;
						$nodeFollow->rel_id		= $node['node_id'];
					}
					
					$nodeFollow->is_anon	= $follow->is_anon;
					$nodeFollow->notify_do	= $follow->notify_do;
					$nodeFollow->notify_freq	= $follow->notify_freq;
					$nodeFollow->save();
				}
			}

			return new \IPS\Api\Response( 200, $follow->apiOutput( $this->member ) );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '2C292/H', 404 );
		}
	}

	/**
	 * DELETE /core/members/{id}/follows/{followKey}
	 * Delete a follow for the member
	 *
	 * @param		int		$id			ID Number
	 * @param		string	$followKey	Follow Key
	 * @throws		2C292/C	INVALID_ID			The member could not be found
	 * @throws		2C292/E	INVALID_FOLLOW_KEY	The follow does not exist or does not belong to this member
	 * @throws		2C292/D	NO_PERMISSION		The authorized user does not have permission to delete the follow
	 * @return		void
	 */
	public function DELETEitem_follows( $id, $followKey='' )
	{
		try
		{
			/* Load member */
			$member = \IPS\Member::load( $id );
			if( !$member->member_id )
			{
				throw new \OutOfRangeException;
			}

			/* We can only adjust follows for ourself, if we are an authorized member */
			if ( $this->member and $member->member_id != $this->member->member_id )
			{
				throw new \IPS\Api\Exception( 'NO_PERMISSION', '2C292/D', 403 );
			}
			
			/* Load our follow, and make sure it belongs to the specified member */
			try
			{
				if( !$followKey )
				{
					throw new \UnderflowException;
				}

				$follow = \IPS\Db::i()->select( '*', 'core_follow', array( 'follow_id=?', $followKey ) )->first();

				if( $follow['follow_member_id'] != $member->member_id )
				{
					throw new \UnderflowException;
				}
			}
			catch( \UnderflowException $e )
			{
				throw new \IPS\Api\Exception( 'INVALID_FOLLOW_KEY', '2C292/E', 404 );
			}

			/* Unfollow */
			\IPS\Db::i()->delete( 'core_follow', array( 'follow_id=?', $followKey ) );

			/* If this is a club, unfollow all nodes in the club too */
			if( $follow['follow_app'] == 'core' AND $follow['follow_area'] == 'club' )
			{
				$class = 'IPS\Member\Club';

				try
				{
					$thing = $class::loadAndCheckPerms( $follow['follow_rel_id'] );

					foreach ( $thing->nodes() as $node )
					{
						$itemClass = $node['node_class']::$contentItemClass;
						$followApp = $itemClass::$application;
						$followArea = mb_strtolower( mb_substr( $node['node_class'], mb_strrpos( $node['node_class'], '\\' ) + 1 ) );
						
						\IPS\Db::i()->delete( 'core_follow', array( 'follow_id=? AND follow_member_id=?', md5( $followApp . ';' . $followArea . ';' . $node['node_id'] . ';' .  $member->member_id ), $member->member_id ) );
					}
				}
				catch ( \OutOfRangeException $e ){}
			}

			return new \IPS\Api\Response( 200, NULL );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '2C292/C', 404 );
		}
	}

	/**
	 * GET /core/members/{id}/notifications
	 * Get list of notifications for a member
	 *
	 * @param		int		$id			ID Number
	 * @apiparam	int		page		Page number
	 * @apiparam	int		perPage		Number of results per page - defaults to 25
	 * @return		\IPS\Api\PaginatedResponse<IPS\Notification\Inline>
	 * @throws		2C292/K	NO_PERMISSION	The authorized user does not have permission to view the follows
	 * @throws		2C292/L	INVALID_ID		The member could not be found
	 */
	public function GETitem_notifications( $id )
	{
		try
		{
			/* Load member */
			$member = \IPS\Member::load( $id );
			if( !$member->member_id )
			{
				throw new \OutOfRangeException;
			}

			/* We can only fetch notifications for ourself, if we are an authorized member */
			if ( $this->member and $member->member_id != $this->member->member_id )
			{
				throw new \IPS\Api\Exception( 'NO_PERMISSION', '2C292/K', 403 );
			}

			/* Return */
			return new \IPS\Api\PaginatedResponse(
				200,
				\IPS\Db::i()->select( '*', 'core_notifications', array( '`member`=?', $member->member_id ), "updated_time ASC" ),
				isset( \IPS\Request::i()->page ) ? \IPS\Request::i()->page : 1,
				'IPS\Notification\Inline',
				\IPS\Db::i()->select( 'COUNT(*)', 'core_notifications', array( '`member`=?', $member->member_id ) )->first(),
				$this->member,
				isset( \IPS\Request::i()->perPage ) ? \IPS\Request::i()->perPage : NULL
			);
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '2C292/L', 404 );
		}
	}

	/**
	 * GET /core/members/{id}/warnings
	 * Get list of warnings for a member
	 *
	 * @param		int		$id			ID Number
	 * @apiparam	int		page		Page number
	 * @apiparam	int		perPage		Number of results per page - defaults to 25
	 * @return		\IPS\Api\PaginatedResponse<IPS\core\Warnings\Warning>
	 * @throws		2C292/M	NO_PERMISSION	The authorized user does not have permission to view the warnings
	 * @throws		2C292/N	INVALID_ID		The member could not be found
	 */
	public function GETitem_warnings( $id )
	{
		try
		{
			/* Load member */
			$member = \IPS\Member::load( $id );
			if( !$member->member_id )
			{
				throw new \OutOfRangeException;
			}

			/* We can only view warnings for ourself, if we are an authorized member */
			if ( $this->member and ( $member->member_id != $this->member->member_id OR !\IPS\Settings::i()->warn_show_own ) )
			{
				throw new \IPS\Api\Exception( 'NO_PERMISSION', '2C292/M', 403 );
			}

			/* Return */
			return new \IPS\Api\PaginatedResponse(
				200,
				\IPS\Db::i()->select( '*', 'core_members_warn_logs', array( 'wl_member=?', $member->member_id ), "wl_date ASC" ),
				isset( \IPS\Request::i()->page ) ? \IPS\Request::i()->page : 1,
				'IPS\core\Warnings\Warning',
				\IPS\Db::i()->select( 'COUNT(*)', 'core_members_warn_logs', array( 'wl_member=?', $member->member_id ) )->first(),
				$this->member,
				isset( \IPS\Request::i()->perPage ) ? \IPS\Request::i()->perPage : NULL
			);
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '2C292/N', 404 );
		}
	}

	/**
	 * GET /core/members/{id}/warning/{warning}
	 * Get a specific warning for a member
	 *
	 * @param		int		$id			ID Number
	 * @param		int		$warning	Warning ID
	 * @apiparam	int		page		Page number
	 * @apiparam	int		perPage		Number of results per page - defaults to 25
	 * @return		\IPS\core\Warnings\Warning
	 * @throws		2C292/T	NO_PERMISSION	The authorized user does not have permission to view the warning
	 * @throws		2C292/U	INVALID_ID		The member could not be found
	 * @throws		2C292/V	INVALID_WARNING		The warning could not be found
	 */
	public function GETitem_warning( $id, $warning = 0 )
	{
		try
		{
			/* Load member */
			$member = \IPS\Member::load( $id );
			if( !$member->member_id )
			{
				throw new \OutOfRangeException;
			}

			try
			{
				$warning = \IPS\core\Warnings\Warning::load( $warning );
			}
			catch( \OutOfRangeException $e )
			{
				throw new \IPS\Api\Exception( 'INVALID_WARNING', '2C292/V', 404 );
			}

			/* We can only view warnings for ourself, if we are an authorized member */
			if ( $this->member and ( $member->member_id != $this->member->member_id OR !\IPS\Settings::i()->warn_show_own OR $warning->member != $member->member_id ) )
			{
				throw new \IPS\Api\Exception( 'NO_PERMISSION', '2C292/T', 403 );
			}

			/* Return */
			return new \IPS\Api\Response( 200, $warning->apiOutput( $this->member ) );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '2C292/U', 404 );
		}
	}

	/**
	 * POST /core/members/{id}/warnings
	 * Store a new warning for the member
	 *
	 * @param		int		$id			ID Number
	 * @reqapiparam	int|null		moderator	Member ID of the moderator to issue the warning from
	 * @apiparam	int|null		reason		Warn reason to use for this warning
	 * @apiparam	int|null		points		Points to issue for the warning. Will use the points from the reason if not specified and a reason is, or if the reason does not allow points to be overridden.
	 * @apiparam	string|null		memberNote	Note to display to the member as HTML (e.g. "<p>This is a comment.</p>"). Will be sanatized for requests using an OAuth Access Token for a particular member; will be saved unaltered for requests made using an API Key or the Client Credentials Grant Type. 
	 * @apiparam	string|null		moderatorNote	Note to display to moderators as HTML (e.g. "<p>This is a comment.</p>"). Will be sanatized for requests using an OAuth Access Token for a particular member; will be saved unaltered for requests made using an API Key or the Client Credentials Grant Type. 
	 * @apiparam	bool			acknowledged	Whether the warning should be considered acknowledged by the member or not
	 * @apiparam	datetime|int|null		modQueue		Date to place in moderator queue until or -1 to place in moderator queue indefinitely. NULL to not place member in moderator queue.
	 * @apiparam	datetime|int|null		restrictPosts	Date to restrict posts until or -1 to restrict posts indefinitely. NULL to not restrict posts.
	 * @apiparam	datetime|int|null		suspend			Date to suspend member until or -1 to suspend indefinitely. NULL to not suspend.
	 * @apiparam	datetime|int|null		expire			Date to expire warn points after or -1 to not expire warn points. Will use the warn points expiration from the warn reason if not specified and a reason is, or if the reason does not allow warn point removal to be overridden. NULL to not expire.
	 * @return		\IPS\core\Warnings\Warning
	 * @throws		2C292/G	NO_PERMISSION	The authorized user does not have permission to warn the member
	 * @throws		2C292/O	INVALID_ID		The member could not be found
	 * @throws		1C292/Y	MODERATOR_REQUIRED		When not using an OAuth access token a moderator member ID must be supplied
	 * @note		The warning will be issued as the current authorized user if using an OAuth access token, otherwise the 'moderator' parameter must be specified to indicate which moderator the warning should be issued from
	 */
	public function POSTitem_warnings( $id )
	{
		try
		{
			/* Load member */
			$member = \IPS\Member::load( $id );
			if( !$member->member_id )
			{
				throw new \OutOfRangeException;
			}

			/* Make sure we can warn */
			if ( $this->member and !$this->member->canWarn( $member ) )
			{
				throw new \IPS\Api\Exception( 'NO_PERMISSION', '2C292/R', 403 );
			}

			/* Make sure we have a moderator */
			if( !$this->member AND ( !\IPS\Request::i()->moderator OR !\IPS\Member::load( \IPS\Request::i()->moderator )->member_id ) )
			{
				throw new \IPS\Api\Exception( 'MODERATOR_REQUIRED', '1C292/Y', 401 );
			}

			/* Start the warning with the easy stuff */
			$warning = new \IPS\core\Warnings\Warning;
			$warning->date		= time();
			$warning->member	= $member->member_id;
			$warning->moderator	= $this->member ? $this->member->member_id : \IPS\Member::load( \IPS\Request::i()->moderator )->member_id;

			$options = array(
				'warn_points'		=> (int) \IPS\Request::i()->points,
				'warn_reason'		=> \IPS\Request::i()->reason,
				'warn_member_note'	=> \IPS\Request::i()->memberNote,
				'warn_mod_note'		=> \IPS\Request::i()->modertorNote,
				'warn_punishment'	=> array(),
				'warn_remove'		=> NULL,
			);

			if ( $this->member )
			{
				$options['warn_member_note']	= \IPS\Text\Parser::parseStatic( $options['warn_member_note'], TRUE, NULL, $this->member, 'core_Modcp' );
				$options['warn_mod_note']		= \IPS\Text\Parser::parseStatic( $options['warn_mod_note'], TRUE, NULL, $this->member, 'core_Modcp' );
			}

			if( isset( \IPS\Request::i()->expire ) )
			{
				if( \IPS\Request::i()->expire == -1 )
				{
					$options['warn_remove']	= \IPS\Request::i()->expire;
				}
				else
				{
					$options['warn_remove'] = new \IPS\DateTime( \IPS\Request::i()->expire );
				}
			}

			if( \IPS\Request::i()->modQueue )
			{
				if( \IPS\Request::i()->modQueue == -1 )
				{
					$options['warn_punishment']['mq']	= \IPS\Request::i()->modQueue;
				}
				else
				{
					$options['warn_punishment']['mq'] = new \IPS\DateTime( \IPS\Request::i()->modQueue );
				}
			}

			if( \IPS\Request::i()->restrictPosts )
			{
				if( \IPS\Request::i()->restrictPosts == -1 )
				{
					$options['warn_punishment']['rpa']	= \IPS\Request::i()->restrictPosts;
				}
				else
				{
					$options['warn_punishment']['rpa'] = new \IPS\DateTime( \IPS\Request::i()->restrictPosts );
				}
			}

			if( \IPS\Request::i()->suspend )
			{
				if( \IPS\Request::i()->suspend == -1 )
				{
					$options['warn_punishment']['suspend']	= \IPS\Request::i()->suspend;
				}
				else
				{
					$options['warn_punishment']['suspend'] = new \IPS\DateTime( \IPS\Request::i()->suspend );
				}
			}

			$options = $warning->processWarning( $options, \IPS\Member::load( $warning->moderator ), TRUE );

			if( \IPS\Settings::i()->warnings_acknowledge AND isset( \IPS\Request::i()->acknowledged ) )
			{
				$warning->acknowledged = \IPS\Request::i()->acknowledged;
			}

			if( !$warning->expire_date )
			{
				$warning->expire_date = -1;
			}

			$warning->save();

			/* Now apply the consequences */
			if ( $warning->points )
			{
				$member->warn_level += $warning->points;
			}
			$consequences = array();
			foreach ( array( 'mq' => 'mod_posts', 'rpa' => 'restrict_post', 'suspend' => 'temp_ban' ) as $k => $v )
			{
				if ( $warning->$k )
				{
					$consequences[ $v ] = $warning->$k;
					if ( $warning->$k != -1 )
					{
						$member->$v = \IPS\DateTime::create()->add( new \DateInterval( $warning->$k ) )->getTimestamp();
					}
					else
					{
						$member->$v = $this->$k;
					}
				}
			}
			$member->members_bitoptions['unacknowledged_warnings'] = \IPS\Settings::i()->warnings_acknowledge ? (bool) !$warning->acknowledged : FALSE;
			$member->save();
			$member->logHistory( 'core', 'warning', array( 'wid' => $warning->id, 'by' => 'api', 'points' => $warning->points, 'reason' => $warning->reason, 'consequences' => $consequences ) );

			return new \IPS\Api\Response( 200, $warning->apiOutput( $this->member ) );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '2C292/O', 404 );
		}
	}

	/**
	 * DELETE /core/members/{id}/warnings/{warning}
	 * Delete (undo) a warning for the member
	 *
	 * @param		int		$id			ID Number
	 * @param		int		$warningId	Warning ID
	 * @apiparam	bool	undoOnly	Only undo the warning but do not delete it
	 * @throws		2C292/P	INVALID_ID			The member could not be found
	 * @throws		2C292/Q	INVALID_WARNING		The warning could not be loaded or the current authorized user does not have permission to delete the warning
	 * @return		void
	 */
	public function DELETEitem_warnings( $id, $warningId = 0 )
	{
		try
		{
			/* Load member */
			$member = \IPS\Member::load( $id );
			if( !$member->member_id )
			{
				throw new \OutOfRangeException;
			}

			/* Load the warning and then undo it */
			try
			{
				$warning = \IPS\core\Warnings\Warning::load( $warningId );

				if( $this->member AND !$warning->canDelete( $this->member ) )
				{
					throw new \OutOfRangeException;
				}
			}
			catch( \OutOfRangeException $e )
			{
				throw new \IPS\Api\Exception( 'INVALID_WARNING', '2C292/Q', 404 );
			}

			/* Revoke the warning */
			$warning->undo();

			if( !isset( \IPS\Request::i()->undoOnly ) OR !\IPS\Request::i()->undoOnly )
			{
				$warning->delete();
			}

			return new \IPS\Api\Response( 200, NULL );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '2C292/P', 404 );
		}
	}

	/**
	 * POST /core/members/{id}/warnings/{warning}/acknowledge
	 * Acknowledge a warning
	 *
	 * @param		int		$id			ID Number
	 * @param		int		$warningId	Warning to acknowledge
	 * @return		\IPS\core\Warnings\Warning
	 * @throws		2C292/M	NO_PERMISSION	The authorized user does not have permission to view the warnings
	 * @throws		2C292/N	INVALID_ID		The member could not be found
	 */
	public function POSTitem_warnings_acknowledge( $id, $warningId = 0 )
	{
		try
		{
			/* Load member */
			$member = \IPS\Member::load( $id );
			if( !$member->member_id )
			{
				throw new \OutOfRangeException;
			}

			/* Load the warning */
			try
			{
				$warning = \IPS\core\Warnings\Warning::load( $warningId );
			}
			catch( \OutOfRangeException $e )
			{
				throw new \IPS\Api\Exception( 'INVALID_WARNING', '2C292/W', 404 );
			}

			/* We can only view warnings for ourself, if we are an authorized member */
			if ( $this->member and !$warning->canAcknowledge( $this->member ) )
			{
				throw new \IPS\Api\Exception( 'NO_PERMISSION', '2C292/X', 403 );
			}

			/* Acknowledge it */
			$warning->acknowledged = TRUE;
			$warning->save();

			$member->members_bitoptions['unacknowledged_warnings'] = (bool) \IPS\Db::i()->select( 'COUNT(*)', 'core_members_warn_logs', array( "wl_member=? AND wl_acknowledged=0", $member->member_id ), NULL, NULL, NULL, NULL, \IPS\Db::SELECT_FROM_WRITE_SERVER )->first();
			$member->save();

			/* Return */
			return new \IPS\Api\Response( 200, $warning->apiOutput() );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '2C292/N', 404 );
		}
	}
}