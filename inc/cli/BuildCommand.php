<?php
/**
 * Command for building language pack ZIP files.
 *
 * @since 2.0.0
 *
 * @package Required\Traduttore\CLI
 */

namespace Required\Traduttore\CLI;

use GP;
use GP_Translation_Set;
use Required\Traduttore\ProjectLocator;
use Required\Traduttore\ZipProvider;
use WP_CLI;
use WP_CLI_Command;

/**
 * Generate translation ZIP files for a project.
 *
 * ## OPTIONS
 *
 * <project>
 * : Path or ID of the project to generate ZIP files for.
 *
 * [--force]
 * : Force ZIP file generation, even if there were no changes since the last build.
 *
 * ## EXAMPLES
 *
 *     # Generate ZIP files for the project with ID 123.
 *     $ wp traduttore build 123
 *     ZIP file generated for translation set (ID: 1)
 *     ZIP file generated for translation set (ID: 3)
 *     ZIP file generated for translation set (ID: 7)
 *
 * @since 2.0.0
 */
class BuildCommand extends WP_CLI_Command {
	/**
	 * Class constructor.
	 *
	 * Automatically called by WP-CLI.
	 *
	 * @param array $args Command args.
	 * @param array $assoc_args Associative args.
	 */
	public function __invoke( $args, $assoc_args ) {
		$locator = new ProjectLocator( $args[0] );
		$project = $locator->get_project();

		if ( ! $project ) {
			WP_CLI::error( 'Project not found' );
		}

		$translation_sets = (array) GP::$translation_set->by_project_id( $project->id );

		/* @var GP_Translation_Set $translation_set */
		foreach ( $translation_sets as $translation_set ) {
			$zip_provider = new ZipProvider( $translation_set );

			if ( isset( $assoc_args['force'] ) && ! $assoc_args['force'] && $translation_set->last_modified() <= ZipProvider::get_last_build_time( $translation_set ) ) {
				WP_CLI::log( sprintf( 'No ZIP file generated for translation set as there were no changes (ID: %d)', $translation_set->id ) );

				continue;
			}

			if ( $zip_provider->generate_zip_file() ) {
				WP_CLI::success( sprintf( 'ZIP file generated for translation set (ID: %d)', $translation_set->id ) );

				continue;
			}

			WP_CLI::warning( sprintf( 'Error generating ZIP file for translation set (ID: %d)', $translation_set->id ) );
		}

		WP_CLI::success( 'ZIP file generation finished' );
	}
}
