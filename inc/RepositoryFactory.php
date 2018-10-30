<?php
/**
 * RepositoryFactory class.
 *
 * @since 3.0.0
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
		 * @param Repository|null $repository Repository instance.
		 * @param Project         $project    Project information.
		 */
		return apply_filters( 'traduttore.repository', $repository, $project );
	}
}
