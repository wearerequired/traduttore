<?php
/**
 * Repository class.
 *
 * @since 2.0.0
 *
 * @package Required\Traduttore
 */

namespace Required\Traduttore;

/**
 * Repository class.
 *
 * @since 3.0.0
 */
class Repository {
	/**
	 * Unknown repository type.
	 *
	 * @since 3.0.0
	 */
	public const TYPE_UNKNOWN = 0;

	/**
	 * GitHub repository type.
	 *
	 * @since 3.0.0
	 */
	public const TYPE_GITHUB = 1;

	/**
	 * GitLab repository type.
	 *
	 * @since 3.0.0
	 */
	public const TYPE_GITLAB = 2;

	/**
	 * GlotPress project.
	 *
	 * @since 3.0.0
	 *
	 * @var Project Project information.
	 */
	protected $project;

	/**
	 * Repository host name.
	 *
	 * @since 3.0.0
	 *
	 * @var string Repository host name.
	 */
	protected $host;

	/**
	 * Repository type.
	 *
	 * @since 3.0.0
	 *
	 * @var string Repository type.
	 */
	protected $type = self::TYPE_UNKNOWN;

	/**
	 * Repository name.
	 *
	 * @since 3.0.0
	 *
	 * @var string Repository name.
	 */
	protected $name;

	/**
	 * Loader constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param Project $project Project information.
	 */
	public function __construct( Project $project ) {
		$this->project = $project;

		$this->host = $this->set_host();
		$this->type = $this->set_type();
		$this->name = $this->set_name();
	}

	/**
	 * Returns the project.
	 *
	 * @since 3.0.0
	 *
	 * @return Project The project.
	 */
	public function get_project() : Project {
		return $this->project;
	}

	/**
	 * Returns the repository host name.
	 *
	 * @since 3.0.0
	 *
	 * @return string Repository host name.
	 */
	public function get_host() :? string {
		return $this->host;
	}

	/**
	 * Returns the repository type.
	 *
	 * @since 3.0.0
	 *
	 * @return int Repository type.
	 */
	public function get_type() : int {
		return $this->type;
	}

	/**
	 * Returns the repository name.
	 *
	 * @since 3.0.0
	 *
	 * @return string Repository name.
	 */
	public function get_name() :? string {
		return $this->name;
	}

	/**
	 * Returns the repository slug.
	 *
	 * @since 3.0.0
	 *
	 * @return string Repository slug.
	 */
	public function get_slug() : string {
		return $this->project->get_slug();
	}

	/**
	 * Sets the repository host name.
	 *
	 * @since 3.0.0
	 *
	 * @return string Repository host name.
	 */
	protected function set_host() :? string {
		$url = $this->project->get_source_url_template();

		return wp_parse_url( $url, PHP_URL_HOST );
	}

	/**
	 * Sets the repository type.
	 *
	 * @since 3.0.0
	 *
	 * @return string Repository type.
	 */
	protected function set_type() : string {
		if ( 'github.com' === $this->host ) {
			return self::TYPE_GITHUB;
		}

		if ( 'gitlab.com' === $this->host ) {
			return self::TYPE_GITLAB;
		}

		return self::TYPE_UNKNOWN;
	}

	/**
	 * Sets the repository name.
	 *
	 * @return string Repository name.
	 */
	protected function set_name() :? string {
		switch ( $this->type ) {
			case self::TYPE_GITHUB:
			case self::TYPE_GITLAB:
				$url   = $this->project->get_source_url_template();
				$parts = explode( '/blob/', wp_parse_url( $url, PHP_URL_PATH ) );
				$path  = array_shift( $parts );

				return ltrim( $path, '/' );
		}

		return null;
	}
}
