<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/phuchnh
 * @since             1.0.0
 * @package           Wp_Crawler
 *
 * @wordpress-plugin
 * Plugin Name:       Wordpress crawler
 * Plugin URI:        https://github.com/phuchnh/wp-crawler
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Phúc Huỳnh
 * Author URI:        https://github.com/phuchnh
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-crawler
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WP_CRAWLER_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-crawler-activator.php
 */
function activate_wp_crawler() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-crawler-activator.php';
	Wp_Crawler_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-crawler-deactivator.php
 */
function deactivate_wp_crawler() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-crawler-deactivator.php';
	Wp_Crawler_Deactivator::deactivate();
}

function add_custom_cron_intervals( $schedules ) {
	$schedules['30_minutes'] = [
		'interval' => 60 * 30,
		'display'  => __( 'Once Every 30 Minutes' ),
	];

	return $schedules;
}

add_filter( 'cron_schedules', 'add_custom_cron_intervals' );
register_activation_hook( __FILE__, 'activate_wp_crawler' );
register_deactivation_hook( __FILE__, 'deactivate_wp_crawler' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-crawler.php';
require plugin_dir_path( __FILE__ ) . 'typerocket/init.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wp_crawler() {

	$plugin = new Wp_Crawler();
	$plugin->run();

}

run_wp_crawler();
