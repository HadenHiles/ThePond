<?php
/**
 * @brief		Facebook Promotion
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		10 Feb 2017
 */

namespace IPS\Content\Promote;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Facebook Promotion
 */
class _Facebook extends PromoteAbstract
{
	/** 
	 * @brief	Icon
	 */
	public static $icon = 'facebook';
	
	/**
	 * @brief Default settings
	 */
	public $defaultSettings = array(
		'token' => NULL,
		'owner' => NULL,
		'page_name' => NULL, //
		'permissions' => NULL,
		'members' => NULL,
		'page' => NULL, //
		'tags' => NULL,
		'image' => NULL,
		'last_sync' => 0,
		'shareable' => array(),
		'member_token' => array(),
	);
	
	/**
	 * Get image
	 *
	 * @return string
	 */
	public function getPhoto()
	{
		if ( ! $this->settings['image'] or $this->settings['last_sync'] < time() - 86400 )
		{ 
			/* Fetch again */
			$response = \IPS\Http\Url::external( "https://graph.facebook.com/" . $this->settings['page'] . "/picture?type=large&appsecret_proof=" . static::appSecretProof( $this->settings['token'] ) )->request()->get();
			 
			$extension = str_replace( 'image/', '', $response->httpHeaders['Content-Type'] );
			$newFile = \IPS\File::create( 'core_Promote', 'facebook_' . $this->settings['page'] . '.' . $extension, (string) $response, NULL, FALSE, NULL, FALSE );
			 
			$this->saveSettings( array( 'image' => (string) $newFile->url, 'last_sync' => time() ) );
		}
		 
		return $this->settings['image'];
	}
	
	/**
	 * Get name
	 *
	 * @param	string|NULL	$serviceId		Specific page/group ID
	 * @return string
	 */
	public function getName( $serviceId=NULL )
	{
		if ( $serviceId )
		{
			$realId = mb_substr( $serviceId, 2 );
			if ( mb_substr( $serviceId, 0, 2 ) == 'p_' )
			{
				return isset( $this->settings['shareable']['pages'][ $realId ]['name'] ) ? $this->settings['shareable']['pages'][ $realId ]['name'] : \IPS\Member::loggedIn()->language()->addToStack( 'promote_service_name_missing' );
			}
			else
			{
				return isset( $this->settings['shareable']['groups'][ $realId ]['name'] ) ? $this->settings['shareable']['groups'][ $realId ]['name'] : \IPS\Member::loggedIn()->language()->addToStack( 'promote_service_name_missing' );
			}
		}
	}
	
	/**
	 * Check publish permissions
	 *
	 * @return	boolean
	 */
	public function canPostToPage()
	{
		$facebook = static::getLoginHandler();
		
		$authorizedScopes = $facebook->authorizedScopes( $this->member );

		if( $authorizedScopes === NULL )
		{
			return FALSE;
		}
		
		foreach( array( 'manage_pages', 'publish_pages' ) as $perm )
		{
			if ( ! \in_array( $perm, $authorizedScopes ) )
			{
				return FALSE;
			}
		}
		
		return TRUE;
	}
	
	/**
	 * Create the appSecretProof key
	 *
	 * @param	string	$token	The Token
	 * @return	string
	 */
	protected static function appSecretProof( $token )
	{
		$facebook = static::getLoginHandler();
		return hash_hmac( 'sha256', $token, $facebook->settings['client_secret'] );
	}
		
	/**
	 * Get form elements for this share service
	 *
	 * @param	string		$text		Text for the text entry
	 * @param	string		$link		Short or full link (short when available)
	 * @param	string		$content	Additional text content (usually a comment, or the item content)
	 *
	 * @return array of form elements
	 */
	public function form( $text, $link=null, $content=null )
	{
		$shareable = array();
		$_text = '';
		
		if ( $this->promote and $this->promote->id )
		{
			$_text = $text;
			
			if ( isset( $this->promote->form_data['facebook'] ) AND $settings = $this->promote->form_data['facebook'] )
			{
				$shareable = $settings['shareable'];
			}
		}
		else
		{
			if ( \count( $this->settings['tags'] ) )
			{
				$_text .= '#' . implode( ' #', $this->settings['tags'] );
			} 
		}
		
		$names = array();
		$options = array();
		if ( isset( $this->settings['shareable'] ) )
		{
			if( isset( $this->settings['shareable']['pages'] ) )
			{
				foreach( $this->settings['shareable']['pages'] as $id => $data )
				{
					$options[ 'p_' . $id ] = \IPS\Member::loggedIn()->language()->addToStack( 'promote_facebook_page_prefix', NULL, array( 'sprintf' => array( $data['name'] ) ) );
					$names[] = $data['name'];
				}
			}
			
			if( isset( $this->settings['shareable']['groups'] ) )
			{
				foreach( $this->settings['shareable']['groups'] as $id => $data )
				{
					$options[ 'g_' . $id ] = \IPS\Member::loggedIn()->language()->addToStack( 'promote_facebook_group_prefix', NULL, array( 'sprintf' => array( $data['name'] ) ) );
					$names[] = $data['name'];
				}
			}
		}
		
		return array(
			new \IPS\Helpers\Form\TextArea( 'promote_social_content_facebook', $_text, FALSE, array( 'maxLength' => 2000, 'rows' => 10 ) ),
			new \IPS\Helpers\Form\CheckboxSet( 'promote_facebook_shareable', $shareable, FALSE, array( 'options' => $options ) )
		);
	}
	
