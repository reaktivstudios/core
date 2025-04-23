<?php
/**
 * CTA post type class.
 *
 * @package rkv-utilities
 */

namespace RKV\Utilities\Post_Type;

/**
 * Define the CTA class and associated methods.
 */
class CTAs extends Base {

	/**
	 * Post type name.
	 *
	 * @var string
	 */
	protected $post_type_name = 'rkv-cta';


	/**
	 * Initialize the post type.
	 */
	protected function initialize_post_type() {
		$this->post_type_args = [
			'label'     => __( 'CTAs', 'rkv-utilities' ),
			'labels'    => [
				'name'          => _x( 'CTAs', 'Post Type General Name', 'rkv-utilities' ),
				'singular_name' => _x( 'CTA', 'Post Type Singular Name', 'rkv-utilities' ),
			],
			'supports'  => [ 'title', 'editor', 'revisions' ],
			'public'    => false,
			'menu_icon' => 'dashicons-megaphone',
		];
	}
}
