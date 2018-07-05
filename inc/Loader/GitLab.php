<?php
/**
 * GitLab loader class.
 *
 * @package Required\Traduttore
 */

namespace Required\Traduttore\Loader;

/**
 * GitLab Loader.
 *
 * @since 3.0.0
 */
class GitLab extends Git {
	/**
	 * Indicates whether a GitHub repository is publicly accessible or not.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether the repository is publicly accessible.
	 */
	protected function is_public_repository() : bool {
		$response = wp_remote_head( 'https://gitlab.com/api/v4/projects/' . rawurlencode( $this->repository->get_name() ) );

		return 200 === wp_remote_retrieve_response_code( $response );
	}
}
