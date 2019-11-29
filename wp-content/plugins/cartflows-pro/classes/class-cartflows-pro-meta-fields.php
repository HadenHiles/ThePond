<?php
/**
 * Meta Fields.
 *
 * @package CartFlows
 */

/**
 * Class Cartflows_Pro_Meta_Fields.
 */
class Cartflows_Pro_Meta_Fields {

	/**
	 * Instance
	 *
	 * @var $instance
	 */
	private static $instance;

	/**
	 * Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * Constructor
	 */
	public function __construct() {

		add_action( 'wp_ajax_wcf_pro_add_custom_checkout_field', array( $this, 'add_pro_checkout_custom_field' ) );

		add_action( 'wp_ajax_wcf_pro_delete_custom_checkout_field', array( $this, 'delete_checkout_custom_field' ) );

		add_filter( 'cartflows_admin_js_localize', array( $this, 'localize_vars' ) );

	}

	/**
	 * Localize variables in admin
	 *
	 * @param array $vars variables.
	 */
	function localize_vars( $vars ) {

		$ajax_actions = array(
			'wcf_pro_add_custom_checkout_field',
			'wcf_pro_delete_custom_checkout_field',
		);

		foreach ( $ajax_actions as $action ) {

			$vars[ $action . '_nonce' ] = wp_create_nonce( str_replace( '_', '-', $action ) );
		}

		return $vars;
	}


