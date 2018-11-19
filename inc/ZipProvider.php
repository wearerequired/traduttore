<?php
/**
 * ZipProvider class.
 *
 * @since 2.0.0
 *
 * @package Required\Traduttore
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
 * Class used to generate language packs for translations.
 *
 * @since 2.0.0
 */
class ZipProvider {
	/**
	 * Traduttore cache directory name.
	 *
	 * @since 2.0.0
	 *
	 * @var string Cache directory for ZIP files.
	 */
	protected const CACHE_DIR = 'traduttore';

	/**
	 * Build time meta key.
	 *
	 * @since 2.0.0
	 *
	 * @var string Build time meta key.
	 */
	protected const BUILD_TIME_KEY = '_traduttore_build_time';

	/**
	 * The GlotPress translation set.
	 *
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
	 * Schedules ZIP generation for the current translation set.
	 *
	 * Adds a single cron event to generate the ZIP archive after a short amount of time.
	 *
	 * @since 3.0.0
	 */
	public function schedule_generation(): void {
		/**
		 * Filters the delay for scheduled language pack generation.
		 *
		 * @since 3.0.0
		 *
		 * @param int                $delay           Delay in minutes. Default is 5 minutes.
		 * @param GP_Translation_Set $translation_set Translation set the ZIP generation will be scheduled for.
		 */
		$delay = (int) apply_filters( 'traduttore.generate_zip_delay', MINUTE_IN_SECONDS * 5, $this->translation_set->id );

		$next_schedule = wp_next_scheduled( 'traduttore.generate_zip', [ $this->translation_set->id ] );

		if ( $next_schedule ) {
			wp_unschedule_event( 'traduttore.generate_zip', $next_schedule, [ $this->translation_set->id ] );
		}

		wp_schedule_single_event( time() + $delay, 'traduttore.generate_zip', [ $this->translation_set->id ] );
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
	public function generate_zip_file() : bool {
		if ( ! class_exists( '\ZipArchive' ) ) {
			return false;
		}

		/* @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		if ( ! $wp_filesystem ) {
			require_once ABSPATH . '/wp-admin/includes/admin.php';

			if ( ! \WP_Filesystem() ) {
				return false;
			}
		}

		// Make sure the cache directory exists.
		if ( ! is_dir( static::get_cache_dir() ) ) {
			$wp_filesystem->mkdir( static::get_cache_dir(), FS_CHMOD_DIR );
		}

		/* @var GP_Locale $locale */
		$locale     = GP_Locales::by_slug( $this->translation_set->locale );
		$gp_project = GP::$project->get( $this->translation_set->project_id );
		$entries    = GP::$translation->for_export( $gp_project, $this->translation_set, [ 'status' => 'current' ] );

		if ( ! $entries ) {
			return false;
		}

		$files_for_zip = [];
		$project       = new Project( $gp_project );
		$text_domain   = $project->get_text_domain();

		/* @var GP_Format $format */
		foreach ( [ GP::$formats['po'], GP::$formats['mo'] ] as $format ) {
			$file_name = str_replace( '.zip', '.' . $format->extension, $this->get_zip_filename() );

			if ( $text_domain ) {
				$file_name = $text_domain . '.' . $format->extension;
			}

			$temp_file = wp_tempnam( $file_name );

			$contents = $format->print_exported_file( $gp_project, $locale, $this->translation_set, $entries );

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

		gp_update_meta( $this->translation_set->id, static::BUILD_TIME_KEY, $this->translation_set->last_modified(), 'translation_set' );

		/**
		 * Fires after a language pack for a given translation set has been generated.
		 *
		 * @since 3.0.0
		 *
		 * @param string             $file            Path to the generated language pack.
		 * @param string             $url             URL to the generated language pack.
		 * @param GP_Translation_Set $translation_set Translation set the language pack is for.
		 */
		do_action( 'traduttore.zip_generated', $this->get_zip_path(), $this->get_zip_url(), $this->translation_set );

		return true;
	}

	/**
	 * Removes the ZIP file for a translation set.
	 *
	 * @since 3.0.0
	 *
	 * @global WP_Filesystem_Base $wp_filesystem
	 *
	 * @return bool True on success, false on failure.
	 */
	public function remove_zip_file() : bool {
		if ( ! file_exists( $this->get_zip_path() ) ) {
			return false;
		}

		/* @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		if ( ! $wp_filesystem ) {
			require_once ABSPATH . '/wp-admin/includes/admin.php';

			if ( ! \WP_Filesystem() ) {
				return false;
			}
		}

		$success = $wp_filesystem->rmdir( $this->get_zip_path(), true );

		if ( $success ) {
			gp_update_meta( $this->translation_set->id, static::BUILD_TIME_KEY, '', 'translation_set' );
		}

		return $success;
	}

	/**
	 * Returns the name of the ZIP file without the path.
	 *
	 * @since 2.0.0
	 *
	 * @return string ZIP filename.
	 */
	protected function get_zip_filename() : string {
		/* @var GP_Locale $locale */
		$locale  = GP_Locales::by_slug( $this->translation_set->locale );
		$project = GP::$project->get( $this->translation_set->project_id );

		return sprintf(
			'%1$s-%2$s.zip',
			str_replace( '/', '-', $project->slug ),
			$locale->wp_locale
		);
	}

	/**
	 * Returns the last ZIP build time for a given translation set.
	 *
	 * @since 2.0.0
	 *
	 * @return string Last build time.
	 */
	public function get_last_build_time() :? string {
		$meta = gp_get_meta( 'translation_set', $this->translation_set->id, static::BUILD_TIME_KEY );

		return $meta ?: null;
	}

	/**
	 * Returns the full URL to the ZIP file.
	 *
	 * @since 2.0.0
	 *
	 * @return string ZIP file URL.
	 */
	public function get_zip_url() : string {
		$url = content_url( self::CACHE_DIR );

		/**
		 * Filters the path to Traduttore's cache directory.
		 *
		 * Useful when language packs should be stored somewhere else.
		 *
		 * @since 3.0.0
		 *
		 * @param string $url Cache directory URL.
		 */
		$url = apply_filters( 'traduttore.content_url', $url );

		return sprintf(
			'%1$s/%2$s',
			$url,
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
	public function get_zip_path() : string {
		return sprintf(
			'%1$s/%2$s',
			static::get_cache_dir(),
			$this->get_zip_filename()
		);
	}

	/**
	 * Returns the full path to the directory where language packs are stored.
	 *
	 * @since 2.0.0
	 *
	 * @return string Cache directory path.
	 */
	public static function get_cache_dir() : string {
		$dir = sprintf(
			'%1$s/%2$s',
			WP_CONTENT_DIR,
			self::CACHE_DIR
		);

		/**
		 * Filters the path to Traduttore's cache directory.
		 *
		 * Useful when language packs should be stored somewhere else.
		 *
		 * @since 3.0.0
		 *
		 * @param string $dir Cache directory path.
		 */
		return apply_filters( 'traduttore.content_dir', $dir );
	}
}
