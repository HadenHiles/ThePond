<?php

// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

GFForms::include_feed_addon_framework();

class GFGetResponse extends GFFeedAddOn {

	protected $_version = GF_GETRESPONSE_VERSION;
	protected $_min_gravityforms_version = '1.9.14.26';
	protected $_slug = 'gravityformsgetresponse';
	protected $_path = 'gravityformsgetresponse/getresponse.php';
	protected $_full_path = __FILE__;
	protected $_url = 'http://www.gravityforms.com';
	protected $_title = 'Gravity Forms GetResponse Add-On';
	protected $_short_title = 'GetResponse';

	// Members plugin integration
	protected $_capabilities = array( 'gravityforms_getresponse', 'gravityforms_getresponse_uninstall' );

	// Permissions
	protected $_capabilities_settings_page = 'gravityforms_getresponse';
	protected $_capabilities_form_settings = 'gravityforms_getresponse';
	protected $_capabilities_uninstall = 'gravityforms_getresponse_uninstall';
	protected $_enable_rg_autoupgrade = true;
	
	protected $api = null;
	private static $_instance = null;

	/**
	 * Get an instance of this class.
	 *
	 * @return GFGetResponse
	 */
	public static function get_instance() {
		
		if ( self::$_instance == null ) {
			self::$_instance = new self;
		}

		return self::$_instance;
		
	}


	// # FEED PROCESSING -----------------------------------------------------------------------------------------------

	/**
	 * Process the feed, subscribe the user to the list.
	 *
	 * @param array $feed The feed object to be processed.
	 * @param array $entry The entry object currently being processed.
	 * @param array $form The form object currently being processed.
	 *
	 * @return void
	 */
	public function process_feed( $feed, $entry, $form ) {

		$this->log_debug( __METHOD__ . '(): Processing feed.' );

		/* If GetResponse instance is not initialized, exit. */
		if ( ! $this->initialize_api() ) {

			$this->log_debug( __METHOD__ . '(): Failed to set up the API.' );

			return;

		}

		/* Prepare new contact array */
		$contact = array(
			'name'          => $this->get_field_value( $form, $entry, $feed['meta']['fields_name'] ),
			'email'         => $this->get_field_value( $form, $entry, $feed['meta']['fields_email'] ),
			'custom_fields' => array()
		);

		/* If email address is empty or invalid, exit. */
		if ( GFCommon::is_invalid_or_empty_email( $contact['email'] ) ) {
			$this->log_error( __METHOD__ . '(): Aborting. Email address invalid.' );

			return;
		}

		/* Find any custom fields mapped and push them to the new contact array. */
		if ( ! empty( $feed['meta']['custom_fields'] ) ) {

			foreach ( $feed['meta']['custom_fields'] as $custom_field ) {

				/* If no field is paired to this key, skip field. */
				if ( rgblank( $custom_field['value'] ) ) {
					continue;
				}

				/* Get the field value. */
				$field_value = $this->get_field_value( $form, $entry, $custom_field['value'] );

				/* If this field is empty, skip field. */
				if ( rgblank( $field_value ) ) {
					continue;
				}

				/* Get the custom field name */
				if ( $custom_field['key'] == 'gf_custom' ) {

					$custom_field_name = trim( $custom_field['custom_key'] ); // Set shortcut name to custom key
					$custom_field_name = str_replace( ' ', '_', $custom_field_name ); // Remove all spaces
					$custom_field_name = preg_replace( '([^\w\d])', '', $custom_field_name ); // Strip all custom characters
					$custom_field_name = strtolower( $custom_field_name ); // Set to lowercase
					$custom_field_name = substr( $custom_field_name, 0, 32 );

				} else {

					$custom_field_name = $custom_field['key'];

				}

				/* Trim field value to max length. */
				$field_value = substr( $field_value, 0, 255 );

				$contact['custom_fields'][ $custom_field_name ] = $field_value;

			}

		}

		$this->log_debug( __METHOD__ . '(): Contact to be added => ' . print_r( $contact, true ) );

		/* Check if email address is already on this campaign list. */
		$this->log_debug( __METHOD__ . "(): Checking to see if {$contact['email']} is already on the list." );
		$email_in_campaign = get_object_vars( $this->api->getContactsByEmail( $contact['email'], array( $feed['meta']['campaign'] ), 'CONTAINS' ) );

		/* If email address is not in campaign, add. Otherwise, update. */
		if ( empty( $email_in_campaign ) ) {

			$add_contact_response = $this->api->addContact( $feed['meta']['campaign'], $contact['name'], $contact['email'], 'standard', 0, $contact['custom_fields'] );

			if ( is_null( $add_contact_response ) ) {

				$this->log_debug( __METHOD__ . "(): {$contact['email']} is on campaign list, but unconfirmed." );

				return;

			} else {

				$this->log_debug( __METHOD__ . "(): {$contact['email']} is not on campaign list; added info." );

				return;

			}

		} else {

			$this->log_debug( __METHOD__ . "(): {$contact['email']} is already on campaign list; updating info." );

			$contact_id = key( $email_in_campaign );

			if ( ! empty( $contact['name'] ) ) {

				$contact_name_response = $this->api->setContactName( $contact_id, $contact['name'] );

				if ( isset( $contact_name_response->updated ) ) {
					$this->log_debug( __METHOD__ . "(): Name for {$contact['email']} have been updated." );
				}

			}

			if ( ! empty( $contact['custom_fields'] ) ) {

				$contact_customs_response = $this->api->setContactCustoms( $contact_id, $contact['custom_fields'] );

				if ( isset( $contact_customs_response->updated ) ) {
					$this->log_debug( __METHOD__ . "(): Custom fields for {$contact['email']} have been updated." );
				}

			}

		}

	}


