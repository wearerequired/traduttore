@require-php-7.1
Feature: Print various details about the environment.

  Background:
	Given a WP installation with the Traduttore plugin
	And GlotPress develop being active

  Scenario: Check active plugins
	When I run `wp plugin list --active`
    Then STDOUT should contain:
      """
      glotpress
      """
	And STDOUT should contain:
	  """
	  3.0.0-alpha
	  """

  Scenario: Run info command with invalid project ID
	When I try `wp traduttore project info 99999`
	# FIXME
	Then STDERR should contain:
      """
      PHP Fatal error:  Uncaught Error: Class 'GP' not foun
      """
#
#    Then STDERR should contain:
#      """
#      Project not found
#      """

  Scenario: Run update command with invalid project ID
    When I try `wp traduttore project update 99999`
	# FIXME
    Then STDERR should contain:
      """
      PHP Fatal error:  Uncaught Error: Class 'GP' not foun
      """
#
#    Then STDERR should contain:
#      """
#      Project not found
#      """
