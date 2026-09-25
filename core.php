<?php
/**
 * Plugin Name: Core
 * Plugin URI: https://github.com/reaktivstudios/foundation
 * Description: A collection of utilities, helpers and best practices for WordPress.
 * Author: Reaktiv Studios
 * Author URI: https://reaktiv.co
 * Version: 0.1.0
 * License: GPL2+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package rkv-core
 */

define( 'RKV_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'RKV_CORE_URL', plugin_dir_url( __FILE__ ) );
define( 'RKV_CORE_VERSION', '0.1.0' );

/**
 * Autoloader
 */
require_once RKV_CORE_PATH . 'vendor/autoload.php';

require_once RKV_CORE_PATH . 'deprecated/helpers.php';

require_once RKV_CORE_PATH . 'inc/functions.php';
