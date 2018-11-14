<?php
/**
 * Class GitLab
 *
 * @package Traduttore\Tests\WebhookHandler
 */

namespace Required\Traduttore\Tests\WebhookHandler;

use Required\Traduttore\Project;
use Required\Traduttore\Repository;
use Required\Traduttore\Tests\TestCase;
use \WP_REST_Request;

/**
 * Test cases for \Required\Traduttore\WebhookHandler\GitLab.
 */
class GitLab extends TestCase {
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
					'source_url_template' => 'https://gitlab.com/wearerequired/traduttore/blob/master/%file%#L%line%',
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
		$request->add_header( 'x-gitlab-event', 'Foo Event' );
		$response = rest_get_server()->dispatch( $request );

		$this->assertErrorResponse( 'rest_forbidden', $response, 401 );
	}

	public function test_missing_token(): void {
		$request = new WP_REST_Request( 'POST', '/traduttore/v1/incoming-webhook' );
		$request->add_header( 'x-gitlab-event', 'Push Hook' );
		$response = rest_get_server()->dispatch( $request );

		$this->assertErrorResponse( 'rest_forbidden', $response, 401 );
	}

	public function test_invalid_token(): void {
		$request = new WP_REST_Request( 'POST', '/traduttore/v1/incoming-webhook' );
		$request->set_body_params( [] );
		$request->add_header( 'x-gitlab-event', 'Push Hook' );
		$request->add_header( 'x-gitlab-token', 'foo' );
		$response = rest_get_server()->dispatch( $request );

		$this->assertErrorResponse( 'rest_forbidden', $response, 401 );
	}

	public function test_invalid_branch(): void {
		$request = new WP_REST_Request( 'POST', '/traduttore/v1/incoming-webhook' );
		$request->set_body_params(
			[
				'ref'     => 'refs/heads/master',
				'project' => [
					'default_branch' => 'develop',
					'homepage'       => 'https://gitlab.com/wearerequired/traduttore',
				],
			]
		);
		$request->add_header( 'x-gitlab-event', 'Push Hook' );
		$request->add_header( 'x-gitlab-token', 'traduttore-test' );
		$response = rest_get_server()->dispatch( $request );

		$this->assertEquals( 200, $response->get_status() );
		$this->assertSame( [ 'result' => 'Not the default branch' ], $response->get_data() );
	}

	public function test_invalid_project(): void {
		$request = new WP_REST_Request( 'POST', '/traduttore/v1/incoming-webhook' );
		$request->set_body_params(
			[
				'ref'     => 'refs/heads/master',
				'project' => [
					'default_branch'      => 'master',
					'path_with_namespace' => 'foo/bar',
					'homepage'            => 'https://gitlab.com/wearerequired/not-traduttore',
					'http_url'            => 'https://gitlab.com/wearerequired/not-traduttore.git',
					'ssh_url'             => 'git@gitlab.com/wearerequired/not-traduttore.git',
					'visibility_level'    => 0,
				],
			]
		);
		$request->add_header( 'x-gitlab-event', 'Push Hook' );
		$request->add_header( 'x-gitlab-token', 'traduttore-test' );
		$response = rest_get_server()->dispatch( $request );

		$this->assertErrorResponse( 404, $response );
	}

	public function test_valid_project(): void {
		$request = new WP_REST_Request( 'POST', '/traduttore/v1/incoming-webhook' );
		$request->set_body_params(
			[
				'ref'     => 'refs/heads/master',
				'project' => [
					'default_branch'      => 'master',
					'path_with_namespace' => 'wearerequired/traduttore',
					'homepage'            => 'https://gitlab.com/wearerequired/traduttore',
					'http_url'            => 'https://gitlab.com/wearerequired/traduttore.git',
					'ssh_url'             => 'git@gitlab.com:wearerequired/traduttore.git',
					'visibility_level'    => 0,
				],
			]
		);
		$request->add_header( 'x-gitlab-event', 'Push Hook' );
		$request->add_header( 'x-gitlab-token', 'traduttore-test' );
		$response = rest_get_server()->dispatch( $request );

		$this->assertEquals( 200, $response->get_status() );
		$this->assertSame( [ 'result' => 'OK' ], $response->get_data() );
		$this->assertSame( Repository::VCS_TYPE_GIT, $this->project->get_repository_vcs_type() );
		$this->assertSame( Repository::TYPE_GITLAB, $this->project->get_repository_type() );
		$this->assertSame( 'wearerequired/traduttore', $this->project->get_repository_name() );
		$this->assertSame( 'https://gitlab.com/wearerequired/traduttore', $this->project->get_repository_url() );
		$this->assertSame( 'git@gitlab.com:wearerequired/traduttore.git', $this->project->get_repository_ssh_url() );
		$this->assertSame( 'https://gitlab.com/wearerequired/traduttore.git', $this->project->get_repository_https_url() );
		$this->assertSame( 'public', $this->project->get_repository_visibility() );
	}

	public function test_valid_project_custom_webhook_secret(): void {
		$secret = 'Sup3rS3cr3tPassw0rd';

		$this->project->set_repository_webhook_secret( $secret );

		$request = new WP_REST_Request( 'POST', '/traduttore/v1/incoming-webhook' );
		$request->set_body_params(
			[
				'ref'     => 'refs/heads/master',
				'project' => [
					'default_branch'      => 'master',
					'path_with_namespace' => 'wearerequired/traduttore',
					'homepage'            => 'https://gitlab.com/wearerequired/traduttore',
					'http_url'            => 'https://gitlab.com/wearerequired/traduttore.git',
					'ssh_url'             => 'git@gitlab.com:wearerequired/traduttore.git',
					'visibility_level'    => 0,
				],
			]
		);
		$request->add_header( 'x-gitlab-event', 'Push Hook' );
		$request->add_header( 'x-gitlab-token', $secret );
		$response = rest_get_server()->dispatch( $request );

		$this->assertEquals( 200, $response->get_status() );
		$this->assertSame( [ 'result' => 'OK' ], $response->get_data() );
		$this->assertSame( Repository::VCS_TYPE_GIT, $this->project->get_repository_vcs_type() );
		$this->assertSame( Repository::TYPE_GITLAB, $this->project->get_repository_type() );
		$this->assertSame( 'wearerequired/traduttore', $this->project->get_repository_name() );
		$this->assertSame( 'https://gitlab.com/wearerequired/traduttore', $this->project->get_repository_url() );
		$this->assertSame( 'git@gitlab.com:wearerequired/traduttore.git', $this->project->get_repository_ssh_url() );
		$this->assertSame( 'https://gitlab.com/wearerequired/traduttore.git', $this->project->get_repository_https_url() );
		$this->assertSame( 'public', $this->project->get_repository_visibility() );
	}
}
