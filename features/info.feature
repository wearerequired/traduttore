@require-php-7.1
Feature: Print various details about the environment.

  Background:
	Given a WP installation
	And these installed and active plugins:
	  """
	  {PROJECT_DIR}/build/traduttore.zip
	  """

  Scenario: Run info command
    When I run `wp traduttore info`
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
