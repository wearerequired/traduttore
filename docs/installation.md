---
layout: default
title: Installation
nav_order: 2
---

# Installation

The goal for Traduttore is to make it as easy as possible to supercharge your WordPress internationalization workflow.

## System Requirements

### WordPress

Traduttore is a WordPress plugin that sits on top of the [GlotPress](https://glotpress.org/) plugin. That means you need to have both WordPress and GlotPress installed.

GlotPress is available as a WordPress plugin through the plugin directory. Installing it is as simple as searching for “GlotPress” and installing it. After activating the plugin, GlotPress can be accessed via `https://<home_url>/glotpress/`. There's also a [GlotPress manual](https://glotpress.blog/the-manual/) that you can follow.

To send Slack notifications, Traduttore requires a separate WordPress plugin. [Learn more about setting up notifications](notifications.md).

### Server

Traduttore requires at least PHP 7.1.

To download the latest code from your source code repositories, Traduttore requires the respective version control to be installed on the server. Depending on the projects it may be Git, Subversion or Mercurial.

For string extraction Traduttore requires [WP-CLI](https://wp-cli.org/) 2.0 or newer. [Learn more about the available CLI commands](cli.md).

If you're not sure whether Git or WP-CLI are available on your system, please contact your hosting provider or run the WP-CLI command `wp traduttore info`.

## Installing Traduttore

If you're using [Composer](https://getcomposer.org/) to manage dependencies, you can use the following command to add the plugin to your site:

```bash
composer require wearerequired/traduttore
```

Alternatively, you can download a ZIP file containing the plugin on [GitHub](https://github.com/wearerequired/traduttore) and upload it in your WordPress admin screen.

Afterwards, activating Traduttore is all you need to do. There's no special settings UI or anything for it.
