---
layout: default
title: Configuration
nav_order: 3
---

# Configuration

## Constants

The following constants can be defined to configure Traduttore:

* `TRADUTTORE_BITBUCKET_SYNC_SECRET`: Secret token for incoming Bitbucket webhook requests.
* `TRADUTTORE_GITHUB_SYNC_SECRET`: Secret token for incoming GitHub webhook requests.
* `TRADUTTORE_GITLAB_SYNC_SECRET`: Secret token for incoming GitLab webhook requests.
* `TRADUTTORE_WP_BIN`: Path to the WP-CLI executable on the system.

## Restricted Site Access

Sometimes you might not want your translation platform to be publicly accessible. However, to function properly some parts of the site need to be open to the public in order for Traduttore to work.

For this case, it's recommended to use the free [Restricted Site Access](https://wordpress.org/plugins/restricted-site-access/) plugin. Traduttore integrates well with this plugin by making sure the REST API endpoints remain unaffected by it.

There's no need for any manual configuration, everything happens in the background.

## Task Scheduler

Traduttore relies on WordPress' built-in cron functionality to schedule single events. The WordPress cron normally relies on users visiting your WordPress site in order to execute scheduled events.

To make sure your events are executed on time, we suggest setting up system cron jobs that runs reliably every few minutes.

[Learn more about hooking WP-Cron into the system task scheduler](https://developer.wordpress.org/plugins/cron/hooking-wp-cron-into-the-system-task-scheduler/).

There are two tasks that are scheduled:
1. The `traduttore.update` task is created when the webhook is hit. The task runs by default 3 minutes after being triggered.
2. The `traduttore.generate_zip` tasks in created when a translation is updated. This tasks runs by default 5 minutes after being triggered.

## String Extraction

Traduttore uses the [WP-CLI i18n command](https://github.com/wp-cli/i18n-command) to extract all available strings from your WordPress plugin or theme.

By default, it scans both PHP and JavaScript files looking for strings where the text domain matches the one of your project. By default, the "Text Domain" header of the plugin or theme is used.	If none is provided, it falls back to the project slug.

In some cases it might be needed to customize this behavior. Traduttore allows you to do so through a special configuration file, `traduttore.json`.

Right now, the following options are available:

* `mergeWith`: The path to an existing POT file in your project that strings should be extracted from as well.
* `textDomain`: An alternative text domain to override the default one.
* `exclude`: A list of files and paths that should be skipped for string extraction.
  Simple glob patterns can be used, i.e. `--exclude=foo-*.php` excludes any PHP file with the `foo-` prefix.
  Leading and trailing slashes are ignored, i.e. `/my/directory/` is the same as `my/directory`.
  The following files and folders are always excluded: node_modules, .git, .svn, .CVS, .hg, vendor, *.min.js.

Here's an example `traduttore.json` file:

```json
{
  "mergeWith": "languages/some-more-strings.pot",
  "textDomain": "foo",
  "exclude": [
    "some/file.php",
    "foo-directory"
  ]
}
```

**Note:** Alternatively you can provide this configuration by adding it to your `composer.json` file. Here's an example:

```json
{
  "extra": {
    "traduttore": {
      "mergeWith": "languages/some-more-strings.pot",
      "textDomain": "foo"
    }
  }
}
```

In the future, more options might be supported.
