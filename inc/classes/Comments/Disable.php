<?php
/**
 * The Disable comments class.
 *
 * @package rkv-utilities
 */

namespace RKV\Utilities\Comments;

/**
 * Completely removes comments.
 */
class Disable {

	/**
	 * Adds the filters and actions required to disable comments
	 */
	public function run() {
		$disable_comments = apply_filters( 'rkv_disable_comments', true );

		if ( ! $disable_comments ) {
			return;
		}

		add_action( 'widgets_init', array( $this, 'disable_rc_widget' ) );
		add_filter( 'wp_headers', array( $this, 'filter_wp_headers' ) );
		add_action( 'template_redirect', array( $this, 'filter_query' ), 9 );

		add_action( 'template_redirect', array( $this, 'filter_admin_bar' ) );
		add_action( 'admin_init', array( $this, 'filter_admin_bar' ) );

		add_action( 'wp_loaded', array( $this, 'init_wploaded_filters' ) );
	}

	/**
	 * Additional filters to disable comments.
	 */
	public function init_wploaded_filters() {
		$post_types = get_post_types( array( 'public' => true ) );
		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $type ) {
				// we need to know what native support was for later.
				if ( post_type_supports( $type, 'comments' ) ) {
					remove_post_type_support( $type, 'comments' );
					remove_post_type_support( $type, 'trackbacks' );
				}
			}
		}

		add_filter( 'comments_array', '__return_empty_array', 20, 2 );
		add_filter( 'comments_open', '__return_false', 20, 2 );
		add_filter( 'pings_open', '__return_false', 20, 2 );

		add_action( 'admin_menu', array( $this, 'filter_admin_menu' ), 9999 );
		add_action( 'admin_print_styles-index.php', array( $this, 'admin_css' ) );
		add_action( 'admin_print_styles-profile.php', array( $this, 'admin_css' ) );
		add_action( 'wp_dashboard_setup', array( $this, 'filter_dashboard' ) );
		add_filter( 'pre_option_default_pingback_flag', '__return_zero' );

		add_action( 'template_redirect', array( $this, 'check_comment_template' ) );
		add_filter( 'feed_links_show_comments_feed', '__return_false' );
	}

	/**
	 * Ensure scripts/header output is disabled.
	 */
	public function check_comment_template() {
		if ( is_singular() ) {
			wp_deregister_script( 'comment-reply' );

			remove_action( 'wp_head', 'feed_links_extra', 3 );
		}
	}

	/**
	 * Remove the X-Pingback HTTP header
	 *
	 * @param array $headers The headers.
	 *
	 * @return array The headers.
	 */
	public function filter_wp_headers( $headers ) {
		unset( $headers['X-Pingback'] );
		return $headers;
	}

	/**
	 * Issue a 403 for all comment feed requests.
	 */
	public function filter_query() {
		if ( is_comment_feed() ) {
			wp_die( esc_html__( 'Comments are closed.', 'rkv-utilities' ), '', array( 'response' => 403 ) );
		}
	}

	/**
	 * Remove comment links from the admin bar.
	 */
	public function filter_admin_bar() {
		if ( is_admin_bar_showing() ) {
			// Remove comments links from admin bar.
			remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 60 );
		}
	}

	/**
	 * Removes the comment pages.
	 */
	public function filter_admin_menu() {
		global $pagenow;

		if ( in_array( $pagenow, array( 'comment.php', 'edit-comments.php', 'options-discussion.php' ), true ) ) {
			wp_die( esc_html__( 'Comments are closed.', 'rkv-utilities' ), '', array( 'response' => 403 ) );
		}

		remove_menu_page( 'edit-comments.php' );
		remove_submenu_page( 'options-general.php', 'options-discussion.php' );
	}

	/**
	 * Removes the comments dashboard widget.
	 */
	public function filter_dashboard() {
		remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
	}

	/**
	 * Hides comment elements that may have missed removal.
	 */
	public function admin_css() {
		echo '<style>
			#dashboard_right_now .comment-count,
			#dashboard_right_now .comment-mod-count,
			#latest-comments,
			#welcome-panel .welcome-comments,
			.user-comment-shortcuts-wrap {
				display: none !important;
			}
		</style>';
	}

	/**
	 * Removes the recent comments widget.
	 */
	public function disable_rc_widget() {
		unregister_widget( 'WP_Widget_Recent_Comments' );
	}
}
