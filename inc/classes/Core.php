<?php
/**
 * The core RKV utilities class.
 *
 * @package rkv-utilities
 */

namespace RKV\Utilities;

/**
 * Initialize all other classes here.
 */
class Core {
	/**
	 * The classes or callables.
	 *
	 * Provide fully qualified classes or callbacks
	 * to instantiate the various objects for
	 * the utilities.
	 *
	 * @var array
	 */
	private $classes = [
		// More to come.


		// FSE.
		// '\RKV\Utilities\FSE\Category_Post_Template',
		// '\RKV\Utilities\FSE\Lock_Down',
		// '\RKV\Utilities\FSE\Reset_To_Theme',

		// // Post Types.
		'\RKV\Utilities\Post_Type\CTAs',

		// // REST.
		// '\RKV\Utilities\REST\Load_More',
		// '\RKV\Utilities\REST\Post_Select',
		// '\RKV\Utilities\REST\Search',
	];

	/**
	 * Files that should be loaded.
	 *
	 * @var array
	 */
	private $files = [];

	/**
	 * Calls the classes callbacks and initializes the objects.
	 */
	public function __construct() {
		$this->init_classes();
		$this->require_files();
	}

	/**
	 * Initialize the classes.
	 *
	 * @return void
	 */
	private function init_classes() {
		/**
		 * Allow filtering the classes.
		 *
		 * @param array $classes The classes or callables.
		 */
		$classes = apply_filters( 'rkv_utility_classes', $this->classes );

		foreach ( $classes as $class ) {
			if ( is_callable( $class ) ) {
				call_user_func( $class );
			} elseif ( class_exists( $class ) ) {
				$obj = new $class();

				if ( method_exists( $obj, 'run' ) ) {
					$obj->run();
				}
			}
		}
	}

	/**
	 * Require the files.
	 *
	 * @return void
	 */
	private function require_files() {
		/**
		 * Allow filtering the files.
		 *
		 * @param array $files The files.
		 */
		$files = apply_filters( 'rkv_utility_files', $this->files );

		foreach ( $files as $file ) {
			$file_path = trailingslashit( RKV_UTILITIES_PATH ) . $file;

			if ( file_exists( $file_path ) ) {
				require_once $file_path;
			}
		}
	}
}
