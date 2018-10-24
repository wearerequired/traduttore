<?php
/**
 * Bitbucket webhook handler class.
 *
 * @package Required\Traduttore
 */

namespace Required\Traduttore\WebhookHandler;

use Required\Traduttore\ProjectLocator;
use Required\Traduttore\Updater;
use WP_Error;
use WP_REST_Response;

/**
 * Bitbucket webhook handler class.
 *
 * @see https://confluence.atlassian.com/bitbucket/event-payloads-740262817.html
 */
class Bitbucket extends Base {
	/**
	 * @inheritdoc
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
	 * @inheritdoc
	 */
	public function callback() {
		$params     = $this->request->get_params();

		if ( ! isset( $params['repository']['links']['html']['href'] ) ) {
			return new WP_Error( '400', 'Bad request' );
		}

		$locator = new ProjectLocator( $params['repository']['links']['html']['href'] );
		$project = $locator->get_project();

		if ( ! $project ) {
			return new WP_Error( '404', 'Could not find project for this repository' );
		}

		( new Updater( $project ) )->schedule_update();

		return new WP_REST_Response( [ 'result' => 'OK' ] );
	}
}
