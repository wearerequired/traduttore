<?php
/**
 * Class LoaderFactory
 *
 * @package Traduttore\Tests
 */

namespace Required\Traduttore\Tests;

use \GP_UnitTestCase;
use Required\Traduttore\Loader\{Bitbucket, Git, GitHub, GitLab};
use \Required\Traduttore\Project;
use \Required\Traduttore\LoaderFactory as Factory;

/**
 *  Test cases for \Required\Traduttore\Repository.
 */
class LoaderFactory extends GP_UnitTestCase {
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
					'source_url_template' => 'https://github.com/wearerequired/traduttore/blob/master/%file%#L%line%',
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
					'source_url_template' => 'https://bitbucket.org/wearerequired/traduttore/src/master/%file%#lines-%line%',
				]
			)
		);
	}

	public function test_get_unknown_repository(): void {
		$factory = new Factory();

		$this->assertNull( $factory->get_loader( $this->unknown ) );
	}

	public function test_get_github_repository(): void {
		$factory = new Factory();

		$this->assertInstanceOf( Git::class, $factory->get_loader( $this->github ) );
	}

	public function test_get_gitlab_repository(): void {
		$factory = new Factory();

		$this->assertInstanceOf( Git::class, $factory->get_loader( $this->gitlab ) );
	}

	public function test_get_bitbucket_repository(): void {
		$factory = new Factory();

		$this->assertInstanceOf( Git::class, $factory->get_loader( $this->bitbucket ) );
	}
}
