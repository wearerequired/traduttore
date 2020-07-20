# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [3.1.0] - 2020-07-20

### Added
* Introduce `traduttore.map_entries_to_source` filter to change the mapping of sources to translation entries. Props @florianbrinkmann. [#170]
* Support `application/x-www-form-urlencoded` as content type for GitHub webhooks. [#166]
* Include file reference in JSON translation files. [#176]

### Fixed
* Fix generating empty language pack ZIP files. Props @florianbrinkmann. [#168]
* Fix compatibility with GlotPress 3.0 and its stricter type checks. [#174]

## [3.0.0] - 2019-03-15
Due to the large number of changes in the release it is recommended to update all of the language packs. This can be done with the WP-CLI command `wp traduttore language-pack build --all`.

### Changed
* Heavy architectural changes to make the plugin more modular.
* All filters and actions now use `.` as the separator between the prefix and hook name instead of `_`.
* Scheduling of cron events to reduce number of unnecessary builds and updates.
* Bump Traduttore Registry version to 2.0.
* Existing WP-CLI commands:
  * `wp traduttore build <project>` → `wp traduttore language-pack build <project>`
  * `wp traduttore cache clear <project>` → `wp traduttore project cache clear <project>`
  * `wp traduttore update <project>` → `wp traduttore project update <project>`

### Added
* Support for Bitbucket.org repositories (Mercurial and Git).
* Support for GitLab repositories.
* Support for self-managed repositories (GitLab and others).
* New REST API route for incoming webhooks (`traduttore/v1/incoming-webhook`).
* Support for [JavaScript translations](https://make.wordpress.org/core/2018/11/09/new-javascript-i18n-support-in-wordpress/).
* Greatly improved [documentation](https://wearerequired.github.io/traduttore/).
* New WP-CLI commands:
  * `wp traduttore info` for information about the Traduttore setup.
  * `wp traduttore project info <project>` for information about a project.
  * `wp traduttore language-pack list <project>` for listing all language packs in a project.

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

[Unreleased]: https://github.com/wearerequired/traduttore/compare/3.1.0...HEAD
[3.1.0]: https://github.com/wearerequired/traduttore/compare/3.0.0...3.1.0
[3.0.0]: https://github.com/wearerequired/traduttore/compare/2.0.3...3.0.0
[2.0.3]: https://github.com/wearerequired/traduttore/compare/2.0.2...2.0.3
[2.0.2]: https://github.com/wearerequired/traduttore/compare/2.0.1...2.0.2
[2.0.1]: https://github.com/wearerequired/traduttore/compare/2.0.0...2.0.1

[#166]: https://github.com/wearerequired/traduttore/issues/166
[#168]: https://github.com/wearerequired/traduttore/issues/168
[#170]: https://github.com/wearerequired/traduttore/issues/170
[#174]: https://github.com/wearerequired/traduttore/issues/174
[#176]: https://github.com/wearerequired/traduttore/issues/176
