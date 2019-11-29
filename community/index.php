<?php
/**
 * @brief		Public bootstrap
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		18 Feb 2013
 */ 
\define('REPORT_EXCEPTIONS', TRUE);
$_SERVER['SCRIPT_FILENAME']	= __FILE__;
require_once 'init.php';
\IPS\Dispatcher\Front::i()->run();