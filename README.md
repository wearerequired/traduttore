# Traduttore

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

## Installation

If you're using [Composer](https://getcomposer.org/) to manage dependencies, you can use the following command to add the plugin to your site:

```bash
composer require wearerequired/traduttore
```

Alternatively, you can download a ZIP file containing the plugin on [GitHub](https://github.com/wearerequired/traduttore).

### Webhooks

To enable automatic string extraction from your GitHub projects, you need to create a new webhook for each of them.

1. In your repository, go to Settings -> Webhooks. You might need to enter your password.
2. Click on "Add webhook".
3. Set `https://<url-to-your-glotpress-site>.com/wp-json/github-webhook/v1/push-event` as the payload URL.
4. Choose `application/json` as the content type.
5. Enter and remember a secret key.
6. In the "Which events would you like to trigger this webhook?" section, select only the `push` event.

Now, every time you push changes to GitHub, Traduttore will get notified and then attempts to update the project's translatable strings automatically.

Note: `TRADUTTORE_GITHUB_SYNC_SECRET` needs to be defined in your `wp-config.php` file to enable webhooks. Use the secret from step 5 for this.

## Usage

### CLI

#### Generate ZIP files

Create ZIP files containing the translations for all translation sets of a project.

```bash
wp traduttore build <project>
```

ZIP files will automatically be updated when the translations change. This WP-CLI command is mostly for debugging / testing.

#### Update translations from GitHub

Update a project's translatable string from a GitHub repository.

Pulls the latest changes from GitHub, extracts translatable strings and imports them into GitHub.

```bash
wp traduttore update <project|repository_url>
```

#### Clearing the cached Git repository

Traduttore pulls the remote Git repository from GitHub and uses that for importing translatable strings.

If this local repository somehow gets broken, you can remove it via WP-CLI as follows:

```
wp traduttore cache clear <project|repository_url>
````
