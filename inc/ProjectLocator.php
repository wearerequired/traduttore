<?php
/**
 * ProjectLocator class.
 *
 * @since 2.0.0
 *
 * @package Required\Traduttore
 */

namespace Required\Traduttore;

use GP;
use GP_Project;

/**
 * Helper class to find a GlotPress project based on path, ID, or GitHub repository URL.
 *
 * @since 2.0.0
 */
class ProjectLocator {
	/**
	 * Possible GlotPress project ID or path or GitHub repository path.
	 *
	 * @var string|int Project information.
	 */
	protected $project;

	/**
	 * ProjectLocator constructor.
	 *
	 * @param string|int $project Project information.
	 */
	public function __construct( $project ) {
		$this->project = $project;
	}

	/**
	 * Returns the found project.
	 *
	 * @return GP_Project|false GlotPress project on success, false otherwise.
	 */
	public function get_project() {
		$project = GP::$project->by_path( $this->project );

		if ( is_numeric( $this->project ) ) {
			$project = GP::$project->get( (int) $this->project );
		}

		if ( ! $project ) {
			$project = $this->find_by_github_repository_url();
		}

		return $project;
	}

	/**
	 * Finds a GlotPress project by a GitHub repository URL, e.g. https://github.com/wearerequired/required-valencia.
	 *
	 * @since 2.0.0
	 * @return false|GP_Project Project on success, false otherwise.
	 */
	protected function find_by_github_repository_url() {
		global $wpdb;

		$table = GP::$project->table;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$query = $wpdb->prepare( "SELECT * FROM $table WHERE source_url_template LIKE %s LIMIT 1", '%' . $wpdb->esc_like( $this->project ) . '%' );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return GP::$project->coerce( $wpdb->get_row( $query ) );
	}
}
