<?php
/**
 * Webhook handler interface
 *
 * @since 3.0.0
 */

namespace Required\Traduttore;

use WP_REST_Request;

/**
 * WebhookHandler interface.
 *
 * @since 3.0.0
 */
interface WebhookHandler {
	/**
	 * Class constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_REST_Request $request Request object.
	 */
	public function __construct( WP_REST_Request $request );

	/**
	 * Permission callback for incoming webhooks.
	 *
	 * @since 3.0.0
	 *
	 * @return bool True if permission is granted, false otherwise.
	 */
	public function permission_callback(): ?bool;

	/**
	 * Callback for incoming webhooks.
	 *
	 * @since 3.0.0
	 *
	 * @return \WP_Error|\WP_REST_Response REST response on success, error object on failure.
	 */
	public function callback();
}
