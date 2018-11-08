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
	 * Project repository type meta key.
	 *
	 * @since 3.0.0
	 *
	 * @var string Project repository type meta key.
	 */
	protected const REPOSITORY_TYPE_KEY = '_traduttore_repository_type';

	/**
	 * Project repository URL meta key.
	 *
	 * @since 3.0.0
	 *
	 * @var string Project repository URL meta key.
	 */
	protected const REPOSITORY_URL_KEY = '_traduttore_repository_url';

	/**
	 * Project repository name meta key.
	 *
	 * @since 3.0.0
	 *
	 * @var string Project repository name meta key.
	 */
	protected const REPOSITORY_NAME_KEY = '_traduttore_repository_name';

	/**
	 * Project repository visibility meta key.
	 *
	 * @since 3.0.0
	 *
	 * @var string Project repository visibility meta key.
	 */
	protected const REPOSITORY_VISIBILITY_KEY = '_traduttore_repository_visibility';

	/**
	 * Project repository VCS type key.
	 *
	 * @since 3.0.0
	 *
	 * @var string Project repository VCS type key.
	 */
	protected const REPOSITORY_VCS_TYPE_KEY = '_traduttore_repository_vcs_type';

	/**
	 * Project repository SSH URL meta key.
	 *
	 * @since 3.0.0
	 *
	 * @var string Project repository SSH URL meta key.
	 */
	protected const REPOSITORY_SSH_URL_KEY = '_traduttore_repository_ssh_url';

	/**
	 * Project repository HTTPS URL meta key.
	 *
	 * @since 3.0.0
	 *
	 * @var string Project repository HTTPS URL meta key.
	 */
	protected const REPOSITORY_HTTPS_URL_KEY = '_traduttore_repository_https_url';

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
	public function get_project(): GP_Project {
		return $this->project;
	}

	/**
	 * Returns the project's ID.
	 *
	 * @since 3.0.0
	 *
	 * @return int Project ID.
	 */
	public function get_id(): int {
		return (int) $this->project->id;
	}

	/**
	 * Returns the project's name.
	 *
	 * @since 3.0.0
	 *
	 * @return string Project name.
	 */
	public function get_name(): string {
		return $this->project->name;
	}

	/**
	 * Returns the project's slug.
	 *
	 * @since 3.0.0
	 *
	 * @return string Project slug.
	 */
	public function get_slug(): string {
		return $this->project->slug;
	}

	/**
	 * Returns the project's source URL template.
	 *
	 * @since 3.0.0
	 *
	 * @return string|null Source URL template if set, null otherwise.
	 */
	public function get_source_url_template(): ?string {
		$source_url_template = $this->project->source_url_template();

		return $source_url_template ?: null;
	}

	/**
	 * Returns the project's repository type (github, gitlab, etc.)
	 *
	 * @since 3.0.0
	 *
	 * @return string|null Repository type if stored, null otherwise.
	 */
	public function get_repository_type(): ?string {
		$type = gp_get_meta( 'project', $this->project->id, static::REPOSITORY_TYPE_KEY );

		return $type ?: null;
	}

	/**
	 * Updates the project's repository type.
	 *
	 * @since 3.0.0
	 *
	 * @param string $type The new repository type.
	 * @return bool Whether the data was successfully saved or not.
	 */
	public function set_repository_type( string $type ): bool {
		return (bool) gp_update_meta( $this->project->id, static::REPOSITORY_TYPE_KEY, $type, 'project' );
	}

	/**
	 * Returns the project's repository VSC type (git, hg, svn, etc.)
	 *
	 * @since 3.0.0
	 *
	 * @return null|string VCS type if stored, null otherwise.
	 */
	public function get_repository_vcs_type(): ?string {
		$type = gp_get_meta( 'project', $this->project->id, static::REPOSITORY_VCS_TYPE_KEY );

		return $type ?: null;
	}

	/**
	 * Updates the project's repository VCS type.
	 *
	 * @since 3.0.0
	 *
	 * @param string $type THe new repository VCS type.
	 * @return bool Whether the data was successfully saved or not.
	 */
	public function set_repository_vcs_type( string $type ): bool {
		return (bool) gp_update_meta( $this->project->id, static::REPOSITORY_VCS_TYPE_KEY, $type, 'project' );
	}

	/**
	 * Returns the project's repository URL.
	 *
	 * @since 3.0.0
	 *
	 * @return null|string Repository URL if stored, null otherwise.
	 */
	public function get_repository_url(): ?string {
		$url = gp_get_meta( 'project', $this->project->id, static::REPOSITORY_URL_KEY );

		return $url ?: null;
	}

	/**
	 * Updates the project's repository URL.
	 *
	 * @since 3.0.0
	 *
	 * @param string $url The new URL.
	 * @return bool Whether the data was successfully saved or not.
	 */
	public function set_repository_url( string $url ): bool {
		return (bool) gp_update_meta( $this->project->id, static::REPOSITORY_URL_KEY, $url, 'project' );
	}

	/**
	 * Returns the project's repository name.
	 *
	 * @since 3.0.0
	 *
	 * @return null|string Repository name if stored, null otherwise.
	 */
	public function get_repository_name(): ?string {
		$name = gp_get_meta( 'project', $this->project->id, static::REPOSITORY_NAME_KEY );

		return $name ?: null;
	}

	/**
	 * Updates the project's repository name.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name The new name.
	 * @return bool Whether the data was successfully saved or not.
	 */
	public function set_repository_name( string $name ): bool {
		return (bool) gp_update_meta( $this->project->id, static::REPOSITORY_NAME_KEY, $name, 'project' );
	}

	/**
	 * Returns the project's repository visibility.
	 *
	 * @since 3.0.0
	 *
	 * @return null|string Repository visibility if stored, null otherwise.
	 */
	public function get_repository_visibility(): ?string {
		$visibility = gp_get_meta( 'project', $this->project->id, static::REPOSITORY_VISIBILITY_KEY );

		return $visibility ?: null;
	}

	/**
	 * Updates the project's repository visibility.
	 *
	 * @param string $visibility The new visibility.
	 * @return bool Whether the data was successfully saved or not.
	 */
	public function set_repository_visibility( string $visibility ): bool {
		return (bool) gp_update_meta( $this->project->id, static::REPOSITORY_VISIBILITY_KEY, $visibility, 'project' );
	}

	/**
	 * Returns the project's repository SSH URL.
	 *
	 * @since 3.0.0
	 *
	 * @return null|string Repository SSH URL if stored, null otherwise.
	 */
	public function get_repository_ssh_url(): ?string {
		$url = gp_get_meta( 'project', $this->project->id, static::REPOSITORY_SSH_URL_KEY );

		return $url ?: null;
	}

	/**
	 * Updates the project's repository SSH URL.
	 *
	 * @since 3.0.0
	 *
	 * @param string $url The new URL.
	 * @return bool Whether the data was successfully saved or not.
	 */
	public function set_repository_ssh_url( string $url ): bool {
		return (bool) gp_update_meta( $this->project->id, static::REPOSITORY_SSH_URL_KEY, $url, 'project' );
	}

	/**
	 * Returns the project's repository HTTPS URL.
	 *
	 * @since 3.0.0
	 *
	 * @return null|string Repository HTTPS URL if stored, null otherwise.
	 */
	public function get_repository_https_url(): ?string {
		$url = gp_get_meta( 'project', $this->project->id, static::REPOSITORY_HTTPS_URL_KEY );

		return $url ?: null;
	}

	/**
	 * Updates the project's repository HTTPS URL.
	 *
	 * @since 3.0.0
	 *
	 * @param string $url The new URL.
	 * @return bool Whether the data was successfully saved or not.
	 */
	public function set_repository_https_url( string $url ): bool {
		return (bool) gp_update_meta( $this->project->id, static::REPOSITORY_HTTPS_URL_KEY, $url, 'project' );
	}
}
