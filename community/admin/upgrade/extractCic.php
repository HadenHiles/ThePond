<!DOCTYPE html>
<html lang="en">
<head>
<title>Invision Community Update Extractor</title>
<style type='text/css'>.ipsType_text{text-align:center;font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;font-size: 13px;line-height: 18px;}.ipsLoader_container{ position: absolute; top:0;left:0;right:0;bottom:0;display: flex;flex-direction:column;justify-content:center;align-items:center;}.ipsLoader {display: inline-block; width: 48px;height: 48px;background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAAE70lEQVR4Xu2bjZHUMAyFdRUAFQAVwFUAVwFQAVwFQAVwFQAVABUAFcBVAFQAHXBUAPPtWDuO1n9xnKwT0MzO7u0ljvUkPcm29kT+cTlZSP+7InJHRG6JyH33TL67bp7/U0R4fXPvl+7zbNOcE4AHIvLQKYziU+SjiPD6JCJXUway97YGAIs+FZEnztot56pjvRORN608oxUAqvizgFvPAQJjfhGRC/de/YwWAGDxlwsqbpXFIwAC7hgtUwCAxN6KCO/HFnjh3PHEqLnUAgC5obxl8dDDfzs3hdlxWybLZ18YByAhS16MT9YYK3jD8zFEWQPAKxEh1lOC0srcvNcIoAAEhHpvxACAe1YKwlgAsDoTigmKv3avlukKr4BnHhcCAQiEhPW0g9vHAJBTHiJC+ZaK2wkDBG5e4hHM43ZuPqUAgP6LCPrfnVdk0S60XsllhCBzupa5OBsOJQDg8lg/JO8dH8xp9ZiOkCb8crMAhNPYNTkAeMjnCNvj8ljhmAJRkllyGYPKMUjcOQC+RvI8BEMs9iClIDwK1QkpAGJx34PlLfCAQLynwiFIijEAYFusbwsdVmPk5h6FcCUcUsR4EAoxAHBvm3Nhe9byxyC8UsCJcwq1lJAa9+uGEABY/0dgBKorEO5dmGOqTiBz7Yu5EAAUM6zwfImyaIdoxAzoT3XvBSEAfpnYp7xl0J5d39ohFML+NXsvsABAcB/MaD2yfs7xcl6AMW8wiAUghBwXrsn6Cg5VIvuSMdnVMhYA6/4DwsjB3tn/UyU8U93p5gNAHiX3+xKsnjpTNDYdahgMGpNdGPgAhHJorlTuHYtcSjz1FbTpj0MJPcToXdHY/FLLeO459wGwaK2R/S0QoazmX3PhA2BXfmuOf1USD2Y5H5NLH4A/5qq1lL658LR6+dcnAVhr/reApAC4SnnA2jOAApECYFAJ2gv/OQAoDPzNhK0AkKoFBhxgL9wKAKlMcJaqA9hKXnKvP8fmU/7PuoBCTz2cJT6V72AxZKumraRBHziU1kPa3fe+B9jVE6esoLYlIRwG23o+AHYToecd4FqjUBoPTqst0bFbqnvr+12T2qd1dh/LfXQadJJYAOyKcEtESPwfhLQFwG6KrGk3OOdwkPzBWWYo128xDIh9bcAcABUCwO4M9XQQmrNyamMkeJIdAoC9NNDSooHPHCSsVUh92llyoEOs3LVF0Zq9gLwf3dpL1fuWC7L9Nh26CIak8ot2qqUAsIsIBmGbbC1CRiPtJTd2cys+WxesJRS0a0TZP2q0HADciAtpDw6VFIuk3leJnG9y8pNt0iwBAAZFYc0KvYNARxt6pRo69x5RAgAX2/aTXkFAeTZzi9t4SgHoHQRiHrdH+VFtPGMACIHAd8cmRm3b147VUVlqLAAMDidALn5zIn8DxNJ9BLTykKmqF201AAACLkeR4fcSoTzfMZm5BTenG4ziDLLLsn1sQrUA6HiQDRbwGxSpIAGi+S+8XMcHizUAYHw+V/1URhWYCoCOg8JMxj9XwCOwDG03HLXXCjFOzyJgE37EOs9q0rLXCgANC9yRyYVaVgGDeoIX4KCI5QyUBUQszGf9GQ3jAyJgNu1RbgmAb2Gspa9cT3/KMwAJSxNmk1x9Lg4ocWu1pL5DoLa9nYMK9Qz1Ev2BVckzqq+ZywOqJ7T0jf8BWBrx3p73Fz7CAs0clL73AAAAAElFTkSuQmCC);background-size:48px 48px; background-position: center; background-repeat:no-repeat;animation: spinner 0.75s infinite linear;}@keyframes spinner {	0% { transform: rotate(0deg); }	100% { transform: rotate(359deg); }}#ipsLoader_text { margin: 15px 0 0 0; padding: 0; font-size: 14px;text-align:center;}
</style>
</head>
<body style="margin:0">
<?php
/**
 * @brief		Update Extractor
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		31 Jul 2017
 */

