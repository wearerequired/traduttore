<?php
/**
 * LoaderFactory class.
 *
 * @since 3.0.0
 *
 * @package Required\Traduttore
 */

namespace Required\Traduttore;

use Required\Traduttore\Loader\Git as GitLoader;

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
	 * @return Loader Loader instance.
	 */
	public function get_loader( Project $project ) :? Loader {
		$repository = ( new RepositoryFactory() )->get_repository( $project );

		$loader = null;

		if ( $repository ) {
			if ( Repository::TYPE_BITBUCKET === $repository->get_type() &&
			     'hg' === $repository->get_project()->get_repository_vcs_type()
			) {
				// Todo: Add Mercurial loader.
			} elseif ( in_array(
				$repository->get_type(),
				[
					Repository::TYPE_BITBUCKET,
					Repository::TYPE_GITHUB,
					Repository::TYPE_GITLAB,
				],
				true
			) ) {
				$loader = new GitLoader( $repository );
			}
		}

		/**
		 * Filters the loader instance for a given repository and project.
		 *
		 * @param Loader|null     $loader     Loader instance.
		 * @param Repository|null $repository Repository instance.
		 * @param Project         $project    Project information.
		 */
		return apply_filters( 'traduttore.loader', $loader, $repository, $project );
	}
}
