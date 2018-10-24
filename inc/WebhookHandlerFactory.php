<?php
/**
 * WebhookHandlerFactory class.
 *
 * @since   3.0.0
 *
 * @package Required\Traduttore
 */

namespace Required\Traduttore;

use Required\Traduttore\WebhookHandler\{
	Bitbucket, GitHub, GitLab
};
use WP_REST_Request;

/**
 * WebhookHandlerFactory class.
 *
 * @since 3.0.0
 */
class WebhookHandlerFactory {
	/**
	 * Returns a new webhook handler instance for a given project based on the request.
	 *
	 * @since 3.0.0
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WebhookHandler Webhook handler instance.
	 */
	public function get_handler( WP_REST_Request $request ): ?WebhookHandler {
		// See https://developer.github.com/webhooks/
		if ( $request->get_header( 'x-github-event' ) ) {
			return new GitHub( $request );
		}

		// See https://docs.gitlab.com/ee/user/project/integrations/webhooks.html
		if ( $request->get_header( 'x-gitlab-event' ) ) {
			return new GitLab( $request );
		}

		// See https://confluence.atlassian.com/bitbucket/event-payloads-740262817.html
		if ( $request->get_header( 'x-event-key' ) ) {
			return new Bitbucket( $request );
		}

		return null;
	}
}
