<?php
/**
 * @brief		Admin CP Member Form
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		15 Apr 2013
 */

namespace IPS\core\extensions\core\MemberForm;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Admin CP Member Form
 */
class _Preferences
{
	/**
	 * Process Form
	 *
	 * @param	\IPS\Helpers\Form		$form	The form
	 * @param	\IPS\Member				$member	Existing Member
	 * @return	void
	 */
	public function process( &$form, $member )
	{
		/* Timezone */
		$form->addHeader('member_preferences_system');
		$form->add( new \IPS\Helpers\Form\YesNo( 'member_timezone_override',!$member->members_bitoptions['timezone_override'], FALSE, array( 'togglesOff' => array( 'timezone' ) ) ) );
		$timezones = array();
		foreach ( \IPS\DateTime::getTimezoneIdentifiers() as $tz )
		{
			if ( $pos = mb_strpos( $tz, '/' ) )
			{
				$timezones[ 'timezone__' . mb_substr( $tz, 0, $pos ) ][ $tz ] = 'timezone__' . $tz;
			}
			else
			{
				$timezones[ $tz ] = 'timezone__' . $tz;
			}
		}
		$form->add( new \IPS\Helpers\Form\Select( 'timezone', $member->timezone, FALSE, array( 'options' => $timezones, 'sort' => TRUE ), NULL, NULL, NULL, 'timezone' ) );
		
		/* Language */
		$languages = array( 0 => 'language_none' );
		foreach ( \IPS\Lang::languages() as $lang )
		{
			$languages[ $lang->id ] = $lang->title;
		}
		$form->add( new \IPS\Helpers\Form\Select( 'language', $member->language, TRUE, array( 'options' => $languages ) ) );

		if( $member->isAdmin() )
		{
			$form->add( new \IPS\Helpers\Form\Select( 'acp_language', $member->acp_language, TRUE, array( 'options' => $languages ) ) );
		}
		
		/* Skin */
		$themes = array( 0 => 'skin_none' );
		foreach( \IPS\Theme::themes() as $theme )
		{
			$themes[ $theme->id ] = $theme->_title;
		}
		
		$form->add( new \IPS\Helpers\Form\Select( 'skin', $member->skin, TRUE, array( 'options' => $themes ) ) );

		if( $member->isAdmin() )
		{
			$form->add( new \IPS\Helpers\Form\Select( 'acp_skin', $member->acp_skin, TRUE, array( 'options' => $themes ) ) );
		}
		
		/* Content */
		$form->addHeader('member_preferences_content');
		$form->add( new \IPS\Helpers\Form\YesNo( 'view_sigs', $member->members_bitoptions['view_sigs'], FALSE ) );
		
		/* Profile */
		$form->addHeader('member_preferences_profile');
		$form->add( new \IPS\Helpers\Form\YesNo( 'pp_setting_count_visitors', $member->members_bitoptions['pp_setting_count_visitors'], FALSE ) );
		$form->add( new \IPS\Helpers\Form\YesNo( 'pp_setting_moderate_followers', !$member->members_bitoptions['pp_setting_moderate_followers'] ) );
	}
	
	/**
	 * Save
	 *
	 * @param	array				$values	Values from form
	 * @param	\IPS\Member			$member	The member
	 * @return	void
	 */
	public function save( $values, &$member )
	{
		/* Timezone */
		$member->members_bitoptions['timezone_override'] = !$values['member_timezone_override'];
		if ( !$values['member_timezone_override'] )
		{
			$member->timezone = $values['timezone'];
		}
		
		/* Language and Theme */
		$member->language = $values['language'];		
		$member->skin = $values['skin'];
		if( $member->isAdmin() )
		{
			$member->acp_language = $values['acp_language'];
			$member->acp_skin = $values['acp_skin'];
		}
			
		/* Other */
		$member->members_bitoptions['view_sigs'] = $values['view_sigs'];
		$member->members_bitoptions['pp_setting_count_visitors']		= $values['pp_setting_count_visitors'];
		$member->members_bitoptions['pp_setting_moderate_followers']	= $values['pp_setting_moderate_followers'] ? FALSE : TRUE;		
	}
}