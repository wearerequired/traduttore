# CLI Commands

Traduttore requires [WP-CLI](https://wp-cli.org/) 2.0 or newer to be installed on the server.

You can define `TRADUTTORE_WP_BIN` in your `wp-config.php` file to tell Traduttore where the WP-CLI executable is. The default is `wp`.

## Generate language packs

Generate language packs for one or more projects.

```bash
wp traduttore project build <project>
```

Language packs will automatically be updated upon translation changes.

This WP-CLI command is mostly useful for debugging / testing.

Use the `--force` flag to force ZIP file generation, even if there were no changes since the last build.

## Update translations from remote

Updates project translations from source code repository.

Pulls the latest changes, extracts translatable strings and imports them into GlotPress.

```bash
wp traduttore project update <project|repository_url>
```

Use the `--delete` flag to first delete the existing local repository.

## Clearing the cached source code repository

Removes the cached source code repository for a given project.

Useful when the local repository was somehow corrupted.

```bash
wp traduttore cache clear <project|repository_url>
````

## Show various details about a project

There's a command to print some helpful debug information about a given project.

This includes things like the text domain and repository URLs.

```bash
wp traduttore project info <project|repository_url>
```


## Show various details about the environment

There's a command to print some helpful debug information about Traduttore.

This includes things like the plugin version and path to the cache directory.

```bash
wp traduttore info
```
