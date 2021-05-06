<?php
/**
 * Repository factory
 *
 * @since 3.0.0
 */

namespace Required\Traduttore;

use Required\Traduttore\Repository\Bitbucket;
use Required\Traduttore\Repository\GitHub;
use Required\Traduttore\Repository\GitLab;

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
	 * @param \Required\Traduttore\Project $project Project information.
	 * @return \Required\Traduttore\Repository Repository instance.
	 */
	public function get_repository( Project $project ): ?Repository {
		$repository = null;

		$repository_type = $project->get_repository_type();

		switch ( $repository_type ) {
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

		if ( ! $repository && ! $repository_type ) {
			$url = $project->get_repository_url();

			if ( ! $url ) {
				$url = $project->get_source_url_template();
			}

			$host = $url ? wp_parse_url( $url, PHP_URL_HOST ) : null;

			switch ( $host ) {
				case 'github.com':
					$repository = new GitHub( $project );
					break;
				case 'gitlab.com':
					$repository = new GitLab( $project );
					break;
				case 'bitbucket.org':
					$repository = new Bitbucket( $project );
					break;
			}
		}

		/**
		 * Filters the determined repository instance for a given project.
		 *
		 * Can be used to set a custom handler for self-managed repositories.
		 *
		 * @param \Required\Traduttore\Repository|null $repository Repository instance.
		 * @param \Required\Traduttore\Project         $project    Project information.
		 */
		return apply_filters( 'traduttore.repository', $repository, $project );
	}
}
