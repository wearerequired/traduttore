<?php
/**
 * Class ZipProvider
 *
 * @package Traduttore\Tests
 */

namespace Required\Traduttore\Tests;

use DateTime;
use GP;
use GP_Translation_Set;
use Required\Traduttore\ProjectLocator;
use \Required\Traduttore\ZipProvider as Provider;
use Translations;
use ZipArchive;

/**
 * Test cases for \Required\Traduttore\ZipProvider.
 */
class ZipProvider extends TestCase {
	/**
	 * @var \GP_Locale
	 */
	protected $locale;

	/**
	 * @var GP_Translation_Set
	 */
	protected $translation_set;

	/**
	 * @var GP_Translation_Set
	 */
	protected $sub_translation_set;

	/**
	 * @var \Required\Traduttore\Project
	 */
	protected $project;

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

		$this->project = new \Required\Traduttore\Project( GP::$project->get( $this->translation_set->project_id ) );
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

	public function test_get_cache_dir(): void {
		$dir = Provider::get_cache_dir();

		$this->assertStringEndsWith( 'wp-content/traduttore', $dir );
	}

	public function test_get_zip_path(): void {
		$provider = new Provider( $this->translation_set );

		$this->assertStringEndsWith( 'wp-content/traduttore/foo-project-de_DE.zip', $provider->get_zip_path() );
	}

	public function test_get_zip_url(): void {
		$provider = new Provider( $this->translation_set );

		$this->assertSame( home_url( 'wp-content/traduttore/foo-project-de_DE.zip' ), $provider->get_zip_url() );
	}

	public function test_get_last_build_time_for_new_set(): void {
		$provider = new Provider( $this->translation_set );

		$this->assertNull( $provider->get_last_build_time() );
	}

	public function test_generate_zip_file_empty_set(): void {
		$provider = new Provider( $this->translation_set );

		$this->assertFalse( $provider->generate_zip_file() );
	}

	public function test_generate_zip_file_no_filesystem(): void {
		$provider = new Provider( $this->translation_set );

		add_filter( 'filesystem_method', '__return_empty_string' );
		$result = $provider->generate_zip_file();
		remove_filter( 'filesystem_method', '__return_empty_string' );

		$this->assertFalse( $result );
	}

