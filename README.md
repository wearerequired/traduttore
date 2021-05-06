# Traduttore

![PHPUnit](https://github.com/wearerequired/traduttore/workflows/PHPUnit/badge.svg)
![Lint](https://github.com/wearerequired/traduttore/workflows/Lint/badge.svg)
[![codecov](https://codecov.io/gh/wearerequired/traduttore/branch/master/graph/badge.svg)](https://codecov.io/gh/wearerequired/traduttore)
[![Latest Stable Version](https://poser.pugx.org/wearerequired/traduttore/v/stable)](https://packagist.org/packages/wearerequired/traduttore)
[![Latest Unstable Version](https://poser.pugx.org/wearerequired/traduttore/v/unstable)](https://packagist.org/packages/wearerequired/traduttore)

Traduttore is a WordPress plugin that allows you to host your own WordPress.org-style translation API for your WordPress projects.

## How it Works

Working on a multilingual WordPress project with custom plugins and themes can be quite cumbersome. Every time you add new strings to the project, you have to regenerate POT files and update the PO/MO files for every locale. All these changes clutter the history of your Git repository and are prone to errors as well. Plus, you can't easily send these translation files to your clients.

These problems don't exist for plugins and themes hosted on [WordPress.org](https://wordpress.org/), as they benefit from the [translate.wordpress.org](https://translate.wordpress.org/) translation platform. Whenever you publish a new version of your project, WordPress.org makes sure that new strings can be translated. With Traduttore, you can now get the same experience for your custom projects hosted on GitHub!

Every time you commit something to your plugin or theme, Traduttore will extract translatable strings using [WP-CLI](https://github.com/wp-cli/i18n-command) and add import these to GlotPress.

Then, you (or even your clients!) can translate these strings right from within GlotPress. Whenever translations are edited, Traduttore will create a ZIP file containing PO and MO files that can be consumed by WordPress. A list of available ZIP files is exposed over a simple API endpoint that is understood by WordPress.

Using our little helper library called [Traduttore Registry](https://github.com/wearerequired/traduttore-registry), you can then tell WordPress that translations for your project should be loaded from that API endpoint.

After that, you never have to worry about the translation workflow ever again!

## Features

* Automatic string extraction
* ZIP file generation and caching
* Works with any WordPress plugin or theme hosted on GitHub
* Custom WP-CLI commands to manage translations
* Supports [Restricted Site Access](https://de.wordpress.org/plugins/restricted-site-access/)
* Supports sending [Slack](https://wordpress.org/plugins/slack/) notifications

<br>

[![a required open source product - let's get in touch](https://media.required.com/images/open-source-banner.png)](https://required.com/en/lets-get-in-touch/)
