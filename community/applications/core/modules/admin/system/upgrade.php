<?php
/**
 * @brief		upgrade
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		28 Jul 2015
 */

namespace IPS\core\modules\admin\system;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * upgrade
 */
class _upgrade extends \IPS\Dispatcher\Controller
{
	/**
	 * @brief	IPS clientArea Password
	 */
	protected $_clientAreaPassword;

	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		\IPS\Output::i()->jsFiles = array_merge( \IPS\Output::i()->jsFiles, \IPS\Output::i()->js( 'admin_system.js', 'core', 'admin' ) );
		parent::execute();
	}

	/**
	 * Manage
	 *
	 * @return	void
	 */
	protected function manage()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'upgrade_manage', 'core', 'overview' );
		
		if ( \IPS\NO_WRITES )
		{
			\IPS\Output::i()->error( 'no_writes', '1C287/1', 403, '' );
		}
		
		$initialData = NULL;
		if ( isset( \IPS\Request::i()->patch ) )
		{
			$initialData = array( 'patch' => 1 );
		}
		
		/* If we are on 4.4.4 and start upgrading to 4.4.5, the initial $steps array will include the upgrade_extra_update key, however during the extraction this file
			gets overwritten, and if we are on CiC the new array no longer contains this definition, resulting in an error "Function name must be a string". We need to
			inspect the $_SESSION array to see if "upgrade_extract_update" is defined as the next step and use the first array if so. */
		$upgradeUrl = \IPS\Http\Url::internal( 'app=core&module=system&controller=upgrade' );
		$forceNormalExtraction = ( isset( $_SESSION[ 'wizard-' . md5( $upgradeUrl ) . '-step' ] ) ) ? ( $_SESSION[ 'wizard-' . md5( $upgradeUrl ) . '-step' ] == 'upgrade_extract_update' ) : FALSE;
		
		if ( !\IPS\CIC or $forceNormalExtraction )
		{
			$steps = array(
				'upgrade_confirm_update'	=> array( $this, '_selectVersion' ),
				'upgrade_login'				=> array( $this, '_login' ),
				'upgrade_ftp_details'		=> array( $this, '_ftpDetails' ),
				'upgrade_extract_update'	=> array( $this, '_extractUpdate' ),
				'upgrade_upgrade'			=> array( $this, '_upgrade' ),
			);
		}
		else
		{
			$steps = array(
				'upgrade_confirm_update'	=> array( $this, '_selectVersion' ),
				'upgrade_confirm_cic'		=> array( $this, '_confirmCic' ),
				'upgrade_apply_cic'			=> array( $this, '_applyCic' ),
				'upgrade_upgrade'			=> array( $this, '_upgrade' )
			);
		}

		$wizard = new \IPS\Helpers\Wizard( $steps, $upgradeUrl, TRUE, $initialData );
		
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('ips_suite_upgrade');
		\IPS\Output::i()->output = (string) $wizard;
	}
	
	/**
	 * Select Version
	 *
	 * @param	array	$data	Wizard data
	 * @return	string|array
	 */
	public function _selectVersion( $data )
	{		
		/* Check latest version */
		$versions = array();
		foreach ( \IPS\Db::i()->select( '*', 'core_applications', \IPS\Db::i()->in( 'app_directory', \IPS\Application::$ipsApps ) ) as $app )
		{
			if ( $app['app_enabled'] )
			{
				$versions[] = $app['app_long_version'];
			}
		}
		$version = min( $versions );
		$url = \IPS\Http\Url::ips('updateCheck')->setQueryString( array( 'type' => 'upgrader', 'key' => \IPS\Settings::i()->ipb_reg_number ) );
		if ( \IPS\USE_DEVELOPMENT_BUILDS )
		{
			$url = $url->setQueryString( 'development', 1 );
		}
		try
		{
			$response = $url->setQueryString( 'version', $version )->request()->get()->decodeJson();
			$coreApp = \IPS\Application::load('core');
			$coreApp->update_version = json_encode( $response );
			$coreApp->update_last_check = time();
			$coreApp->save();
		}
		catch ( \Exception $e ) { }
		
		/* Build form */
		$form = new \IPS\Helpers\Form( 'select_version' );
		$options = array();
		$descriptions = array();
		$latestVersion = 0;
		foreach( \IPS\Application::load( 'core' )->availableUpgrade( FALSE, !isset( $data['patch'] ) ) as $possibleVersion )
		{
			$options[ $possibleVersion['longversion'] ] = $possibleVersion['version'];
			$descriptions[ $possibleVersion['longversion'] ] = $possibleVersion;
			if ( $latestVersion < $possibleVersion['longversion'] )
			{
				$latestVersion = $possibleVersion['longversion'];
			}
		}
		if ( \IPS\TEST_DELTA_ZIP )
		{
			$options['test'] = 'x.y.z';
			$descriptions['test'] = array(
				'releasenotes'	=> '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis scelerisque rhoncus leo. In eu ultricies magna. Vivamus nec est vitae felis iaculis mollis non ac ante. In vitae erat quis urna volutpat vulputate. Integer ultrices tellus felis, at posuere nulla faucibus nec. Fusce malesuada nunc purus, luctus accumsan nulla rhoncus ut. Nam ac pharetra magna. Nam semper augue at mi tempus, sed dapibus metus cursus. Suspendisse potenti. Curabitur at pulvinar metus, sed pharetra elit.</p>',
				'security'		=> FALSE,
				'updateurl'		=> '',
			);
		}
		if ( !$options )
		{
			\IPS\core\AdminNotification::remove( 'core', 'NewVersion' );
			\IPS\Output::i()->error( 'download_upgrade_nothing', '1C287/4', 403, '' );
		}
		$form->add( new \IPS\Helpers\Form\Radio( 'version', $latestVersion, TRUE, array( 'options' => $options, '_details' => $descriptions ) ) );
		
		/* Handle submissions */
		if ( $values = $form->values() )
		{
			/* Check requirements */
			try
			{
				$requirements = \IPS\Http\Url::ips('requirements')->setQueryString( 'version', $values['version'] )->request()->get()->decodeJson();
				$phpVersion = PHP_VERSION;
				$mysqlVersion = \IPS\Db::i()->server_info;
				if ( !( version_compare( $phpVersion, $requirements['php']['required'] ) >= 0 ) )
				{
					if ( $requirements['php']['required'] == $requirements['php']['recommended'] )
					{
						$message = \IPS\Member::loggedIn()->language()->addToStack( 'requirements_php_version_fail_no_recommended', FALSE, array( 'sprintf' => array( $phpVersion, $requirements['php']['required'] ) ) );
					}
					else
					{
						$message = \IPS\Member::loggedIn()->language()->addToStack( 'requirements_php_version_fail', FALSE, array( 'sprintf' => array( $phpVersion, $requirements['php']['required'], $requirements['php']['recommended'] ) ) );
					}
					\IPS\Output::i()->error( $message, '1C287/2' );
				}
				if ( !( version_compare( $mysqlVersion, $requirements['mysql']['required'] ) >= 0 ) )
				{
					\IPS\Output::i()->error( \IPS\Member::loggedIn()->language()->addToStack( 'requirements_mysql_version_fail', FALSE, array( 'sprintf' => array( $mysqlVersion, $requirements['mysql']['required'], $requirements['mysql']['recommended'] ) ) ), '1C287/3', 403, '' );
				}
			}
			catch ( \Exception $e ) {}
			
			/* Check our files aren't modified */
			if ( !\IPS\Request::i()->skip_md5_check )
			{
				try
				{
					$files = \IPS\Application::md5Check();
					if ( \count( $files ) )
					{
						return \IPS\Theme::i()->getTemplate('system')->upgradeDeltaMd5( $values['version'], $files );
					}
				}
				catch ( \Exception $e ) {}
			}
			
			/* Check theme compatibility */
			$extra = NULL;
			if ( \IPS\TEST_DELTA_TEMPLATE_CHANGES )
			{
				$themeChanges = json_decode( \IPS\TEST_DELTA_TEMPLATE_CHANGES, TRUE );
			}
			else
			{
				try
				{
					$themeChanges = \IPS\Http\Url::ips( 'themediff/' . \intval( \IPS\Application::getAvailableVersion('core') ) . '-' . \intval( $values['version'] ) )->setQueryString( 'list', 1 )->request()->get()->decodeJson();
				}
				catch ( \Exception $e )
				{
					$themeChanges = NULL;
					$extra = \get_class( $e ) . '::' . $e->getCode() . ": " . $e->getMessage();
				}
			}
			if ( !\is_array( $themeChanges ) )
			{
				\IPS\Output::i()->error( 'delta_upgrade_fail_server', '3C287/5', 500, NULL, array(), $extra );
			}
			if ( !isset( \IPS\Request::i()->skip_theme_check ) )
			{
				$conflicts = array();
				if ( isset( $themeChanges['html'] ) )
				{
					foreach ( $themeChanges['html'] as $_app => $_locations )
					{
						foreach ( $_locations as $_location => $_groups )
						{
							foreach ( $_groups as $_group => $_changedTemplates )
							{														
								foreach ( \IPS\Db::i()->select( array( 'template_id', 'template_set_id', 'template_name' ), 'core_theme_templates', array( array( 'template_set_id>0 AND template_app=? AND template_location=? AND template_group=?', $_app, $_location, $_group ), array( \IPS\Db::i()->in( 'template_name', array_keys( $_changedTemplates ) ) ) ) ) as $modifiedTemplate )
								{
									if ( array_key_exists( $modifiedTemplate['template_set_id'], \IPS\Theme::themes() ) )
									{
										$conflicts[ $modifiedTemplate['template_set_id'] ]['html'][ $_app . '/' . $_location . '/' . $_group . '/' . $modifiedTemplate['template_name'] ] = $modifiedTemplate['template_id'];
									}
								}
							}
						}
					}
				}
				if ( isset( $themeChanges['css'] ) )
				{
					foreach ( $themeChanges['css'] as $_app => $_locations )
					{
						foreach ( $_locations as $_location => $_paths )
						{
							foreach ( $_paths as $_path => $_changedFiles )
							{				
								foreach ( \IPS\Db::i()->select( array( 'css_id', 'css_set_id', 'css_name' ), 'core_theme_css', array( array( 'css_set_id>0 AND css_app=? AND css_location=? AND css_path=?', $_app, $_location, $_path ), array( \IPS\Db::i()->in( 'css_name', array_keys( $_changedFiles ) ) ) ) ) as $modifiedCss )
								{
									if ( array_key_exists( $modifiedCss['css_set_id'], \IPS\Theme::themes() ) )
									{
										$conflicts[ $modifiedCss['css_set_id'] ]['css'][ $_app . '/' . $_location . '/' . $_path . '/' . $modifiedCss['css_name'] ] = $modifiedCss['css_id'];
									}
								}
							}
						}
					}
				}
				if ( \count( $conflicts ) )
				{
					return \IPS\Theme::i()->getTemplate('system')->upgradeDeltaThemeConflicts( $values['version'], $conflicts );
				}
			}
			
			/* Return */
			return array( 'version' => $values['version'], 'themeChanges' => $themeChanges );
		}
		
		/* Display */
		return $form->customTemplate( array( \IPS\Theme::i()->getTemplate( 'system' ), 'upgradeSelectVersion' ) );
	}
	
	/**
	 * Confirm upgrade for CiC
	 *
	 * @param	array	$data	Wizard data
	 * @return	string|array
	 */
	public function _confirmCic( $data )
	{
		$form = new \IPS\Helpers\Form( 'cic_confirm', 'continue' );
		$form->hiddenValues['version'] = $data['version'];
		$form->addMessage( 'cic_upgrade_confirm' );
		if ( $values = $form->values() )
		{
			/* Log that they confirmed. */
			\IPS\Session::i()->log( 'acplogs__cic_upgrade' );
			
			/* Boink */
			return $data;
		}
		
		return $form;
	}
	
	/**
	 * Apply files for CiC
	 *
	 * @param	array	$data	Wizard data
	 * @return	string|array
	 */
	public function _applyCic( $data )
	{
		if ( isset( \IPS\Request::i()->fail ) )
		{
			return \IPS\Theme::i()->getTemplate('system')->upgradeDeltaFailed( 'exception', isset( $data['key'] ) ? \IPS\Http\Url::ips("download/{$data['key']}") : NULL );
		}
		
		\IPS\core\Setup\Upgrade::setUpgradingFlag( TRUE );
		
		/* Check latest version */
		$versions = array();
		foreach ( \IPS\Db::i()->select( '*', 'core_applications', \IPS\Db::i()->in( 'app_directory', \IPS\Application::$ipsApps ) ) as $app )
		{
			if ( $app['app_enabled'] )
			{
				$versions[] = $app['app_long_version'];
			}
		}
		$version = min( $versions );
		
		$extractUrl = new \IPS\Http\Url( \IPS\Settings::i()->base_url . \IPS\CP_DIRECTORY . '/upgrade/extractCic.php' );
		$extractUrl = $extractUrl
			->setScheme( NULL )	// Use protocol-relative in case the AdminCP is being loaded over https but rest of site is not
			->setQueryString( array(
				'account'		=> \IPS\IPS::getCicUsername(),
				'key'			=> md5( \IPS\IPS::getCicUsername() . \IPS\Settings::i()->sql_pass ),
				'version'		=> $version,
				'adsess'		=> \IPS\Request::i()->adsess
			) 
		);
		
		/* Send the request */
		\IPS\IPS::applyLatestFilesIPSCloud( $version );
		
		/* NOTE: We still need to use an iframe here, as CiC would still be susceptible to the same failures. */
		return \IPS\Theme::i()->getTemplate('system')->upgradeExtractCic( $extractUrl );
	}
	
	/**
	 * Login
	 *
	 * @param	array	$data	Wizard data
	 * @return	string|array
	 */
	public function _login( $data )
	{
		/* If we're just testing, we can skip this step */
		if ( \IPS\TEST_DELTA_ZIP and $data['version'] == 'test' )
		{
			$data['key'] = 'test';
			return $data;
		}
				
		/* Build form */
		$form = new \IPS\Helpers\Form( 'login', 'continue' );
		$form->hiddenValues['version'] = $data['version'];
		$form->add( new \IPS\Helpers\Form\Email( 'ips_email_address', NULL ) );
		$form->add( new \IPS\Helpers\Form\Password( 'ips_password', NULL ) );
		
		/* Handle submissions */
		if ( $values = $form->values() )
		{
			try
			{
				$this->_clientAreaPassword = $values['ips_password'];
				if ( $downloadKey = $this->_getDownloadKey( $values['ips_email_address'], isset( $values['version'] ) ? $values['version'] : NULL ) )
				{
					$data['key'] = $downloadKey;
					$data['ips_email'] = $values['ips_email_address'];
					$data['ips_pass'] = $values['ips_password'];
					return $data;
				}
				else
				{
					if ( \IPS\Db::i()->select( 'MIN(app_long_version)', 'core_applications', \IPS\Db::i()->in( 'app_directory', \IPS\Application::$ipsApps ) )->first() < \IPS\Application::getAvailableVersion('core') )
					{
						$data['key'] = NULL;
						return $data;
					}
					$form->error = \IPS\Member::loggedIn()->language()->addToStack('download_upgrade_nothing');
				}
			}
			catch ( \LogicException $e )
			{
				\IPS\Log::log( $e, 'auto_upgrade' );
				$form->error = $e->getMessage();
			}
			catch ( \RuntimeException $e )
			{
				\IPS\Log::log( $e, 'auto_upgrade' );
				$form->error = \IPS\Member::loggedIn()->language()->addToStack('download_upgrade_error');
			}
		}
		
		return (string) $form;
	}
	
	/**
	 * Get a download key
	 *
	 * @param	string		$clientAreaEmail		IPS client area email address
	 * @param	string		$version			Version to download
	 * @param	array		$files				If desired, specific files to download rather than a delta from current version
	 * @return	string|NULL	string is a download key. NULL indicates already running the latest version
	 * @throws	\LogicException
	 * @throws	\IPS\Http\Request\Exception
	 * @throws	\RuntimeException
	 */
	protected function _getDownloadKey( $clientAreaEmail, $version, $files=array() )
	{
		$key = \IPS\IPS::licenseKey();
		$url = \IPS\Http\Url::ips( 'build/' . $key['key'] )->setQueryString( 'ip', \IPS\Request::i()->ipAddress() );
		
		if ( \IPS\USE_DEVELOPMENT_BUILDS )
		{
			$url = $url->setQueryString( 'development', 1 );
		}
		elseif ( $version )
		{
			$url = $url->setQueryString( 'versionToDownload', $version );
		}
		if ( \IPS\CP_DIRECTORY !== 'admin' )
		{
			$url = $url->setQueryString( 'cp_directory', \IPS\CP_DIRECTORY );
		}
		/* Check whether the converter application is present and installed */
		if ( array_key_exists( 'convert', \IPS\Application::applications() )
			AND file_exists( \IPS\ROOT_PATH . '/applications/convert/Application.php' )
			AND \IPS\Application::load( 'convert' )->version == \IPS\Application::load('core')->version )
		{
			$url = $url->setQueryString( 'includeConverters', 1 );
		}
		if ( $files )
		{
			$url = $url->setQueryString( 'files', implode( ',', $files ) );
		}
				
		$response = $url->request( \IPS\LONG_REQUEST_TIMEOUT )->login( $clientAreaEmail, $this->_clientAreaPassword )->get();
		switch ( $response->httpResponseCode )
		{
			case 200:
				if ( !preg_match( '/^ips_[a-z0-9]{5}$/', (string) $response ) )
				{
					throw new \RuntimeException( (string) $response );
				}
				else
				{
					return (string) $response;
				}
			
			case 304:
				return NULL;
			
			default:
				throw new \LogicException( (string) $response );
		}
	}
	
	/**
	 * Get FTP Details
	 *
	 * @param	array	$data	Wizard data
	 * @return	string|array
	 */
	public function _ftpDetails( $data )
	{
		if ( \IPS\DELTA_FORCE_FTP or !is_writable( \IPS\ROOT_PATH . '/init.php' ) or !is_writable( \IPS\ROOT_PATH . '/applications/core/Application.php' ) or !is_writable( \IPS\ROOT_PATH . '/system/Db/Db.php' ) )
		{
			/* If the server does not have the Ftp extension, we can't do this and have to prompt the user to downlad manually... */
			if ( !\function_exists( 'ftp_connect' ) )
			{
				return \IPS\Theme::i()->getTemplate('system')->upgradeDeltaFailed( 'ftp', isset( $data['key'] ) ? \IPS\Http\Url::ips("download/{$data['key']}") : NULL );
			}
			/* Otherwise, we can ask for FTP details... */
			else
			{
				/* If they've clicked the button to manually apply patch, let them do that */
				if ( isset( \IPS\Request::i()->manual ) )
				{
					$data['manual'] = TRUE;
					return $data;
				}
				/* Otherwise, carry on */
				else
				{
					/* Define the method we will use to validate the FTP details */
					$validateCallback = function( $ftp ) {
						try
						{
							if ( file_get_contents( \IPS\ROOT_PATH . '/conf_global.php' ) != $ftp->download( 'conf_global.php' ) )
							{
								throw new \DomainException('delta_upgrade_ftp_details_no_match');
							}
						}
						catch ( \IPS\Ftp\Exception $e )
						{
							throw new \DomainException('delta_upgrade_ftp_details_err');
						}
					};
					
					/* If we have details stored, retreive them */
					if ( \IPS\Settings::i()->upgrade_ftp_details and $decoded = @json_decode( \IPS\Text\Encrypt::fromCipher( \IPS\Settings::i()->upgrade_ftp_details )->decrypt(), TRUE ) )
					{
						$defaultDetails = $decoded;
					}
					/* Otherwise, guess the server/username/password for the user's benefit */
					else
					{
						$defaultDetails = array(
							'server'	=> \IPS\Http\Url::internal('')->data['host'],
							'un'		=> @get_current_user(),
							'path'		=> str_replace( '/home/' . @get_current_user(), '', \IPS\ROOT_PATH )
						);
					}
											
					/* Build the form */
					$form = new \IPS\Helpers\Form( 'ftp_details', 'continue' );
					$form->add( new \IPS\Helpers\Form\Ftp( 'delta_upgrade_ftp_details', $defaultDetails, TRUE, array( 'rejectUnsupportedSftp' => TRUE, 'allowBypassValidation' => FALSE ), $validateCallback ) );
					$form->add( new \IPS\Helpers\Form\Checkbox( 'delta_upgrade_ftp_remember', TRUE ) );
					
					/* Handle submissions */
					if ( $values = $form->values() )
					{
						if ( $values['delta_upgrade_ftp_remember'] )
						{
							\IPS\Settings::i()->changeValues( array( 'upgrade_ftp_details' => \IPS\Text\Encrypt::fromPlaintext( json_encode( $values['delta_upgrade_ftp_details'] ) )->cipher ) );
						}
						
						$data['ftpDetails'] = $values['delta_upgrade_ftp_details'];
						return $data;
					}
					
					/* Display the form */
					return \IPS\Theme::i()->getTemplate('system')->upgradeDeltaFtp( (string) $form );
				}
			}
		}
		else
		{
			return $data;
		}
	}

	/**
	 * Download & Extract Update
	 *
	 * @param	array	$data	Wizard data
	 * @return	string|array
	 */
	public function _extractUpdate( $data )
	{
		/* If extraction failed, show error */
		if ( isset( \IPS\Request::i()->fail ) )
		{
			if( \IPS\CIC )
			{
				try
				{
					\IPS\IPS::applyLatestFilesIPSCloud();
				}
				catch( \IPS\Http\Request\Exception $e ){}

				return \IPS\Theme::i()->getTemplate('system')->upgradeDeltaFailedCic();
			}
			else
			{
				return \IPS\Theme::i()->getTemplate('system')->upgradeDeltaFailed( 'exception', isset( $data['key'] ) ? \IPS\Http\Url::ips("download/{$data['key']}") : NULL );
			}
		}
		
		/* Download & Extract */
		if ( $data['key'] and !isset( \IPS\Request::i()->check ) )
		{			
			/* If we've asked to do it manually, just show that screen */
			if ( isset( $data['manual'] ) and $data['manual'] )
			{
				return \IPS\Theme::i()->getTemplate('system')->upgradeDeltaFailed( NULL, isset( $data['key'] ) ? \IPS\Http\Url::ips("download/{$data['key']}") : NULL );;
			}
					
			/* Multiple Redirector */
			$url = \IPS\Http\Url::internal('app=core&module=system&controller=upgrade');
			return (string) new \IPS\Helpers\MultipleRedirect( $url, function( $mrData ) use ( $data )
			{
				/* Init */
				if ( !\is_array( $mrData ) )
				{
					return array( array( 'status' => 'download' ), \IPS\Member::loggedIn()->language()->addToStack('delta_upgrade_processing') );
				}
				/* Download */
				elseif ( $mrData['status'] == 'download' )
				{
					if ( !isset( $mrData['tmpFileName'] ) )
					{					
						$mrData['tmpFileName'] = tempnam( \IPS\TEMP_DIRECTORY, 'IPS' ) . '.zip';
						
						return array( $mrData, \IPS\Member::loggedIn()->language()->addToStack('delta_upgrade_downloading'), 0 );
					}
					else
					{
						if ( \IPS\TEST_DELTA_ZIP and $data['version'] == 'test' )
						{
							\file_put_contents( $mrData['tmpFileName'], file_get_contents( \IPS\TEST_DELTA_ZIP ) );
							$mrData['status'] = 'extract';
							return array( $mrData, \IPS\Member::loggedIn()->language()->addToStack('delta_upgrade_extracting'), 0 );
						}
						else
						{
							if ( !isset( $mrData['range'] ) )
							{
								$mrData['range'] = 0;
							}
							$startRange = $mrData['range'];
							$endRange = $startRange + 1000000 - 1;
							
							$response = \IPS\Http\Url::ips("download/{$data['key']}")->request( \IPS\LONG_REQUEST_TIMEOUT )->setHeaders( array( 'Range' => "bytes={$startRange}-{$endRange}" ) )->get();

							\IPS\Log::debug( "Fetching download [range={$startRange}-{$endRange}] with a response code: " . $response->httpResponseCode, 'auto_upgrade' );
				
							if ( $response->httpResponseCode == 404 )
							{
								if ( isset( $mrData['tmpFileName'] ) )
								{
									@unlink( $mrData['tmpFileName'] );
								}

								\IPS\Log::log( "Cannot fetch delta download: " . var_export( $response, TRUE ), 'auto_upgrade' );
								
								if( \IPS\CIC )
								{
									try
									{
										\IPS\IPS::applyLatestFilesIPSCloud();
									}
									catch( \IPS\Http\Request\Exception $e ){}

									return \IPS\Theme::i()->getTemplate('system')->upgradeDeltaFailedCic();
								}
								else
								{
									return array( \IPS\Theme::i()->getTemplate('system')->upgradeDeltaFailed( 'unexpected_response', isset( $data['key'] ) ? \IPS\Http\Url::ips("download/{$data['key']}") : NULL ) );
								}
							}
							elseif ( $response->httpResponseCode == 206 )
							{
								$totalFileSize = \intval( mb_substr( $response->httpHeaders['Content-Range'], mb_strpos( $response->httpHeaders['Content-Range'], '/' ) + 1 ) );
								$fh = \fopen( $mrData['tmpFileName'], 'a' );
								\fwrite( $fh, (string) $response );
								\fclose( $fh );
		
								$mrData['range'] = $endRange + 1;
								return array( $mrData, \IPS\Member::loggedIn()->language()->addToStack('delta_upgrade_downloading'), 100 / $totalFileSize * $mrData['range'] );
							}
							else
							{
								$mrData['status'] = 'extract';
								return array( $mrData, \IPS\Member::loggedIn()->language()->addToStack('delta_upgrade_extracting'), 0 );
							}
						}
					}
				}
				/* Extract */
				elseif ( $mrData['status'] == 'extract' )
				{
					\IPS\core\Setup\Upgrade::setUpgradingFlag( TRUE );

					$extractUrl = new \IPS\Http\Url( \IPS\Settings::i()->base_url . \IPS\CP_DIRECTORY . '/upgrade/extract.php' );
					$extractUrl = $extractUrl
						->setScheme( NULL )	// Use protocol-relative in case the AdminCP is being loaded over https but rest of site is not
						->setQueryString( array(
							'file'			=> $mrData['tmpFileName'],
							'container'		=> $data['key'],
							'key'			=> md5( \IPS\Settings::i()->board_start . $mrData['tmpFileName'] . \IPS\Settings::i()->sql_pass ),
							'ftp'			=> ( isset( $data['ftpDetails'] ) ) ? $data['ftpDetails'] : ''
						) 
					);
					
					return array( \IPS\Theme::i()->getTemplate('system')->upgradeExtract( $extractUrl ) );
				}
			},
			function()
			{
				\IPS\Output::i()->redirect( \IPS\Http\Url::internal('app=core&module=system&controller=upgrade&check=1') );
			} );
		}
		
		/* Run md5 check */
		try
		{
			$files = \IPS\Application::md5Check();
			if ( \count( $files ) )
			{
				/* Log */
				\IPS\Log::debug( "MD5 check of delta download failed with " . \count( $files ) . " reported as modified", 'auto_upgrade' );
				
				/* If we'rve already tried to fix them and failed, show an error */
				if ( isset( $data['md5Fix'] ) and $data['md5Fix'] )
				{
					return \IPS\Theme::i()->getTemplate('system')->upgradeDeltaFailed( 'exception', NULL );
				}
				
				/* Otherwise try to just fix them - first get a new download key */
				$files = array_map( function( $file ) {
					return str_replace( \IPS\ROOT_PATH, '', $file );
				}, $files );
				$this->_clientAreaPassword = $data['ips_pass'];
				$newDownloadKey = $this->_getDownloadKey( $data['ips_email'], $data['version'], $files );

				/* Manipulate the wizard data */
				$data = $_SESSION[ 'wizard-' . md5( \IPS\Http\Url::internal( 'app=core&module=system&controller=upgrade' ) ) . '-data' ];
				$data['key'] = $newDownloadKey;
				$data['md5Fix'] = TRUE;
				$_SESSION[ 'wizard-' . md5( \IPS\Http\Url::internal( 'app=core&module=system&controller=upgrade' ) ) . '-data' ] = $data;
				
				/* Redirect back in */
				\IPS\Output::i()->redirect( \IPS\Http\Url::internal('app=core&module=system&controller=upgrade') );
			}
		}
		catch ( \Exception $e ) {}
												
		/* Nope, we're good! */
		return $data;
	}
	
	/**
	 * Upgrade
	 *
	 * @param	array	$data	Wizard data
	 * @return	string|array
	 */
	public function _upgrade( $data )
	{
		/* Resync */
		\IPS\IPS::resyncIPSCloud('Uploaded new version');
		
		/* If this is NOT a patch, redirect them to the upgrader */
		if ( !isset( $data['patch'] ) )
		{
			\IPS\Output::i()->redirect( 'upgrade/?adsess=' . \IPS\Request::i()->adsess );
			return;
		}
		
		/* Otherwise let's do the upgrade! */
		$url = \IPS\Http\Url::internal('app=core&module=system&controller=upgrade');
		return (string) new \IPS\Helpers\MultipleRedirect( $url, function( $mrData ) use ( $data )
		{
			if ( !\is_array( $mrData ) )
			{
				return array( array( 'step' => 0 ), \IPS\Member::loggedIn()->language()->addToStack('delta_upgrade_processing'), 0 );
			}
			else
			{								
				$steps = array(
					'_upgradeTemplates',
				);
				$perStepPercentage = ( 100 / \count( $steps ) );	
							
				if ( array_key_exists( $mrData['step'], $steps ) )
				{
					$step = $steps[ $mrData['step'] ];
					$percentage = $perStepPercentage * \intval( $mrData['step'] );
					$stepData = isset( $mrData[ $step ] ) ? $mrData[ $step ] : array();
										
					$return = $this->$step( $data, $stepData );
					if ( $return === NULL )
					{						
						unset( $mrData[ $step ] );
						$mrData['step']++;
						$percentage += $perStepPercentage;
					}
					else
					{
						$mrData[ $step ] = $return[1];
						$percentage += ( $return[0] / ( 100 / $perStepPercentage ) );
					}
																			
					return array( $mrData, \IPS\Member::loggedIn()->language()->addToStack( 'delta_upgrade' . $step ), round( $percentage, 2 ) );
				}
				else
				{
					\IPS\core\Setup\Upgrade::setUpgradingFlag( FALSE );
					return array( \IPS\Theme::i()->getTemplate('system')->upgradeFinished() );
				}
			}
		},
		function()
		{
			\IPS\Output::i()->redirect( 'upgrade/?adsess=' . \IPS\Request::i()->adsess );
		} );
	}
		
	/**
	 * Upgrade: HTML and CSS
	 *
	 * @param	array	$data		Wizard data
	 * @param	array	$stepData	Data for this step
	 * @return	array|null	array( percentage of this step complete, $stepData ) OR NULL if this step is complete
	 */
	public function _upgradeTemplates( $data, $stepData )
	{
		return $this->_appLoop( $stepData, function( $app, $stepData ) use ( $data )
		{						
			/* Get counts or delete old stuff (we need to do it this way to ensure removed stuff gets removed) */
			$numberOfChangesInThisApp = 0;
			if ( isset( $data['themeChanges']['html'] ) and isset( $data['themeChanges']['html'][ $app ] ) )
			{
				foreach ( $data['themeChanges']['html'][ $app ] as $_location => $_groups )
				{
					foreach ( $_groups as $_group => $_changedTemplates )
					{
						if ( !isset( $stepData['offset'] ) )
						{
							\IPS\Theme::deleteCompiledTemplate( $app, $_location, $_group );
															
							foreach ( $_changedTemplates as $_template => $_type )
							{
								if ( $_type != 'added' )
								{
									\IPS\Theme::removeTemplates( $app, $_location, $_group, NULL, FALSE, $_template );
								}
							}
						}
						else
						{
							$numberOfChangesInThisApp += \count( $_changedTemplates );
						}
					}
				}		
			}
			if ( isset( $data['themeChanges']['css'] ) and isset( $data['themeChanges']['css'][ $app ] ) )
			{
				foreach ( $data['themeChanges']['css'][ $app ] as $_location => $_paths )
				{
					foreach ( $_paths as $_path => $_changedFiles )
					{		
						if ( !isset( $stepData['offset'] ) )
						{		
							foreach ( $_changedFiles as $_file => $_type )
							{
								if ( $_type != 'added' )
								{				
									\IPS\Theme::deleteCompiledCss( $app, $_location, $_path, $_file );
									\IPS\Theme::removeCss( $app, $_location, $_path, NULL, FALSE, $_file );
								}
							}
						}
						else
						{
							$numberOfChangesInThisApp += \count( $_changedFiles );
						}
					}
				}
			}
			if ( !isset( $stepData['offset'] ) )
			{
				if ( $numberOfChangesInThisApp )
				{
					return NULL;
				}
				else
				{
					$stepData['offset'] = 0;
					return array( 0, $stepData );
				}
			}
						
			/* Import new stuff */			
			$perLoop = 150;
			$i = 0;
			$done = 0;
			$xml = new \IPS\Xml\XMLReader;
			$xml->open( \IPS\ROOT_PATH . "/applications/{$app}/data/theme.xml" );
			$xml->read();
			while ( $xml->read() )
			{
				/* Skip to where we need to be */
				if( $xml->nodeType != \XMLReader::ELEMENT )
				{
					continue;
				}
				$i++;
				if ( $stepData['offset'] )
				{
					if ( $i - 1 < $stepData['offset'] )
					{
						$xml->next();
						continue;
					}
				}
				
				/* Templates */
				if( $xml->name == 'template' )
				{			
					if ( $location = $xml->getAttribute('template_location') and isset( $data['themeChanges']['html'][ $app ][ $location ] ) )
					{
						if ( $group = $xml->getAttribute('template_group') and isset( $data['themeChanges']['html'][ $app ][ $location ][ $group ] ) )
						{
							if ( $template = $xml->getAttribute('template_name') and isset( $data['themeChanges']['html'][ $app ][ $location ][ $group ][ $template ] ) )
							{		
								\IPS\Theme::addTemplate( array(
									'app'				=> $app,
									'group'				=> $group,
									'name'				=> $template,
									'variables'			=> $xml->getAttribute('template_data'),
									'content'			=> $xml->readString(),
									'location'			=> $location,
									'_default_template' => true
								) );
								$done++;
							}
						}
					}
				}
				/* CSS Files */
				elseif( $xml->name == 'css' )
				{
					if ( $location = $xml->getAttribute('css_location') and isset( $data['themeChanges']['css'][ $app ][ $location ] ) )
					{
						if ( $path = $xml->getAttribute('css_path') and isset( $data['themeChanges']['css'][ $app ][ $location ][ $path ] ) )
						{
							if ( $name = $xml->getAttribute('css_name') and isset( $data['themeChanges']['css'][ $app ][ $location ][ $path ][ $name ] ) )
							{
								\IPS\Theme::addCss( array(
									'app'		=> $app,
									'location'	=> $location,
									'path'		=> $path,
									'name'		=> $name,
									'content'	=> $xml->readString(),
									'_default_template' => true
								) );
								$done++;
							}
						}
					}
				}
								
				/* Have we done the most we're allowed per loop? */
				if( $done >= $perLoop )
				{
					$stepData['offset'] = $i;
					$stepData['done'] = isset( $stepData['done'] ) ? ( $stepData['done'] + $done ) : $done;
					return array( 100 / $numberOfChangesInThisApp * $stepData['done'], $stepData ); 
				}
			}
			
			/* If we're still here, this app is complete */
			return NULL;
		} );
	}
	
	/**
	 * App Looper
	 *
	 * @param	array		$stepData	Data for this step
	 * @param	callback	$code		Code to execute for each app
	 * @return	array|null	array( percentage of this step complete, $stepData ) OR NULL if this step is complete
	 */
	protected function _appLoop( $stepData, $code )
	{		
		$returnNext = FALSE;
		$apps = array_keys( \IPS\Application::applications() );
		$percentage = 0;
		$perAppPercentage = ( 100 / \count( $apps ) );
		
		foreach ( $apps as $app )
		{
			if( !\in_array( $app, \IPS\Application::$ipsApps ) )
			{
				continue;
			}
			
			if ( !isset( $stepData['app'] ) )
			{
				$stepData['app'] = $app;
			}
			
			if ( $stepData['app'] == $app )
			{
				$val = \call_user_func( $code, $app, $stepData );
				
				if ( $val !== NULL )
				{
					$percentage += ( $val[0] / ( 100 / $perAppPercentage ) );
					return array( $percentage, $val[1] );
				}
				else
				{
					$returnNext = TRUE;
				}
			}
			else
			{
				$percentage += $perAppPercentage;
				
				if ( $returnNext )
				{
					$stepData = array( 'app' => $app );
					return array( $percentage, $stepData );
				}
			}
		}
		
		return NULL;
	}
}
