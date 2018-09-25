<?php
/**
 * LoaderFactory class.
 *
 * @since 3.0.0
 *
 * @package Required\Traduttore
 */

namespace Required\Traduttore;

use Required\Traduttore\Loader\{
	Bitbucket, GitHub, GitLab
};

/**
 * LoaderFactory class.
 *
 * @since 3.0.0
 */
class LoaderFactory {
	/**
	 * Returns a new loader instance for a given project.
	 *
	 * @since 3.0.0
	 *
	 * @param Project $project Project information.
	 * @return Loader
	 */
	public function get_loader( Project $project ) :? Loader {
		$repository = new Repository( $project );

		switch ( $repository->get_type() ) {
			case Repository::TYPE_GITHUB:
				return new GitHub( $repository );
			case Repository::TYPE_GITLAB:
				return new GitLab( $repository );
			case Repository::TYPE_BITBUCKET:
				return new Bitbucket( $repository );
		}

		return null;
	}
}
