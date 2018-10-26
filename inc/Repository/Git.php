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
 * Git repository class.
 *
 * @since 3.0.0
 */
class Git extends Base {
	/**
	 * Returns the repository's HTTPS URL for cloning based on the project's source URL template.
	 *
	 * @since 3.0.0
	 *
	 * @return string HTTPS URL to the repository, e.g. https://github.com/wearerequired/traduttore.git.
	 */
	protected function get_https_url() : string {
		$https_url = $this->project->get_repository_https_url();

		if ( ! $https_url ) {
			$https_url = sprintf( 'https://%1$s/%2$s.git', $this->get_host(), $this->get_name() );
		}

		/**
		 * Filters the credentials to be used for connecting to a Git repository via HTTPS.
		 *
		 * @since 3.0.0
		 *
		 * @param string     $credentials HTTP authentication credentials in the form username:password. Default empty string.
		 * @param Repository $repository  The current repository.
		 */
		$credentials = apply_filters( 'traduttore.git_https_credentials', '', $this );

		if ( ! empty( $credentials ) ) {
			$https_url = str_replace( 'https://', 'https://' . $credentials, $https_url );
		}

		return $https_url;
	}
}
