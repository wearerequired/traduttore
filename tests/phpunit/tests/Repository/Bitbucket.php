<?php
/**
 * Class BitbucketRepository
 *
 * @package Traduttore\Tests
 */

namespace Required\Traduttore\Tests\Repository;

use \GP_UnitTestCase;
use Required\Traduttore\Repository\Bitbucket as BitbucketRepository;
use \Required\Traduttore\Project;
use Required\Traduttore\Repository;

/**
 * Test cases for \Required\Traduttore\Repository\Bitbucket.
 */
class Bitbucket extends GP_UnitTestCase {
	/** @var Project */
	protected $project;

	/**
	 * Count of the number of times an HTTP request was made.
	 *
	 * @var int
	 */
	public $http_request_count = 0;

	public function setUp() {
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
		$repository = new BitbucketRepository( $this->project );

		$this->assertSame( Repository::TYPE_BITBUCKET, $repository->get_type() );
	}

	public function test_get_name_falls_back_to_project_slug(): void {
		$repository = new BitbucketRepository( $this->project );

		$this->assertSame( $this->project->get_slug(), $repository->get_name() );
	}

	public function test_get_name_falls_back_to_source_url_template(): void {
		$this->project->get_project()->source_url_template = 'https://bitbucket.org/wearerequired/traduttore/src/master/%file%#L%line%';

		$repository = new BitbucketRepository( $this->project );

		$this->assertSame( 'wearerequired/traduttore', $repository->get_name() );
	}

	public function test_get_name_falls_back_to_repository_url(): void {
		$this->project->set_repository_url( 'https://bitbucket.org/wearerequired/traduttore/' );

		$repository = new BitbucketRepository( $this->project );

		$this->assertSame( 'wearerequired/traduttore', $repository->get_name() );
	}

