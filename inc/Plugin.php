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
		add_action( 'init', [ $this, 'setup_translations' ] );

		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );

		add_action( 'gp_init', [ $this, 'register_glotpress_api_routes' ] );

		add_action(
			'gp_translation_saved',
			function ( GP_Translation $translation ) {
				/* @var GP_Translation_Set $translation_set */
				$translation_set = GP::$translation_set->get( $translation->translation_set_id );

				$zip_provider = new ZipProvider( $translation_set );

				$zip_provider->schedule_generation();
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

				$repository = ( new RepositoryFactory() )->get_repository( $project );

				if ( ! $repository ) {
					return;
				}

				$loader = ( new LoaderFactory() )->get_loader( $repository );

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
						$project = new Project( GP::$project->get( $translation_set->project_id ) );

						/**
						 * Filters whether a Slack notification for translation updates from GitHub should be sent.
						 *
						 * @since 3.0.0
						 *
						 * @param bool               $send_message    Whether to send a notification or not. Default true.
						 * @param GP_Translation_Set $translation_set Translation set the language pack is for.
						 * @param Project            $project         The project that was updated.
						 */
						$send_message = apply_filters( 'traduttore.zip_generated_send_notification', true, $translation_set, $project );

						if ( ! $send_message ) {
							return false;
						}

						$message = sprintf(
							'<%1$s|%2$s>: ZIP file updated for *%3$s*. (<%4$s|Download>)',
							home_url( gp_url_project( $project->get_project() ) ),
							$project->get_name(),
							$locale->english_name,
							$zip_url
						);

						/**
						 * Filters the Slack notification message for when a new language pack has been built.
						 *
						 * @since 3.0.0
						 *
						 * @param string             $message         The notification message.
						 * @param GP_Translation_Set $translation_set Translation set the language pack is for.
						 * @param GP_Project         $project         The GlotPress project that was updated.
						 */
						return apply_filters( 'traduttore.zip_generated_notification_message', $message, $translation_set, $project );
					},
				];

				$events['traduttore.updated'] = [
					'action'      => 'traduttore.updated',
					'description' => __( 'When new translations are updated for a project', 'traduttore' ),
					'message'     => function( Project $project, array $stats ) {
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
						 * @param bool    $send_message Whether to send a notification or not.
						 *                              Defaults to true, unless there were no string changes at all.
						 * @param Project $project      The Project that was updated.
						 * @param array   $stats        Stats about the number of imported translations.
						 */
						$send_message = apply_filters( 'traduttore.updated_send_notification', $send_message, $project, $stats );

						if ( ! $send_message ) {
							return false;
						}

						$message = sprintf(
							'<%1$s|%2$s>: *%3$d* new strings were added, *%4$d* were fuzzied, and *%5$d* were obsoleted. There were *%6$d* errors.',
							home_url( gp_url_project( $project->get_project() ) ),
							$project->get_name(),
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
						 * @param string  $message The notification message.
						 * @param Project $project The project that was updated.
						 * @param array   $stats   Stats about the number of imported translations.
						 */
						return apply_filters( 'traduttore.updated_notification_message', $message, $project, $stats );
					},
				];

				return $events;
			}
		);

		add_filter( 'restricted_site_access_is_restricted', [ $this, 'filter_restricted_site_access_is_restricted' ], 10, 2 );
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
	 * Sets up translation loading for this plugin using Traduttore Registry.
	 *
	 * @since 3.0.0
	 */
	public function setup_translations(): void {
		\Required\Traduttore_Registry\add_project(
			'plugin',
			'traduttore',
			'https://translate.required.com/api/translations/required/traduttore'
		);
	}

	/**
	 * Registers the translations API route in GlotPress.
	 *
	 * @since 3.0.0
	 */
	public function register_glotpress_api_routes(): void {
		GP::$router->add( '/api/translations/(.+?)', [ TranslationApiRoute::class, 'route_callback' ] );
	}

	/**
	 * Registers new REST API routes.
	 *
	 * @since 2.0.0
	 */
	public function register_rest_routes(): void {
		// Legacy GitHub-only route for incoming webhooks.
		register_rest_route(
			'github-webhook/v1',
			'/push-event',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'incoming_webhook_callback' ],
				'permission_callback' => [ $this, 'incoming_webhook_permission_callback' ],
			]
		);

		// General catch-all route for incoming webhooks.
		register_rest_route(
			'traduttore/v1',
			'/incoming-webhook',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'incoming_webhook_callback' ],
				'permission_callback' => [ $this, 'incoming_webhook_permission_callback' ],
			]
		);
	}

	/**
	 * Filter Restricted Site Access to allow external requests to Traduttore's endpoints.
	 *
	 * @since 3.0.0
	 *
	 * @param bool $is_restricted Whether access is restricted.
	 * @param WP   $wp            The WordPress object. Only available on the front end.
	 * @return bool Whether access should be restricted.
	 */
	public function filter_restricted_site_access_is_restricted( $is_restricted, $wp ): bool {
		if ( $wp instanceof WP && isset( $wp->query_vars['rest_route'] ) ) {
			$route = untrailingslashit( $wp->query_vars['rest_route'] );

			if ( '/github-webhook/v1/push-event' === $route ) {
				return false;
			}

			if ( '/traduttore/v1/incoming-webhook' === $route ) {
				return false;
			}
		}

		if ( $wp instanceof WP && isset( $wp->query_vars['gp_route'] ) && class_exists( '\GP' ) ) {
			$route = untrailingslashit( GP::$router->request_uri() );

			if ( 0 === strpos( $route, '/api/translations' ) ) {
				return false;
			}
		}

		return $is_restricted;
	}

	/**
	 * Permission callback for incoming webhooks.
	 *
	 * Picks a webhook handler based on the request information.
	 *
	 * @since 3.0.0
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return bool True if permission is granted, false otherwise.
	 */
	public function incoming_webhook_permission_callback( WP_REST_Request $request ) : bool {
		$result  = false;
		$handler = ( new WebhookHandlerFactory() )->get_handler( $request );

		if ( $handler ) {
			$result = $handler->permission_callback();
		}

		/**
		 * Filters the result of the incoming webhook permission callback.
		 *
		 * @since 3.0.0
		 *
		 * @param bool                $result  Permission callback result. True if permission is granted, false otherwise.
		 * @param WebhookHandler|null $handler The current webhook handler if found.
		 * @param WP_REST_Request     $request The current request.
		 */
		return apply_filters( 'traduttore.incoming_webhook_permission_callback', $result, $handler, $request );
	}

	/**
	 * Callback for incoming webhooks.
	 *
	 * Picks a webhook handler based on the request information.
	 *
	 * @since 3.0.0
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_Error|WP_REST_Response REST response on success, error object on failure.
	 */
	public function incoming_webhook_callback( WP_REST_Request $request ) {
		$result  = new WP_Error( '400', 'Bad request' );
		$handler = ( new WebhookHandlerFactory() )->get_handler( $request );

		if ( $handler ) {
			$result = $handler->callback();
		}

		/**
		 * Filters the result of the incoming webhook callback.
		 *
		 * @since 3.0.0
		 *
		 * @param WP_Error|WP_REST_Response $result  REST response on success, error object on failure.
		 * @param WebhookHandler|null       $handler The current webhook handler if found.
		 * @param WP_REST_Request           $request The current request.
		 */
		return apply_filters( 'traduttore.incoming_webhook_callback', $result, $handler, $request );
	}
}