	// # ADMIN FUNCTIONS -----------------------------------------------------------------------------------------------

	/**
	 * Plugin starting point. Handles hooks, loading of language files and PayPal delayed payment support.
	 */
	public function init() {

		parent::init();

		$this->add_delayed_payment_support(
			array(
				'option_label' => esc_html__( 'Subscribe contact to GetResponse only when payment is received.', 'gravityformsgetresponse' )
			)
		);

	}

	// ------- Plugin settings -------

	/**
	 * Configures the settings which should be rendered on the add-on settings tab.
	 *
	 * @return array
	 */
	public function plugin_settings_fields() {
		
		return array(
			array(
				'title'       => esc_html__( 'GetResponse Account Information', 'gravityformsgetresponse' ),
				'description' => $this->plugin_settings_description(),
				'fields'      => array(
					array(
						'name'              => 'api_key',
						'label'             => esc_html__( 'GetResponse API Key', 'gravityformsgetresponse' ),
						'type'              => 'text',
						'class'             => 'medium',
						'feedback_callback' => array( $this, 'initialize_api' )
					),
					array(
						'type'              => 'save',
						'messages'          => array(
							'success' => esc_html__( 'GetResponse settings have been updated.', 'gravityformsgetresponse' )
						),
					),
				),
			),
		);
		
	}

	/**
	 * Prepare plugin settings description.
	 *
	 * @return string
	 */
	public function plugin_settings_description() {
		
		$description  = '<p>';
		$description .= sprintf(
			esc_html__( 'GetResponse makes it easy to send email newsletters to your customers, manage your subscriber lists, and track campaign performance. Use Gravity Forms to collect customer information and automatically add it to your GetResponse subscriber list. If you don\'t have a GetResponse account, you can %1$s sign up for one here.%2$s', 'gravityformsgetresponse' ),
			'<a href="http://www.getresponse.com/" target="_blank">', '</a>'
		);
		$description .= '</p>';
		
		if ( ! $this->initialize_api() ) {
			
			$description .= '<p>';
			$description .= sprintf(
				esc_html__( 'Gravity Forms GetResponse Add-On requires your GetResponse API key, which can be found in the %1$sGetResponse API tab%2$s under your account details.', 'gravityformsgetresponse' ),
				'<a href="https://app.getresponse.com/account.html#api" target="_blank">', '</a>'
			);
			
			$description .= '</p>';
			
		}
				
		return $description;
		
	}

	// ------- Feed page -------

