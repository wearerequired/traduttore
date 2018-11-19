<?php
/**
 * Class Bitbucket
 *
 * @package Traduttore\Tests\WebhookHandler
 */

namespace Required\Traduttore\Tests\WebhookHandler;

use Required\Traduttore\Project;
use Required\Traduttore\Repository;
use Required\Traduttore\Tests\TestCase;
use \WP_REST_Request;

/**
 * Test cases for \Required\Traduttore\WebhookHandler\Bitbucket.
 */
class Bitbucket extends TestCase {
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

	public function test_valid_mercurial_project(): void {
		$this->project->set_repository_vcs_type( Repository::VCS_TYPE_HG );

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
		$this->assertSame( Repository::VCS_TYPE_HG, $this->project->get_repository_vcs_type() );
		$this->assertSame( Repository::TYPE_BITBUCKET, $this->project->get_repository_type() );
		$this->assertSame( 'wearerequired/traduttore', $this->project->get_repository_name() );
		$this->assertSame( 'https://bitbucket.org/wearerequired/traduttore', $this->project->get_repository_url() );
		$this->assertSame( 'hg@bitbucket.org/wearerequired/traduttore', $this->project->get_repository_ssh_url() );
		$this->assertSame( 'https://bitbucket.org/wearerequired/traduttore', $this->project->get_repository_https_url() );
		$this->assertSame( 'public', $this->project->get_repository_visibility() );
	}
}
