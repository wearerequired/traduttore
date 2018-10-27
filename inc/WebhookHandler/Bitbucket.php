<?php
/**
 * Bitbucket webhook handler class.
 *
 * @since 3.0.0
 *
 * @package Required\Traduttore
 */

namespace Required\Traduttore\WebhookHandler;

use Required\Traduttore\ProjectLocator;
use Required\Traduttore\Repository;
use Required\Traduttore\Updater;
use WP_Error;
use WP_REST_Response;

/**
 * Bitbucket webhook handler class.
 *
 * @since 3.0.0
 *
 * @see https://confluence.atlassian.com/bitbucket/event-payloads-740262817.html
 */
class Bitbucket extends Base {
	/**
	 * Permission callback for incoming Bitbucket webhooks.
	 *
	 * @since 3.0.0
	 *
	 * @return bool True if permission is granted, false otherwise.
	 */
	public function permission_callback(): ?bool {
		$event_name = $this->request->get_header( 'x-event-key' );

		if ( ! $event_name ) {
			return false;
		}

		if ( 'repo:push' !== $event_name ) {
			return false;
		}

		if ( ! defined( 'TRADUTTORE_BITBUCKET_SYNC_SECRET' ) ) {
			return false;
		}

		$token = $this->request->get_header( 'x-hub-signature' );

		if ( ! $token ) {
			return false;
		}

		$payload_signature = 'sha256=' . hash_hmac( 'sha256', $this->request->get_body(), TRADUTTORE_BITBUCKET_SYNC_SECRET );

		return hash_equals( $token, $payload_signature );
	}

	/**
	 * Callback for incoming Bitbucket webhooks.
	 *
	 * @since 3.0.0
	 *
	 * @return WP_Error|WP_REST_Response REST response on success, error object on failure.
	 */
	public function callback() {
		$params = $this->request->get_params();

		if ( ! isset( $params['repository']['links']['html']['href'] ) ) {
			return new WP_Error( '400', 'Bad request' );
		}

		$locator = new ProjectLocator( $params['repository']['links']['html']['href'] );
		$project = $locator->get_project();

		if ( ! $project ) {
			return new WP_Error( '404', 'Could not find project for this repository' );
		}

		if ( ! $project->get_repository_vcs_type() ) {
			$project->set_repository_vcs_type( 'git' === $params['repository']['scm'] ? Repository::VCS_TYPE_GIT : Repository::VCS_TYPE_HG );
		}

		$project->set_repository_name( $params['repository']['full_name'] );
		$project->set_repository_url( $params['repository']['links']['html']['href'] );

		$ssh_url   = sprintf( 'git@bitbucket.org:%s.git', $project->get_repository_name() );
		$https_url = sprintf( 'https://bitbucket.org/%s.git', $project->get_repository_name() );

		if ( Repository::VCS_TYPE_HG === $project->get_repository_vcs_type() ) {
			$ssh_url   = sprintf( 'hg@bitbucket.org/%s', $project->get_repository_name() );
			$https_url = sprintf( 'https://bitbucket.org/%s', $project->get_repository_name() );
		}

		$project->set_repository_ssh_url( $ssh_url );
		$project->set_repository_https_url( $https_url );

		$project->set_repository_visibility( false === $params['repository']['is_private'] ? 'public' : 'private' );

		if ( ! $project->get_repository_type() ) {
			$project->set_repository_type( Repository::TYPE_BITBUCKET );
		}

		( new Updater( $project ) )->schedule_update();

		return new WP_REST_Response( [ 'result' => 'OK' ] );
	}
}
