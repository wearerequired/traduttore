<?php
/**
 * GitLab webhook handler class.
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
 * GitLab webhook handler class.
 *
 * @since 3.0.0
 *
 * @see https://docs.gitlab.com/ee/user/project/integrations/webhooks.html
 */
class GitLab extends Base {
	/**
	 * @inheritdoc
	 */
	public function permission_callback(): ?bool {
		$event_name = $this->request->get_header( 'x-gitlab-event' );

		if ( ! $event_name ) {
			return false;
		}

		if ( 'Push Hook' !== $event_name ) {
			return false;
		}

		if ( ! defined( 'TRADUTTORE_GITLAB_SYNC_SECRET' ) ) {
			return false;
		}

		$token = $this->request->get_header( 'x-gitlab-token' );

		if ( ! $token ) {
			return false;
		}

		return hash_equals( $token, TRADUTTORE_GITLAB_SYNC_SECRET );
	}

	/**
	 * @inheritdoc
	 */
	public function callback() {
		$params = $this->request->get_params();

		if ( ! isset( $params['repository']['git_http_url'], $params['ref'] ) ) {
			return new WP_Error( '400', 'Bad request' );
		}

		// We only care about the default branch but don't want to send an error still.
		if ( 'refs/heads/' . $params['project']['default_branch'] !== $params['ref'] ) {
			return new WP_REST_Response( [ 'result' => 'Not the default branch' ] );
		}

		$locator = new ProjectLocator( $params['repository']['git_http_url'] );
		$project = $locator->get_project();

		if ( ! $project ) {
			return new WP_Error( '404', 'Could not find project for this repository' );
		}

		$project->set_repository_url( $params['repository']['git_http_url'] );

		if ( ! $project->get_repository_type() ) {
			$project->set_repository_type( Repository::TYPE_GITLAB );
		}

		if ( ! $project->get_repository_vcs_type() ) {
			$project->set_repository_vcs_type( 'git' );
		}

		( new Updater( $project ) )->schedule_update();

		return new WP_REST_Response( [ 'result' => 'OK' ] );
	}
}
