<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://wpsani.store
 * @since             1.0.0
 * @package           WhatsLink_Click_Tracker
 *
 * @wordpress-plugin
 * Plugin Name:       WhatsLink Click Tracker
 * Description:       A simple and easy-to-use WhatsApp link tracker plugin for WordPress.
 * Version:           1.0.0
 * Plugin URI:        https://wpsani.store/whatslink-click-tracker-free/
 * Author:            WPSani
 * Author URI:        https://wpsani.store/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       whatslink-click-tracker
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'WHATSLINK_CLICK_TRACKER_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-whatslink-click-tracker-activator.php
 */
function whatslink_click_tracker_activate() {
	$activator_path = plugin_dir_path( __FILE__ ) . 'includes/class-whatslink-click-tracker-activator.php';
	if ( file_exists( $activator_path ) ) {
		require_once $activator_path;
		WhatsLink_Click_Tracker_Activator::activate();
	}
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-whatslink-click-tracker-deactivator.php
 */
function whatslink_click_tracker_deactivate() {
	$deactivator_path = plugin_dir_path( __FILE__ ) . 'includes/class-whatslink-click-tracker-deactivator.php';
	if ( file_exists( $deactivator_path ) ) {
		require_once $deactivator_path;
		WhatsLink_Click_Tracker_Deactivator::deactivate();
	}
}

register_activation_hook( __FILE__, 'whatslink_click_tracker_activate' );
register_deactivation_hook( __FILE__, 'whatslink_click_tracker_deactivate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-whatslink-click-tracker.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function whatslink_click_tracker_run() {

	$plugin = new WhatsLink_Click_Tracker();
	$plugin->run();

}
whatslink_click_tracker_run();
