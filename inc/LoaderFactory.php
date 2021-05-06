<?php
/**
 * Source code loader factory
 *
 * @since 3.0.0
 */

namespace Required\Traduttore;

use Required\Traduttore\Loader\Git as GitLoader;
use Required\Traduttore\Loader\Mercurial as MercurialLoader;

/**
 * LoaderFactory class.
 *
 * @since 3.0.0
 */
class LoaderFactory {
	/**
	 * Returns a new loader instance for a given repository.
	 *
	 * @since 3.0.0
	 *
	 * @param \Required\Traduttore\Repository $repository Repository instance.
	 * @return \Required\Traduttore\Loader Loader instance.
	 */
	public function get_loader( Repository $repository ): ?Loader {
		$loader = null;

		if ( Repository::VCS_TYPE_HG === $repository->get_project()->get_repository_vcs_type() ) {
			$loader = new MercurialLoader( $repository );
		} elseif ( \in_array(
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

		/**
		 * Filters the loader instance for a given repository and project.
		 *
		 * @param \Required\Traduttore\Loader|null     $loader     Loader instance.
		 * @param \Required\Traduttore\Repository|null $repository Repository instance.
		 */
		return apply_filters( 'traduttore.loader', $loader, $repository );
	}
}
