---
layout: default
title: GitHub Repository Configuration
nav_order: 6
---

# GitHub Repository Configuration

Traduttore supports both private and public Git repositories hosted on [GitHub.com](https://github.com).

## Repository Access

Traduttore connects to GitHub via either HTTPS or SSH to fetch a project's repository. If you're projects are not public, you need to make sure that the server has access to them by providing an SSH key. Ideally, you'd create a so-called [machine user](https://developer.github.com/v3/guides/managing-deploy-keys/#machine-users) for this purpose.

You can learn more about this at [Connecting to GitHub with SSH](https://help.github.com/articles/connecting-to-github-with-ssh/)

## Webhooks

To enable automatic string extraction from your GitHub projects, you need to create a new webhook for each of them.

1. In your repository, go to Settings -> Webhooks. You might need to enter your password.
2. Click on "Add webhook".
3. Set `https://<url-to-your-glotpress-site>.com/wp-json/traduttore/v1/incoming-webhook` as the payload URL.
4. Choose `application/json` as the content type.
5. Enter and remember a secret key.
6. In the "Which events would you like to trigger this webhook?" section, select only the `push` event.

Now, every time you push changes to GitHub, Traduttore will get notified and then attempts to update the project's translatable strings automatically.

**Note:** The `TRADUTTORE_GITHUB_SYNC_SECRET` constant needs to be defined in your `wp-config.php` file to enable webhooks. Use the secret from step 5 for this.

Check out the [Configuration](configuration.md) section for a list of possible constants.

## GitHub App

As an alternative to configuring webhooks for each repository individually, you can create a GitHub App. This way the app automatically has access to all repositories (or the ones you select during installation), removing the need to set up webhooks per repository.

### Creating the App

1. Go to `https://github.com/organizations/<name>/settings/apps/new` (or `https://github.com/settings/apps/new` for a personal account).
2. Enter a name and description for the app.
3. In the **Webhook** section, check the "Active" checkbox.
4. Set `https://<url-to-your-glotpress-site>.com/wp-json/traduttore/v1/incoming-webhook` as the webhook URL.
5. Enter a webhook secret. Use the same value as the `TRADUTTORE_GITHUB_SYNC_SECRET` constant in your `wp-config.php`.
6. In the **Repository permissions** section, set "Contents" to "Read-only".
7. In the **Subscribe to events** section, check the "Push" checkbox.
8. Under "Where can this GitHub App be installed?", choose "Only on this account" for a private app or "Any account" if you want others to install it.

### Installing the App

Once the app is created, install it on your account or organization. During installation you can grant access to all repositories or select specific ones. After installation, Traduttore will automatically receive push events for the configured repositories without any additional webhook setup.
