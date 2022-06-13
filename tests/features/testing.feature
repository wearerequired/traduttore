Feature: Test that the tests are working.

  Background:
    Given a WP installation with the Traduttore plugin

  @require-php-7.4
  Scenario: Traduttore and GlotPress develop should be active.
    Given GlotPress develop being active

    When I run `wp plugin status glotpress`
    Then STDOUT should contain:
      """
      Name: GlotPress
      Status: Active
      """
    And the return code should be 0

    When I run `wp plugin status traduttore`
    Then STDOUT should contain:
      """
      Name: Traduttore
      Status: Active
      """
    And the return code should be 0

  Scenario: Traduttore and GlotPress stable should be active.
    Given GlotPress stable being active

    When I run `wp plugin status glotpress`
    Then STDOUT should contain:
      """
      Name: GlotPress
      Status: Active
      """
    And the return code should be 0

    When I run `wp plugin status traduttore`
    Then STDOUT should contain:
      """
      Name: Traduttore
      Status: Active
      """
    And the return code should be 0
