<?php
/**
 * Command for managing projects.
 *
 * @since 3.0.0
 *
 * @package Required\Traduttore\CLI
 */

namespace Required\Traduttore\CLI;

use GP;
use GP_Locale;
use GP_Locales;
use GP_Translation_Set;
use WP_CLI;
use WP_CLI_Command;
use Required\Traduttore\{ProjectLocator, LoaderFactory, RepositoryFactory, ZipProvider, Updater, Runner};
use function WP_CLI\Utils\get_flag_value;

/**
 * Project command class.
 *
 * @since 3.0.0
 */
class ProjectCommand extends WP_CLI_Command {
	/**
	 * Print various details about the given project.
	 *
	 * ## OPTIONS
	 *
	 * <project>
	 * : Path or ID of the project.
	 *
	 * [--format=<format>]
	 * : Render output in a particular format.
	 * ---
	 * default: list
	 * options:
	 *   - list
	 *   - json
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     # Display various data about the project
	 *     $ wp traduttore project info foo
	 *     Project ID:            1
	 *     Project name:          Foo Project
	 *     Project slug:          foo
	 *     Repository Cache:      /tmp/traduttore-github.com-wearerequired-foo
	 *     Repository URL:        (unknown)
	 *     Repository Type:       github
	 *     Repository VCS Type:   (unknown)
	 *     Repository Visibility: private
	 *     Repository SSH URL:    git@github.com:wearerequired/foo.git
	 *     Repository HTTPS URL:  https://github.com/wearerequired/foo.git
	 *     Repository Instance:   Required\Traduttore\Repository\GitHub
	 *     Loader Instance:       Required\Traduttore\Loader\Git
	 *
	 * @since 3.0.0
	 *
	 * @param array $args Command args.
	 * @param array $assoc_args Associative args.
	 */
	public function info( $args, $assoc_args ): void {
		$locator = new ProjectLocator( $args[0] );
		$project = $locator->get_project();

		if ( ! $project ) {
			WP_CLI::error( 'Project not found' );
		}

		$repository = ( new RepositoryFactory() )->get_repository( $project );
		$loader     = $repository ? ( new LoaderFactory() )->get_loader( $repository ) : null;

		$project_id            = $project->get_id();
		$project_name          = $project->get_name();
		$project_slug          = $project->get_slug();
		$local_path            = $loader ? $loader->get_local_path() : '';
		$repository_url        = $project->get_repository_url() ?? '(unknown)';
		$repository_type       = $repository ? $repository->get_type() : $project->get_repository_type();
		$repository_vcs_type   = $project->get_repository_vcs_type() ?? '(unknown)';
		$repository_visibility = $project->get_repository_visibility() ?? '(unknown)';
		$repository_ssh_url    = $repository ? $repository->get_ssh_url() : '(unknown)';
		$repository_https_url  = $repository ? $repository->get_https_url() : '(unknown)';
		$repository_instance   = $repository ? get_class( $repository ) : '(unknown)';
		$loader_instance       = $loader ? get_class( $loader ) : '';

		if ( \WP_CLI\Utils\get_flag_value( $assoc_args, 'format' ) === 'json' ) {
			$info = array(
				'id'                    => $project_id,
				'name'                  => $project_name,
				'slug'                  => $project_slug,
				'repository_cache'      => $local_path,
				'repository_url'        => $repository_url,
				'repository_type'       => $repository_type,
				'repository_vcs_type'   => $repository_vcs_type,
				'repository_visibility' => $repository_visibility,
				'repository_ssh_url'    => $repository_ssh_url,
				'repository_https_url'  => $repository_https_url,
				'repository_instance'   => $repository_instance,
				'loader_instance'       => $loader_instance,
			);

			WP_CLI::line( json_encode( $info ) );
		} else {
			WP_CLI::line( "Project ID:\t\t" . $project_id );
			WP_CLI::line( "Project name:\t\t" . $project_name );
			WP_CLI::line( "Project slug:\t\t" . $project_slug );
			WP_CLI::line( "Repository Cache:\t" . $local_path );
			WP_CLI::line( "Repository URL:\t\t" . $repository_url );
			WP_CLI::line( "Repository Type:\t" . $repository_type );
			WP_CLI::line( "Repository VCS Type:\t" . $repository_vcs_type );
			WP_CLI::line( "Repository Visibility:\t" . $repository_visibility );
			WP_CLI::line( "Repository SSH URL:\t" . $repository_ssh_url );
			WP_CLI::line( "Repository HTTPS URL:\t" . $repository_https_url );
			WP_CLI::line( "Repository Instance:\t" . $repository_instance );
			WP_CLI::line( "Loader Instance:\t" . $loader_instance );
		}
	}

