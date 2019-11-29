<?php
/**
 * @brief		Public sitemap gateway file
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		18 Feb 2013
 */

/**
 * Path to your IP.Board directory with a trailing /
 * Leave blank if you have not moved sitemap.php
 */
\define('REPORT_EXCEPTIONS', TRUE);
$_SERVER['SCRIPT_FILENAME']	= __FILE__;
$path	= '';

$_GET['app']		= 'core';
$_GET['module']		= 'sitemap';
$_GET['controller']	= 'sitemap';

require_once $path . 'init.php';

if ( \IPS\Request::i()->testsettings )
{
    exit;
}

\IPS\Dispatcher\External::i()->run();