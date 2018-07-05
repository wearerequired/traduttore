<?php
/**
 * Loader interface.
 *
 * @package Required\Traduttore
 */

namespace Required\Traduttore;

/**
 * Loader interface.
 *
 * @since 3.0.0
 */
interface Loader {
	/**
	 * Class constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param Repository $repository Repository instance.
	 */
	public function __construct( Repository $repository );

	/**
	 * Downloads a remote repository.
	 *
	 * If the repository has been downloaded before, the latest changes will be pulled.
	 *
	 * @since 3.0.0
	 *
	 * @return string Path to the downloaded repository on success.
	 */
	public function download() :? string;

	/**
	 * Returns the local repository path..
	 *
	 * @since 3.0.0
	 *
	 * @return string Repository path.
	 */
	public function get_local_path() : string;
}
