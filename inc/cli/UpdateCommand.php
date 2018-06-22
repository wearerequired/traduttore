<?php
/**
 * Command for updating translations.
 *
 * @since 2.0.0
 *
 * @package Required\Traduttore\CLI
 */

namespace Required\Traduttore\CLI;

use GP;
use GP_Translation_Set;
use Required\Traduttore\GitHubUpdater;
use Required\Traduttore\ProjectLocator;
use WP_CLI;
use WP_CLI_Command;

/**
 * Updates project translations from GitHub repository.
 *
 * Finds the project the repository belongs to and updates the translations accordingly.
 *
 * ## OPTIONS
 *
 * <project|url>
 * : Project path / ID or GitHub repository URL, e.g. https://github.com/wearerequired/required-valencia
 *
 * [--delete]
 * : Whether to first delete the existing local repository or not.
 *
 * ## EXAMPLES
 *
 *     # Update translations from repository URL.
 *     $ wp traduttore update https://github.com/wearerequired/required-valencia
 *     Success: Updated translations for project (ID: 123)!
 *
 *     # Update translations from project path.
 *     $ wp traduttore update required/required-valencia
 *     Success: Updated translations for project (ID: 123)!
 *
 *     # Update translations from project ID.
 *     $ wp traduttore update 123
 *     Success: Updated translations for project (ID: 123)!
 *
 * @since 2.0.0
 */
class UpdateCommand extends WP_CLI_Command {
	/**
	 * Class constructor.
	 *
	 * Automatically called by WP-CLI.
	 *
	 * @param array $args Command args.
	 * @param array $assoc_args Associative args.
	 */
	public function __construct( $args, $assoc_args ) {
		$locator = new ProjectLocator( $args[0] );
		$project = $locator->get_project();

		if ( ! $project ) {
			WP_CLI::error( 'Project not found' );
		}

		$github_updater = new GitHubUpdater( $project );
		$success        = $github_updater->fetch_and_update( isset( $assoc_args['delete'] ) );

		if ( $success ) {
			WP_CLI::success( sprintf( 'Updated translations for project (ID: %d)!', $project->id ) );
		} else {
			WP_CLI::warning( sprintf( 'Could not update translations for project (ID: %d)!', $project->id ) );
		}
	}
}
