<?php
/**
 * Class GitHubUpdater
 *
 * @package Traduttore\Tests
 */

namespace Required\Traduttore\Tests;

use \GP_UnitTestCase;
use \WP_REST_Request;
use \Required\Traduttore\GitHubUpdater as Updater;

/**
 *  Test cases for \Required\Traduttore\GitHubUpdater.
 */
class GitHubUpdater extends GP_UnitTestCase {
	/**
	 * @var \GP_Project
	 */
	protected $project;

	public function setUp() {
		parent::setUp();

		$this->project = $this->factory->project->create( [
			'name'                => 'Sample Project',
			'slug'                => 'sample-project',
			'source_url_template' => 'https://github.com/wearerequired/traduttore/blob/master/%file%#L%line%',
		] );
	}

	public function test_get_repository_path() {
		$updater = new Updater( $this->project );

		$this->assertSame( get_temp_dir() . 'traduttore-github-sample-project', $updater->get_repository_path() );
	}

	public function test_remove_local_repository() {
		$updater = new Updater( $this->project );

		mkdir( $updater->get_repository_path() );
		touch( $updater->get_repository_path() . '/foo.txt' );

		$this->assertTrue( file_exists( $updater->get_repository_path() . '/foo.txt' ) );
		$this->assertTrue( $updater->remove_local_repository() );
		$this->assertFalse( file_exists( $updater->get_repository_path() . '/foo.txt' ) );
	}
}
