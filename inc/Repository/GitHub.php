<?php
/**
 * GitHub repository class.
 *
 * @since 3.0.0
 *
 * @package Required\Traduttore
 */

namespace Required\Traduttore\Repository;

use Required\Traduttore\Repository;

/**
 * GitHub repository class.
 *
 * @since 3.0.0
 */
class GitHub extends Base {
	/**
	 * @inheritdoc
	 */
	public function get_type() : string {
		return Repository::TYPE_GITHUB;
	}

	/**
	 * @inheritdoc
	 */
	public function get_name(): string {
		$url   = $this->project->get_source_url_template();
		$parts = explode( '/blob/', wp_parse_url( $url, PHP_URL_PATH ) );
		$path  = array_shift( $parts );

		return ltrim( $path, '/' );
	}

	/**
	 * Indicates whether a GitHub repository is publicly accessible or not.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether the repository is publicly accessible.
	 */
	public function is_public() : bool {
		$response = wp_remote_head( 'https://api.github.com/repos/' . rawurlencode( $this->get_name() ) );

		return 200 === wp_remote_retrieve_response_code( $response );
	}
}
