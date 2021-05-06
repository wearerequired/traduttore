<?php
/**
 * GitLab webhook handler
 *
 * @since 3.0.0
 */

namespace Required\Traduttore\WebhookHandler;

use Required\Traduttore\ProjectLocator;
use Required\Traduttore\Repository;
use Required\Traduttore\Updater;
use WP_REST_Response;

/**
 * GitLab webhook handler class.
 *
 * @since 3.0.0
 *
 * @see https://docs.gitlab.com/ee/user/project/integrations/webhooks.html
 */
class GitLab extends Base {
	/**
	 * Permission callback for incoming GitLab webhooks.
	 *
	 * @since 3.0.0
	 *
	 * @return bool True if permission is granted, false otherwise.
	 */
	public function permission_callback(): ?bool {
		$event_name = $this->request->get_header( 'x-gitlab-event' );

		if ( 'Push Hook' !== $event_name ) {
			return false;
		}

		$token = $this->request->get_header( 'x-gitlab-token' );

		if ( ! $token ) {
			return false;
		}

		$params  = $this->request->get_params();
		$locator = new ProjectLocator( $params['project']['homepage'] ?? null );
		$project = $locator->get_project();

		$secret = $this->get_secret( $project );

		return hash_equals( $token, $secret );
	}

	/**
	 * Callback for incoming GitLab webhooks.
	 *
	 * @since 3.0.0
	 *
	 * @return \WP_Error|\WP_REST_Response REST response on success, error object on failure.
	 */
	public function callback() {
		$params = $this->request->get_params();

		// We only care about the default branch but don't want to send an error still.
		if ( 'refs/heads/' . $params['project']['default_branch'] !== $params['ref'] ) {
			return new WP_REST_Response( [ 'result' => 'Not the default branch' ] );
		}

		$locator = new ProjectLocator( $params['project']['homepage'] );
		$project = $locator->get_project();

		if ( ! $project ) {
			return new \WP_Error( '404', 'Could not find project for this repository' );
		}

		$project->set_repository_name( $params['project']['path_with_namespace'] );
		$project->set_repository_url( $params['project']['homepage'] );
		$project->set_repository_ssh_url( $params['project']['ssh_url'] );
		$project->set_repository_https_url( $params['project']['http_url'] );
		$project->set_repository_visibility( 0 === $params['project']['visibility_level'] ? 'public' : 'private' );

		if ( ! $project->get_repository_type() ) {
			$project->set_repository_type( Repository::TYPE_GITLAB );
		}

		if ( ! $project->get_repository_vcs_type() ) {
			$project->set_repository_vcs_type( Repository::VCS_TYPE_GIT );
		}

		( new Updater( $project ) )->schedule_update();

		return new WP_REST_Response( [ 'result' => 'OK' ] );
	}
}
