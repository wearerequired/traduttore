<?php
/**
 * Bitbucket repository class.
 *
 * @since 3.0.0
 *
 * @package Required\Traduttore
 */

namespace Required\Traduttore\Repository;

use Required\Traduttore\Repository;

/**
 * Bitbucket repository class.
 *
 * @since 3.0.0
 */
class Bitbucket extends Git {
	/**
	 * Bitbucket API base URL.
	 *
	 * @since 3.0.0
	 */
	public const API_BASE = 'https://api.bitbucket.org/2.0';

	/**
	 * Returns the repository type.
	 *
	 * @since 3.0.0
	 *
	 * @return string Repository type.
	 */
	public function get_type() : string {
		return Repository::TYPE_BITBUCKET;
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

				if ( false !== strpos( $url, '/src/' ) ) {
					$parts = explode( '/src/', $url );
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
	 * Indicates whether a Bitbucket repository is publicly accessible or not.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether the repository is publicly accessible.
	 */
	public function is_public() : bool {
		$visibility = $this->project->get_repository_visibility();

		if ( ! $visibility ) {
			$response = wp_remote_head( self::API_BASE . '/repositories/' . rawurlencode( $this->get_name() ) );

			$visibility = 200 === wp_remote_retrieve_response_code( $response ) ? 'public' : 'private';

			$this->project->set_repository_visibility( $visibility );
		}

		return 'public' === $visibility;
	}
}
