<?php
/**
 * Gets templates for posts in specific categories.
 *
 * @package rkv-utilities
 */

namespace RKV\Utilities\FSE;

/**
 * Category post template class.
 */
class Category_Post_Template {

	/**
	 * Categories supporting a single post template.
	 *
	 * @var array
	 */
	private array $category_templates = [
	];

	/**
	 * Add the actions.
	 */
	public function __construct() {
		if ( empty( $this->category_templates ) ) {
			return;
		}

		add_filter( 'pre_get_block_templates', [ $this, 'callback' ], PHP_INT_MAX, 3 );
	}

	/**
	 * Checks if single and if in defined categories then switches template.
	 *
	 * @param WP_Block_Template[]|null $pre Return an array of block templates to short-circuit the default query,
	 *                                                  or null to allow WP to run its normal queries.
	 * @param array                    $query {
	 *                       Arguments to retrieve templates. All arguments are optional.
	 *
	 *     @type string[] $slug__in  List of slugs to include.
	 *     @type int      $wp_id     Post ID of customized template.
	 *     @type string   $area      A 'wp_template_part_area' taxonomy value to filter by (for 'wp_template_part' template type only).
	 *     @type string   $post_type Post type to get the templates for.
	 * }
	 * @param string                   $template_type 'wp_template' or 'wp_template_part'.
	 *
	 * @return mixed
	 */
	public function callback( $pre, $query, $template_type ) {
		if ( 'wp_template' !== $template_type || ! is_single() ) {
			return $pre;
		}

		$categories = array_keys( $this->category_templates );


		if ( isset( $query['slug__in'] ) && is_array( $query['slug__in'] ) && in_array( 'single', $query['slug__in'], true ) ) {
			foreach ( $categories as $category ) {
				if ( $this->in_category( $category ) ) {
					$pre = get_block_templates( [ 'slug__in' => [ $this->category_templates[ $category ] ] ], $template_type );
				}
			}
		}
		return $pre;
	}

	/**
	 * Tests if any of a post's assigned categories are descendants of target categories
	 *
	 * @param int|array  $categories The target categories.
	 * @param int|object $_post     The post. Omit to test the current post in the Loop or main query.
	 *
	 * @return bool True if at least 1 of the post's categories is a descendant of any of the target categories
	 */
	protected function in_category( $categories, $_post = null ) {
		foreach ( (array) $categories as $category ) {
			// get_term_children() accepts integer ID only.
			$category    = get_category_by_slug( $category );
			$descendants = get_term_children( $category->term_id, 'category' );

			if ( $descendants && in_category( $descendants, $_post ) ) {
				return true;
			}
		}

		return in_category( $categories, $_post );
	}
}
