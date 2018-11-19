<?php
/**
 * Translation export class.
 *
 * @since 3.0.0
 *
 * @package Required\Traduttore
 */

namespace Required\Traduttore;

use GP;
use GP_Locale;
use GP_Locales;
use GP_Translation_Set;
use Translation_Entry;

/**
 * Class used to export strings to translation files
 *
 * @since 3.0.0
 */
class Export {
	/**
	 * @var \GP_Translation_Set
	 */
	protected $translation_set;

	/**
	 * @var \GP_Locale
	 */
	protected $locale;

	/**
	 * @var Project
	 */
	protected $project;

	/**
	 * @var array
	 */
	protected $files;

	/**
	 * Export constructor.
	 *
	 * @param GP_Translation_Set $translation_set The translation set this export is for.
	 */
	public function __construct( GP_Translation_Set $translation_set ) {
		$this->translation_set = $translation_set;
		$this->locale          = GP_Locales::by_slug( $translation_set->locale );
		$this->project         = new Project( GP::$project->get( $translation_set->project_id ) );

		/* @var \WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		if ( ! $wp_filesystem ) {
			require_once ABSPATH . '/wp-admin/includes/admin.php';

			\WP_Filesystem();
		}
	}

	/**
	 * @return array List of files with names as key and temporary file location as value.
	 */
	public function export_strings(): ?array {
		$entries = GP::$translation->for_export( $this->project->get_project(), $this->translation_set, [ 'status' => 'current' ] );

		if ( ! $entries ) {
			return null;
		}

		// Build a mapping based on where the translation entries occur and separate the po entries.
		$mapping = $this->map_entries_to_source( $entries );

		$po_entries = array_key_exists( 'po', $mapping ) ? $mapping['po'] : [];

		unset( $mapping['po'] );

		$this->build_json_files( $mapping );
		$this->build_po_file( $po_entries );
		$this->build_mo_file( $po_entries );

		return $this->files;
	}

	/**
	 * @since 3.0.0
	 *
	 * @return string
	 */
	protected function get_base_filename(): string {
		/* @var GP_Locale $locale */
		$locale = GP_Locales::by_slug( $this->translation_set->locale );

		$slug        = $this->project->get_slug();
		$text_domain = $this->project->get_text_domain();

		if ( $text_domain ) {
			$slug = $text_domain;
		}

		if ( ! $locale ) {
			return $slug;
		}

		return "{$slug}-{$locale->wp_locale}";
	}

	/**
	 * Build a mapping of JS files to translation entries occurring in those files.
	 *
	 * Translation entries occurring in other files are added to the 'po' key.
	 *
	 * @since 3.0.0
	 *
	 * @param Translation_Entry[] $entries The translation entries to map.
	 *
	 * @return array The mapping of sources to translation entries.
	 */
	protected function map_entries_to_source( $entries ): array {
		$mapping = [];

		foreach ( $entries as $entry ) {
			// Find all unique sources this translation originates from.
			$sources = array_map(
				function ( $reference ) {
					$parts = explode( ':', $reference );
					$file  = $parts[0];

					if ( substr( $file, - 7 ) === '.min.js' ) {
						return substr( $file, 0, - 7 ) . '.js';
					}

					if ( substr( $file, - 3 ) === '.js' ) {
						return $file;
					}

					return 'po';
				},
				$entry->references
			);

			// Always add all entries to the PO file.
			$sources[] = 'po';

			$sources = array_unique( $sources );

			foreach ( $sources as $source ) {
				$mapping[ $source ][] = $entry;
			}
		}

		return $mapping;
	}

	/**
	 * Builds a mapping of JS file names to translation entries.
	 *
	 * Exports translations for each JS file to a separate translation file.
	 *
	 * @since 3.0.0
	 *
	 * @param array $mapping A mapping of files to translation entries.
	 */
	protected function build_json_files( $mapping ): void {
		global $wp_filesystem;

		/** @var \GP_Format $format */
		$format = gp_array_get( GP::$formats, 'jed1x' );

		$base_file_name = $this->get_base_filename();

		foreach ( $mapping as $file => $entries ) {
			$contents = $format->print_exported_file( $this->project->get_project(), $this->locale, $this->translation_set, $entries );

			$hash      = md5( $file );
			$file_name = "{$base_file_name}-{$hash}.json";

			$temp_file = wp_tempnam( $file_name );

			$wp_filesystem->put_contents( $temp_file, $contents, FS_CHMOD_FILE );

			$this->files[ $file_name ] = $temp_file;
		}
	}

	/**
	 * Builds a PO file for translations.
	 *
	 * @since 3.0.0
	 *
	 * @param Translation_Entry[] $entries The translation entries.
	 */
	protected function build_po_file( $entries ): void {
		global $wp_filesystem;

		/** @var \GP_Format $format */
		$format = gp_array_get( GP::$formats, 'po' );

		$base_file_name = $this->get_base_filename();
		$file_name      = "{$base_file_name}.po";
		$temp_file      = wp_tempnam( $file_name );

		$contents = $format->print_exported_file( $this->project->get_project(), $this->locale, $this->translation_set, $entries );

		$wp_filesystem->put_contents( $temp_file, $contents, FS_CHMOD_FILE );

		$this->files[ $file_name ] = $temp_file;
	}

	/**
	 * Builds a MO file for translations.
	 *
	 * @since 3.0.0
	 *
	 * @param Translation_Entry[] $entries The translation entries.
	 */
	protected function build_mo_file( $entries ): void {
		global $wp_filesystem;

		/** @var \GP_Format $format */
		$format = gp_array_get( GP::$formats, 'mo' );

		$base_file_name = $this->get_base_filename();
		$file_name      = "{$base_file_name}.mo";
		$temp_file      = wp_tempnam( $file_name );

		$contents = $format->print_exported_file( $this->project->get_project(), $this->locale, $this->translation_set, $entries );

		$wp_filesystem->put_contents( $temp_file, $contents, FS_CHMOD_FILE );

		$this->files[ $file_name ] = $temp_file;
	}
}
