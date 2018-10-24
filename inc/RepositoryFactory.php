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
	public function get_repistory( Project $project ): ?Repository {
		$url  = $project->get_source_url_template();
		$host = $url ? wp_parse_url( $url, PHP_URL_HOST ) : null;

		$repository = null;

		if ( 'github.com' === $host ) {
			$repository = new GitHub( $project );
		} elseif ( 'gitlab.com' === $host ) {
			$repository = new GitLab( $project );
		} elseif ( 'bitbucket.org' === $host ) {
			$repository = new Bitbucket( $project );
		}

		/**
		 * Filters the determined repository instance for a given project.
		 *
		 * Can be used to set a custom handler for self-hosted repositories.
		 *
		 * @param Repository|null $repository Repository instance.
		 * @param Project         $project    Project information.
		 */
		return apply_filters( 'traduttore.repository', $repository, $project );
	}
}
