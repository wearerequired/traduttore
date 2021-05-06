<?php
/**
 * Command for managing the Traduttore cache
 *
 * @since 2.0.0
 */

namespace Required\Traduttore\CLI;

use Required\Traduttore\LoaderFactory;
use Required\Traduttore\ProjectLocator;
use Required\Traduttore\RepositoryFactory;
use Required\Traduttore\Runner;
use Required\Traduttore\Updater;
use WP_CLI;
use WP_CLI_Command;

/**
 * Cache management command.
 *
 * @since 2.0.0
 */
class CacheCommand extends WP_CLI_Command {
	/**
	 * Removes the cached source code repository for a given project.
	 *
	 * Useful when the local repository was somehow corrupted.
	 *
	 * ## OPTIONS
	 *
	 * <project|url>
	 * : Project path / ID or source code repository URL, e.g. https://github.com/wearerequired/required-valencia
	 *
	 * ## EXAMPLES
	 *
	 *     # Remove cached repository.
	 *     $ wp traduttore project cache clear https://github.com/wearerequired/required-valencia
	 *     Success: Removed cached Git repository for project (ID: 123)!
	 *
	 *     # Remove cached repository for given project path.
	 *     $ wp traduttore project cache clear required/required-valencia
	 *     Success: Removed cached Git repository for project (ID: 123)!
	 *
	 *     # Remove cached repository for given project ID.
	 *     $ wp traduttore project cache clear 123
	 *     Success: Removed cached Git repository for project (ID: 123)!
	 *
	 * @since 2.0.0
	 *
	 * @param array $args Command args.
	 */
	public function clear( $args ): void {
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
