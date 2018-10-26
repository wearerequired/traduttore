<?php
/**
 * Class Repository
 *
 * @package Traduttore\Tests
 */

namespace Required\Traduttore\Tests;

use \GP_UnitTestCase;
use \Required\Traduttore\Project;
use \Required\Traduttore\Repository\Base as Repo;

/**
 *  Test cases for \Required\Traduttore\Repository.
 */
class Repository extends GP_UnitTestCase {
	/**
	 * @var Project
	 */
	protected $unknown;

	/**
	 * @var Project
	 */
	protected $github;

	/**
	 * @var Project
	 */
	protected $gitlab;

	/**
	 * @var Project
	 */
	protected $bitbucket;

	public function setUp() {
		parent::setUp();

		$this->unknown = new Project(
			$this->factory->project->create(
				[
					'name' => 'Unknown',
				]
			)
		);

		$this->github = new Project(
			$this->factory->project->create(
				[
					'name'                => 'GitHub',
					'source_url_template' => 'https://github.com/github/traduttore/blob/master/%file%#L%line%',
				]
			)
		);

		$this->gitlab = new Project(
			$this->factory->project->create(
				[
					'name'                => 'GitLab',
					'source_url_template' => 'https://gitlab.com/gitlab/traduttore/blob/master/%file%#L%line%',
				]
			)
		);

		$this->bitbucket = new Project(
			$this->factory->project->create(
				[
					'name'                => 'Bitbucket',
					'source_url_template' => 'https://bitbucket.org/bitbucket/traduttore/src/master/%file%#lines-%line%',
				]
			)
		);
	}

	public function test_get_project_unknown(): void {
		$repository = new Repo( $this->unknown );

		$this->assertSame( $this->unknown, $repository->get_project() );
	}

	public function test_get_host_unknown(): void {
		$repository = new Repo( $this->unknown );

		$this->assertNull( $repository->get_host() );
	}

	public function test_get_type_unknown(): void {
		$repository = new Repo( $this->unknown );

		$this->assertSame( Repo::TYPE_UNKNOWN, $repository->get_type() );
	}

	public function test_get_name_unknown(): void {
		$repository = new Repo( $this->unknown );

		$this->assertNull( $repository->get_name() );
	}

	public function test_get_project_github(): void {
		$repository = new Repo( $this->github );

		$this->assertSame( $this->github, $repository->get_project() );
	}

	public function test_get_host_github(): void {
		$repository = new Repo( $this->github );

		$this->assertSame( 'github.com', $repository->get_host() );
	}

	public function test_get_type_github(): void {
		$repository = new Repo( $this->github );

		$this->assertSame( Repo::TYPE_GITHUB, $repository->get_type() );
	}

	public function test_get_name_github(): void {
		$repository = new Repo( $this->github );

		$this->assertSame( 'github/traduttore', $repository->get_name() );
	}

	public function test_get_project_gitlab(): void {
		$repository = new Repo( $this->gitlab );

		$this->assertSame( $this->gitlab, $repository->get_project() );
	}

	public function test_get_host_gitlab(): void {
		$repository = new Repo( $this->gitlab );

		$this->assertSame( 'gitlab.com', $repository->get_host() );
	}

	public function test_get_type_gitlab(): void {
		$repository = new Repo( $this->gitlab );

		$this->assertSame( Repo::TYPE_GITLAB, $repository->get_type() );
	}

	public function test_get_name_gitlab(): void {
		$repository = new Repo( $this->gitlab );

		$this->assertSame( 'gitlab/traduttore', $repository->get_name() );
	}

	public function test_get_project_bitbucket(): void {
		$repository = new Repo( $this->bitbucket );

		$this->assertSame( $this->bitbucket, $repository->get_project() );
	}

	public function test_get_host_bitbucket(): void {
		$repository = new Repo( $this->bitbucket );

		$this->assertSame( 'bitbucket.org', $repository->get_host() );
	}

	public function test_get_type_bitbucket(): void {
		$repository = new Repo( $this->bitbucket );

		$this->assertSame( Repo::TYPE_BITBUCKET, $repository->get_type() );
	}

	public function test_get_name_bitbucket(): void {
		$repository = new Repo( $this->bitbucket );

		$this->assertSame( 'bitbucket/traduttore', $repository->get_name() );
	}
}
