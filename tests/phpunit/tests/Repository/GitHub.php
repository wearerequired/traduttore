<?php
/**
 * Class GitHubRepository
 *
 * @package Traduttore\Tests
 */

namespace Required\Traduttore\Tests\Repository;

use \GP_UnitTestCase;
use Required\Traduttore\Repository\GitHub as GitHubRepository;
use \Required\Traduttore\Project;
use Required\Traduttore\Repository;
use Required\Traduttore\Tests\TestCase;

/**
 * Test cases for \Required\Traduttore\Repository\GitHub.
 */
class GitHub extends TestCase {
	/** @var Project */
	protected $project;

	/**
	 * Count of the number of times an HTTP request was made.
	 *
	 * @var int
	 */
	public $http_request_count = 0;

	public function setUp(): void {
		parent::setUp();

		$this->project = new Project(
			$this->factory->project->create(
				[
					'name' => 'Project',
				]
			)
		);

		$this->http_request_count = 0;
	}

	public function test_get_type(): void {
		$repository = new GitHubRepository( $this->project );

		$this->assertSame( Repository::TYPE_GITHUB, $repository->get_type() );
	}

	public function test_get_name_falls_back_to_project_slug(): void {
		$repository = new GitHubRepository( $this->project );

		$this->assertSame( $this->project->get_slug(), $repository->get_name() );
	}

	public function test_get_name_falls_back_to_source_url_template(): void {
		$project = new Project(
			$this->factory->project->create(
				[
					'name'                => 'Project',
					'source_url_template' => 'https://github.com/wearerequired/traduttore/blob/master/%file%#L%line%',
				]
			)
		);

		$repository = new GitHubRepository( $project );

		$this->assertSame( 'wearerequired/traduttore', $repository->get_name() );
	}

	public function test_get_name_falls_back_to_different_source_url_template(): void {
		$project = new Project(
			$this->factory->project->create(
				[
					'name'                => 'Project',
					'source_url_template' => 'https://github.com/wearerequired/traduttore/tree/master/%file%#L%line%',
				]
			)
		);

		$repository = new GitHubRepository( $project );

		$this->assertSame( 'wearerequired/traduttore', $repository->get_name() );
	}

	public function test_get_name_falls_back_to_repository_url(): void {
		$this->project->set_repository_url( 'https://github.com/wearerequired/traduttore/' );

		$repository = new GitHubRepository( $this->project );

		$this->assertSame( 'wearerequired/traduttore', $repository->get_name() );
	}

	public function test_get_name(): void {
		$this->project->set_repository_name( 'wearerequired/traduttore' );

		$repository = new GitHubRepository( $this->project );

		$this->assertSame( 'wearerequired/traduttore', $repository->get_name() );
	}

	/**
	 * Intercept HTTP requests and mock responses.
	 *
	 * @param false  $preempt Whether to preempt an HTTP request's return value. Default false.
	 * @param mixed  $r       HTTP request arguments.
	 * @param string $url     The request URL.
	 * @return array|false Response data.
	 */
	public function mock_repository_visibility_request( $preempt, $r, $url ) {
		if ( GitHubRepository::API_BASE . '/repos/wearerequired/traduttore' === $url ) {
			++ $this->http_request_count;

			return [
				'response' => [
					'code' => 200,
				],
				'body'     => 'Irrelevant response.',
			];
		}

		return $preempt;
	}

	public function test_is_public_performs_http_request_and_caches_it(): void {
		$this->project->set_repository_name( 'wearerequired/traduttore' );

		add_filter( 'pre_http_request', array( $this, 'mock_repository_visibility_request' ), 10, 3 );

		$repository = new GitHubRepository( $this->project );

		$visibility_before = $repository->get_project()->get_repository_visibility();
		$is_public         = $repository->is_public();
		$is_public_after   = $repository->is_public();
		$visibility_after  = $repository->get_project()->get_repository_visibility();

		remove_filter( 'pre_http_request', array( $this, 'mock_repository_visibility_request' ), 10 );

		$this->assertNull( $visibility_before );
		$this->assertTrue( $is_public );
		$this->assertTrue( $is_public_after );
		$this->assertSame( 'public', $visibility_after );
		$this->assertSame( 1, $this->http_request_count );
	}

	public function test_is_public_performs_no_unnecessary_http_request(): void {
		$this->project->set_repository_name( 'wearerequired/traduttore' );

		add_filter( 'pre_http_request', array( $this, 'mock_repository_visibility_request' ), 10, 3 );

		$this->project->set_repository_visibility( 'private' );

		$repository = new GitHubRepository( $this->project );

		$visibility_before = $repository->get_project()->get_repository_visibility();
		$is_public         = $repository->is_public();

		remove_filter( 'pre_http_request', array( $this, 'mock_repository_visibility_request' ), 10 );

		$this->assertSame( 'private', $visibility_before );
		$this->assertFalse( $is_public );
		$this->assertSame( 0, $this->http_request_count );
	}

	public function test_get_ssh_url_falls_back_to_host_and_determined_name(): void {
		$repository = new GitHubRepository( $this->project );

		$url = $repository->get_ssh_url();

		$this->assertSame( 'git@github.com:project.git', $url );
	}

	public function test_get_ssh_url_falls_back_to_host_and_repository_name(): void {
		$this->project->set_repository_name( 'wearerequired/traduttore' );

		$repository = new GitHubRepository( $this->project );

		$url = $repository->get_ssh_url();

		$this->assertSame( 'git@github.com:wearerequired/traduttore.git', $url );
	}

	public function test_get_ssh_url_uses_stored_data(): void {
		$this->project->set_repository_ssh_url( 'git@github.com:wearerequired/custom.git' );

		$repository = new GitHubRepository( $this->project );

		$url = $repository->get_ssh_url();

		$this->assertSame( 'git@github.com:wearerequired/custom.git', $url );
	}

	public function test_get_https_url_falls_back_to_host_and_determined_name(): void {
		$repository = new GitHubRepository( $this->project );

		$url = $repository->get_https_url();

		$this->assertSame( 'https://github.com/project.git', $url );
	}

	public function test_get_https_url_falls_back_to_host_and_repository_name(): void {
		$this->project->set_repository_name( 'wearerequired/traduttore' );

		$repository = new GitHubRepository( $this->project );

		$url = $repository->get_https_url();

		$this->assertSame( 'https://github.com/wearerequired/traduttore.git', $url );
	}

	public function test_get_https_url_uses_stored_data(): void {
		$this->project->set_repository_https_url( 'https://github.com/wearerequired/custom.git' );

		$repository = new GitHubRepository( $this->project );

		$url = $repository->get_https_url();

		$this->assertSame( 'https://github.com/wearerequired/custom.git', $url );
	}
}
