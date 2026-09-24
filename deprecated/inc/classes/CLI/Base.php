<?php
/**
 * CLI Class.
 *
 * @package rkv-utilities
 */

namespace RKV\Utilities\CLI;

use RKV\Core\CLI\Base as New_Base;

if ( ! defined( '\WP_CLI' ) ) {
	return;
}

if ( class_exists( __NAMESPACE__ . '\Base' ) ) {
	return;
}

\RKV\Utilities\deprecated_class( __NAMESPACE__, 'Base' );

/**
 * Adds CLI commands and serves as an extendable template.
 */
abstract class Base extends New_Base {}
