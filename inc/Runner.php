<?php
/**
 * Project class.
 *
 * @since 2.0.0
 *
 * @package Required\Traduttore
 */

namespace Required\Traduttore;

use WP_Filesystem_Base;

/**
 * Project class.
 *
 * @since 3.0.0
 */
class Runner {
	/**
	 * Loader instance.
	 *
	 * @since 3.0.0
	 *
	 * @var Loader VCS loader.
	 */
	protected $loader;

	/**
	 * Updater instance.
	 *
	 * @since 3.0.0
	 *
	 * @var Updater Translation updater.
	 */
	protected $updater;

	/**
	 * Runner constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param Loader  $loader VCS loader.
	 * @param Updater $updater Translation updater.
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
	public function delete_local_repository() : bool {
		/* @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		if ( ! $wp_filesystem ) {
			require_once ABSPATH . '/wp-admin/includes/admin.php';

			if ( ! \WP_Filesystem() ) {
				return false;
			}
		}

		return $wp_filesystem->rmdir( $this->loader->get_local_path(), true );
	}

	/**
	 * Fetches the GitHub repository and updates the translations based on the source code.
	 *
	 * @since 3.0.0
	 *
	 * @return bool True on success, false otherwise.
	 */
	public function run() : bool {
		if ( $this->updater->has_lock() ) {
			return false;
		}

		$this->updater->add_lock();

		$local_repository = $this->loader->download();

		if ( ! $local_repository ) {
			$this->updater->remove_lock();

			return false;
		}

		$configuration = new Configuration( $local_repository );

		$result = $this->updater->update( $configuration );

		$this->updater->remove_lock();

		return $result;
	}
}
