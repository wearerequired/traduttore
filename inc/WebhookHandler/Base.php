<?php
/**
 * Base webhook handler class.
 *
 * @since 3.0.0
 *
 * @package Required\Traduttore\WebhookHandler
 */

namespace Required\Traduttore\WebhookHandler;

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
	 * @inheritdoc
	 */
	public function __construct( WP_REST_Request $request ) {
		$this->request = $request;
	}
}
