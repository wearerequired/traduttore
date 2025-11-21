<?php
/**
 * Base webhook handler implementation
 *
 * @since 3.0.0
 */

namespace Required\Traduttore\WebhookHandler;

use Required\Traduttore\Project;
use Required\Traduttore\ProjectLocator;
use Required\Traduttore\WebhookHandler;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Base webhook handler class.
 *
 * @since 3.0.0
 */
abstract class Base implements WebhookHandler {
	/**
	 * The current REST request.
	 *
	 * @since 3.0.0
	 *
	 * @var \WP_REST_Request The current REST request.
	 *
	 * @phpstan-var \WP_REST_Request<array{}>
	 */
	protected WP_REST_Request $request;

	/**
	 * Class constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @phpstan-param \WP_REST_Request<array{}> $request
	 */
	public function __construct( WP_REST_Request $request ) {
		$this->request = $request;
	}

	/**
	 * Returns the webhook sync secret.
	 *
	 * @since 3.0.0
	 *
	 * @param \Required\Traduttore\Project|null $project The current project if found.
	 * @return string Secret if set, null otherwise.
	 */
	protected function get_secret( ?Project $project = null ): ?string {
		$secret = null;

		switch ( static::class ) {
			case Bitbucket::class:
				if ( \defined( 'TRADUTTORE_BITBUCKET_SYNC_SECRET' ) ) {
					$secret = TRADUTTORE_BITBUCKET_SYNC_SECRET;
				}
				break;
			case GitHub::class:
				if ( \defined( 'TRADUTTORE_GITHUB_SYNC_SECRET' ) ) {
					$secret = TRADUTTORE_GITHUB_SYNC_SECRET;
				}
				break;
			case GitLab::class:
				if ( \defined( 'TRADUTTORE_GITLAB_SYNC_SECRET' ) ) {
					$secret = TRADUTTORE_GITLAB_SYNC_SECRET;
				}
				break;
		}

		$project_secret = $project ? $project->get_repository_webhook_secret() : null;

		$secret = $project_secret ?? $secret;

		/**
		 * Filters the sync secret for an incoming webhook request.
		 *
		 * @since 3.0.0
		 *
		 * @param string|null                         $secret  Webhook sync secret.
		 * @param \Required\Traduttore\WebhookHandler $handler The current webhook handler instance.
		 * @param \Required\Traduttore\Project|null   $project The current project if passed through.
		 */
		return apply_filters( 'traduttore.webhook_secret', $secret, $this, $project );
	}

	/**
	 * Return a valid project or errors. Allows to customize the pulled branch.
	 *
	 * @param string $repository Metadata to find a GlotPress project.
	 * @param string $default_branch Name of the repository's default branch.
	 * @param string $ref Name of the received branch through the webhook.
	 * @return \Required\Traduttore\Project|\WP_REST_Response|\WP_Error
	 */
	protected function resolve_project( string $repository, string $default_branch = '', string $ref = '' ): Project|WP_REST_Response|\WP_Error {
		$locator = new ProjectLocator( $repository );
		$project = $locator->get_project();

		if ( ! $project ) {
			return new \WP_Error( '404', 'Could not find project for this repository', [ 'status' => 404 ] );
		}

		if ( empty( $default_branch ) || empty( $ref ) ) {
			return $project;
		}

		$branch = 'refs/heads/' . (string) apply_filters( 'traduttore.git_clone_branch', $default_branch, $project->get_repository_name() );

		// We only care about the default or custom branch but don't want to send an error still.
		if ( $branch !== $ref ) {
			return new WP_REST_Response( [ 'result' => 'Not the default or custom branch' ] );
		}

		return $project;
	}
}
