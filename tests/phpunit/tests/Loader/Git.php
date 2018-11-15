<?php
/**
 * Class Git
 *
 * @package Traduttore\Tests
 */

namespace Required\Traduttore\Tests\Loader;

use \GP_UnitTestCase;
use \Required\Traduttore\Loader\Git as GitLoader;
use \Required\Traduttore\Project;
use Required\Traduttore\Repository\GitHub;
use Required\Traduttore\Tests\TestCase;

/**
 * Test cases for \Required\Traduttore\Loader\Git.
 *
 * @todo Mock shell execution
 */
class Git extends TestCase {
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

	public function test_get_local_path(): void {
		$loader = new GitLoader( new GitHub( $this->project ) );

		$this->assertStringEndsWith( 'traduttore-github.com-wearerequired-traduttore', $loader->get_local_path() );
	}

	public function test_download_repository(): void {
		$this->markTestSkipped( 'Need to mock shell command execution' );

		$loader = new GitLoader( new GitHub( $this->project ) );

		add_filter( 'traduttore.git_clone_use_https', '__return_true' );
		$result = $loader->download();
		remove_filter( 'traduttore.git_clone_use_https', '__return_true' );

		$this->assertSame( $result, $loader->get_local_path() );
	}

	public function test_download_existing_repository(): void {
		$this->markTestSkipped( 'Need to mock shell command execution' );

		$loader = new GitLoader( new GitHub( $this->project ) );

		add_filter( 'traduttore.git_clone_use_https', '__return_true' );
		$result1 = $loader->download();
		$result2 = $loader->download();
		remove_filter( 'traduttore.git_clone_use_https', '__return_true' );

		$this->assertSame( $result1, $loader->get_local_path() );
		$this->assertSame( $result2, $loader->get_local_path() );
	}
}
