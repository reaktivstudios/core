<?php
/**
 * Base post type class.
 *
 * @package rkv-utilities
 */

namespace RKV\Utilities\Post_Type;

/**
 * Define the base class and associated methods.
 */
abstract class Base {

	/**
	 * Post type name.
	 *
	 * @var string
	 */
	protected $post_type_name;

	/**
	 * Post type args.
	 *
	 * @var array
	 */
	protected $post_type_args = [];

	/**
	 * Show in GraphQL.
	 *
	 * @var bool
	 */
	protected $show_in_graphql = false;

	/**
	 * GraphQL single name.
	 *
	 * @var string
	 */
	protected $graphql_single_name = '';

	/**
	 * GraphQL plural name.
	 *
	 * @var string
	 */
	protected $graphql_plural_name = '';

	/**
	 * Flag to remove the post type slug from permalinks.
	 * Defaults to false to keep the slug.
	 *
	 * @var bool
	 */
	protected $remove_slug = false;

	/**
	 * Class constructor.
	 *
	 * Called during init hook.
	 *
	 * @return void
	 */
	public function __construct() {
		// Make sure a post type name is defined.
		if ( empty( $this->post_type_name ) ) {
			return;
		}

		// Initialize the post type.
		add_action( 'init', [ $this, 'init' ] );

		// Action for adding custom fields to a post type with Fieldmanager.
		add_action( "fm_post_{$this->post_type_name}", [ $this, 'add_custom_fields' ] );

		// Action to register custom fields for the API.
		add_action( 'rest_api_init', [ $this, 'register_rest_fields' ] );

		// Action to register GraphQL fields.
		add_action( 'graphql_register_types', [ $this, 'register_graphql_fields' ] );

		// Add filters to remove the post slug from permalinks.
		add_filter( 'post_type_link', [ $this, 'remove_slug' ], 10, 2 );
		add_action( 'pre_get_posts', [ $this, 'add_post_names_to_main_query' ] );
	}

	/**
	 * Init callback.
	 *
	 * @return void
	 */
	public function init() {
		$this->initialize_post_type();
		$this->create_post_type();
	}

	/**
	 * Initialize the post type.
	 */
	abstract protected function initialize_post_type();

	/**
	 * Determine whether or not to remove the slug.
	 *
	 * @return bool
	 */
	protected function do_remove_slug() {
		$do_remove_slug = apply_filters( "rkv_post_type_remove_slug_{$this->post_type_name}", $this->remove_slug );

		return $do_remove_slug;
	}

	/**
	 * Remove the slug from published post permalinks.
	 * Only affect our custom post type, though.
	 *
	 * @param string  $post_link The permalink to the post.
	 * @param WP_Post $post      The post object.
	 *
	 * @return string
	 */
	public function remove_slug( $post_link, $post ) {
		$do_remove_slug = $this->do_remove_slug();

		if (
			$do_remove_slug
			&& $this->post_type_name === $post->post_type
			&& 'publish' === $post->post_status
		) {
			$post_link = str_replace( '/' . $post->post_type . '/', '/', $post_link );
		}

		return $post_link;
	}

	/**
	 * Have WordPress match postname to any of our public post types (post, page, race).
	 * All of our public post types can have /post-name/ as the slug, so they need to be unique across all posts.
	 * By default, WordPress only accounts for posts and pages where the slug is /post-name/.
	 *
	 * @param WP_Query $query The current query.
	 */
	public function add_post_names_to_main_query( $query ) {
		$do_remove_slug = $this->do_remove_slug();

		if ( ! $do_remove_slug ) {
			return;
		}

		// Bail if this is not the main query.
		if ( ! $query->is_main_query() ) {
			return;
		}

		// Bail if this query doesn't match our very specific rewrite rule.
		if ( ! isset( $query->query['page'] ) || 2 !== count( $query->query ) ) {
			return;
		}

		// Bail if we're not querying based on the post name.
		if ( empty( $query->query['name'] ) ) {
			return;
		}

		// Add CPT to the list of post types WP will include when it queries based on the post name.
		$post_types = $query->get( 'post_type', [ 'post', 'page' ] );

		if ( ! in_array( $this->post_type_name, $post_types, true ) ) {
			array_push( $post_types, $this->post_type_name );
			$query->set( 'post_type', $post_types );
		}
	}

	/**
	 * Create the post type.
	 *
	 * @return void
	 */
	protected function create_post_type() {
		$default_args = $this->get_default_post_type_args();

		if ( ! empty( $this->post_type_args['supports'] ) && in_array( 'block-editor', $this->post_type_args['supports'], true ) ) {
			$this->post_type_args['supports'] = wp_parse_args(
				$this->post_type_args['supports'],
				[
					'editor',
				]
			);

			$default_args['show_in_rest'] = true;
		}

		$this->post_type_args = wp_parse_args( $this->post_type_args, $default_args );

		if ( ! empty( $this->post_type_args ) ) {
			register_post_type( $this->post_type_name, $this->post_type_args );
		}
	}

	/**
	 * Get the default post type args.
	 *
	 * @return array Default args.
	 */
	protected function get_default_post_type_args() {
		$default_args = [
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => false,
			'can_export'          => true,
			'exclude_from_search' => true,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
			'show_in_rest'        => true,
		];

		if (
			$this->show_in_graphql &&
			! empty( $this->graphql_single_name ) &&
			! empty( $this->graphql_plural_name )
		) {
			$default_args['show_in_graphql']     = true;
			$default_args['graphql_single_name'] = $this->graphql_single_name;
			$default_args['graphql_plural_name'] = $this->graphql_plural_name;
		}

		return $default_args;
	}

	/**
	 * Add custom fields to the post type with Fieldmanager.
	 *
	 * @return void
	 */
	public function add_custom_fields() {}

	/**
	 * Register custom fields for the REST API.
	 *
	 * @return void
	 */
	public function register_rest_fields() {}

	/**
	 * Register custom fields for GraphQL.
	 *
	 * @return void
	 */
	public function register_graphql_fields() {}
}
