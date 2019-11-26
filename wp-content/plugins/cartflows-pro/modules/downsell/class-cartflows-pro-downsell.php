<?php
/**
 * Downsell
 *
 * @package cartflows
 */

define( 'CARTFLOWS_PRO_DOWNSELL_DIR', CARTFLOWS_PRO_DIR . 'modules/downsell/' );
define( 'CARTFLOWS_PRO_DOWNSELL_URL', CARTFLOWS_URL . 'modules/downsell/' );

/**
 * Initial Setup
 *
 * @since 1.0.0
 */
class Cartflows_Pro_Downsell {


	/**
	 * Member Variable
	 *
	 * @var object instance
	 */
	private static $instance;

	/**
	 *  Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor function that initializes required actions and hooks
	 */
	public function __construct() {
		require_once CARTFLOWS_PRO_DOWNSELL_DIR . 'classes/class-cartflows-downsell-meta.php';
		require_once CARTFLOWS_PRO_DOWNSELL_DIR . 'classes/class-cartflows-downsell-markup.php';
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Downsell::get_instance();
