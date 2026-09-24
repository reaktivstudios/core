<?php
/**
 * CLI class to iterate over posts in bulk.
 * 
 * This will handle:
 *  - pagination
 *  - progress bars
 *  - memory cleanup 
 * for bulk operations on posts.
 *
 * @package rkv-utilities
 */

namespace RKV\Utilities\CLI;

use RKV\Core\CLI\Bulk_Post_Iterator as New_Bulk_Post_Iterator;

if ( ! defined( '\WP_CLI' ) ) {
	return;
}

if ( class_exists( __NAMESPACE__ . '\Bulk_Post_Iterator' ) ) {
	return;
}

\RKV\Utilities\deprecated_class( __NAMESPACE__, 'Bulk_Post_Iterator' );

/**
 * Bulk Post Iterator class.
 */
abstract class Bulk_Post_Iterator extends New_Bulk_Post_Iterator {}
