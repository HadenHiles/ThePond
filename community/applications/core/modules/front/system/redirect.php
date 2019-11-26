<?php
/**
 * @brief		External redirector with key checks
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		12 Jun 2013
 */

namespace IPS\core\modules\front\system;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Redirect
 */
class _redirect extends \IPS\Dispatcher\Controller
{
	/**
	 * Handle munged links
	 *
	 * @return	void
	 */
	protected function manage()
	{
		/* First check the key to make sure this actually came from HTMLPurifier */
		if ( \IPS\Login::compareHashes( hash_hmac( "sha256", \IPS\Request::i()->url, \IPS\Settings::i()->site_secret_key ), (string) \IPS\Request::i()->key ) OR \IPS\Login::compareHashes( hash_hmac( "sha256", \IPS\Request::i()->url, \IPS\Settings::i()->site_secret_key . 'r' ), (string) \IPS\Request::i()->key ) )
		{
			/* Construct the URL */
			$url = \IPS\Http\Url::external( \IPS\Request::i()->url );

			/* If this is coming from email tracking, log the click */
			if( isset( \IPS\Request::i()->email ) AND \IPS\Settings::i()->prune_log_emailstats != 0 )
			{
				/* If we have a row for "today" then update it, otherwise insert one */
				$today = \IPS\DateTime::create()->format( 'Y-m-d' );

				try
				{
					/* We only include the time column in the query so that the db index can be effectively used */
					if( !\IPS\Request::i()->type )
					{
						$currentRow = \IPS\Db::i()->select( '*', 'core_statistics', array( 'type=? AND time>? AND value_4=? AND extra_data IS NULL', 'email_clicks', 1, $today ) )->first();
					}
					else
					{
						$currentRow = \IPS\Db::i()->select( '*', 'core_statistics', array( 'type=? AND time>? AND value_4=? AND extra_data=?', 'email_clicks', 1, $today, \IPS\Request::i()->type ) )->first();
					}

					\IPS\Db::i()->update( 'core_statistics', "value_1=value_1+1", array( 'id=?', $currentRow['id'] ) );
				}
				catch( \UnderflowException $e )
				{
					\IPS\Db::i()->insert( 'core_statistics', array( 'type' => 'email_clicks', 'value_1' => 1, 'value_4' => $today, 'time' => time(), 'extra_data' => \IPS\Request::i()->type ) );
				}
			}

			/* If it's a resource (image, etc.), we pull the actual contents to prevent the referrer being exposed (which is an issue in the ACP where the session ID is private) */
			if ( \IPS\Request::i()->resource )
			{
				/* Except if it's internal or localhost, we can't make a HTTP request to it because doing that would potentially allow access to secured resources because the server
					thinks the request is internal. We don't need to worry about about things on this domain getting access to the referrer anyway */
				if ( $url->isLocalhost() )
				{
					\IPS\Output::i()->redirect( $url, NULL, 303 );
				}
				/* For everything else, pull the contents... */
				else
				{
					/* It can't be protocol relative */
					if ( !$url->data['scheme'] )
					{
						$url = \IPS\Http\Url::external( 'http:' . \IPS\Request::i()->url );
					}

					/* Get the contents */
					try
					{
						$response = \IPS\Http\Url::external( $url )->request( \IPS\DEFAULT_REQUEST_TIMEOUT, null, 5, true )->get();
					}
					catch ( \Exception $e )
					{
						\IPS\Output::i()->redirect( \IPS\Http\Url::internal('') );
					}

					/* If this is a safe content type, output it - otherwise perform a safe redirect */
					$contentType = isset( $response->httpHeaders['Content-Type'] ) ? $response->httpHeaders['Content-Type'] : 'unknown/unknown';

					if( \in_array( array_search( $contentType, \IPS\File::$mimeTypes ), \IPS\File::$safeFileExtensions ) )
					{					
						/* Send output - we only want to pass along the content and content type. We can't allow the response
							to perform a redirect (which would cause the referrer to be exposed) and we don't want to pass
							along any of their headers which could include setting cookies, etc. */
						\IPS\Output::i()->sendOutput( $response->content, $response->httpResponseCode == 200 ? 200 : 404, $contentType );
					}
					else
					{
						\IPS\Output::i()->redirect( $url, \IPS\Request::i()->email ? '' : \IPS\Member::loggedIn()->language()->addToStack('external_redirect'), 303, \IPS\Request::i()->email ? FALSE : TRUE );
					}
				}
			}
			/* For everything else, we'll do a 303 redirect */
			else
			{
				\IPS\Output::i()->redirect( $url, \IPS\Request::i()->email ? '' : \IPS\Member::loggedIn()->language()->addToStack('external_redirect'), 303, \IPS\Request::i()->email ? FALSE : TRUE );
			}
		}
		/* If it doesn't validate, send the user to the index page */
		else
		{
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal('') );
		}
	}

	/**
	 * Redirect an ACP click
	 *
	 * @note	The purpose of this method is to avoid exposing \IPS\CP_DIRECTORY to non-admins
	 * @return	void
	 */
	protected function admin()
	{
		if( \IPS\Member::loggedIn()->isAdmin() )
		{
			$queryString	= base64_decode( \IPS\Request::i()->_data );
			\IPS\Output::i()->redirect( new \IPS\Http\Url( \IPS\Http\Url::baseUrl() . \IPS\CP_DIRECTORY . '/?' . $queryString, TRUE ) );
		}

		\IPS\Output::i()->error( 'no_access_cp', '2C159/3', 403 );
	}

	/**
	 * Redirect an advertisement click
	 *
	 * @return	void
	 */
	protected function advertisement()
	{
		/* Get the advertisement */
		$advertisement	= array();

		if( isset( \IPS\Request::i()->ad ) )
		{
			try
			{
				$advertisement	= \IPS\core\Advertisement::load( \IPS\Request::i()->ad );
			}
			catch( \OutOfRangeException $e )
			{
				\IPS\Output::i()->error( 'ad_not_found', '2C159/2', 404, 'ad_not_found_admin' );
			}
		}

		if( !$advertisement->id OR !$advertisement->link )
		{
			\IPS\Output::i()->error( 'ad_not_found', '2C159/1', 404, 'ad_not_found_admin' );
		}

		if ( \IPS\Login::compareHashes( hash_hmac( "sha256", $advertisement->link, \IPS\Settings::i()->site_secret_key ), (string) \IPS\Request::i()->key ) OR \IPS\Login::compareHashes( hash_hmac( "sha256", $advertisement->link, \IPS\Settings::i()->site_secret_key . 'a' ), (string) \IPS\Request::i()->key ) )
		{
			/* We need to update click count for this advertisement. Does it need to be shut off too due to hitting click maximum?
				Note that this needs to be done as a string to do "col=col+1", which is why we're not using the ActiveRecord save() method.
				Updating by doing col=col+1 is more reliable when there are several clicks at nearly the same time. */
			$update	= "ad_clicks=ad_clicks+1";

			if( $advertisement->maximum_unit == 'c' AND $advertisement->maximum_value > -1 AND $advertisement->clicks + 1 >= $advertisement->maximum_value )
			{
				$update	.= ", ad_active=0";
			}

			/* Update the database */
			\IPS\Db::i()->update( 'core_advertisements', $update, array( 'ad_id=?', $advertisement->id ) );

			/* And do the redirect */
			\IPS\Output::i()->redirect( \IPS\Http\Url::external( $advertisement->link ) );
		}
		/* If it doesn't validate, send the user to the index page */
		else
		{
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal('') );
		}
	}
}