<?php
/**
 * @brief		Date/Time Class
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		8 Mar 2013
 */

namespace IPS;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Date/Time Class
 */
class _DateTime extends \DateTime
{
	/**
	 * Create from timestamp
	 *
	 * @param	int		$timestamp		UNIX Timestamp
	 * @param	bool	$bypassTimezone	Ignore timezone (useful for things like rfc1123() which forces to GMT anyways)
	 * @return	\IPS\DateTime
	 */
	public static function ts( $timestamp, $bypassTimezone=FALSE )
	{
		$obj = new static;
		$obj->setTimestamp( $timestamp );
		if ( !$bypassTimezone AND \IPS\Dispatcher::hasInstance() and \IPS\Member::loggedIn()->timezone )
		{
			$validTimezone = TRUE;
			if( \in_array( \IPS\Member::loggedIn()->timezone, static::getTimezoneIdentifiers() ) )
			{
				try
				{
					$obj->setTimezone( new \DateTimeZone( \IPS\Member::loggedIn()->timezone ) );
				}
				catch ( \Exception $e )
				{
					$validTimezone = FALSE;
				}
			}
			else
			{
				$validTimezone = FALSE;
			}

			if( ! $validTimezone )
			{
				\IPS\Member::loggedIn()->timezone = null;

				if ( \IPS\Member::loggedIn()->member_id )
				{
					\IPS\Member::loggedIn()->save();
				}
			}

		}
		return $obj;
	}

	/**
	 * @brief	Cached timezone identifiers
	 */
	protected static $timeZoneIdentifiers = NULL;

	/**
	 * Get the valid time zone identifiers
	 *
	 * @note	Abstracted to implement caching
	 * @return	array
	 */
	public static function getTimezoneIdentifiers()
	{
		if( static::$timeZoneIdentifiers === NULL )
		{
			static::$timeZoneIdentifiers = \DateTimeZone::listIdentifiers();
		}

		return static::$timeZoneIdentifiers;
	}
	
	/**
	 * Create New
	 *
	 * @return	\IPS|DateTime
	 */
	public static function create()
	{
		return new static;
	}
	
	/**
	 * Format a DateInterval showing only the relevant pieces.
	 *
	 * @param	\DateInterval	$diff			The interval
	 * @param	int				$restrictParts	The maximum number of "pieces" to return.  Restricts "1 year, 1 month, 1 day, 1 hour, 1 minute, 1 second" to just "1 year, 1 month".  Pass 0 to not reduce.
	 * @param	\IPS\Lang|\IPS\Member|NULL	$memberOrLanguage		The language or member to use, or NULL for currently logged in member
	 * @return	string 
	 */
	public static function formatInterval( \DateInterval $diff, $restrictParts=2, $memberOrLanguage=NULL )
	{
		$language = static::determineLanguage( $memberOrLanguage );

		/* Figure out what pieces we have.  Note that we are letting the language manager perform the formatting to implement better pluralization. */
		$format		= array();

		if( $diff->y !== 0 )
		{
			$format[] = $language->addToStack( 'f_years', FALSE, array( 'pluralize' => array( $diff->y ) ) );
		}

		if( $diff->m !== 0 )
		{
			$format[] = $language->addToStack( 'f_months', FALSE, array( 'pluralize' => array( $diff->m ) ) );
		}

		if( $diff->d !== 0 )
		{
			$format[] = $language->addToStack( 'f_days', FALSE, array( 'pluralize' => array( $diff->d ) ) );
		}

		if( $diff->h !== 0 )
		{
			$format[] = $language->addToStack( 'f_hours', FALSE, array( 'pluralize' => array( $diff->h ) ) );
		}

		if( $diff->i !== 0 )
		{
			$format[] = $language->addToStack( 'f_minutes', FALSE, array( 'pluralize' => array( $diff->i ) ) );
		}

		/* If we don't have anything but seconds, return "less than a minute ago" */
		if( !\count($format) )
		{
			if( $diff->s !== 0 )
			{
				return $language->addToStack('less_than_a_minute');
			}
		}
		else if( $diff->s !== 0 )
		{
			$format[] = $language->addToStack( 'f_seconds', FALSE, array( 'pluralize' => array( $diff->s ) ) );
		}

		/* If we are still here, reduce the number of items in the $format array as appropriate */
		if( $restrictParts > 0 )
		{
			$useOnly	= array();
			$haveUsed	= 0;

			foreach( $format as $period )
			{
				$useOnly[]	= $period;
				$haveUsed++;

				if( $haveUsed >= $restrictParts )
				{
					break;
				}
			}

			$format	= $useOnly;
		}
		
		return $language->formatList( $format );
	}
	
