<?php
/**
 * Base source code loader implementation
 *
 * @package Required\Traduttore
 */

namespace Required\Traduttore\Loader;

use Required\Traduttore\Loader;
use Required\Traduttore\Repository;

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
			'%1$straduttore-%2$s-%3$s',
			get_temp_dir(),
			$this->repository->get_host(),
			sanitize_title( $this->repository->get_name() )
		);
	}
}
