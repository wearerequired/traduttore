<?php
/**
 * Class GetPushResources
 *
 * @package H2push
 */

namespace Required\Traduttore\Tests;

use \GP_UnitTestCase;
use \Required\Traduttore\ProjectLocator as Locator;

/**
 *  Test cases for \Required\Traduttore\ProjectLocator.
 */
class ProjectLocator extends GP_UnitTestCase {
	/**
	 * @var \GP_Project
	 */
	public $root;

	/**
	 * @var \GP_Project
	 */
	public $sub;

	/**
	 * @var \GP_Project
	 */
	public $subsub;

	public function setUp() {
		parent::setUp();

		$this->root   = $this->factory->project->create( [ 'name' => 'Root' ] );
		$this->sub    = $this->factory->project->create(
			[
				'name'              => 'Sub',
				'parent_project_id' => $this->root->id,
			]
		);
		$this->subsub = $this->factory->project->create(
			[
				'name'                => 'SubSub',
				'parent_project_id'   => $this->sub->id,
				'source_url_template' => 'https://github.com/wearerequired/traduttore/blob/master/%file%#L%line%',
			]
		);
	}

	public function test_find_project_by_glotpress_path() {
		$locator = new Locator( 'root' );

		$this->assertEquals( $locator->get_project()->id, $this->root->id );
	}

	public function test_find_project_by_glotpress_subpath() {
		$locator = new Locator( 'root/sub' );

		$this->assertEquals( $locator->get_project()->id, $this->sub->id );
	}

	public function test_find_project_by_glotpress_subsubpath() {
		$locator = new Locator( 'root/sub/subsub' );

		$this->assertEquals( $locator->get_project()->id, $this->subsub->id );
	}

	public function test_find_project_by_glotpress_id() {
		$locator = new Locator( (int) $this->sub->id );

		$this->assertEquals( $locator->get_project()->id, $this->sub->id );
	}

	public function test_find_project_by_glotpress_id_as_string() {
		$locator = new Locator( (string) $this->sub->id );

		$this->assertEquals( $locator->get_project()->id, $this->sub->id );
	}

	public function test_find_project_by_github_url() {
		$locator = new Locator( 'https://github.com/wearerequired/traduttore' );

		$this->assertEquals( $locator->get_project()->id, $this->subsub->id );
	}
}
