<?php
/**
 * Loader class.
 *
 * @since 3.0.0
 *
 * @package Required\Traduttore
 */

namespace Required\Traduttore;

use GP;
use PO;

/**
 * LoaderFactory class.
 *
 * @since 3.0.0
 */
class Updater {
	/**
	 * Lock meta key.
	 *
	 * @since 3.0.0
	 *
	 * @var string Lock meta key.
	 */
	protected const LOCK_KEY = '_traduttore_update_lock';

	/**
	 * GlotPress project.
	 *
	 * @var Project Project information.
	 */
	protected $project;

	/**
	 * Returns a new loader instance for a given project.
	 *
	 * @param Project $project Project information.
	 */
	public function __construct( Project $project ) {
		$this->project = $project;
	}

	/**
	 * Adds a lock to the current project to prevent two simultaneous imports.
	 *
	 * @since 3.0.0
	 */
	public function add_lock(): void {
		gp_update_meta( $this->project->get_id(), static::LOCK_KEY, 1, 'project' );
	}

	/**
	 * Checks whether an import is currently in progress or not.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether the project is locked.
	 */
	public function has_lock(): bool {
		return (bool) gp_get_meta( 'project', $this->project->get_id(), static::LOCK_KEY );
	}

	/**
	 * Removes the lock for the current project.
	 *
	 * @since 3.0.0
	 */
	public function remove_lock(): void {
		gp_delete_meta( $this->project->get_id(), static::LOCK_KEY, null, 'project' );
	}

	/**
	 * Updates the project based on the given configuration.
	 *
	 * @param Configuration $config Configuration object.
	 * @return bool True on success, false otherwise.
	 */
	public function update( Configuration $config ) : bool {
		$pot_file = $this->create_pot_file( $config );

		if ( ! $pot_file ) {
			return false;
		}

		$translations = new PO();

		$result = $translations->import_from_file( $pot_file );

		unlink( $pot_file );

		if ( ! $result ) {
			return false;
		}

		$stats = GP::$original->import_for_project( $this->project->get_project(), $translations );

		/**
		 * Fires after translations have been updated from GitHub.
		 *
		 * @since 3.0.0
		 *
		 * @param Project $project      The GlotPress project that was updated.
		 * @param array   $stats        Stats about the number of imported translations.
		 * @param PO      $translations PO object containing all the translations from the POT file.
		 */
		do_action( 'traduttore_updated', $this->project, $stats, $translations );

		return true;
	}

	/**
	 * Creates a POT file from a given source directory.
	 *
	 * @since 3.0.0
	 *
	 * @param Configuration $config Configuration object.
	 * @return string Path to the POT file.
	 */
	protected function create_pot_file( Configuration $config ) :? string {
		$source  = $config->get_path();
		$merge   = $config->get_config_value( 'mergeWith' );
		$domain  = $config->get_config_value( 'textDomain' );
		$exclude = $config->get_config_value( 'exclude' );

		$merge = $merge ? $source . $merge : null;

		if ( $merge && ! file_exists( $merge ) ) {
			$merge = null;
		}

		$target = $this->get_temp_pot_file();

		exec(
			escapeshellcmd(
				trim(
					sprintf(
						'%1$s i18n make-pot %2$s %3$s --slug=%4$s %5$s %6$s %7$s',
						$this->get_wp_bin(),
						escapeshellarg( $source ),
						escapeshellarg( $target ),
						escapeshellarg( $this->project->get_slug() ),
						$merge ? escapeshellarg( '--merge=' . $merge ) : '',
						$domain ? escapeshellarg( '--domain=' . $domain ) : '',
						$exclude ? escapeshellarg( '--exclude=' . implode( ',', $exclude ) ) : ''
					)
				)
			),
			$output,
			$status
		);

		return 0 === $status ? $target : null;
	}

	/**
	 * Returns the path to the WP-CLI binary.
	 *
	 * Allows overriding the path to the binary via the TRADUTTORE_WP_BIN constant.
	 *
	 * @return string WP-CLI binary path.
	 */
	protected function get_wp_bin() : string {
		if ( defined( 'TRADUTTORE_WP_BIN' ) && TRADUTTORE_WP_BIN ) {
			return TRADUTTORE_WP_BIN;
		}

		return 'wp';
	}

	/**
	 * Returns the path to the temporary POT file.
	 *
	 * @return string POT file path.
	 */
	protected function get_temp_pot_file() : string {
		require_once ABSPATH . 'wp-admin/includes/file.php';

		return wp_tempnam( sprintf( 'traduttore-%s.pot', $this->project->get_slug() ) );
	}
}