// This file deliberately exists outside of the framework
// and MUST NOT call init.php

@ini_set('display_errors', 'off');

if ( file_exists( "../../constants.php" ) )
{
	require "../../constants.php";
}

if ( !defined( 'CP_DIRECTORY' ) )
{
	define( 'CP_DIRECTORY', 'admin' );
}

if ( !defined( 'ROOT_PATH' ) )
{
	define( 'ROOT_PATH', str_replace( CP_DIRECTORY . '/upgrade', '', __DIR__ ) );
}

require "../../conf_global.php";

/**
* Compare hashes in fixed length, time constant manner.
*
* @param	string	$expected	The expected hash
* @param	string	$provided	The provided input
* @return	boolean
*/
function compareHashes( $expected, $provided )
{
	if ( !\is_string( $expected ) || !\is_string( $provided ) || $expected === '*0' || $expected === '*1' || $provided === '*0' || $provided === '*1' ) // *0 and *1 are failures from crypt() - if we have ended up with an invalid hash anywhere, we will reject it to prevent a possible vulnerability from deliberately generating invalid hashes
	{
		return FALSE;
	}
	
	$len = \strlen( $expected );
	if ( $len !== \strlen( $provided ) )
	{
		return FALSE;
	}
	
	$status = 0;
	for ( $i = 0; $i < $len; $i++ )
	{
		$status |= \ord( $expected[ $i ] ) ^ \ord( $provided[ $i ] );
	}
	
	return $status === 0;
}

/**
 * Get CiCloud User
 *
 * @return	string|NULL
 */
function getCicUsername(): ?string
{
	if ( preg_match( '/^\/var\/www\/html\/(.+?)(?:\/|$)/i', ROOT_PATH, $matches ) )
	{
		return $matches[1];
	}
	
	return NULL;
}

/**
 * Function to write a log file to disk
 *
 * @param	mixed	$message	Exception or message to log
 * @return	void
 */
function writeLogFile( $message )
{
	/* What are we writing? */
	$date = date('r');
	if ( $message instanceof \Exception )
	{
		$messageToLog = $date . "\n" . \get_class( $message ) . '::' . $message->getCode() . "\n" . $message->getMessage() . "\n" . $message->getTraceAsString();
	}
	else
	{
		if ( \is_array( $message ) )
		{
			$message = var_export( $message, TRUE );
		}
		$messageToLog = $date . "\n" . $message . "\n" . ( new \Exception )->getTraceAsString();
	}
	
	/* Where are we writing it? */
	$dir = rtrim( __DIR__, '/' ) . '/../../uploads/logs';
	
	/* Write it */
	$header = "<?php exit; ?>\n\n";
	$file = $dir . '/' . date( 'Y' ) . '_' . date( 'm' ) . '_' . date('d') . '_' . ( 'extractfailure' ) . '.php';
	if ( file_exists( $file ) )
	{
		@\file_put_contents( $file, "\n\n-------------\n\n" . $messageToLog, FILE_APPEND );
	}
	else
	{
		@\file_put_contents( $file, $header . $messageToLog );
	}
	@chmod( $file, IPS_FILE_PERMISSION );
}

