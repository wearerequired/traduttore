<?php
/**
 * Plugin class.
 *
 * @since 1.0.0
 */

namespace Required\Traduttore;

use GP;
use GP_Locale;
use GP_Locales;
use GP_Translation;
use GP_Translation_Set;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Class used to register main actions and filters.
 *
 * @since 1.0.0
 */
class Plugin {

	/**
	 * Initializes the plugin.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		$this->register_hooks();
	}

	/**
	 * Registers actions and filters.
	 *
	 * @since 1.0.0
	 */
	public function register_hooks() {
		$permissions = new Permissions();
		add_filter( 'gp_projects', [ $permissions, 'filter_projects' ] );

		add_action( 'gp_tmpl_load_locations', function( $locations ) {
			$core_templates = GP_PATH . 'gp-templates/';

			require_once $core_templates . 'helper-functions.php';

			$locations[] = $core_templates;

			return $locations;
		}, 50 );

		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );

		add_action( 'gp_init', function () {
			GP::$router->add( '/api/translations/(.+?)', [ TranslationApiRoute::class, 'route_callback' ] );
		} );

		add_action( 'gp_translation_saved', function ( GP_Translation $translation ) {
			// Regenerate ZIP file if not already scheduled.
			if ( ! wp_next_scheduled( 'traduttore_generate_zip', [ $translation->translation_set_id ] ) ) {
				wp_schedule_single_event( time() + MINUTE_IN_SECONDS * 15, 'traduttore_generate_zip', [ $translation->translation_set_id ] );
			}
		} );

		add_action( 'traduttore_generate_zip', function( $translation_set_id ) {
			/** @var GP_Translation_Set $translation_set */
			$translation_set = GP::$translation_set->get( $translation_set_id );

			$zip_provider = new ZipProvider( $translation_set );
			$success      = $zip_provider->generate_zip_file();

			do_action( 'traduttore_zip_generated', $success, $translation_set );
		} );

		add_action( 'traduttore_update_from_github', function ( $repository, $project_id ) {
			$project = GP::$project->get( $project_id );

			$github_updater = new GitHubUpdater( $repository, $project );
			$success        = $github_updater->fetch_and_update();

			do_action( 'traduttore_updated_from_github', $success, $project );
		} );

		add_filter( 'slack_get_events', function( $events ) {
			$events['traduttore_zip_generated'] = array(
				'action'      => 'traduttore_zip_generated',
				'description' => __( 'When a new translation ZIP files is built', 'traduttore' ),
				'message'     => function( $success, GP_Translation_Set $translation_set ) {
					if ( ! $success ) {
						// Todo: Send error message.
						return false;
					}

					/** @var GP_Locale $locale */
					$locale  = GP_Locales::by_slug( $translation_set->locale );
					$project = GP::$project->get( $translation_set->project_id );

					return sprintf(
						'Successfully updated *%1$s* ZIP file for *%2$s*',
						$locale->english_name,
						$project->name
					);
				}
			);

			return $events;
		} );
	}

	/**
	 * Clears all scheduled hooks upon plugin deactivation.
	 *
	 * @since 1.0.0
	 */
	public static function on_plugin_deactivation() {
		wp_unschedule_hook( 'traduttore_generate_zip' );
		wp_unschedule_hook( 'traduttore_update_from_github' );
	}

	/**
	 * Registers new REST API routes.
	 *
	 * @since 1.0.0
	 */
	public function register_rest_routes() {
		register_rest_route( 'github-webhook/v1', '/push-event', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this,  'github_webhook_push' ],
			'permission_callback' => [ $this,  'github_webhook_permission_push'],
		] );
	}

	/**
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function github_webhook_push( WP_REST_Request $request ) {
		$params = $request->get_params();

		if ( ! isset( $params['repository']['url'] ) ) {
			return new WP_Error( '400', 'Bad request' );
		}

		$project = GitHubUpdater::find_project( $params['html_url'] );

		if ( ! $project ) {
			return new WP_Error( '404', 'Could not find project for this repository' );
		}

		// Schedule job to be run in the background to
		if ( ! wp_next_scheduled( 'traduttore_update_from_github', [ $params['repository']['url'] ] ) ) {
			wp_schedule_single_event( time() + MINUTE_IN_SECONDS * 15, 'traduttore_update_from_github', [ $params['repository']['url'], $project->id ] );
		}

		return new WP_REST_Response( [ 'OK' ] );
	}

	/**
	 * Permission callback for the incoming webhook REST route.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return True if permission is granted, false otherwise.
	 */
	public function github_webhook_permission_push( $request ) {
		$event_name = $request->get_header( 'x-github-event' );

		if ( 'push' !== $event_name ) {
			return false;
		}

		if ( ! \defined( 'TRADUTTORE_GITHUB_SYNC_SECRET' ) ) {
			return false;
		}

		$github_signature  = $request->get_header( 'x-hub-signature' );
		$payload_signature = 'sha1=' . hash_hmac( 'sha1', $request->get_body(), TRADUTTORE_GITHUB_SYNC_SECRET );

		return hash_equals( $github_signature, $payload_signature );
	}
}
