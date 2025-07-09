<?php
/**
 * Hides the FSE menu in production.
 * 
 * @package rkv-utilities
 */

namespace RKV\Utilities\FSE;

/**
 * Hide FSE Menu.
 */
class Hide_Menu {

	/**
	 * Add the actions.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'callback' ], PHP_INT_MAX );
	}

	/**
	 * Hides the FSE menu if not a local environment.
	 * 
	 * Add `define( 'WP_ENVIRONMENT_TYPE', 'local' );` to wp-config.php to enable FSE menu item.
	 *
	 * @return void
	 */
	public function callback() {
		if ( 'local' === wp_get_environment_type() ) {
			return;
		}

		remove_submenu_page( 'themes.php', 'site-editor.php' );
		remove_submenu_page( 'themes.php', 'site-editor.php?postType=wp_template_part&amp;path=/wp_template_part/all' );
	}
}
