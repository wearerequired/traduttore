<?php
/**
 * Command for building language pack Language packs.
 *
 * @since 2.0.0
 *
 * @package Required\Traduttore\CLI
 */

namespace Required\Traduttore\CLI;

use GP;
use GP_Translation_Set;
use Required\Traduttore\{ProjectLocator, ZipProvider};
use WP_CLI;
use WP_CLI_Command;
use function WP_CLI\Utils\get_flag_value;

/**
 * Language pack builder command.
 *
 * @since 2.0.0
 */
class BuildCommand extends WP_CLI_Command {
	/**
	 * Generate language packs for one or more projects.
	 *
	 * ## OPTIONS
	 *
	 * [<project>...]
	 * : One or more project paths or IDs.
	 *
	 * [--force]
	 * : Force language pack generation, even if there were no changes since the last build.
	 *
	 * [--all]
	 * : If set, language packs will be generated for all projects.
	 *
	 * ## EXAMPLES
	 *
	 *     # Generate language packs for the project with ID 123.
	 *     $ wp traduttore build 123
	 *     Language pack generated for translation set (ID: 1)
	 *     Language pack generated for translation set (ID: 3)
	 *     Language pack generated for translation set (ID: 7)
	 *     Success: Language pack generation finished
	 *
	 *     # Generate language packs for all projects.
	 *     $ wp traduttore build --all
	 *     Language pack generated for translation set (ID: 1)
	 *     Language pack generated for translation set (ID: 2)
	 *     Language pack generated for translation set (ID: 3)
	 *     Language pack generated for translation set (ID: 4)
	 *     Language pack generated for translation set (ID: 5)
	 *     Language pack generated for translation set (ID: 7)
	 *     Success: Language pack generation finished
	 *
	 * @since 2.0.0
	 *
	 * @param array $args Command args.
	 * @param array $assoc_args Associative args.
	 */
	public function __invoke( $args, $assoc_args ) {
		$all      = get_flag_value( $assoc_args, 'all', false );
		$force    = get_flag_value( $assoc_args, 'force', false );
		$projects = $this->check_optional_args_and_all( $args, $all );

		if ( ! $projects ) {
			return;
		}

		foreach ( $projects as $project ) {
			if ( ! $project ) {
				continue;
			}

			$translation_sets = (array) GP::$translation_set->by_project_id( $project->get_id() );

			/* @var GP_Translation_Set $translation_set */
			foreach ( $translation_sets as $translation_set ) {
				$zip_provider = new ZipProvider( $translation_set );

				if ( ! $force && $translation_set->last_modified() <= $zip_provider->get_last_build_time() ) {
					WP_CLI::log( sprintf( 'No language pack generated for translation set as there were no changes (ID: %d)', $translation_set->id ) );

					continue;
				}

				if ( $zip_provider->generate_zip_file() ) {
					WP_CLI::log( sprintf( 'Language pack generated for translation set (ID: %d)', $translation_set->id ) );

					continue;
				}

				WP_CLI::warning( sprintf( 'Error generating Language pack for translation set (ID: %d)', $translation_set->id ) );
			}
		}

		WP_CLI::success( 'Language pack generation finished' );
	}

	/**
	 * If there are optional args ([<project>...]) and an all option, then check if have something to do.
	 *
	 * @param array $args Passed arguments.
	 * @param bool  $all  All flag.
	 *
	 * @return array Same as $args if not all, otherwise all slugs.
	 */
	protected function check_optional_args_and_all( $args, $all ) {
		if ( $all ) {
			$args = $this->get_all_projects();
		}

		if ( empty( $args ) ) {
			if ( ! $all ) {
				WP_CLI::error( 'Please specify one or more projects, or use --all.' );
			}

			WP_CLI::success( 'No projects found' );
		}

		$args = array_map( function ( $project ) {
			$locator = new ProjectLocator( $project );

			return $locator->get_project();
		}, $args );

		return $args;
	}

	/**
	 * Returns all active GlotPress projects.
	 *
	 * @return \GP_Project[] GlotPress projects
	 */
	protected function get_all_projects(): array {
		return GP::$project->many( GP::$project->select_all_from_conditions_and_order( [ 'active' => 1 ] ) );
	}
}
