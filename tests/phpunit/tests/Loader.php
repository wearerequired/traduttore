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
}
