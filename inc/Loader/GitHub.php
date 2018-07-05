<?php
/**
 * GitHub loader class.
 *
 * @package Required\Traduttore
 */

namespace Required\Traduttore\Loader;

/**
 * GitHub Loader.
 *
 * @since 3.0.0
 */
class GitHub extends Git {
	/**
	 * Indicates whether a GitHub repository is publicly accessible or not.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether the repository is publicly accessible.
	 */
	protected function is_public_repository() : bool {
		$response = wp_remote_head( 'https://api.github.com/repos/' . rawurlencode( $this->repository->get_name() ) );

		return 200 === wp_remote_retrieve_response_code( $response );
	}
}
