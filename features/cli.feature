@require-php-7.1
Feature: Adds custom WP-CLI commands

  Background:
	Given a WP installation

  Scenario: Traduttore commands are not available by default.
    When I try `wp traduttore`
    Then STDERR should contain:
      """
      Error: 'traduttore' is not a registered wp command.
      """
	And the return code should be 1

  Scenario: Traduttore commands are available after plugin activation.
	Given these installed and active plugins:
      """
      {PROJECT_DIR}/build/traduttore.zip
      """

	When I run `wp plugin is-active traduttore`
	Then the return code should be 0

	When I try `wp traduttore`
	Then STDERR should not contain:
      """
      Error: 'traduttore' is not a registered wp command.
      """
