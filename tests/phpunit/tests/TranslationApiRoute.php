<?php
/**
 * Class TranslationApiRoute
 */

namespace Required\Traduttore\Tests;

use GP;
use GP_Locale;
use GP_Translation_Set;
use GP_UnitTest_Factory;
use Required\Traduttore\Tests\Utils\TestCase_Route;
use ReflectionClass;
use Required\Traduttore\TranslationApiRoute as Route;
use Required\Traduttore\ZipProvider as Provider;

/**
 * Test cases for \Required\Traduttore\TranslationApiRoute.
 */
class TranslationApiRoute extends TestCase_Route {
	/**
	 * @var class-string
	 */
	public $route_class = Route::class;

	protected GP_Translation_Set $translation_set;

	protected GP_Locale $locale;

	/**
	 * Fetches the factory object for generating WordPress fixtures.
	 *
	 * @return \GP_UnitTest_Factory The fixture factory.
	 */
	protected static function factory(): GP_UnitTest_Factory {
		static $factory = null;
		if ( ! $factory ) {
			$factory = new GP_UnitTest_Factory();
		}
		return $factory;
	}

	public function setUp(): void {
		parent::setUp();

		$this->locale = $this->factory()->locale->create(
			[
				'english_name' => 'German',
				'native_name'  => 'Deutsch',
				'slug'         => 'de',
				'wp_locale'    => 'de_DE',
			]
		);

		$this->translation_set = $this->factory()->translation_set->create_with_project(
			[
				'locale' => $this->locale->slug,
			],
			[
				'name' => 'foo-project',
			]
		);
	}

	public function tearDown(): void {
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

	/**
	 * @return array{error?: string, translations?: array<int, array<string, string|string[]>>} Response data.
	 */
	protected function get_route_callback( string $project_path ): array {
		$route = $this->route;

		/**
		 * Route response.
		 *
		 * @var string $response
		 */
		$response = get_echo(
			function () use ( $route, $project_path ): void {
				/** @var \Required\Traduttore\TranslationApiRoute $route */
				$route->route_callback( $project_path );
			}
		);

		/**
		 * @var array{error?: string, translations?: array<int, array<string, string|string[]>>} $result
		 */
		$result = (array) json_decode( $response, true );

		return $result;
	}

	/**
	 * @covers \Required\Traduttore\Plugin::register_glotpress_api_routes
	 */
	public function test_route_exists(): void {
		$class = new ReflectionClass( GP::$router );

		$property = $class->getProperty( 'urls' );
		$property->setAccessible( true );

		/**
		 * Registered routes.
		 *
		 * @var array<string, string> $routes
		 */
		$routes = $property->getValue( GP::$router );

		$this->assertTrue( isset( $routes['get:/api/translations/(.+?)'] ) );
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
		$original = $this->factory()->original->create( [ 'project_id' => $this->translation_set->project_id ] );

		$this->factory()->translation->create(
			[
				'original_id'        => $original->id,
				'translation_set_id' => $this->translation_set->id,
				'status'             => 'current',
			]
		);

		$provider = new Provider( $this->translation_set );

		$provider->generate_zip_file();

		$response = $this->get_route_callback( 'foo-project' );

		$this->assertArrayHasKey( 'translations', $response );
		$this->assertCount( 1, $response['translations'] );
		$this->assertArrayHasKey( 'language', $response['translations'][0] );
		$this->assertSame( 'de_DE', $response['translations'][0]['language'] );
		$this->assertArrayHasKey( 'version', $response['translations'][0] );
		$this->assertSame( '1.0', $response['translations'][0]['version'] );
		$this->assertArrayHasKey( 'english_name', $response['translations'][0] );
		$this->assertSame( 'German', $response['translations'][0]['english_name'] );
		$this->assertArrayHasKey( 'native_name', $response['translations'][0] );
		$this->assertSame( 'Deutsch', $response['translations'][0]['native_name'] );
		$this->assertArrayHasKey( 'package', $response['translations'][0] );
		$this->assertSame( $provider->get_zip_url(), $response['translations'][0]['package'] );
	}

	public function test_missing_build_time(): void {
		$original = $this->factory()->original->create( [ 'project_id' => $this->translation_set->project_id ] );

		$this->factory()->translation->create(
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

		$this->assertArrayHasKey( 'translations', $response );
		$this->assertCount( 0, $response['translations'] );
	}

	public function test_uses_stored_project_version(): void {
		$project = ( new \Required\Traduttore\ProjectLocator( $this->translation_set->project_id ) )->get_project();

		$this->assertInstanceOf( \Required\Traduttore\Project::class, $project );

		$project->set_version( '1.2.3' );

		$original = $this->factory()->original->create( [ 'project_id' => $this->translation_set->project_id ] );

		$this->factory()->translation->create(
			[
				'original_id'        => $original->id,
				'translation_set_id' => $this->translation_set->id,
				'status'             => 'current',
			]
		);

		$provider = new Provider( $this->translation_set );

		$provider->generate_zip_file();

		$response = $this->get_route_callback( 'foo-project' );

		$this->assertArrayHasKey( 'translations', $response );
		$this->assertCount( 1, $response['translations'] );
		$this->assertArrayHasKey( 'language', $response['translations'][0] );
		$this->assertSame( 'de_DE', $response['translations'][0]['language'] );
		$this->assertArrayHasKey( 'version', $response['translations'][0] );
		$this->assertSame( '1.2.3', $response['translations'][0]['version'] );
		$this->assertArrayHasKey( 'english_name', $response['translations'][0] );
		$this->assertSame( 'German', $response['translations'][0]['english_name'] );
		$this->assertArrayHasKey( 'native_name', $response['translations'][0] );
		$this->assertSame( 'Deutsch', $response['translations'][0]['native_name'] );
		$this->assertArrayHasKey( 'package', $response['translations'][0] );
		$this->assertSame( $provider->get_zip_url(), $response['translations'][0]['package'] );
	}
}
