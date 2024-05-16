<?php
/**
 * Class Mercurial
 *
 * @package Traduttore\Tests
 */

namespace Required\Traduttore\Tests\Loader;

use \Required\Traduttore\Loader\Mercurial as MercurialLoader;
use \Required\Traduttore\Project;
use Required\Traduttore\Repository\Bitbucket;
use Required\Traduttore\Tests\TestCase;

/**
 * Test cases for \Required\Traduttore\Loader\Mercurial.
 *
 * @todo Mock shell execution
 */
class Mercurial extends TestCase {
	/**
	 * @var Project
	 */
	protected $project;

	public function setUp(): void {
		parent::setUp();

		$this->project = new Project(
			$this->factory->project->create(
				[
					'name'                => 'Sample Project',
					'slug'                => 'sample-project',
					'source_url_template' => 'https://bitbucket.org/wearerequired/traduttore/src/master/%file%#L%line%',
				]
			)
		);
	}

	public function test_get_local_path(): void {
		$loader = new MercurialLoader( new Bitbucket( $this->project ) );

		$this->assertStringEndsWith( 'traduttore-bitbucket.org-wearerequired-traduttore', $loader->get_local_path() );
	}

	public function test_download_repository(): void {
		$this->markTestSkipped( 'Need to mock shell command execution' );

		$loader = new MercurialLoader( new Bitbucket( $this->project ) );

		add_filter( 'traduttore.hg_clone_use_https', '__return_true' );
		$result = $loader->download();
		remove_filter( 'traduttore.hg_clone_use_https', '__return_true' );

		$this->assertSame( $result, $loader->get_local_path() );
	}

	public function test_download_existing_repository(): void {
		$this->markTestSkipped( 'Need to mock shell command execution' );

		$loader = new MercurialLoader( new Bitbucket( $this->project ) );

		add_filter( 'traduttore.hg_clone_use_https', '__return_true' );
		$result1 = $loader->download();
		$result2 = $loader->download();
		remove_filter( 'traduttore.hg_clone_use_https', '__return_true' );

		$this->assertSame( $result1, $loader->get_local_path() );
		$this->assertSame( $result2, $loader->get_local_path() );
	}
}
