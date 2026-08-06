<?php
/**
 * Class TestCase_Route
 */

namespace Required\Traduttore\Tests\Utils;

use GP_UnitTestCase_Route;

/**
 * Base TestCase class for custom routes.
 */
class TestCase_Route extends GP_UnitTestCase_Route {

	/**
	 * Temporary workaround to allow the tests to run on PHPUnit 10.
	 *
	 * @link https://core.trac.wordpress.org/ticket/59486
	 */
	public function expectDeprecated(): void {
	}
}
