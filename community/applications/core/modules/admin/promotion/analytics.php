<?php
/**
 * @brief		Analytics
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		03 Nov 2016
 */

namespace IPS\core\modules\admin\promotion;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Analytics
 */
class _analytics extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'analytics_manage' );
		parent::execute();
	}
	
	/**
	 * Analytics Settings
	 *
	 * @return	void
	 */
	protected function manage()
	{
		$providers = array(
			'none'		=> 'analytics_provider_none',
			'ga'		=> 'analytics_provider_ga',
			'piwik'		=> 'analytics_provider_piwik',
			'custom'	=> 'analytics_provider_custom'
		);

		$toggles = array(
			'ga'		=> array('analytics_ga'),
			'piwik'		=> array('analytics_piwik'),
			'custom'	=> array('analytics_custom', 'ipbseo_ga_paginatecode')
		);

		$form = new \IPS\Helpers\Form;
		$form->addMessage('analytics_description');
		$form->add( new \IPS\Helpers\Form\Radio( 'ipbseo_ga_provider', \IPS\Settings::i()->ipbseo_ga_provider, FALSE, array( 'options' => $providers, 'toggles' => $toggles ) ) );
		
		/* Add a CodeMirror element for each supported provider */
		foreach( $providers as $key => $provider )
		{
			if( $key == 'none' )
			{
				continue;
			}

			$form->add( new \IPS\Helpers\Form\Codemirror( 'analytics_' . $key, \IPS\Settings::i()->ipbseo_ga_provider == $key ? \IPS\Settings::i()->ipseo_ga : '', FALSE, array('height' => 150, 'mode' => 'javascript'), NULL, NULL, NULL, 'analytics_' . $key ) );	
		}
	
		$form->add( new \IPS\Helpers\Form\Codemirror( 'ipbseo_ga_paginatecode', \IPS\Settings::i()->ipbseo_ga_paginatecode, FALSE, array('height' => 150, 'mode' => 'javascript'), NULL, NULL, NULL, 'ipbseo_ga_paginatecode' ) );

		if ( $values = $form->values() )
		{
			/* Fix up values to ensure the correct snippet is saved */
			if ( !isset( $providers[ $values['ipbseo_ga_provider'] ] ) || $values['ipbseo_ga_provider'] == 'none' )
			{
				$values['ipbseo_ga_enabled'] = FALSE;
				$values['ipseo_ga'] = '';
				$values['ipbseo_ga_paginatecode'] = '';
			}
			else
			{
				$values['ipbseo_ga_enabled'] = TRUE;
				$values['ipseo_ga'] = $values['analytics_' . $values['ipbseo_ga_provider'] ];
			}

			foreach( $providers as $key => $provider )
			{
				unset( $values['analytics_' . $key ] );
			}

			$form->saveAsSettings( $values );
			
			\IPS\Session::i()->log( 'acplog__analytics_edited' );
			\IPS\Output::i()->inlineMessage	= \IPS\Member::loggedIn()->language()->addToStack('saved');

			/* Clear guest page caches */
			\IPS\Data\Cache::i()->clearAll();
		}
		
		\IPS\Output::i()->title	= \IPS\Member::loggedIn()->language()->addToStack('menu__core_promotion_analytics');
		\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'global' )->block( 'analytics', $form );
	}
}