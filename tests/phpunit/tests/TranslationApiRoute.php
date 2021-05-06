<?php
/**
 * Class TranslationApiRoute
 *
 * @package Traduttore\Tests
 */

namespace Required\Traduttore\Tests;

use ReflectionClass;
use GP;
use GP_UnitTestCase_Route;
use Required\Traduttore\TranslationApiRoute as Route;
use Required\Traduttore\ZipProvider as Provider;

/**
 * Test cases for \Required\Traduttore\TranslationApiRoute.
 */
class TranslationApiRoute extends GP_UnitTestCase_Route {
	public $route_class = Route::class;

	/**
	 * @var \GP_Translation_Set
	 */
	protected $translation_set;

	/**
	 * @var \GP_Locale
	 */
	protected $locale;

	public function setUp() {
		parent::setUp();

		$this->locale = $this->factory->locale->create(
			[
				'english_name' => 'German',
				'native_name'  => 'Deutsch',
				'slug'         => 'de',
				'wp_locale'    => 'de_DE',
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
	}

	public function tearDown() {
		/* @var \WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		if ( ! $wp_filesystem ) {
			require_once ABSPATH . '/wp-admin/includes/admin.php';
		}

		if ( \WP_Filesystem() ) {
			$wp_filesystem->rmdir( Provider::get_cache_dir(), true );
		}

		parent::tearDown();
	}

	public function assert404(): void {
		$this->assertSame( 404, $this->route->http_status );
	}

	protected function get_route_callback( $project_path ) {
		$route = $this->route;

		$response = get_echo(
			function() use ( $route, $project_path ) {
				/** @var Route $route */
				return $route->route_callback( $project_path );
			}
		);

		return json_decode( $response, true );
	}

	/**
	 * @covers \Required\Traduttore\Plugin::register_glotpress_api_routes
	 */
	public function test_route_exists(): void {
		$class = new ReflectionClass( GP::$router );

		$property = $class->getProperty( 'urls' );
		$property->setAccessible( true );

		$this->assertTrue( isset( $property->getValue( GP::$router )['get:/api/translations/(.+?)'] ) );
	}

	public function test_invalid_project(): void {
		$response = $this->get_route_callback( 'foo' );

		$this->assertArrayHasKey( 'error', $response );
		$this->assertSame( 'Project not found.', $response['error'] );
		$this->assert404();
	}

	public function test_no_zip_files(): void {
		$response = $this->get_route_callback( 'foo-project' );

		$this->assertSame( [ 'translations' => [] ], $response );
	}

	public function test_one_zip_file(): void {
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

		$response = $this->get_route_callback( 'foo-project' );

		$this->assertCount( 1, $response['translations'] );
		$this->assertArraySubset(
			[
				'language'     => 'de_DE',
				'version'      => '1.0',
				'english_name' => 'German',
				'native_name'  => 'Deutsch',
				'package'      => $provider->get_zip_url(),
			],
			$response['translations'][0]
		);
	}

	public function test_missing_build_time(): void {
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

		gp_delete_meta( $this->translation_set->id, '_traduttore_build_time', null, 'translation_set' );

		$response = $this->get_route_callback( 'foo-project' );

		$this->assertCount( 0, $response['translations'] );
	}

	public function test_uses_stored_project_version(): void {
		$project = ( new \Required\Traduttore\ProjectLocator( $this->translation_set->project_id ) )->get_project();
		$project->set_version( '1.2.3' );

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

		$response = $this->get_route_callback( 'foo-project' );

		$this->assertCount( 1, $response['translations'] );
		$this->assertArraySubset(
			[
				'language'     => 'de_DE',
				'version'      => '1.2.3',
				'english_name' => 'German',
				'native_name'  => 'Deutsch',
				'package'      => $provider->get_zip_url(),
			],
			$response['translations'][0]
		);
	}
}
