<?php
/**
 * Base taxonomy class.
 *
 * @package rkv-utilities
 */

namespace RKV\Utilities\Taxonomy;

use RKV\Core\Taxonomy\Base as New_Base;

if ( class_exists( __NAMESPACE__ . '\Base' ) ) {
	return;
}

\RKV\Utilities\deprecated_class( __NAMESPACE__, 'Base' );


/**
 * Define the base class and associated methods.
 *
 * This class is used to create a custom taxonomy.
 *
 * Example usage:
 *
 * ```php
 * // Include the class.
 * class Custom_Taxonomy extends \RKV\Utilities\Taxonomy\Base {
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
 * $taxonomy = new Custom_Taxonomy();
 * $taxonomy->run();
 *
 * ```
 */
abstract class Base extends New_Base {}
