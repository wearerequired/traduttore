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
	 * Project instance.
	 *
	 * @var Project Project instance.
	 */
	protected $project;

	/**
	 * ProjectLocator constructor.
	 *
	 * @param string|int $project Possible GlotPress project ID or path or GitHub repository path.
	 */
	public function __construct( $project ) {
		$this->project = $this->find_project( $project );
	}

	/**
	 * Returns the found project.
	 *
	 * @return Project GlotPress project.
	 */
	public function get_project() :? Project {
		return $this->project;
	}

	/**
	 * Attempts to find a GlotPress project.
	 *
	 * @param string|int $project Possible GlotPress project ID or path or GitHub repository path.
	 * @return Project Project instance.
	 */
	protected function find_project( $project ) :? Project {
		$found = GP::$project->by_path( $project );

		if ( ! $found && is_numeric( $project ) ) {
			$found = GP::$project->get( (int) $project );
		}

		if ( ! $found ) {
			$found = $this->find_project_by_github_repository_url( $project );
		}

		return $found ? new Project( $found ) : null;
	}

	/**
	 * Finds a GlotPress project by a GitHub repository URL, e.g. https://github.com/wearerequired/required-valencia.
	 *
	 * @since 2.0.0
	 *
	 * @param string $project Possible GitHub repository path or URL.
	 * @return false|GP_Project Project on success, false otherwise.
	 */
	protected function find_project_by_github_repository_url( $project ) {
		global $wpdb;

		$table = GP::$project->table;

		// phpcs:ignore WordPress.WP.PreparedSQL.NotPrepared
		$query = $wpdb->prepare( "SELECT * FROM $table WHERE source_url_template LIKE %s LIMIT 1", '%' . $wpdb->esc_like( $project ) . '%' );

		// phpcs:ignore WordPress.WP.PreparedSQL.NotPrepared
		return GP::$project->coerce( $wpdb->get_row( $query ) );
	}
}
