# Changelog

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
