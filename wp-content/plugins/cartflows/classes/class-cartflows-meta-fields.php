<?php
// @codingStandardsIgnoreStart
/**
 * Meta Fields.
 *
 * @package CartFlows
 */

/**
 * Class Cartflows_Meta_Fields.
 */
class Cartflows_Meta_Fields {

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
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {

		/* Add Scripts */
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_meta_scripts' ), 20 );

		add_action( 'wp_ajax_wcf_json_search_coupons', array( $this, 'json_search_coupons' ) );

		add_action( 'wp_ajax_wcf_add_checkout_custom_field', array( $this, 'add_checkout_custom_field' ) );

		add_action( 'wp_ajax_wcf_pro_add_checkout_custom_field', array( $this, 'add_pro_checkout_custom_field' ) );

		add_action( 'wp_ajax_wcf_delete_checkout_custom_field', array( $this, 'delete_checkout_custom_field' ) );

		add_action( 'wp_ajax_wcf_json_search_pages', array( $this, 'json_search_pages' ) );

		add_filter( 'cartflows_admin_js_localize', array( $this, 'localize_vars' ) );
	}

	public function admin_meta_scripts() {

		global $pagenow;
		global $post;

		$screen = get_current_screen();

		if (
			( 'post-new.php' == $pagenow || 'post.php' == $pagenow ) &&
			wcf()->utils->is_step_post_type( $screen->post_type )
		) {

			wp_enqueue_style( 'woocommerce_admin_styles' );

			wp_enqueue_script( 'select2' );
			wp_enqueue_script( 'wc-enhanced-select' );

			wp_enqueue_script(
				'wcf-admin-meta',
				CARTFLOWS_URL . 'admin/meta-assets/js/admin-edit.js',
				array( 'jquery', 'wp-color-picker' ),
				CARTFLOWS_VER,
				true
			);

			wp_enqueue_style( 'wcf-admin-meta', CARTFLOWS_URL . 'admin/meta-assets/css/admin-edit.css', array( 'wp-color-picker' ), CARTFLOWS_VER );
			wp_style_add_data( 'wcf-admin-meta', 'rtl', 'replace' );

			$localize = array(
				'ajax_url'     => admin_url( 'admin-ajax.php' ),
				'google_fonts' => CartFlows_Font_Families::get_google_fonts(),
				'system_fonts' => CartFlows_Font_Families::get_system_fonts(),
				'font_weights' => array(
					'100' => __( 'Thin 100', 'cartflows' ),
					'200' => __( 'Extra-Light 200', 'cartflows' ),
					'300' => __( 'Light 300', 'cartflows' ),
					'400' => __( 'Normal 400', 'cartflows' ),
					'500' => __( 'Medium 500', 'cartflows' ),
					'600' => __( 'Semi-Bold 600', 'cartflows' ),
					'700' => __( 'Bold 700', 'cartflows' ),
					'800' => __( 'Extra-Bold 800', 'cartflows' ),
					'900' => __( 'Ultra-Bold 900', 'cartflows' ),
				)
			);

			wp_localize_script( 'jquery', 'wcf', apply_filters( 'wcf_js_localize', $localize ) );

			do_action( 'cartflows_admin_meta_scripts' );
		}
	}

	/**
	 * Function to search coupons
	 */
	public function json_search_coupons() {

		check_admin_referer( 'wcf-json-search-coupons', 'security' );

		global $wpdb;

		$term = (string) urldecode( sanitize_text_field( wp_unslash( $_GET['term'] ) ) ); // phpcs:ignore

		if ( empty( $term ) ) {
			die();
		}

		$posts = wp_cache_get( 'wcf_search_coupons', 'wcf_funnel_Cart' );

		if ( false === $posts ) {
			$posts = $wpdb->get_results( // phpcs:ignore
						$wpdb->prepare(
							"SELECT *
								FROM {$wpdb->prefix}posts
								WHERE post_type = %s
								AND post_title LIKE %s
								AND post_status = %s",
							'shop_coupon',
							$wpdb->esc_like( $term ) . '%',
							'publish'
						)
			);
			wp_cache_set( 'wcf_search_coupons', $posts, 'wcf_funnel_Cart' );
		}

		$coupons_found      = array();
		$all_discount_types = wc_get_coupon_types();

		if ( $posts ) {
			foreach ( $posts as $post ) {

				$discount_type = get_post_meta( $post->ID, 'discount_type', true );

				if ( ! empty( $all_discount_types[ $discount_type ] ) ) {
					$coupons_found[ get_the_title( $post->ID ) ] = get_the_title( $post->ID ) . ' (Type: ' . $all_discount_types[ $discount_type ] . ')';
				}
			}
		}

		wp_send_json( $coupons_found );
	}

