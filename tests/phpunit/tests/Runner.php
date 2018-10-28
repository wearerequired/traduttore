<?php
/**
 * Class Runner
 *
 * @package Traduttore\Tests
 */

namespace Required\Traduttore\Tests;

use \GP_UnitTestCase;
use \Required\Traduttore\Project;
use \Required\Traduttore\Updater;
use \Required\Traduttore\Runner as R;
use \Required\Traduttore\Loader\Git as Loader;

/**
 * Test cases for \Required\Traduttore\Runner.
 */
class Runner extends GP_UnitTestCase {
	/**
	 * @var P
	 */
	protected $project;

	/**
	 * @var R
	 */
	protected $runner;

	/**
	 * @var Git
	 */
	protected $loader;

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

		$test_path = get_temp_dir() . 'traduttore-test-dir';

		$this->loader = $this->createMock( Loader::class );
		$this->loader->method( 'get_local_path' )->willReturn( $test_path );
		$this->loader->method( 'download' )->willReturn( $test_path );

		$updater = $this->createMock( Updater::class );
		$updater->method( 'update' )->willReturn( true );

		$this->runner = new R( $this->loader, $updater );
		$this->runner->delete_local_repository();
	}

	public function test_delete_local_repository(): void {
		mkdir( $this->loader->get_local_path() );
		touch( $this->loader->get_local_path() . '/foo.txt' );

		$this->assertFileExists( $this->loader->get_local_path() . '/foo.txt' );
		$this->assertTrue( $this->runner->delete_local_repository() );
		$this->assertFileNotExists( $this->loader->get_local_path() . '/foo.txt' );
	}

	public function test_delete_local_repository_without_filesystem(): void {
		mkdir( $this->loader->get_local_path() );
		touch( $this->loader->get_local_path() . '/foo.txt' );

		unset( $GLOBALS['wp_filesystem'] );

		add_filter( 'filesystem_method', '__return_empty_string' );
		$result = $this->runner->delete_local_repository();
		remove_filter( 'filesystem_method', '__return_empty_string' );

		$this->assertFileExists( $this->loader->get_local_path() . '/foo.txt' );
		$this->assertFalse( $result );
		$this->assertFileExists( $this->loader->get_local_path() . '/foo.txt' );
	}

	public function test_run(): void {
		$result = $this->runner->run();

		$this->assertTrue( $result );
	}

	public function test_run_with_existing_repository(): void {
		$result1 = $this->runner->run();
		$result2 = $this->runner->run();

		$this->assertTrue( $result1 );
		$this->assertTrue( $result2 );
	}

	public function test_run_and_delete_existing_repository(): void {
		$result1 = $this->runner->run();
		$this->runner->delete_local_repository();
		$result2 = $this->runner->run();

		$this->assertTrue( $result1 );
		$this->assertTrue( $result2 );
	}

	public function test_run_stops_when_project_is_locked(): void {
		$updater = $this->createMock( Updater::class );
		$updater->method( 'has_lock' )->willReturn( true );

		$this->runner = new R( $this->loader, $updater );

		$result = $this->runner->run();

		$this->assertFalse( $result );
	}

	public function test_run_stops_when_download_fails(): void {
		$loader = $this->createMock( Loader::class );
		$loader->method( 'download' )->willReturn( null );
		$updater = $this->createMock( Updater::class );

		$this->runner = new R( $loader, $updater );

		$result = $this->runner->run();

		$this->assertFalse( $result );
	}
}
