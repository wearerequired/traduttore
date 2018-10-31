# Contribute to Traduttore

[Traduttore](https://github.com/wearerequired/traduttore) and [Traduttore Registry](https://github.com/wearerequired/traduttore-registry) both are open source projects. Everyone is welcome to contribute to them, no matter if it's with code, documentation, or something else.

Bug reports and patches are very welcome. When contributing, please ensure you stick to the following guidelines.

## Writing a Bug Report

When writing a bug report...

* [Open an issue](https://github.com/wearerequired/traduttore/issues/new)
* Follow the guidelines specified in the issue template

We will take a look at your issue and either assign it keywords and a milestone or get back to you if there are open questions.

## Contributing Code

When contributing code...

* Fork the `master` branch of the repository on GitHub
* Run `composer install` to install necessary development tools requirements
* Make changes to the forked repository
    * Try to follow the [WordPress coding standards](https://make.wordpress.org/core/handbook/best-practices/coding-standards/), especially in terms of inline documentation.
    * Verify that using `composer lint` and `composer format` to automatically fix most coding standards issues
    * Ideally write unit tests for any code changes and verify them using `composer test`
* Commit and push changes to your fork and [submit a pull request](https://github.com/wearerequired/traduttore/compare) to the `master` branch

We try to review incoming pull requests as soon as possible and either merge them or make suggestions for some further improvements. 
