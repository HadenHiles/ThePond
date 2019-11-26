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
class _Notifications
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
		$form->add( new \IPS\Helpers\Form\YesNo( 'allow_admin_mails', $member->allow_admin_mails ) );

		$_autoTrack	= array();
		if( $member->auto_follow['content'] )
		{
			$_autoTrack[]	= 'content';
		}
		if( $member->auto_follow['comments'] )
		{
			$_autoTrack[]	= 'comments';
		}
		$form->add( new \IPS\Helpers\Form\CheckboxSet( 'auto_track', $_autoTrack, FALSE, array( 'options' => array( 'content' => 'auto_track_content', 'comments' => 'auto_track_comments' ), 'multiple' => TRUE ) ) );
		$form->add( new \IPS\Helpers\Form\Radio( 'auto_track_type', ( $member->auto_follow['method'] and \in_array( $member->auto_follow['method'], array( 'immediate', 'daily', 'weekly', 'none' ) ) ) ? $member->auto_follow['method'] : 'immediate', FALSE, array( 'options' => array(
			'immediate'	=> \IPS\Member::loggedIn()->language()->addToStack('follow_type_immediate'),
			//'offline'	=> \IPS\Member::loggedIn()->language()->addToStack('follow_type_offline'),
			'daily'		=> \IPS\Member::loggedIn()->language()->addToStack('follow_type_daily'),
			'weekly'	=> \IPS\Member::loggedIn()->language()->addToStack('follow_type_weekly'),
			'none'		=> \IPS\Member::loggedIn()->language()->addToStack('follow_type_none')
		) ), NULL, NULL, NULL, 'auto_track_type' ) );
		$form->add( new \IPS\Helpers\Form\YesNo( 'show_pm_popup', $member->members_bitoptions['show_pm_popup'] ) );
		$form->add( new \IPS\Helpers\Form\YesNo( 'email_notifications_once', $member->members_bitoptions['email_notifications_once'] ) );
		$form->add( new \IPS\Helpers\Form\YesNo( 'enable_notification_sounds', !$member->members_bitoptions['disable_notification_sounds'] ) );

		$form->addMatrix( 'notifications', \IPS\Notification::buildMatrix( $member ) );
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
		if ( $member->allow_admin_mails != $values['allow_admin_mails'] )
		{
			$member->logHistory( 'core', 'admin_mails', array( 'enabled' => (bool) $values['allow_admin_mails'] ) );
		}		
		$member->allow_admin_mails = (int) $values['allow_admin_mails'];
		
		$member->auto_track = json_encode( array(
			'content'	=> ( \is_array( $values['auto_track'] ) AND \in_array( 'content', $values['auto_track'] ) ) ? 1 : 0,
			'comments'	=> ( \is_array( $values['auto_track'] ) AND \in_array( 'comments', $values['auto_track'] ) ) ? 1 : 0,
			'method'	=> $values['auto_track_type']
		)	);
		$member->members_bitoptions['show_pm_popup'] = $values['show_pm_popup'];
		$member->members_bitoptions['email_notifications_once'] = $values['email_notifications_once'];
		$member->members_bitoptions['disable_notification_sounds'] = !$values['enable_notification_sounds'];

		\IPS\Notification::saveMatrix( $member, $values );
	}
}