	/**
	 * Format the date and time according to the user's locale
	 *
	 * @return	string
	 * @note	We cast to a string explicitly because in some environments (e.g. Windows) boolean FALSE can be returned from strftime if conversion specifiers are not valid
	 */
	public function __toString()
	{
		return (string) \IPS\Member::loggedIn()->language()->convertString( strftime( '%x ' . static::localeTimeFormat(), $this->getTimestamp() + $this->getTimezone()->getOffset( $this ) ) );
	}
	
	/**
	 * Get HTML output
	 *
	 * @param	bool						$capitalize			TRUE if by itself, FALSE if in the middle of a sentence
	 * @param	bool						$short				Whether or not to use the short form
	 * @param	\IPS\Lang|\IPS\Member|NULL	$memberOrLanguage	The language or member to use, or NULL for currently logged in member
	 * @return	string
	 */
	public function html( $capitalize=TRUE, $short=FALSE, $memberOrLanguage=NULL )
	{
		$format = $short ? 1 : ( $capitalize ? static::RELATIVE_FORMAT_NORMAL : static::RELATIVE_FORMAT_LOWER );

		return "<time datetime='{$this->rfc3339()}' title='{$this}' data-short='" . trim( $this->relative( 1, $memberOrLanguage ) ) . "'>" . trim( $this->relative( $format, $memberOrLanguage ) ) . "</time>";
	}

	/**
	 * Format the date according to the user's locale (without the time)
	 *
	 * @param	\IPS\Lang|\IPS\Member|NULL	$memberOrLanguage		The language or member to use, or NULL for currently logged in member
	 * @return	string
	 */
	public function localeDate( $memberOrLanguage=NULL )
	{
		return static::determineLanguage( $memberOrLanguage )->convertString( strftime( '%x', $this->getTimestamp() + $this->getTimezone()->getOffset( $this ) ) );
	}

	/**
	 * Format the date to return month and day without a year
	 *
	 * @param	\IPS\Lang|\IPS\Member|NULL	$memberOrLanguage		The language or member to use, or NULL for currently logged in member
	 * @return	string
	 */
	public function dayAndMonth( $memberOrLanguage=NULL )
	{
		return static::determineLanguage( $memberOrLanguage )->addToStack(
			'_date_day_and_month',
			FALSE,
			array(
				'pluralize'	=> array(
					strftime( ( strftime( '%d' ) !== FALSE ) ? '%d' : '%e', $this->getTimestamp() + $this->getTimezone()->getOffset( $this ) ),
					strftime( '%m', $this->getTimestamp() + $this->getTimezone()->getOffset( $this ) ),
				)
			)
		);
	}

	/**
	 * Get locale date, forced to 4-digit year format
	 *
	 * @param	\IPS\Lang|\IPS\Member|NULL	$memberOrLanguage		The language or member to use, or NULL for currently logged in member
	 * @return	string
	 */
	public function fullYearLocaleDate( $memberOrLanguage=NULL )
	{
		$timeStamp		= $this->getTimestamp() + $this->getTimezone()->getOffset( $this );
		$dateString		= strftime( '%x', $timeStamp );
		$twoDigitYear	= strftime( '%y', $timeStamp );
		$fourDigitYear	= strftime( '%Y', $timeStamp );
		$dateString		= preg_replace_callback( "/(\s|\/|,|-){$twoDigitYear}$/", function( $matches ) use ( $fourDigitYear ) {
			return $matches[1] . $fourDigitYear;
		}, $dateString );
		return static::determineLanguage( $memberOrLanguage )->convertString( $dateString );
	}
		
