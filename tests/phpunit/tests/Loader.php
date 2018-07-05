<?php
/**
 * Class Loader
 *
 * @package Traduttore\Tests
 */

namespace Required\Traduttore\Tests;

use \GP_UnitTestCase;
use \Required\Traduttore\Loader\GitHub as GitHubLoader;
use \Required\Traduttore\Project;
use \Required\Traduttore\Repository;

/**
 *  Test cases for \Required\Traduttore\Loader.
 */
class Loader extends GP_UnitTestCase {
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
					'slug'                => 'sample-project',
					'source_url_template' => 'https://github.com/wearerequired/traduttore/blob/master/%file%#L%line%',
				]
			)
		);
	}

	public function test_get_local_path() {
		$loader = new GitHubLoader( new Repository( $this->project ) );

		$this->assertStringEndsWith( 'traduttore-github.com-sample-project', $loader->get_local_path() );
	}

	public function test_download_repository() {
		$loader = new GitHubLoader( new Repository( $this->project ) );

		add_filter( 'traduttore.git_clone_use_https', '__return_true' );
		$result = $loader->download();
		remove_filter( 'traduttore.git_clone_use_https', '__return_true' );

		$this->assertSame( $result, $loader->get_local_path() );
	}

	public function test_download_existing_repository() {
		$loader = new GitHubLoader( new Repository( $this->project ) );

		add_filter( 'traduttore.git_clone_use_https', '__return_true' );
		$result1 = $loader->download();
		$result2 = $loader->download();
		remove_filter( 'traduttore.git_clone_use_https', '__return_true' );

		$this->assertSame( $result1, $loader->get_local_path() );
		$this->assertSame( $result2, $loader->get_local_path() );
	}
}