	/**
	 * [add_checkout_custom_field description]
	 *
	 * @hook wcf_add_checkout_custom_field
	 */
	public function add_checkout_custom_field() {

		check_ajax_referer( 'wcf-add-checkout-custom-field', 'security' );

		$post_id = intval( $_POST['post_id'] );
		$add_to  = sanitize_text_field( wp_unslash( $_POST['add_to'] ) );
		$type    = sanitize_text_field( wp_unslash( $_POST['type'] ) );
		$options = sanitize_text_field( wp_unslash( $_POST['options'] ) );
		$label   = sanitize_text_field( wp_unslash( $_POST['label'] ) );
		$name    = sanitize_text_field( wp_unslash( $_POST['name'] ) );

		if ( '' !== $name ) {

			$fields     = Cartflows_Helper::get_checkout_fields( $add_to, $post_id );
			$field_keys = array_keys($fields);

			$name = $add_to . '_' . sanitize_key( $name );
			if( in_array($name, $field_keys) ) {
				$name = $name . '_' . rand( 0000, 9999 );
			}

			$field_data = array(
				'type'        => $type,
				'label'       => $label,
				'placeholder' => '',
				'class'       => array( 'form-row-wide' ),
				'label_class' => array(),
				'required'    => true,
				'custom'      => true,
			);

			if ( 'select' === $type ) {
				
				$options 				= explode( ',', $options );
				$field_data['options'] 	= array();

				if ( is_array( $options ) && ! empty( $options ) ) {

					foreach ( $options as $key => $value ) {
						
						$field_data['options'][ $value ] = $value;
					}
				}
			}

			Cartflows_Helper::add_checkout_field( $add_to, $name, $field_data, $post_id );

			$key  = sanitize_key( $name );
			$name = 'wcf-' . $key;

			$field_args = array(
				'label' => $label,
				'name'  => $name,
				'value' => 'yes',
				'after' => 'Enable',
			);

			$field_args['after_html']  = '<span class="wcf-cpf-actions" data-type="billing" data-key="' . $key . '"> | ';
			$field_args['after_html'] .= '<a class="wcf-cpf-action-remove">' . __( 'Remove', 'cartflows' ) . '</a>';
			$field_args['after_html'] .= '</span>';

			$field_markup = wcf()->meta->get_checkbox_field( $field_args );

			if( 'billing' === $add_to ) {
				$add_to_class = 'wcf-cb-fields';
			} else if( 'shipping' === $add_to ) {
				$add_to_class = 'wcf-sb-fields';
			}

			$data = array(
				'field_data'   => $field_data,
				'field_args'   => $field_args,
				'add_to_class' => $add_to_class,
				'markup'       => $field_markup,
			);

			wp_send_json( $data );
		}

		wp_send_json( false );

	}

	/**
	 * [add_checkout_custom_field description]
	 *
	 * @hook wcf_add_checkout_custom_field
	 */
	public function add_pro_checkout_custom_field() {

		check_ajax_referer( 'wcf-pro-add-checkout-custom-field', 'security' );

		$post_id 		= intval( $_POST['post_id'] );
		$add_to  		= sanitize_text_field( wp_unslash( $_POST['add_to'] ) );
		$type    		= sanitize_text_field( wp_unslash( $_POST['type'] ) );
		$options 		= sanitize_text_field( wp_unslash( $_POST['options'] ) );
		$label   		= sanitize_text_field( wp_unslash( $_POST['label'] ) );
		$name    		= sanitize_text_field( wp_unslash( str_replace(' ', '_', $_POST['label'] ) ) );
		$placeholder    = sanitize_text_field( wp_unslash( $_POST['placeholder'] ) );
		$width    		= sanitize_text_field( wp_unslash( $_POST['width'] ) );
		$default_value	= sanitize_text_field( wp_unslash( $_POST['default'] ) );
		$is_required	= sanitize_text_field( wp_unslash( $_POST['required'] ) );

		$field_markup = '';

		if ( '' !== $name ) {

			$fields     = Cartflows_Helper::get_checkout_fields( $add_to, $post_id );
			$field_keys = array_keys($fields);

			$name = $add_to . '_' . sanitize_key( $name );
			if( in_array($name, $field_keys) ) {
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
				'default'	  => $default_value,
				'options'	  => $options,
			);

			if ( 'select' === $type ) {
				
				$options 				= explode( ',', $options );
				$field_data['options'] 	= array();

				if ( is_array( $options ) && ! empty( $options ) ) {

					foreach ( $options as $key => $value ) {
						
						$field_data['options'][ $value ] = $value;
					}
				}
			}

			$width_args = array(
				'wcf-field-width_'.$name => $width,
			);

			Cartflows_Helper::add_checkout_field( $add_to, $name, $field_data, $post_id );
			Cartflows_Helper::save_meta_option( $post_id, $width_args );
			
			$key  = sanitize_key( $name );
			$name = 'wcf-' . $key;

			$field_args = array(
				'type'		  => $type,
				'label' 	  => $label,
				'name'  	  => $name,
				'value' 	  => 'yes',
				'placeholder' => $placeholder,
				'width'		  => $width,
				'after' 	  => 'Enable',
				'section' 	  => $add_to,
				'default' 	  => $default_value,
				'required'    => $is_required,
				'options'	  => $options,
			);

			$field_args['after_html']  = '<span class="wcf-cpf-actions" data-type="billing" data-key="' . $key . '"> ';
			$field_args['after_html'] .= '<a class="wcf-cpf-action-remove wp-ui-text-notification">'. __( 'Remove', 'cartflows' ).'</a>';
			$field_args['after_html'] .= '</span>';

			// $field_markup = wcf()->meta->get_checkbox_field( $field_args );

			$field_markup .= "<li class='wcf-field-item-edit-inactive wcf-field-item ui-sortable-handle'>";
			$field_markup .= $this->get_field_html_via_ajax($field_args);
			$field_markup .= "</li>";

			if( 'billing' === $add_to ) {
				$add_to_class = 'billing-field-sortable';
				$section      = 'billing';
			} else if( 'shipping' === $add_to ) {
				$add_to_class = 'shipping-field-sortable';
				$section      = 'shipping';
			}

			$data = array(
				'field_data'   => $field_data,
				'field_args'   => $field_args,
				'add_to_class' => $add_to_class,
				'markup'       => $field_markup,
				'section'	   => $section,
			);

			wp_send_json( $data );
		}

		wp_send_json( false );

	}

