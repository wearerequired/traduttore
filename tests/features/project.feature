@require-php-7.4
Feature: Print various details about the environment.

  Background:
    Given a WP installation
    And GlotPress develop being active
    And Traduttore being active

  Scenario: Run info command with invalid project ID
    When I try the WP-CLI command `traduttore project info 99999`
    Then STDERR should contain:
      """
      Project not found
      """
    And STDERR should not contain:
      """
      WordPress database error
      """

  Scenario: Run update command with invalid project ID
    When I try the WP-CLI command `traduttore project update 99999`
    Then STDERR should contain:
      """
      Project not found
      """
    And STDERR should not contain:
      """
      WordPress database error
      """
