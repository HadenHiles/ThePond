<?php
/**
 * @brief		My Attachments Controller
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		22 Jul 2013
 */
 
namespace IPS\core\modules\front\system;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * My Attachments Controller
 */
class _attachments extends \IPS\Dispatcher\Controller
{
	/**
	 * Manage
	 *
	 * @return	void
	 */
	public function manage()
	{
		/* Logged in and can upload only */
		if ( !\IPS\Member::loggedIn()->member_id or \IPS\Member::loggedIn()->group['g_attach_max'] == 0 )
		{
			\IPS\Output::i()->error( 'no_module_permission', '2C229/1', 403, '' );
		}
		
		/* Build Table */
		$table = new \IPS\core\Attachments\Table( 'core_attachments', \IPS\Http\Url::internal( 'app=core&module=system&controller=attachments', 'front', 'attachments' ), array( array( 'attach_member_id=?', \IPS\Member::loggedIn()->member_id ) ) );
		$table->include = array( 'attach_id', 'attach_location', 'attach_date', 'attach_file', 'attach_filesize', 'attach_is_image', 'attach_content', 'attach_hits' );
		$table->rowsTemplate = array( \IPS\Theme::i()->getTemplate('myAttachments'), 'rows' );
		$table->joins = array();
		
		/* Sort */
		$table->sortOptions = array( 'attach_date' => 'attach_date', 'attach_file' => 'attach_file', 'attach_filesize' => 'attach_filesize' );
		$table->sortBy = $table->sortBy ?: 'attach_date';
		if ( $table->sortBy === 'attach_file' )
		{
			$table->sortDirection = 'asc';
		}
		$table->filters = array( 'images' => array( 'attach_is_image=1' ), 'files' => array( 'attach_is_image=0' ) );

		/* Get the associated content */
		$self = $this;
		$table->parsers = array( 'attach_content' => function( $val, $row ) use ( $self )
		{
			return \IPS\core\extensions\core\EditorMedia\Attachment::getLocations( $row['attach_id'] );
		} );
			
		/* Display */
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('my_attachments');
		\IPS\Output::i()->breadcrumb[] = array( NULL, \IPS\Member::loggedIn()->language()->addToStack('my_attachments') );
		\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('myAttachments')->template( (string) $table, \IPS\Db::i()->select( 'SUM(attach_filesize)', 'core_attachments', array( 'attach_member_id=?', \IPS\Member::loggedIn()->member_id ) )->first(), \IPS\Db::i()->select( 'COUNT(*)', 'core_attachments', array( 'attach_member_id=?', \IPS\Member::loggedIn()->member_id ) )->first() );
	}
}