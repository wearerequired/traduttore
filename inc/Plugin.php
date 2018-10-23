<?php
/**
 * Plugin class.
 *
 * @since 1.0.0
 *
 * @package Required\Traduttore
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
	public function init(): void {
		$this->register_hooks();
	}

	/**
	 * Registers actions and filters.
	 *
	 * @since 1.0.0
	 */
	public function register_hooks(): void {
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );

		add_action(
			'gp_init',
			function () {
				GP::$router->add( '/api/translations/(.+?)', [ TranslationApiRoute::class, 'route_callback' ] );
			}
		);

		add_action(
			'gp_translation_saved',
			function ( GP_Translation $translation ) {
				// Regenerate ZIP file if not already scheduled.
				if ( ! wp_next_scheduled( 'traduttore.generate_zip', [ $translation->translation_set_id ] ) ) {
					wp_schedule_single_event( time() + MINUTE_IN_SECONDS * 5, 'traduttore.generate_zip', [ $translation->translation_set_id ] );
				}
			}
		);

		add_action(
			'traduttore.generate_zip',
			function( $translation_set_id ) {
				/* @var GP_Translation_Set $translation_set */
				$translation_set = GP::$translation_set->get( $translation_set_id );

				$zip_provider = new ZipProvider( $translation_set );

				if ( $translation_set->last_modified() <= $zip_provider->get_last_build_time() ) {
					return;
				}

				$zip_provider->generate_zip_file();
			}
		);

		add_action(
			'traduttore.update',
			function ( $project_id ) {
				$locator = new ProjectLocator( $project_id );
				$project = $locator->get_project();

				if ( ! $project ) {
					return;
				}

				$loader = ( new LoaderFactory() )->get_loader( $project );

				if ( ! $loader ) {
					return;
				}

				$updater = new Updater( $project );
				$runner  = new Runner( $loader, $updater );

				$runner->delete_local_repository();

				$runner->run();
			}
		);

		add_filter(
			'slack_get_events',
			function( $events ) {
				$events['traduttore.zip_generated'] = [
					'action'      => 'traduttore.zip_generated',
					'description' => __( 'When a new translation ZIP file is built', 'traduttore' ),
					'message'     => function( $zip_path, $zip_url, GP_Translation_Set $translation_set ) {
						/* @var GP_Locale $locale */
						$locale  = GP_Locales::by_slug( $translation_set->locale );
						$project = GP::$project->get( $translation_set->project_id );

						/**
						 * Filters whether a Slack notification for translation updates from GitHub should be sent.
						 *
						 * @since 3.0.0
						 *
						 * @param bool               $send_message    Whether to send a notification or not. Default true.
						 * @param GP_Translation_Set $translation_set Translation set the ZIP is for.
						 * @param GP_Project         $project         The GlotPress project that was updated.
						 */
						$send_message = apply_filters( 'traduttore.zip_generated_send_notification', true, $translation_set, $project );

						if ( ! $send_message ) {
							return false;
						}

						$message = sprintf(
							'<%1$s|%2$s>: ZIP file updated for *%3$s*. (<%4$s|Download>)',
							home_url( gp_url_project( $project ) ),
							$project->name,
							$locale->english_name,
							$zip_url
						);

						/**
						 * Filters the Slack notification message when a new translation ZIP file is built.
						 *
						 * @since 3.0.0
						 *
						 * @param string             $message         The notification message.
						 * @param GP_Translation_Set $translation_set Translation set the ZIP is for.
						 * @param GP_Project         $project         The GlotPress project that was updated.
						 */
						return apply_filters( 'traduttore.zip_generated_notification_message', $message, $translation_set, $project );
					},
				];

				$events['traduttore.updated'] = [
					'action'      => 'traduttore.updated',
					'description' => __( 'When new translations are updated for a project', 'traduttore' ),
					'message'     => function( GP_Project $project, array $stats ) {
						[
							$originals_added,
							$originals_existing,
							$originals_fuzzied,
							$originals_obsoleted,
							$originals_error,
						] = $stats;

						$send_message = $originals_added + $originals_fuzzied + $originals_obsoleted + $originals_error > 0;

						/**
						 * Filters whether a Slack notification for translation updates should be sent.
						 *
						 * @since 3.0.0
						 *
						 * @param bool       $send_message Whether to send a notification or not.
						 *                                 Defaults to true, unless there were no string changes at all.
						 * @param GP_Project $project      The GlotPress project that was updated.
						 * @param array      $stats        Stats about the number of imported translations.
						 */
						$send_message = apply_filters( 'traduttore.updated_send_notification', $send_message, $project, $stats );

						if ( ! $send_message ) {
							return false;
						}

						$message = sprintf(
							'<%1$s|%2$s>: *%3$d* new strings were added, *%4$d* were fuzzied, and *%5$d* were obsoleted. There were *%6$d* errors.',
							home_url( gp_url_project( $project ) ),
							$project->name,
							$originals_added,
							$originals_fuzzied,
							$originals_obsoleted,
							$originals_error
						);

						/**
						 * Filters the Slack notification message when new translations are updated.
						 *
						 * @since 3.0.0
						 *
						 * @param string     $message The notification message.
						 * @param GP_Project $project The GlotPress project that was updated.
						 * @param array      $stats   Stats about the number of imported translations.
						 */
						return apply_filters( 'traduttore.updated_notification_message', $message, $project, $stats );
					},
				];

				return $events;
			}
		);

		/**
		 * Filter Restricted Site Access to allow external requests to Traduttore's endpoints.
		 *
		 * @param bool $is_restricted Whether access is restricted.
		 * @param WP   $wp            The WordPress object. Only available on the front end.
		 * @return bool Whether access should be restricted.
		 */
		add_filter(
			'restricted_site_access_is_restricted',
			function( $is_restricted, $wp ) {
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
			},
			10,
			2
		);
	}

	/**
	 * Clears all scheduled hooks upon plugin deactivation.
	 *
	 * @since 2.0.0
	 */
	public static function on_plugin_deactivation(): void {
		wp_unschedule_hook( 'traduttore.generate_zip' );
		wp_unschedule_hook( 'traduttore.update' );
	}

	/**
	 * Registers new REST API routes.
	 *
	 * @since 2.0.0
	 */
	public function register_rest_routes(): void {
		register_rest_route(
			'github-webhook/v1',
			'/push-event',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'github_webhook_push' ],
				'permission_callback' => [ $this, 'github_webhook_permission_push' ],
			]
		);
	}

	/**
	 * GitHub webhook callback function.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_Error|WP_REST_Response REST response on success, error object on failure.
	 */
	public function github_webhook_push( WP_REST_Request $request ) {
		$params     = $request->get_params();
		$event_name = $request->get_header( 'x-github-event' );

		if ( 'ping' === $event_name ) {
			return new WP_REST_Response( [ 'result' => 'OK' ] );
		}

		if ( ! isset( $params['repository']['html_url'], $params['ref'] ) ) {
			return new WP_Error( '400', 'Bad request' );
		}

		// We only care about the default branch but don't want to send an error still.
		if ( 'refs/heads/' . $params['repository']['default_branch'] !== $params['ref'] ) {
			return new WP_REST_Response( [ 'result' => 'Not the default branch' ] );
		}

		$locator = new ProjectLocator( $params['repository']['html_url'] );
		$project = $locator->get_project();

		if ( ! $project ) {
			return new WP_Error( '404', 'Could not find project for this repository' );
		}

		if ( ! wp_next_scheduled( 'traduttore.update', [ $project->get_id() ] ) ) {
			wp_schedule_single_event( time() + MINUTE_IN_SECONDS * 3, 'traduttore.update', [ $project->get_id() ] );
		}

		return new WP_REST_Response( [ 'result' => 'OK' ] );
	}

	/**
	 * Permission callback for the incoming webhook REST route.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return True if permission is granted, false otherwise.
	 */
	public function github_webhook_permission_push( $request ) : bool {
		$event_name = $request->get_header( 'x-github-event' );

		if ( ! $event_name ) {
			return false;
		}

		if ( 'ping' === $event_name ) {
			return true;
		}

		if ( 'push' !== $event_name ) {
			return false;
		}

		if ( ! defined( 'TRADUTTORE_GITHUB_SYNC_SECRET' ) ) {
			return false;
		}

		$github_signature = $request->get_header( 'x-hub-signature' );

		if ( ! $github_signature ) {
			return false;
		}

		$payload_signature = 'sha1=' . hash_hmac( 'sha1', $request->get_body(), TRADUTTORE_GITHUB_SYNC_SECRET );

		return hash_equals( $github_signature, $payload_signature );
	}
}
