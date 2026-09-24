<?php
/**
 * Find posts and import meta from CSV.
 *
 * @package rkv-utilities
 */

namespace RKV\Utilities\CLI;

if ( ! defined( '\WP_CLI' ) ) {
	return;
}

/**
 * Adds meta_import CLI Command.
 */
class Meta_Import extends Base {
	/**
	 * The CLI commands that will be registered.
	 *
	 * Must be extended.
	 *
	 * @var array
	 */
	public $cli_commands = [
		'rkv_meta_import',
	];

	/**
	 * Default args.
	 *
	 * @var array
	 */
	public $defaults = [
		'file'      => '',
		'post-type' => 'post',
	];

	/**
	 * Stores CSV data.
	 *
	 * @var array
	 */
	private array $csv_data = [];

	/**
	 * Post count.
	 * 
	 * @var int
	 */
	private int $post_count = 0;

	/**
	 * Errors.
	 * 
	 * @var array
	 */
	private array $errors = [];

	/**
	 * Indicates the field should be overwritten if present.
	 * 
	 * @var bool
	 */
	private bool $overwrite = false;

	/**
	 * Import meta from CSV.
	 *
	 * ## OPTIONS
	 *
	 * [--file=<file>]
	 * : The CSV file to import. This can be a local file path or a URL.
	 * 
	 * Paths are relative to the uploads directory. 
	 * For example, if the file is located at `wp-content/uploads/posts.csv`, you can specify `--file=posts.csv`.
	 * 
	 * [--post-type=<post-type>]
	 * : The post type to import. Defaults to 'post'.
	 * 
	 * [--overwrite]
	 * : Whether to overwrite existing meta. Defaults to false.
	 * 
	 * [--dry-run]
	 * : If set, the command will simulate the import process without making any changes to the database. This is useful for testing and verifying the CSV data before performing the actual import.
	 * 
	 * [--verbose]
	 * : If set, the command will output detailed information about the import process.
	 *
	 * @param array $args The arguments.
	 * @param array $assoc_args Associative array of args.
	 */
	public function callback( $args, $assoc_args ) {
		$this->set_args( $args, $assoc_args );

		if ( empty( $this->assoc_args['file'] ) ) {
			\WP_CLI::error( 'Please provide a file to import.' );
		}

		if ( ! post_type_exists( $this->assoc_args['post-type'] ) ) {
			\WP_CLI::error( 'The specified post type does not exist: ' . $this->assoc_args['post-type'] );
		}

		$this->overwrite = isset( $this->assoc_args['overwrite'] );

		$this->set_csv();

		$this->process_csv();
		$this->output();
	}

	/**
	 * Set the CSV data.
	 */
	private function set_csv() {
		$file = $this->assoc_args['file'];

		if ( filter_var( $file, FILTER_VALIDATE_URL ) ) {
			if ( function_exists( 'vip_safe_wp_remote_get' ) ) {
				$response = vip_safe_wp_remote_get( $file );
			} else {
				$response = wp_remote_get( $file );
			}

			if ( is_wp_error( $response ) ) {
				\WP_CLI::error( 'Error fetching the file: ' . $response->get_error_message() );
			}
			$body = wp_remote_retrieve_body( $response );
			if ( empty( $body ) ) {
				\WP_CLI::error( 'The file is empty.' );
			}

			foreach ( explode( "\n", $body ) as $line ) {
				if ( ! empty( $line ) ) {
					$this->csv_data[] = str_getcsv( $line, ',', '"', '\\' );
				}
			}
		} else {
			$path = trailingslashit( wp_get_upload_dir()['basedir'] ) . ltrim( $file, '/' );
			if ( ! file_exists( $path ) ) {
				\WP_CLI::error( 'The file does not exist: ' . $path );
			}
			if ( ! is_readable( $path ) ) {
				\WP_CLI::error( 'The file is not readable: ' . $path );
			}

			$handle = fopen( $path, 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
			if ( ( $handle ) !== false ) {
				do {
					$data = fgetcsv( $handle, 1000, ',' );
					if ( $data ) {
						$this->csv_data[] = $data;
					}
				} while ( false !== ( $data ) );

				fclose( $handle );
			} else {
				\WP_CLI::error( 'Error opening the file: ' . $path );
			}
		}

		if ( empty( $this->csv_data ) ) {
			\WP_CLI::error( 'No data found in the CSV file.' );
		}
	}

	/**
	 * Process the CSV.
	 */
	private function process_csv() {
		$fields    = array_shift( $this->csv_data ); // Get the header row.
		$post_name = array_search( 'post_name', $fields, true );
		$url_field = array_search( 'url', $fields, true );
		$id        = array_search( 'ID', $fields, true );

		if ( false === $id ) {
			$id = array_search( 'post_id', $fields, true );
		}

		if ( false === $post_name && false === $url_field && false === $id ) {
			\WP_CLI::error( 'The CSV file must contain a "post_name", "url", or "ID" column.' );
		}

		$progress = \WP_CLI\Utils\make_progress_bar( 'Processing posts', count( $this->csv_data ) );

		foreach ( $this->csv_data as $row ) {
			$progress->tick();
			$post = null;

			if ( false !== $id && ! empty( $row[ $id ] ) ) {
				$post = get_post( $row[ $id ] );
				if ( ! $post ) {
					$this->errors[] = 'Post not found for ID: ' . $row[ $id ];
					continue;
				}
			} else {
				// Work with post_name OR url.
				if ( false === $post_name || empty( $row[ $post_name ] ) ) {
					$url = $row[ $url_field ] ?? '';
					if ( empty( $url ) ) {
						$this->errors[] = 'Missing post_name and url for row: ' . implode( ',', $row );
						continue;
					}

					$row[ $post_name ] = basename( wp_parse_url( $url, PHP_URL_PATH ) );
				}

				$post = get_page_by_path( $row[ $post_name ], OBJECT, $this->assoc_args['post-type'] ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.get_page_by_path_get_page_by_path
			}

			if ( ! $post ) {
				$this->errors[] = 'Post not found for post_name: ' . $row[ $post_name ];
				continue;
			}

			++$this->post_count;

			foreach ( $fields as $index => $field ) {
				if ( 'post_name' === $field ) {
					continue;
				}

				if ( ! isset( $this->assoc_args['dry-run'] ) ) {
					if ( $this->overwrite || ! get_post_meta( $post->ID, $field, true ) ) {
						update_post_meta( $post->ID, $field, $row[ $index ] );
					}
				}
			}
		}
		$progress->finish();
	}


	/**
	 * Output the data.
	 */
	private function output() {
		if ( ! empty( $this->errors ) ) {
			\WP_CLI::warning( sprintf( '%d errors were encountered during the import process:', count( $this->errors ) ) );

			if ( isset( $this->assoc_args['verbose'] ) ) {
				foreach ( $this->errors as $error ) {
					\WP_CLI::line( '- ' . $error );
				}
			}
		}

		\WP_CLI::success( sprintf( 'Import process completed. %d posts were processed.', $this->post_count ) );
	}
}
