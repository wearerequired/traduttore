<?php
/**
 * Base loader class.
 *
 * @package Required\Traduttore
 */

namespace Required\Traduttore\Loader;

use Required\Traduttore\Repository;
use Required\Traduttore\Loader;

/**
 * Base loader.
 *
 * @since 3.0.0
 */
abstract class Base implements Loader {
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
}
