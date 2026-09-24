<?php
/**
 * Find posts and import meta from CSV.
 *
 * @package rkv-utilities
 */

namespace RKV\Utilities\CLI;

use RKV\Core\CLI\Meta_Import as New_Meta_Import;

if ( ! defined( '\WP_CLI' ) ) {
	return;
}

if ( class_exists( __NAMESPACE__ . '\Meta_Import' ) ) {
	return;
}

\RKV\Utilities\deprecated_class( __NAMESPACE__, 'Meta_Import' );


/**
 * Adds meta_import CLI Command.
 */
class Meta_Import extends New_Meta_Import {}
