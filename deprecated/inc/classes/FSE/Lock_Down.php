<?php
/**
 * Prevents unlocking locked blocks in production.
 * 
 * @package rkv-utilities
 */

namespace RKV\Utilities\FSE;

/**
 * Lock FSE Down.
 */
class Lock_Down {

	/**
	 * Add the actions.
	 */
	public function __construct() {
		add_filter( 'block_editor_settings_all', [ $this, 'callback' ], PHP_INT_MAX );
	}

	/**
	 * Prevents unlocking blocks except in local environment.
	 * 
	 * Add `define( 'WP_ENVIRONMENT_TYPE', 'local' );` to wp-config.php to allow unlocking.
	 *
	 * @param array $settings The block editor settings.
	 * @return array
	 */
	public function callback( $settings ) {
		if ( 'local' === wp_get_environment_type() ) {
			return $settings;
		}

		$settings['canLockBlocks'] = false;

		return $settings;
	}
}
