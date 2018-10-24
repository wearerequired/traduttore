<?php
/**
 * Base webhook handler class.
 *
 * @package Required\Traduttore
 */

namespace Required\Traduttore\WebhookHandler;

use Required\Traduttore\WebhookHandler;
use WP_REST_Request;

abstract class Base implements WebhookHandler {
	/**
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
