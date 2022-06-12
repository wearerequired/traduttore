@require-php-7.4
Feature: Print various details about the environment.

  Background:
	Given a WP installation with the Traduttore plugin
	And GlotPress develop being active

  Scenario: Run info command
	When I run the WP-CLI command `traduttore info`
    Then STDOUT should contain:
      """
      Traduttore version:
      """
	And STDOUT should contain:
      """
      WordPress version:
      """
	And STDOUT should contain:
      """
      GlotPress version:
      """
    And STDOUT should contain:
      """
      WP-CLI version
      """
    And STDOUT should contain:
      """
      WP-CLI binary path
      """
    And STDOUT should contain:
      """
      Git binary path
      """
    And STDOUT should contain:
      """
      Mercurial binary path
      """
    And STDOUT should contain:
      """
      Subversion binary path
      """
    And STDOUT should contain:
      """
      Cache directory
      """

  Scenario: Run info command with JSON formatting
	When I run the WP-CLI command `traduttore info --format=json`
	Then STDOUT should contain:
      """
      "traduttore_version"
      """
	And STDOUT should contain:
      """
      "wp_version"
      """
	And STDOUT should contain:
      """
      "gp_version"
      """
	And STDOUT should contain:
      """
      "wp_cli_version"
      """
	And STDOUT should contain:
      """
      "wp_cli_path"
      """
	And STDOUT should contain:
      """
      "git_path"
      """
	And STDOUT should contain:
      """
      "hg_path"
      """
	And STDOUT should contain:
      """
      "svn_path"
      """
	And STDOUT should contain:
      """
      "cache_dir"
      """
