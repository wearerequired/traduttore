<?php
/**
 * Mercurial loader class.
 *
 * @since 3.0.0
 *
 * @package Required\Traduttore
 */

namespace Required\Traduttore\Loader;

use Required\Traduttore\Repository;

/**
 * Mercurial loader class.
 *
 * @since 3.0.0
 */
class Mercurial extends Base {
	/**
	 * Downloads a remote Mercurial repository.
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
			exec( escapeshellcmd( 'hg update --clean -q' ), $output, $status );
			exec( escapeshellcmd( 'hg pull -q' ), $output, $status );
			chdir( $current_dir );

			return 0 === $status ? $target : null;
		}

		exec(
			escapeshellcmd(
				sprintf(
					'hg clone %1$s %2$s -q',
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
	 * @return string URL to clone the repository, e.g. hg@bitbucket.org/wearerequired/traduttore
	 *                or https://bitbucket.org/wearerequired/traduttore.
	 */
	protected function get_clone_url(): string {
		/**
		 * Filters whether HTTPS or SSH should be used to clone a repository.
		 *
		 * @since 3.0.0
		 *
		 * @param bool       $use_https  Whether to use HTTPS instead of SSH for cloning repositories.
		 *                               Defaults to true for public repositories.
		 * @param Repository $repository The current repository.
		 */
		$use_https = apply_filters( 'traduttore.hg_clone_use_https', $this->repository->is_public(), $this->repository );

		$clone_url = $this->repository->get_ssh_url();

		if ( $use_https ) {
			$clone_url = $this->repository->get_https_url();
		}

		/**
		 * Filters the URL used to clone a Mercurial repository.
		 *
		 * @since 3.0.0
		 *
		 * @param string     $clone_url  The URL to clone a Mercurial repository.
		 * @param Repository $repository The current repository.
		 */
		return apply_filters( 'traduttore.hg_clone_url', $clone_url, $this->repository );
	}
}
