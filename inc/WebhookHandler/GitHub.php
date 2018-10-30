<?php
/**
 * GitHub webhook handler class.
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
 * GitHub webhook handler class.
 *
 * @since 3.0.0
 *
 * @see https://developer.github.com/webhooks/
 */
class GitHub extends Base {
	/**
	 * Permission callback for incoming GitHub webhooks.
	 *
	 * @since 3.0.0
	 *
	 * @return bool True if permission is granted, false otherwise.
	 */
	public function permission_callback(): ?bool {
		$event_name = $this->request->get_header( 'x-github-event' );

		if ( 'ping' === $event_name ) {
			return true;
		}

		if ( 'push' !== $event_name ) {
			return false;
		}

		if ( ! defined( 'TRADUTTORE_GITHUB_SYNC_SECRET' ) ) {
			return false;
		}

		$token = $this->request->get_header( 'x-hub-signature' );

		if ( ! $token ) {
			return false;
		}

		$payload_signature = 'sha1=' . hash_hmac( 'sha1', $this->request->get_body(), TRADUTTORE_GITHUB_SYNC_SECRET );

		return hash_equals( $token, $payload_signature );
	}

	/**
	 * Callback for incoming GitHub webhooks.
	 *
	 * @since 3.0.0
	 *
	 * @return WP_Error|WP_REST_Response REST response on success, error object on failure.
	 */
	public function callback() {
		$params     = $this->request->get_params();
		$event_name = $this->request->get_header( 'x-github-event' );

		if ( 'ping' === $event_name ) {
			return new WP_REST_Response( [ 'result' => 'OK' ] );
		}

		// We only care about the default branch but don't want to send an error still.
		if ( 'refs/heads/' . $params['repository']['default_branch'] !== $params['ref'] ) {
			return new WP_REST_Response( [ 'result' => 'Not the default branch' ] );
		}

		$locator = new ProjectLocator( $params['repository']['html_url'] );
		$project = $locator->get_project();

		if ( ! $project ) {
			return new WP_Error( '404', 'Could not find project for this repository' );
		}

		$project->set_repository_name( $params['repository']['full_name'] );
		$project->set_repository_url( $params['repository']['html_url'] );
		$project->set_repository_ssh_url( $params['repository']['ssh_url'] );
		$project->set_repository_https_url( $params['repository']['clone_url'] );
		$project->set_repository_visibility( false === $params['repository']['private'] ? 'public' : 'private' );

		if ( ! $project->get_repository_type() ) {
			$project->set_repository_type( Repository::TYPE_GITHUB );
		}

		if ( ! $project->get_repository_vcs_type() ) {
			$project->set_repository_vcs_type( Repository::VCS_TYPE_GIT );
		}

		( new Updater( $project ) )->schedule_update();

		return new WP_REST_Response( [ 'result' => 'OK' ] );
	}
}