	/**
	 * Get Pro Checkout Field Repeater.
	 *
	 * @param array $field_data field data.
	 * @return string
	 */
	function get_pro_checkout_field_repeater( $field_data ) {

		$value = array();

		$value[0] = array(
			'add_to' => '',
			'type'   => '',
			'label'  => '',
			'name'   => '',
		);

		$field_content = '';

		$field_content .= '<div class="wcf-field-row">';
		// $field_content .= '<div class="wcf-field-row-heading">';
		// $field_content .= '<label>' . esc_html( $field_data['label'] ) . '</label>';
		// $field_content .= '</div>';
		$field_content .= '<div class="wcf-field-row-content">';
		$field_content .= '<div class="wcf-cpf-wrap">';

		foreach ( $value as $p_key => $p_data ) {
			$field_content .= '<div class="wcf-cpf-row" data-key="' . $p_key . '">';
			$field_content .= '<div class="wcf-cpf-row-header">';
			$field_content .= '<span class="wcf-cpf-row-title">Add New Custom Field</span>';
			$field_content .= '</div>';

			$field_content .= '<div class="wcf-cpf-row-standard-fields">';

			/* Add To */
			$field_content .= '<div class="wcf-cpf-fields wcf-cpf-add_to">';
			$field_content .= '<span class="wcf-cpf-row-setting-label">Add to</span>';
			$field_content .= '<span class="wcf-cpf-row-setting-field">';
			$field_content .= '<select name="wcf-checkout-custom-fields[' . $p_key . '][add_to]" class="wcf-cpf-add_to">';
			$field_content .= '<option value="billing">Billing</option>';
			$field_content .= '<option value="shipping">Shipping</option>';
			$field_content .= '</select>';
			$field_content .= '</span>';
			$field_content .= '</div>';

			/* Type */
			$field_content .= '<div class="wcf-cpf-fields wcf-cpf-type">';
			$field_content .= '<span class="wcf-cpf-row-setting-label">Type</span>';
			$field_content .= '<span class="wcf-cpf-row-setting-field">';
			$field_content .= '<select name="wcf-checkout-custom-fields[' . $p_key . '][type]" class="wcf-cpf-type">';
			$field_content .= '<option value="text">Text</option>';
			$field_content .= '<option value="textarea">Textarea</option>';
			$field_content .= '<option value="select">Select</option>';
			$field_content .= '<option value="checkbox">Checkbox</option>';
			$field_content .= '<option value="hidden">Hidden</option>';
			$field_content .= '</select>';
			$field_content .= '</span>';
			$field_content .= '</div>';

			/* Label */
			$field_content .= '<div class="wcf-cpf-fields wcf-cpf-label">';
			$field_content .= '<span class="wcf-cpf-row-setting-label">Label <i>*</i></span>';
			$field_content .= '<span class="wcf-cpf-row-setting-field">';
			$field_content .= '<input type="text" value="" name="wcf-checkout-custom-fields[' . $p_key . '][label]" class="wcf-cpf-label">';
			$field_content .= '<span id="wcf-cpf-label-error-msg"></span>';
			$field_content .= '</span>';
			$field_content .= '</div>';

			/* Default */
			$field_content .= '<div class="wcf-cpf-fields wcf-cpf-default">';
			$field_content .= '<span class="wcf-cpf-row-setting-label">Default</span>';
			$field_content .= '<span class="wcf-cpf-row-setting-field">';
			$field_content .= '<input type="text" value="" name="wcf-checkout-custom-fields[' . $p_key . '][default]" class="wcf-cpf-default">';
			$field_content .= '<span id="wcf-cpf-default-error-msg"></span>';
			$field_content .= '</span>';
			$field_content .= '</div>';

			/* Placeholder */
			$field_content .= '<div class="wcf-cpf-fields wcf-cpf-placeholder">';
			$field_content .= '<span class="wcf-cpf-row-setting-label">Placeholder</span>';
			$field_content .= '<span class="wcf-cpf-row-setting-field">';
			$field_content .= '<input type="text" value="" name="wcf-checkout-custom-fields[' . $p_key . '][placeholder]" class="wcf-cpf-placeholder">';
			$field_content .= '<span id="wcf-cpf-placeholder-error-msg"></span>';
			$field_content .= '</span>';
			$field_content .= '</div>';

			/* Options */
			$field_content .= '<div class="wcf-cpf-fields wcf-cpf-options">';
			$field_content .= '<span class="wcf-cpf-row-setting-label">Options <i>*</i></span>';
			$field_content .= '<span class="wcf-cpf-row-setting-field">';
			$field_content .= '<textarea value="" name="wcf-checkout-custom-fields[' . $p_key . '][label]" class="wcf-cpf-options" placeholder="Enter your options separated by comma."></textarea>';
			$field_content .= '</span>';
			$field_content .= '</div>';

			/* Width */
			$field_content .= '<div class="wcf-cpf-fields wcf-cpf-width">';
			$field_content .= '<span class="wcf-cpf-row-setting-label">Width</span>';
			$field_content .= '<span class="wcf-cpf-row-setting-field">';
			$field_content .= '<select name="wcf-checkout-custom-fields[' . $p_key . '][width]" class="wcf-cpf-width">';
			$field_content .= '<option value="33">33%</option>';
			$field_content .= '<option value="50">50%</option>';
			$field_content .= '<option value="100" selected>100%</option>';
			$field_content .= '</select>';
			$field_content .= '</span>';
			$field_content .= '</div>';

			/* Required */
			$field_content .= '<div class="wcf-cpf-fields wcf-cpf-required">';
			$field_content .= '<span class="wcf-cpf-row-setting-label">Required</span>';
			$field_content .= '<span class="wcf-cpf-row-setting-field">';
			$field_content .= '<input type="hidden" value="no" name="wcf-checkout-custom-fields[' . $p_key . '][required]" class="wcf-cpf-required">';
			$field_content .= '<input type="checkbox" value="yes" name="wcf-checkout-custom-fields[' . $p_key . '][required]" class="wcf-cpf-required">';
			$field_content .= '<span id="wcf-cpf-required-error-msg"></span>';
			$field_content .= '</span>';
			$field_content .= '</div>';

			/* Optimized */
			$field_content .= '<div class="wcf-cpf-fields wcf-cpf-optimized">';
			$field_content .= '<span class="wcf-cpf-row-setting-label">Collapsible</span>';
			$field_content .= '<span class="wcf-cpf-row-setting-field">';
			$field_content .= '<input type="hidden" value="no" name="wcf-checkout-custom-fields[' . $p_key . '][optimized]" class="wcf-cpf-optimized">';
			$field_content .= '<input type="checkbox" value="yes" name="wcf-checkout-custom-fields[' . $p_key . '][optimized]" class="wcf-cpf-optimized">';
			$field_content .= '<span id="wcf-cpf-optimized-error-msg"></span>';
			$field_content .= '</span>';
			$field_content .= '</div>';

			$field_content .= '</div>';
			$field_content .= '</div>';
		}

		/* Add New Custom Field */
		$field_content .= '<div class="wcf-cpf-add-row">';
		$field_content .= '<div class="wcf-cpf-add-wrap">';
		$field_content .= '<button class="button button-secondary wcf-pro-custom-field-add" data-name="wcf-checkout-custom-fields">Add New Field</button>';
		$field_content .= '</div>';
		$field_content .= '</div>';
		/* End Add new custom field */

		$field_content .= '</div>';
		$field_content .= '</div>';
		$field_content .= '</div>';

		return $field_content;
	}

