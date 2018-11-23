<?php
/**
 * Repository interface
 *
 * @since 3.0.0
 *
 * @package Required\Traduttore
 */

namespace Required\Traduttore;

/**
 * Repository interface.
 *
 * @since 3.0.0
 */
interface Repository {
	/**
	 * Mercurial repository VCS type.
	 *
	 * @since 3.0.0
	 */
	public const VCS_TYPE_HG = 'hg';

	/**
	 * Git repository VCS type.
	 *
	 * @since 3.0.0
	 */
	public const VCS_TYPE_GIT = 'git';

	/**
	 * Unknown repository type.
	 *
	 * @since 3.0.0
	 */
	public const TYPE_UNKNOWN = 'unknown';

	/**
	 * GitHub repository type.
	 *
	 * @since 3.0.0
	 */
	public const TYPE_GITHUB = 'github';

	/**
	 * GitLab repository type.
	 *
	 * @since 3.0.0
	 */
	public const TYPE_GITLAB = 'gitlab';

	/**
	 * Bitbucket repository type.
	 *
	 * @since 3.0.0
	 */
	public const TYPE_BITBUCKET = 'bitbucket';

	/**
	 * Returns the repository type.
	 *
	 * @since 3.0.0
	 *
	 * @return string Repository type.
	 */
	public function get_type(): string;

	/**
	 * Indicates whether a repository is publicly accessible or not.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether the repository is publicly accessible.
	 */
	public function is_public() : bool;

	/**
	 * Returns the project.
	 *
	 * @since 3.0.0
	 *
	 * @return Project The project.
	 */
	public function get_project() : Project;

	/**
	 * Returns the repository host name.
	 *
	 * @since 3.0.0
	 *
	 * @return string Repository host name.
	 */
	public function get_host() :? string;

	/**
	 * Returns the repository name.
	 *
	 * @since 3.0.0
	 *
	 * @return string Repository name.
	 */
	public function get_name() :? string;

	/**
	 * Returns the repository's SSH URL for cloning based on the project's source URL template.
	 *
	 * @since 3.0.0
	 *
	 * @return string SSH URL to the repository.
	 */
	public function get_ssh_url() : ?string;

	/**
	 * Returns the repository's HTTPS URL for cloning based on the project's source URL template.
	 *
	 * @since 3.0.0
	 *
	 * @return string HTTPS URL to the repository.
	 */
	public function get_https_url() : ?string;
}
