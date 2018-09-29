---
id: configuration
title: Configuration
---

## Constants

The following constants can be defined to configure Traduttore:

* `TRADUTTORE_GITHUB_SYNC_SECRET`: Secret token for incoming GitHub webhook requests.
* `TRADUTTORE_WP_BIN`: Path to the WP-CLI executable on the system.

## Restricted Site Access

Sometimes you might not want your translation platform to be publicly accessible. However, to function properly some parts of the site need to be open to the public in order for Traduttore to work.

For this case, it's recommended to use the free [Restricted Site Access](https://wordpress.org/plugins/restricted-site-access/) plugin. Traduttore integrates well with this plugin by making sure the REST API endpoints remain unaffected by it.

There's no need for any manual configuration, everything happens in the background.
