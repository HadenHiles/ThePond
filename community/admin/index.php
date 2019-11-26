<?php
/**
 * @brief		Admin CP bootstrap
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		18 Feb 2013
 */

\define('READ_WRITE_SEPARATION', FALSE);
\define('REPORT_EXCEPTIONS', TRUE);
require_once '../init.php';
\IPS\Dispatcher\Admin::i()->run();