<?php
/**
 * Plugin Name:       Bulk Woo Discount
 * Description:       Woocommerce gives feature to provide discounted price.
 * Version:           1.1.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Nikita Solanki
 * Author URI:        https://profiles.wordpress.org/nikitasolanki1812/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       bulk-woo-discount
 * Domain Path:       /languages
 *
 * @package Bulk_Woo_Discount
 * @category Core
 * @author Nikita Solanki
 */

/*
Bulk Woo Discount is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Bulk Woo Discount is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Bulk Woo Discount. If not, see {URI to Plugin License}.
*/

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'BWDP_DIR' ) ) {
	define( 'BWDP_DIR', __DIR__ ); // Plugin dir.
}

if ( ! defined( 'BWDP_TEXT_DOMAIN' ) ) { // Check if variable is not defined previous then define it.
	define( 'BWDP_TEXT_DOMAIN', 'bulk-woo-discount' ); // This is for multi language support in plugin.
}

if ( ! defined( 'BWDP_ADMIN' ) ) {
	define( 'BWDP_ADMIN', BWDP_DIR . '/includes/admin' ); // Plugin admin dir.
}

if ( ! defined( 'BWDP_URL' ) ) {
	define( 'BWDP_URL', plugin_dir_url( __FILE__ ) ); // Plugin url.
}

if ( ! defined( 'BWDP_BASENAME' ) ) {
	define( 'BWDP_BASENAME', basename( BWDP_DIR ) ); // Plugin base name.
}



/**
 * Load Text Domain
 *
 * This gets the plugin ready for translation.
 *
 * @package Bulk_Woo_Discount
 * @since 1.1.0
 */
function bwdp_load_textdomain() {

	// Set filter for plugin's languages directory.
	$woo_bulk_dis_lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
	$woo_bulk_dis_lang_dir = apply_filters( 'woo_bulk_dis_languages_directory', $woo_bulk_dis_lang_dir );

	// Traditional WordPress plugin locale filter.
	$locale = apply_filters( 'plugin_locale', get_locale(), 'bulk-woo-discount' );
	$mofile = sprintf( '%1$s-%2$s.mo', 'bulk-woo-discount', $locale );

	// Setup paths to current locale file.
	$mofile_local  = $woo_bulk_dis_lang_dir . $mofile;
	$mofile_global = WP_LANG_DIR . '/' . BWDP_BASENAME . '/' . $mofile;

	if ( file_exists( $mofile_global ) ) { // Look in global /wp-content/languages/wp-option-list-table folder.
		load_textdomain( 'bulk-woo-discount', $mofile_global );
	} elseif ( file_exists( $mofile_local ) ) { // Look in local /wp-content/plugins/wp-option-list-table/languages/ folder.
		load_textdomain( 'bulk-woo-discount', $mofile_local );
	} else { // Load the default language files.
		load_plugin_textdomain( 'bulk-woo-discount', false, $woo_bulk_dis_lang_dir );
	}
}

/**
 * Activation hook.
 *
 * Register plugin activation hook.
 *
 * @package Bulk_Woo_Discount
 * @since 1.1.0
 */
register_activation_hook( __FILE__, 'bwdp_install' );

/**
 * Deactivation hook.
 *
 * Register plugin deactivation hook.
 *
 * @package Bulk_Woo_Discount
 * @since 1.1.0
 */
register_deactivation_hook( __FILE__, 'bwdp_uninstall' );

/**
 * Plugin Setup Activation hook call back
 *
 * Initial setup of the plugin setting default options
 * and database tables creations.
 *
 * @package Bulk_Woo_Discount
 * @since 1.1.0
 */
function bwdp_install() {
	$required_plugins = array(
		'woocommerce/woocommerce.php', // Replace with the path to the required plugin.
		// Add more dependencies as needed.
	);

	foreach ( $required_plugins as $plugin ) {
		if ( ! is_plugin_active( $plugin ) ) {
			// Required plugin is not active, deactivate and show an error message.
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( 'Sorry, but Bulk woocommerce Discount requires the "woocommerce" to be activated. Please activate it and try again.' );
		}
	}
}

/**
 * Plugin Setup (On Deactivation)
 *
 * Does the drop tables in the database and
 * delete  plugin options.
 *
 * @package Bulk_Woo_Discount
 * @since 1.1.0
 */
function bwdp_uninstall() {
}

/**
 * Parent Plugin should be installed and active to use this plugin.
 *
 * @package Bulk_Woo_Discount
 * @since 1.1.0
 */
function bwdp_child_plugin_has_parent_plugin() {
	$required_plugins = array(
		'woocommerce/woocommerce.php', // Replace with the path to the required plugin.
		// Add more dependencies as needed.
	);

	foreach ( $required_plugins as $plugin ) {
		if ( ! is_plugin_active( $plugin ) ) {
			// Required plugin is not active, deactivate and show an error message.
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( 'Sorry, but Bulk woocommerce Discount requires the "woocommerce" to be activated. Please activate it and try again.' );
		}
	}
}
add_action( 'admin_init', 'bwdp_child_plugin_has_parent_plugin' );

/**
 * Load Plugin
 *
 * Handles to load plugin after
 * dependent plugin is loaded
 * successfully
 *
 * @package Bulk_Woo_Discount
 * @since 1.1.0
 */
function bwdp_loaded() {
	// Load first plugin text domain.
	bwdp_load_textdomain();
}
// Add action to load.
add_action( 'plugins_loaded', 'bwdp_loaded' );

// Admin class file.
require_once BWDP_DIR . '/admin/class-bwdp-admin.php';
$woo_bulk_dis_admin = new BWDP_Admin();
$woo_bulk_dis_admin->add_hooks();

// public class file.
require_once BWDP_DIR . '/public/class-bwdp-public.php';
$woo_bulk_dis_public = new BWDP_Public();
$woo_bulk_dis_public->add_hooks();
