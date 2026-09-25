<?php
/**
 * CTA post type class.
 *
 * @package rkv-utilities
 */

namespace RKV\Utilities\Post_Type;

use RKV\Core\Post_Type\CTAs as New_CTAs;

if ( class_exists( __NAMESPACE__ . '\CTAs' ) ) {
	return;
}

\RKV\Utilities\deprecated_class( __NAMESPACE__, 'CTAs' );

/**
 * Define the CTA class and associated methods.
 */
class CTAs extends New_CTAs {}
