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
	 * @access public
	 */
	public function init() {
		$this->register_hooks();
	}

	/**
	 * Registers actions and filters.
	 *
	 * @since 1.0.0
	 * @access public
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
}
