<?php
/**
 * ZipProvider class.
 *
 * @since 2.0.0
 */

namespace Required\Traduttore;

use GP;
use GP_Format;
use GP_Locale;
use GP_Locales;
use GP_Translation_Set;
use WP_Filesystem_Base;
use ZipArchive;

/**
 * Class used to generate ZIP files for translations.
 *
 * @since 2.0.0
 */
class ZipProvider {
	/**
	 * @since 2.0.0
	 *
	 * @var string Cache directory for ZIP files.
	 */
	const CACHE_DIR = 'traduttore';

	/**
	 * @since 2.0.0
	 *
	 * @var GP_Translation_Set The translation set.
	 */
	protected $translation_set;

	/**
	 * ZipProvider constructor.
	 *
	 * @since 2.0.0
	 *
	 * @param GP_Translation_Set $translation_set Translation set to get the ZIP for.
	 */
	public function __construct( GP_Translation_Set $translation_set ) {
		$this->translation_set = $translation_set;
	}

	/**
	 * Generates and caches a ZIP file for a translation set.
	 *
	 * @since 2.0.0
	 *
	 * @global WP_Filesystem_Base $wp_filesystem
	 *
	 * @return bool True on success, false on failure.
	 */
	public function generate_zip_file() {
		if ( ! class_exists( '\ZipArchive' ) ) {
			return false;
		}

		/** @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		if ( ! $wp_filesystem ) {
			require_once ABSPATH . '/wp-admin/includes/admin.php';

			if ( ! \WP_Filesystem() ) {
				return false;
			}
		}

		// Make sure the cache directory exists.
		if ( ! @is_dir( static::get_cache_dir() ) ) {
			$wp_filesystem->mkdir( static::get_cache_dir(), FS_CHMOD_DIR );
		}

		/** @var GP_Locale $locale */
		$locale  = GP_Locales::by_slug( $this->translation_set->locale );
		$project = GP::$project->get( $this->translation_set->project_id );
		$entries = GP::$translation->for_export( $project, $this->translation_set, [ 'status' => 'current' ] );

		if ( ! $entries ) {
			return false;
		}

		$files_for_zip = [];

		/** @var GP_Format $format */
		foreach ( [ GP::$formats['po'], GP::$formats['mo'] ] as $format ) {
			$file_name = str_replace( '.zip', '.' . $format->extension, $this->get_zip_filename() );
			$temp_file = wp_tempnam( $file_name );

			$contents = $format->print_exported_file( $project, $locale, $this->translation_set, $entries );

			$wp_filesystem->put_contents( $temp_file, $contents, FS_CHMOD_FILE );

			$files_for_zip[ $temp_file ] = $file_name;
		}

		$zip = new ZipArchive();

		if ( $zip->open( $this->get_zip_path(), ZipArchive::CREATE ) === true ) {
			foreach ( $files_for_zip as $temp_file => $file_name ) {
				$zip->addFile( $temp_file, $file_name );
			}

			$zip->close();
		}

		foreach ( $files_for_zip as $temp_file => $file_name ) {
			unlink( $temp_file );
		}

		return true;
	}

	/**
	 * Returns the name of the ZIP file without the path.
	 *
	 * @since 2.0.0
	 *
	 * @return string ZIP filename.
	 */
	protected function get_zip_filename() {
		/** @var GP_Locale $locale */
		$locale  = GP_Locales::by_slug( $this->translation_set->locale );
		$project = GP::$project->get( $this->translation_set->project_id );

		return sprintf(
			'%1$s-%2$s.zip',
			str_replace( '/', '-', $project->slug ),
			$locale->wp_locale
		);
	}

	/**
	 * Returns the full URL to the ZIP file.
	 *
	 * @since 2.0.0
	 *
	 * @return string ZIP file URL.
	 */
	public function get_zip_url() {
		return sprintf(
			'%1$s/%2$s/%3$s',
			WP_CONTENT_URL,
			self::CACHE_DIR,
			$this->get_zip_filename()
		);
	}

	/**
	 * Returns the full path to the ZIP file.
	 *
	 * @since 2.0.0
	 *
	 * @return string ZIP file path.
	 */
	public function get_zip_path() {
		return sprintf(
			'%1$s/%2$s/%3$s',
			WP_CONTENT_DIR,
			self::CACHE_DIR,
			$this->get_zip_filename()
		);
	}

	/**
	 * Returns the full path to the cache directory.
	 *
	 * @since 2.0.0
	 *
	 * @return string Cache directory path.
	 */
	public static function get_cache_dir() {
		return sprintf(
			'%1$s/%2$s',
			WP_CONTENT_DIR,
			self::CACHE_DIR
		);
	}
}