	/**
	 * List language packs for the given project.
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
	 * * Completed
	 * * Updated
	 * * English Name
	 * * Native Name
	 * * Package
	 *
	 * ## EXAMPLES
	 *
	 *     # Display available language packs for the given project
	 *     $ wp traduttore project list-language-packs 1 --fields=Locale,Package
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

		/* @var GP_Translation_Set $set */
		foreach ( $translation_sets as $set ) {
			/* @var GP_Locale $locale */
			$locale = GP_Locales::by_slug( $set->locale );

			$zip_provider = new ZipProvider( $set );

			$language_packs[] = [
				'Locale'       => $locale->wp_locale,
				// Adding 0 removes trailing zeros.
				'Completed'    => sprintf( '%s%%', number_format( $set->percent_translated(), 1 ) + 0 ),
				'Updated'      => $zip_provider->get_last_build_time(),
				'English Name' => $locale->english_name,
				'Native Name'  => $locale->native_name,
				'Package'      => file_exists( $zip_provider->get_zip_path() ) ? $zip_provider->get_zip_url() : 'n/a',
			];
		}

		$formatter = new WP_CLI\Formatter(
			$assoc_args,
			[
				'Locale',
				'Completed',
				'Updated',
				'English Name',
				'Native Name',
				'Package',
			]
		);

		$formatter->display_items( $language_packs );
	}

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
	 *
	 * @param array $args Command args.
	 * @param array $assoc_args Associative args.
	 */
	public function build( $args, $assoc_args ): void {
		$locator = new ProjectLocator( $args[0] );
		$project = $locator->get_project();

		if ( ! $project ) {
			WP_CLI::error( 'Project not found' );
		}

		$translation_sets = (array) GP::$translation_set->by_project_id( $project->get_id() );

		/* @var GP_Translation_Set $translation_set */
		foreach ( $translation_sets as $translation_set ) {
			$zip_provider = new ZipProvider( $translation_set );

			if ( ! get_flag_value( $assoc_args, 'force' ) && $translation_set->last_modified() <= $zip_provider->get_last_build_time() ) {
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

	/**
	 * Updates project translations from source code repository.
	 *
	 * Finds the project the repository belongs to and updates the translations accordingly.
	 *
	 * ## OPTIONS
	 *
	 * <project|url>
	 * : Project path / ID or source code repository URL, e.g. https://github.com/wearerequired/required-valencia
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
	 *
	 * @param array $args Command args.
	 * @param array $assoc_args Associative args.
	 */
	public function update( $args, $assoc_args ): void {
		$delete  = get_flag_value( $assoc_args, 'delete', false );
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

		$runner = new Runner( $loader, $updater );

		if ( $delete ) {
			$runner->delete_local_repository();
		}

		$success = $runner->run();

		if ( $success ) {
			WP_CLI::success( sprintf( 'Updated translations for project (ID: %d)!', $project->get_id() ) );

			return;
		}

		WP_CLI::warning( sprintf( 'Could not update translations for project (ID: %d)!', $project->get_id() ) );
	}
}