	public function test_get_name(): void {
		$this->project->set_repository_name( 'wearerequired/traduttore' );

		$repository = new BitbucketRepository( $this->project );

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
		if ( BitbucketRepository::API_BASE . '/repositories/wearerequired/traduttore' === $url ) {
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

		$repository = new BitbucketRepository( $this->project );

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

		$repository = new BitbucketRepository( $this->project );

		$visibility_before = $repository->get_project()->get_repository_visibility();
		$is_public         = $repository->is_public();

		remove_filter( 'pre_http_request', array( $this, 'mock_repository_visibility_request' ), 10 );

		$this->assertSame( 'private', $visibility_before );
		$this->assertFalse( $is_public );
		$this->assertSame( 0, $this->http_request_count );
	}

	public function test_get_ssh_url_falls_back_to_host_and_determined_name(): void {
		$repository = new BitbucketRepository( $this->project );

		$url = $repository->get_ssh_url();

		$this->assertSame( 'git@bitbucket.org:project.git', $url );
	}

	public function test_get_ssh_url_falls_back_to_host_and_determined_name_for_hg_repository(): void {
		$this->project->set_repository_vcs_type( Repository::VCS_TYPE_HG );

		$repository = new BitbucketRepository( $this->project );

		$url = $repository->get_ssh_url();

		$this->assertSame( 'hg@bitbucket.org/project', $url );
	}

	public function test_get_ssh_url_falls_back_to_host_and_repository_name(): void {
		$this->project->set_repository_name( 'wearerequired/traduttore' );

		$repository = new BitbucketRepository( $this->project );

		$url = $repository->get_ssh_url();

		$this->assertSame( 'git@bitbucket.org:wearerequired/traduttore.git', $url );
	}

	public function test_get_ssh_url_falls_back_to_host_and_repository_name_for_hg_repository(): void {
		$this->project->set_repository_vcs_type( Repository::VCS_TYPE_HG );
		$this->project->set_repository_name( 'wearerequired/traduttore' );

		$repository = new BitbucketRepository( $this->project );

		$url = $repository->get_ssh_url();

		$this->assertSame( 'hg@bitbucket.org/wearerequired/traduttore', $url );
	}

	public function test_get_ssh_url_uses_stored_data(): void {
		$this->project->set_repository_ssh_url( 'git@bitbucket.org:wearerequired/custom.git' );

		$repository = new BitbucketRepository( $this->project );

		$url = $repository->get_ssh_url();

		$this->assertSame( 'git@bitbucket.org:wearerequired/custom.git', $url );
	}

	public function test_get_ssh_url_uses_stored_data_for_hg_repository(): void {
		$this->project->set_repository_vcs_type( Repository::VCS_TYPE_HG );
		$this->project->set_repository_ssh_url( 'hg@bitbucket.org:wearerequired/custom' );

		$repository = new BitbucketRepository( $this->project );

		$url = $repository->get_ssh_url();

		$this->assertSame( 'hg@bitbucket.org:wearerequired/custom', $url );
	}

	public function test_get_https_url_falls_back_to_host_and_determined_name(): void {
		$repository = new BitbucketRepository( $this->project );

		$url = $repository->get_https_url();

		$this->assertSame( 'https://bitbucket.org/project.git', $url );
	}

	public function test_get_https_url_falls_back_to_host_and_determined_name_for_hg_repository(): void {
		$this->project->set_repository_vcs_type( Repository::VCS_TYPE_HG );

		$repository = new BitbucketRepository( $this->project );

		$url = $repository->get_https_url();

		$this->assertSame( 'https://bitbucket.org/project', $url );
	}

	public function test_get_https_url_falls_back_to_host_and_repository_name(): void {
		$this->project->set_repository_name( 'wearerequired/traduttore' );

		$repository = new BitbucketRepository( $this->project );

		$url = $repository->get_https_url();

		$this->assertSame( 'https://bitbucket.org/wearerequired/traduttore.git', $url );
	}

	public function test_get_https_url_falls_back_to_host_and_repository_name_for_hg_repository(): void {
		$this->project->set_repository_vcs_type( Repository::VCS_TYPE_HG );
		$this->project->set_repository_name( 'wearerequired/traduttore' );

		$repository = new BitbucketRepository( $this->project );

		$url = $repository->get_https_url();

		$this->assertSame( 'https://bitbucket.org/wearerequired/traduttore', $url );
	}

	public function test_get_https_url_uses_stored_data(): void {
		$this->project->set_repository_https_url( 'https://bitbucket.org/wearerequired/custom.git' );

		$repository = new BitbucketRepository( $this->project );

		$url = $repository->get_https_url();

		$this->assertSame( 'https://bitbucket.org/wearerequired/custom.git', $url );
	}

	public function test_get_https_url_uses_stored_data_for_hg_repository(): void {
		$this->project->set_repository_vcs_type( Repository::VCS_TYPE_HG );
		$this->project->set_repository_https_url( 'https://bitbucket.org/wearerequired/custom' );

		$repository = new BitbucketRepository( $this->project );

		$url = $repository->get_https_url();

		$this->assertSame( 'https://bitbucket.org/wearerequired/custom', $url );
	}

	public function test_get_https_url_with_credentials(): void {
		$this->project->set_repository_https_url( 'https://bitbucket.org/wearerequired/custom.git' );

		$repository = new BitbucketRepository( $this->project );

		add_filter(
			'traduttore.git_https_credentials',
			function() {
				return 'foo:bar';
			}
		);

		$url = $repository->get_https_url();

		$this->assertSame( 'https://foo:bar@bitbucket.org/wearerequired/custom.git', $url );
	}

	public function test_get_https_url_with_credentials_for_ht_repository(): void {
		$this->project->set_repository_vcs_type( Repository::VCS_TYPE_HG );
		$this->project->set_repository_https_url( 'https://bitbucket.org/wearerequired/custom' );

		$repository = new BitbucketRepository( $this->project );

		add_filter(
			'traduttore.hg_https_credentials',
			function() {
				return 'foo:bar';
			}
		);

		$url = $repository->get_https_url();

		$this->assertSame( 'https://foo:bar@bitbucket.org/wearerequired/custom', $url );
	}
}
