<?php

/**
 * Fired during plugin activation
 *
 * @link       https://wpsani.store
 * @since      1.0.0
 *
 * @package    WhatsLink_Click_Tracker
 * @subpackage WhatsLink_Click_Tracker/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    WhatsLink_Click_Tracker
 * @subpackage WhatsLink_Click_Tracker/includes
 * @author     WPSani <support@wpsani.store>
 */
class WhatsLink_Click_Tracker_Activator {

	/**
	 * When the plugin is activated, this method will be called.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		global $wpdb;
		$table_name      = $wpdb->prefix . 'whatslink_click_tracker_clicks';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id              BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			post_title      TEXT                NOT NULL,
			post_type       VARCHAR(20)         NOT NULL,
			permalink       TEXT                NOT NULL,
			click_datetime  DATETIME            NOT NULL,
			referrer        TEXT                NOT NULL,
			user_id 	    BIGINT(20) UNSIGNED DEFAULT NULL,
			utm_source      VARCHAR(255)        DEFAULT NULL,
			utm_medium      VARCHAR(255)        DEFAULT NULL,
			utm_campaign    VARCHAR(255)        DEFAULT NULL,
			country         VARCHAR(100)        DEFAULT NULL,
			PRIMARY KEY  (id),
			KEY post_type  (post_type(10)),
			KEY click_dt   (click_datetime)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( 'whatslink_click_tracker_db_version', '1.0.0' );
	}


}
