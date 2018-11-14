<?php
/**
 * Class Configuration
 *
 * @package Traduttore\Tests
 */

namespace Required\Traduttore\Tests;

use \Required\Traduttore\Configuration as Config;

/**
 * Test cases for \Required\Traduttore\Configuration.
 */
class Configuration extends TestCase {
	public function test_get_path(): void {
		$config = new Config( dirname( __DIR__ ) . '/data/example-no-config' );

		$this->assertSame( dirname( __DIR__ ) . '/data/example-no-config', $config->get_path() );
	}

	public function test_get_config_empty_directory(): void {
		$config = new Config( dirname( __DIR__ ) . '/data/example-no-config' );

		$this->assertEmpty( $config->get_config() );
	}

	public function test_get_config_value_empty_directory(): void {
		$config = new Config( dirname( __DIR__ ) . '/data/example-no-config' );

		$this->assertNull( $config->get_config_value( 'foo' ) );
	}

	public function test_get_config_composer(): void {
		$config = new Config( dirname( __DIR__ ) . '/data/example-with-composer' );

		$this->assertEqualSets(
			[
				'mergeWith'  => 'foobar.pot',
				'textDomain' => 'baz',
				'exclude'    => [
					'bar',
					'bar/baz.php',
				],
			],
			$config->get_config()
		);
	}

	public function test_get_config_traduttore(): void {
		$config = new Config( dirname( __DIR__ ) . '/data/example-with-config' );

		$this->assertEqualSets(
			[
				'mergeWith'  => 'foobar.pot',
				'textDomain' => 'foo',
				'exclude'    => [
					'bar',
					'bar/baz.php',
				],
			],
			$config->get_config()
		);
	}
}