	/**
	 * Locale time format
	 *
	 * PHP always wants to use 24-hour format but some
	 * countries prefer 12-hour format, so we override
	 * specifically for them
	 *
	 * @param	bool				$seconds	If TRUE, will include seconds
	 * @param	bool				$minutes	If TRUE, will include minutes
	 * @param	\IPS\Lang|\IPS\Member|NULL	$memberOrLanguage		The language or member to use, or NULL for currently logged in member
	 * @return	string
	 */
	public static function localeTimeFormat( $seconds=FALSE, $minutes=TRUE, $memberOrLanguage=NULL )
	{
		if ( \in_array( preg_replace( '/\.UTF-?8$/', '', static::determineLanguage( $memberOrLanguage )->short ), array(
			'sq_AL', // Albanian - Albania
			'zh_SG', 'sgp', 'singapore', // Chinese - Singapore
			'zh_TW', 'twn', 'taiwan', // Chinese - Taiwan
			'en_AU', 'aus', 'australia', 'australian', 'ena', 'english-aus', // English - Australia
			'en_CA', 'can', 'canda', 'canadian', 'enc', 'english-can', // English - Canada
			'en_NZ', 'nzl', 'new zealand', 'new-zealand', 'nz', 'english-nz', 'enz', // English - New Zealand
			'en_PH', // English - Phillipines
			'en_ZA', // English - South Africa
			'en_US', 'american', 'american english', 'american-english', 'english-american', 'english-us', 'english-usa', 'enu', 'us', 'usa', 'america', 'united states', 'united-states', // English - United States
			'el_CY', // Greek - Cyprus
			'el_GR', 'grc', 'greece', 'ell', 'greek', // Greek - Greece
			'ms_MY', // Malay - Malaysia
			'ko_KR', 'kor', 'korean', // Korean - South Korea
			'es_MX', 'mex', 'mexico', 'esm', 'spanish-mexican', // Spanish - Mexico
		) ) )
		{
			if( strftime( '%I' ) !== FALSE )
			{
				return '%I' . ( $minutes ? ':%M' : '' ) . ( $seconds ? ':%S ' : ' ' ) . ' %p';
			}
			else
			{
				return '%l' . ( $minutes ? ':%M' : '' ) . ( $seconds ? ':%S ' : ' ' ) . ' %p';
			}
		}
		
		return '%H' . ( $minutes ? ':%M' : '' ) . ( $seconds ? ':%S ' : ' ' );
	}
	
	/**
	 * Format the time according to the user's locale (without the date)
	 *
	 * @param	bool				$seconds	If TRUE, will include seconds
	 * @param	bool				$minutes	If TRUE, will include minutes
	 * @param	\IPS\Lang|\IPS\Member|NULL	$memberOrLanguage		The language or member to use, or NULL for currently logged in member
	 * @return	string
	 */
	public function localeTime( $seconds=TRUE, $minutes=TRUE, $memberOrLanguage=NULL )
	{
		return static::determineLanguage( $memberOrLanguage )->convertString( strftime( static::localeTimeFormat( $seconds, $minutes, $memberOrLanguage ), $this->getTimestamp() + $this->getTimezone()->getOffset( $this ) ) );
	}
	
	/**
	 * @brief	Normal relative format: Yesterday at 2pm
	 */
	const RELATIVE_FORMAT_NORMAL = 0;

	/**
	 * @brief	Lowercase relative format: yesterday at 2pm
	 */
	const RELATIVE_FORMAT_LOWER  = 2;

	/**
	 * @brief	Short relative format: 1dy (for mobile view)
	 */
	const RELATIVE_FORMAT_SHORT  = 1;