	/**
	 * [add_checkout_custom_field description]
	 *
	 * @hook wcf_add_checkout_custom_field
	 */
	public function add_pro_checkout_custom_field() {

		check_ajax_referer( 'wcf-pro-add-custom-checkout-field', 'security' );

		$post_id       = intval( $_POST['post_id'] );
		$add_to        = sanitize_text_field( wp_unslash( $_POST['add_to'] ) );
		$type          = sanitize_text_field( wp_unslash( $_POST['type'] ) );
		$options       = sanitize_text_field( wp_unslash( $_POST['options'] ) );
		$label         = sanitize_text_field( wp_unslash( $_POST['label'] ) );
		$name          = sanitize_text_field( wp_unslash( str_replace( ' ', '_', $_POST['label'] ) ) );
		$placeholder   = sanitize_text_field( wp_unslash( $_POST['placeholder'] ) );
		$optimized     = sanitize_text_field( wp_unslash( $_POST['optimized'] ) );
		$width         = sanitize_text_field( wp_unslash( $_POST['width'] ) );
		$default_value = sanitize_text_field( wp_unslash( $_POST['default'] ) );
		$is_required   = sanitize_text_field( wp_unslash( $_POST['required'] ) );

		$field_markup = '';

		if ( '' !== $name ) {

			$fields     = Cartflows_Helper::get_checkout_fields( $add_to, $post_id );
			$field_keys = array_keys( $fields );

			$name = $add_to . '_' . sanitize_key( $name );
			if ( in_array( $name, $field_keys ) ) {
				$name = $name . '_' . rand( 0000, 9999 );
			}

			$field_data = array(
				'type'        => $type,
				'label'       => $label,
				'placeholder' => $placeholder,
				'class'       => array( 'form-row-wide' ),
				'label_class' => array(),
				'required'    => $is_required,
				'custom'      => true,
				'default'     => $default_value,
				'options'     => $options,
				'optimized'   => $optimized,
			);

			if ( 'select' === $type ) {
				$options               = explode( ',', $options );
				$field_data['options'] = array_combine( $options, $options );

			}

			Cartflows_Helper::add_checkout_field( $add_to, $name, $field_data, $post_id );

			$key  = sanitize_key( $name );
			$name = 'wcf-' . $key;

			$field_args = array(
				'type'        => $type,
				'label'       => $label,
				'name'        => $name,
				'value'       => 'yes',
				'placeholder' => $placeholder,
				'width'       => $width,
				'after'       => 'Enable',
				'section'     => $add_to,
				'default'     => $default_value,
				'required'    => $is_required,
				'options'     => $options,
				'optimized'   => $optimized,
			);

			$field_args['after_html']  = '<span class="wcf-cpf-actions" data-type="billing" data-key="' . $key . '"> ';
			$field_args['after_html'] .= '<a class="wcf-pro-custom-field-remove wp-ui-text-notification">' . __( 'Remove', 'cartflows-pro' ) . '</a>';
			$field_args['after_html'] .= '</span>';

			// $field_markup = wcf()->meta->get_checkbox_field( $field_args );

			$field_markup .= "<li class='wcf-field-item-edit-inactive wcf-field-item ui-sortable-handle'>";
			$field_markup .= $this->get_field_html_via_ajax( $field_args, 'wcf_field_order_' . $add_to . '[' . $key . ']' );
			$field_markup .= '</li>';

			if ( 'billing' === $add_to ) {
				$add_to_class = 'billing-field-sortable';
				$section      = 'billing';
			} elseif ( 'shipping' === $add_to ) {
				$add_to_class = 'shipping-field-sortable';
				$section      = 'shipping';
			}

			$data = array(
				'field_data'   => $field_data,
				'field_args'   => $field_args,
				'add_to_class' => $add_to_class,
				'markup'       => $field_markup,
				'section'      => $section,
			);

			wp_send_json( $data );
		}

		wp_send_json( false );

	}

