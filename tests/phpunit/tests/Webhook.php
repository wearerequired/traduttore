<?php
/**
 * Class GetPushResources
 *
 * @package H2push
 */

namespace Required\Traduttore\Tests;

use \GP_UnitTestCase;
use \WP_REST_Request;

/**
 *  Test cases for incoming webhooks from GitHub.
 */
class Webhook extends GP_UnitTestCase {
	/**
	 * @var \GP_Project
	 */
	protected $project;

	public function setUp() {
		parent::setUp();

		$this->project = $this->factory->project->create( [
			'name'                => 'Sample Project',
			'source_url_template' => 'https://github.com/wearerequired/traduttore/blob/master/%file%#L%line%',
		] );
	}

	/**
	 * @see WP_Test_REST_TestCase
	 */
	protected function assertErrorResponse( $code, $response, $status = null ) {
		if ( is_a( $response, 'WP_REST_Response' ) ) {
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

	public function test_missing_event_header() {
		$request = new WP_REST_Request( 'POST', '/github-webhook/v1/push-event' );
		$response = rest_get_server()->dispatch( $request );

		$this->assertErrorResponse( 'rest_forbidden', $response, 401 );
	}

	public function test_invalid_event_header() {
		$request = new WP_REST_Request( 'POST', '/github-webhook/v1/push-event' );
		$request->add_header( 'x-github-event', 'pull' );
		$response = rest_get_server()->dispatch( $request );

		$this->assertErrorResponse( 'rest_forbidden', $response, 401 );
	}

	public function test_ping_request() {
		$request = new WP_REST_Request( 'POST', '/github-webhook/v1/push-event' );
		$request->add_header( 'x-github-event', 'ping' );
		$response = rest_get_server()->dispatch( $request );

		$this->assertEquals( 200, $response->get_status() );
		$this->assertSame( [ 'result' => 'OK' ], $response->get_data() );
	}

	public function test_missing_signature() {
		$request = new WP_REST_Request( 'POST', '/github-webhook/v1/push-event' );
		$request->add_header( 'x-github-event', 'push' );
		$response = rest_get_server()->dispatch( $request );

		$this->assertErrorResponse( 'rest_forbidden', $response, 401 );
	}

	public function test_invalid_signature() {
		$request = new WP_REST_Request( 'POST', '/github-webhook/v1/push-event' );
		$request->set_body_params( [] );
		$signature = 'sha1=' . hash_hmac( 'sha1', $request->get_body(), 'foo' );
		$request->add_header( 'x-github-event', 'push' );
		$request->add_header( 'x-hub-signature', $signature );
		$response = rest_get_server()->dispatch( $request );

		$this->assertErrorResponse( 'rest_forbidden', $response, 401 );
	}

	public function test_valid_signature_but_invalid_payload() {
		$request = new WP_REST_Request( 'POST', '/github-webhook/v1/push-event' );
		$request->set_body_params( [] );
		$signature = 'sha1=' . hash_hmac( 'sha1', $request->get_body(), 'traduttore-test' );
		$request->add_header( 'x-github-event', 'push' );
		$request->add_header( 'x-hub-signature', $signature );
		$response = rest_get_server()->dispatch( $request );

		$this->assertErrorResponse( 400, $response );
	}

	public function test_invalid_branch() {
		$request = new WP_REST_Request( 'POST', '/github-webhook/v1/push-event' );
		$request->set_body_params( [
			'ref'        => 'refs/heads/master',
			'repository' => [
				'html_url'       => 'https://github.com/wearerequired/traduttore',
				'default_branch' => 'develop',
			],
		] );
		$signature = 'sha1=' . hash_hmac( 'sha1', $request->get_body(), 'traduttore-test' );
		$request->add_header( 'x-github-event', 'push' );
		$request->add_header( 'x-hub-signature', $signature );
		$response = rest_get_server()->dispatch( $request );

		$this->assertEquals( 200, $response->get_status() );
		$this->assertSame( [ 'result' => 'Not the default branch' ], $response->get_data() );
	}

	public function test_invalid_project() {
		$request = new WP_REST_Request( 'POST', '/github-webhook/v1/push-event' );
		$request->set_body_params( [
			'ref'        => 'refs/heads/master',
			'repository' => [
				'default_branch' => 'master',
				'html_url'       => 'https://github.com/wearerequired/not-traduttore',
				'url'            => 'https://github.com/wearerequired/not-traduttore',
			],
		] );
		$signature = 'sha1=' . hash_hmac( 'sha1', $request->get_body(), 'traduttore-test' );
		$request->add_header( 'x-github-event', 'push' );
		$request->add_header( 'x-hub-signature', $signature );
		$response = rest_get_server()->dispatch( $request );

		$this->assertErrorResponse( 404, $response );
	}

	public function test_valid_project() {
		$request = new WP_REST_Request( 'POST', '/github-webhook/v1/push-event' );
		$request->set_body_params( [
			'ref'        => 'refs/heads/master',
			'repository' => [
				'default_branch' => 'master',
				'html_url'       => 'https://github.com/wearerequired/traduttore',
				'url'            => 'https://github.com/wearerequired/traduttore',
			],
		] );
		$signature = 'sha1=' . hash_hmac( 'sha1', $request->get_body(), 'traduttore-test' );
		$request->add_header( 'x-github-event', 'push' );
		$request->add_header( 'x-hub-signature', $signature );
		$response = rest_get_server()->dispatch( $request );

		$this->assertEquals( 200, $response->get_status() );
		$this->assertSame( [ 'result' => 'OK' ], $response->get_data() );
	}
}
