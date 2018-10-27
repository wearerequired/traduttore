<?php
/**
 * Class LoaderFactory
 *
 * @package Traduttore\Tests
 */

namespace Required\Traduttore\Tests;

use \GP_UnitTestCase;
use Required\Traduttore\Loader\{Git, Mercurial};
use \Required\Traduttore\Project;
use \Required\Traduttore\Repository;
use \Required\Traduttore\LoaderFactory as Factory;
use Required\Traduttore\Repository\Bitbucket;
use Required\Traduttore\Repository\GitHub;
use Required\Traduttore\Repository\GitLab;

/**
 *  Test cases for \Required\Traduttore\LoaderFactory.
 */
class LoaderFactory extends GP_UnitTestCase {
	public function test_get_mercurial_loader(): void {
		$factory = new Factory();
		$project = new Project(
			$this->factory->project->create(
				[
					'name' => 'Project',
				]
			)
		);

		$project->set_repository_vcs_type( Repository::VCS_TYPE_HG );

		$repository = new Bitbucket( $project );

		$this->assertInstanceOf( Mercurial::class, $factory->get_loader( $repository ) );
	}

	public function test_get_git_loader_for_bitbucket_repository(): void {
		$factory = new Factory();
		$project = new Project(
			$this->factory->project->create(
				[
					'name' => 'Project',
				]
			)
		);

		$repository = new Bitbucket( $project );

		$this->assertInstanceOf( Git::class, $factory->get_loader( $repository ) );
	}

	public function test_get_git_loader_for_github_repository(): void {
		$factory = new Factory();
		$project = new Project(
			$this->factory->project->create(
				[
					'name' => 'Project',
				]
			)
		);

		$repository = new GitHub( $project );

		$this->assertInstanceOf( Git::class, $factory->get_loader( $repository ) );
	}

	public function test_get_git_loader_for_gitlab_repository(): void {
		$factory = new Factory();
		$project = new Project(
			$this->factory->project->create(
				[
					'name' => 'Project',
				]
			)
		);

		$repository = new GitLab( $project );

		$this->assertInstanceOf( Git::class, $factory->get_loader( $repository ) );
	}
}
