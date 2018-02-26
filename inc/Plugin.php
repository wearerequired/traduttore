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
use GP_Project;
use GP_Translation;
use GP_Translation_Set;
use WP;
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
		} );

		add_action( 'traduttore_update_from_github', function ( $repository ) {
			$project = GitHubUpdater::find_project( $repository );

			if ( ! $project ) {
				return;
			}

			$github_updater = new GitHubUpdater( $repository, $project );
			$github_updater->fetch_and_update();
		} );

		add_filter( 'slack_get_events', function( $events ) {
			$events['traduttore_zip_generated'] = [
				'action'      => 'traduttore_zip_generated',
				'description' => __( 'When a new translation ZIP file is built', 'traduttore' ),
				'message'     => function( $zip_path, $zip_url, GP_Translation_Set $translation_set ) {
					/** @var GP_Locale $locale */
					$locale  = GP_Locales::by_slug( $translation_set->locale );
					$project = GP::$project->get( $translation_set->project_id );

					return sprintf(
						'<%1$s|%2$s>: ZIP file updated for *%3$s*. (<%4$s|Download>)',
						home_url( gp_url_project( $project ) ),
						$project->name,
						$locale->english_name,
						$zip_url
					);
				}
			];

			$events['traduttore_updated_from_github'] = [
				'action'      => 'traduttore_updated_from_github',
				'description' => __( 'When new translations are updated from GitHub', 'traduttore' ),
				'message'     => function( GP_Project $project, array $stats ) {
					list($originals_added, $originals_existing, $originals_fuzzied, $originals_obsoleted, $originals_error) = $stats;

					return sprintf(
						'<%1$s|%2$s>: *%3$d* new strings were added, *%4$d* were fuzzied, and *%5$d* were obsoleted. There were *%6$d* errors.',
						home_url( gp_url_project( $project ) ),
						$project->name,
						$originals_added,
						$originals_fuzzied,
						$originals_obsoleted,
						$originals_error
					);
				}
			];

			return $events;
		} );

		/**
		 * Filter Restricted Site Access to allow external requests to Traduttore's endpoints.
		 *
		 * @param bool $is_restricted Whether access is restricted.
		 * @param WP   $wp            The WordPress object. Only available on the front end.
		 *
		 * @return bool Whether access should be restricted.
		 */
		add_filter( 'restricted_site_access_is_restricted', function( $is_restricted, $wp ) {
			if ( $wp instanceof WP && isset( $wp->query_vars['rest_route'] ) ) {
				$route = untrailingslashit( $wp->query_vars['rest_route'] );

				if ( '/github-webhook/v1/push-event' === $route ) {
					return false;
				}
			}

			if ( $wp instanceof WP && isset( $wp->query_vars['gp_route'] ) && class_exists( '\GP' ) ) {
				$route = GP::$router->request_uri();

				if ( 0 === strpos( $route, '/api/translations/' ) ) {
					return false;
				}
			}

			return $is_restricted;
		}, 10, 2 );
	}

	/**
	 * Clears all scheduled hooks upon plugin deactivation.
	 *
	 * @since 2.0.0
	 */
	public static function on_plugin_deactivation() {
		wp_unschedule_hook( 'traduttore_generate_zip' );
		wp_unschedule_hook( 'traduttore_update_from_github' );
	}

	/**
	 * Registers new REST API routes.
	 *
	 * @since 2.0.0
	 */
	public function register_rest_routes() {
		register_rest_route( 'github-webhook/v1', '/push-event', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this,  'github_webhook_push' ],
			'permission_callback' => [ $this,  'github_webhook_permission_push'],
		] );
	}

	/**
	 * GitHub webhook callback function.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_Error|WP_REST_Response REST response on success, error object on failure.
	 */
	public function github_webhook_push( WP_REST_Request $request ) {
		$params     = $request->get_params();
		$event_name = $request->get_header( 'x-github-event' );

		if ( 'ping' === $event_name ) {
			return new WP_REST_Response( [ 'OK' ] );
		}

		if ( ! isset( $params['repository']['html_url'] ) ) {
			return new WP_Error( '400', 'Bad request' );
		}

		$project = GitHubUpdater::find_project( $params['repository']['html_url'] );

		if ( ! $project ) {
			return new WP_Error( '404', 'Could not find project for this repository' );
		}

		if ( ! wp_next_scheduled( 'traduttore_update_from_github', [ $params['repository']['url'] ] ) ) {
			wp_schedule_single_event( time() + MINUTE_IN_SECONDS * 15, 'traduttore_update_from_github', [ $params['repository']['html_url'] ] );
		}

		return new WP_REST_Response( [ 'OK' ] );
	}

	/**
	 * Permission callback for the incoming webhook REST route.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return True if permission is granted, false otherwise.
	 */
	public function github_webhook_permission_push( $request ) {
		$event_name = $request->get_header( 'x-github-event' );

		if ( ! in_array( $event_name, [ 'push', 'ping' ], true ) ) {
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
