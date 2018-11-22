<?php
/**
 * Class SourceForge
 *
 * @package Traduttore\Tests\WebhookHandler
 */

namespace Required\Traduttore\Tests\WebhookHandler;

use Required\Traduttore\Project;
use Required\Traduttore\Repository;
use Required\Traduttore\Tests\TestCase;
use \WP_REST_Request;

/**
 * Test cases for \Required\Traduttore\WebhookHandler\SourceForge.
 */
class SourceForge extends TestCase {
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
					'source_url_template' => 'https://sourceforge.net/p/traduttore/code/%file%#l%line%',
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
		$request  = new WP_REST_Request( 'POST', '/traduttore/v1/incoming-webhook' );
		$response = rest_get_server()->dispatch( $request );

		$this->assertErrorResponse( 'rest_forbidden', $response, 401 );
	}

	public function test_missing_token(): void {
		$request  = new WP_REST_Request( 'POST', '/traduttore/v1/incoming-webhook' );
		$response = rest_get_server()->dispatch( $request );

		$this->assertErrorResponse( 'rest_forbidden', $response, 401 );
	}

	public function test_invalid_token(): void {
		$request = new WP_REST_Request( 'POST', '/traduttore/v1/incoming-webhook' );
		$request->set_body_params( [] );
		$request->add_header( 'x-allura-signature', 'foo' );
		$response = rest_get_server()->dispatch( $request );

		$this->assertErrorResponse( 'rest_forbidden', $response, 401 );
	}

	public function test_invalid_branch(): void {
		$request = new WP_REST_Request( 'POST', '/traduttore/v1/incoming-webhook' );
		$request->set_body_params(
			[
				'ref'        => 'refs/heads/develop',
				'repository' => [
					'full_name' => '/p/wearerequired/',
					'name'      => 'Traduttore',
					'url'       => 'https://sourceforge.net/p/traduttore',
				],
			]
		);
		$signature = 'sha1=' . hash_hmac( 'sha1', $request->get_body(), 'traduttore-test' );
		$request->add_header( 'x-allura-signature', $signature );
		$response = rest_get_server()->dispatch( $request );

		$this->assertEquals( 200, $response->get_status() );
		$this->assertSame( [ 'result' => 'Not the default branch' ], $response->get_data() );
	}

	public function test_invalid_project(): void {
		$request = new WP_REST_Request( 'POST', '/traduttore/v1/incoming-webhook' );
		$request->set_body_params(
			[
				'ref'        => 'refs/heads/master',
				'repository' => [
					'full_name' => '/p/wearerequired/not-traduttore',
					'name'      => 'Not Traduttore',
					'url'       => 'https://sourceforge.net/p/not-traduttore',
				],
			]
		);
		$signature = 'sha1=' . hash_hmac( 'sha1', $request->get_body(), 'traduttore-test' );
		$request->add_header( 'x-allura-signature', $signature );
		$response = rest_get_server()->dispatch( $request );

		$this->assertErrorResponse( 404, $response );
	}

	public function test_valid_project(): void {
		$request = new WP_REST_Request( 'POST', '/traduttore/v1/incoming-webhook' );
		$request->set_body_params(
			[
				'ref'        => 'refs/heads/master',
				'repository' => [
					'full_name' => '/p/wearerequired/traduttore',
					'name'      => 'Traduttore',
					'url'       => 'https://sourceforge.net/p/traduttore',
				],
			]
		);
		$signature = 'sha1=' . hash_hmac( 'sha1', $request->get_body(), 'traduttore-test' );
		$request->add_header( 'x-allura-signature', $signature );
		$response = rest_get_server()->dispatch( $request );

		$this->assertEquals( 200, $response->get_status() );
		$this->assertSame( [ 'result' => 'OK' ], $response->get_data() );
		$this->assertNull( $this->project->get_repository_vcs_type() );
		$this->assertSame( Repository::TYPE_SOURCEFORGE, $this->project->get_repository_type() );
		$this->assertSame( 'wearerequired/traduttore', $this->project->get_repository_name() );
		$this->assertSame( 'https://sourceforge.net/p/traduttore', $this->project->get_repository_url() );
		$this->assertNull( $this->project->get_repository_ssh_url() );
		$this->assertNull( $this->project->get_repository_https_url() );
		$this->assertSame( 'public', $this->project->get_repository_visibility() );
	}

	public function test_valid_project_custom_webhook_secret(): void {
		$secret = 'Sup3rS3cr3tPassw0rd';

		$this->project->set_repository_webhook_secret( $secret );

		$request = new WP_REST_Request( 'POST', '/traduttore/v1/incoming-webhook' );
		$request->set_body_params(
			[
				'ref'        => 'refs/heads/master',
				'repository' => [
					'full_name' => '/p/wearerequired/traduttore',
					'name'      => 'Traduttore',
					'url'       => 'https://sourceforge.net/p/traduttore',
				],
			]
		);
		$signature = 'sha1=' . hash_hmac( 'sha1', $request->get_body(), $secret );
		$request->add_header( 'x-allura-signature', $signature );
		$response = rest_get_server()->dispatch( $request );

		$this->assertEquals( 200, $response->get_status() );
		$this->assertSame( [ 'result' => 'OK' ], $response->get_data() );
		$this->assertNull( $this->project->get_repository_vcs_type() );
		$this->assertSame( Repository::TYPE_SOURCEFORGE, $this->project->get_repository_type() );
		$this->assertSame( 'wearerequired/traduttore', $this->project->get_repository_name() );
		$this->assertSame( 'https://sourceforge.net/p/traduttore', $this->project->get_repository_url() );
		$this->assertNull( $this->project->get_repository_ssh_url() );
		$this->assertNull( $this->project->get_repository_https_url() );
		$this->assertSame( 'public', $this->project->get_repository_visibility() );
	}
}
