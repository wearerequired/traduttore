<?php
/**
 * Class Export
 *
 * @package Traduttore\Tests
 */

namespace Required\Traduttore\Tests;

use GP_Translation_Set;
use PO;
use \Required\Traduttore\Export as E;

/**
 * Test cases for \Required\Traduttore\Export.
 */
class Export extends TestCase {
	/**
	 * @var GP_Translation_Set
	 */
	protected $translation_set;

	public function setUp(): void {
		parent::setUp();

		$locale = $this->factory->locale->create(
			[
				'english_name' => 'German',
				'native_name'  => 'Deutsch',
				'slug'         => 'de',
				'wp_locale'    => 'de_DE',
			]
		);

		$this->translation_set = $this->factory->translation_set->create_with_project(
			[
				'locale' => $locale->slug,
			],
			[
				'name' => 'foo-project',
			]
		);
	}

	public function test_does_nothing_for_empty_translation_set(): void {
		$export = new E( $this->translation_set );

		$this->assertNull( $export->export_strings() );
	}

	public function test_creates_only_po_and_mo_files(): void {
		$original = $this->factory->original->create(
			[
				'project_id' => $this->translation_set->project_id,
				'references' => 'my-plugin.php',
			]
		);

		$this->factory->translation->create(
			[
				'original_id'        => $original->id,
				'translation_set_id' => $this->translation_set->id,
				'status'             => 'current',
			]
		);

		$export = new E( $this->translation_set );

		$actual = $export->export_strings();

		array_map( 'unlink', $actual );

		$this->assertEqualSets(
			[
				'foo-project-de_DE.po',
				'foo-project-de_DE.mo',
			],
			array_keys( $actual )
		);
	}

	public function test_creates_multiple_json_files(): void {
		$filename_1 = 'my-super-script';
		$filename_2 = 'my-super-minified-script';

		/* @var \GP_Original $original_1 */
		$original_1 = $this->factory->original->create(
			[
				'project_id' => $this->translation_set->project_id,
				'references' => $filename_1 . '.js',
			]
		);

		/* @var \GP_Original $original_2 */
		$original_2 = $this->factory->original->create(
			[
				'project_id' => $this->translation_set->project_id,
				'references' => $filename_2 . '.min.js',
			]
		);

		$this->factory->translation->create(
			[
				'original_id'        => $original_1->id,
				'translation_set_id' => $this->translation_set->id,
				'status'             => 'current',
			]
		);

		$this->factory->translation->create(
			[
				'original_id'        => $original_2->id,
				'translation_set_id' => $this->translation_set->id,
				'status'             => 'current',
			]
		);

		$export = new E( $this->translation_set );

		$actual = $export->export_strings();

		$json_filename_1 = 'foo-project-de_DE-' . md5( $filename_1 . '.js' ) . '.json';
		$json_filename_2 = 'foo-project-de_DE-' . md5( $filename_2 . '.js' ) . '.json';

		$json_1 = file_get_contents( $actual[ $json_filename_1 ] );
		$json_2 = file_get_contents( $actual[ $json_filename_2 ] );

		array_map( 'unlink', $actual );

		$this->assertJson( $json_1 );
		$this->assertJson( $json_2 );
		$this->assertEqualSets(
			[
				'foo-project-de_DE.po',
				'foo-project-de_DE.mo',
				$json_filename_1,
				$json_filename_2,
			],
			array_keys( $actual )
		);
	}

	/**
	 * Modify the mapping of sources to translation entries.
	 *
	 * @param array $mapping The mapping of sources to translation entries.
	 *
	 * @return array The maybe modified mapping.
	 */
	public function filter_map_entries_to_source( array $mapping ): string {
		$mapping['build.js'] = array_merge( $mapping['my-super-script.js'], $mapping['my-other-script.js'] );

		unset( $mapping['my-super-script.js'], $mapping['my-other-script.js'] );

		return $mapping;
	}

