<?php
/**
 * Git source code loader
 *
 * @since 3.0.0
 */

namespace Required\Traduttore\Loader;

/**
 * Git loader class.
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
	public function download(): ?string {
		$target = $this->get_local_path();

		if ( is_dir( $target ) ) {
			$current_dir = getcwd();
			chdir( $target );
			exec( escapeshellcmd( 'git reset --hard -q' ), $output, $status );
			exec( escapeshellcmd( 'git pull -q' ), $output, $status );
			if ( $current_dir ) {
				chdir( $current_dir );
			}

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
	 * Returns the URL to clone the current repository.
	 *
	 * Supports HTTPS and SSH URLs.
	 *
	 * @since 3.0.0
	 *
	 * @return string URL to clone the repository, e.g. git@github.com:wearerequired/traduttore.git
	 *                or https://github.com/wearerequired/traduttore.git.
	 */
	protected function get_clone_url(): string {
		/**
		 * Filters whether HTTPS or SSH should be used to clone a repository.
		 *
		 * @since 3.0.0
		 *
		 * @param bool                            $use_https  Whether to use HTTPS instead of SSH for
		 *                                                    cloning repositories.
		 *                                                    Defaults to true for public repositories.
		 * @param \Required\Traduttore\Repository $repository The current repository.
		 */
		$use_https = apply_filters( 'traduttore.git_clone_use_https', $this->repository->is_public(), $this->repository );

		$clone_url = $this->repository->get_ssh_url();

		if ( $use_https ) {
			$clone_url = $this->repository->get_https_url();
		}

		/**
		 * Filters the URL used to clone a Git repository.
		 *
		 * @since 3.0.0
		 *
		 * @param string                          $clone_url  The URL to clone a Git repository.
		 * @param \Required\Traduttore\Repository $repository The current repository.
		 */
		return apply_filters( 'traduttore.git_clone_url', (string) $clone_url, $this->repository );
	}
}
