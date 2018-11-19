<?php
/**
 * Class LegacyGitHub
 *
 * @package Traduttore\Tests
 */

namespace Required\Traduttore\Tests\WebhookHandler;

use Required\Traduttore\Project;
use Required\Traduttore\Repository;
use Required\Traduttore\Tests\TestCase;
use \WP_REST_Request;

/**
 * Test cases for \Required\Traduttore\WebhookHandler\GitHub.
 */
class LegacyGitHub extends TestCase {
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
					'source_url_template' => 'https://github.com/wearerequired/traduttore/blob/master/%file%#L%line%',
				]
			)
		);
	}

	public function test_missing_event_header(): void {
		$request  = new WP_REST_Request( 'POST', '/github-webhook/v1/push-event' );
		$response = rest_get_server()->dispatch( $request );

		$this->assertErrorResponse( 'rest_forbidden', $response, 401 );
	}

	public function test_invalid_event_header(): void {
		$request = new WP_REST_Request( 'POST', '/github-webhook/v1/push-event' );
		$request->add_header( 'x-github-event', 'pull' );
		$response = rest_get_server()->dispatch( $request );

		$this->assertErrorResponse( 'rest_forbidden', $response, 401 );
	}

	public function test_ping_request(): void {
		$request = new WP_REST_Request( 'POST', '/github-webhook/v1/push-event' );
		$request->add_header( 'x-github-event', 'ping' );
		$response = rest_get_server()->dispatch( $request );

		$this->assertEquals( 200, $response->get_status() );
		$this->assertSame( [ 'result' => 'OK' ], $response->get_data() );
	}

	public function test_missing_signature(): void {
		$request = new WP_REST_Request( 'POST', '/github-webhook/v1/push-event' );
		$request->add_header( 'x-github-event', 'push' );
		$response = rest_get_server()->dispatch( $request );

		$this->assertErrorResponse( 'rest_forbidden', $response, 401 );
	}

	public function test_invalid_signature(): void {
		$request = new WP_REST_Request( 'POST', '/github-webhook/v1/push-event' );
		$request->set_body_params( [] );
		$signature = 'sha1=' . hash_hmac( 'sha1', $request->get_body(), 'foo' );
		$request->add_header( 'x-github-event', 'push' );
		$request->add_header( 'x-hub-signature', $signature );
		$response = rest_get_server()->dispatch( $request );

		$this->assertErrorResponse( 'rest_forbidden', $response, 401 );
	}

	public function test_invalid_branch(): void {
		$request = new WP_REST_Request( 'POST', '/github-webhook/v1/push-event' );
		$request->set_body_params(
			[
				'ref'        => 'refs/heads/master',
				'repository' => [
					'html_url'       => 'https://github.com/wearerequired/traduttore',
					'default_branch' => 'develop',
				],
			]
		);
		$signature = 'sha1=' . hash_hmac( 'sha1', $request->get_body(), 'traduttore-test' );
		$request->add_header( 'x-github-event', 'push' );
		$request->add_header( 'x-hub-signature', $signature );
		$response = rest_get_server()->dispatch( $request );

		$this->assertEquals( 200, $response->get_status() );
		$this->assertSame( [ 'result' => 'Not the default branch' ], $response->get_data() );
	}

	public function test_invalid_project(): void {
		$request = new WP_REST_Request( 'POST', '/github-webhook/v1/push-event' );
		$request->set_body_params(
			[
				'ref'        => 'refs/heads/master',
				'repository' => [
					'default_branch' => 'master',
					'full_name'      => 'wearerequired/not-traduttore',
					'html_url'       => 'https://github.com/wearerequired/not-traduttore',
					'ssh_url'        => 'git@github.com:wearerequired/not-traduttore.git',
					'clone_url'      => 'https://github.com/wearerequired/not-traduttore.git',
					'url'            => 'https://github.com/wearerequired/not-traduttore',
					'private'        => false,
				],
			]
		);
		$signature = 'sha1=' . hash_hmac( 'sha1', $request->get_body(), 'traduttore-test' );
		$request->add_header( 'x-github-event', 'push' );
		$request->add_header( 'x-hub-signature', $signature );
		$response = rest_get_server()->dispatch( $request );

		$this->assertErrorResponse( 404, $response );
	}

	public function test_valid_project(): void {
		$request = new WP_REST_Request( 'POST', '/github-webhook/v1/push-event' );
		$request->set_body_params(
			[
				'ref'        => 'refs/heads/master',
				'repository' => [
					'full_name'      => 'wearerequired/traduttore',
					'default_branch' => 'master',
					'html_url'       => 'https://github.com/wearerequired/traduttore',
					'ssh_url'        => 'git@github.com:wearerequired/traduttore.git',
					'clone_url'      => 'https://github.com/wearerequired/traduttore.git',
					'url'            => 'https://github.com/wearerequired/traduttore',
					'private'        => false,
				],
			]
		);
		$signature = 'sha1=' . hash_hmac( 'sha1', $request->get_body(), 'traduttore-test' );
		$request->add_header( 'x-github-event', 'push' );
		$request->add_header( 'x-hub-signature', $signature );
		$response = rest_get_server()->dispatch( $request );

		$this->assertEquals( 200, $response->get_status() );
		$this->assertSame( [ 'result' => 'OK' ], $response->get_data() );
		$this->assertSame( Repository::VCS_TYPE_GIT, $this->project->get_repository_vcs_type() );
		$this->assertSame( Repository::TYPE_GITHUB, $this->project->get_repository_type() );
		$this->assertSame( 'wearerequired/traduttore', $this->project->get_repository_name() );
		$this->assertSame( 'https://github.com/wearerequired/traduttore', $this->project->get_repository_url() );
		$this->assertSame( 'git@github.com:wearerequired/traduttore.git', $this->project->get_repository_ssh_url() );
		$this->assertSame( 'https://github.com/wearerequired/traduttore.git', $this->project->get_repository_https_url() );
		$this->assertSame( 'public', $this->project->get_repository_visibility() );
	}

	public function test_valid_project_custom_webhook_secret(): void {
		$secret = 'Sup3rS3cr3tPassw0rd';

		$this->project->set_repository_webhook_secret( $secret );

		$request = new WP_REST_Request( 'POST', '/github-webhook/v1/push-event' );
		$request->set_body_params(
			[
				'ref'        => 'refs/heads/master',
				'repository' => [
					'full_name'      => 'wearerequired/traduttore',
					'default_branch' => 'master',
					'html_url'       => 'https://github.com/wearerequired/traduttore',
					'ssh_url'        => 'git@github.com:wearerequired/traduttore.git',
					'clone_url'      => 'https://github.com/wearerequired/traduttore.git',
					'url'            => 'https://github.com/wearerequired/traduttore',
					'private'        => false,
				],
			]
		);
		$signature = 'sha1=' . hash_hmac( 'sha1', $request->get_body(), $secret );
		$request->add_header( 'x-github-event', 'push' );
		$request->add_header( 'x-hub-signature', $signature );
		$response = rest_get_server()->dispatch( $request );

		$this->assertEquals( 200, $response->get_status() );
		$this->assertSame( [ 'result' => 'OK' ], $response->get_data() );
		$this->assertSame( Repository::VCS_TYPE_GIT, $this->project->get_repository_vcs_type() );
		$this->assertSame( Repository::TYPE_GITHUB, $this->project->get_repository_type() );
		$this->assertSame( 'wearerequired/traduttore', $this->project->get_repository_name() );
		$this->assertSame( 'https://github.com/wearerequired/traduttore', $this->project->get_repository_url() );
		$this->assertSame( 'git@github.com:wearerequired/traduttore.git', $this->project->get_repository_ssh_url() );
		$this->assertSame( 'https://github.com/wearerequired/traduttore.git', $this->project->get_repository_https_url() );
		$this->assertSame( 'public', $this->project->get_repository_visibility() );
	}
}
