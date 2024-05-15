<?php
/**
 * Translation export class
 *
 * @since 3.0.0
 */

namespace Required\Traduttore;

use GP;
use GP_Locales;
use GP_Translation_Set;

/**
 * Export strings to translation files in PO, MO, and JSON format.
 *
 * @since 3.0.0
 */
class Export {
	/**
	 * The current translation set.
	 *
	 * @since 3.0.0
	 *
	 * @var \GP_Translation_Set
	 */
	protected $translation_set;

	/**
	 * The current locale.
	 *
	 * @since 3.0.0
	 *
	 * @var \GP_Locale
	 */
	protected $locale;

	/**
	 * The current Project instance.
	 *
	 * @since 3.0.0
	 *
	 * @var \Required\Traduttore\Project
	 */
	protected $project;

	/**
	 * List of generated files.
	 *
	 * @since 3.0.0
	 *
	 * @var string[]
	 */
	protected $files;

	/**
	 * Export constructor.
	 *
	 * @param \GP_Translation_Set $translation_set The translation set this export is for.
	 */
	public function __construct( GP_Translation_Set $translation_set ) {
		$this->translation_set = $translation_set;
		$this->locale          = GP_Locales::by_slug( $translation_set->locale );
		$this->project         = new Project( GP::$project->get( $translation_set->project_id ) );
	}

	/**
	 * Saves strings to different file formats and returns a list of generated files.
	 *
	 * @since 3.0.0
	 *
	 * @return array<string,string> List of files with names as key and temporary file location as value.
	 */
	public function export_strings(): ?array {
		$entries = GP::$translation->for_export( $this->project->get_project(), $this->translation_set, [ 'status' => 'current' ] );

		if ( ! $entries ) {
			return null;
		}

		// Build a mapping based on where the translation entries occur and separate the po entries.
		$mapping = $this->map_entries_to_source( $entries );

		$php_entries = \array_key_exists( 'php', $mapping ) ? $mapping['php'] : [];

		unset( $mapping['php'] );

		$this->build_json_files( $mapping );
		$this->build_po_file( $php_entries );
		$this->build_mo_file( $php_entries );
		$this->build_php_file( $php_entries );

		return $this->files;
	}

	/**
	 * Writes content to a file using the WordPress Filesystem Abstraction interface.
	 *
	 * @since 3.0.0
	 *
	 * @param string $file     File path.
	 * @param string $contents File contents.
	 * @return bool True on success, false otherwise.
	 */
	protected function write_to_file( string $file, string $contents ): bool {
		/** @var \WP_Filesystem_Base|null $wp_filesystem */
		global $wp_filesystem;

		if ( ! $wp_filesystem instanceof \WP_Filesystem_Base ) {
			require_once ABSPATH . '/wp-admin/includes/admin.php';

			if ( ! \WP_Filesystem() ) {
				return false;
			}
		}

		return $wp_filesystem->put_contents( $file, $contents, FS_CHMOD_FILE );
	}

	/**
	 * Returns the base name for translation files.
	 *
	 * @since 3.0.0
	 *
	 * @return string Base file name without extension.
	 */
	protected function get_base_file_name(): string {
		$slug        = $this->project->get_slug();
		$text_domain = $this->project->get_text_domain();

		if ( $text_domain ) {
			$slug = $text_domain;
		}

		return "{$slug}-{$this->locale->wp_locale}";
	}

	/**
	 * Build a mapping of JS files to translation entries occurring in those files.
	 *
	 * Translation entries occurring in other files are added to the 'php' key.
	 *
	 * @since 3.0.0
	 *
	 * @param \Translation_Entry[] $entries The translation entries to map.
	 * @return array<string,string> The mapping of sources to translation entries.
	 */
	protected function map_entries_to_source( array $entries ): array {
		$mapping = [];

		foreach ( $entries as $entry ) {
			// Find all unique sources this translation originates from.
			if ( ! empty( $entry->references ) ) {
				$sources = array_map(
					function ( $reference ) {
						$parts = explode( ':', $reference );
						$file  = $parts[0];

						if ( substr( $file, -7 ) === '.min.js' ) {
							return substr( $file, 0, -7 ) . '.js';
						}

						if ( substr( $file, -3 ) === '.js' ) {
							return $file;
						}

						return 'php';
					},
					$entry->references
				);

				$sources = array_unique( $sources );
			} else {
				$sources = [ 'php' ];
			}

			foreach ( $sources as $source ) {
				$mapping[ $source ][] = $entry;
			}
		}

		/**
		 * Filters the mapping of sources to translation entries.
		 *
		 * @since 3.1.0
		 *
		 * @param array                        $mapping The mapping of sources to translation entries.
		 * @param \Translation_Entry[]         $entries The translation entries to map.
		 * @param \Required\Traduttore\Project $project The project that is exported.
		 */
		return (array) apply_filters( 'traduttore.map_entries_to_source', $mapping, $entries, $this->project );
	}

