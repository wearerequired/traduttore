<?php
/**
 * Git loader class.
 *
 * @package Required\Traduttore
 */

namespace Required\Traduttore\Loader;

use Required\Traduttore\Repository;
use Required\Traduttore\Loader;

/**
 * Git loader.
 *
 * @since 3.0.0
 */
abstract class Git implements Loader {
	/**
	 * Repository information.
	 *
	 * @since 3.0.0
	 *
	 * @var Repository Repository object.
	 */
	protected $repository;

	/**
	 * Class constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param Repository $repository Repository instance.
	 */
	public function __construct( Repository $repository ) {
		$this->repository = $repository;
	}

	/**
	 * Downloads a remote Git repository.
	 *
	 * If the repository has been cloned before, the latest changes will be pulled.
	 *
	 * @since 3.0.0
	 *
	 * @return string Path to the downloaded repository on success.
	 */
	public function download() :? string {
		$target = $this->get_local_path();

		if ( is_dir( $target ) ) {
			$current_dir = getcwd();
			chdir( $target );
			exec( escapeshellcmd( 'git reset --hard -q' ), $output, $status );
			exec( escapeshellcmd( 'git pull -q' ), $output, $status );
			chdir( $current_dir );

			return 0 === $status;
		}

		exec(
			escapeshellcmd(
				sprintf(
					'git clone --depth=1 %1$s %2$s -q',
					escapeshellarg( $this->get_clone_url() ),
					escapeshellarg( $target )
				)
			), $output, $status
		);

		return 0 === $status ? $target : null;
	}

	/**
	 * Returns the path to where the Git repository should be checked out.
	 *
	 * @since 3.0.0
	 *
	 * @return string Git repository path.
	 */
	public function get_local_path() : string {
		return sprintf(
			'%1$s-traduttore-%2$s-%3$s',
			get_temp_dir(),
			$this->repository->get_host(),
			$this->repository->get_slug()
		);
	}

	/**
	 * Indicates whether a Git repository is publicly accessible or not.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether the repository is publicly accessible.
	 */
	protected function is_public_repository() : bool {
		return false;
	}

	/**
	 * Returns the repository's clone URL.
	 *
	 * Supports either HTTPS or SSH.
	 *
	 * @since 3.0.0
	 *
	 * @return string SSH URL to the repository, e.g. git@github.com:wearerequired/traduttore.git
	 *                or https://github.com/wearerequired/traduttore.git.
	 */
	protected function get_clone_url() : string {
		/**
		 * Filters whether HTTPS or SSH should be used to clone a repository.
		 *
		 * @since 3.0.0
		 *
		 * @param bool       $use_https  Whether to use HTTPS or SSH. Defaults to HTTPS for public repositories.
		 * @param Repository $repository The current repository.
		 */
		$use_https = apply_filters( 'traduttore.git_clone_use_https', $this->is_public_repository(), $this->repository );

		$clone_url = $this->get_ssh_url();

		if ( $use_https ) {
			$clone_url = $this->get_https_url();
		}

		/**
		 * Filters the URL used to clone a Git repository.
		 *
		 * @since 3.0.0
		 *
		 * @param string     $clone_url  The URL to clone a Git repository.
		 * @param Repository $repository The current repository.
		 */
		return apply_filters( 'traduttore.git_clone_url', $clone_url, $this->repository );
	}

	/**
	 * Returns the repository's SSH URL for cloning based on the project's source URL template.
	 *
	 * @since 3.0.0
	 *
	 * @return string SSH URL to the repository, e.g. git@github.com:wearerequired/traduttore.git.
	 */
	protected function get_ssh_url() : string {
		return sprintf( 'git@%1$s:%2$s.git', $this->repository->get_host(), $this->repository->get_name() );
	}

	/**
	 * Returns the repository's HTTPS URL for cloning based on the project's source URL template.
	 *
	 * @since 3.0.0
	 *
	 * @return string HTTPS URL to the repository, e.g. https://github.com/wearerequired/traduttore.git.
	 */
	protected function get_https_url() : string {
		/**
		 * Filters the credentials to be used for connecting to a Git repository via HTTPS.
		 *
		 * @since 3.0.0
		 *
		 * @param string     $credentials Git credentials in the form username:password. Default empty string.
		 * @param Repository $repository  The current repository.
		 */
		$credentials = apply_filters( 'traduttore.git_https_credentials', '', $this->repository );

		if ( ! empty( $credentials ) ) {
			return sprintf( 'https://%1$s@%2$s/%3$s.git', $credentials, $this->repository->get_host(), $this->repository->get_name() );
		}

		return sprintf( 'https://%1$s/%2$s.git', $this->repository->get_host(), $this->repository->get_name() );
	}
}
