<?php
/**
 * Disable Jetpack Modules.
 * 
 * @package rkv-theme
 */
 
namespace RKV\Utilities\Jetpack;

/**
 * Class to manage Jetpack modules.
 */
class Modules {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'jetpack_get_available_modules', [ $this, 'disable_modules' ] );
	}

	/**
	 * Disable specific Jetpack modules.
	 *
	 * @param array $modules Array of active Jetpack modules.
	 * @return array Modified array of active Jetpack modules.
	 */
	public function disable_modules( $modules ) {
		$modules_to_disable = [
			'blaze',
			'comments',
			'comment-likes',
			'contact-form',
			'copy-post',
			'google-fonts',
			'gravatar-hovercards',
			'latex',
			'likes',
			'monitor',
			'notes',
			'post-list',
			'seo-tools',
			'sitemaps',
			'subscriptions',
			'vaultpress',
			'widgets',
			'wordads',
		];
		
		foreach ( $modules_to_disable as $module ) {
			unset( $modules[ $module ] );
		}

		return $modules;
	}
}