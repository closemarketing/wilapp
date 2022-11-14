<?php
/**
 * Plugin Name: Wilapp
 * Plugin URI:  https://wilapp.com
 * Description: Make appointments for your shop with Wilapp.
 * Version:     1.0.0-beta.1
 * Author:      Closetechnology
 * Author URI:  https://close.technology
 * Text Domain: wilapp
 * Domain Path: /languages
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Requires at least: 5.4
 * Requires PHP: 7.4
 *
 * @package     WordPress
 * @author      Closetechnology
 * @copyright   2021 Closetechnology
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 *
 * Prefix:      wilapp
 */

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

define( 'WILAPP_VERSION', '1.0.0-beta.1' );
define( 'WILAPP_PLUGIN', __FILE__ );
define( 'WILAPP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WILAPP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );


define( 'WILAPP_MAXDAYS', 15 );

add_action( 'plugins_loaded', 'wilapp_plugin_init' );
/**
 * Load localization files
 *
 * @return void
 */
function wilapp_plugin_init() {
	load_plugin_textdomain( 'wilapp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

/**
 * # Includes
 * ---------------------------------------------------------------------------------------------------- */
require_once WILAPP_PLUGIN_PATH . 'includes/class-helpers-wilapp.php';
require_once WILAPP_PLUGIN_PATH . 'includes/class-admin-settings.php';
require_once WILAPP_PLUGIN_PATH . 'includes/class-form-wizard.php';