	public function test_map_entries_to_source_filter(): void {
		$filename_1      = 'my-super-script.js';
		$filename_2      = 'my-other-script.js';
		$filename_target = 'build.js';

		/* @var \GP_Original $original_1 */
		$original_1 = $this->factory->original->create(
			[
				'project_id' => $this->translation_set->project_id,
				'references' => $filename_1,
			]
		);

		/* @var \GP_Original $original_2 */
		$original_2 = $this->factory->original->create(
			[
				'project_id' => $this->translation_set->project_id,
				'references' => $filename_2,
			]
		);

		$this->factory->translation->create(
			[
				'original_id'        => $original_1->id,
				'translation_set_id' => $this->translation_set->id,
				'status'             => 'current',
			]
		);

		$this->factory->translation->create(
			[
				'original_id'        => $original_2->id,
				'translation_set_id' => $this->translation_set->id,
				'status'             => 'current',
			]
		);

		$export = new E( $this->translation_set );

		add_filter( 'traduttore.filter_map_entries_to_source', [ $this, 'filter_map_entries_to_source' ] );

		$actual = $export->export_strings();

		remove_filter( 'traduttore.filter_map_entries_to_source', [ $this, 'filter_map_entries_to_source' ] );

		$json_filename_1      = 'foo-project-de_DE-' . md5( $filename_1 ) . '.json';
		$json_filename_2      = 'foo-project-de_DE-' . md5( $filename_2 ) . '.json';
		$json_filename_target = 'foo-project-de_DE-' . md5( $filename_target ) . '.json';

		$this->assertArrayNotHasKey( $json_filename_1, $actual );
		$this->assertArrayNotHasKey( $json_filename_2, $actual );

		$json = file_get_contents( $actual[ $json_filename_target ] );

		array_map( 'unlink', $actual );

		$this->assertJson( $json );
		$this->assertEqualSets(
			[
				'foo-project-de_DE.po',
				'foo-project-de_DE.mo',
				$json_filename_target,
			],
			array_keys( $actual )
		);
	}

	public function test_js_entries_are_not_in_po_file(): void {
		$filename_1 = 'my-super-script';
		$filename_2 = 'my-super-minified-script';

		/* @var \GP_Original $original_1 */
		$original_1 = $this->factory->original->create(
			[
				'project_id' => $this->translation_set->project_id,
				'references' => $filename_1 . '.js',
			]
		);

		/* @var \GP_Original $original_2 */
		$original_2 = $this->factory->original->create(
			[
				'project_id' => $this->translation_set->project_id,
				'references' => $filename_2 . '.min.js',
			]
		);

		/* @var \GP_Original $original_2 */
		$original_3 = $this->factory->original->create(
			[
				'project_id' => $this->translation_set->project_id,
				'references' => 'foo.php',
			]
		);

		$this->factory->translation->create(
			[
				'original_id'        => $original_1->id,
				'translation_set_id' => $this->translation_set->id,
				'status'             => 'current',
			]
		);
		$this->factory->translation->create(
			[
				'original_id'        => $original_2->id,
				'translation_set_id' => $this->translation_set->id,
				'status'             => 'current',
			]
		);
		$this->factory->translation->create(
			[
				'original_id'        => $original_3->id,
				'translation_set_id' => $this->translation_set->id,
				'status'             => 'current',
			]
		);

		$export = new E( $this->translation_set );

		$actual = $export->export_strings();

		$translations = new PO();
		$translations->import_from_file( $actual['foo-project-de_DE.po'] );

		$json_filename_1 = 'foo-project-de_DE-' . md5( $filename_1 . '.js' ) . '.json';
		$json_filename_2 = 'foo-project-de_DE-' . md5( $filename_2 . '.js' ) . '.json';

		$json_1 = json_decode( file_get_contents( $actual[ $json_filename_1 ] ), true );
		$json_2 = json_decode( file_get_contents( $actual[ $json_filename_2 ] ), true );

		array_map( 'unlink', $actual );

		$this->assertCount( 1, $translations->entries );
		$this->assertCount( 2, $json_1['locale_data']['messages'] );
		$this->assertCount( 2, $json_2['locale_data']['messages'] );
		$this->assertArrayHasKey( $original_1->singular, $json_1['locale_data']['messages'] );
		$this->assertArrayHasKey( $original_2->singular, $json_2['locale_data']['messages'] );
		$this->assertArrayHasKey( $original_3->singular, $translations->entries );
	}
}
