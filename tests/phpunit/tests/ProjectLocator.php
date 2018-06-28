<?php
/**
 * Class GetPushResources
 *
 * @package H2push
 */

namespace Required\Traduttore\Tests;

use \GP_UnitTestCase;
use \Required\Traduttore\ProjectLocator as PL;

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

		$this->root   = $this->factory->project->create( array( 'name' => 'Root' ) );
		$this->sub    = $this->factory->project->create( array( 'name' => 'Sub', 'parent_project_id' => $root->id ) );
		$this->subsub = $this->factory->project->create( array( 'name' => 'SubSub', 'parent_project_id' => $sub->id ) );
	}
}
