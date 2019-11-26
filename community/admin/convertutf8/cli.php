#!/usr/local/bin/php
<?php
/**
 * @brief		UTF-8 Conversion
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		IPS Tools
 * @since		4 Sept 2013
 */


require_once 'init.php';

if ( IS_CLI )
{
	\IPSUtf8\Dispatcher\Cli::i()->run();
}
else
{
	print "<html><head><title>Warning</title></head>\n";
    print "<body style='text-align:center'>\n";
    print "This script is meant to be run via command line<br />\n";
    print "More information:<br />\n";
    print "<a href=\"http://www.google.com/search?hl=en&q=php+cli+windows\" target=\"_blank\" rel=\"noopener\">http://www.google.com/search?hl=en&q=php+cli+windows</a><br />\n";
    print "This script will not run through a webserver.<br />\n";
    print "</body></html>\n";
    exit();

}

exit();
