<?php
/**
 * RestrictedSiteAccess class.
 *
 * @package Traduttore\Tests
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

		$this->go_to( '/index.php?gp_route=/api/translations/' );

		$is_restricted = $plugin->filter_restricted_site_access_is_restricted( true, $wp );

		$this->assertTrue( $is_restricted );
	}
}
