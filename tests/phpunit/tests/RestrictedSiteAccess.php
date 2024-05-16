<?php
/**
 * RestrictedSiteAccess class.
 */

namespace Required\Traduttore\Tests;

use Required\Traduttore\Plugin;

/**
 * Test cases for Restricted Site Access integration in \Required\Traduttore\Plugin.
 */
class RestrictedSiteAccess extends TestCase {
	public function test_does_not_change_restriction_by_default(): void {
		global $wp;

		$plugin = new Plugin();

		$this->go_to( '/' );

		$is_restricted = $plugin->filter_restricted_site_access_is_restricted( true, $wp );

		$this->assertTrue( $is_restricted );
	}

	public function test_lifts_restriction_for_gp_translations_api_route(): void {
		global $wp;

		$plugin = new Plugin();

		$this->go_to( '/index.php?gp_route=api/translations/' );

		$is_restricted = $plugin->filter_restricted_site_access_is_restricted( true, $wp );

		$this->assertFalse( $is_restricted );
	}

	public function test_does_not_change_restriction_for_other_gp_api_route(): void {
		global $wp;

		$plugin = new Plugin();

		$this->go_to( '/index.php?gp_route=api/projects' );

		$is_restricted = $plugin->filter_restricted_site_access_is_restricted( true, $wp );

		$this->assertTrue( $is_restricted );
	}

	public function test_lifts_restriction_for_incoming_webhook_rest_route(): void {
		global $wp;

		$plugin = new Plugin();

		$wp->query_vars['rest_route'] = '/traduttore/v1/incoming-webhook';

		$is_restricted = $plugin->filter_restricted_site_access_is_restricted( true, $wp );

		$this->assertFalse( $is_restricted );
	}

	public function test_lifts_restriction_for_legacy_incoming_webhook_rest_rout(): void {
		global $wp;

		$plugin = new Plugin();

		$wp->query_vars['rest_route'] = '/github-webhook/v1/push-event';

		$is_restricted = $plugin->filter_restricted_site_access_is_restricted( true, $wp );

		$this->assertFalse( $is_restricted );
	}
}
