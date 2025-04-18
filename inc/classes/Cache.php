<?php
/**
 * Cache class.
 *
 * @package rkv-utilities
 */

namespace RKV\Utilities;

/**
 * Cache class.
 *
 * Example usage:
 * ```php
 * // Get a value from the cache.
 * $var = new \RKV\Utilities\Cache(
 * 	'my_cache_key',
 * 	function() {
 * 		// Expensive code to generate the value if not found in cache.
 * 		return 'my_cache_value';
 * 	},
 * 	'my_cache_group',
 * 	DAY_IN_SECONDS, // Cache expiration time in seconds.
 *  MONTH_IN_SECONDS, // Long expiration time in seconds.
 * 	'my_fallback_value' // Fallback value if the cache is not found.
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
class Cache {
	/**
	 * Cache key.
	 *
	 * @var string
	 */
	private $key;
	/**
	 * Cache group.
	 *
	 * @var string
	 */
	private $group;
	/**
	 * Cache expiration time.
	 *
	 * @var int
	 */
	private $expiration;
	/**
	 * Callback function to generate the value if not found in cache.
	 *
	 * @var callable
	 */
	private $callback;
	/**
	 * Long expiration time.
	 *
	 * @var int
	 */
	private $long_expiration;
	/**
	 * Cached value.
	 *
	 * @var mixed
	 */
	private $value;
	/**
	 * Fallback value if the cache is not found.
	 *
	 * @var mixed
	 */
	private $fallback;

	/**
	 * Constructor.
	 *
	 * @param string   $key             Cache key.
	 * @param string   $group           Cache group.
	 * @param callable $callback        Callback function to generate the value if not found in cache.
	 * @param int      $expiration      Cache expiration time in seconds.
	 * @param int      $long_expiration Long expiration time in seconds.
	 * @param mixed    $fallback        Fallback value if the cache is not found.
	 */
	public function __construct( $key, $callback = null, $group = '', $expiration = 43200, $long_expiration = 0, $fallback = null ) {
		$this->fallback        = $fallback;
		$this->key             = $key;
		$this->group           = $group;
		$this->expiration      = $expiration;
		$this->long_expiration = $long_expiration;
		$this->callback        = $callback;
	}

	/**
	 * Get the cached value.
	 *
	 * @return mixed Cached value or fallback value.
	 */
	public function get() {
		$found = false;
		$this->value = wp_cache_get( $this->key, $this->group, false, $found );

		if ( ! $found ) {
			if ( is_callable( $this->callback ) ) {
				$this->value = call_user_func( $this->callback );
			} else {
				return $this->get_fallback();
			}

			wp_cache_set( $this->key, $this->value, $this->group, $this->expiration );

			if ( $this->value && $this->long_expiration > 0 ) {
				wp_cache_set( $this->key . '-long', $this->value, $this->group, $this->long_expiration );
			}
		}

		if ( $this->value ) {
			return $this->value;
		} else {
			return $this->get_fallback();
		}
	}

	/**
	 * Clears the cache.
	 *
	 * @param bool $clear_long Whether to clear the long expiration cache.
	 * @return bool True if the cache was cleared, false otherwise.
	 */
	public function clear( $clear_long = false ) {
		$cleared = wp_cache_delete( $this->key, $this->group );
		if ( $clear_long ) {
			wp_cache_delete( $this->key . '-long', $this->group );
		}

		return $cleared;
	}

	/**
	 * Clear the group cache.
	 *
	 * @return bool True if the group cache was cleared, false otherwise.
	 */
	public function clear_group() {
		return wp_cache_flush_group( $this->group );
	}

	/**
	 * Get the fallback value.
	 *
	 * @return mixed Fallback value.
	 */
	private function get_fallback() {
		if ( $this->long_expiration > 0 ) {
			return wp_cache_get( $this->key . '-long', $this->group );
		} else {
			return $this->fallback;
		}
	}
}
