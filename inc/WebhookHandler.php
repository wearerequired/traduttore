<?php
/**
 * WebhookHandler interface.
 *
 * @since 3.0.0
 *
 * @package Required\Traduttore
 */

namespace Required\Traduttore;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

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
	 * @param WP_REST_Request $request Request object.
	 */
	public function __construct( WP_REST_Request $request );

	/**
	 * Downloads a remote repository.
	 *
	 * If the repository has been downloaded before, the latest changes will be pulled.
	 *
	 * @since 3.0.0
	 *
	 * @return bool True if permission is granted, false otherwise.
	 */
	public function permission_callback(): ?bool;

	/**
	 * Returns the local repository path..
	 *
	 * @since 3.0.0
	 *
	 * @return WP_Error|WP_REST_Response REST response on success, error object on failure.
	 */
	public function callback();
}
