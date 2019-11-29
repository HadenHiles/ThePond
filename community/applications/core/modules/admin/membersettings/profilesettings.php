<?php
/**
 * @brief		Profile Settings
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		08 Jan 2018
 */

namespace IPS\core\modules\admin\membersettings;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Profile Settings
 */
class _profilesettings extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'profiles_manage' );
		parent::execute();
	}

	/**
	 * Manage
	 *
	 * @return	void
	 */
	public function manage()
	{
		$form = new \IPS\Helpers\Form;
		
		$form->addHeader( 'photos' );
		$form->add( new \IPS\Helpers\Form\YesNo( 'photos_url', \IPS\Settings::i()->photos_url ) );

		if( \IPS\Image::canWriteText() )
		{
			$form->add( new \IPS\Helpers\Form\Radio( 'letter_photos', \IPS\Settings::i()->letter_photos, FALSE, array( 'options' => array( 'default' => 'letterphoto_default', 'letters' => 'letterphoto_letters' ) ) ) );
		}

		$form->addHeader( 'usernames' );
		$form->add( new \IPS\Helpers\Form\Custom( 'user_name_length', array( \IPS\Settings::i()->min_user_name_length, \IPS\Settings::i()->max_user_name_length ), FALSE, array(
			'getHtml'	=> function( $field ) {
				return \IPS\Theme::i()->getTemplate('members')->usernameLengthSetting( $field->name, $field->value );
			}
		),
		function( $val )
		{
			if ( $val[0] < 1 )
			{
				throw new \DomainException('user_name_length_too_low');
			}
			if ( $val[1] > 255 )
			{
				throw new \DomainException('user_name_length_too_high');
			}
			if ( $val[0] > $val[1] )
			{
				throw new \DomainException('user_name_length_no_match');
			}
		} ) );
		$form->add( new \IPS\Helpers\Form\Text( 'username_characters', \IPS\Settings::i()->username_characters, FALSE, array( 'max' => 255 ) ) );
		$form->addHeader( 'signatures' );
		$form->add( new \IPS\Helpers\Form\YesNo( 'signatures_enabled', \IPS\Settings::i()->signatures_enabled,  FALSE, array( 'togglesOn' => array( 'signatures_mobile', 'signatures_guests' ) ) ) );
		$form->add( new \IPS\Helpers\Form\YesNo( 'signatures_mobile', \IPS\Settings::i()->signatures_mobile,  FALSE, array(), NULL, NULL, NULL, 'signatures_mobile' ) );
		$form->add( new \IPS\Helpers\Form\YesNo( 'signatures_guests', \IPS\Settings::i()->signatures_guests,  FALSE, array(), NULL, NULL, NULL, 'signatures_guests' ) );

		/* Status updates */
		$options = array(
			0	=> 'status_updates_disabled',
			1	=> 'status_updates_enabled_nomem',
			2	=> 'status_updates_enabled_mem'
		);

		$default = \IPS\Settings::i()->profile_comments ? ( \IPS\Settings::i()->status_updates_mem_enable ? 2 : 1 ) : 0;

		$form->addHeader( 'statuses_profile_comments' );
		$form->add( new \IPS\Helpers\Form\Radio( 'profile_comments', $default, FALSE, array( 'options' => $options, 'toggles' => array( 1 => array( 'profile_comment_approval' ), 2 => array( 'profile_comment_approval' ) ) ) ) );
		$form->add( new \IPS\Helpers\Form\YesNo( 'profile_comment_approval', \IPS\Settings::i()->profile_comment_approval, FALSE, array(), NULL, NULL, NULL, 'profile_comment_approval' ) );

		$form->addHeader( 'profile_settings_birthdays' );
		$form->add( new \IPS\Helpers\Form\Radio( 'profile_birthday_type', \IPS\Settings::i()->profile_birthday_type, TRUE, array(
			'options'	=> array( 'public' => 'profile_birthday_type_public', 'private' => 'profile_birthday_type_private', 'none' => 'profile_birthday_type_none' )
		), NULL, NULL, NULL, 'profile_birthday_type' ) );

		if( \IPS\Member::loggedIn()->hasAcpRestriction( 'core', 'profiles', 'member_history_prune' ) )
		{
			$form->addHeader( 'profile_member_history' );
			$form->add( new \IPS\Helpers\Form\Interval( 'prune_member_history', \IPS\Settings::i()->prune_member_history, FALSE, array( 'valueAs' => \IPS\Helpers\Form\Interval::DAYS, 'unlimited' => 0, 'unlimitedLang' => 'never' ), NULL, \IPS\Member::loggedIn()->language()->addToStack('after'), NULL, 'prune_member_history' ) );
		}

		$form->addHeader( 'profile_settings_ignore' );
		$form->add( new \IPS\Helpers\Form\YesNo( 'ignore_system_on', \IPS\Settings::i()->ignore_system_on, FALSE, array(), NULL, NULL, NULL, 'ignore_system_on' ) );

		$form->addHeader( 'profile_settings_display' );
		$form->add( new \IPS\Helpers\Form\Radio( 'group_formatting_type', \IPS\Settings::i()->group_formatting, FALSE, array( 'options' => array(
			'legacy'	=> 'group_formatting_type_legacy',
			'global'	=> 'group_formatting_type_global'
		) ) ) );
		if ( $values = $form->values() )
		{
			$values['group_formatting'] = $values['group_formatting_type'];
			unset( $values['group_formatting_type'] );
			
			$values['min_user_name_length'] = $values['user_name_length'][0];
			$values['max_user_name_length'] = $values['user_name_length'][1];
			unset( $values['user_name_length'] );

			$values['status_updates_mem_enable']	= ( $values['profile_comments'] == 2 ) ? 1 : 0;
			$values['profile_comments']				= ( $values['profile_comments'] > 0 ) ? 1 : 0;
		
			$form->saveAsSettings( $values );
			\IPS\Session::i()->log( 'acplog__profile_settings' );
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=core&module=membersettings&controller=profiles&tab=profilesettings' ), 'saved' );
		}
		
		\IPS\Output::i()->output = (string) $form;
	}
}