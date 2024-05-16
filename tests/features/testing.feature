Feature: Test that the tests are working.

  Background:
    Given a WP installation

  @require-php-7.4
  Scenario: Traduttore and GlotPress develop should be active.
    Given GlotPress develop being active
    And Traduttore being active

    When I run `wp plugin status glotpress`
    Then STDOUT should contain:
      """
      Name: GlotPress
      """
    And STDOUT should contain:
      """
      Status: Active
      """
    And the return code should be 0

    When I run `wp plugin status traduttore`
    Then STDOUT should contain:
      """
      Name: Traduttore
      """
    And STDOUT should contain:
      """
      Status: Active
      """
    And the return code should be 0

  Scenario: Traduttore and GlotPress stable should be active.
    Given GlotPress stable being active
    And Traduttore being active

    When I run `wp plugin status glotpress`
    Then STDOUT should contain:
      """
      Name: GlotPress
      """
    And STDOUT should contain:
      """
      Status: Active
      """
    And the return code should be 0

    When I run `wp plugin status traduttore`
    Then STDOUT should contain:
      """
      Name: Traduttore
      """
    And STDOUT should contain:
      """
      Status: Active
      """
    And the return code should be 0
