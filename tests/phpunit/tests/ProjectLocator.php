<?php
/**
 * Class ProjectLocator
 *
 * @package Traduttore\Tests
 */

namespace Required\Traduttore\Tests;

use \Required\Traduttore\Project;
use \Required\Traduttore\ProjectLocator as Locator;

/**
 * Test cases for \Required\Traduttore\ProjectLocator.
 */
class ProjectLocator extends TestCase {
	/**
	 * @var \GP_Project
	 */
	protected $root;

	/**
	 * @var \GP_Project
	 */
	protected $sub;

	/**
	 * @var \GP_Project
	 */
	protected $subsub;

	public function setUp() {
		parent::setUp();

		$this->root   = $this->factory->project->create(
			[
				'name' => 'Root',
			]
		);
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

	public function test_empty_string(): void {
		$locator = new Locator( '' );

		$this->assertNull( $locator->get_project() );
	}

	public function test_false(): void {
		$locator = new Locator( false );

		$this->assertNull( $locator->get_project() );
	}

	public function test_invalid_project_id(): void {
		$locator = new Locator( 0 );

		$this->assertNull( $locator->get_project() );
	}

	public function test_existing_project_instance(): void {
		$project = new Project( $this->root );
		$locator = new Locator( $project );

		$this->assertSame( $project, $locator->get_project() );
	}

	public function test_existing_glotpress_project_instance(): void {
		$project = $this->root;
		$locator = new Locator( $project );

		$this->assertEquals( $this->root->id, $locator->get_project()->get_id() );
	}

	public function test_find_project_by_glotpress_path(): void {
		$locator = new Locator( 'root' );

		$this->assertEquals( $this->root->id, $locator->get_project()->get_id() );
	}

	public function test_find_project_by_glotpress_subpath(): void {
		$locator = new Locator( 'root/sub' );

		$this->assertEquals( $this->sub->id, $locator->get_project()->get_id() );
	}

	public function test_find_project_by_glotpress_subsubpath(): void {
		$locator = new Locator( 'root/sub/subsub' );

		$this->assertEquals( $this->subsub->id, $locator->get_project()->get_id() );
	}

	public function test_find_project_by_glotpress_id(): void {
		$locator = new Locator( (int) $this->sub->id );

		$this->assertEquals( $this->sub->id, $locator->get_project()->get_id() );
	}

	public function test_find_project_by_glotpress_id_as_string(): void {
		$locator = new Locator( (string) $this->sub->id );

		$this->assertEquals( $this->sub->id, $locator->get_project()->get_id() );
	}

	public function test_find_project_by_github_url(): void {
		$locator = new Locator( 'https://github.com/wearerequired/traduttore' );

		$this->assertEquals( $this->subsub->id, $locator->get_project()->get_id() );
	}

	public function test_find_project_by_repository_name(): void {
		$project = new Project(
			$this->factory->project->create(
				[
					'name' => 'Foo Bar',
				]
			)
		);

		$project->set_repository_name( 'wearerequired/traduttore-registry' );

		$locator = new Locator( 'wearerequired/traduttore-registry' );

		$this->assertEquals( $project->get_id(), $locator->get_project()->get_id() );
	}

	public function test_find_project_by_repository_url(): void {
		$project = new Project(
			$this->factory->project->create(
				[
					'name' => 'Foo Bar',
				]
			)
		);

		$project->set_repository_url( 'https://github.com/wearerequired/traduttore-registry' );

		$locator = new Locator( 'https://github.com/wearerequired/traduttore-registry' );

		$this->assertEquals( $project->get_id(), $locator->get_project()->get_id() );
	}
}
