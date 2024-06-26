<?php
/**
 * Project class.
 */

namespace Required\Traduttore\Tests;

use DateTime;
use DateTimeZone;
use GP_Project;
use Required\Traduttore\Tests\Utils\TestCase;
use Required\Traduttore\Project as TraduttoreProject;

/**
 * Test cases for \Required\Traduttore\Project.
 */
class Project extends TestCase {
	protected GP_Project $gp_project;

	protected TraduttoreProject $project;

	public function setUp(): void {
		parent::setUp();

		$this->gp_project = $this->factory()->project->create(
			[
				'name'   => 'Project',
				'active' => 1,
			]
		);

		$this->project = new TraduttoreProject( $this->gp_project );
	}

	public function test_get_project(): void {
		$this->assertSame( $this->gp_project, $this->project->get_project() );
	}

	public function test_get_id(): void {
		$this->assertSame( $this->gp_project->id, $this->project->get_id() );
	}

	public function test_get_name(): void {
		$this->assertSame( 'Project', $this->project->get_name() );
	}

	public function test_get_slug(): void {
		$this->assertSame( $this->gp_project->slug, $this->project->get_slug() );
	}

	public function test_get_source_url_template_returns_null_if_missing(): void {
		$this->assertNull( $this->project->get_source_url_template() );
		$this->assertSame( $this->gp_project->source_url_template(), $this->project->get_source_url_template() );
	}

	public function test_get_source_url_template(): void {
		$this->gp_project->source_url_template = 'foobar';

		$this->assertSame( 'foobar', $this->project->get_source_url_template() );
	}

	public function test_get_repository_type_returns_null_if_missing(): void {
		$this->assertNull( $this->project->get_repository_type() );
	}

	public function test_get_repository_type(): void {
		$type = \Required\Traduttore\Repository::TYPE_GITHUB;

		$this->project->set_repository_type( $type );

		$this->assertSame( $type, $this->project->get_repository_type() );
	}

	public function test_get_repository_vcs_type_returns_null_if_missing(): void {
		$this->assertNull( $this->project->get_repository_vcs_type() );
	}

	public function test_get_repository_vcs_type(): void {
		$type = 'git';

		$this->project->set_repository_vcs_type( $type );

		$this->assertSame( $type, $this->project->get_repository_vcs_type() );
	}

	public function test_get_repository_url_returns_null_if_missing(): void {
		$this->assertNull( $this->project->get_repository_url() );
	}

	public function test_get_repository_url(): void {
		$url = 'https://github.com/wearerequired/traduttore';

		$this->project->set_repository_url( $url );

		$this->assertSame( $url, $this->project->get_repository_url() );
	}

	public function test_get_repository_visibility_returns_null_if_missing(): void {
		$this->assertNull( $this->project->get_repository_visibility() );
	}

	public function test_get_repository_visibility(): void {
		$visibility = 'public';

		$this->project->set_repository_visibility( $visibility );

		$this->assertSame( $visibility, $this->project->get_repository_visibility() );
	}

	public function test_get_repository_name_returns_null_if_missing(): void {
		$this->assertNull( $this->project->get_repository_name() );
	}

	public function test_get_repository_name(): void {
		$name = 'wearerequired/traduttore';

		$this->project->set_repository_name( $name );

		$this->assertSame( $name, $this->project->get_repository_name() );
	}

	public function test_get_repository_ssh_url_returns_null_if_missing(): void {
		$this->assertNull( $this->project->get_repository_ssh_url() );
	}

	public function test_get_repository_ssh_url(): void {
		$url = 'git@github.com:wearerequired/traduttore.git';

		$this->project->set_repository_ssh_url( $url );

		$this->assertSame( $url, $this->project->get_repository_ssh_url() );
	}

	public function test_get_repository_https_url_returns_null_if_missing(): void {
		$this->assertNull( $this->project->get_repository_https_url() );
	}

	public function test_get_repository_https_url(): void {
		$url = 'https://github.com/wearerequired/traduttore.git';

		$this->project->set_repository_https_url( $url );

		$this->assertSame( $url, $this->project->get_repository_https_url() );
	}

	public function test_get_last_updated_time_returns_null_if_missing(): void {
		$this->assertNull( $this->project->get_repository_https_url() );
	}

	public function test_get_last_updated_time(): void {
		$time = new DateTime( 'now', new DateTimeZone( 'UTC' ) );

		$this->project->set_last_updated_time( $time );

		$this->assertInstanceOf( DateTime::class, $this->project->get_last_updated_time() );
		$this->assertSame( $time->getTimestamp(), $this->project->get_last_updated_time()->getTimestamp(), 'Last updated time is not identical' );
	}

	public function test_get_repository_webhook_secret(): void {
		$secret = 'Sup3rS3cr3tPassw0rd';

		$this->project->set_repository_webhook_secret( $secret );

		$this->assertSame( $secret, $this->project->get_repository_webhook_secret() );
	}

	public function test_get_version(): void {
		$version = '1.2.3';

		$this->project->set_version( $version );

		$this->assertSame( $version, $this->project->get_version() );
	}

	public function test_is_active(): void {
		$this->assertTrue( $this->project->is_active() );
	}
}
