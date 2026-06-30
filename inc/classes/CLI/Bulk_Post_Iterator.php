<?php
/**
 * CLI class to iterate over posts in bulk.
 * 
 * This will handle:
 *  - pagination
 *  - progress bars
 *  - memory cleanup 
 * for bulk operations on posts.
 *
 * @package rkv-utilities
 */

namespace RKV\Utilities\CLI;

use stdClass;
use WP_CLI;

/**
 * Bulk Post Iterator class.
 */
abstract class Bulk_Post_Iterator extends Base {
	/**
	 * Get the query args.
	 *
	 * @param int $page The page number.
	 * @return array
	 */
	abstract protected function get_query_args( $page = 1 ): array;

	/**
	 * Process a single post.
	 *
	 * @param \WP_Post $post The post object.
	 */
	abstract protected function process_post( $post );

	/**
	 * This is a dry run. Do not make changes.
	 *
	 * @var boolean
	 */
	protected bool $dry_run = false;

	/**
	 * Indicates to output verbose data.
	 * 
	 * @var bool
	 */
	protected bool $verbose = false;

	/**
	 * The progress bar.
	 * 
	 * @var mixed|\cli\progress\Bar|\WP_CLI\NoOp|stdClass|null
	 */
	protected $progress_bar = null;

	/**
	 * Errors.
	 * 
	 * @var array
	 */
	protected array $errors = [];

	/**
	 * Callback.
	 *
	 * @param array $args       Command arguments.
	 * @param array $assoc_args Command associative arguments.
	 */
	public function callback( $args, $assoc_args ) {
		$this->set_args( $args, $assoc_args );

		$this->dry_run = $this->assoc_args['dry-run'] ?? $this->dry_run;
		$this->verbose = $this->assoc_args['verbose'] ?? $this->verbose;

		$this->iterate_posts();
		$this->output();
	}

	/**
	 * Check for orphaned posts.
	 */
	protected function iterate_posts() {
		$page          = 1;
		$max_num_pages = 2;
		$found_posts   = 0;

		$this->start_bulk_operation();
		$this->make_progress_bar();

		do {
			$query = new \WP_Query( $this->get_query_args( $page ) );

			$max_num_pages = $query->max_num_pages;
			$found_posts   = $query->found_posts;

			if ( empty( $found_posts ) ) {
				WP_CLI::warning( 'No posts found.' );
			}

			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					$post = $query->post;

					$this->process_post( $post );

					$this->tick_progress_bar();
				}
			} else {
				// Break now if no posts found.
				$page = $max_num_pages + 1;
			}

			$total_processed = $query->post_count + ( ( $page - 1 ) * 99 );
			WP_CLI::line( 'Processed ' . $total_processed . ' of ' . $query->found_posts . ' posts.' );

			++$page;

			$this->in_memory_cleanup();
		} while ( $page < $max_num_pages );

		$this->end_bulk_operation();
		$this->finish_progress_bar();
	}

	/**
	 * Start the bulk operation.
	 */
	protected function start_bulk_operation() {
		if ( class_exists( 'WPCOM_VIP_CLI_Command' ) ) {
			\WPCOM_VIP_CLI_Command::start_bulk_operation();
		}
	}

	/**
	 * Finish the bulk operation.
	 */
	protected function end_bulk_operation() {
		if ( class_exists( 'WPCOM_VIP_CLI_Command' ) ) {
			\WPCOM_VIP_CLI_Command::end_bulk_operation();
		}
	}

	/**
	 * Clean up memory.
	 */
	protected function in_memory_cleanup() {
		if ( class_exists( 'WPCOM_VIP_CLI_Command' ) ) {
			\WPCOM_VIP_CLI_Command::vip_inmemory_cleanup();
		}
	}

	/**
	 * Start the progress bar.
	 */
	protected function make_progress_bar() {
		$query_args = $this->get_query_args();
		$query_args['posts_per_page'] = 1;

		$query = new \WP_Query( $query_args );

		$found_posts = $query->found_posts;

		$this->progress_bar = \WP_CLI\Utils\make_progress_bar( __( 'Processing posts', 'rkv-utilities' ), $found_posts );
	}

	/**
	 * Tick the progress bar.
	 */
	protected function tick_progress_bar() {
		if ( $this->progress_bar ) {
			$this->progress_bar->tick();
		}
	}

	/**
	 * End the progress bar.
	 */
	protected function finish_progress_bar() {
		if ( $this->progress_bar ) {
			$this->progress_bar->finish();
		}
	}

	/**
	 * Output results.
	 */
	protected function output() {
		if ( ! empty( $this->errors ) ) {
			WP_CLI::warning( 
				sprintf(
					// Translators: %d is the number of errors encountered during processing.
					__( 'Encountered %d errors during processing.', 'core' ),
					count( $this->errors )
				)
			);

			if ( $this->verbose ) {
				WP_CLI::line( 'Errors:' );
				foreach ( $this->errors as $error ) {
					WP_CLI::line( '- ' . $error );
				}
			}
		} else {
			WP_CLI::success( 'No errors encountered.' );
		}
	}
}
