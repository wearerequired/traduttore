# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.0.0] - 2019-03-14
### Changed

* Heavy architectural changes to make the plugin more modular.
* All filters and actions now use `.` as the separator between the prefix and hook name instead of `_`.
* Scheduling of cron events to reduce number of unnecessary builds and updates.

### Added
* Support for Bitbucket.org repositories (Mercurial and Git).
* Support for GitLab repositories.
* Support for self-managed repositories (GitLab and others).
* New REST API route for incoming webhooks (`traduttore/v1/incoming-webhook`).
* CLI command `wp traduttore info` for information about Traduttore setup.
* Greatly improved [documentation](https://wearerequired.github.io/traduttore/).

### Deprecated
* The REST API route `github-webhook/v1/push-event` for incoming webhooks is replaced by `traduttore/v1/incoming-webhook`.

### Removed
* Remove all filters and actions with `_` as the separator.

## [2.0.3] - 2018-07-09
### Changed
* Use HTTPS instead of SSH for cloning repositories if possible.

### Fixed
* Fix uninstall routine and a few other smaller issues.

## [2.0.2] - 2018-06-25
### Added
* Introduce `TRADUTTORE_WP_BIN` constant to allow overriding the path to WP-CLI.

### Fixed
* Fix a few errors within the CLI commands.
* Fix an error where deleting the local Git repository wasn't possible.
* Make sure `wp_tempnam()` is always available.

## [2.0.1] - 2018-06-21

### Fixed
* Fix a possible fatal error in the project locator class.

### Changed
* Improve code formatting and inline documentation.

## 2.0.0 - 2018-06-19

### Added
* CLI commands.
* ZIP file generation.
* Translation API.
* Slack notifications.

## 1.0.0 - 2017-05-30

### Added
* Initial release.

[Unreleased]: https://github.com/wearerequired/traduttore/compare/2.0.3...3.0.0
[2.0.3]: https://github.com/wearerequired/traduttore/compare/2.0.2...2.0.3
[2.0.2]: https://github.com/wearerequired/traduttore/compare/2.0.1...2.0.2
[2.0.1]: https://github.com/wearerequired/traduttore/compare/2.0.0...2.0.1
