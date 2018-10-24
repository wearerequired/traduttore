<?php
/**
 * GitHub webhook handler class.
 *
 * @package Required\Traduttore
 */

namespace Required\Traduttore\WebhookHandler;

use Required\Traduttore\ProjectLocator;
use Required\Traduttore\Updater;
use WP_Error;
use WP_REST_Response;

class GitHub extends Base {
	/**
	 * @inheritdoc
	 */
	public function permission_callback(): ?bool {
		$event_name = $this->request->get_header( 'x-github-event' );

		if ( ! $event_name ) {
			return false;
		}

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
	 * @inheritdoc
	 */
	public function callback() {
		$params     = $this->request->get_params();
		$event_name = $this->request->get_header( 'x-github-event' );

		if ( 'ping' === $event_name ) {
			return new WP_REST_Response( [ 'result' => 'OK' ] );
		}

		if ( ! isset( $params['repository']['html_url'], $params['ref'] ) ) {
			return new WP_Error( '400', 'Bad request' );
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

		( new Updater( $project ) )->schedule_update();

		return new WP_REST_Response( [ 'result' => 'OK' ] );
	}
}