	/**
	 * Set feed creation control.
	 *
	 * @access public
	 * @return bool
	 */
	public function can_create_feed() {

		return $this->initialize_api();

	}

	/**
	 * Enable feed duplication.
	 * 
	 * @access public
	 * @param  int|array $feed_id The ID of the feed to be duplicated or the feed object when duplicating a form.
	 * @return bool
	 */
	public function can_duplicate_feed( $feed_id ) {
		
		return true;
		
	}

	/**
	 * Configures which columns should be displayed on the feed list page.
	 *
	 * @return array
	 */
	public function feed_list_columns() {

		return array(
			'feed_name' => esc_html__( 'Name', 'gravityformsgetresponse' ),
			'campaign'  => esc_html__( 'GetResponse Campaign', 'gravityformsgetresponse' )
		);

	}

	/**
	 * Returns the value to be displayed in the campaign name column.
	 *
	 * @param array $feed The feed being included in the feed list.
	 *
	 * @return string
	 */
	public function get_column_value_campaign( $feed ) {

		/* If GetResponse instance is not initialized, return campaign ID. */
		if ( ! $this->initialize_api() ) {
			return $feed['meta']['campaign'];
		}

		/* Get campaign and return name */
		$campaign = $this->api->getCampaignByID( $feed['meta']['campaign'] );

		return isset( $campaign->{$feed['meta']['campaign']} ) ? $campaign->{$feed['meta']['campaign']}->name : $feed['meta']['campaign'];

	}

	/**
	 * Configures the settings which should be rendered on the feed edit page.
	 *
	 * @return array The feed settings.
	 */
	public function feed_settings_fields() {	        

		$settings = array(
			array(
				'title' =>	'',
				'fields' =>	array(
					array(
						'name'           => 'feed_name',
						'label'          => esc_html__( 'Name', 'gravityformsgetresponse' ),
						'type'           => 'text',
						'required'       => true,
						'tooltip'        => '<h6>'. esc_html__( 'Name', 'gravityformsgetresponse' ) .'</h6>' . esc_html__( 'Enter a feed name to uniquely identify this setup.', 'gravityformsgetresponse' )
					),
					array(
						'name'           => 'campaign',
						'label'          => esc_html__( 'GetResponse Campaign', 'gravityformsgetresponse' ),
						'type'           => 'select',
						'required'       => true,
						'choices'        => $this->campaigns_for_feed_setting(),
						'tooltip'        => '<h6>'. esc_html__( 'GetResponse Campaign', 'gravityformsgetresponse' ) .'</h6>' . esc_html__( 'Select which GetResponse campaign this feed will add contacts to.', 'gravityformsgetresponse' )
					),
					array(
						'name'           => 'fields',
						'label'          => esc_html__( 'Map Fields', 'gravityformsgetresponse' ),
						'type'           => 'field_map',
						'field_map'      => $this->fields_for_feed_mapping(),
						'tooltip'        => '<h6>'. esc_html__( 'Map Fields', 'gravityformsgetresponse' ) .'</h6>' . esc_html__( 'Select which Gravity Form fields pair with their respective GetResponse field.', 'gravityformsgetresponse' )
					),
					array(
						'name'           => 'custom_fields',
						'label'          => esc_html__( 'Custom Fields', 'gravityformsgetresponse' ),
						'type'           => 'dynamic_field_map',
						'field_map'      => $this->custom_fields_for_feed_mapping(),
						'tooltip'        => '<h6>'. esc_html__( 'Custom Fields', 'gravityformsgetresponse' ) .'</h6>' . esc_html__( 'Select or create a new custom GetResponse field to pair with Gravity Forms fields. Custom field names can only contain up to 32 lowercase alphanumeric characters and underscores.', 'gravityformsgetresponse' )
					),
					array(
						'name'           => 'feed_condition',
						'label'          => esc_html__( 'Conditional Logic', 'gravityformsgetresponse' ),
						'type'           => 'feed_condition',
						'checkbox_label' => esc_html__( 'Enable', 'gravityformsgetresponse' ),
						'instructions'   => esc_html__( 'Export to GetResponse if', 'gravityformsgetresponse' ),
						'tooltip'        => '<h6>'. esc_html__( 'Conditional Logic', 'gravityformsgetresponse' ) .'</h6>' . esc_html__( 'When conditional logic is enabled, form submissions will only be exported to GetResponse when the condition is met. When disabled, all form submissions will be exported.', 'gravityformsgetresponse' )

					)
				)
			)
		);

		return $settings;
	
	}


