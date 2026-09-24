<?php
/**
 * Base class for a Shadow Taxonomy.
 *
 * @package rkv-theme
 */

namespace RKV\Utilities\Taxonomy;

/**
 * Shadow Taxonomy class.
 *
 * This class is used to create a shadow taxonomy for a post type.
 * To use, create your post type first, and then use this class to create a shadow taxonomy for it.
 *
 * Example Usage
 * ```php
 * // Include the class.
 * class Custom_Taxonomy extends \RKV\Utilities\Taxonomy\Shadow_Taxonomy {
 *    protected $post_type_name = 'rkv-example-post-type';
 *    protected $taxonomy_name = 'rkv-example-taxonomy';
 *
 *    protected function initialize_taxonomy() {
 *       $this->taxonomy_args = [
 *           'label'     => __( 'Taxonomy Name', 'rkv-utilities' ),
 *           'labels'    => [
 *               'name'          => _x( 'Taxonimies', 'Post Type General Name', 'rkv-utilities' ),
 *               'singular_name' => _x( 'Taxonomy', 'Post Type Singular Name', 'rkv-utilities' ),
 *           ],
 *           'hierarchical'    => false,
 *       ]
 *     }
 * }
 *
 * // Instantiate the class.
 * new Custom_Taxonomy();
 *
 * You can then use it to grab terms or posts depending on context.
 * ```php
 * $taxonomy = new \RKV\Utilities\Taxonomy\Custom_Taxonomy();
 * $post_id = $taxonomy->get_post_id_from_term_id( $term_id );
 * $term_id = $taxonomy->get_term_id_from_post_id( $post_id );
 * ```
 *
 * If you want to get a list of posts for all of the terms assocaited with a given post, you can use the get_related_posts function:
 * ```php
 * $taxonomy      = new \RKV\Utilities\Taxonomy\Custom_Taxonomy();
 * $related_posts = $taxonomy->get_related_posts( $post_id );
 * ```
 */
class Shadow_Taxonomy extends Base {

	/**
	 * Post type name to associate with the taxonomy.
	 *
	 * @var string
	 */
	protected $post_type_name;

	/**
	 * Setup the taxonomy.
	 */
	protected function initialize_taxonomy() {}

	/**
	 * Setup the taxonomy args and hooks.
	 */
	public function run() {
		parent::run();

		// Create a new term when posts are created.
		add_action( 'save_post_' . $this->post_type_name, [ $this, 'create_term_on_new_post' ], 10, 3 );

		// Remove the term when it is deleted.
		add_action( 'delete_post', [ $this, 'remove_related_company_term' ] );

		// One time process to add the terms for existing terms. This is when you enable an existing taxonomy as a shadow taxonomy on a new post type.
		add_action( 'admin_init', [ $this, 'add_existing_company_terms' ], PHP_INT_MAX );
	}


	/**
	 * Create a new term for each Company.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 * @param bool    $update  Whether this is an existing post being updated.
	 */
	public function create_term_on_new_post( $post_id, $post, $update ) {
		if ( 'auto-draft' === $post->post_status ) {
			return;
		}

		$term = $this->get_term_id_from_post_id( $post_id );

		if ( ! empty( $term ) ) {
			$this->create_related_term( $post );
		} else {
			// If the post and the term already have the same title and slug, we don't need to update the term.
			if ( $this->post_type_already_in_sync( $term, $post ) ) {
				return;
			}

			wp_update_term(
				$term->term_id,
				$this->taxonomy_name,
				[
					'name' => $post->post_title,
					'slug' => $post->post_name,
				]
			);
		}
	}

	/**
	 * Function will get the term ID from the post ID.
	 *
	 * @param int $post_id The Post ID.
	 *
	 * @return int|bool Returns the term ID if found, or false if not found.
	 */
	public function get_term_id_from_post_id( $post_id ) {
		return get_post_meta( $post_id, 'shadow_term_id', true );
	}

