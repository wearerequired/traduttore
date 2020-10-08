<?php
/**
 * Command for managing language packs
 *
 * @since 3.0.0
 */

namespace Required\Traduttore\CLI;

use DateTime;
use DateTimeZone;
use GP;
use GP_Locales;
use Required\Traduttore\ProjectLocator;
use Required\Traduttore\ZipProvider;
use WP_CLI;
use WP_CLI_Command;
use function WP_CLI\Utils\get_flag_value;

/**
 * LanguagePack command class.
 *
 * @since 3.0.0
 */
class LanguagePackCommand extends WP_CLI_Command {

	/**
	 * List language packs for a given project.
	 *
	 * ## OPTIONS
	 *
	 * <project>
	 * : Path or ID of the project.
	 *
	 * [--field=<field>]
	 * : Prints the value of a single field for each language pack.
	 *
	 * [--fields=<fields>]
	 * : Limit the output to specific language pack fields.
	 *
	 * [--format=<format>]
	 * : Render output in a particular format.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - csv
	 *   - json
	 *   - count
	 *   - yaml
	 * ---
	 *
	 * ## AVAILABLE FIELDS
	 *
	 * These fields will be displayed by default for each translation:
	 *
	 * * Locale
	 * * English Name
	 * * Native Name
	 * * Completed
	 * * Updated
	 * * Package
	 *
	 * ## EXAMPLES
	 *
	 *     # Display available language packs for the given project
	 *     $ wp traduttore language-pack list 1 --fields=Locale,Package
	 *     +--------+----------------------------------------------------------------+
	 *     | Locale | Package                                                        |
	 *     +--------+----------------------------------------------------------------+
	 *     | fr_FR  | https://translate.example.com/content/traduttore/foo-fr_FR.zip |
	 *     | de_DE  | https://translate.example.com/content/traduttore/foo-de_DE.zip |
	 *     +--------+----------------------------------------------------------------+
	 *
	 * @since 3.0.0
	 *
	 * @param array $args Command args.
	 * @param array $assoc_args Associative args.
	 */
	public function list( $args, $assoc_args ): void {
		$locator = new ProjectLocator( $args[0] );
		$project = $locator->get_project();

		if ( ! $project ) {
			WP_CLI::error( 'Project not found' );
		}

		$translation_sets = (array) GP::$translation_set->by_project_id( $project->get_id() );

		$language_packs = [];

		/** @var \GP_Translation_Set $set */
		foreach ( $translation_sets as $set ) {
			/** @var \GP_Locale $locale */
			$locale = GP_Locales::by_slug( $set->locale );

			$zip_provider = new ZipProvider( $set );

			$language_packs[] = [
				'Locale'       => $locale->wp_locale,
				'English Name' => $locale->english_name,
				'Native Name'  => $locale->native_name,
				'Completed'    => sprintf( '%s%%', $set->percent_translated() ),
				'Updated'      => $zip_provider->get_last_build_time()->format( DATE_ATOM ),
				'Package'      => file_exists( $zip_provider->get_zip_path() ) ? $zip_provider->get_zip_url() : 'n/a',
			];
		}

		$formatter = new WP_CLI\Formatter(
			$assoc_args,
			[
				'Locale',
				'English Name',
				'Native Name',
				'Completed',
				'Updated',
				'Package',
			]
		);

		$formatter->display_items( $language_packs );
	}

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
	 *     $ wp traduttore language-pack build 123
	 *     Language pack generated for translation set (ID: 1)
	 *     Language pack generated for translation set (ID: 3)
	 *     Language pack generated for translation set (ID: 7)
	 *     Success: Language pack generation finished
	 *
	 *     # Generate language packs for all projects.
	 *     $ wp traduttore language-pack build --all
	 *     Language pack generated for translation set (ID: 1)
	 *     Language pack generated for translation set (ID: 2)
	 *     Language pack generated for translation set (ID: 3)
	 *     Language pack generated for translation set (ID: 4)
	 *     Language pack generated for translation set (ID: 5)
	 *     Language pack generated for translation set (ID: 7)
	 *     Success: Language pack generation finished
	 *
	 * @since 3.0.0
	 *
	 * @param array $args Command args.
	 * @param array $assoc_args Associative args.
	 */
	public function build( $args, $assoc_args ): void {
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

			/** @var \GP_Translation_Set $translation_set */
			foreach ( $translation_sets as $translation_set ) {
				if ( 0 === $translation_set->current_count() ) {
					WP_CLI::log( sprintf( 'No language pack generated for translation set as there are no entries (ID: %d)', $translation_set->id ) );

					continue;
				}

				$zip_provider  = new ZipProvider( $translation_set );
				$last_modified = $translation_set->last_modified();

				if ( $last_modified ) {
					$last_modified = new DateTime( $last_modified, new DateTimeZone( 'UTC' ) );
				} else {
					$last_modified = new DateTime( 'now', new DateTimeZone( 'UTC' ) );
				}

				if ( ! $force && $last_modified <= $zip_provider->get_last_build_time() ) {
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
	 * If there are optional args ([<project>...]) and an all option, then check if we have something to do.
	 *
	 * @since 3.0.0
	 *
	 * @param array $args Passed arguments.
	 * @param bool  $all  All flag.
	 * @return array Same as $args if not all, otherwise all slugs.
	 */
	protected function check_optional_args_and_all( $args, $all ): array {
		if ( $all ) {
			$args = $this->get_all_projects();
		}

		if ( empty( $args ) ) {
			if ( ! $all ) {
				WP_CLI::error( 'Please specify one or more projects, or use --all.' );
			}

			WP_CLI::success( 'No projects found' );
		}

		$args = array_map(
			function ( $project ) {
				$project = ( new ProjectLocator( $project ) )->get_project();
				if ( $project->is_active() ) {
					return $project;
				}
				WP_CLI::log( sprintf( 'Project (ID: %d) is inactive.', $project->get_id() ) );
				return null;
			},
			$args
		);

		return $args;
	}

	/**
	 * Returns all active GlotPress projects.
	 *
	 * @since 3.0.0
	 *
	 * @return \GP_Project[] GlotPress projects
	 */
	protected function get_all_projects(): array {
		return GP::$project->many( GP::$project->select_all_from_conditions_and_order( [ 'active' => 1 ] ) );
	}
}
