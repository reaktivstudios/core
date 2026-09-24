<?php
/**
 * CTA post type class.
 *
 * @package rkv-core
 */

namespace RKV\Core\Post_Type;

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
			'label'     => __( 'CTAs', 'rkv-core' ),
			'labels'    => [
				'name'          => _x( 'CTAs', 'Post Type General Name', 'rkv-core' ),
				'singular_name' => _x( 'CTA', 'Post Type Singular Name', 'rkv-core' ),
			],
			'supports'  => [ 'title', 'editor', 'revisions' ],
			'public'    => false,
			'menu_icon' => 'dashicons-megaphone',
		];
	}
}
