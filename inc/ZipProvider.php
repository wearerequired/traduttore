<?php
/**
 * Language pack provider implementation
 *
 * @since 2.0.0
 */

namespace Required\Traduttore;

use DateTime;
use DateTimeZone;
use GP;
use GP_Locales;
use GP_Translation_Set;
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
	 * The current GlotPress translation set.
	 *
	 * @since 2.0.0
	 *
	 * @var \GP_Translation_Set The translation set.
	 */
	protected $translation_set;

	/**
	 * The current GlotPress locale.
	 *
	 * @since 3.0.0
	 *
	 * @var \GP_Locale The locale.
	 */
	protected $locale;

	/**
	 * The current project.
	 *
	 * @since 3.0.0
	 *
	 * @var \Required\Traduttore\Project The project.
	 */
	protected $project;

	/**
	 * ZipProvider constructor.
	 *
	 * @since 2.0.0
	 *
	 * @param \GP_Translation_Set $translation_set Translation set to get the ZIP for.
	 */
	public function __construct( GP_Translation_Set $translation_set ) {
		$this->translation_set = $translation_set;
		$this->locale          = GP_Locales::by_slug( $this->translation_set->locale );
		$this->project         = new Project( GP::$project->get( $this->translation_set->project_id ) );
	}

	/**
	 * Schedules ZIP generation for the current translation set.
	 *
	 * Adds a single cron event to generate the ZIP archive after a short amount of time.
	 *
	 * @since 3.0.0
	 */
	public function schedule_generation(): void {
		$translation_set_id = (int) $this->translation_set->id;

		/**
		 * Filters the delay for scheduled language pack generation.
		 *
		 * @since 3.0.0
		 *
		 * @param int                 $delay           Delay in minutes. Default is 5 minutes.
		 * @param \GP_Translation_Set $translation_set Translation set the ZIP generation will be scheduled for.
		 */
		$delay = (int) apply_filters( 'traduttore.generate_zip_delay', MINUTE_IN_SECONDS * 5, $translation_set_id );

		$next_schedule = wp_next_scheduled( 'traduttore.generate_zip', [ $translation_set_id ] );

		if ( $next_schedule ) {
			wp_unschedule_event( 'traduttore.generate_zip', $next_schedule, [ $translation_set_id ] );
		}

		wp_schedule_single_event( time() + $delay, 'traduttore.generate_zip', [ $translation_set_id ] );
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
	public function generate_zip_file(): bool {
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

		$export = new Export( $this->translation_set );

		$files_for_zip = $export->export_strings();

		if ( ! $files_for_zip ) {
			return false;
		}

		$zip = new ZipArchive();

		$temp_zip_file = wp_tempnam( $this->get_zip_filename() );

		if ( $zip->open( $temp_zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE ) === true ) {
			foreach ( $files_for_zip as $file_name => $temp_file ) {
				$zip->addFile( $temp_file, $file_name );
			}

			$zip->close();
		}

		$wp_filesystem->move( $temp_zip_file, $this->get_zip_path(), true );

		foreach ( $files_for_zip as $temp_file ) {
			$wp_filesystem->delete( $temp_file );
		}

		$last_modified = $this->translation_set->last_modified();

		if ( $last_modified ) {
			$last_modified = new DateTime( $last_modified, new DateTimeZone( 'UTC' ) );
		} else {
			$last_modified = new DateTime( 'now', new DateTimeZone( 'UTC' ) );
		}

		gp_update_meta( $this->translation_set->id, static::BUILD_TIME_KEY, $last_modified->format( DATE_ATOM ), 'translation_set' );

		/**
		 * Fires after a language pack for a given translation set has been generated.
		 *
		 * @since 3.0.0
		 *
		 * @param string                       $file            Path to the generated language pack.
		 * @param string                       $url             URL to the generated language pack.
		 * @param \GP_Translation_Set          $translation_set Translation set the language pack is for.
		 * @param \Required\Traduttore\Project $project         The translation set's project.
		 */
		do_action( 'traduttore.zip_generated', $this->get_zip_path(), $this->get_zip_url(), $this->translation_set, $this->project );

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
	public function remove_zip_file(): bool {
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
	protected function get_zip_filename(): string {
		$slug    = str_replace( '/', '-', $this->project->get_slug() );
		$version = $this->project->get_version();

		if ( $version ) {
			return sprintf(
				'%1$s-%2$s-%3$s.zip',
				$slug,
				$this->locale->wp_locale,
				$version
			);
		}

		return sprintf(
			'%1$s-%2$s.zip',
			$slug,
			$this->locale->wp_locale
		);
	}

	/**
	 * Returns the last ZIP build time for a given translation set.
	 *
	 * @since 2.0.0
	 *
	 * @return \DateTime Last build time.
	 */
	public function get_last_build_time(): ?DateTime {
		$meta = gp_get_meta( 'translation_set', $this->translation_set->id, static::BUILD_TIME_KEY );

		return $meta ? new DateTime( $meta, new DateTimeZone( 'UTC' ) ) : null;
	}

	/**
	 * Returns the full URL to the ZIP file.
	 *
	 * @since 2.0.0
	 *
	 * @return string ZIP file URL.
	 */
	public function get_zip_url(): string {
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
	public function get_zip_path(): string {
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
	public static function get_cache_dir(): string {
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
