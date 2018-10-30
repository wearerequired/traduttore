<?php
/**
 * Class RepositoryFactory
 *
 * @package Traduttore\Tests
 */

namespace Required\Traduttore\Tests;

use \GP_UnitTestCase;
use Required\Traduttore\Repository\{Bitbucket, GitHub, GitLab};
use \Required\Traduttore\Project;
use \Required\Traduttore\RepositoryFactory as Factory;

/**
 * Test cases for \Required\Traduttore\RepositoryFactory.
 */
class RepositoryFactory extends GP_UnitTestCase {
	public function test_get_unknown_repository(): void {
		$factory = new Factory();
		$project = new Project(
			$this->factory->project->create(
				[
					'name' => 'Project',
				]
			)
		);

		$this->assertNull( $factory->get_repository( $project ) );
	}

	public function test_get_unknown_repository_by_type(): void {
		$factory = new Factory();
		$project = new Project(
			$this->factory->project->create(
				[
					'name' => 'Project',
				]
			)
		);

		$project->set_repository_type( \Required\Traduttore\Repository::TYPE_UNKNOWN );

		$this->assertNull( $factory->get_repository( $project ) );
	}

	public function test_get_bitbucket_repository_by_type(): void {
		$factory = new Factory();
		$project = new Project(
			$this->factory->project->create(
				[
					'name' => 'Project',
				]
			)
		);

		$project->set_repository_type( \Required\Traduttore\Repository::TYPE_BITBUCKET );

		$this->assertInstanceOf( Bitbucket::class, $factory->get_repository( $project ) );
	}

	public function test_get_bitbucket_repository_by_url(): void {
		$factory = new Factory();
		$project = new Project(
			$this->factory->project->create(
				[
					'name' => 'Project',
				]
			)
		);

		$project->set_repository_url( 'https://bitbucket.org/wearerequired/traduttore' );

		$this->assertInstanceOf( Bitbucket::class, $factory->get_repository( $project ) );
	}

	public function test_get_bitbucket_repository_by_source_url_template(): void {
		$factory = new Factory();
		$project = new Project(
			$this->factory->project->create(
				[
					'name'                => 'Project',
					'source_url_template' => 'https://bitbucket.org/wearerequired/traduttore/src/master/%file%#L%line%',
				]
			)
		);

		$this->assertInstanceOf( Bitbucket::class, $factory->get_repository( $project ) );
	}

	public function test_get_github_repository_by_type(): void {
		$factory = new Factory();
		$project = new Project(
			$this->factory->project->create(
				[
					'name' => 'Project',
				]
			)
		);

		$project->set_repository_type( \Required\Traduttore\Repository::TYPE_GITHUB );

		$this->assertInstanceOf( GitHub::class, $factory->get_repository( $project ) );
	}

	public function test_get_github_repository_by_url(): void {
		$factory = new Factory();
		$project = new Project(
			$this->factory->project->create(
				[
					'name' => 'Project',
				]
			)
		);

		$project->set_repository_url( 'https://github.com/wearerequired/traduttore' );

		$this->assertInstanceOf( GitHub::class, $factory->get_repository( $project ) );
	}

	public function test_get_github_repository_by_source_url_template(): void {
		$factory = new Factory();
		$project = new Project(
			$this->factory->project->create(
				[
					'name'                => 'Project',
					'source_url_template' => 'https://github.com/wearerequired/traduttore/blob/master/%file%#L%line%',
				]
			)
		);

		$this->assertInstanceOf( GitHub::class, $factory->get_repository( $project ) );
	}

	public function test_get_gitlab_repository_by_type(): void {
		$factory = new Factory();
		$project = new Project(
			$this->factory->project->create(
				[
					'name' => 'Project',
				]
			)
		);

		$project->set_repository_type( \Required\Traduttore\Repository::TYPE_GITLAB );

		$this->assertInstanceOf( GitLab::class, $factory->get_repository( $project ) );
	}

	public function test_get_gitlab_repository_by_source_url_template(): void {
		$factory = new Factory();
		$project = new Project(
			$this->factory->project->create(
				[
					'name'                => 'Project',
					'source_url_template' => 'https://gitlab.com/wearerequired/traduttore/blob/master/%file%#L%line%',
				]
			)
		);

		$this->assertInstanceOf( GitLab::class, $factory->get_repository( $project ) );
	}
}
