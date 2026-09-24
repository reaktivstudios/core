<?php
/**
 * Adds familiar helpers and methods for setting up a custom post type.
 *
 * @package rkv-utilities
 */

namespace RKV\Utilities\Post_Type;

use RKV\Core\Post_Type\Base as New_Base;

if ( class_exists( __NAMESPACE__ . '\Base' ) ) {
	return;
}

\RKV\Utilities\deprecated_class( __NAMESPACE__, 'Base' );


/**
 * Extend this class and then initiate the class to add the custom post type to your theme.
 *
 * The only required property is the post type name, which is used to register the post type.
 *
 * Use the `initialize_post_type` method to set up the post type arguments and override the default arguments.
 *
 * Example usage:
 * ```php
 * // Include the class.
 * class Custom_Post_Type extends Base {
 *    protected $post_type_name = 'rkv-cta';
 *
 *    protected function initialize_post_type() {
 *       $this->post_type_args = [
 *           'label'     => __( 'CTAs', 'rkv-utilities' ),
 *           'labels'    => [
 *               'name'          => _x( 'CTAs', 'Post Type General Name', 'rkv-utilities' ),
 *               'singular_name' => _x( 'CTA', 'Post Type Singular Name', 'rkv-utilities' ),
 *           ],
 *           'supports'  => [ 'title', 'editor', 'revisions' ],
 *           'public'    => false,
 *       ]
 *     }
 * }
 *
 * // Instantiate the class.
 * new Custom_Post_Type();
 * ```
 */
abstract class Base extends New_Base {}