	/**
	 * Format the date relative to the current date/time
	 * e.g. "30 minutes ago"
	 *
	 * @param	int					$format		The format (see RELATIVE_FORMAT_* constants)
	 * @param	\IPS\Lang|\IPS\Member|NULL	$memberOrLanguage		The language or member to use, or NULL for currently logged in member
	 * @return	string
	 */
	public function relative( $format=0, $memberOrLanguage=NULL )
	{
		$language	= static::determineLanguage( $memberOrLanguage );
		$now		= static::ts( time() );
		$difference	= $this->diff( $now );
		$capitalKey = ( $format == static::RELATIVE_FORMAT_LOWER ) ? '' : '_c';

		/* More than a year ago...1y is good enough for short dates */
		if( $format == static::RELATIVE_FORMAT_SHORT AND !$difference->invert and $now->format( 'Y', $language ) != $this->format( 'Y', $language ) )
		{
			if( $difference->y )
			{
				return $language->addToStack( 'f_years_short', FALSE, array( 'pluralize' => array( $difference->y ) ) );
			}
			else
			{
				return $language->addToStack(
					'_date_this_year_short',
					FALSE,
					array(
						'pluralize'	=> array(
							strftime( ( strftime( '%d' ) !== FALSE ) ? '%d' : '%e', $this->getTimestamp() + $this->getTimezone()->getOffset( $this ) ),
							strftime( '%m', $this->getTimestamp() + $this->getTimezone()->getOffset( $this ) )
						)
					)
				);
			}
		}

		/* In the past and from this year */
		if ( !$difference->invert and $now->format( 'Y', $language ) == $this->format( 'Y', $language ) )
		{
			/* More than a week ago: "March 4" */
            if ( $difference->m or $difference->d >= 6 )
			{
				return $language->addToStack(
					$format == static::RELATIVE_FORMAT_SHORT ? '_date_this_year_short' : '_date_this_year_long',
					FALSE,
					array(
						'pluralize'	=> array(
							strftime( ( strftime( '%d' ) !== FALSE ) ? '%d' : '%e', $this->getTimestamp() + $this->getTimezone()->getOffset( $this ) ),
							strftime( '%m', $this->getTimestamp() + $this->getTimezone()->getOffset( $this ) )
						)
					)
				);
			}
			/* Less than a week but more than a day ago */
			elseif ( $difference->d )
			{
				$compare = clone $this;
				
				/* Short format: "1 dy" */
				if ( $format === static::RELATIVE_FORMAT_SHORT )
				{
					return $language->addToStack( 'f_days_short', FALSE, array( 'pluralize' => array( $difference->d ) ) );
				}
				/* Yesterday: "Yesterday at 23:56" */
				elseif ( $difference->d == 1 && ( $compare->add( new \DateInterval( 'P1D' ) )->format( 'Y-m-d', $language ) == $now->format( 'Y-m-d', $language ) ) )
				{
					return $language->addToStack( "_date_yesterday{$capitalKey}", FALSE, array( 'sprintf' => array( $this->localeTime( FALSE, TRUE, $language ) ) ) );
				}
				/* Other: "Wednesday at 23:56" */
				else
				{
					return $language->addToStack(
						"_date_this_week{$capitalKey}",
						FALSE,
						array(
							'sprintf' => array(
								$this->localeTime( FALSE, TRUE, $language )
							),
							'pluralize'	=> array(
								$this->strFormat( '%w', $language ),
							)
						)
					);
				}
			}
			/* Less than a day but more than an hour ago */
			elseif ( $difference->h )
			{
				/* Short format: "1 hr" */
				if ( $format == static::RELATIVE_FORMAT_SHORT )
				{
					return $language->addToStack( 'f_hours_short', FALSE, array( 'pluralize' => array( $difference->h ) ) );
				}
				/* Long format: "1 hour ago" */
				else
				{
					return $language->addToStack( "_date_ago{$capitalKey}", FALSE, array( 'sprintf' => array( $language->addToStack( 'f_hours', FALSE, array( 'pluralize' => array( $difference->h ) ) ) ) ) );
				}
			}
			/* Less than an hour but more than a minute ago */
			elseif ( $difference->i )
			{
				/* Short format: "4 min" */
				if ( $format == static::RELATIVE_FORMAT_SHORT )
				{
					return $language->addToStack( 'f_minutes_short', FALSE, array( 'pluralize' => array( $difference->i ) ) );
				}
				/* Short format: "4 minutes ago" */
				else
				{
					return $language->addToStack( "_date_ago{$capitalKey}", FALSE, array( 'sprintf' => array( $language->addToStack( 'f_minutes', FALSE, array( 'pluralize' => array( $difference->i ) ) ) ) ) );
				}
			}
			/* Less than a minute ago */
			else
			{
				if ( $format == static::RELATIVE_FORMAT_SHORT )
				{
					return $language->addToStack( 'f_minutes_short', FALSE, array( 'pluralize' => array( 1 ) ) );
				}
				else
				{
					return $language->addToStack( "_date_just_now{$capitalKey}" );
				}
			}
		}
		
		/* Anything else - "March 4, 1992" */
		return $language->addToStack(
			$format == static::RELATIVE_FORMAT_SHORT ? '_date_last_year_short' : '_date_last_year_long',
			FALSE,
			array(
				'sprintf'	=> array(
					strftime( '%Y', $this->getTimestamp() + $this->getTimezone()->getOffset( $this ) )
				),
				'pluralize'	=> array(
					strftime( ( strftime( '%d' ) !== FALSE ) ? '%d' : '%e', $this->getTimestamp() + $this->getTimezone()->getOffset( $this ) ),
					strftime( '%m', $this->getTimestamp() + $this->getTimezone()->getOffset( $this ) ),
				)
			)
		);
	}

