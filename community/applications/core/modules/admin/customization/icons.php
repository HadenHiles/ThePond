<?php
/**
 * @brief		icons
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		25 Jul 2018
 */

namespace IPS\core\modules\admin\customization;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * icons
 */
class _icons extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'theme_sets_manage' );
		parent::execute();
	}

	/**
	 * Show the form
	 *
	 * @return	void
	 */
	protected function manage()
	{
		$form = new \IPS\Helpers\Form;

		/* Generic favicon - easy enough */
		$form->add( new \IPS\Helpers\Form\Upload( 'icons_favicon', \IPS\Settings::i()->icons_favicon ? \IPS\File::get( 'core_Icons', \IPS\Settings::i()->icons_favicon ) : NULL, FALSE, array( 'obscure' => false, 'allowedFileTypes' => array( 'ico', 'png', 'gif', 'jpeg', 'jpg', 'jpe' ), 'storageExtension' => 'core_Icons' ) ) );

		/* Sharer logos, allows multiple images to be uploaded */
		$shareLogos = \IPS\Settings::i()->icons_sharer_logo ? json_decode( \IPS\Settings::i()->icons_sharer_logo, true ) : array();
		$form->add( new \IPS\Helpers\Form\Upload( 'icons_sharer_logo', \count( $shareLogos ) ? array_map( function( $val ) { return \IPS\File::get( 'core_Icons', $val ); }, $shareLogos ) : array(), FALSE, array( 'image' => true, 'storageExtension' => 'core_Icons', 'multiple' => true ) ) );

		/* Homescreen icons - we accept one upload and create the images we need */
		$homeScreen = json_decode( \IPS\Settings::i()->icons_homescreen, TRUE ) ?? array();
		$form->add( new \IPS\Helpers\Form\Upload( 'icons_homescreen', ( isset( $homeScreen['original'] ) ) ? \IPS\File::get( 'core_Icons', $homeScreen['original'] ) : NULL, FALSE, array( 'image' => true, 'storageExtension' => 'core_Icons' ) ) );

		/* Safari pinned tabs icon and highlight color */
		$form->add( new \IPS\Helpers\Form\Upload( 'icons_mask_icon', \IPS\Settings::i()->icons_mask_icon ? \IPS\File::get( 'core_Icons', \IPS\Settings::i()->icons_mask_icon ) : NULL, FALSE, array( 'allowedFileTypes' => array( 'svg' ), 'storageExtension' => 'core_Icons' ) ) );
		$form->add( new \IPS\Helpers\Form\Color( 'icons_mask_color', \IPS\Settings::i()->icons_mask_color, FALSE ) );

		/* And finally, additional manifest and livetile details */
		$manifestDetails = json_decode( \IPS\Settings::i()->manifest_details, TRUE );

		$form->add( new \IPS\Helpers\Form\YesNo( 'configure_manifest', \count( $manifestDetails ) > 0, FALSE, array(
			'togglesOn'	=> array( 'manifest_shortname', 'manifest_fullname', 'manifest_description', 'manifest_defaultapp', 'manifest_themecolor', 'manifest_bgcolor', 'manifest_display', 'manifest_custom_url_toggle' ),
		) ) );

		$form->add( new \IPS\Helpers\Form\Text( 'manifest_shortname', ( isset( $manifestDetails['short_name'] ) ) ? $manifestDetails['short_name'] : '', FALSE, array(), NULL, NULL, NULL, 'manifest_shortname' ) );
		$form->add( new \IPS\Helpers\Form\Text( 'manifest_fullname', ( isset( $manifestDetails['name'] ) ) ? $manifestDetails['name'] : '', FALSE, array(), NULL, NULL, NULL, 'manifest_fullname' ) );
		$form->add( new \IPS\Helpers\Form\TextArea( 'manifest_description', ( isset( $manifestDetails['description'] ) ) ? $manifestDetails['description'] : '', FALSE, array(), NULL, NULL, NULL, 'manifest_description' ) );

		$formStartUrl = '';
		if( isset( $manifestDetails['start_url'] ) )
		{
			$formStartUrl = str_replace( 'index.php?/', '', \IPS\Http\Url\Friendly::fixComponentPath( $manifestDetails['start_url'] ) );
		}

		$form->add( new \IPS\Helpers\Form\YesNo( 'manifest_custom_url_toggle', ( isset( $manifestDetails['start_url'] ) and empty( $formStartUrl ) ? FALSE : TRUE ), FALSE, array(
			'togglesOn'	=> array( 'manifest_short_url' ),
		), NULL, NULL, NULL, 'manifest_custom_url_toggle' ) );

		$form->add( new \IPS\Helpers\Form\Text( 'manifest_short_url', $formStartUrl, FALSE, array(), function( $val )
		{
			if ( $val and \IPS\Request::i()->manifest_custom_url_toggle_checkbox )
			{
				if ( mb_substr( $val, -1 ) !== '/' )
				{
					$val .= '/';
				}
				
				$response = \IPS\Http\Url::external( \IPS\Http\Url::baseUrl() . ( \IPS\Settings::i()->htaccess_mod_rewrite ? $val : 'index.php?/' . $val ) )->request( NULL, NULL, FALSE )->get();
				if ( $response->httpResponseCode != 200 and $response->httpResponseCode != 303 and ( \IPS\Settings::i()->site_online OR $response->httpResponseCode != 503 ) )
				{
					throw new \LogicException( 'pwa_start_url_incorrect' );
				}
			}
		}, \IPS\Http\Url::baseUrl() . ( !\IPS\Settings::i()->htaccess_mod_rewrite ? 'index.php?/' : '' ), NULL, 'manifest_short_url' ) );

		$form->add( new \IPS\Helpers\Form\Color( 'manifest_themecolor', ( isset( $manifestDetails['theme_color'] ) ) ? $manifestDetails['theme_color'] : NULL, FALSE, array(), NULL, NULL, NULL, 'manifest_themecolor' ) );
		$form->add( new \IPS\Helpers\Form\Color( 'manifest_bgcolor', ( isset( $manifestDetails['background_color'] ) ) ? $manifestDetails['background_color'] : NULL, FALSE, array(), NULL, NULL, NULL, 'manifest_bgcolor' ) );
		$form->add( new \IPS\Helpers\Form\Radio( 'manifest_display', ( isset( $manifestDetails['display'] ) ) ? $manifestDetails['display'] : 'standalone', FALSE, array( 'options' => array( 'fullscreen' => 'manifest_fullscreen', 'standalone' => 'manifest_standalone', 'minimal-ui' => 'manifest_minimalui', 'browser' => 'manifest_browser' ) ), NULL, NULL, NULL, 'manifest_display' ) );

		/* We've submitted, check our values! */
		if ( $values = $form->values() )
		{
			/* Favicon is easy, we just store the string value of the file object */
			$values['icons_favicon']		= (string) $values['icons_favicon'];

			/* Sharer logos are easy too, except it's an array of images instead of a single image */
			if( \count( $values['icons_sharer_logo'] ) )
			{
				$values['icons_sharer_logo']	= json_encode( array_map( function( $val ){ return (string) $val; }, $values['icons_sharer_logo'] ) );
			}

			$path = \IPS\Http\Url::createFromString( \IPS\Http\Url::baseUrl() )->data[ \IPS\Http\Url::COMPONENT_PATH ];
			$startUrl = $path ?? '';

			if ( $values['manifest_custom_url_toggle'] !== FALSE and ! empty( $values['manifest_short_url'] ) )
			{
				$startUrl = '/' . trim( $values['manifest_short_url'], '/' ) . '/';

				if( !empty( $path ) )
				{
					$startUrl = '/' . trim( $path . ( !\IPS\Settings::i()->htaccess_mod_rewrite ? 'index.php?/' : '' ) . ltrim( $values['manifest_short_url'], '/' ), '/' ) . '/';
				}
			}

			/* Homescreen icons are the hardest part, as we need to generate different sizes.. */
			if( $values['icons_homescreen'] )
			{
				$sizes = array( 
					'android-chrome-36x36'				=> array( 36, 36 ),
					'android-chrome-48x48'				=> array( 48, 48 ),
					'android-chrome-72x72'				=> array( 72, 72 ),
					'android-chrome-96x96'				=> array( 96, 96 ),
					'android-chrome-144x144'			=> array( 144, 144 ),
					'android-chrome-192x192'			=> array( 192, 192 ),
					'android-chrome-256x256'			=> array( 256, 256 ),
					'android-chrome-384x384'			=> array( 384, 384 ),
					'android-chrome-512x512'			=> array( 512, 512 ),
					'msapplication-square70x70logo'		=> array( 128, 128 ),
					'msapplication-TileImage'			=> array( 144, 144 ),
					'msapplication-square150x150logo'	=> array( 270, 270 ),
					'msapplication-wide310x150logo'		=> array( 558, 558 ),
					'msapplication-square310x310logo'	=> array( 558, 270 ),
					'apple-touch-icon-57x57'			=> array( 57, 57 ),
					'apple-touch-icon-60x60'			=> array( 60, 60 ),
					'apple-touch-icon-72x72'			=> array( 72, 72 ),
					'apple-touch-icon-76x76'			=> array( 76, 76 ),
					'apple-touch-icon-114x114'			=> array( 114, 114 ),
					'apple-touch-icon-120x120'			=> array( 120, 120 ),
					'apple-touch-icon-144x144'			=> array( 144, 144 ),
					'apple-touch-icon-152x152'			=> array( 152, 152 ),
					'apple-touch-icon-180x180'			=> array( 180, 180 ),
				);

				$setting = array( 'original' => (string) $values['icons_homescreen'] );

				foreach( $sizes as $filename => $_sizes )
				{
					try
					{
						$image = \IPS\Image::create( $values['icons_homescreen']->contents() );

						if( $image::exifSupported() )
						{
							$image->setExifData( $values['icons_homescreen']->contents() );
						}

						$image->crop( $_sizes[0], $_sizes[1] );
		
						$setting[ $filename ] = array(
							'url' 		=> (string) \IPS\File::create( 'core_Icons', $filename . '.png', (string) $image, NULL, TRUE, NULL, FALSE ),
							'width'		=> $image->width,
							'height'	=> $image->height
						);
					}
					catch ( \Exception $e ) { }
				}

				$values['icons_homescreen'] = json_encode( $setting );
			}
			else
			{
				/* Delete any images that may already exist */
				foreach( $homeScreen as $key => $image )
				{
					try
					{
						\IPS\File::get( 'core_Icons', ( $key == 'original' ) ? $image : $image['url'] )->delete();
					}
					catch( \Exception $e ){}
				}

				$values['icons_homescreen'] = '';
			}

			/* We need the string value of this uploaded file as well */
			$values['icons_mask_icon'] = (string) $values['icons_mask_icon'];

			/* Finally, handle the manifest details */
			$values['manifest_details'] = array();
			
			if( $values['configure_manifest'] )
			{
				$values['manifest_details']['short_name']		= $values['manifest_shortname'];
				$values['manifest_details']['start_url']		= $startUrl;
				$values['manifest_details']['name']				= $values['manifest_fullname'];
				$values['manifest_details']['description']		= $values['manifest_description'];
				$values['manifest_details']['theme_color']		= $values['manifest_themecolor'];
				$values['manifest_details']['background_color']	= $values['manifest_bgcolor'];
				$values['manifest_details']['display']			= $values['manifest_display'];
			}

			unset( $values['configure_manifest'], $values['manifest_shortname'], $values['manifest_fullname'], $values['manifest_description'], $values['manifest_display'], $values['manifest_bgcolor'], $values['manifest_themecolor'], $values['manifest_custom_url_toggle'], $values['manifest_short_url'] );

			$values['manifest_details'] = json_encode( $values['manifest_details'] );

			/* Save the settings */
			$form->saveAsSettings( $values );

			/* Clear guest page caches */
			\IPS\Data\Cache::i()->clearAll();

			/* Clear manifest and ie browser data stores */
			unset( \IPS\Data\Store::i()->manifest, \IPS\Data\Store::i()->iebrowserconfig );

			/* And log */
			\IPS\Session::i()->log( 'acplogs__icons_and_logos' );

			/* And Redirect */
			\IPS\Output::i()->redirect( $this->url , 'saved' );
		}
		
		\IPS\Output::i()->title		= \IPS\Member::loggedIn()->language()->addToStack('menu__core_customization_icons');
		\IPS\Output::i()->output	.= \IPS\Theme::i()->getTemplate( 'global' )->block( 'menu__core_customization_icons', $form );
	}
	
	// Create new methods with the same name as the 'do' parameter which should execute it
}