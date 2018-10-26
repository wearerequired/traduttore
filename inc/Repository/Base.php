<?php
/**
 * Base repository class.
 *
 * @since 3.0.0
 *
 * @package Required\Traduttore\Repository
 */

namespace Required\Traduttore\Repository;

use Required\Traduttore\Project;
use Required\Traduttore\Repository;

/**
 * Base repository class.
 *
 * @since 3.0.0
 */
class Base implements Repository {
	/**
	 * GlotPress project.
	 *
	 * @since 3.0.0
	 *
	 * @var Project Project information.
	 */
	protected $project;

	/**
	 * Loader constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param Project $project Project information.
	 */
	public function __construct( Project $project ) {
		$this->project = $project;
	}

	/**
	 * Returns the repository type.
	 *
	 * @since 3.0.0
	 *
	 * @return string Repository type.
	 */
	public function get_type() : string {
		$type = $this->project->get_repository_type();

		return $type ?: Repository::TYPE_UNKNOWN;
	}

	/**
	 * Indicates whether a repository is publicly accessible or not.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether the repository is publicly accessible.
	 */
	public function is_public() : bool {
		return 'public' === $this->project->get_repository_visibility();
	}

	/**
	 * Returns the project.
	 *
	 * @since 3.0.0
	 *
	 * @return Project The project.
	 */
	public function get_project(): Project {
		return $this->project;
	}

	/**
	 * Returns the repository host name.
	 *
	 * @since 3.0.0
	 *
	 * @return string Repository host name.
	 */
	public function get_host(): ?string {
		$url = $this->project->get_repository_url();

		return $url ? wp_parse_url( $url, PHP_URL_HOST ) : null;
	}

	/**
	 * Returns the repository name.
	 *
	 * @since 3.0.0
	 *
	 * @return string Repository name.
	 */
	public function get_name(): ?string {
		return $this->project->get_repository_name();
	}

	/**
	 * Returns the repository's SSH URL for cloning based on the project's source URL template.
	 *
	 * @since 3.0.0
	 *
	 * @return string SSH URL to the repository, e.g. git@github.com:wearerequired/traduttore.git.
	 */
	public function get_ssh_url() : string {
		$ssh_url = $this->project->get_repository_ssh_url();

		if ( $ssh_url ) {
			return $ssh_url;
		}

		return sprintf( 'git@%1$s:%2$s.git', $this->get_host(), $this->get_name() );
	}

	/**
	 * Returns the repository's HTTPS URL for cloning based on the project's source URL template.
	 *
	 * @since 3.0.0
	 *
	 * @return string HTTPS URL to the repository, e.g. https://github.com/wearerequired/traduttore.git.
	 */
	public function get_https_url() : string {
		$https_url = $this->project->get_repository_https_url();

		if ( ! $https_url ) {
			$https_url = sprintf( 'https://%1$s/%2$s.git', $this->get_host(), $this->get_name() );
		}

		/**
		 * Filters the credentials to be used for connecting to a Git repository via HTTPS.
		 *
		 * @since 3.0.0
		 *
		 * @param string     $credentials HTTP authentication credentials in the form username:password. Default empty string.
		 * @param Repository $repository  The current repository.
		 */
		$credentials = apply_filters( 'traduttore.git_https_credentials', '', $this );

		if ( ! empty( $credentials ) ) {
			$https_url = str_replace( 'https://', 'https://' . $credentials . '@', $https_url );
		}

		return $https_url;
	}
}
