<?php
/**
 * Adds an authenticated endpoint to reset FSE templates to the theme.
 * 
 * @package rkv-utilities
 */

namespace RKV\Utilities\FSE;

/**
 * Reset to theme class.
 */
class Reset_To_Theme {

	/**
	 * The endpoint.
	 *
	 * @var string
	 */
	private string $endpoint = 'rkv-reset-fse';

	/**
	 * Add the actions.
	 */
	public function __construct() {
		if ( ! isset( $_GET[ $this->endpoint ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		add_action( 'admin_init', [ $this, 'callback' ], PHP_INT_MAX, 3 );
	}

	/**
	 * Checks if current user is an admin and then resets the FSE templates to the theme.
	 *
	 * @return void
	 */
	public function callback() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$templates = new \WP_Query(
			[
				'post_type'      => [ 'wp_template', 'wp_template_part' ],
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'post_status'    => 'any',
				'no_found_rows'  => true,
			]
		);

		if ( ! $templates->have_posts() ) {
			return;
		}

		foreach ( $templates->posts as $template ) {
			wp_delete_post( $template, true );
		}

		wp_safe_redirect( admin_url( 'index.php' ) );
		exit;
	}
}