	/**
	 * Get field html.
	 *
	 * @param array $args field arguments.
	 * @return string
	 */
	function get_field_html_via_ajax( $field_args ) {

		$value = $field_args['value'];

		$is_checkbox = false;
		$is_require  = false;
		$is_select	 = false;

		$display 	 = 'none';

		$field_content = '';

		if ( isset( $field_args['before'] ) ) {
			$field_content .= '<span>' . $field_args['before'] . '</span>';
		}
		$field_content .= '<input type="hidden" name="' . $field_args['name'] . '" value="no">';
		$field_content .= '<input type="checkbox" name="' . $field_args['name'] . '" value="yes" ' . checked( 'yes', $value, false ) . '>';

		if ( isset( $field_args['after'] ) ) {
			$field_content .=  $field_args['after'];
		}

		$type      = isset( $field_args['type'] ) ? $field_args['type'] : '';
		$label      = isset( $field_args['label'] ) ? $field_args['label'] : '';
		$help       = isset( $field_args['help'] ) ? $field_args['help'] : '';
		$after_html = isset( $field_args['after_html'] ) ? $field_args['after_html'] : '';
		$name 		= isset( $field_args['name'] ) ? $field_args['name'] : '';
		$default 	= isset( $field_args['default']) ? $field_args['default'] : '';
		$required 	= isset( $field_args['required']) ? $field_args['required'] : '';
		$options 	= isset( $field_args['options']) ? $field_args['options'] : '';
		$width 		= isset( $field_args['width']) ? $field_args['width'] : '';
		$name_class = 'field-' . $field_args['name'];

		if( isset( $options ) && !empty( $options ) ){
			$options = implode(', ', $options );
		}else{
			$options = '';
		}

		if( 'yes' == $required ){
			$is_require = true;
		}

		if( 'checkbox' == $type ){
			$is_checkbox = true;
		}

		if( 'select' == $type ){
			$is_select = true;
			$display   = 'block';
		}

		// echo "<pre>";
		// var_dump($after_html);
		// echo "</pre>";

		// $field_markup = wcf()->meta->get_only_checkbox_field( $field_args );
		ob_start();

		?>
		<div class="wcf-field-item-bar">
			<div class="wcf-field-item-handle ui-sortable-handle">
				<label class="dashicons <?php if( 'no' == $value ){ echo 'dashicons-hidden'; } else{ echo 'dashicons-visibility';} ?> " for="<?php echo $field_args['name']; ?>"></label>
				<span class="item-title">
					<span class="wcf-field-item-title"><?php echo $label; if( $is_require ) { ?>  <i>*</i> <?php } ?></span>
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
							'label'   => __( 'Field Width', 'cartflows' ),
							'name'    => 'wcf-field-width_' . str_replace( 'wcf-', '', $field_args['name'] ),
							'value'   => $width,
							'options' => array(
								'33'  => __( '33%', 'cartflows' ),
								'50'  => __( '50%', 'cartflows' ),
								'100' => __( '100%', 'cartflows' ),
							),
						)
					);
				?>
			</div>
			
			<div class="wcf-field-item-settings-label">
				<?php 
					echo wcf()->meta->get_text_field(
						array(
							'label' => __( 'Field Label', 'cartflows' ),
							'name'  => 'wcf_label_text_field_' . $field_args['section'] . '[' . str_replace( 'wcf-', '', $field_args['name'] ) . ']',
							'value' => $label,
						)
					);

				?>
				<input type="hidden" name="wcf_field_order_<?php echo $field_args['section']; ?>[]" value="<?php echo str_replace( 'wcf-', '', $field_args['name'] ); ?>"> 
			</div>

			<div class="wcf-field-item-select-options" style="display:<?php if( isset( $display ) ){ print $display; } ?>;" >
				<?php 
					echo wcf()->meta->get_text_field(
						array(
							'label' => __( 'Options', 'cartflows' ),
							'name'  => 'wcf_select_option_field_' . $field_args['section'] . '[' . str_replace( 'wcf-', '', $field_args['name'] ) . ']',
							'value' => $options,
						)
					);

				?>
				<input type="hidden" name="wcf_field_order_<?php echo $field_args['section']; ?>[]" value="<?php echo str_replace( 'wcf-', '', $field_args['name'] ); ?>"> 
			</div>

			<div class="wcf-field-item-settings-default">
				<?php
					if( true == $is_checkbox ){
					echo wcf()->meta->get_select_field(
					array(
					'label'   => __( 'Default', 'cartflows' ),
					'name'    => 'wcf_label_default_field_' . $field_args['section'] . '[' . str_replace( 'wcf-', '', $field_args['name'] ) . ']',
					'value'   => $value,
					'options' => array(
					'1'  => __( 'Checked', 'cartflows' ),
					'0'  => __( 'Un-Checked', 'cartflows' ),
					),
					)
				);
					}else{

					echo wcf()->meta->get_text_field(
					array(
					'label' => __( 'Default', 'cartflows' ),
					'name'  => 'wcf_label_default_field_' . $field_args['section'] . '[' . str_replace( 'wcf-', '', $field_args['name'] ) . ']',
					'value' => $default,
					)
					);
					}
				?>
			</div>

			<div class="wcf-field-item-settings-placeholder" <?php if( true == $is_checkbox ) {?> style="display: none;" <?php } ?> >
				<?php
					echo wcf()->meta->get_text_field(
						array(
							'label' => __( 'Placeholder', 'cartflows' ),
							'name'  => 'wcf_label_placeholder_field_' . $field_args['section'] . '[' . str_replace( 'wcf-', '', $field_args['name'] ) . ']',
							'value' => $label,
						)
					);
				?>
			</div>

			<div class="wcf-field-item-settings-required">
				<?php
					echo wcf()->meta->get_checkbox_field(
						array(
							'label' => __( 'Required', 'cartflows' ),
							'name'  => 'wcf_is_required_field_' . $field_args['section'] . '[' . str_replace( 'wcf-', '', $field_args['name'] ) . ']',
							'value' => $required,
						)
					);
				?>
			</div>

			<div class="wcf-field-item-settings-checkbox">
				<?php
					echo wcf()->meta->get_checkbox_field(
						array(
							'label' => __( 'Enable this field', 'cartflows' ),
							'name'  => $field_args['name'],
							'value' => $value,
						)
					);
				?>
			</div>

			<?php
				if(isset( $field_args['after_html']) )
				{
			?>
				<div class="wcf-field-item-settings-row-delete-cf">
				<?php echo $field_args['after_html']; ?>
				</div>
			<?php 
				}
			?>
			<!-- 
			<label for="<?php echo $field_args['name']; ?>">
				<?php _e( 'Label', 'cartflows' ); ?><br>
				<input type="text" value="<?php echo $field_args['label']; ?>"> 
				
				<?php if( isset( $field_markup ) ) { echo $field_markup; }?>

				<input type="hidden" name="wcf_field_order_<?php echo $field_args['section']; ?>[]" value="<?php echo str_replace('wcf-', '', $field_args['name'] ); ?>"> 
			</label> -->
		</div>

		<?php

		return ob_get_clean();
	}

	/**
	 * [delete_checkout_custom_field description]
	 *
	 * @hook wcf_delete_checkout_custom_field
	 * @return [type] [description]
	 */
	public function delete_checkout_custom_field() {

		check_ajax_referer( 'wcf-delete-checkout-custom-field', 'security' );

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
	 * Function to search coupons
	 */
	public function json_search_pages() {

		check_ajax_referer( 'wcf-json-search-pages', 'security' );

		$term = (string) urldecode( sanitize_text_field( wp_unslash( $_GET['term'] ) ) ); // phpcs:ignore

		if ( empty( $term ) ) {
			die( 'not found' );
		}

		$search_string = $term;
		$data          = array();
		$result        = array();

		add_filter( 'posts_search', array( $this, 'search_only_titles' ), 10, 2 );

		$query = new WP_Query(
			array(
				's'              => $search_string,
				'post_type'      => 'page',
				'posts_per_page' => - 1,
			)
		);

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$title  = get_the_title();
				$title .= ( 0 != $query->post->post_parent ) ? ' (' . get_the_title( $query->post->post_parent ) . ')' : '';
				$id     = get_the_id();
				$data[] = array(
					'id'   => $id,
					'text' => $title,
				);
			}
		}

		if ( is_array( $data ) && ! empty( $data ) ) {
			$result[] = array(
				'text'     => '',
				'children' => $data,
			);
		}

		wp_reset_postdata();

		// return the result in json.
		wp_send_json( $result );
	}

	public function search_only_titles( $search, $wp_query ) {
		if ( ! empty( $search ) && ! empty( $wp_query->query_vars['search_terms'] ) ) {
			global $wpdb;

			$q = $wp_query->query_vars;
			$n = ! empty( $q['exact'] ) ? '' : '%';

			$search = array();

			foreach ( (array) $q['search_terms'] as $term ) {
				$search[] = $wpdb->prepare( "$wpdb->posts.post_title LIKE %s", $n . $wpdb->esc_like( $term ) . $n );
			}

			if ( ! is_user_logged_in() ) {
				$search[] = "$wpdb->posts.post_password = ''";
			}

			$search = ' AND ' . implode( ' AND ', $search );
		}

		return $search;
	}

	function get_field( $field_data, $field_content ) {

		$label      = isset( $field_data['label'] ) ? $field_data['label'] : '';
		$help       = isset( $field_data['help'] ) ? $field_data['help'] : '';
		$after_html = isset( $field_data['after_html'] ) ? $field_data['after_html'] : '';

		$name_class = 'field-' . $field_data['name'];

		$field_html  = '<div class="wcf-field-row ' . $name_class . '">';

		if( ! empty( $label ) || ! empty( $help ) ) {
			$field_html .= '<div class="wcf-field-row-heading">';		
			if( ! empty( $label ) ) {
				$field_html .= '<label>' . esc_html( $label ) . '</label>';
			}
			if ( ! empty( $help ) ) {
				$field_html .= '<i class="wcf-field-heading-help dashicons dashicons-editor-help">';
					// $field_html .= '<span class="wcf-tooltip" data-tooltip= "'. esc_attr( $help ) .'"></span>';
				$field_html .= '</i>';
				$field_html .= '<span class="wcf-tooltip-text">';
					$field_html .= $help;
				$field_html .= '</span>';
			}
			$field_html .= '</div>';
		}

		$field_html .= '<div class="wcf-field-row-content">';
		$field_html .= $field_content;

		if ( ! empty( $after_html ) ) {
			$field_html .= $after_html;
		}

		$field_html .= '</div>';
		$field_html .= '</div>';

		return $field_html;
	}

	function get_text_field( $field_data ) {

		$value = $field_data['value'];

		$attr = '';

		if ( isset( $field_data['attr'] ) && is_array( $field_data['attr'] ) ) {

			foreach ( $field_data['attr'] as $attr_key => $attr_value ) {
				$attr .= ' ' . $attr_key . '="' . $attr_value . '"';
			}
		}

		$field_content = '<input type="text" name="' . $field_data['name'] . '" value="' . $value . '" ' . $attr . '>';

		return $this->get_field( $field_data, $field_content );
	}

	function get_shortcode_field( $field_data ) {

		$attr = '';

		$attr_fields = array(
			'readonly'  => 'readonly',
			'onfocus'   => 'this.select()',
			'onmouseup' => 'return false',
		);

		if ( $attr_fields && is_array( $attr_fields ) ) {

			foreach ( $attr_fields as $attr_key => $attr_value ) {
				$attr .= ' ' . $attr_key . '="' . $attr_value . '"';
			}
		}

		$field_content = '<input type="text" name="' . $field_data['name'] . '" value="' . $field_data['content'] . '" ' . $attr . '>';

		return $this->get_field( $field_data, $field_content );
	}

	function get_display_field( $field_data ) {

		$field_content = $field_data['content'];

		return $this->get_field( $field_data, $field_content );
	}

	function get_hr_line_field( $field_data ) {

		$field_data = array(
			'name'	  => 'wcf-hr-line',
			'content' => '<hr>'
		);

		$field_content = $field_data['content'];

		return $this->get_field( $field_data, $field_content );
	}

	function get_number_field( $field_data ) {

		$value = $field_data['value'];

		$attr = '';

		if ( isset( $field_data['attr'] ) && is_array( $field_data['attr'] ) ) {

			foreach ( $field_data['attr'] as $attr_key => $attr_value ) {
				$attr .= ' ' . $attr_key . '="' . $attr_value . '"';
			}
		}

		$field_content = '<input type="number" name="' . $field_data['name'] . '" value="' . $value . '" ' . $attr . '>';

		return $this->get_field( $field_data, $field_content );
	}

	function get_hidden_field( $field_data ) {

		$value = $field_data['value'];

		$attr = '';

		if ( isset( $field_data['attr'] ) && is_array( $field_data['attr'] ) ) {

			foreach ( $field_data['attr'] as $attr_key => $attr_value ) {
				$attr .= ' ' . $attr_key . '="' . $attr_value . '"';
			}
		}

		$field_content = '<input type="hidden" id="' . $field_data['name'] . '" name="' . $field_data['name'] . '" value="' . $value . '" ' . $attr . '>';

		return $this->get_field( $field_data, $field_content );
	}

	function get_area_field( $field_data ) {

		$value = $field_data['value'];

		$attr = '';

		if ( isset( $field_data['attr'] ) && is_array( $field_data['attr'] ) ) {

			foreach ( $field_data['attr'] as $attr_key => $attr_value ) {
				$attr .= ' ' . $attr_key . '="' . $attr_value . '"';
			}
		}

		$field_content  = '<textarea name="' . $field_data['name'] . '" rows="10" cols="50" ' . $attr . '>';
		$field_content .= $value;
		$field_content .= '</textarea>';

		return $this->get_field( $field_data, $field_content );
	}

	function get_only_checkbox_field( $field_data ) {
		// echo "<pre>";
		// var_dump($field_data);
		// echo "</pre>";

		$value = $field_data['value'];
		

		$field_content = '';
		if ( isset( $field_data['before'] ) ) {
			$field_content .= '<span>' . $field_data['before'] . '</span>';
		}
		$field_content .= '<input type="hidden" name="' . $field_data['name'] . '" value="no">';
		$field_content .= '<input type="checkbox" name="' . $field_data['name'] . '" value="yes" ' . checked( 'yes', $value, false ) . '>';

		if ( isset( $field_data['after'] ) ) {
			$field_content .= '<span>' . $field_data['after'] . '</span>';
		}

		if ( isset( $field_data['after_html'] ) ) {
			$field_content .= '<span>' . $field_data['after_html'] . '</span>';
		}

		return $field_content;
	}

	function get_checkbox_field( $field_data ) {

		$value = $field_data['value'];

		$field_content = '';
		if ( isset( $field_data['before'] ) ) {
			$field_content .= '<span>' . $field_data['before'] . '</span>';
		}
		$field_content .= '<input type="hidden" name="' . $field_data['name'] . '" value="no">';
		$field_content .= '<input type="checkbox" id="'.$field_data['name'].'" name="' . $field_data['name'] . '" value="yes" ' . checked( 'yes', $value, false ) . '>';

		if ( isset( $field_data['after'] ) ) {
			$field_content .= '<span>' . $field_data['after'] . '</span>';
		}

		return $this->get_field( $field_data, $field_content );
	}

	function get_radio_field( $field_data ) {

		$value 			= $field_data['value'];
		$field_content 	= '';

		if ( is_array( $field_data['options'] ) && ! empty( $field_data['options'] ) ) {

			foreach ( $field_data['options'] as $data_key => $data_value ) {
				
				$field_content .= '<div class="wcf-radio-option">';
				$field_content .= '<input type="radio" name="' . $field_data['name'] . '" value="' . $data_key . '" ' . checked( $data_key, $value, false ) . '>';
				$field_content .= $data_value;
				$field_content .= '</div>';
			}
		}

		return $this->get_field( $field_data, $field_content );
	}

	function get_font_family_field( $field_data ) {

		$value 			= $field_data['value'];

		$pro_options	= isset( $field_data['pro-options'] ) ? $field_data['pro-options'] : array();

		$field_content = '<select class="wcf-field-font-family" data-for="' . $field_data['for'] . '" name="' . $field_data['name'] . '">';
		
		$field_content .= '<option value="" ' . selected( '', $value, false ) . '>Default</option>';

		$field_content .= '<optgroup label="Other System Fonts">';
		foreach ( CartFlows_Font_Families::get_system_fonts() as $name => $variants ) {
			$field_content .= '<option value="' . esc_attr( $name ) . '" ' . selected( $name, $value, false ) . '>' . esc_attr( $name ) . '</option>';
		}
		$field_content .= '</optgroup>';
		$field_content .= '<optgroup label="Google">';
		foreach ( CartFlows_Font_Families::get_google_fonts() as $name => $single_font ) {
			$variants   = wcf_get_prop( $single_font, '0' );
			$category   = wcf_get_prop( $single_font, '1' );
			$font_value = '\'' . esc_attr( $name ) . '\', ' . esc_attr( $category );
			$field_content .= '<option value="' . esc_attr( $font_value ) . '" ' . selected( $font_value, $value, false ) . '>' . esc_attr( $name ) . '</option>';
		}
		$field_content .= '</optgroup>';

		$field_content .= '</select>';

		return $this->get_field( $field_data, $field_content );
	}

	function get_font_weight_field( $field_data ) {

		$value 			= $field_data['value'];

		$pro_options	= isset( $field_data['pro-options'] ) ? $field_data['pro-options'] : array();

		$field_content = '<select data-selected="'.esc_attr( $value ).'" class="wcf-field-font-weight" data-for="' . $field_data['for'] . '" name="' . $field_data['name'] . '">';
		
		$field_content .= '<option value="" ' . selected( '', $value, false ) . '>Default</option>';

		$field_content .= '</select>';

		return $this->get_field( $field_data, $field_content );
	}

	function get_select_field( $field_data ) {

		$value 			= $field_data['value'];
		$pro_options	= isset( $field_data['pro-options'] ) ? $field_data['pro-options'] : array();

		$field_content = '<select name="' . $field_data['name'] . '">';


		if ( is_array( $field_data['options'] ) && ! empty( $field_data['options'] ) ) {

			foreach ( $field_data['options'] as $data_key => $data_value ) {

				$disabled = '';

				if ( array_key_exists( $data_key, $pro_options ) ) {
					$disabled 	= 'disabled ';
					$data_value = $pro_options[ $data_key ];
				}

				$field_content .= '<option value="' . $data_key . '" ' . selected( $value, $data_key, false ) . ' ' . $disabled .'>' . $data_value . '</option>';
			}
		}

		$field_content .= '</select>';

		if ( isset( $field_data['after'] ) ) {
			$field_content .= '<span>' . $field_data['after'] . '</span>';
		}

		return $this->get_field( $field_data, $field_content );
	}

	function get_color_picker_field( $field_data ) {

		$value = $field_data['value'];

		$field_content = '<input class="wcf-color-picker" type="text" name="' . $field_data['name'] . '" value="' . $value . '">';

		return $this->get_field( $field_data, $field_content );
	}

	function get_product_selection_field( $field_data ) {

		$value = $field_data['value'];

		$multiple = '';

		if ( isset( $field_data['multiple'] ) && $field_data['multiple'] ) {
			$multiple = ' multiple="multiple"';
		}

		$allow_clear = '';

		if ( isset( $field_data['allow_clear'] ) && $field_data['allow_clear'] ) {
			$allow_clear = ' data-allow_clear="allow_clear"';
		}

		$field_content = '<select
					name="' . $field_data['name'] . '[]"
					class="wcf-product-search" ' . $multiple . $allow_clear . '
					data-placeholder="' . __( 'Search for a product&hellip;', 'cartflows' ) . '"
					data-action="woocommerce_json_search_products_and_variations">';

		if ( is_array( $value ) && ! empty( $value ) ) {

			foreach ( $value as $data_key => $product_id ) {

				$product = wc_get_product( $product_id );

				// posts.
				if ( ! empty( $product ) ) {
					$post_title = $product->get_name() . ' (#' . $product_id . ')';

					$field_content .= '<option value="' . $product_id . '" selected="selected" >' . $post_title . '</option>';
				}
			}
		}
		$field_content .= '</select>';

		return $this->get_field( $field_data, $field_content );
	}

	function get_coupon_selection_field( $field_data ) {

		$value = $field_data['value'];

		$multiple = '';

		if ( isset( $field_data['multiple'] ) && $field_data['multiple'] ) {
			$multiple = ' multiple="multiple"';
		}

		$allow_clear = '';

		if ( isset( $field_data['allow_clear'] ) && $field_data['allow_clear'] ) {
			$allow_clear = ' data-allow_clear="allow_clear"';
		}

		$field_content = '<select
					name="' . $field_data['name'] . '[]"
					class="wc-coupon-search wcf-coupon-search" ' . $multiple . $allow_clear . '
					data-placeholder="' . __( 'Search for a coupon&hellip;', 'cartflows' ) . '"
					data-action="wcf_json_search_coupons">';

		if ( is_array( $value ) && ! empty( $value ) ) {

			$all_discount_types = wc_get_coupon_types();

			foreach ( $value as $coupon_title ) {

				$coupon = new WC_Coupon( $coupon_title );

				$discount_type = $coupon->get_discount_type();

				if ( isset( $discount_type ) && $discount_type ) {
					$discount_type = ' ( Type: ' . $all_discount_types[ $discount_type ] . ' )';
				}

				$field_content .= '<option value="' . $coupon_title . '" selected="selected">' . $coupon_title . $discount_type . '</option>';
			}
		}

		$field_content .= '</select>';

		return $this->get_field( $field_data, $field_content );
	}

	function get_page_selection_field( $field_data ) {

		$value = $field_data['value'];

		$multiple = '';

		if ( isset( $field_data['multiple'] ) && $field_data['multiple'] ) {
			$multiple = 'multiple="multiple"';
		}

		$field_content = '<select
					name="' . $field_data['name'] . '[]"
					class="wcf-search-pages" ' . $multiple . '"
					data-action="wcf_json_search_pages">';

		if ( is_array( $value ) && ! empty( $value ) ) {

			foreach ( $value as $data_key => $data_value ) {

				$field_content .= '<option value="' . $data_value . '">' . get_the_title( $data_value ) . '</option>';
			}
		}

		$field_content .= '</select>';

		return $this->get_field( $field_data, $field_content );
	}

	function get_section( $field_data ) {
		$field_html      = '<div class="wcf-field-row wcf-field-section">';
			$field_html    .= '<div class="wcf-field-section-heading" colspan="2">';
				$field_html  .= '<label>' . esc_html( $field_data['label'] ) . '</label>';

						if ( isset( $field_data['help'] ) ) {
			$field_html .= '<i class="wcf-field-heading-help dashicons dashicons-editor-help" title="' . esc_attr( $field_data['help'] ) . '"></i>';
						}
			$field_html    .= '</div>';
		$field_html     .= '</div>';
		return $field_html;
	}

	function get_description_field( $field_data ) {
		
		$field_html      = '<div class="wcf-field-row wcf-field-desc ' . $field_data['name'] . '">';
			$field_html    .= '<div class="wcf-field-desc-content">';
				$field_html  .= $field_data['content'];
			$field_html    .= '</div>';
		$field_html     .= '</div>';
		
		return $field_html;
	}

	function get_checkout_field_repeater( $field_data ) {

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
			// 	$field_content .= '<label>' . esc_html( $field_data['label'] ) . '</label>';
			// $field_content .= '</div>';
			$field_content 	.= '<div class="wcf-field-row-content">';
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
		$field_content     .= '<select name="wcf-checkout-custom-fields[' . $p_key . '][type]" class="wcf-cpf-type">';
		$field_content .= '<option value="text">Text</option>';
		$field_content .= '<option value="textarea">Textarea</option>';
		$field_content .= '<option value="select">Select</option>';
		$field_content .= '<option value="checkbox">Checkbox</option>';
		$field_content     .= '</select>';
		$field_content  .= '</span>';
			$field_content .= '</div>';

			/* Textarea */
			$field_content .= '<div class="wcf-cpf-fields wcf-cpf-options">';
			$field_content     .= '<span class="wcf-cpf-row-setting-label">Options *</span>';
			$field_content     .= '<span class="wcf-cpf-row-setting-field">';
			$field_content .= '<textarea value="" name="wcf-checkout-custom-fields[' . $p_key . '][label]" class="wcf-cpf-options" placeholder="Enter your options separated by comma."></textarea>';
			$field_content     .= '</span>';
			$field_content .= '</div>';

			/* Label */
			$field_content .= '<div class="wcf-cpf-fields wcf-cpf-label">';
			$field_content     .= '<span class="wcf-cpf-row-setting-label">Label *</span>';
			$field_content     .= '<span class="wcf-cpf-row-setting-field">';
			$field_content .= '<input type="text" value="" name="wcf-checkout-custom-fields[' . $p_key . '][label]" class="wcf-cpf-label">';
			$field_content     .= '</span>';
			$field_content .= '</div>';

			/* Name */
			$field_content .= '<div class="wcf-cpf-fields wcf-cpf-name">';
			$field_content     .= '<span class="wcf-cpf-row-setting-label">Name *</span>';
			$field_content     .= '<span class="wcf-cpf-row-setting-field">';
			$field_content .= '<input type="text" value="" name="wcf-checkout-custom-fields[' . $p_key . '][name]" class="wcf-cpf-name">';
			$field_content     .= '</span>';
		$field_content .= '</div>';

		$field_content .= '</div>';
			$field_content .= '</div>';
		}	

					/* Add New Custom Field */
					$field_content  .= '<div class="wcf-cpf-add-row">';
						$field_content .= '<div class="wcf-cpf-add-wrap">';
							$field_content .= '<button class="button button-secondary wcf-cpf-add" data-name="wcf-checkout-custom-fields">Add New Field</button>';
						$field_content .= '</div>';
					$field_content .= '</div>';
					/* End Add new custom field */

				$field_content .= '</div>';
			$field_content .= '</div>';
		$field_content .= '</div>';

		return $field_content;
	}

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
			// 	$field_content .= '<label>' . esc_html( $field_data['label'] ) . '</label>';
			// $field_content .= '</div>';
			$field_content 	.= '<div class="wcf-field-row-content">';
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
			$field_content     .= '<select name="wcf-checkout-custom-fields[' . $p_key . '][type]" class="wcf-cpf-type">';
			$field_content .= '<option value="text">Text</option>';
			$field_content .= '<option value="textarea">Textarea</option>';
			$field_content .= '<option value="select">Select</option>';
			$field_content .= '<option value="checkbox">Checkbox</option>';
			$field_content .= '<option value="hidden">Hidden</option>';
			$field_content     .= '</select>';
			$field_content  .= '</span>';
			$field_content .= '</div>';
			
			/* Label */
			$field_content .= '<div class="wcf-cpf-fields wcf-cpf-label">';
			$field_content .= '<span class="wcf-cpf-row-setting-label">Label <i>*</i></span>';
			$field_content .=  '<span class="wcf-cpf-row-setting-field">';
			$field_content .=   '<input type="text" value="" name="wcf-checkout-custom-fields[' . $p_key . '][label]" class="wcf-cpf-label">';
			$field_content .=   '<span id="wcf-cpf-label-error-msg"></span>';
			$field_content .= '</span>';
			$field_content .= '</div>';

			/* Default */
			$field_content	.= '<div class="wcf-cpf-fields wcf-cpf-default">';
			$field_content	.= '<span class="wcf-cpf-row-setting-label">Default</span>';
			$field_content	.=  '<span class="wcf-cpf-row-setting-field">';
			$field_content	.=   '<input type="text" value="" name="wcf-checkout-custom-fields[' . $p_key . '][default]" class="wcf-cpf-default">';
			$field_content 	.=   '<span id="wcf-cpf-default-error-msg"></span>';
			$field_content	.=  '</span>';
			$field_content	.= '</div>';

			/* Placeholder */
			$field_content	.= '<div class="wcf-cpf-fields wcf-cpf-placeholder">';
			$field_content	.= '<span class="wcf-cpf-row-setting-label">Placeholder</span>';
			$field_content	.=  '<span class="wcf-cpf-row-setting-field">';
			$field_content	.=   '<input type="text" value="" name="wcf-checkout-custom-fields[' . $p_key . '][placeholder]" class="wcf-cpf-placeholder">';
			$field_content 	.=   '<span id="wcf-cpf-placeholder-error-msg"></span>';
			$field_content	.=  '</span>';
			$field_content	.= '</div>';

			/* Options */
			$field_content .= '<div class="wcf-cpf-fields wcf-cpf-options">';
			$field_content     .= '<span class="wcf-cpf-row-setting-label">Options <i>*</i></span>';
			$field_content     .= '<span class="wcf-cpf-row-setting-field">';
			$field_content .= '<textarea value="" name="wcf-checkout-custom-fields[' . $p_key . '][label]" class="wcf-cpf-options" placeholder="Enter your options separated by comma."></textarea>';
			$field_content     .= '</span>';
			$field_content .= '</div>';
			
			/* Width */
			$field_content	.= '<div class="wcf-cpf-fields wcf-cpf-width">';
			$field_content	.= '<span class="wcf-cpf-row-setting-label">Width</span>';
			$field_content	.=  '<span class="wcf-cpf-row-setting-field">';
			$field_content	.=   '<select name="wcf-checkout-custom-fields[' . $p_key . '][width]" class="wcf-cpf-width">';
			$field_content	.=    '<option value="33">33%</option>';
			$field_content	.=    '<option value="50">50%</option>';
			$field_content	.=    '<option value="100" selected>100%</option>';
			$field_content	.=   '</select>';
			$field_content	.=  '</span>';
			$field_content	.= '</div>';
			
			/* Required */
			$field_content	.= '<div class="wcf-cpf-fields wcf-cpf-required">';
			$field_content	.= '<span class="wcf-cpf-row-setting-label">Required</span>';
			$field_content	.=  '<span class="wcf-cpf-row-setting-field">';
			$field_content	.=   '<input type="hidden" value="no" name="wcf-checkout-custom-fields[' . $p_key . '][required]" class="wcf-cpf-required">';
			$field_content	.=   '<input type="checkbox" value="yes" name="wcf-checkout-custom-fields[' . $p_key . '][required]" class="wcf-cpf-required">';
			$field_content 	.=   '<span id="wcf-cpf-required-error-msg"></span>';
			$field_content	.=  '</span>';
			$field_content	.= '</div>';

		$field_content .= '</div>';
			$field_content .= '</div>';
		}	

					/* Add New Custom Field */
					$field_content  .= '<div class="wcf-cpf-add-row">';
						$field_content .= '<div class="wcf-cpf-add-wrap">';
							$field_content .= '<button class="button button-secondary wcf-pro-cpf-add" data-name="wcf-checkout-custom-fields">Add New Field</button>';
						$field_content .= '</div>';
					$field_content .= '</div>';
					/* End Add new custom field */

				$field_content .= '</div>';
			$field_content .= '</div>';
		$field_content .= '</div>';

		return $field_content;
	}

	function get_product_selection_repeater( $field_data ) {

		$value = $field_data['value'];

		if ( ! is_array( $value ) ) {

			$value[0] = array(
				'product' => '',
			);
		} else {

			if ( ! isset( $value[0] ) ) {

				$value[0] = array(
					'product' => '',
				);
			}
		}

		$field_html = '';

		$field_html     .= '<script type="text/html" id="tmpl-wcf-product-repeater">';
			$field_html .= $this->generate_product_repeater_html( '{{id}}' );
		$field_html     .= '</script>';

		$field_html .= '<div class="wcf-field-row">';
			$field_html .= '<div class="wcf-field-row-content">';
				$field_html .= '<div class="wcf-repeatables-wrap">';

					if ( is_array( $value ) ) {

			foreach ( $value as $p_key => $p_data ) {

				$selected_options = '';

				if ( isset( $p_data['product'] ) ) {

					$product = wc_get_product( $p_data['product'] );

					// posts.
					if ( ! empty( $product ) ) {
						$post_title = $product->get_name() . ' (#' . $p_data['product'] . ')';

						$selected_options = '<option value="' . $p_data['product'] . '" selected="selected" >' . $post_title . '</option>';
					}
				}

				$field_html .= $this->generate_product_repeater_html( $p_key, $selected_options );
			}
					}

					$field_html         .= '<div class="wcf-add-repeatable-row">';
						$field_html     .= '<div class="submit wcf-add-repeatable-wrap">';
							$field_html .= '<button class="button-primary wcf-add-repeatable" data-name="wcf-checkout-products">Add New Product</button>';
						$field_html .= '</div>';
					$field_html .= '</div>';
				$field_html .= '</div>';
			$field_html .= '</div>';
		$field_html .= '</div>';

		return $field_html;
	}

	function generate_product_repeater_html( $id, $options = '' ) {

		$field_html = '<div class="wcf-repeatable-row" data-key="' . $id . '">';

			$field_html .= '<div class="wcf-repeatable-row-standard-fields">';

				/* Product Name */
				$field_html             .= '<div class="wcf-repeatable-fields wcf-sel-product">';
					$field_html         .= '<span class="wcf-repeatable-row-setting-field">';
						$field_html     .= '<select
							name="wcf-checkout-products[' . $id . '][product]"
							class="wcf-product-search"
							data-allow_clear="allow_clear"
							data-placeholder="' . __( 'Search for a product&hellip;', 'cartflows' ) . '"
							data-action="woocommerce_json_search_products_and_variations">';
							$field_html .= $options;
						$field_html     .= '</select>';
					$field_html         .= '</span>';
					$field_html     	.= '<span class="wcf-repeatable-row-actions">';
						$field_html 	.= '<a class="wcf-remove-row wcf-repeatable-remove button" data-type="product">';
							$field_html .= '<span class="dashicons dashicons-trash"></span>';
							$field_html .= '<span class="wcf-repeatable-remove-button">'. __( 'Remove', 'cartflows' ).'</span>';
							$field_html .= '</a>';
						$field_html     .= '</span>';
				$field_html             .= '</div>';
			$field_html                 .= '</div>';
		$field_html                     .= '</div>';

		return $field_html;
	}

	function get_image_field( $field_data ) {

		$value = $field_data['value'];

		$attr = '';

		if ( isset( $field_data['attr'] ) && is_array( $field_data['attr'] ) ) {

			foreach ( $field_data['attr'] as $attr_key => $attr_value ) {
				$attr .= ' ' . $attr_key . '="' . $attr_value . '"';
			}
		}

		$display_preview_box = ( isset( $value ) && '' != $value ) ? 'display:block;' : 'display:none';

		$field_content  = '<div id="wcf-image-preview" style="'.$display_preview_box.'">';
			if( isset( $value ) ){ 
			$field_content .= '<img src="'. $value .'" class="saved-image" name="'. $field_data['name'] .'" width="150">';
			}
		$field_content .= '</div>';
		// $field_content  .= '<input type="hidden" id="wcf-image-id" class="wcf-image-id" name="wcf-image-id[image-id]" value="">';
		$field_content  .= '<input type="hidden" id="wcf-image-value" class="wcf-image"  name="' . $field_data['name'] . '" value="'.$value.'">';
		
		$field_content .= '<button type="button" ' . $attr . ' class="wcf-select-image button-secondary">Select Image</button>';

		$display_remove_button = ( isset( $value ) && '' != $value ) ? 'display:inline-block; margin-left: 5px;' : 'display:none';

		$field_content .= '<button type="button" class="wcf-remove-image button-secondary" style="'.$display_remove_button.'">Remove Image</button>';

		return $this->get_field( $field_data, $field_content );
	}

	/**
	 * Localize variables in admin
	 *
	 * @param array $vars variables.
	 */
	function localize_vars( $vars ) {

		$ajax_actions = array(
			'wcf_add_checkout_custom_field',
			'wcf_pro_add_checkout_custom_field',
			'wcf_delete_checkout_custom_field',
			'wcf_json_search_pages',
			'wcf_json_search_coupons'
		);

		foreach ( $ajax_actions as $action ) {

			$vars[ $action . '_nonce' ] = wp_create_nonce( str_replace( '_', '-', $action ) );
		}

		return $vars;
	}
}
// @codingStandardsIgnoreEnd
