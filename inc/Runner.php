<?php
/**
 * Runner class
 *
 * @since 3.0.0
 */

namespace Required\Traduttore;

/**
 * Update runner class to update a project's strings given a specific loader and an updater.
 *
 * By using a simple locking mechanism, only a single update is performed at a time.
 *
 * @since 3.0.0
 */
class Runner {
	/**
	 * Loader instance.
	 *
	 * @since 3.0.0
	 *
	 * @var \Required\Traduttore\Loader VCS loader.
	 */
	protected $loader;

	/**
	 * Updater instance.
	 *
	 * @since 3.0.0
	 *
	 * @var \Required\Traduttore\Updater Translation updater.
	 */
	protected $updater;

	/**
	 * Runner constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param \Required\Traduttore\Loader  $loader  VCS loader.
	 * @param \Required\Traduttore\Updater $updater Translation updater.
	 */
	public function __construct( Loader $loader, Updater $updater ) {
		$this->loader  = $loader;
		$this->updater = $updater;
	}

	/**
	 * Attempts to delete the folder containing the local repository checkout.
	 *
	 * @since 3.0.0
	 *
	 * @return bool True on success, false on failure.
	 */
	public function delete_local_repository(): bool {
		/** @var \WP_Filesystem_Base|null $wp_filesystem */
		global $wp_filesystem;

		if ( ! $wp_filesystem instanceof \WP_Filesystem_Base ) {
			require_once ABSPATH . '/wp-admin/includes/admin.php';

			if ( ! \WP_Filesystem() ) {
				return false;
			}
		}

		return $wp_filesystem->rmdir( $this->loader->get_local_path(), true );
	}

	/**
	 * Updates the project's translations based on the source code.
	 *
	 * @since 3.0.0
	 *
	 * @param bool $cached Whether to use cached source code instead of updated one.
	 * @return bool True on success, false otherwise.
	 */
	public function run( $cached = false ): bool {
		if ( $this->updater->has_lock() ) {
			return false;
		}

		$this->updater->add_lock();

		$local_repository = $cached ? $this->loader->get_local_path() : $this->loader->download();

		if ( ! $local_repository || ! is_dir( $local_repository ) ) {
			$this->updater->remove_lock();

			return false;
		}

		$configuration = new Configuration( $local_repository );

		$result = $this->updater->update( $configuration );

		$this->updater->remove_lock();

		return $result;
	}
}
