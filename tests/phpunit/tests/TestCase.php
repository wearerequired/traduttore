<?php
/**
 * Class TestCase
 *
 * @package Traduttore\Tests
 */

namespace Required\Traduttore\Tests;

use GP_UnitTestCase;
use WP_Error;
use WP_REST_Response;

/**
 * Base TestCase class.
 */
class TestCase extends GP_UnitTestCase {
	/**
	 * @see WP_Test_REST_TestCase
	 *
	 * @param mixed                     $code
	 * @param WP_REST_Response|WP_Error $response
	 * @param mixed                     $status
	 */
	protected function assertErrorResponse( $code, $response, $status = null ): void {
		if ( $response instanceof WP_REST_Response ) {
			$response = $response->as_error();
		}

		$this->assertInstanceOf( 'WP_Error', $response );
		$this->assertEquals( $code, $response->get_error_code() );
		if ( null !== $status ) {
			$data = $response->get_error_data();
			$this->assertArrayHasKey( 'status', $data );
			$this->assertEquals( $status, $data['status'] );
		}
	}
}
