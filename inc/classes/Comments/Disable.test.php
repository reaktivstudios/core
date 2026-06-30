<?php
/**
 * Unit tests for Disable class.
 *
 * @package rkv-utilities
 */

namespace RKV\Utilities\Comments;

use WP_Mock\Tools\TestCase;

/**
 * Class Disable_Test
 *
 * @covers \RKV\Utilities\Comments\Disable
 */
class Disable_Test extends TestCase {

	/**
	 * Set up before class.
	 *
	 * @return void
	 */
	public static function setUpBeforeClass(): void {
		require_once dirname( __DIR__ ) . '/Disable.php';
	}

	/**
	 * Set up test environment.
	 *
	 * @return void
	 */
	public function setUp(): void {
		// Initialize WP_Mock.
		WP_Mock::setUp();
	}

	/**
	 * Tear down test environment.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		// Tear down WP_Mock.
		WP_Mock::tearDown();
	}

	/**
	 * Test constructor.
	 *
	 * @return void
	 */
	public function test_constructor() {
		$instance = new Disable();
		$this->assertInstanceOf( Disable::class, $instance );
	}

	/**
	 * Test actions added.
	 *
	 * @return void
	 */
	public function test_actions_added() {
		$instance = new Disable();
		\WP_Mock::expectActionAdded( 'widgets_init', array( $instance, 'disable_rc_widget' ) );

		$instance->init();
		WP_Mock::assertHooksAdded();
	}

	/**
	 * Test filter_wp_headers method.
	 *
	 * @return void
	 */
	public function test_filter_wp_headers() {
		$instance         = new Disable();
		$filtered_headers = $instance->filter_wp_headers(
			[
				'X-Pingback' => 'http://example.com/xmlrpc.php',
				'Link'       => '<http://example.com/wp-json/>; rel="https://api.w.org/"',
			]
		);

		$this->assertArrayNotHasKey( 'X-Pingback', $filtered_headers );
	}
}
