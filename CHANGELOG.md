# Changelog

## 3.0.0

* Heavy architectural changes to make the plugin more modular
* Added support for Bitbucket.org repositories (Mercurial and Git)
* Added support for GitLab repositories
* Added support for self-managed repositories (GitLab and others)
* Added new REST API route for incoming webhooks (`traduttore/v1/incoming-webhook`) which replaces the deprecated route (`github-webhook/v1/push-event`)
* Changed all filters and actions to use `.` as the separator between the prefix and hook name instead of `_`
* Improved scheduling of cron events to reduce number of unnecessary builds and updates
* Added new `wp traduttore info` CLI command
* Greatly improved documentation

## 2.0.3

* Uses HTTPS instead of SSH for cloning repositories if possible
* Fixed uninstall routine
* Fixed a few other smaller issues

## 2.0.2

* Fixed a few errors within the CLI commands
* Fixed an error where deleting the local Git repository wasn't possible ([ee4ee06](https://github.com/wearerequired/traduttore/commit/ee4ee0626b009f88e40362b22dd69c9092e742e5))
* Introduce `TRADUTTORE_WP_BIN` constant to allow overriding the path to WP-CLI ([#32](https://github.com/wearerequired/traduttore/pull/32))
* Makes sure `wp_tempnam()` is always available ([#31](https://github.com/wearerequired/traduttore/pull/31))

## 2.0.1

* Fixed a possible fatal error in the project locator class ([b6f6ceb](https://github.com/wearerequired/traduttore/commit/b6f6cebbed32f67d5891726c00f7d6bc44f42ff2))
* Improved code formatting and inline documentation

## 2.0.0

* Added CLI commands
* Added ZIP file generation
* Added translation API
* Added Slack notifications

## 1.0.0

* Initial release
