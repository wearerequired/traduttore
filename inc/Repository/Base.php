<?php
/**
 * Git repository class.
 *
 * @since 3.0.0
 *
 * @package Required\Traduttore\Repository
 */

namespace Required\Traduttore\Repository;

use Required\Traduttore\Project;
use Required\Traduttore\Repository;

/**
 * Base repository class.
 *
 * @since 3.0.0
 */
class Base implements Repository {
	/**
	 * GlotPress project.
	 *
	 * @since 3.0.0
	 *
	 * @var Project Project information.
	 */
	protected $project;

	/**
	 * Loader constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param Project $project Project information.
	 */
	public function __construct( Project $project ) {
		$this->project = $project;
	}

	/**
	 * @inheritdoc
	 */
	public function get_type() : string {
		return Repository::TYPE_UNKNOWN;
	}

	/**
	 * @inheritdoc
	 */
	public function is_public() : bool {
		return 'public' === $this->project->get_repository_visibility();
	}

	/**
	 * @inheritdoc
	 */
	public function get_project(): Project {
		return $this->project;
	}

	/**
	 * @inheritdoc
	 */
	public function get_host(): ?string {
		$url = $this->project->get_source_url_template();

		return wp_parse_url( $url, PHP_URL_HOST );
	}

	/**
	 * @inheritdoc
	 */
	public function get_name(): string {
		return $this->project->get_slug();
	}
}