	/**
	 * Allow for any extra processing
	 *
	 * @param	array	$values	Values from the form isn't it though
	 * @return	mixed
	 */
	public function processPromoteForm( $values )
	{
		if ( isset( $values['promote_facebook_shareable'] ) )
		{
			return array( 'shareable' => $values['promote_facebook_shareable'] );
		}
		
		return NULL;
	}
	 
	/**
	 * Post to Facebook
	 *
	 * @param	\IPS\core\Promote	$promote 	Promote Object
	 * @return void
	 */
	public function post( $promote )
	{
		$photos = $promote->imageObjects();
		
		$settings = $promote->form_data['facebook'];
		$shareable = $this->settings['shareable'];
		$responses = array();

		foreach( $settings['shareable'] as $id )
		{
			$feedId = mb_substr( $id, 2 );

			if ( mb_substr( $id, 0, 2 ) == 'p_' )
			{
				$responses[ $id ][] = $this->_post( $promote, $photos, $feedId, $shareable['pages'][ $feedId ]['token'] );
			}
			else
			{
				$responses[ $id ][] = $this->_post( $promote, $photos, $feedId, isset( $this->settings['member_token']['access_token'] ) ? $this->settings['member_token']['access_token'] : $this->settings['token'] );
			}
		}

		return $responses;
	}
	
	/**
	 * Actually post the post, but not post post as that would be too late
	 *
	 * @param	\IPS\core\Promote	$promote 	Promote Object
	 * @param	array				$photos		The photos
	 * @param	int					$feedId		The feed ID to post to. Honestly, it's not rocket science.
	 * @param	string				$token		I'll give you one guess. Does anyone actually read this?
	 * @return stuff
	 */
	protected function _post( $promote, $photos, $feedId, $token=NULL )
	{
		/* Get the last 20 items to see if we have a duplicate */
		try
		{
			$items = \IPS\Http\Url::external( "https://graph.facebook.com/" . $feedId . "/feed" )->setQueryString( $this->_addToken( array(
				'limit' => 20
			), $token ) )->request()->get()->decodeJson();

			if ( isset( $items['data'] ) )
			{
				foreach( $items['data'] as $item )
				{
					$time = new \IPS\DateTime( ( isset( $item['created_time'] ) ? $item['created_time'] : $item['updated_time' ] ) );
					$now = new \IPS\DateTime();
					
					/* Only look back past the last 30 mins */
					if ( \intval( $now->diff( $time )->format('%i') ) > 30 )
					{
						continue;
					}
					
					if ( preg_replace( '#\s{1,}#', " ", $promote->text['facebook'] ) == preg_replace( '#\s{1,}#', " ", $item['message'] ) )
					{
						/* Duplicate */
						return $item['id'];
					}
				}
			}
		}
		catch( \Exception $e )
		{
			\IPS\Log::log( $e, 'facebook' );
		}
		
		if ( ! $photos or \count( $photos ) === 1 )
		{
			/* Simple message */
			try
			{
				if ( $photos )
				{
					$thePhoto = array_pop( $photos );

					$response = \IPS\Http\Url::external( "https://graph.facebook.com/" . $feedId . "/photos" )->request()->post( $this->_addToken( array(
						'message' => $promote->text['facebook'],
						'url' => (string) $this->returnUrlWithProtocol( $thePhoto->url ),
						'link' => (string) $promote->short_link
					), $token ) )->decodeJson();
				}
				else
				{
					$response = \IPS\Http\Url::external( "https://graph.facebook.com/" . $feedId . "/feed" )->request()->post( $this->_addToken( array(
						'message' => $promote->text['facebook'],
						'link' => (string) $promote->short_link
					), $token ) )->decodeJson();
				}
			}
			catch( \Exception $e )
			{
				\IPS\Log::log( $e, 'facebook' );
				throw new \InvalidArgumentException( \IPS\Member::loggedIn()->language()->addToStack('facebook_publish_exception') );
			}
			
			if ( isset( $response['id'] ) )
			{
				return $response['id'];
			}
			else
			{
				if ( isset( $response['error'] ) )
				{
					\IPS\Log::log( $response['error']['message'], 'facebook' );
				}
				
				throw new \InvalidArgumentException( \IPS\Member::loggedIn()->language()->addToStack('facebook_publish_exception') );
			}
		}
		else
		{
			/* We have multiple photos so we first need to create an album, and then upload into that */
			try
			{
				/* The item may be a comment, which uses a language string for objectTitle - make sure that is parsed before sending */
				$title = $promote->objectTitle;
				\IPS\Member::loggedIn()->language()->parseOutputForDisplay( $title );

				$response = \IPS\Http\Url::external( "https://graph.facebook.com/" . $feedId . "/albums" )->request()->post( $this->_addToken( array(
					'name' => $title,
				), $token ) )->decodeJson();
				
				if ( ! isset( $response['id'] ) )
				{
					throw new \InvalidArgumentException('Could not create album');
				}
				
				$newAlbumId = $response['id'];
				
				foreach( $photos as $photo )
				{
					$response = \IPS\Http\Url::external( "https://graph.facebook.com/" . $newAlbumId . "/photos" )->request()->post( $this->_addToken( array(
						'message' => $promote->text['facebook'],
						'url' => (string) $this->returnUrlWithProtocol( $photo->url ),
					), $token ) )->decodeJson();
				}
			}
			catch( \Exception $e )
			{
				\IPS\Log::log( $e, 'facebook' );
				
				throw new \InvalidArgumentException( \IPS\Member::loggedIn()->language()->get('facebook_publish_exception') );
			}
			
			if ( isset( $response['id'] ) )
			{
				return $response['id'];
			}
			else
			{
				throw new \InvalidArgumentException( \IPS\Member::loggedIn()->language()->addToStack('facebook_publish_exception') );
			}
		}
	}
	
