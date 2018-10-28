<?php
/**
 * TranslationApi class.
 *
 * @since 2.0.0
 *
 * @package Required\Traduttore
 */

namespace Required\Traduttore;

use GP;
use GP_Locale;
use GP_Locales;
use GP_Route_Main;
use GP_Translation_Set;

/**
 * Class used to add a simple translations API.
 *
 * @since 2.0.0
 */
class TranslationApiRoute extends GP_Route_Main {
	/**
	 * Route callback handler.
	 *
	 * @since 2.0.0
	 *
	 * @param string $project_path Project path.
	 */
	public function route_callback( $project_path ) : void {
		$this->header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );

		// Get the project object from the project path that was passed in.
		$project = GP::$project->by_path( $project_path );
		if ( ! $project ) {
			$this->status_header( 404 );
			echo wp_json_encode( [ 'error' => 'Project not found.' ] );
			return;
		}

		$translation_sets = (array) GP::$translation_set->by_project_id( $project->id );

		$result = [];

		/** @var GP_Translation_Set $set */
		foreach ( $translation_sets as $set ) {
			/** @var GP_Locale $locale */
			$locale = GP_Locales::by_slug( $set->locale );

			$zip_provider = new ZipProvider( $set );

			if ( ! $zip_provider->get_last_build_time() || ! file_exists( $zip_provider->get_zip_path() ) ) {
				continue;
			}

			$result[] = [
				'language'     => $locale->wp_locale,
				'version'      => '1.0',
				'updated'      => $zip_provider->get_last_build_time(),
				'english_name' => $locale->english_name,
				'native_name'  => $locale->native_name,
				'package'      => $zip_provider->get_zip_url(),
				'iso'          => array_filter(
					[
						$locale->lang_code_iso_639_1,
						$locale->lang_code_iso_639_2,
						$locale->lang_code_iso_639_3,
					]
				),
			];
		}

		echo wp_json_encode( [ 'translations' => $result ] );
	}
}
