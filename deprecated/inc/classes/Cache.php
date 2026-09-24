<?php
/**
 * Cache class.
 *
 * @package rkv-utilities
 */

namespace RKV\Utilities;

use RKV\Core\Cache as New_Cache;

if ( class_exists( __NAMESPACE__ . '\Cache' ) ) {
	return;
}

\RKV\Utilities\deprecated_class( __NAMESPACE__, 'Cache' );


/**
 * Cache class.
 *
 * Example usage:
 * ```php
 * // Get a value from the cache.
 * $var = new \RKV\Utilities\Cache(
 *  'my_cache_key',
 *  function() {
 *      // Expensive code to generate the value if not found in cache.
 *      return 'my_cache_value';
 *  },
 *  'my_cache_group',
 *  DAY_IN_SECONDS, // Cache expiration time in seconds.
 *  MONTH_IN_SECONDS, // Long expiration time in seconds.
 *  'my_fallback_value' // Fallback value if the cache is not found.
 * )->get();
 *
 * // Clear the cache.
 * new \RKV\Utilities\Cache( 'my_cache_key' )->clear();
 * // Clear the group cache.
 * new \RKV\Utilities\Cache( 'my_cache_key' )->clear_group();
 * // Clear the long expiration cache.
 * new \RKV\Utilities\Cache( 'my_cache_key' )->clear( true );
 * ```
 */
class Cache extends New_Cache {}