	/**
	 * Builds a mapping of JS file names to translation entries.
	 *
	 * Exports translations for each JS file to a separate translation file.
	 *
	 * @since 3.0.0
	 *
	 * @param array<string,string> $mapping A mapping of files to translation entries.
	 */
	protected function build_json_files( array $mapping ): void {
		/** @var \GP_Format $format */
		$format = gp_array_get( GP::$formats, 'jed1x' );

		$base_file_name = $this->get_base_file_name();

		foreach ( $mapping as $file => $entries ) {
			// Don't create JSON files for source files.
			if ( 0 === strpos( $file, 'src/' ) || false !== strpos( $file, '/src/' ) ) {
				continue;
			}

			$contents = $format->print_exported_file( $this->project->get_project(), $this->locale, $this->translation_set, $entries );

			// Add comment with file reference for debugging.
			$contents_decoded          = json_decode( $contents );
			$contents_decoded->comment = [ 'reference' => $file ];
			$contents                  = wp_json_encode( $contents_decoded );

			$hash      = md5( $file );
			$file_name = "{$base_file_name}-{$hash}.json";
			$temp_file = wp_tempnam( $file_name );

			if ( $this->write_to_file( $temp_file, $contents ) ) {
				$this->files[ $file_name ] = $temp_file;
			}
		}
	}

	/**
	 * Builds a PO file for translations.
	 *
	 * @since 3.0.0
	 *
	 * @param \Translation_Entry[] $entries The translation entries.
	 */
	protected function build_po_file( array $entries ): void {
		/** @var \GP_Format $format */
		$format = gp_array_get( GP::$formats, 'po' );

		$base_file_name = $this->get_base_file_name();
		$file_name      = "{$base_file_name}.po";
		$temp_file      = wp_tempnam( $file_name );

		$contents = $format->print_exported_file( $this->project->get_project(), $this->locale, $this->translation_set, $entries );

		if ( $this->write_to_file( $temp_file, $contents ) ) {
			$this->files[ $file_name ] = $temp_file;
		}
	}

	/**
	 * Builds a MO file for translations.
	 *
	 * @since 3.0.0
	 *
	 * @param \Translation_Entry[] $entries The translation entries.
	 */
	protected function build_mo_file( array $entries ): void {
		/** @var \GP_Format $format */
		$format = gp_array_get( GP::$formats, 'mo' );

		$base_file_name = $this->get_base_file_name();
		$file_name      = "{$base_file_name}.mo";
		$temp_file      = wp_tempnam( $file_name );

		$contents = $format->print_exported_file( $this->project->get_project(), $this->locale, $this->translation_set, $entries );

		if ( $this->write_to_file( $temp_file, $contents ) ) {
			$this->files[ $file_name ] = $temp_file;
		}
	}

	/**
	 * Builds a PHP file for translations.
	 *
	 * @since 3.3.0
	 *
	 * @param \Translation_Entry[] $entries The translation entries.
	 */
	protected function build_php_file( array $entries ): void {
		/** @var \GP_Format $format */
		$format = gp_array_get( GP::$formats, 'php' );

		$base_file_name = $this->get_base_file_name();
		$file_name      = "{$base_file_name}.l10n.php";
		$temp_file      = wp_tempnam( $file_name );

		$contents = $format->print_exported_file( $this->project->get_project(), $this->locale, $this->translation_set, $entries );

		if ( $this->write_to_file( $temp_file, $contents ) ) {
			$this->files[ $file_name ] = $temp_file;
		}
	}
}
