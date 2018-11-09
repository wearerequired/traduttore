<?php
/**
 * SourceForge webhook handler class.
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
 * SourceForge webhook handler class.
 *
 * @since 3.0.0
 *
 * @see https://forge-allura.apache.org/p/allura/wiki/Webhooks/
 */
class SourceForge extends Base {
	/**
	 * Permission callback for incoming SourceForge webhooks.
	 *
	 * @since 3.0.0
	 *
	 * @return bool True if permission is granted, false otherwise.
	 */
	public function permission_callback(): ?bool {
		$token   = $this->request->get_header( 'x-allura-signature' );
		$params  = $this->request->get_params();
		$locator = new ProjectLocator( $params['repository']['url'] ?? null );
		$project = $locator->get_project();
		$secret  = $this->get_secret( $project );

		if ( ! $token ) {
			return false;
		}

		$payload_signature = 'sha1=' . hash_hmac( 'sha1', $this->request->get_body(), $secret );

		return hash_equals( $token, $payload_signature );
	}

	/**
	 * Callback for incoming SourceForge webhooks.
	 *
	 * @since 3.0.0
	 *
	 * @return WP_Error|WP_REST_Response REST response on success, error object on failure.
	 */
	public function callback() {
		$params = $this->request->get_params();

		// We only care about the default branch (we have to assume it's master) but don't want to send an error still.
		if ( 'refs/heads/master' !== $params['ref'] ) {
			return new WP_REST_Response( [ 'result' => 'Not the default branch' ] );
		}

		$locator = new ProjectLocator( $params['repository']['url'] );
		$project = $locator->get_project();

		if ( ! $project ) {
			return new WP_Error( '404', 'Could not find project for this repository' );
		}

		$project->set_repository_name( $params['repository']['full_name'] );
		$project->set_repository_url( $params['repository']['url'] );

		if ( ! $project->get_repository_type() ) {
			$project->set_repository_type( Repository::TYPE_SOURCEFORGE );
		}

		( new Updater( $project ) )->schedule_update();

		return new WP_REST_Response( [ 'result' => 'OK' ] );
	}
}
