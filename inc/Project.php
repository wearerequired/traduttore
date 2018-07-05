<?php
/**
 * Project class.
 *
 * @since 2.0.0
 *
 * @package Required\Traduttore
 */

namespace Required\Traduttore;

use GP_Project;

/**
 * GlotPress Project decorator class.
 *
 * @since 3.0.0
 */
class Project {
	/**
	 * GlotPress project.
	 *
	 * @since 3.0.0
	 *
	 * @var GP_Project Project information.
	 */
	protected $project;

	/**
	 * Project constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param GP_Project $project GlotPress project.
	 */
	public function __construct( $project ) {
		$this->project = $project;
	}

	/**
	 * Returns the actual GlotPress project.
	 *
	 * @since 3.0.0
	 *
	 * @return GP_Project GlotPress project.
	 */
	public function get_project() : GP_Project {
		return $this->project;
	}

	/**
	 * Returns the project's ID.
	 *
	 * @since 3.0.0
	 *
	 * @return int Project ID.
	 */
	public function get_id() : int {
		return (int) $this->project->id;
	}

	/**
	 * Returns the project's slug.
	 *
	 * @since 3.0.0
	 *
	 * @return string Project slug.
	 */
	public function get_slug() : string {
		return $this->project->slug;
	}

	/**
	 * Returns the project's source URL template.
	 *
	 * @since 3.0.0
	 *
	 * @return string Source URL template.
	 */
	public function get_source_url_template() :? string {
		return $this->project->source_url_template();
	}
}