	/**
	 * Adds 'access_token' to query string if token defined (it is for pages, but not for groups
	 *
	 * @param	array	$values		The values of the array
	 * @param	string|NULL	$token	The token obvs
	 * @return array
	 */
	protected function _addToken( $values, $token )
	{
		return ( $token ) ? array_merge( $values, array( 'access_token' => $token, 'appsecret_proof' => static::appSecretProof( $token ) ) ) : $values;
	}
	
	/**
	 * Return the published URL
	 *
	 * @param	array	$data	Data returned from a successful POST
	 * @return	\IPS\Http\Url
	 * @throws \InvalidArgumentException
	 */
	public function getUrl( $data )
	{
		if ( \is_array( $data ) and \count( $data ) )
		{
			/* Get a random URL from the many it was shared to */
			$array = $data[ array_rand( $data ) ];
			$data = array_pop( $array );
		}

		if ( $data and preg_match( '#^[0-9_]*$#', $data ) )
		{
			return \IPS\Http\Url::external( 'https://facebook.com/' . $data );
		}
		
		throw new \InvalidArgumentException();
	}
	
	/**
	 * Is this member connected to the Facebook app?
	 *
	 * @param	\IPS\Member		$member		A member object
	 * @return	boolean
	 */
	public function isConnected( $member )
	{
		if ( \is_numeric( \IPS\Settings::i()->promote_facebook_auth ) )
		{
			/* Standard handler */
			$facebook = \IPS\Login\Handler::findMethod('IPS\Login\Handler\Oauth2\Facebook');
			return $facebook->canProcess( $member );
		}
		
		/* Custom */
		if ( isset( $this->settings['member_token']['access_token'] ) )
		{
			return TRUE;
		}
		
		return FALSE;
	}
	
	/**
	 * Ensure the URL is not protocol relative
	 *
	 * @return \IPS\Http\Url
	 */
	protected function returnUrlWithProtocol( \IPS\Http\Url $url )
	{
		if ( ! $url->data['scheme'] )
		{
			$url = $url->setScheme( ( \substr( \IPS\Settings::i()->base_url, 0, 5 ) == 'https' ) ? 'https' : 'http' );
		}
		
		return $url;
	}
	
	/**
	 * Wrapper to Facebook log in handler with the correct settings
	 *
	 * @return \IPS\Login object
	 */
	public static function getLoginHandler()
	{
		$facebook = \IPS\Login\Handler::findMethod('IPS\Login\Handler\Oauth2\Facebook');
		
		/* We are using the default app here */
		if ( ! \is_numeric( \IPS\Settings::i()->promote_facebook_auth ) )
		{
			$facebook->settings = json_decode( \IPS\Settings::i()->promote_facebook_auth, TRUE );
		}
		
		return $facebook;
	}
}