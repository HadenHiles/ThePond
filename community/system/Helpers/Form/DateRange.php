<?php
/**
 * @brief		Date range input class for Form Builder
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		18 Feb 2013
 */

namespace IPS\Helpers\Form;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Date range input class for Form Builder
 */
class _DateRange extends FormAbstract
{
	/**
	 * @brief	Default Options
	 * @see		\IPS\Helpers\Form\Date::$defaultOptions
	 * @code
	 	$defaultOptions = array(
	 		'start'			=> array( ... ),
	 		'end'			=> array( ... ),
	 	);
	 * @endcode
	 */
	protected $defaultOptions = array(
		'start'		=> array(
			'min'				=> NULL,
			'max'				=> NULL,
			'disabled'			=> FALSE,
			'time'				=> FALSE,
			'unlimited'			=> NULL,
			'unlimitedLang'		=> 'indefinite',
			'unlimitedToggles'	=> array(),
			'unlimitedToggleOn'	=> TRUE,
		),
		'end'		=> array(
			'min'				=> NULL,
			'max'				=> NULL,
			'disabled'			=> FALSE,
			'time'				=> FALSE,
			'unlimited'			=> NULL,
			'unlimitedLang'		=> 'indefinite',
			'unlimitedToggles'	=> array(),
			'unlimitedToggleOn'	=> TRUE,
		),
	);

	/**
	 * @brief	Start Date Object
	 */
	public $start = NULL;
	
	/**
	 * @brief	End Date Object
	 */
	public $end = NULL;
	
	/**
	 * Constructor
	 *
	 * @param	string			$name					Name
	 * @param	mixed			$defaultValue			Default value
	 * @param	bool|NULL		$required				Required? (NULL for not required, but appears to be so)
	 * @param	array			$options				Type-specific options
	 * @param	callback		$customValidationCode	Custom validation code
	 * @param	string			$prefix					HTML to show before input field
	 * @param	string			$suffix					HTML to show after input field
	 * @param	string			$id						The ID to add to the row
	 * @return	void
	 */
	public function __construct( $name, $defaultValue=NULL, $required=FALSE, $options=array(), $customValidationCode=NULL, $prefix=NULL, $suffix=NULL, $id=NULL )
	{
		$this->start = new \IPS\Helpers\Form\Date( "{$name}[start]", isset( $defaultValue['start'] ) ? $defaultValue['start'] : NULL, FALSE, isset( $options['start'] ) ? $options['start'] : array() );
		$this->end = new \IPS\Helpers\Form\Date( "{$name}[end]", isset( $defaultValue['end'] ) ? $defaultValue['end'] : NULL, FALSE, isset( $options['end'] ) ? $options['end'] : array() );
		
		parent::__construct( $name, $defaultValue, $required, $options, $customValidationCode, $prefix, $suffix, $id );
	}

	/**
	 * Get the value to use in the label 'for' attribute
	 *
	 * @return	mixed
	 */
	public function getLabelForAttribute()
	{
		return NULL;
	}
	
	/**
	 * Format Value
	 *
	 * @return	array
	 */
	public function formatValue()
	{
		/* The start time may be offset a few hours depending on the users timezone, let's fix that now */
		$start = $this->start->formatValue();
		if ( $start instanceof \IPS\DateTime and $this->options['start']['time'] === FALSE )
		{
			$start->setTime( 00, 00, 00 );
		}

		/* The end date needs to be 23:59:59 rather than 00:00:00 as we need to go right up to the end of the day */
		$end = $this->end->formatValue();
		if ( $end instanceof \IPS\DateTime and $this->options['end']['time'] === FALSE )
		{
			$end->setTime( 23, 59, 59 );
		}

		/* Return */
		return array(
			'start'	=> $start,
			'end'	=> $end
		);
	}
	
	/**
	 * Get HTML
	 *
	 * @return	string
	 */
	public function html()
	{
		return \IPS\Theme::i()->getTemplate( 'forms', 'core', 'global' )->dateRange( $this->start->html(), $this->end->html() );
	}
	
	/**
	 * Validate
	 *
	 * @throws	\InvalidArgumentException
	 * @throws	\LengthException
	 * @return	TRUE
	 */
	public function validate()
	{
		$this->start->validate();
		$this->end->validate();
		
		if ( $this->required and $this->value['start'] === NULL and $this->value['end'] === NULL )
		{
			throw new \InvalidArgumentException('form_required');
		}
		
		if( $this->customValidationCode !== NULL )
		{
			$validationFunction = $this->customValidationCode;
			$validationFunction( $this->value );
		}
	}
}