	/**
	 * Get field html via ajax.
	 *
	 * @param array  $field_args field args.
	 * @param string $field_key field key.
	 * @return false|string
	 */
	function get_field_html_via_ajax( $field_args, $field_key ) {

		$value = $field_args['value'];

		$is_checkbox = false;
		$is_require  = false;
		$is_select   = false;

		$display = 'none';

		$field_content = '';

		if ( isset( $field_args['before'] ) ) {
			$field_content .= '<span>' . $field_args['before'] . '</span>';
		}
		$field_content .= '<input type="hidden" name="' . $field_args['name'] . '" value="no">';
		$field_content .= '<input type="checkbox" name="' . $field_args['name'] . '" value="yes" ' . checked( 'yes', $value, false ) . '>';

		if ( isset( $field_args['after'] ) ) {
			$field_content .= $field_args['after'];
		}

		$type        = isset( $field_args['type'] ) ? $field_args['type'] : '';
		$label       = isset( $field_args['label'] ) ? $field_args['label'] : '';
		$help        = isset( $field_args['help'] ) ? $field_args['help'] : '';
		$after_html  = isset( $field_args['after_html'] ) ? $field_args['after_html'] : '';
		$name        = isset( $field_args['name'] ) ? $field_args['name'] : '';
		$default     = isset( $field_args['default'] ) ? $field_args['default'] : '';
		$required    = isset( $field_args['required'] ) ? $field_args['required'] : '';
		$options     = isset( $field_args['options'] ) ? $field_args['options'] : '';
		$width       = isset( $field_args['width'] ) ? $field_args['width'] : '';
		$placeholder = isset( $field_args['placeholder'] ) ? $field_args['placeholder'] : '';
		$optimized   = isset( $field_args['optimized'] ) ? $field_args['optimized'] : '';
		$name_class  = 'field-' . $field_args['name'];

		if ( isset( $options ) && ! empty( $options ) ) {
			$options = implode( ',', $options );
		} else {
			$options = '';
		}

		if ( 'yes' == $required ) {
			$is_require = true;
		}

		if ( 'checkbox' == $type ) {
			$is_checkbox = true;
		}

		if ( 'select' == $type ) {
			$is_select = true;
			$display   = 'block';
		}

		// $field_markup = wcf()->meta->get_only_checkbox_field( $field_args );
		ob_start();

		?>
		<div class="wcf-field-item-bar">
			<div class="wcf-field-item-handle ui-sortable-handle">
				<label class="dashicons 
				<?php
				if ( 'no' == $value ) {
					echo 'dashicons-hidden';
				} else {
					echo 'dashicons-visibility';}
				?>
				" for="<?php echo $field_args['name']; ?>"></label>
				<span class="item-title">
					<span class="wcf-field-item-title">
					<?php
					echo $label; if ( $is_require ) {
						?>
					<i>*</i> <?php } ?></span>
					<span class="is-submenu" style="display: none;">sub item</span>
				</span>
				<span class="item-controls">
					<span class="dashicons dashicons-menu"></span>
					<span class="item-order hide-if-js">
						<a href="#" class="item-move-up" aria-label="Move up">↑</a>
						|
						<a href="#" class="item-move-down" aria-label="Move down">↓</a>
					</span>
					<a class="item-edit" id="edit-64" href="javascript:void(0);" aria-label="My account. Menu item 1 of 5."><span class="screen-reader-text">Edit</span></a>
				</span>
			</div>
		</div>
		<div class="wcf-field-item-settings">
			<div class="wcf-field-item-settings-row-width">
				<?php
				echo wcf()->meta->get_select_field(
					array(
						'label'   => __( 'Field Width', 'cartflows-pro' ),
						'name'    => $field_key . '[width]',
						'value'   => $width,
						'options' => array(
							'33'  => __( '33%', 'cartflows-pro' ),
							'50'  => __( '50%', 'cartflows-pro' ),
							'100' => __( '100%', 'cartflows-pro' ),
						),
					)
				);
				?>
			</div>

			<div class="wcf-field-item-settings-label">
				<?php
				echo wcf()->meta->get_text_field(
					array(
						'label' => __( 'Field Label', 'cartflows-pro' ),
						'name'  => $field_key . '[label]',
						'value' => $label,
					)
				);

				?>
				<input type="hidden" name="wcf_field_order_<?php echo $field_args['section']; ?>[]" value="<?php echo str_replace( 'wcf-', '', $field_args['name'] ); ?>">
			</div>

			<div class="wcf-field-item-select-options" style="display:
			<?php
			if ( isset( $display ) ) {
				print $display; }
			?>
			;" >
				<?php
				echo wcf()->meta->get_text_field(
					array(
						'label' => __( 'Options', 'cartflows-pro' ),
						'name'  => $field_key . '[options]',
						'value' => $options,
					)
				);

				?>
				<input type="hidden" name="wcf_field_order_<?php echo $field_args['section']; ?>[]" value="<?php echo str_replace( 'wcf-', '', $field_args['name'] ); ?>">
			</div>

			<div class="wcf-field-item-settings-default">
				<?php
				if ( true == $is_checkbox ) {
					echo wcf()->meta->get_select_field(
						array(
							'label'   => __( 'Default', 'cartflows-pro' ),
							'name'    => $field_key . '[default]',
							'value'   => $default,
							'options' => array(
								'1' => __( 'Checked', 'cartflows-pro' ),
								'0' => __( 'Un-Checked', 'cartflows-pro' ),
							),
						)
					);
				} else {

					echo wcf()->meta->get_text_field(
						array(
							'label' => __( 'Default', 'cartflows-pro' ),
							'name'  => $field_key . '[default]',
							'value' => $default,
						)
					);
				}
				?>
			</div>

			<div class="wcf-field-item-settings-placeholder" 
			<?php
			if ( true == $is_checkbox ) {
				?>
			style="display: none;" <?php } ?> >
				<?php
				echo wcf()->meta->get_text_field(
					array(
						'label' => __( 'Placeholder', 'cartflows-pro' ),
						'name'  => $field_key . '[placeholder]',
						'value' => $placeholder,
					)
				);
				?>
			</div>

			<div class="wcf-field-item-settings-required">
				<?php
				echo wcf()->meta->get_checkbox_field(
					array(
						'label' => __( 'Required', 'cartflows-pro' ),
						'name'  => $field_key . '[required]',
						'value' => $required,
					)
				);
				?>
			</div>

			<div class="wcf-field-item-settings-optimized">
				<?php
				echo wcf()->meta->get_checkbox_field(
					array(
						'label' => __( 'Collapsible', 'cartflows-pro' ),
						'name'  => $field_key . '[optimized]',
						'value' => $optimized,
					)
				);
				?>
			</div>

			<div class="wcf-field-item-settings-checkbox">
				<?php
				echo wcf()->meta->get_checkbox_field(
					array(
						'label' => __( 'Enable this field', 'cartflows-pro' ),
						'name'  => $field_key . '[enabled]',
						'value' => $value,
					)
				);
				?>
			</div>

			<?php
			if ( isset( $field_args['after_html'] ) ) {
				?>
				<div class="wcf-field-item-settings-row-delete-cf">
					<?php echo $field_args['after_html']; ?>
				</div>
				<?php
			}
			?>
			<!--
			<label for="<?php echo $field_args['name']; ?>">
				<?php _e( 'Label', 'cartflows-pro' ); ?><br>
				<input type="text" value="<?php echo $field_args['label']; ?>">

				<?php
				if ( isset( $field_markup ) ) {
					echo $field_markup; }
				?>

				<input type="hidden" name="wcf_field_order_<?php echo $field_args['section']; ?>[]" value="<?php echo str_replace( 'wcf-', '', $field_args['name'] ); ?>">
			</label> -->
		</div>

		<?php

		return ob_get_clean();
	}


