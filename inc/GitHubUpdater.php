<?php
/**
 * GitHubUpdater class.
 *
 * @since 2.0.0
 *
 * @package Required\Traduttore
 */

namespace Required\Traduttore;

use GP;
use GP_Project;
use PO;

/**
 * Updates a project's translation from a GitHub repository.
 *
 * @since 2.0.0
 */
class GitHubUpdater {
	/**
	 * Lock meta key.
	 *
	 * @since 2.0.0
	 *
	 * @var string Lock meta key.
	 */
	protected const LOCK_KEY = '_traduttore_update_lock';

	/**
	 * GlotPress project.
	 *
	 * @since 2.0.0
	 *
	 * @var GP_Project GlotPress project.
	 */
	protected $project;

	/**
	 * The project's repository name.
	 *
	 * @since 2.0.0
	 *
	 * @var string Repository name.
	 */
	protected $repository_name;

	/**
	 * GitHubUpdater constructor.
	 *
	 * @since 2.0.0
	 *
	 * @param GP_Project $project GlotPress project.
	 */
	public function __construct( GP_Project $project ) {
		$this->project         = $project;
		$this->repository_name = $this->get_repository_name();
	}

	/**
	 * Returns the name for a Git repository.
	 *
	 * @since 2.0.3
	 *
	 * @return string Repository name in the form <owner>/<repo>, e.g. wearerequired/traduttore.
	 */
	protected function get_repository_name() : string {
		// e.g. https://github.com/wearerequired/traduttore/blob/master/%file%#L%line%.
		$url   = $this->project->source_url_template();
		$parts = explode( '/blob/', wp_parse_url( $url, PHP_URL_PATH ) );
		$path  = array_shift( $parts );

		return ltrim( $path, '/' );
	}

	/**
	 * Indicates whether a GitHub repository is publicly accessible or not.
	 *
	 * @since 2.0.3.
	 *
	 * @return bool Whether the repository is publicly accessible.
	 */
	protected function is_public_repository() : bool {
		$response = wp_remote_head( 'https://api.github.com/repos/' . $this->repository_name );

		return 200 === wp_remote_retrieve_response_code( $response );
	}

	/**
	 * Returns the repository's clone URL.
	 *
	 * Supports either HTTPS or SSH.
	 *
	 * @since 2.0.3
	 *
	 * @return string SSH URL to the repository, e.g. git@github.com:wearerequired/traduttore.git
	 *                or https://github.com/wearerequired/traduttore.git.
	 */
	protected function get_clone_url() : string {
		/**
		 * Filters whether HTTPS or SSH should be used to clone a repository.
		 *
		 * @since 2.0.3
		 *
		 * @param bool       $use_https Whether to use HTTPS or SSH. Defaults to HTTPS for public repositories.
		 * @param GP_Project $project   The currrent GlotPress project.
		 */
		$use_https = apply_filters( 'traduttore_git_clone_use_https', $this->is_public_repository(), $this->project );

		$clone_url = $this->get_ssh_url();

		if ( $use_https ) {
			$clone_url = $this->get_https_url();
		}

		/**
		 * Filters the URL used to clone a Git repository.
		 *
		 * @since 2.0.3
		 *
		 * @param string     $clone_url The URL to clone a Git repository.
		 * @param GP_Project $project   The currrent GlotPress project.
		 */
		return apply_filters( 'traduttore_git_clone_url', $clone_url, $this->project );
	}

	/**
	 * Returns the repository's SSH URL for cloning based on the project's source URL template.
	 *
	 * @since 2.0.0
	 *
	 * @return string SSH URL to the repository, e.g. git@github.com:wearerequired/traduttore.git.
	 */
	protected function get_ssh_url() : string {
		return sprintf( 'git@github.com:%s.git', $this->repository_name );
	}

	/**
	 * Returns the repository's HTTPS URL for cloning based on the project's source URL template.
	 *
	 * @since 2.0.3
	 *
	 * @return string HTTPS URL to the repository, e.g. https://github.com/wearerequired/traduttore.git.
	 */
	protected function get_https_url() : string {
		/**
		 * Filters the credentials to be used for connecting to GitHub via HTTPS.
		 *
		 * @since 2.0.3
		 *
		 * @param string     $credentials GitHub credentials in the form username:password. Default empty string.
		 * @param GP_Project $project     The currrent GlotPress project.
		 */
		$credentials = apply_filters( 'traduttore_github_https_credentials', '', $this->project );

		if ( ! empty( $credentials ) ) {
			return sprintf( 'https://%1$s@github.com/%2$s.git', $credentials, $this->repository_name );
		}

		return sprintf( 'https://github.com/%s.git', $this->repository_name );
	}

	/**
	 * Returns the path to where the GitHub repository should be checked out.
	 *
	 * @since 2.0.0
	 *
	 * @return string Git repository path.
	 */
	public function get_repository_path() : string {
		$slug = $this->project->slug;

		return get_temp_dir() . 'traduttore-github-' . $slug;
	}

