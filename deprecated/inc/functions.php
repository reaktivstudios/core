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


/**
 * Gets the current environment.
 *
 * @return string The current environment (e.g., 'production', 'staging', 'development', etc.).
 */
function get_env() {
	$env = wp_get_environment_type();
	$src = 'wp';

	if ( defined( 'VIP_GO_APP_ENVIRONMENT' ) && VIP_GO_APP_ENVIRONMENT ) {
		$env = VIP_GO_APP_ENVIRONMENT;
		$src = 'wpvip';
	}

	$pantheon_env = filter_input( INPUT_ENV, 'PANTHEON_ENVIRONMENT', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
	if ( $pantheon_env ) {
		$env = $pantheon_env;
		$src = 'pantheon';
	}

	if ( function_exists( 'is_wpe' ) && is_wpe() ) {
		$src = 'wpe';
	}

	/**
	 * Filters the current environment.
	 * 
	 * @param string $env The current environment.
	 * @param string $src The source of the environment detection (e.g., 'wp', 'wpvip', 'pantheon', 'wpe').
	 * @return string
	 */
	return apply_filters( 'rkv_core_get_env', $env, $src );
}

/**
 * Checks if the current environment matches the given environment(s).
 *
 * @param  string|array[string] $env The environment(s) to check against.
 * @return boolean
 */
function is_env( $env ) {
	if ( is_string( $env ) ) {
		$env = [ $env ];
	}

	return in_array( get_env(), $env, true );
}

