<?php
/**
 * Class GetPushResources
 *
 * @package H2push
 */

namespace Required\Traduttore\Tests;

use \GP_UnitTestCase;
use \Required\Traduttore\ZipProvider as Provider;

/**
 *  Test cases for \Required\Traduttore\ZipProvider.
 */
class ZipProvider extends GP_UnitTestCase {
	/**
	 * @var \GP_Locale
	 */
	protected $locale;

	/**
	 * @var \GP_Translation_Set
	 */
	protected $translation_set;

	/**
	 * @var GP_Translation_Set
	 */
	protected $sub_translation_set;

	public function setUp() {
		parent::setUp();

		$this->locale = $this->factory->locale->create(
			[
				'slug'      => 'de',
				'wp_locale' => 'de_DE',
			]
		);

		$this->translation_set = $this->factory->translation_set->create_with_project(
			[
				'locale' => $this->locale->slug,
			],
			[
				'name' => 'foo-project',
			]
		);

		$this->sub_translation_set = $this->factory->translation_set->create_with_project(
			[
				'locale' => $this->locale->slug,
			],
			[
				'name'              => 'foo-project',
				'parent_project_id' => $this->translation_set->project->id,
			]
		);
	}

	public function tearDown() {
		/* @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		if ( ! $wp_filesystem ) {
			require_once ABSPATH . '/wp-admin/includes/admin.php';

			if ( ! \WP_Filesystem() ) {
				return false;
			}
		}

		$wp_filesystem->rmdir( Provider::get_cache_dir(), true );

		parent::tearDown();
	}

	public function test_get_cache_dir() {
		$dir = Provider::get_cache_dir();

		$this->assertStringEndsWith( 'wp-content/traduttore', $dir );
	}

	public function test_get_zip_path() {
		$provider = new Provider( $this->translation_set );

		$this->assertStringEndsWith( 'wp-content/traduttore/foo-project-de_DE.zip', $provider->get_zip_path( $this->translation_set ) );
	}

	public function test_get_zip_url() {
		$provider = new Provider( $this->translation_set );

		$this->assertSame( home_url( 'wp-content/traduttore/foo-project-de_DE.zip' ), $provider->get_zip_url( $this->translation_set ) );
	}

	public function test_get_last_build_time_for_new_set() {
		$build_time = Provider::get_last_build_time( $this->translation_set );

		$this->assertFalse( $build_time );
	}

	public function test_generate_zip_file_empty_set() {
		$provider = new Provider( $this->translation_set );

		$this->assertFalse( $provider->generate_zip_file() );
	}

	public function test_generate_zip_file() {
		$original = $this->factory->original->create( [ 'project_id' => $this->translation_set->project_id ] );

		$this->factory->translation->create(
			[
				'original_id'        => $original->id,
				'translation_set_id' => $this->translation_set->id,
				'status'             => 'current',
			]
		);

		$provider = new Provider( $this->translation_set );

		$this->assertTrue( $provider->generate_zip_file() );
	}

	public function test_get_last_build_time_after_zip_generation() {
		$original = $this->factory->original->create( [ 'project_id' => $this->translation_set->project_id ] );

		$this->factory->translation->create(
			[
				'original_id'        => $original->id,
				'translation_set_id' => $this->translation_set->id,
				'status'             => 'current',
			]
		);

		$provider = new Provider( $this->translation_set );

		$provider->generate_zip_file();

		$build_time = Provider::get_last_build_time( $this->translation_set );

		$this->assertInternalType( 'string', $build_time );
	}
}
