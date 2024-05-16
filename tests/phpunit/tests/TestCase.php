<?php
/**
 * Class TestCase
 */

namespace Required\Traduttore\Tests;

use GP_UnitTest_Factory;
use GP_UnitTestCase;
use WP_Error;
use WP_REST_Response;

/**
 * Base TestCase class.
 */
class TestCase extends GP_UnitTestCase {
	/**
	 * Fetches the factory object for generating WordPress fixtures.
	 *
	 * @return \GP_UnitTest_Factory The fixture factory.
	 */
	protected static function factory(): GP_UnitTest_Factory {
		static $factory = null;
		if ( ! $factory ) {
			$factory = new GP_UnitTest_Factory();
		}
		return $factory;
	}

	/**
	 * @see WP_Test_REST_TestCase
	 */
	protected function assertErrorResponse( mixed $code, WP_REST_Response|WP_Error $response, mixed $status = null ): void {
		if ( $response instanceof WP_REST_Response ) {
			$response = $response->as_error();
		}

		$this->assertInstanceOf( 'WP_Error', $response );
		$this->assertSame( $code, $response->get_error_code() );
		if ( null !== $status ) {
			$data = $response->get_error_data();
			$this->assertIsArray( $data );
			$this->assertArrayHasKey( 'status', $data );
			$this->assertSame( $status, $data['status'] );
		}
	}
}
