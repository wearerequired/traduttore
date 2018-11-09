<?php
/**
 * Base webhook handler class.
 *
 * @since 3.0.0
 *
 * @package Required\Traduttore\WebhookHandler
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
	 * @var WP_REST_Request The current REST request.
	 */
	protected $request;

	/**
	 * Class constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param WP_REST_Request $request Request object.
	 */
	public function __construct( WP_REST_Request $request ) {
		$this->request = $request;
	}

	/**
	 * Returns the webhook sync secret.
	 *
	 * @since 3.0.0
	 *
	 * @param Project|null $project The current project if found.
	 *
	 * @return string Secret if set, null otherwise.
	 */
	protected function get_secret( Project $project = null ): ?string {
		$secret = null;

		switch ( get_class( $this ) ) {
			case Bitbucket::class:
				if ( defined( 'TRADUTTORE_BITBUCKET_SYNC_SECRET' ) ) {
					$secret = TRADUTTORE_BITBUCKET_SYNC_SECRET;
				}
				break;
			case GitHub::class:
				if ( defined( 'TRADUTTORE_GITHUB_SYNC_SECRET' ) ) {
					$secret = TRADUTTORE_GITHUB_SYNC_SECRET;
				}
				break;
			case GitLab::class:
				if ( defined( 'TRADUTTORE_GITLAB_SYNC_SECRET' ) ) {
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
		 * @param string         $secret  Webhook sync secret.
		 * @param WebhookHandler $handler The current webhook handler instance.
		 * @param Project|null   $project The current project if passed through.
		 */
		return apply_filters( 'traduttore.webhook_secret', $secret, $this, $project );
	}
}
