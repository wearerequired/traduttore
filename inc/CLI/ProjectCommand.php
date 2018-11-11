<?php
/**
 * Command for printing project information.
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
use Required\Traduttore\LoaderFactory;
use Required\Traduttore\ProjectLocator;
use Required\Traduttore\RepositoryFactory;
use Required\Traduttore\ZipProvider;
use WP_CLI;
use WP_CLI_Command;

/**
 * Project information command.
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
	 * * Language
	 * * Updated
	 * * English Name
	 * * Native Name
	 * * Package
	 *
	 * ## EXAMPLES
	 *
	 *     # Display available language packs for the given project
	 *     $ wp traduttore project list-language-packs 1 --fields=Language,Package
	 *     +----------+----------------------------------------------------------------+
	 *     | Language | Package                                                        |
	 *     +----------+----------------------------------------------------------------+
	 *     | fr_FR    | https://translate.example.com/content/traduttore/foo-fr_FR.zip |
	 *     | de_DE    | https://translate.example.com/content/traduttore/foo-de_DE.zip |
	 *     +----------+----------------------------------------------------------------+
	 *
	 * @subcommand list-language-packs
	 *
	 * @since 3.0.0
	 *
	 * @param array $args Command args.
	 * @param array $assoc_args Associative args.
	 */
	public function list_language_packs( $args, $assoc_args ): void {
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

			if ( ! $zip_provider->get_last_build_time() || ! file_exists( $zip_provider->get_zip_path() ) ) {
				continue;
			}

			$language_packs[] = [
				'Language'     => $locale->wp_locale,
				'Updated'      => $zip_provider->get_last_build_time(),
				'English Name' => $locale->english_name,
				'Native Name'  => $locale->native_name,
				'Package'      => $zip_provider->get_zip_url(),
			];
		}

		$formatter = new WP_CLI\Formatter(
			$assoc_args,
			[
				'Language',
				'Updated',
				'English Name',
				'Native Name',
				'Package',
			]
		);

		$formatter->display_items( $language_packs );
	}
}
