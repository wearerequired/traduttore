<?php
/**
 * Base webhook handler implementation
 *
 * @since 3.0.0
 */

namespace Required\Traduttore\WebhookHandler;

use Required\Traduttore\Project;
use Required\Traduttore\WebhookHandler;
use WP_REST_Request;

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
}