	// # HELPERS -------------------------------------------------------------------------------------------------------

	/**
	 * Prepare campaigns for feed field.
	 *
	 * @return array
	 */
	public function campaigns_for_feed_setting() {

		/* If GetResponse API instance is not initialized, return an empty array. */
		if ( ! $this->initialize_api() ) {
			return array();
		}

		/* Setup choices array */
		$choices = array();

		/* Get the campaigns */
		$campaigns = $this->api->getCampaigns();

		/* Add campaigns to the choices array */
		if ( ! empty( $campaigns ) ) {

			foreach ( $campaigns as $campaign_id => $campaign ) {

				$choices[] = array(
					'label' => $campaign->name,
					'value' => $campaign_id
				);

			}

		}

		return $choices;

	}

	/**
	 * Prepare fields for feed field mapping.
	 *
	 * @return array
	 */
	public function fields_for_feed_mapping() {
		
		/* Setup initial field map */
		$field_map = array(
			array(	
				'name'       => 'name',
				'label'      => esc_html__( 'Name', 'gravityformsgetresponse' ),
				'required'   => false
			),
			array(	
				'name'       => 'email',
				'label'      => esc_html__( 'Email Address', 'gravityformsgetresponse' ),
				'required'   => true,
				'field_type' => array( 'email' )
			)
		);
		
		return $field_map;
		
	}

	/**
	 * Prepare custom fields for feed field mapping.
	 *
	 * @return array
	 */
	public function custom_fields_for_feed_mapping() {

		/* Setup initial field map */
		$field_map = array();

		/* If GetResponse instance is not initialized, return initial field map. */
		if ( ! $this->initialize_api() ) {
			return $field_map;
		}

		/* Get GetResponse account's custom fields and add to field map array */
		$custom_fields = $this->get_custom_fields();

		/* If custom fields exist, add them to the field map. */
		if ( ! empty( $custom_fields ) ) {

			foreach ( $custom_fields as $custom_field ) {

				$field_map[] = array(
					'name'  => 'custom_' . $custom_field->name,
					'label' => $custom_field->name,
				);

			}

		}

		return $field_map;

	}
		
	/**
	 * Checks validity of GetResponse API key and initializes API if valid.
	 *
	 * @return bool|null
	 */
	public function initialize_api() {

		if ( ! is_null( $this->api ) ) {
			return true;
		}

		/* Load the GetResponse API library. Class has been modified to make some functions public. */
		if ( ! class_exists( 'GetResponse' ) ) {
			require_once 'api/GetResponseAPI.class.php';
		}
		
		/* Get the plugin settings */
		$settings = $this->get_plugin_settings();
		
		/* If the API key is empty, return null. */
		if ( rgblank( $settings['api_key'] ) ) {
			return null;
		}
			
		$this->log_debug( __METHOD__ . "(): Validating login for API Info for key {$settings['api_key']}." );

		$getresponse = new GetResponse( $settings['api_key'] );
		
		/* Run authentication test request. */
		if ( $getresponse->ping() == 'pong' ) {
			
			/* Log that test passed. */
			$this->log_debug( __METHOD__ . '(): API Key is valid.' );
			
			/* Assign GetResponse object to the class. */
			$this->api = $getresponse;
			
			return true;
			
		} else {
			
			$this->log_error( __METHOD__ . '(): Invalid API Key.' );
			return false;			
			
		}
					
	}
	
	/**
	 * GetResponse: Get Custom Fields.
	 *
	 * @return array
	 */
	public function get_custom_fields() {
			
		/* If GetResponse instance is not initialized, return an empty array. */
		if ( ! $this->initialize_api() ) {
			return array();
		}
		
		return $this->api->execute( $this->api->prepRequest( 'get_account_customs' ) );
		
	}
	
}