<?php
/**
 * Flow
 *
 * @package cartflows
 */

/**
 * Analytics DB class.
 */
class Cartflows_Pro_Analytics_Db {

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
	 *  Constructor
	 */
	public function __construct() {
		$this->create_db_tables();
	}

	/**
	 *  Create tables for analytics.
	 */
	public function create_db_tables() {

		global $wpdb;

		if ( get_option( 'cartflows_database_tables_created' ) === 'yes' ) {
			return;
		}

		$visits_db       = $wpdb->prefix . CARTFLOWS_PRO_VISITS_TABLE;
		$visits_meta_db  = $wpdb->prefix . CARTFLOWS_PRO_VISITS_META_TABLE;
		$charset_collate = $wpdb->get_charset_collate();

		// visits db sql command.
		$sql = "CREATE TABLE $visits_db (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            step_id bigint(20) NOT NULL,
            date_visited datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            visit_type enum('new','return'),
            PRIMARY KEY (id)
        ) $charset_collate;\n";

		// visits meta db sql command.
		$sql .= "CREATE TABLE $visits_meta_db (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            visit_id bigint(20) NOT NULL,
            meta_key varchar(255) NULL,
            meta_value longtext NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( 'cartflows_database_tables_created', 'yes' );

	}
}

Cartflows_Pro_Analytics_Db::get_instance();
