<?php
/**
 * Loader class.
 *
 * @since 2.0.0
 *
 * @package Required\Traduttore
 */

namespace Required\Traduttore;

use Loader;

/**
 * LoaderFactory class.
 *
 * @since 2.0.0
 */
class LoaderFactory {
	/**
	 * Returns a new loader instance for a given project.
	 *
	 * @param Project $project Project information.
	 * @return Loader
	 */
	public function get_loader( Project $project ) :? Loader {
		$repository = new Repository( $project );

		switch ( $repository->get_type( $project ) ) {
			case Repository::TYPE_GITHUB:
				return new GitHub( $repository );
			case Repository::TYPE_GITLAB:
				return new GitLab( $repository );
		}

		return null;
	}
}
