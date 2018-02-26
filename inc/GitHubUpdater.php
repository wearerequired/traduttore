<?php
/**
 * GitHubUpdater class.
 *
 * @since 1.0.0
 */

namespace Required\Traduttore;

use GP;
use GP_Project;
use PO;

/**
 * Updates a project's translation from a GitHub repository.
 *
 * @since 1.0.0
 */
class GitHubUpdater {
	/**
	 * @since 1.0.0
	 *
	 * @var string Repository SSH URL.
	 */
	protected $ssh_url;

	/**
	 * @since 1.0.0
	 *
	 * @var GP_Project GlotPress project.
	 */
	protected $project;

	/**
	 * GitHubUpdater constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $repository GitHub repository URL, e.g. https://github.com/wearerequired/required-valencia.
	 * @param GP_Project $project GlotPress project.
	 */
	public function __construct( $repository, GP_Project $project ) {
		$this->ssh_url = $this->get_ssh_from_url( $repository );
		$this->project = $project;
	}

	/**
	 * Find a GlotPress project by a GitHub repository URL.
	 *
	 * @since 1.0.0
	 *
	 * @param string $html_url GitHub repository URL, e.g. https://github.com/wearerequired/required-valencia.
	 *
	 * @return false|GP_Project Project on success, false otherwise.
	 */
	public static function find_project( $html_url ) {
		global $wpdb;

		$table = GP::$project->table;

		$query = $wpdb->prepare( "SELECT * FROM $table WHERE source_url_template LIKE %s LIMIT 1", $wpdb->esc_like( $html_url ) . '%' );

		return GP::$project->coerce( $wpdb->get_row( $query ) );
	}

	/**
	 * Turns a regular repository URL into one that can be connected to via SSH.
	 *
	 * @since 1.0.0
	 *
	 * @param string $url GitHub repository URL, e.g. https://github.com/wearerequired/required-valencia.
	 *
	 * @return string SSH URL to the repository, e.g. git@github.com:wearerequired/required-valencia.git.
	 */
	protected function get_ssh_from_url( $url ) {
		$path = wp_parse_url( $url, PHP_URL_PATH );

		return sprintf( 'git@github.com:%s.git', ltrim( $path, '/' ) );
	}

	/**
	 * Fetches the GitHub repository and updates the translations based on the source code.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True on success, false otherwise.
	 */
	public function fetch_and_update() {
		$slug       = $this->project->slug;
		$git_target = get_temp_dir() . 'traduttore-github-' . $slug;
		$pot_target = wp_tempnam( 'traduttore-' . $slug . '.pot' );

		$result = $this->fetch_github_repository( $this->ssh_url, $git_target );

		if ( ! $result ) {
			return false;
		}

		$result = $this->create_pot_file( $git_target, $pot_target, $slug );

		if ( ! $result ) {
			return false;
		}

		$translations = new PO();
		$result       = $translations->import_from_file( $pot_target );

		unlink( $pot_target );

		if ( ! $result ) {
			return false;
		}

		GP::$original->import_for_project( $this->project, $translations );

		return true;
	}

	/**
	 * Fetches a remote repository from GitHub.
	 *
	 * If the repository has been cloned before, the latest changes will be pulled.
	 *
	 * @since 1.0.0
	 *
	 * @param string $source GitHub repository URL.
	 * @param string $target Target directory.
	 *
	 * @return bool True on success, false otherwise.
	 */
	protected function fetch_github_repository( $source, $target ) {
		if ( is_dir( $target ) ) {
			$current_dir = getcwd();
			chdir( $target );
			exec( escapeshellcmd( 'git reset --hard -q' ), $output, $status );
			exec( escapeshellcmd( 'git pull -q' ), $output, $status );
			chdir( $current_dir );
		} else {
			exec( escapeshellcmd( sprintf( 'git clone %1$s %2$s', $source, $target ) ), $output, $status );
		}

		return 0 === $status;
	}

	/**
	 * Creates a POT file from a given source directory.
	 *
	 * @since 1.0.0
	 *
	 * @param string $source Source directory.
	 * @param string $target Target file name.
	 * @param string $slug Project slug/domain.
	 *
	 * @return bool True on success, false otherwise.
	 */
	protected function create_pot_file( $source, $target, $slug ) {
		exec( escapeshellcmd( sprintf( 'wp i18n make-pot %1$s %2$s --slug=%3$s --domain=%3$s', $source, $target, $slug ) ), $output, $status );

		return 0 === $status;
	}
}
