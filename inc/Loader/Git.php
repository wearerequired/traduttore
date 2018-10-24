<?php
/**
 * Git loader class.
 *
 * @package Required\Traduttore
 */

namespace Required\Traduttore\Loader;

use Required\Traduttore\Repository;

/**
 * Git loader.
 *
 * @since 3.0.0
 */
class Git extends Base {
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

			return 0 === $status ? $target : null;
		}

		exec(
			escapeshellcmd(
				sprintf(
					'git clone --depth=1 %1$s %2$s -q',
					escapeshellarg( $this->get_clone_url() ),
					escapeshellarg( $target )
				)
			),
			$output,
			$status
		);

		return 0 === $status ? $target : null;
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
		 * @param bool       $use_https  Whether to use HTTPS instead of SSH for cloning repositories.
		 *                               Defaults to true for public repositories.
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
