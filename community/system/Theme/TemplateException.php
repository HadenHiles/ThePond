<?php
/**
 * @brief		Template Exception Class
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		12 Dec 2017
 */

namespace IPS\Theme;
 
/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Template Exception Class
 */
class _TemplateException extends \RuntimeException
{
	/**
	 * @brief	Template data
	 */
	public $template	= array( 'location' => NULL, 'group' => NULL, 'app' => NULL );

	/**
	 * @brief	Theme
	 */
	public $theme		= NULL;

	/**
	 * Constructor
	 *
	 * @param	string			$message	MySQL Error message
	 * @param	int				$code		MySQL Error Code
	 * @param	\Exception|NULL	$previous	Previous Exception
	 * @param	array 			$template	Template data (app, group, location)
	 * @param	\IPS\Theme		$theme		Theme object
	 * @return	void
	 */
	public function __construct( $message = null, $code = 0, $previous = null, $template=NULL, $theme=NULL )
	{
		/* Store these for the extraLogData() method */
		$this->template = $template;
		$this->theme = $theme;
				
		return parent::__construct( $message, $code, $previous );
	}

	/**
	 * Is this an issue with a third party theme?
	 *
	 * @return	bool
	 */
	public function isThirdPartyError()
	{
		/* Try to see if the template group has any modifications...if so, ignore the exception for reporting purposes */
		try
		{
			return (bool) \IPS\Db::i()->select( 'COUNT(*)', 'core_theme_templates', array( 'template_set_id=? AND template_group=? AND template_location=? AND template_app=?', $this->theme->id, $this->template['group'], $this->template['location'], $this->template['app'] ) )->first();
		}
		catch( \Exception $e )
		{
			return FALSE;
		}
		
		return FALSE;
	}
}