	/**
	 * Function will get the post ID from the term ID.
	 *
	 * @param int $term_id The Term ID.
	 *
	 * @return int|bool Returns the post ID if found, or false if not found.
	 */
	public function get_post_id_from_term_id( $term_id ) {
		return get_term_meta( $term_id, 'shadow_post_id', true );
	}

	/**
	 * Function finds the associated shadow post for a given term slug. This function is required due
	 * to some possible recursion issues if we only check for posts by ID.
	 *
	 * @param string $term_slug The Term Object.
	 * @param string $post_type The Post Type Slug.
	 *
	 * @return bool|object Returns false if no post is found, or the Post Object if one is found.
	 */
	public function get_shadow_post_from_slug( $term_slug, $post_type ) {
		$post = new \WP_Query(
			[
				'post_type'      => $post_type,
				'posts_per_page' => 1,
				'post_status'    => 'publish',
				'name'           => $term_slug,
				'no_found_rows'  => true,
			]
		);

		if ( empty( $post->posts ) || is_wp_error( $post ) ) {
			return false;
		}

		return $post->posts[0];
	}

	/**
	 * Function checks to see if the current term and its associated post have the same
	 * title and slug. While we generally rely on term and post meta to track association,
	 * its important that these two value stay synced.
	 *
	 * @param object $term The Term Object.
	 * @param object $post The $_POST array.
	 *
	 * @return bool Return true if a match is found, or false if no match is found.
	 */
	public function post_type_already_in_sync( $term, $post ) {
		if ( isset( $term->slug ) && isset( $post->post_name ) ) {
			if ( $term->name === $post->post_title && $term->slug === $post->post_name ) {
				return true;
			}
		} elseif ( $term->name === $post->post_title ) {
				return true;
		}

		return false;
	}

	/**
	 * Create a new term for the Company.
	 *
	 * @param object $post Full post object.
	 */
	private function create_related_term( $post ) {
		$term = wp_insert_term(
			$post->post_title,
			$this->taxonomy_name,
			[
				'slug' => $post->post_name,
			]
		);

		if ( ! is_wp_error( $term ) ) {
			update_term_meta( $term['term_id'], 'shadow_post_id', $post->ID );
			update_post_meta( $post->ID, 'shadow_term_id', $term['term_id'] );
		}

		return $term;
	}

	/**
	 * Remove the term from the Company when it is deleted.
	 *
	 * @param int $post_id Post ID.
	 */
	public function remove_related_company_term( $post_id ) {
		if ( $this->post_type_name === get_post_type( $post_id ) ) {
			$term = $this->get_term_id_from_post_id( $post_id );
			if ( ! $term ) {
				return false;
			}

			wp_delete_term( $term->term_id, $this->taxonomy_name );
		}
	}

	/**
	 * Add the terms for existing Companies.
	 */
	public function add_existing_post_terms_terms() {
		if ( get_option( 'rkv_add_existing_' . $this->post_type_name . '_terms' ) ) {
			return;
		}

		$posts = get_posts(
			[
				'post_type'      => $this->post_type_name,
				'posts_per_page' => -1,
			]
		);

		foreach ( $posts as $post_object ) {
			$this->create_related_term( $post_object->ID );
		}

		update_option( 'rkv_add_existing_' . $this->post_type_name . '_terms', true );
	}

	/**
	 * Function will get all related posts for a given post ID. The function
	 * essentially converts all the attached shadow term relations into the actual associated
	 * posts.
	 *
	 * @param object $post The Post Object.
	 *
	 * @return array|bool Returns false or an are of post Objects if any are found.
	 */
	public function get_related_posts( $post ) {
		$posts = [];
		$terms     = get_the_terms( $post, $this->taxonomy_name );

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$posts[] = $this->get_post_id_from_term_id( $term->term_id );
			}
		}

		return $posts;
	}
}
