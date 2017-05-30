<?php
/**
 * Plugin class.
 *
 * @since 1.0.0
 */

namespace Required\Traduttore;

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
	}
}
