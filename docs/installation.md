---
id: installation
title: Installation
---

The goal for Traduttore is to make it as easy as possible to supercharge your WordPress internationalization workflow.

## System Requirements

Traduttore is a WordPress plugin that sits on top of the [GlotPress](https://glotpress.org/) plugin. That means you need to have both WordPress and GlotPress installed.

GlotPress is available as a WordPress plugin through the plugin directory. Installing it is as simple as searching for “GlotPress” and installing it. After activating the plugin, GlotPress can be accessed via `https://<home_url>/glotpress/`.

Traduttore requires at least PHP 7.1, while the [Traduttore Registry](https://github.com/wearerequired/traduttore-registry) also supports PHP 7.0.

Traduttore requires [WP-CLI](https://wp-cli.org/) 2.0 or newer to be installed on the server. [Learn more about the available CLI commands](cli.md).

## Installing Traduttore

If you're using [Composer](https://getcomposer.org/) to manage dependencies, you can use the following command to add the plugin to your site:

```bash
composer require wearerequired/traduttore
```

Alternatively, you can download a ZIP file containing the plugin on [GitHub](https://github.com/wearerequired/traduttore) and upload it in your WordPress admin screen.

Afterwards, activating Traduttore is all you need to do. There's no special settings UI or anything for it.
