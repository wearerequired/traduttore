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
