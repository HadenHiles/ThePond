<?php
/**
 * @brief		Editor class for Form Builder
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		5 Apr 2013
 */

namespace IPS\Helpers\Form;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Editor class for Form Builder
 */
class _Editor extends FormAbstract
{
	/**
	 * @brief	Default Options
	 * @code
	 	$defaultOptions = array(
	 		'app'			=> 'core',		// The application that owns this type of editor (as defined by an extension)
	 		'key'			=> 'Example',	// The key for this type of editor (as defined by an extension)
	 		'autoSaveKey'	=> 'abc',		// Pass a string which identifies this editor's purpose. For example, if the editor is for replying to a topic with ID 5, you could use "topic-reply-5". Make sure you pass the same key every time, but a different key for different editors.
	 		'attachIds'		=> array(		// ID numbers to identify content for attachments if the content has been saved - the first two must be int or null, the third must be string or null. If content has not been saved yet, you must claim attachments after saving.
	 			1,
	 			2,
	 			'foo'
	 		),
	 		'minimize'		=> 'clickme',	// Language string to use for minimized view. NULL will mean editor is not minimized.
	 		'minimizeIcon'	=> 'flag-us',	// Icon to use for minimized view
	 		'allButtons'	=> FALSE,		// Only used for the customisation ACP page. Do not use.
	 		'tags'			=> array(),		// An array of extra insertable tags in key => value pair with key being what is inserted and value serving as a description
	 		'autoGrow'		=> FALSE,		// Used to specify if editor should grow in size as content is added. Defaults to TRUE.
	 		'controller'	=> NULL,		// Used to specify the editor controller. Defaults to NULL, which will use app=core&module=system&controller=editor
	 		'defaultIfNoAutoSave'=> FALSE,	// If TRUE, the default value will not override any autosaved content
	 		'minimizeWithContent'=> FALSE,	// If TRUE, the editor will be minimized even if there is default content
	 		'maxLength'		=> FALSE,	// The maximum length. Note that the content is HTML, so this isn't the maximum number of visible characters, so should only be used for database limits. The database's max allowed packet will override if smaller
	 		'editorId'		=> NULL,	// Passed to editorAttachments. Only necessary if the name may be changed
	 		'allowAttachments => TRUE,	// Should the editor show upload options?
	 		'embedLocalOnly'  => FALSE, // Allow local embeds only
	 	);
	 * @endcode
	 */
	protected $defaultOptions = array(
		'app'					=> NULL,
		'key'					=> NULL,
		'autoSaveKey'			=> NULL,
		'attachIds'				=> NULL,
		'minimize'				=> NULL,
		'minimizeIcon'			=> 'fa fa-comment-o',
		'allButtons'			=> FALSE,
		'tags'					=> array(),
		'autoGrow'				=> TRUE,
		'controller'			=> NULL,
		'defaultIfNoAutoSave'	=> FALSE,
		'minimizeWithContent'	=> FALSE,
		'maxLength'				=> 16777215, // The default for MEDIUMTEXT */
		'editorId'				=> NULL,
		'allowAttachments'		=> TRUE,
		'embedLocalOnly'		=> FALSE,
		'ipsPlugins'			=> "ipsautolink,ipsautosave,ipsctrlenter,ipscode,ipscontextmenu,ipsemoticon,ipsimage,ipslink,ipsmentions,ipspage,ipspaste,ipspreview,ipsquote,ipsspoiler,ipsautogrow,ipssource,removeformat"
	);
	
	/**
	 * @brief	The extension that owns this type of editor
	 */
	protected $extension;
	
	/**
	 * @brief	The uploader helper
	 */
	protected $uploader = NULL;

	/**
	 * @brief	Editor identifier
	 */
	protected $postKey;
		
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
		$this->postKey = md5( $options['autoSaveKey'] . ':' . session_id() );

		if ( \IPS\Settings::i()->giphy_enabled )
		{
			$this->defaultOptions['ipsPlugins'] .= ',ipsgiphy';
		}

		if ( isset( \IPS\Request::i()->usingEditor ) and \IPS\Request::i()->isAjax() and \IPS\Dispatcher::hasInstance() and \IPS\Dispatcher::i()->controllerLocation == 'front' )
		{
			\IPS\Session::i()->setUsingEditor();
			\IPS\Output::i()->json( array( true ) );
		}
		
		$this->defaultOptions['controller'] = 'app=core&module=system&controller=editor';
		
