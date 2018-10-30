<?php
/**
 * Command for managing the Traduttore cache.
 *
 * @since 2.0.0
 *
 * @package Required\Traduttore\CLI
 */

namespace Required\Traduttore\CLI;

use Required\Traduttore\{LoaderFactory, RepositoryFactory, Updater, Runner};
use Required\Traduttore\ProjectLocator;
use WP_CLI;
use WP_CLI_Command;

/**
 * Manages the Traduttore cache.
 *
 * @since 2.0.0
 */
class CacheCommand extends WP_CLI_Command {
	/**
	 * Removes the cached Git repository for a given project.
	 *
	 * Finds the project the repository belongs to and removes the checked out Git repository completely.
	 *
	 * Useful when the local repository was somehow corrupted.
	 *
	 * ## OPTIONS
	 *
	 * <project|url>
	 * : Project path / ID or GitHub repository URL, e.g. https://github.com/wearerequired/required-valencia
	 *
	 * ## EXAMPLES
	 *
	 *     # Update translations from repository URL.
	 *     $ wp traduttore cache clear https://github.com/wearerequired/required-valencia
	 *     Success: Removed cached Git repository for project (ID: 123)!
	 *
	 *     # Update translations from project path.
	 *     $ wp traduttore cache clear required/required-valencia
	 *     Success: Removed cached Git repository for project (ID: 123)!
	 *
	 *     # Update translations from project ID.
	 *     $ wp traduttore cache clear 123
	 *     Success: Removed cached Git repository for project (ID: 123)!
	 *
	 * @since 2.0.0
	 *
	 * @param array $args Command args.
	 * @param array $assoc_args Associative args.
	 */
	public function clear( $args, $assoc_args ): void {
		$locator = new ProjectLocator( $args[0] );
		$project = $locator->get_project();

		if ( ! $project ) {
			WP_CLI::error( 'Project not found' );
		}

		$repository = ( new RepositoryFactory() )->get_repository( $project );

		if ( ! $repository ) {
			WP_CLI::error( 'Invalid project type' );
		}

		$loader = ( new LoaderFactory() )->get_loader( $repository );

		if ( ! $loader ) {
			WP_CLI::error( 'Invalid project type' );
		}

		$updater = new Updater( $project );
		$runner  = new Runner( $loader, $updater );

		if ( $runner->delete_local_repository() ) {
			WP_CLI::success( sprintf( 'Removed cached Git repository for project (ID: %d)!', $project->get_id() ) );

			return;
		}

		WP_CLI::error( sprintf( 'Could not remove cached Git repository for project (ID: %d)!', $project->get_id() ) );
	}
}
