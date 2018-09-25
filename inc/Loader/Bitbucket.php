<?php
/**
 * Bitbucket loader class.
 *
 * @package Required\Traduttore
 */

namespace Required\Traduttore\Loader;

/**
 * Bitbucket Loader.
 *
 * @since 3.0.0
 */
class Bitbucket extends Git {
	/**
	 * Indicates whether a Bitbucket repository is publicly accessible or not.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether the repository is publicly accessible.
	 */
	protected function is_public_repository() : bool {
		$response = wp_remote_head( 'https://api.bitbucket.org/2.0/repositories/' . rawurlencode( $this->repository->get_name() ) );

		return 200 === wp_remote_retrieve_response_code( $response );
	}
}