		/* Get our extension */		
		if ( !isset( $options['allButtons'] ) or !$options['allButtons'] )
		{
			$extensions = \IPS\Application::load( $options['app'] )->extensions( 'core', 'EditorLocations' );
			if ( !isset( $extensions[ $options['key'] ] ) )
			{
				throw new \OutOfBoundsException( $options['key'] );
			}
			
			$this->extension = $extensions[ $options['key'] ];
			
			if ( method_exists( $this->extension, 'embedLocalOnly' ) )
			{
				$options['embedLocalOnly'] = $this->extension->embedLocalOnly();
			}
		}
		
		/* Don't minimize if we have a value */
		$name = isset( $options['editorId'] ) ? $options['editorId'] : $name;
		if ( ( !isset( $options['minimizeWithContent'] ) or !$options['minimizeWithContent'] ) and ( $defaultValue or \IPS\Request::i()->$name or ( isset( \IPS\Request::i()->cookie['vle_editor'] ) and \IPS\Request::i()->cookie['vle_editor'] ) ) )
		{
			$options['minimize'] = NULL;
		}
		
		/* Create the upload helper - if the form has been submitted, this has to be done before parent::__construct() as we need the uploader present for getValue(), but for views, we won't load until the editor is clicked */
		$this->options = array_merge( $this->defaultOptions, $options );
		if ( $this->canAttach() )
		{
			if ( isset( \IPS\Request::i()->getUploader ) and \IPS\Request::i()->getUploader === $name or ( isset( \IPS\Request::i()->postKey ) and \IPS\Request::i()->postKey === $this->postKey and isset( \IPS\Request::i()->deleteFile ) ) )
			{
				if ( $uploader = $this->getUploader( $name ) )
				{
					\IPS\Output::i()->sendOutput( $uploader->html() );
				}
				else
				{
					\IPS\Output::i()->sendOutput( \IPS\Theme::i()->getTemplate( 'forms', 'core', 'global' )->editorAttachmentsPlaceholder( $name, $this->postKey ) );
				}
			}
			elseif( mb_strtolower( $_SERVER['REQUEST_METHOD'] ) == 'post' or !$this->options['minimize'] )
			{
				$this->uploader = $this->getUploader( $name );
			}
			else
			{
				$this->uploader = FALSE;
			}
		}
		
		/* Work out the biggest value MySQL will allow */
		if ( !isset( \IPS\Data\Store::i()->maxAllowedPacket ) )
		{
			$maxAllowedPacket = 0;
			foreach ( \IPS\Db::i()->query("SHOW VARIABLES LIKE 'max_allowed_packet'") as $row )
			{
				$maxAllowedPacket = $row['Value'];
			}
			\IPS\Data\Store::i()->maxAllowedPacket = $maxAllowedPacket;
		}
		if ( \IPS\Data\Store::i()->maxAllowedPacket )
		{
			if ( !isset( $options['maxLength'] ) or $options['maxLength'] > \IPS\Data\Store::i()->maxAllowedPacket )
			{
				$options['maxLength'] = \IPS\Data\Store::i()->maxAllowedPacket;
			}
		}
		
		/* Go */
		parent::__construct( $name, $defaultValue, $required, $options, $customValidationCode, $prefix, $suffix, $id );
		
		/* Preview? */
		if ( \IPS\Request::i()->isAjax() and isset( \IPS\Request::i()->_previewField ) and \IPS\Request::i()->_previewField === $this->name )
		{
			\IPS\Output::i()->sendOutput( $this->getValue() );
		}
				
		/* Include editor JS - but not if this page was loaded via ajax OR if we're a guest and the editor is minimized - because the JS loader will handle that on demand */
		if( ( !$this->options['minimize'] or \IPS\Member::loggedIn()->member_id ) and !\IPS\Request::i()->isAjax() )
		{
			if ( \IPS\IN_DEV )
			{
				\IPS\Output::i()->jsFiles = array_merge( \IPS\Output::i()->jsFiles, array( (string) \IPS\Http\Url::internal( 'applications/core/dev/ckeditor/ckeditor.js', 'none', NULL, array(), \IPS\Http\Url::PROTOCOL_RELATIVE ) ) );
			}
			else
			{
				\IPS\Output::i()->jsFiles = array_merge( \IPS\Output::i()->jsFiles, array( \IPS\Http\Url::internal( 'applications/core/interface/ckeditor/ckeditor/ckeditor.js', 'none', NULL, array(), \IPS\Http\Url::PROTOCOL_RELATIVE ) ) );
			}
		}

