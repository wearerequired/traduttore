<?php

namespace Required\Traduttore;

use GP;
use GP_Translation_Set;
use WP_CLI;
use WP_CLI_Command;

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
}
