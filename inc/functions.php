<?php
/**
 * Helper functions for the core plugin.
 *
 * @package RKV\Core
 */

namespace RKV\Core;

/**
 * Detects if the current request is an app page.
 * 
 * @param string $input_var The input variable to check. Default 'embedded'.
 * @param string $test The value to test against. Default 'app'.
 * @param int    $input_type The input type to check. Default INPUT_GET.
 * @param int    $filter The filter to apply. Default FILTER_SANITIZE_FULL_SPECIAL_CHARS.
 *
 * @return bool
 */
function is_app( $input_var = 'embedded', $test = 'app', $input_type = INPUT_GET, $filter = FILTER_SANITIZE_FULL_SPECIAL_CHARS ) {
	$input_var  = apply_filters( 'rkv_core_is_app_input_var', $input_var );
	$input_type = apply_filters( 'rkv_core_is_app_input_type', $input_type );
	$filter     = apply_filters( 'rkv_core_is_app_filter', $filter );
	$test       = apply_filters( 'rkv_core_is_app_test', $test );
	$is_app     = filter_input( $input_type, $input_var, $filter ) === $test;

	/**
	 * Filters whether the current request is an app page.
	 * 
	 * @param bool   $is_app     Whether the current request is an app page.
	 * @param string $input_var  The input variable to check.
	 * @param string $test       The value to test against.
	 * @param int    $input_type The input type to check.
	 * @param int    $filter     The filter to apply.
	 *
	 * @return bool True if the current request is an app page, false otherwise.
	 */
	return apply_filters( 'rkv_core_is_app', $is_app, $input_var, $test, $input_type, $filter );
}
