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
 * @package rkv-utilities
 */
define( 'RKV_UTILITIES_PATH', plugin_dir_path( __FILE__ ) );
define( 'RKV_UTILITIES_URL', plugin_dir_url( __FILE__ ) );

/**
 * Autoloader
 */
require_once RKV_UTILITIES_PATH . 'vendor/autoload.php';

/**
 * Boostrap all of our core functions.
 */
new RKV\Utilities\Core();
