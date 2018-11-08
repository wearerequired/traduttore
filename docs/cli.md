# CLI Commands

Traduttore requires [WP-CLI](https://wp-cli.org/) 2.0 or newer to be installed on the server.

You can define `TRADUTTORE_WP_BIN` in your `wp-config.php` file to tell Traduttore where the WP-CLI executable is. The default is `wp`.

## Generate language packs

Create ZIP files containing the translations for all translation sets of a project.

```bash
wp traduttore build <project>
```

Language packs will automatically be updated when the translations change. This WP-CLI command is mostly for debugging / testing. Use the `--force` flag to force ZIP file generation, even if there were no changes since the last build.

## Update translations from GitHub

Update a project's translatable string from a GitHub repository.

Pulls the latest changes from GitHub, extracts translatable strings and imports them into GitHub.

```bash
wp traduttore update <project|repository_url>
```

## Clearing the cached Git repository

Traduttore pulls the remote Git repository from GitHub and uses that for importing translatable strings.

If this local repository somehow gets broken, you can remove it via WP-CLI as follows:

```bash
wp traduttore cache clear <project|repository_url>
````
