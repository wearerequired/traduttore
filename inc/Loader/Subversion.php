<?php
/**
 * Subversion source code loader
 *
 * @since 3.0.0
 *
 * @package Required\Traduttore
 */

namespace Required\Traduttore\Loader;

use Required\Traduttore\Repository;

/**
 * Subversion loader class.
 *
 * @since 3.0.0
 */
class Subversion extends Base {
	/**
	 * Downloads a remote Subversion repository.
	 *
	 * If the repository has been checked out before, the latest changes will be pulled.
	 *
	 * @since 3.0.0
	 *
	 * @return string Path to the downloaded repository on success.
	 */
	public function download(): ?string {
		if ( is_dir( $this->get_local_path() ) ) {
			$this->update_existing_repository();
		}

		return $this->download_new_repository();
	}

	/**
	 * Downloads a fresh copy of a remote repository.
	 *
	 * @since 3.0.0
	 *
	 * @return null|string Path to the downloaded repository on success.
	 */
	protected function download_new_repository(): ?string {
		$target = $this->get_local_path();

		exec(
			escapeshellcmd(
				sprintf(
					'svn checkout %1$s %2$s -q',
					escapeshellarg( $this->get_checkout_url() ),
					escapeshellarg( $target )
				)
			),
			$output,
			$status
		);

		return 0 === $status ? $target : null;

	}

	/**
	 * Updates an existing copy of a remote repository.
	 *
	 * @since 3.0.0
	 *
	 * @return null|string Path to the downloaded repository on success.
	 */
	protected function update_existing_repository(): ?String {
		$target = $this->get_local_path();

		$current_dir = getcwd();
		chdir( $target );
		exec( escapeshellcmd( 'svn revert --recursive .' ), $output, $status );
		exec( escapeshellcmd( 'svn update .' ), $output, $status );
		chdir( $current_dir );

		return 0 === $status ? $target : null;
	}

	/**
	 * Returns the URL to check out the current repository.
	 *
	 * Supports HTTPS and SSH URLs.
	 *
	 * @since 3.0.0
	 *
	 * @return string URL to check out the repository, e.g. svn+ssh://svn.example.com/wearerequired/traduttore
	 *                or https://svn.example.com/wearerequired/traduttore.
	 */
	protected function get_checkout_url(): string {
		/**
		 * Filters whether HTTPS or SSH should be used to check out a Subversion repository.
		 *
		 * @since 3.0.0
		 *
		 * @param bool       $use_https  Whether to use HTTPS instead of SSH for checking out Subversion repositories.
		 *                               Defaults to true for public repositories.
		 * @param Repository $repository The current repository.
		 */
		$use_https = apply_filters( 'traduttore.svn_checkout_use_https', $this->repository->is_public(), $this->repository );

		$checkout_url = $this->repository->get_ssh_url();

		if ( $use_https ) {
			$checkout_url = $this->repository->get_https_url();
		}

		/**
		 * Filters the URL used to check out a Subversion repository.
		 *
		 * @since 3.0.0
		 *
		 * @param string     $checkout_url The URL to check out a Subversion repository.
		 * @param Repository $repository   The current repository.
		 */
		return apply_filters( 'traduttore.svn_checkout_url', $checkout_url, $this->repository );
	}
}
