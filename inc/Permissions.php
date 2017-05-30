<?php
/**
 * Permission class.
 *
 * @since 1.0.0
 */

namespace Required\Traduttore;

use GP;

/**
 * Class used to customize permissions
 *
 * @since 1.0.0
 */
class Permissions {

	/**
	 * Registers actions and filters.
	 *
	 * @since 1.0.0
	 *
	 * @param \GP_Project[] $projects Unfiltered projects.
	 * @return \GP_Project[] Projects.
	 */
	public function filter_projects( $projects ) {
		if ( current_user_can( 'manage_options' ) ) {
			return $projects;
		}

		$project_ids = $this->get_allowed_projects( get_current_user_id() );

		foreach ( $projects as $key => $project ) {
			if ( in_array( $project->id, $project_ids, true ) ) {
				continue;
			}

			$parent_project_id = $project->parent_project_id;

			if ( in_array( $parent_project_id, $project_ids, true ) ) {
				continue ;
			}

			while ( $parent_project_id ) {
				$parent_project = GP::$project->get( $parent_project_id );
				$parent_project_id = $parent_project->parent_project_id;

				if ( in_array( $parent_project_id, $project_ids, true ) ) {
					continue 2;
				}
			}

			unset( $projects[ $key ] );
		}

		return $projects;
	}

	/**
	 * Retrieve the list of allowed project IDs.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id User ID.
	 * @return array List of project ID.
	 */
	private function get_allowed_projects( $user_id ) {
		$project_ids = [];

		$permissions = GP::$permission->find_many_no_map( [ 'user_id' => $user_id, 'action' => 'approve' ] );

		foreach ( $permissions as $permission ) {
			$object_id = GP::$validator_permission->project_id_locale_slug_set_slug( $permission->object_id );

			// Skip admin permissions.
			if ( ! isset( $object_id[0] ) ) {
				continue;
			}

			$project_ids[] = $object_id[0];
		}

		return $project_ids;
	}

}
