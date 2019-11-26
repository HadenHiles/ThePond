<?php
/**
 * Elementor page builder compatibility
 *
 * @package CartFlows
 */

namespace Elementor\Modules\PageTemplates;

use Elementor\Core\Base\Document;
use Elementor\Plugin;

/**
 * Class for elementor page builder compatibility
 */
class Cartflows_Elementor_Compatibility {

	/**
	 * Member Variable
	 *
	 * @var instance
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
	 *  Constructor
	 */
	public function __construct() {

		add_filter( 'cartflows_page_template', array( $this, 'get_page_template' ) );
	}

	/**
	 * Get page template fiter callback for elementor preview mode
	 *
	 * @param string $template page template.
	 * @return string
	 */
	public function get_page_template( $template ) {

		if ( is_singular() ) {
			$document = Plugin::$instance->documents->get_doc_for_frontend( get_the_ID() );

			if ( $document ) {
				$template = $document->get_meta( '_wp_page_template' );
			}
		}

		return $template;
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Elementor_Compatibility::get_instance();
