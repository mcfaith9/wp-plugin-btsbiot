<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              aguyiknow.com.au
 * @since             1.0.1
 * @package           Btsbiot
 *
 * @wordpress-plugin
 * Plugin Name:       BTSBIOT by Mcfaith
 * Plugin URI:        http://www.aguyiknow.com.au
 * Description: Custome plugins dedicated for Sync/Copy products, post and pages. etc.
 * Version:           1.0.1
 * Author:            Mcfaith 
 * Author URI:        https://www.facebook.com/mcfaith
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       btsbiot
 * Required WP : 4.6
 * Domain Path:       /languages
 * Copyright 2021-2021 - Mcfaith (https://www.facebook.com/mcfaith)
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
define( 'BTSBIOT_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-btsbiot-activator.php
 */
function activate_btsbiot() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-btsbiot-activator.php';
	Btsbiot_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-btsbiot-deactivator.php
 */
function deactivate_btsbiot() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-btsbiot-deactivator.php';
	Btsbiot_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_btsbiot' );
register_deactivation_hook( __FILE__, 'deactivate_btsbiot' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-btsbiot.php';

require plugin_dir_path( __FILE__ ) . 'includes/btsbiot-move-post.php';
require plugin_dir_path( __FILE__ ) . 'includes/btsbiot-move-product.php';
require plugin_dir_path( __FILE__ ) . 'includes/btsbiot-move-page.php';
/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_btsbiot() {

	$plugin = new Btsbiot();
	$plugin->run();

}
run_btsbiot();
