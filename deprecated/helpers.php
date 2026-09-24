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

/**
 * Autoload the deprecated classes.
 * 
 * Checks if the class belongs to the deprecated namespace.
 * Then checks if it could be loaded with another autoloader.
 * Finally loads the deprecated class if the file exists.
 *
 * @param  string $class_name The class.
 * @return void
 */
function deprecated_autoloader( $class_name = '' ) {
	// Unregister the deprecated autoloader to prevent recursion.
	spl_autoload_unregister( __NAMESPACE__ . '\\deprecated_autoloader' );
	if ( 0 === strpos( $class_name, 'RKV\\Utilities\\' ) ) {
		if ( ! class_exists( $class_name ) ) {
			$file_base = str_replace( '\\', '/', $class_name );
			$file      = sprintf(
				'%1$s/deprecated/inc/classes/%2$s.php',
				RKV_CORE_PATH,
				str_replace( 'RKV\\Utilities\\', '', $file_base )
			);
			
			if ( file_exists( $file ) ) {
				require_once $file;
			}
		}
	}

	// Always re-register the deprecated autoloader.
	spl_autoload_register( __NAMESPACE__ . '\\deprecated_autoloader' );
}

// Invoking this will register the deprecated autoloader.
deprecated_autoloader();
