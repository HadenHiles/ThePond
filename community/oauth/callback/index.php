<?php
/**
 * @brief		OAuth Client Redirection Endpoint
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		31 May 2017
 */

\define('REPORT_EXCEPTIONS', TRUE);
require '../../init.php';
\IPS\Session\Front::i();

if ( isset( \IPS\Request::i()->state ) and $explodedData = explode( '-', \IPS\Request::i()->state ) and \count( $explodedData ) === 4 and $destination = @base64_decode( $explodedData[1] ) )
{
	try
	{
		$destination = \IPS\Http\Url::createFromString( $destination )->setQueryString( array(
			'_processLogin'	=> $explodedData[0],
			'csrfKey'		=> $explodedData[2],
			'ref'			=> $explodedData[3],
		) );
		if ( !( $destination instanceof \IPS\Http\Url\Internal ) )
		{
			throw new \Exception;
		}
		
		if ( isset( \IPS\Request::i()->error ) )
		{
			foreach ( array( 'error', 'error_description', 'error_uri' ) as $k )
			{
				if ( isset( \IPS\Request::i()->$k ) )
				{
					$destination = $destination->setQueryString( $k, \IPS\Request::i()->$k );
				}
			}
		}

		/* OAuth 2 */
		if ( isset( \IPS\Request::i()->access_token ) )
		{
			foreach ( array( 'access_token', 'token_type', 'expires_in', 'scope', 'state' ) as $k )
			{
				if ( isset( \IPS\Request::i()->$k ) )
				{
					$destination = $destination->setQueryString( $k, \IPS\Request::i()->$k );
				}
			}
		}
		/* OAuth 1 */
        elseif ( isset( \IPS\Request::i()->oauth_token ) )
		{
			foreach ( array( 'oauth_token', 'oauth_verifier', 'state' ) as $k )
			{
				if ( isset( \IPS\Request::i()->$k ) )
				{
					$destination = $destination->setQueryString( $k, \IPS\Request::i()->$k );
				}
			}
		}
		elseif ( isset( \IPS\Request::i()->code ) )
		{
			$destination = $destination->setQueryString( 'code', \IPS\Request::i()->code );
		}
						
		\IPS\Output::i()->redirect( $destination );
		exit;
	}
	catch ( \Exception $e ) {}
}

$url = (string) \IPS\Http\Url::internal( 'oauth/callback/', 'none' );
?><!DOCTYPE html>
<html>
	<head>
		<title><?php echo \IPS\Member::loggedIn()->language()->get( 'loading' ); ?></title>
		<script>
			if ( window.location.hash ) {
				var hash = window.location.hash.substr( 0, 1 ) == '#' ? window.location.hash.substr( 1 ) : window.location.hash;
				window.location = "<?php echo $url; ?>?" + hash;
			}
		</script>
	</head>
	<body>
		<noscript><?php echo \IPS\Member::loggedIn()->language()->get( 'oauth_implicit_no_js' ); ?></noscript>
	</body>
</html>