		/* And send a preload header for editor.css if we can, but not if we are using IN_DEV */
		if( !\IPS\Request::i()->isAjax() AND !\IPS\IN_DEV )
		{
			/* If we don't have the 'timestamp' parameter cached we need to fetch it */
			if( !isset( \IPS\Data\Store::i()->editorTimestamp ) OR !\IPS\Data\Store::i()->editorTimestamp )
			{
				$scriptCode	= \file_get_contents( \IPS\ROOT_PATH . '/applications/core/interface/ckeditor/ckeditor/ckeditor.js' );
				preg_match( "/\{timestamp:\"(.+?)\"/", $scriptCode, $matches );
				
				\IPS\Data\Store::i()->editorTimestamp = $matches[1];
			}
			
			$cssUrl = \IPS\Http\Url::external( rtrim( \IPS\Settings::i()->base_url, '/' ) . '/applications/core/interface/ckeditor/ckeditor/skins/' . \IPS\Theme::i()->editor_skin . '/editor.css?t=' . \IPS\Data\Store::i()->editorTimestamp );

			\IPS\Output::i()->linkTags[] = array( 'as' => 'style', 'rel' => 'preload', 'href' => (string) $cssUrl );
		}
	}
	
	/**
	 * Get HTML
	 *
	 * @param	bool	$raw	If TRUE, will return without HTML any chrome
	 * @return	string
	 */
	public function html( $raw=FALSE )
	{
		/* What buttons should we show? */
		$allowed = NULL;
		if ( !$this->options['allButtons'] )
		{
			$toolbars	= json_decode( \IPS\Settings::i()->ckeditor_toolbars, TRUE );
			$allowed	= array();
			
			foreach ( $toolbars as $device => $rows )
			{
				foreach ( $rows as $rowId => $data )
				{
					if ( \is_array( $data ) )
					{
						$allowed[ $device ][ $rowId ]['name'] = $data['name'];
						foreach ( $data['items'] as $k => $v )
						{
							if ( \IPS\Text\Parser::canUse( \IPS\Member::loggedIn(), $v, "{$this->options['app']}_{$this->options['key']}" ) )
							{
								$allowed[ $device ][ $rowId ]['items'][] = $v;
							}
						}
					}
					else
					{
						$allowed[ $device ][ $rowId ] = $data;
					}
				}
			}

			/* Can we use HTML? */
			if ( $this->canUseHtml() === TRUE )
			{
				if ( !empty( $allowed['desktop'][0]['items'] ) )
				{
					array_unshift( $allowed[ 'desktop' ][ 0 ][ 'items' ], 'Source' );
				}
				if ( !empty( $allowed['tablet'][0]['items'] ) )
				{
					array_unshift( $allowed[ 'tablet' ][ 0 ][ 'items' ], 'Source' );
				}
				if ( !empty( $allowed['phone'][0]['items'] ) )
				{
					array_unshift( $allowed[ 'phone' ][ 0 ][ 'items' ], 'Source' );
				}
			}
		}
		
		/* Clean resources in ACP */
		$value = $this->value;
		\IPS\Output::i()->parseFileObjectUrls( $value );
		if( \IPS\Dispatcher::hasInstance() AND \IPS\Dispatcher::i()->controllerLocation == 'admin' )
		{
			$value	= \IPS\Text\Parser::mungeResources( $value );
		}
		
		/* Fix Emoji */		
		$value = \IPS\Output::i()->replaceEmojiWithImages( $value );
		
		/* CKEditor will replace <p></p> (which doesn't display anything in a normal post) with <p><br></p> (which does)
			which creates a discrepency between wheat displays in a post and what displays in the editor */
		$value = preg_replace( '/<p>\s*<\/p>/', '', $value );

		/* Show full uploader */
		if ( $this->uploader )
		{
			$attachmentArea = $this->uploader->html();
		}
		/* Or show a loading icon where the uploader will go if the editor is minimized */
		elseif ( $this->uploader === FALSE )
		{
			$attachmentArea = \IPS\Theme::i()->getTemplate( 'forms', 'core', 'global' )->editorAttachmentsMinimized( $this->name );
			
			/* We still need to include plupload otherwise it won't work when they click in */
			if ( \IPS\IN_DEV )
			{
				\IPS\Output::i()->jsFiles = array_merge( \IPS\Output::i()->jsFiles, \IPS\Output::i()->js( 'plupload/moxie.js', 'core', 'interface' ) );
				\IPS\Output::i()->jsFiles = array_merge( \IPS\Output::i()->jsFiles, \IPS\Output::i()->js( 'plupload/plupload.dev.js', 'core', 'interface' ) );
			}
			else
			{
				\IPS\Output::i()->jsFiles = array_merge( \IPS\Output::i()->jsFiles, \IPS\Output::i()->js( 'plupload/plupload.full.min.js', 'core', 'interface' ) );
			}
		}
		/* Or if the user can't attach, just show a bar */
		else
		{
			$attachmentArea = \IPS\Theme::i()->getTemplate( 'forms', 'core', 'global' )->editorAttachmentsPlaceholder( $this->name, $this->postKey, $this->noUploaderError, $this->canUseMediaExtension() );
		}

		/* Display */
		$template = $raw ? 'editorRaw' : 'editor';
		return \IPS\Theme::i()->getTemplate( 'forms', 'core', 'global' )->$template( $this->name, $value, $this->options, $allowed, md5( $this->options['autoSaveKey'] . ':' . session_id() ), $attachmentArea, json_encode( array() ), $this->options['tags'] );
	}
	
	/**
	 * Convert the value to something that will look okay for the no-JS fallback
	 *
	 * @param	string	$value	Value
	 * @return	string
	 */
	public static function valueForNoJsFallback( $value )
	{		
		$value = preg_replace( "/\<br(\s*)?\/?\>(\s*)?/i", "\n", html_entity_decode( $value ) );
		
		$value = trim( $value );
		
		$value = preg_replace( '/<\/p>\s*<p>/', "\n\n", $value );
		
		if ( mb_substr( $value, 0, 3 ) === '<p>' )
		{
			$value = mb_substr( $value, 3 );
		}
		if ( mb_substr( $value, -4 ) === '</p>' )
		{
			$value = mb_substr( $value, 0, -4 );
		}
		
		return $value;
	}
		
	/**
	 * Get Value
	 *
	 * @return	string
	 */
	public function getValue()
	{
		$error = NULL;
		$value = parent::getValue();
		
		/* If it was made without JS, convert linebreaks to <br>s */
		$noJsKey = $this->name . '_noscript';
		if ( isset( \IPS\Request::i()->$noJsKey ) )
		{
			$value = nl2br( \IPS\Request::i()->$noJsKey, FALSE );
		}
		
		/* Or remove any invisible spaces used by the editor JS */
		else
		{
			$value = preg_replace( '/[\x{200B}\x{2063}]/u', '', $value );
		}
					
		/* Parse value */
		if ( $value )
		{
			$parser = $this->_getParser();
			$value = $parser->parse( $value );
		}

		/* Add any attachments that weren't inserted in the content */
		if ( $this->uploader )
		{
			$inserts = array();
			$fileAttachments = array();

			foreach ( $this->getAttachments() as $attachment )
			{
				if ( !isset( $parser ) or !\in_array( $attachment['attach_id'], $parser->mappedAttachments ) or array_key_exists( $attachment['attach_id'], $parser->existingAttachments ) )
				{
					$ext = mb_substr( $attachment['attach_file'], mb_strrpos( $attachment['attach_file'], '.' ) + 1 );
					if ( \in_array( mb_strtolower( $ext ), \IPS\File::$videoExtensions ) )
					{
						$value .= \IPS\Theme::i()->getTemplate( 'editor', 'core', 'global' )->attachedVideo( $attachment['attach_location'], \IPS\Http\Url::baseUrl( \IPS\Http\Url::PROTOCOL_RELATIVE ) . "applications/core/interface/file/attachment.php?id=" . $attachment['attach_id'], $attachment['attach_file'], \IPS\File::getMimeType( $attachment['attach_file'] ), $attachment['attach_id'] );
					}
					elseif ( $attachment['attach_is_image'] )
					{
						if ( $attachment['attach_thumb_location'] )
						{
							$ratio = round( ( $attachment['attach_thumb_height'] / $attachment['attach_thumb_width'] ) * 100, 2 );
							$width = $attachment['attach_thumb_width'];
						}
						else
						{
							$ratio = round( ( $attachment['attach_img_height'] / $attachment['attach_img_width'] ) * 100, 2 );
							$width = $attachment['attach_img_width'];
						}
						
						$value .= \IPS\Theme::i()->getTemplate( 'editor', 'core', 'global' )->attachedImage( $attachment['attach_location'], $attachment['attach_thumb_location'] ? $attachment['attach_thumb_location'] : $attachment['attach_location'], $attachment['attach_file'], $attachment['attach_id'], $width, $ratio );
					}
					else
					{
						$fileAttachments[] = \IPS\Theme::i()->getTemplate( 'editor', 'core', 'global' )->attachedFile( \IPS\Http\Url::baseUrl() . "applications/core/interface/file/attachment.php?id=" . $attachment['attach_id'], $attachment['attach_file'], FALSE, $attachment['attach_ext'], $attachment['attach_id'] );
					}
					
					if ( !isset( $parser ) or !\in_array( $attachment['attach_id'], $parser->mappedAttachments ) )
					{
						$inserts[] = array(
							'attachment_id'	=> $attachment['attach_id'],
							'location_key'	=> "{$this->options['app']}_{$this->options['key']}",
							'id1'			=> ( \is_array( $this->options['attachIds'] ) and isset( $this->options['attachIds'][0] ) ) ? $this->options['attachIds'][0] : NULL,
							'id2'			=> ( \is_array( $this->options['attachIds'] ) and isset( $this->options['attachIds'][1] ) ) ? $this->options['attachIds'][1] : NULL,
							'id3'			=> ( \is_array( $this->options['attachIds'] ) and isset( $this->options['attachIds'][2] ) ) ? $this->options['attachIds'][2] : NULL,
							'temp'			=> \is_string( $this->options['attachIds'] ) ? $this->options['attachIds'] : ( $this->options['attachIds'] === NULL ? md5( $this->options['autoSaveKey'] ) : $this->options['attachIds'] )
						);
					}
				}
			}

			/* Add any file attachments on a single line */
			if( \count( $fileAttachments ) )
			{
				$value .= str_replace( \IPS\Http\Url::baseUrl(), '<___base_url___>/', "<p>" . implode( ' ', $fileAttachments ) . "</p>" );
			}

			if( \count( $inserts ) and !isset( \IPS\Request::i()->_previewField ) )
			{
				\IPS\Db::i()->insert( 'core_attachments_map', $inserts, TRUE );
			}
			
			/* Clear out the post key for attachments that are claimed automatically */
			if ( $this->options['attachIds'] )
			{
				\IPS\Db::i()->update( 'core_attachments', array( 'attach_post_key' => '' ), array( 'attach_post_key=?', $this->postKey ) );
			}
		}

		/* Remove abandoned attachments */
		foreach ( \IPS\Db::i()->select( '*', 'core_attachments', array( array( 'attach_id NOT IN(?)', \IPS\Db::i()->select( 'DISTINCT attachment_id', 'core_attachments_map', NULL, NULL, NULL, NULL, NULL, \IPS\Db::SELECT_FROM_WRITE_SERVER ) ), array( 'attach_member_id=? AND attach_date<?', \IPS\Member::loggedIn()->member_id, time() - 86400 ) ) ) as $attachment )
		{
			try
			{
				\IPS\Db::i()->delete( 'core_attachments', array( 'attach_id=?', $attachment['attach_id'] ) );
				\IPS\File::get( 'core_Attachment', $attachment['attach_location'] )->delete();
				if ( $attachment['attach_thumb_location'] )
				{
					\IPS\File::get( 'core_Attachment', $attachment['attach_thumb_location'] )->delete();
				}
			}
			catch ( \Exception $e ) { }
		}
							
		/* Throw any errors */
		if ( $error )
		{
			$this->value = $value;
			throw $error;
		}

		/* Return */
		return $value;
	}
	
	/**
	 * Set the value of the element
	 *
	 * @param	bool	$initial	Whether this is the initial call or not. Do not reset default values on subsequent calls.
	 * @param	bool	$force		Set the value even if one was not submitted (done on the final validation when getting values)?
	 * @return	void
	 */
	public function setValue( $initial=FALSE, $force=FALSE )
	{
		// @todo extend this to all form elements. See commit notes.
		
		if ( !isset( \IPS\Request::i()->csrfKey ) or !\IPS\Login::compareHashes( (string) \IPS\Session::i()->csrfKey, (string) \IPS\Request::i()->csrfKey ) )
		{
			$name = $this->name;
			unset( \IPS\Request::i()->$name );
		}
		
		return parent::setValue( $initial, $force );
	}

	/**
	 * Get parser object
	 *
	 * @return	\IPS\Text\Parser
	 */
	protected function _getParser()
	{
		return new \IPS\Text\Parser( TRUE, ( $this->options['attachIds'] === NULL ? md5( $this->options['autoSaveKey'] ) : $this->options['attachIds'] ), NULL, "{$this->options['app']}_{$this->options['key']}", !$this->bypassFilterProfanity(), !$this->canUseHtml(), method_exists( $this->extension, 'htmlPurifierConfig' ) ? array( $this->extension, 'htmlPurifierConfig' ) : NULL );
	}
		
	/**
	 * Can use HTML?
	 *
	 * @return	bool
	 */
	protected function canUseHtml()
	{
		$canUseHtml = (bool) \IPS\Member::loggedIn()->group['g_dohtml'];
		if ( $this->extension )
		{
			$extensionCanUseHtml = $this->extension->canUseHtml( \IPS\Member::loggedIn(), $this );
			if ( $extensionCanUseHtml !== NULL )
			{
				$canUseHtml = $extensionCanUseHtml;
			}
		}
		return $canUseHtml;
	}
	
	/**
	 * Can Attach?
	 *
	 * @return	bool
	 */
	protected function canAttach()
	{
		$canAttach = ( \IPS\Member::loggedIn()->group['g_attach_max'] == '0' ) ? FALSE : TRUE;

		if ( $this->extension )
		{
			$extensionCanAttach = $this->extension->canAttach( \IPS\Member::loggedIn(), $this );
			if ( $extensionCanAttach !== NULL )
			{
				$canAttach = $extensionCanAttach;
			}
		}
		return $canAttach;
	}

	/**
	 * Can the member attach any of his existing media uploads?
	 *
	 * @return bool
	 */
	protected function canUseMediaExtension()
	{
		/* If we have an extension and it implicit disallows any attaachments, don't allow media extensions too , i.e. contact us form needs this */
		if ( $this->extension )
		{
			$extensionCanAttach = $this->extension->canAttach( \IPS\Member::loggedIn(), $this );
			if ( $extensionCanAttach === FALSE )
			{
				return FALSE;
			}
		}

		foreach ( \IPS\Application::allExtensions( 'core', 'EditorMedia' ) as $k => $class )
		{
			if ( $class->count( \IPS\Member::loggedIn(), '' ) )
			{
				return TRUE;
			}
		}
		return FALSE;
	}
	
	/**
	 * @brief	Error message to display if we're not showing the uploader
	 */
	protected $noUploaderError = NULL;
	
	/**
	 * Get uploader
	 *
	 * @param	string	$name	Form name
	 * @return	\IPS\Helpers\Form\Upload|NULL
	 */
	protected function getUploader( $name )
	{
		/* Attachments enabled? */
		if ( \IPS\Settings::i()->attach_allowed_types == 'none' )
		{
			return NULL;
		}
		
		/* Load existing attachments */
		$existing = array();
		$currentPostUsage = 0;
		foreach ( $this->getAttachments() as $attachment )
		{
			try
			{
				$file = \IPS\File::get( 'core_Attachment', $attachment['attach_location'] );
				$file->attachmentThumbnailUrl = $attachment['attach_thumb_location'] ? \IPS\File::get( 'core_Attachment', $attachment['attach_thumb_location'] )->url : $file->url;
				
				/* Reset the original filename based on the attachment record as filenames can be renamed if they contain special characters as AWS being the lowest common denominator cannot
				   handle special characters */
				$file->originalFilename = $attachment['attach_file'];
				
				$existing[ $attachment['attach_id'] ] = $file;
				
				$currentPostUsage += $attachment['attach_filesize'];
			}
			catch ( \Exception $e ) { }
		}
	
		/* Can we upload more? */
		$maxTotalSize = array();
		if ( \IPS\Member::loggedIn()->group['g_attach_max'] > 0 and \IPS\Member::loggedIn()->member_id )
		{
			/* Only query logged in member's usage once per page load, even if there are more than one editor */
			static $loggedInMembersAttachmentUsage = NULL;

			if( $loggedInMembersAttachmentUsage === NULL )
			{
				$loggedInMembersAttachmentUsage = \IPS\Db::i()->select( 'SUM(attach_filesize)', 'core_attachments', array( 'attach_member_id=?', \IPS\Member::loggedIn()->member_id ) )->first();
			}

			$globalSpaceRemaining = ( ( \IPS\Member::loggedIn()->group['g_attach_max'] * 1024 ) - $loggedInMembersAttachmentUsage ) / 1048576;
			$maxTotalSize[] = $globalSpaceRemaining;

			if ( $globalSpaceRemaining <= 0 )
			{
				$this->noUploaderError = 'editor_used_global_space';
			}
		}
		if ( \IPS\Member::loggedIn()->group['g_attach_per_post'] )
		{
			$perPostSpaceRemaining = ( ( \IPS\Member::loggedIn()->group['g_attach_per_post'] * 1024 ) - $currentPostUsage ) / 1048576;
			$maxTotalSize[] = $perPostSpaceRemaining;
			if ( $perPostSpaceRemaining <= 0 )
			{
				$this->noUploaderError = 'editor_used_post_space';
			}
		}
		$maxTotalSize = \count( $maxTotalSize ) ? min( $maxTotalSize ) : NULL;
		
		/* Create the uploader */
		if ( $maxTotalSize === NULL or $maxTotalSize > 0 )
		{
			$maxTotalSize = ( !\is_null( $maxTotalSize ) ) ? round( $maxTotalSize, 2 ) : NULL;
			$postKey = $this->postKey;
			
			$options = array(
				'template'			=> 'core.attachments.fileItem',
				'multiple'			=> TRUE,
				'postKey'			=> $this->postKey,
				'storageExtension'	=> 'core_Attachment',
				'retainDeleted'		=> TRUE,
				'totalMaxSize'		=> $maxTotalSize,
				'callback' => function( $file ) use ( $postKey )
				{
					\IPS\Db::i()->delete( 'core_files_temp', array( 'contents=?', (string) $file ) );
					$attachment = $file->makeAttachment( $postKey, \IPS\Member::loggedIn() );
					return $attachment['attach_id'];
				}
			);
			
			if ( \IPS\Settings::i()->attach_allowed_types == 'all' and \IPS\Settings::i()->attach_allowed_extensions )
			{
				$options['allowedFileTypes'] = explode( ',', \IPS\Settings::i()->attach_allowed_extensions );
			}
			elseif ( \IPS\Settings::i()->attach_allowed_types == 'media' )
			{
				$options['allowedFileTypes'] = array_merge( \IPS\Image::$imageExtensions, \IPS\File::$videoExtensions );
			}
			
			if ( \IPS\Settings::i()->attachment_resample_size )
			{
				$maxImageSizes = explode( 'x', \IPS\Settings::i()->attachment_resample_size );
				if ( $maxImageSizes[0] and $maxImageSizes[1] )
				{
					$options['image'] = array( 'maxWidth' => $maxImageSizes[0], 'maxHeight' => $maxImageSizes[1] );
					if ( \IPS\Settings::i()->attach_allowed_types != 'images' )
					{
						$options['image']['optional'] = TRUE;
					}
				}
				elseif ( \IPS\Settings::i()->attach_allowed_types == 'images' )
				{
					$options['image'] = TRUE;
				}
			}
			elseif ( \IPS\Settings::i()->attach_allowed_types == 'images' )
			{
				$options['image'] = TRUE;
			}
			
			$uploaderName = str_replace( array( '[', ']' ), '_', $name ) . '_upload';
			unset( \IPS\Request::i()->$uploaderName ); // We are setting the value here, so we don't want the normal form helper to overload, which will wipe out any attachments if there is an error elsewhere in the form
			$uploader = new \IPS\Helpers\Form\Upload( $uploaderName, $existing, FALSE, $options );
			$uploader->template = array( \IPS\Theme::i()->getTemplate( 'forms', 'core', 'global' ), 'editorAttachments' );
			
			/* Handle delete calls */
			if ( isset( \IPS\Request::i()->postKey ) and \IPS\Request::i()->postKey == $this->postKey and isset( \IPS\Request::i()->deleteFile ) )
			{
				/* CSRF check */
				\IPS\Session::i()->csrfCheck();
				
				/* Get the attachment */
				try
				{				
					$attachment = \IPS\Db::i()->select( '*', 'core_attachments', array( 'attach_id=?', \IPS\Request::i()->deleteFile ) )->first();
				}
				catch ( \UnderflowException $e )
				{
					\IPS\Output::i()->json( 'NO_ATTACHMENT' );
				}
				
				/* Delete the maps - Only do this for attachments that have actually been saved (if they haven't been saved, there's nothing in core_attachments_map for us to delete */
				if ( isset( $this->options['attachIds'] ) and \is_array( $this->options['attachIds'] ) and \count( $this->options['attachIds'] ) )
				{
					$where = array( array( 'location_key=?', "{$this->options['app']}_{$this->options['key']}" ), array( 'attachment_id=?', $attachment['attach_id'] ) );
					$i = 1;

					foreach ( $this->options['attachIds'] as $id )
					{
						$where[] = array( "id{$i}=?", $id );
						$i++;
					}

					\IPS\Db::i()->delete( 'core_attachments_map', $where );
				}
				else
				{
					/* If the attachment hasn't been claimed yet, it should only be deletable by the person who uploaded it */
					if( $attachment['attach_member_id'] != \IPS\Member::loggedIn()->member_id )
					{
						\IPS\Output::i()->json( 'NO_ATTACHMENT' );
					}
				}
				
				/* If there's no other maps, we can delete the attachment itself */
				$otherMaps = \IPS\Db::i()->select( 'COUNT(*)', 'core_attachments_map', array( 'attachment_id=?', $attachment['attach_id'] ) )->first();
				if ( !$otherMaps )
				{
					\IPS\Db::i()->delete( 'core_attachments', array( 'attach_id=?', $attachment['attach_id'] ) );
					try
					{
						\IPS\File::get( 'core_Attachment', $attachment['attach_location'] )->delete();
					}
					catch ( \Exception $e ) { }
					if ( $attachment['attach_thumb_location'] )
					{
						try
						{
							\IPS\File::get( 'core_Attachment', $attachment['attach_thumb_location'] )->delete();
						}
						catch ( \Exception $e ) { }
					}
				}
				
				/* Output */
				\IPS\Output::i()->json( 'OK' );
			}
		}
		else
		{
			$uploader = NULL;
		}
		
		/* Return */
		return $uploader;
	}
	
	/**
	 * Attachments
	 */
	protected $attachments;

	/**
	 * Fetch existing attachments
	 *
	 * @return	array
	 */
	protected function getAttachments()
	{
		if ( $this->attachments === NULL )
		{
			$existingAttachments = '';
			$where = array();
			if ( isset( $this->options['attachIds'] ) and \is_array( $this->options['attachIds'] ) and \count( $this->options['attachIds'] ) )
			{
				$where = array( array( 'location_key=?', "{$this->options['app']}_{$this->options['key']}" ) );
				$i = 1;
				foreach ( $this->options['attachIds'] as $id )
				{
					$where[] = array( "id{$i}=?", $id );
					$i++;
				}
				$_existingAttachments = iterator_to_array( \IPS\Db::i()->select( 'attachment_id', 'core_attachments_map', $where ) );
				if ( !empty( $_existingAttachments ) )
				{
					$existingAttachments = \IPS\Db::i()->in( 'attach_id', $_existingAttachments ) . ' OR ';
				}
			}
			
			$this->attachments = \IPS\Db::i()->select( '*', 'core_attachments', array( $existingAttachments . 'attach_post_key=?', $this->postKey ) );
		}
		
		return $this->attachments;
	}
	
	/**
	 * Get CKEditor Version
	 *
	 * @return	string
	 */
	public static function ckeditorVersion()
	{
		if ( file_exists( \IPS\ROOT_PATH . '/Credits.txt' ) )
		{
			preg_match( '#\s+Included version: (.+?)\s+?Website: http://ckeditor.com#', file_get_contents( \IPS\ROOT_PATH . '/Credits.txt' ), $matches );
			return $matches[1];
		}
		else
		{
			return "[Could not determine the CKEditor version. Please restore Credits.txt to your suite's root directory.]";
		}
	}

	/**
	 * Bypass the Profanity Check
	 *
	 * @return bool
	 */
	protected function bypassFilterProfanity()
	{
		return  (bool) \IPS\Member::loggedIn()->group['g_bypass_badwords'];
	}
	
	/**
	 * Validate
	 *
	 * @throws	\InvalidArgumentException
	 * @return	TRUE
	 */
	public function validate()
	{
		parent::validate();
		
		if ( $this->options['maxLength'] and \strlen( $this->value ) > $this->options['maxLength'] )
		{
			throw new \DomainException('form_maxlength_unspecific');
		}
		
		return TRUE;
	}
}