	/**
	 * Delete checkout custom fields.
	 */
	public function delete_checkout_custom_field() {

		check_ajax_referer( 'wcf-pro-delete-custom-checkout-field', 'security' );

		$post_id = intval( $_POST['post_id'] );
		$type    = sanitize_text_field( wp_unslash( $_POST['type'] ) );
		$key     = sanitize_text_field( wp_unslash( $_POST['key'] ) );

		if ( '' !== $key ) {

			Cartflows_Helper::delete_checkout_field( $type, $key, $post_id );

			wp_send_json( true );

		}

		wp_send_json( false );

	}

	/**
	 * Get optgroup fields.
	 *
	 * @param array $field_data field data.
	 * @return mixed
	 */
	public function get_optgroup_field( $field_data ) {

		$saved_value = $field_data['value'];
		$flow_id     = $field_data['data-flow-id'];
		$exclude_id  = $field_data['data-exclude-id'];

		if ( is_array( $field_data['optgroup'] ) && ! empty( $field_data['optgroup'] ) ) {

			$field_content        = '<select name="' . $field_data['name'] . '">';
			$cartflows_steps_args = array(
				'posts_per_page' => -1,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'post_type'      => 'cartflows_step',
				'post_status'    => 'publish',
				'post__not_in'   => array( $exclude_id ),
				// 'fields'           => 'ids',
			);
			$field_content .= '<option class="wcf_steps_option" value="" ' . selected( $saved_value, '', false ) . ' >Default</option>';
			foreach ( $field_data['optgroup'] as $optgroup_key => $optgroup_value ) {
				$cartflows_steps_args['tax_query'] = array(
					'relation' => 'AND',
					array(
						'taxonomy' => 'cartflows_step_type',
						'field'    => 'slug',
						'terms'    => $optgroup_key,
					),
					array(
						'taxonomy' => 'cartflows_step_flow',
						'field'    => 'slug',
						'terms'    => 'flow-' . $flow_id,

					),
				);
				$cartflows_steps_query = new WP_Query( $cartflows_steps_args );
				$cartflows_steps       = $cartflows_steps_query->posts;

				if ( ! empty( $cartflows_steps ) ) {

					$field_content .= '<optgroup label="' . $optgroup_value . '"></optgroup>';
					foreach ( $cartflows_steps as $key => $value ) {
						$field_content .= '<option class="wcf_steps_option" value="' . esc_attr( $value->ID ) . '" ' . selected( $saved_value, $value->ID, false ) . ' >&emsp;' . esc_attr( $value->post_title ) . '</option>';
					}
					$field_content .= '</optgroup>';
				}
			}
		}

		$field_content .= '</select>';

		return wcf()->meta->get_field( $field_data, $field_content );
	}

}
