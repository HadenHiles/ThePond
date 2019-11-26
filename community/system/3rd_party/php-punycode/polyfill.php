<?php

// HTMLPurifier will automatically use http://php.net/idn_to_ascii if
// the IDN extension is installed. This is a small Polyfill to declare
// that method and use the php-punycode library so that IDNs can be
// supported in all environments
	
function idn_to_ascii( $string )
{
	$punycode = new \TrueBV\Punycode;
	try
	{
		return $punycode->encode( $string );
	}
	catch ( \Exception $e )
	{
		return $string;
	}
}