	/**
	 * Format times based on strftime() calls instead of date() calls, and convert to UTF-8 if necessary
	 *
	 * @param	string				$format		Format accepted by strftime()
	 * @param	\IPS\Lang|\IPS\Member|NULL	$memberOrLanguage		The language or member to use, or NULL for currently logged in member
	 * @return	string
	 */
	public function strFormat( $format, $memberOrLanguage=NULL )
	{
		/* We only do this on Windows - Windows does not support the %e formatter */
		if( mb_strtoupper( mb_substr( PHP_OS, 0, 3 ) ) === 'WIN' )
		{
			$format = preg_replace( '#(?<!%)((?:%%)*)%e#', '\1%#d', $format );
		}

		return static::determineLanguage( $memberOrLanguage )->convertString( strftime( $format, $this->getTimestamp() + $this->getTimezone()->getOffset( $this ) ) );
	}

	/**
	 * Wrapper for format() so we can convert to UTF-8 if needed
	 *
	 * @param	string				$format		Format accepted by date()
	 * @param	\IPS\Lang|\IPS\Member|NULL	$memberOrLanguage		The language or member to use, or NULL for currently logged in member
	 * @return	string
	 */
	public function format( $format, $memberOrLanguage=NULL )
	{
		/* If this is just the year, which we do periodically, then we can skip UTF-8 conversion stuff since the result is always an integer -
			saves resources, especially from the relative() method which can be called 25+ times per page load and can call this method 4 times each*/
		if( $format == 'Y' )
		{
			return parent::format( $format );
		}
		
		return static::determineLanguage( $memberOrLanguage )->convertString( parent::format( $format ) );
	}
	
	/**
	 * Format the date for the datetime attribute HTML `<time>` tags
	 * This will always be in UTC (so offset is not included) and so should never be displayed normally to users
	 *
	 * @return	string
	 */
	public function rfc3339()
	{
		return date( 'Y-m-d', $this->getTimestamp() ) . 'T' . date( 'H:i:s', $this->getTimestamp() ) . 'Z';
	}

	/**
	 * Format the date for the expires header
	 * This must be in english only and follow a very specific format in GMT (so offset is not included)
	 *
	 * @return	string
	 */
	public function rfc1123()
	{
		return gmdate( "D, d M Y H:i:s", $this->getTimestamp() ) . ' GMT';
	}

	/**
	 * Determine the language object to use based on the passed in parameter, which could be NULL, \IPS\Member or \IPS\Lang
	 *
	 * @param	\IPS\Lang|\IPS\Member|NULL	$formatter	Value we are using to determine how to format the result
	 * @return	\IPS\Lang
	 */
	protected static function determineLanguage( $formatter=NULL )
	{
		/* If nothing is passed in (the norm) we want the current viewing user's language selection */
		if( $formatter === NULL )
		{
			return \IPS\Member::loggedIn()->language();
		}
		elseif( $formatter instanceof \IPS\Member )
		{
			return $formatter->language();
		}
		elseif( $formatter instanceof \IPS\Lang )
		{
			return $formatter;
		}

		throw new \UnexpectedValueException;
	}
}