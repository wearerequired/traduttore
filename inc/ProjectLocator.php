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
	 * @param mixed $project Possible GlotPress project ID or path or source code repository path.
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
	 * @param mixed $project Possible GlotPress project ID or path or source code repository path.
	 *
	 * @return Project Project instance.
	 */
	protected function find_project( $project ) :? Project {
		if ( ! $project ) {
			return null;
		}

		if ( $project instanceof Project ) {
			return $project;
		}

		if ( $project instanceof GP_Project ) {
			return new Project( $project );
		}

		$found = GP::$project->by_path( $project );

		if ( ! $found && is_numeric( $project ) ) {
			$found = GP::$project->get( (int) $project );
		}

		if ( ! $found ) {
			$found = $this->find_by_repository_name( $project );
		}

		if ( ! $found ) {
			$found = $this->find_by_repository_url( $project );
		}

		if ( ! $found ) {
			$found = $this->find_by_source_url_template( $project );
		}

		return $found ? new Project( $found ) : null;
	}

	/**
	 * Finds a GlotPress project by a partially matching repository name meta data.
	 *
	 * Given a path like required-valencia, this would match
	 * a repository name like wearerequired/required-valencia.
	 *
	 * @since 3.0.0
	 *
	 * @param string $project Possible repository path or URL.
	 * @return GP_Project|null Project on success, null otherwise.
	 */
	protected function find_by_repository_name( $project ):? GP_Project {
		global $wpdb;

		$meta_key = '_traduttore_repository_name';

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$query = $wpdb->prepare( "SELECT object_id FROM `$wpdb->gp_meta` WHERE `object_type` = 'project' AND `meta_key` = %s AND `meta_value` LIKE %s LIMIT 1", $meta_key, '%' . $wpdb->esc_like( $project ) . '%' );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$result = $wpdb->get_row( $query );

		if ( ! $result ) {
			return null;
		}

		$gp_project = GP::$project->get( (int) $result->object_id );

		return $gp_project ?: null;
	}

	/**
	 * Finds a GlotPress project by a partially matching repository URL meta data.
	 *
	 * Given a path like wearerequired/required-valencia, this would match
	 * a repository URL like https://github.com/wearerequired/required-valencia.
	 *
	 * Since there can be projects with the same repository name but different providers,
	 * this can lead to false positives when not given enough information.
	 *
	 * @since 3.0.0
	 *
	 * @param string $project Possible repository path or URL.
	 * @return GP_Project|null Project on success, null otherwise.
	 */
	protected function find_by_repository_url( $project ): ?GP_Project {
		global $wpdb;

		$meta_key = '_traduttore_repository_url';

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$query = $wpdb->prepare( "SELECT object_id FROM `$wpdb->gp_meta` WHERE `object_type` = 'project' AND `meta_key` = %s AND `meta_value` LIKE %s LIMIT 1", $meta_key, '%' . $wpdb->esc_like( $project ) . '%' );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$result = $wpdb->get_row( $query );

		if ( ! $result ) {
			return null;
		}

		$gp_project = GP::$project->get( (int) $result->object_id );

		return $gp_project ?: null;
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
	 * @return GP_Project|null Project on success, null otherwise.
	 */
	protected function find_by_source_url_template( $project ): ?GP_Project {
		global $wpdb;

		$table = GP::$project->table;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$query = $wpdb->prepare( "SELECT * FROM $table WHERE source_url_template LIKE %s LIMIT 1", '%' . $wpdb->esc_like( $project ) . '%' );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$gp_project = GP::$project->coerce( $wpdb->get_row( $query ) );

		/* @var GP_Project $gp_project */
		return $gp_project ?: null;
	}
}
