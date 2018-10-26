<?php
/**
 * RepositoryFactory class.
 *
 * @since   3.0.0
 *
 * @package Required\Traduttore
 */

namespace Required\Traduttore;

use Required\Traduttore\Repository\{Bitbucket, GitHub, GitLab};

/**
 * RepositoryFactory class.
 *
 * @since 3.0.0
 */
class RepositoryFactory {
	/**
	 * Returns a new repository instance for a given project.
	 *
	 * @since 3.0.0
	 *
	 * @param Project $project Project information.
	 * @return Repository Repository instance.
	 */
	public function get_repository( Project $project ): ?Repository {
		$repository = null;

		switch ( $project->get_repository_type() ) {
			case Repository::TYPE_BITBUCKET:
				$repository = new Bitbucket( $project );
				break;
			case Repository::TYPE_GITHUB:
				$repository = new GitHub( $project );
				break;
			case Repository::TYPE_GITLAB:
				$repository = new GitLab( $project );
				break;
		}

		if ( ! $repository ) {
			$url  = $project->get_repository_url();
			$host = $url ? wp_parse_url( $url, PHP_URL_HOST ) : null;

			if ( 'github.com' === $host ) {
				$repository = new GitHub( $project );
			} elseif ( 'gitlab.com' === $host ) {
				$repository = new GitLab( $project );
			} elseif ( 'bitbucket.org' === $host ) {
				$repository = new Bitbucket( $project );
			}
		}

		/**
		 * Filters the determined repository instance for a given project.
		 *
		 * Can be used to set a custom handler for self-managed repositories.
		 *
		 * @param Repository|null $repository Repository instance.
		 * @param Project         $project    Project information.
		 */
		return apply_filters( 'traduttore.repository', $repository, $project );
	}
}
