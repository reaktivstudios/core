<?php
/**
 * Base class for a Shadow Taxonomy.
 *
 * @package rkv-theme
 */

namespace RKV\Utilities\Taxonomy;

use RKV\Core\Taxonomy\Shadow_Taxonomy as New_Shadow_Taxonomy;

if ( class_exists( __NAMESPACE__ . '\Shadow_Taxonomy' ) ) {
	return;
}

\RKV\Utilities\deprecated_class( __NAMESPACE__, 'Shadow_Taxonomy' );

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
class Shadow_Taxonomy extends New_Shadow_Taxonomy {}
