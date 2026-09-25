<?php
/**
 * The Disable comments class.
 *
 * @package rkv-utilities
 */

namespace RKV\Utilities\Comments;

use RKV\Core\Comments\Disable as New_Disable;

if ( class_exists( __NAMESPACE__ . '\Disable' ) ) {
	return;
}

\RKV\Utilities\deprecated_class( __NAMESPACE__, 'Disable' );


/**
 * Completely removes comments.
 */
class Disable extends New_Disable {}
