<?php
/**
 * CLI Class.
 *
 * @package rkv-utilities
 */

namespace RKV\Utilities\CLI;

if ( ! defined( '\WP_CLI' ) ) {
	return;
}

/**
 * Adds CLI commands and serves as an extendable template.
 */
abstract class Base {
	/**
	 * The CLI commands that will be registered.
	 *
	 * Must be extended.
	 *
	 * @var array
	 */
	public $cli_commands = [];

	/**
	 * The combined associative array of args.
	 *
	 * @var array
	 */
	public $assoc_args = [];

	/**
	 * List of items used for the named args.
	 *
	 * @var array
	 */
	public $args = [];

	/**
	 * Default args, should be set via child class.
	 *
	 * @var array
	 */
	public $defaults = [];

	/**
	 * Register the CLI commands.
	 */
	public function __construct() {
		foreach ( $this->cli_commands as $cli_command ) {
			\WP_CLI::add_command( $cli_command, [ $this, 'callback' ] );
		}
	}

	/**
	 * Callback for CLI command. 
	 * 
	 * Extend this and include CLI details in comment.
	 *
	 * @param array $args The arguments.
	 * @param array $assoc_args Associative array of args.
	 * @return void
	 */
	abstract public function callback( $args, $assoc_args );

	/**
	 * Sets the $assoc_args property.
	 *
	 * Uses the $args parameter to set the named args in the $assoc_args property
	 *
	 * @param array $args       The arguments.
	 * @param array $assoc_args Associative array of args.
	 */
	public function set_args( $args, $assoc_args ) {
		$this->assoc_args = array_merge( $this->defaults, $assoc_args );

		$count = 0;

		if ( ! empty( $this->args ) ) {
			foreach ( $this->args as $name ) {
				$this->assoc_args[ $name ] = empty( $args[ $count ] ) ? '' : $args[ $count++ ];
			}
		}
	}

	/**
	 * Get specific args from asociative array.
	 *
	 * @param array $defaults The associative array to extract.
	 *
	 * @return array
	 */
	public function extract_args( $defaults ) {
		$out = [];
		foreach ( $defaults as $key => $value ) {
			$out[ $key ] = \WP_CLI\Utils\get_flag_value( $this->assoc_args, $key, $value );
		}
		return $out;
	}

	/**
	 * Checks to see if a flag is set and returns true if so otherwise false.
	 *
	 * @param string $flag The item to check.
	 *
	 * @return bool
	 */
	public function get_flag_value( $flag ) {
		return empty( $this->assoc_args[ $flag ] ) ? false : true;
	}
}
