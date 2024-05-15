<?php
/**
 * Command for managing projects
 *
 * @since 3.0.0
 */

namespace Required\Traduttore\CLI;

use Required\Traduttore\LoaderFactory;
use Required\Traduttore\ProjectLocator;
use Required\Traduttore\RepositoryFactory;
use Required\Traduttore\Runner;
use Required\Traduttore\Updater;
use WP_CLI;
use WP_CLI_Command;
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
	 *     Version:               1.0.1
	 *     Text domain:           foo-plugin
	 *     Last updated:          2018-11-11 11:11:11
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
	 * @param string[] $args Command args.
	 * @param string[] $assoc_args Associative args.
	 */
	public function info( array $args, array $assoc_args ): void {
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
		$project_version       = $project->get_version();
		$project_text_domain   = $project->get_text_domain();
		$last_updated          = $project->get_last_updated_time() ? $project->get_last_updated_time()->format( DATE_ATOM ) : '';
		$local_path            = $loader ? $loader->get_local_path() : '';
		$repository_url        = $project->get_repository_url() ?? '(unknown)';
		$repository_type       = $repository ? $repository->get_type() : $project->get_repository_type();
		$repository_vcs_type   = $project->get_repository_vcs_type() ?? '(unknown)';
		$repository_visibility = $project->get_repository_visibility() ?? '(unknown)';
		$repository_ssh_url    = $repository ? $repository->get_ssh_url() : '(unknown)';
		$repository_https_url  = $repository ? $repository->get_https_url() : '(unknown)';
		$repository_instance   = $repository ? \get_class( $repository ) : '(unknown)';
		$loader_instance       = $loader ? \get_class( $loader ) : '(unknown)';

		if ( get_flag_value( $assoc_args, 'format' ) === 'json' ) {
			$info = [
				'id'                    => $project_id,
				'name'                  => $project_name,
				'slug'                  => $project_slug,
				'version'               => $project_version,
				'text_domain'           => $project_text_domain,
				'last_updated'          => $last_updated,
				'repository_cache'      => $local_path,
				'repository_url'        => $repository_url,
				'repository_type'       => $repository_type,
				'repository_vcs_type'   => $repository_vcs_type,
				'repository_visibility' => $repository_visibility,
				'repository_ssh_url'    => $repository_ssh_url,
				'repository_https_url'  => $repository_https_url,
				'repository_instance'   => $repository_instance,
				'loader_instance'       => $loader_instance,
			];

			WP_CLI::line( (string) json_encode( $info ) );
		} else {
			WP_CLI::line( "Project ID:\t\t" . $project_id );
			WP_CLI::line( "Project name:\t\t" . $project_name );
			WP_CLI::line( "Project slug:\t\t" . $project_slug );
			WP_CLI::line( "Version:\t\t" . $project_version );
			WP_CLI::line( "Text domain:\t\t" . $project_text_domain );
			WP_CLI::line( "Last updated:\t\t" . $last_updated );
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
	 * Updates project translations from source code repository.
	 *
	 * Finds the project the repository belongs to and updates the translations accordingly.
	 *
	 * ## OPTIONS
	 *
	 * <project|url>
	 * : Project path / ID or source code repository URL, e.g. https://github.com/wearerequired/required-valencia
	 *
	 * [--cached]
	 * : Use cached repository information and do not try to download code from remote.
	 *
	 * [--delete]
	 * : Whether to first delete the existing local repository or not.
	 *
	 * ## EXAMPLES
	 *
	 *     # Update translations from repository URL.
	 *     $ wp traduttore project update https://github.com/wearerequired/required-valencia
	 *     Success: Updated translations for project (ID: 123)!
	 *
	 *     # Update translations from project path.
	 *     $ wp traduttore project update required/required-valencia
	 *     Success: Updated translations for project (ID: 123)!
	 *
	 *     # Update translations from project ID.
	 *     $ wp traduttore project update 123
	 *     Success: Updated translations for project (ID: 123)!
	 *
	 * @since 3.0.0
	 *
	 * @param string[] $args       Command args.
	 * @param string[] $assoc_args Associative args.
	 */
	public function update( array $args, array $assoc_args ): void {
		$delete  = get_flag_value( $assoc_args, 'delete', false );
		$cached  = get_flag_value( $assoc_args, 'cached', false );
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

		$success = $runner->run( $cached );

		if ( $success ) {
			WP_CLI::success( sprintf( 'Updated translations for project (ID: %d)!', $project->get_id() ) );

			return;
		}

		WP_CLI::warning( sprintf( 'Could not update translations for project (ID: %d)!', $project->get_id() ) );
	}
}