/* ! Controller */
try
{
	/* Check this request came from the ACP */
	if ( !getCicUsername() OR compareHashes( md5( getCicUsername() . $INFO['sql_pass'] ), $_GET['key'] ) === FALSE )
	{
		throw new Exception( "Security check failed" );
	}
	
	/* We are done if last_auto_upgrade exists and is both older than the current time but updated less than ten minutes ago. */
	$done = ( isset( $INFO['last_auto_upgrade'] ) AND $INFO['last_auto_upgrade'] < time() AND ( $INFO['last_auto_upgrade'] > time() - ( 10 * 60 ) ) );
	$adsess	= $_GET['adsess'] ?? '';
	if ( $done )
	{
		if ( \function_exists( 'opcache_reset' ) )
		{
			@opcache_reset();
		}

		$siteurl	= $INFO['base_url'] ?? $INFO['board_url'];
		$upgradeUrl = rtrim( $siteurl, '/' ) . '/' . CP_DIRECTORY . "/upgrade/?adsess={$adsess}";
		
		echo <<<HTML
<div class='ipsLoader_container'>
	<div class="ipsLoader"></div>
	<p id='ipsLoader_text' class='ipsType_text'>&nbsp;&nbsp;</p>
</div>
<script type='text/javascript'>parent.location = '{$upgradeUrl}';</script><noscript><a href='index.php' target='_parent'>Click here to continue</a></noscript>
HTML;
	}
	
	/* Nope, redirect to the next batch */
	else
	{
		if ( !isset( $_GET['counter'] ) )
		{
			$_GET['counter'] = 0;
		}

		$i = 1 + $_GET['counter'];
		$url = "extractCic.php?counter={$i}&key={$_GET['key']}&adsess={$adsess}";
		echo <<<HTML
<div class='ipsLoader_container'>
	<div class="ipsLoader"></div>
	<p id='ipsLoader_text' class='ipsType_text' data-counter='{$i}'>&nbsp;&nbsp;</p>
</div>
<noscript><a href='{$url}' target='_parent'>Click here to continue</a></noscript>
<script type='text/javascript'>window.onload = function(){setTimeout(function(){window.skipConfirm = true;window.location = '{$url}';}, 10000);};</script>
HTML;
	}
}
catch ( Throwable $e )
{
	writeLogFile( $e );
	
	echo "<script type='text/javascript'>parent.location = parent.location + '&fail=1';</script><noscript>An error occurred. Please visit the <a href='https://remoteservices.invisionpower.com/docs/client_area' target='_blank' rel='noopener'>client area</a> to manually download the latest version. After uploading the files, <a href='index.php' target='_parent'>continue to the upgrader</a>.</noscript>";
	exit;
}
?>
<script type='text/javascript'>
	window.addEventListener('beforeunload', function (event) {
		if( !window.skipConfirm ){
			event.preventDefault();
			event.returnValue = '';
		}
	});

	var phrases = [
		"Files are still being applied, please wait...",
		"Sit tight, we're still working on applying those latest files...",
		"We'll get this finished up as quickly as possible...",
		"Your upgrade is important to us caller, please stay on the line...",
		"Fetching awesome new features, fresh from the Invision Community kitchen...",
		"We promise we won't keep you much longer...",
		"A wise man once said, 'Please be patient while we apply new files'...",
		"This would be a good time to check out what's new in <a href='https://invisioncommunity.com/news' target='_blank'>our blog</a>...",
		"Know what's better than Invision Community? A newer version of Invision Community!",
		"🎵 If you're happy and you know it, please wait some more... 🎵",
		"While you're waiting, we'd like to take a moment to thank you for using Invision Community."
	];
	
	var selected = phrases[Math.floor(Math.random()*phrases.length)];
	var element = document.getElementById('ipsLoader_text');

	if( element.hasAttribute('data-counter') && parseInt( element.getAttribute('data-counter') ) > 1 ){
		element.innerHTML = selected;
	}
</script>
</body>
</html>