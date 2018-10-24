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
	 * @since 2.0.0
	 *
	 * @var Project Project instance.
	 */
	protected $project;

	/**
	 * ProjectLocator constructor.
	 *
	 * @since 2.0.0
	 *
	 * @param string|int $project Possible GlotPress project ID or path or source code repository path.
	 */
	public function __construct( $project ) {
		$this->project = $this->find_project( $project );
	}

	/**
	 * Returns the found project.
	 *
	 * @since 2.0.0
	 *
	 * @return Project GlotPress project.
	 */
	public function get_project() :? Project {
		return $this->project;
	}

	/**
	 * Attempts to find a GlotPress project.
	 *
	 * @since 2.0.0
	 *
	 * @param string|int $project Possible GlotPress project ID or path or source code repository path.
	 * @return Project Project instance.
	 */
	protected function find_project( $project ) :? Project {
		$found = GP::$project->by_path( $project );

		if ( ! $found && is_numeric( $project ) ) {
			$found = GP::$project->get( (int) $project );
		}

		if ( ! $found ) {
			$found = $this->find_by_source_url_template( $project );
		}

		return $found ? new Project( $found ) : null;
	}

	/**
	 * Finds a GlotPress project by a partially matching source_url_template setting.
	 *
	 * Given a URL like https://github.com/wearerequired/required-valencia, this would match
	 * a setting like https://github.com/wearerequired/required-valencia/blob/master/%file%#L%line%.
	 *
	 * @since 3.0.0
	 *
	 * @param string $project Possible source code repository path or URL.
	 * @return false|GP_Project Project on success, false otherwise.
	 */
	protected function find_by_source_url_template( $project ) {
		global $wpdb;

		$table = GP::$project->table;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$query = $wpdb->prepare( "SELECT * FROM $table WHERE source_url_template LIKE %s LIMIT 1", '%' . $wpdb->esc_like( $project ) . '%' );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return GP::$project->coerce( $wpdb->get_row( $query ) );
	}
}
