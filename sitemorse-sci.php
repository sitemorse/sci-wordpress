<?php
/**
 * Sitemorse SCI bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://sitemorse.com
 * @since             1.1.0
 * @package           Sitemorse_SCI
 *
 * @wordpress-plugin
 * Plugin Name:       Sitemorse SCI
 * Plugin URI:        https://sitemorse.com/wordpress-sci
 * Description:       The Sitemorse SCI plugin allows you to access Sitemorse
 *                    tests and metrics before your pages are published, to ensure pages fully
 *                    conform to standards.
 * Version:           1.1.0
 * Author:            Sitemorse (UK Sales) Ltd
 * Author URI:        https://sitemorse.com/wordpress-sci
 * Text Domain:       sitemorse-sci
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
function activate_sitemorse_sci() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-sitemorse-sci-activator.php';
	Sitemorse_SCI_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function deactivate_() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-sitemorse-sci-deactivator.php';
	Sitemorse_SCI_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_sitemorse_sci' );
register_deactivation_hook( __FILE__, 'deactivate_sitemorse_sci' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-sitemorse-sci.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_sitemorse_sci() {

	$sci = new Sitemorse_SCI();
	$sci->run();

}
run_sitemorse_sci();
