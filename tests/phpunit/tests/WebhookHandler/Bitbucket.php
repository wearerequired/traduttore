<?php
/**
 * Class Bitbucket
 *
 * @package Traduttore\Tests\WebhookHandler
 */

namespace Required\Traduttore\Tests\WebhookHandler;

use \GP_UnitTestCase;
use Required\Traduttore\Project;
use Required\Traduttore\Repository;
use \WP_Error;
use \WP_REST_Request;
use \WP_REST_Response;

/**
 * Test cases for \Required\Traduttore\WebhookHandler\Bitbucket.
 */
class Bitbucket extends GP_UnitTestCase {
	/**
	 * @var Project
	 */
	protected $project;

	public function setUp() {
		parent::setUp();

		$this->project = new Project(
			$this->factory->project->create(
				[
					'name'                => 'Sample Project',
					'source_url_template' => 'https://bitbucket.org/wearerequired/traduttore/blob/master/%file%#L%line%',
				]
			)
		);
	}

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

	public function test_missing_event_header(): void {
		$request  = new WP_REST_Request( 'POST', '/traduttore/v1/incoming-webhook' );
		$response = rest_get_server()->dispatch( $request );

		$this->assertErrorResponse( 'rest_forbidden', $response, 401 );
	}

	public function test_invalid_event_header(): void {
		$request = new WP_REST_Request( 'POST', '/traduttore/v1/incoming-webhook' );
		$request->add_header( 'x-event-key', 'pull' );
		$response = rest_get_server()->dispatch( $request );

		$this->assertErrorResponse( 'rest_forbidden', $response, 401 );
	}

	public function test_invalid_signature(): void {
		$request = new WP_REST_Request( 'POST', '/traduttore/v1/incoming-webhook' );
		$request->set_body_params( [] );
		$signature = 'sha256=' . hash_hmac( 'sha256', $request->get_body(), 'foo' );
		$request->add_header( 'x-event-key', 'repo:push' );
		$request->add_header( 'x-hub-signature', $signature );
		$response = rest_get_server()->dispatch( $request );

		$this->assertErrorResponse( 'rest_forbidden', $response, 401 );
	}

	public function test_missing_signature_is_valid(): void {
		$request = new WP_REST_Request( 'POST', '/traduttore/v1/incoming-webhook' );
		$request->set_body_params(
			[
				'ref'        => 'refs/heads/master',
				'repository' => [
					'links'      => [
						'html' => [
							'href' => 'https://bitbucket.org/wearerequired/not-traduttore',
						],
					],
					'full_name'  => 'wearerequired/not-traduttore',
					'scm'        => 'git',
					'is_private' => false,
				],
			]
		);
		$request->add_header( 'x-event-key', 'repo:push' );
		$response = rest_get_server()->dispatch( $request );

		$this->assertErrorResponse( 404, $response );
	}

	public function test_invalid_project(): void {
		$request = new WP_REST_Request( 'POST', '/traduttore/v1/incoming-webhook' );
		$request->set_body_params(
			[
				'ref'        => 'refs/heads/master',
				'repository' => [
					'links'      => [
						'html' => [
							'href' => 'https://bitbucket.org/wearerequired/not-traduttore',
						],
					],
					'full_name'  => 'wearerequired/not-traduttore',
					'scm'        => 'git',
					'is_private' => false,
				],
			]
		);
		$signature = 'sha256=' . hash_hmac( 'sha256', $request->get_body(), 'traduttore-test' );
		$request->add_header( 'x-event-key', 'repo:push' );
		$request->add_header( 'x-hub-signature', $signature );
		$response = rest_get_server()->dispatch( $request );

		$this->assertErrorResponse( 404, $response );
	}

	public function test_valid_project(): void {
		$request = new WP_REST_Request( 'POST', '/traduttore/v1/incoming-webhook' );
		$request->set_body_params(
			[
				'ref'        => 'refs/heads/master',
				'repository' => [
					'links'      => [
						'html' => [
							'href' => 'https://bitbucket.org/wearerequired/traduttore',
						],
					],
					'full_name'  => 'wearerequired/traduttore',
					'scm'        => 'git',
					'is_private' => false,
				],
			]
		);
		$signature = 'sha256=' . hash_hmac( 'sha256', $request->get_body(), 'traduttore-test' );
		$request->add_header( 'x-event-key', 'repo:push' );
		$request->add_header( 'x-hub-signature', $signature );
		$response = rest_get_server()->dispatch( $request );

		$this->assertEquals( 200, $response->get_status() );
		$this->assertSame( [ 'result' => 'OK' ], $response->get_data() );
		$this->assertSame( Repository::VCS_TYPE_GIT, $this->project->get_repository_vcs_type() );
		$this->assertSame( Repository::TYPE_BITBUCKET, $this->project->get_repository_type() );
		$this->assertSame( 'wearerequired/traduttore', $this->project->get_repository_name() );
		$this->assertSame( 'https://bitbucket.org/wearerequired/traduttore', $this->project->get_repository_url() );
		$this->assertSame( 'git@bitbucket.org:wearerequired/traduttore.git', $this->project->get_repository_ssh_url() );
		$this->assertSame( 'https://bitbucket.org/wearerequired/traduttore.git', $this->project->get_repository_https_url() );
		$this->assertSame( 'public', $this->project->get_repository_visibility() );
	}
}
