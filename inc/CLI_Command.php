<?php

namespace Required\Traduttore;

use GP;
use GP_Translation_Set;
use WP_CLI;
use WP_CLI_Command;

/**
 * Class to handle Traduttore CLI commands.
 *
 * @since 2.0.0
 */
class CLI_Command extends WP_CLI_Command {
	/**
	 * Generate translation ZIP files for a project.
	 *
	 * ## OPTIONS
	 *
	 * <project>
	 * : Slug or ID of the project to generate ZIP files for.
	 *
	 * ## EXAMPLES
	 *
	 *     # Generate ZIP files for the project with ID 123.
	 *     $ wp traduttore generate-zip 123
	 *     ZIP file generated for translation set (ID: 1)
	 *     ZIP file generated for translation set (ID: 3)
	 *     ZIP file generated for translation set (ID: 7)
	 */
	public function generate_zip( $args, $assoc_args ) {
		if ( is_numeric( $args[0] ) ) {
			$project = GP::$project->get( $args[0] );
		} else {
			$project = GP::$project->by_path( $args[0] );
		}

		// Get the project object from the project path that was passed in.
		if ( ! $project ) {
			WP_CLI::error( 'Project not found' );
		}

		$translation_sets = (array) GP::$translation_set->by_project_id( $project->id );

		/** @var GP_Translation_Set $translation_set */
		foreach ( $translation_sets as $translation_set ) {
			$zip_provider = new ZipProvider( $translation_set );
			$success      = $zip_provider->generate_zip_file();

			do_action( 'traduttore_zip_generated', $success, $translation_set );

			if ( $success ) {
				WP_CLI::success( sprintf( 'ZIP file generated for translation set (ID: %d)', $translation_set->id ) );
			} else {
				WP_CLI::warning( sprintf( 'Error generating ZIP file for translation set (ID: %d)', $translation_set->id ) );
			}
		}
	}

	/**
	 * Updates project translations from GitHub repository.
	 *
	 * Finds the project the repository belongs to and updates the translations accordingly.
	 *
	 * ## OPTIONS
	 *
	 * <url>
	 * : GitHub repository URL, e.g. https://github.com/wearerequired/required-valencia
	 *
	 * ## EXAMPLES
	 *
	 *     # Update
	 *     $ wp traduttore update_from_github https://github.com/wearerequired/required-valencia
	 *     Success: Updated translations for project (ID: 123)!
	 */
	public function update_from_github( $args, $assoc_args ) {
		$project = GitHubUpdater::find_project( $args[0] );

		if ( ! $project ) {
			WP_CLI::error( 'Project not found' );
		}

		$github_updater = new GitHubUpdater( $args[0], $project );
		$success        = $github_updater->fetch_and_update();

		if ( $success ) {
			WP_CLI::success( sprintf( 'Updated translations for project (ID: %d)!', $project->id ) );
		} else {
			WP_CLI::warning( sprintf( 'Could not update translations for project (ID: %d)!', $project->id ) );
		}
	}
}
