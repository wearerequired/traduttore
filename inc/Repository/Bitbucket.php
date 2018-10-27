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
class Bitbucket extends Base {
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
	 * Returns the repository host name.
	 *
	 * @since 3.0.0
	 *
	 * @return string Repository host name.
	 */
	public function get_host(): string {
		return 'bitbucket.org';
	}

	/**
	 * Returns the repository name.
	 *
	 * If the name is not stored in the database,
	 * it tries to determine it from the repository URL and the project slug.
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
			$response = wp_remote_head( self::API_BASE . '/repositories/' . $this->get_name() );

			$visibility = 200 === wp_remote_retrieve_response_code( $response ) ? 'public' : 'private';

			$this->project->set_repository_visibility( $visibility );
		}

		return 'public' === $visibility;
	}

	/**
	 * Returns the repository's SSH URL for cloning based on the project's source URL template.
	 *
	 * @since 3.0.0
	 *
	 * @return string SSH URL to the repository, e.g. git@github.com:wearerequired/traduttore.git.
	 */
	public function get_ssh_url() : ?string {
		if ( Repository::VCS_TYPE_HG === $this->project->get_repository_vcs_type() ) {
			$ssh_url = $this->project->get_repository_ssh_url();

			if ( $ssh_url ) {
				return $ssh_url;
			}

			if ( $this->get_host() && $this->get_name() ) {
				return sprintf( 'hg@%1$s/%2$s', $this->get_host(), $this->get_name() );
			}
		}

		return parent::get_ssh_url();
	}

	/**
	 * Returns the repository's HTTPS URL for cloning based on the project's source URL template.
	 *
	 * @since 3.0.0
	 *
	 * @return string HTTPS URL to the repository, e.g. https://github.com/wearerequired/traduttore.git.
	 */
	public function get_https_url() : ?string {
		if ( Repository::VCS_TYPE_HG === $this->project->get_repository_vcs_type() ) {
			$https_url = $this->project->get_repository_https_url();

			if ( ! $https_url && $this->get_host() ) {
				$https_url = sprintf( 'https://%1$s/%2$s', $this->get_host(), $this->get_name() );
			}

			if ( ! $https_url ) {
				return null;
			}

			/**
			 * Filters the credentials to be used for connecting to a Mercurial repository via HTTPS.
			 *
			 * @since 3.0.0
			 *
			 * @param string     $credentials HTTP authentication credentials in the form username:password. Default empty string.
			 * @param Repository $repository  The current repository.
			 */
			$credentials = apply_filters( 'traduttore.hg_https_credentials', '', $this );

			if ( ! empty( $credentials ) ) {
				$https_url = str_replace( 'https://', 'https://' . $credentials . '@', $https_url );
			}

			return $https_url;
		}

		return parent::get_https_url();
	}
}