	/**
	 * Attempts to delete the folder containing the local repository checkout.
	 *
	 * @since 2.0.0
	 *
	 * @return bool True on success, false on failure.
	 */
	public function remove_local_repository() : bool {
		/* @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		if ( ! $wp_filesystem ) {
			require_once ABSPATH . '/wp-admin/includes/admin.php';

			if ( ! \WP_Filesystem() ) {
				return false;
			}
		}

		return $wp_filesystem->rmdir( $this->get_repository_path(), true );
	}

	/**
	 * Fetches the GitHub repository and updates the translations based on the source code.
	 *
	 * @since 2.0.0
	 *
	 * @param bool $delete Whether to first delete the existing local repository or not.
	 * @return bool True on success, false otherwise.
	 */
	public function fetch_and_update( $delete = false ) : bool {
		if ( $this->has_lock() ) {
			return false;
		}

		// Include Administration File API for wp_tempnam().
		require_once ABSPATH . 'wp-admin/includes/file.php';

		$slug       = $this->project->slug;
		$git_target = $this->get_repository_path();
		$pot_target = wp_tempnam( 'traduttore-' . $slug . '.pot' );

		$this->add_lock();

		if ( $delete ) {
			$this->remove_local_repository();
		}

		$result = $this->fetch_github_repository( $this->get_clone_url(), $git_target );

		if ( ! $result ) {
			$this->remove_lock();

			return false;
		}

		$result = $this->create_pot_file( $git_target, $pot_target, $slug );

		if ( ! $result ) {
			$this->remove_lock();

			return false;
		}

		$translations = new PO();
		$result       = $translations->import_from_file( $pot_target );

		unlink( $pot_target );

		if ( ! $result ) {
			$this->remove_lock();

			return false;
		}

		$stats = GP::$original->import_for_project( $this->project, $translations );

		/**
		 * Fires after translations have been updated from GitHub.
		 *
		 * @since 2.0.0
		 *
		 * @param GP_Project $project      The GlotPress project that was updated.
		 * @param array      $stats        Stats about the number of imported translations.
		 * @param PO         $translations PO object containing all the translations from the POT file.
		 */
		do_action( 'traduttore_updated_from_github', $this->project, $stats, $translations );

		$this->remove_lock();

		return true;
	}

	/**
	 * Fetches a remote repository from GitHub.
	 *
	 * If the repository has been cloned before, the latest changes will be pulled.
	 *
	 * @since 2.0.0
	 *
	 * @param string $source GitHub repository URL.
	 * @param string $target Target directory.
	 * @return bool True on success, false otherwise.
	 */
	protected function fetch_github_repository( $source, $target ) : bool {
		if ( is_dir( $target ) ) {
			$current_dir = getcwd();
			chdir( $target );
			exec( escapeshellcmd( 'git reset --hard -q' ), $output, $status );
			exec( escapeshellcmd( 'git pull -q' ), $output, $status );
			chdir( $current_dir );
		} else {
			exec(
				escapeshellcmd(
					sprintf(
						'git clone --depth=1 %1$s %2$s -q',
						escapeshellarg( $source ),
						escapeshellarg( $target )
					)
				), $output, $status
			);
		}

		return 0 === $status;
	}

	/**
	 * Creates a POT file from a given source directory.
	 *
	 * @since 2.0.0
	 *
	 * @param string $source Source directory.
	 * @param string $target Target file name.
	 * @param string $slug Project slug/domain.
	 * @return bool True on success, false otherwise.
	 */
	protected function create_pot_file( $source, $target, $slug ) : bool {
		// Allow to override the path to the WP-CLI bin.
		$wp_bin = 'wp';
		if ( defined( 'TRADUTTORE_WP_BIN' ) && TRADUTTORE_WP_BIN ) {
			$wp_bin = TRADUTTORE_WP_BIN;
		}

		exec(
			escapeshellcmd(
				sprintf(
					'%1$s i18n make-pot %2$s %3$s --slug=%4$s',
					$wp_bin,
					escapeshellarg( $source ),
					escapeshellarg( $target ),
					escapeshellarg( $slug )
				)
			), $output, $status
		);

		return 0 === $status;
	}

	/**
	 * Adds a lock to the current project to prevent two simultaneous imports.
	 *
	 * @since 2.0.0
	 */
	protected function add_lock(): void {
		gp_update_meta( $this->project->id, static::LOCK_KEY, 1, 'project' );
	}

	/**
	 * Checks whether an import is currently in progress or not.
	 *
	 * @since 2.0.0
	 *
	 * @return bool Whether the project is locked.
	 */
	protected function has_lock(): bool {
		return (bool) gp_get_meta( 'project', $this->project->id, static::LOCK_KEY );
	}

	/**
	 * Removes the lock for the current project.
	 *
	 * @since 2.0.0
	 */
	protected function remove_lock(): void {
		gp_delete_meta( $this->project->id, static::LOCK_KEY, null, 'project' );
	}
}