	public function test_generate_zip_file(): void {
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

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_generate_zip_file_missing_wp_filesystem(): void {
		$original = $this->factory->original->create( [ 'project_id' => $this->translation_set->project_id ] );

		$this->factory->translation->create(
			[
				'original_id'        => $original->id,
				'translation_set_id' => $this->translation_set->id,
				'status'             => 'current',
			]
		);

		$provider = new Provider( $this->translation_set );

		add_filter( 'filesystem_method', '__return_empty_string' );
		$result = $provider->generate_zip_file();
		remove_filter( 'filesystem_method', '__return_empty_string' );

		$this->assertFalse( $result );
	}

	public function test_replaces_existing_zip_file(): void {
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

		$zip_before = new ZipArchive();
		$zip_before->open( $provider->get_zip_path() );
		$zip_before->addFromString( 'foo.txt', 'bar' );

		$file_before = $zip_before->statName( 'foo.txt' );

		$zip_before->close();

		$provider->generate_zip_file();

		$zip_after = new ZipArchive();
		$zip_after->open( $provider->get_zip_path() );

		$file_after = $zip_after->statName( 'foo.txt' );

		$this->assertInternalType( 'array', $file_before );
		$this->assertFalse( $file_after );
	}

	public function test_get_last_build_time_after_zip_generation(): void {
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

		$this->assertInstanceOf( DateTime::class, $provider->get_last_build_time() );
	}

	public function test_remove_zip_file(): void {
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

		$this->assertTrue( $provider->remove_zip_file() );
	}

	public function test_remove_zip_file_resets_build_time(): void {
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
		$provider->remove_zip_file();

		$build_time = $provider->get_last_build_time();

		$this->assertNull( $build_time );
	}

	public function test_remove_zip_file_does_not_exist(): void {
		$provider = new Provider( $this->translation_set );

		$result = $provider->remove_zip_file();

		$this->assertFalse( $result );
	}

	public function test_remove_zip_file_no_filesystem(): void {
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

		unset( $GLOBALS['wp_filesystem'] );

		add_filter( 'filesystem_method', '__return_empty_string' );
		$result = $provider->remove_zip_file();
		remove_filter( 'filesystem_method', '__return_empty_string' );

		$this->assertFalse( $result );
	}

	public function test_use_text_domain_for_translation_files(): void {
		$project  = ( new ProjectLocator( $this->translation_set->project_id ) )->get_project();
		$original = $this->factory->original->create( [ 'project_id' => $this->translation_set->project_id ] );

		$this->factory->translation->create(
			[
				'original_id'        => $original->id,
				'translation_set_id' => $this->translation_set->id,
				'status'             => 'current',
			]
		);

		$project->set_text_domain( 'foo-bar-baz' );

		$provider = new Provider( $this->translation_set );
		$result   = $provider->generate_zip_file();

		$expected_files = [ 'foo-bar-baz-de_DE.po', 'foo-bar-baz-de_DE.mo' ];
		$actual_files   = [];

		$zip = new ZipArchive();

		$zip->open( $provider->get_zip_path() );

		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar
		for ( $i = 0; $i < $zip->numFiles; $i ++ ) {
			$stat           = $zip->statIndex( $i );
			$actual_files[] = $stat['name'];
		}

		$zip->close();

		$this->assertTrue( $result );
		$this->assertEqualSets( $expected_files, $actual_files );
	}

	public function test_schedule_generation_schedules_event(): void {
		$before = wp_next_scheduled( 'traduttore.generate_zip', [ $this->translation_set->id ] );

		$provider = new Provider( $this->translation_set );
		$provider->schedule_generation();

		$after = wp_next_scheduled( 'traduttore.generate_zip', [ $this->translation_set->id ] );

		$this->assertFalse( $before );
		$this->assertInternalType( 'int', $after );
	}

	public function test_schedule_generation_removes_existing_event(): void {
		$provider = new Provider( $this->translation_set );
		$provider->schedule_generation();
		$provider->schedule_generation();
		$provider->schedule_generation();

		$expected_count = 1;
		$actual_count   = 0;

		$crons = _get_cron_array();
		$key   = md5( serialize( [ $this->translation_set->id ] ) );
		foreach ( $crons as $timestamp => $cron ) {
			if ( isset( $cron['traduttore.generate_zip'][ $key ] ) ) {
				$actual_count ++;
			}
		}

		$this->assertSame( $expected_count, $actual_count );
	}

	public function test_does_not_schedule_generation_after_saving_translation_for_inactive_project(): void {
		$original           = $this->factory->original->create( [ 'project_id' => $this->translation_set->project_id ] );
		$translation_set_id = (int) $this->translation_set->id;

		/** @var \GP_Translation $translation */
		$translation = $this->factory->translation->create(
			[
				'original_id'        => $original->id,
				'translation_set_id' => $this->translation_set->id,
				'status'             => 'current',
				'translation_0'      => 'Before',
			]
		);

		$before = wp_next_scheduled( 'traduttore.generate_zip', [ $translation_set_id ] );

		// Triggers scheduling.
		$result = $translation->save( [ 'translation_0' => 'After' ] );

		$after = wp_next_scheduled( 'traduttore.generate_zip', [ $translation_set_id ] );

		$this->assertFalse( $before );
		$this->assertTrue( $result );
		$this->assertFalse( $after );
	}

	public function test_schedules_generation_after_saving_translation(): void {
		$this->project->get_project()->save( [ 'active' => 1 ] );

		$original           = $this->factory->original->create( [ 'project_id' => $this->translation_set->project_id ] );
		$translation_set_id = (int) $this->translation_set->id;

		/** @var \GP_Translation $translation */
		$translation = $this->factory->translation->create(
			[
				'original_id'        => $original->id,
				'translation_set_id' => $this->translation_set->id,
				'status'             => 'current',
				'translation_0'      => 'Before',
			]
		);

		$before = wp_next_scheduled( 'traduttore.generate_zip', [ $translation_set_id ] );

		// Triggers scheduling.
		$result = $translation->save( [ 'translation_0' => 'After' ] );

		$after = wp_next_scheduled( 'traduttore.generate_zip', [ $translation_set_id ] );

		$this->assertFalse( $before );
		$this->assertTrue( $result );
		$this->assertInternalType( 'int', $after );
	}

	public function test_does_not_schedule_generation_after_importing_originals_for_inactive_project(): void {
		/** @var \GP_Original $original */
		$original = $this->factory->original->create( [ 'project_id' => $this->translation_set->project_id ] );

		$before = wp_next_scheduled( 'traduttore.generate_zip', [ $this->translation_set->id ] );

		$original->import_for_project( $this->translation_set->project, new Translations() );

		$originals_for_project = $original->by_project_id( $this->translation_set->project_id );
		$after                 = wp_next_scheduled( 'traduttore.generate_zip', [ $this->translation_set->id ] );

		$this->assertFalse( $before );
		$this->assertFalse( $after );
		$this->assertCount( 0, $originals_for_project );
	}

	public function test_schedules_generation_after_importing_originals(): void {
		$this->project->get_project()->save( [ 'active' => 1 ] );

		/** @var \GP_Original $original */
		$original = $this->factory->original->create( [ 'project_id' => $this->translation_set->project_id ] );

		$before = wp_next_scheduled( 'traduttore.generate_zip', [ $this->translation_set->id ] );

		$original->import_for_project( $this->translation_set->project, new Translations() );

		$originals_for_project = $original->by_project_id( $this->translation_set->project_id );
		$after                 = wp_next_scheduled( 'traduttore.generate_zip', [ $this->translation_set->id ] );

		$this->assertFalse( $before );
		$this->assertInternalType( 'int', $after );
		$this->assertCount( 0, $originals_for_project );
	}
}
