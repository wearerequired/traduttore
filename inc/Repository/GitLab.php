<?php
/**
 * GitLab repository class.
 *
 * @since 3.0.0
 *
 * @package Required\Traduttore
 */

namespace Required\Traduttore\Repository;

use Required\Traduttore\Repository;

/**
 * GitLab repository class.
 *
 * @since 3.0.0
 */
class GitLab extends Git {
	/**
	 * GitLab API base URL.
	 *
	 * @since 3.0.0
	 */
	public const API_BASE = 'https://gitlab.com/api/v4';

	/**
	 * Returns the repository type.
	 *
	 * @since 3.0.0
	 *
	 * @return string Repository type.
	 */
	public function get_type() : string {
		return Repository::TYPE_GITLAB;
	}

	/**
	 * Returns the repository name.
	 *
	 * If the name is not stored in the database,
	 * it tries to determine it from the repository URL and the project path.
	 *
	 * @since 3.0.0
	 *
	 * @return string Repository name.
	 */
	public function get_name(): string {
		$name = $this->project->get_repository_name();

		if ( ! $name ) {
			$url = $this->project->get_repository_url();

			if ( ! $url ) {
				$url = $this->project->get_source_url_template();

				if ( false !== strpos( $url, '/blob/' ) ) {
					$parts = explode( '/blob/', $url );
					$url   = array_shift( $parts );
				}
			}

			if ( $url ) {
				$path = wp_parse_url( $url, PHP_URL_PATH );
				$name = trim( $path, '/' );
			}
		}

		return $name ?: $this->project->get_project()->slug;
	}

	/**
	 * Indicates whether a GitLab repository is publicly accessible or not.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether the repository is publicly accessible.
	 */
	public function is_public() : bool {
		$visibility = $this->project->get_repository_visibility();

		if ( ! $visibility ) {
			$response = wp_remote_head( self::API_BASE . '/projects/' . rawurlencode( $this->get_name() ) );

			$visibility = 200 === wp_remote_retrieve_response_code( $response ) ? 'public' : 'private';

			$this->project->set_repository_visibility( $visibility );
		}

		return 'public' === $visibility;
	}
}
