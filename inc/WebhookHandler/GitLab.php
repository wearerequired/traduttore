<?php
/**
 * GitLab webhook handler
 *
 * @since 3.0.0
 */

namespace Required\Traduttore\WebhookHandler;

use Required\Traduttore\Project;
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
	public function permission_callback(): bool {
		$event_name = $this->request->get_header( 'x-gitlab-event' );

		if ( 'Push Hook' !== $event_name ) {
			return false;
		}

		$token = $this->request->get_header( 'x-gitlab-token' );

		if ( ! $token ) {
			return false;
		}

		/**
		 * Request params.
		 *
		 * @var array{project: array{homepage?: string}} $params
		 */
		$params     = $this->request->get_params();
		$repository = $params['project']['homepage'] ?? null;

		if ( ! $repository ) {
			return false;
		}

		$locator = new ProjectLocator( $repository );
		$project = $locator->get_project();

		$secret = $this->get_secret( $project );

		if ( ! $secret ) {
			return false;
		}

		return hash_equals( $token, $secret );
	}

	/**
	 * Callback for incoming GitLab webhooks.
	 *
	 * @since 3.0.0
	 *
	 * @return \WP_Error|\WP_REST_Response REST response on success, error object on failure.
	 */
	public function callback(): \WP_Error|\WP_REST_Response {
		/**
		 * Request params.
		 *
		 * @var array{project: array{default_branch: string, homepage: string, path_with_namespace: string, ssh_url: string, http_url: string, visibility_level: int}, ref: string} $params
		 */
		$params = $this->request->get_params();

		if ( ! isset( $params['project']['default_branch'] )
			| ! isset( $params['project']['homepage'] )
			| ! isset( $params['ref'] )
		) {
			return new \WP_Error( '400', 'Request incomplete', [ 'status' => 400 ] );
		}

		$project = $this->get_validated_project( $params['project']['homepage'], $params['project']['default_branch'], $params['ref'] );

		if ( ! $project instanceof Project ) {
			return $project;
		}

		$project->set_repository_name( $params['project']['path_with_namespace'] );
		$project->set_repository_url( $params['project']['homepage'] );
		$project->set_repository_ssh_url( $params['project']['ssh_url'] );
		$project->set_repository_https_url( $params['project']['http_url'] );
		$project->set_repository_visibility( 20 === $params['project']['visibility_level'] ? 'public' : 'private' );

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
