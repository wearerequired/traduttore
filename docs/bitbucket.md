---
layout: default
title: Bitbucket Repository Configuration
nav_order: 5
---

#  Bitbucket Repository Configuration

Traduttore supports both private and public Git repositories hosted on [Bitbucket.org](https:/bitbucket.org).

Mercurial repositores are not supported at this time. If you want to use Traduttore with Mercurial repositories, please [open an issue in our bug tracker](https://github.com/wearerequired/traduttore/issues).

## Repository Access

Traduttore connects to Bitbucket via either HTTPS or SSH to fetch a project's repository. If you're projects are not public, you need to make sure that the server has access to them by providing an SSH key. Ideally, you'd create a so-called [machine user](https://developer.github.com/v3/guides/managing-deploy-keys/#machine-users) for this purpose.

You can learn more about this at [Connecting to GitHub with SSH](https://help.github.com/articles/connecting-to-github-with-ssh/)

## Webhooks

To enable automatic string extraction from your Bitbucket projects, you need to create a new webhook for each of them.

1. In your repository, go to Settings -> Webhooks. You might need to enter your password.
2. Click on "Add webhook".
3. Enter a descriptive title and set `https://<url-to-your-glotpress-site>.com/wp-json/traduttore/v1/incoming-webhook` as the URL.
5. Make sure the `Status` is "Active"
6. Keep "Repository push" as the trigger.

Now, every time you push changes to Bitbucket, Traduttore will get notified and then attempts to update the project's translatable strings automatically.

**Note:** If you're using *Bitbucket Server*, you can optionally define a secret that should be sent with each request in the webhook settings. For this to work the `TRADUTTORE_BITBUCKET_SYNC_SECRET` constant needs to be defined in your `wp-config.php` file with the same secret.

Check out the [Configuration](configuration.md) section for a list of possible constants.
