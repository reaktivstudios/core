<?php
/**
 * Deprecated helper functions.
 * 
 * @package rkv-core
 */

namespace RKV\Utilities;

/**
 * Throws a deprecation notice.
 *
 * @param  string $old_namespace The namespace.
 * @param  string $class_name    The class.
 * @return void
 */
function deprecated_class( $old_namespace, $class_name ) {
	_deprecated_class( 
		$old_namespace . '\\' . $class_name,
		'2.0.0',
		str_replace(
			'Utilities',
			'Core',
			$old_namespace . '\\' . $class_name
		)
	);
}
