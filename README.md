# Traduttore

WordPress plugin that enables you to translate your WordPress projects in GlotPress and automatically install these translations.

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

```bash
wp traduttore update <project|repository_url>
```

This step can be automated by setting up a new webhook on GitHub. To do this, follow these steps:

1. In your repository, go to Settings -> Webhooks. You might need to enter your password.
2. Click on "Add webhook".
3. Set `https://<url-to-your-glotpress-site>.com/wp-json/github-webhook/v1/push-event` as the payload URL.
4. Choose `application/json` as the content type.
5. Enter and remember a secret key.
6. In the "Which events would you like to trigger this webhook?" section, select only the `push` event.

Now, every time you push changes to GitHub, Traduttore will get notified and then attempts to update the project's translatable strings automatically.

Note: the constant `TRADUTTORE_GITHUB_SYNC_SECRET` needs to be defined in your `wp-config.php` file to enable webhooks. Use the secret from step 5 for this.

#### Clearing the cached Git repository

Traduttore pulls the remote Git repository from GitHub and uses that for importing translatable strings.

If this local repository somehow gets broken, you can remove it via WP-CLI as follows:

```
wp traduttore cache clear <project|repository_url>
````
