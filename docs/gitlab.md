---
layout: default
title: GitLab Repository Configuration
nav_order: 7
---

# GitLab Repository Configuration

Traduttore supports both private and public Git repositories hosted on [GitLab.com](https://gitlab.com) as well as self-managed GitLab instances.

## Repository Access

Traduttore connects to GitLab via either HTTPS or SSH to fetch a project's repository. If you're projects are not public, you need to make sure that the server has access to them by providing an SSH key. Ideally, you'd create a so-called [machine user](https://developer.github.com/v3/guides/managing-deploy-keys/#machine-users) for this purpose.

You can learn more about this at [Connecting to GitHub with SSH](https://help.github.com/articles/connecting-to-github-with-ssh/)

## Webhooks

To enable automatic string extraction from your GitLab projects, you need to create a new webhook for each of them.

1. In your repository, go to Settings -> Integrations.
3. Enter `https://<url-to-your-glotpress-site>.com/wp-json/traduttore/v1/incoming-webhook` as the URL.
5. Enter the secret token defined in `TRADUTTORE_GITLAB_SYNC_SECRET`.
6. In the "Trigger" section, select only `Push events`.

Now, every time you push changes to GitLab, Traduttore will get notified and then attempts to update the project's translatable strings automatically.

**Note:** The `TRADUTTORE_GITLAB_SYNC_SECRET` constant needs to be defined in your `wp-config.php` file to enable webhooks. Use the secret from step 5 for this.

Check out the [Configuration](configuration.md) section for a list of possible constants.

## Self-managed GitLab

Some people prefer to install GitLab on their own system instead of using [GitLab.com](https://gitlab.com).

Traduttore tries to automatically recognize self-managed repositories to the best of its ability. As soon as it receives a webhook for a repository, it stores all needed information in the database for later use.

If no incoming webhooks are set up or received, some manual configuration is still involved. Here's how you can tell Traduttore how to properly locate your repository in that case:

Let's say your GitLab instance is available via `gitlab.example.com`. To tell Traduttore this should be treated as such, you can hook into the `traduttore.repository` filter to do so. Here's an example:

```php
class MySelfhostedGitLabRepository extends \Required\Traduttore\Repository\GitLab {
	/**
	 * GitLab API base URL.
	 *
	 * Used to access information about a repository's visibility level.
	 */
	public const API_BASE = 'https://gitlab.example.com/api/v4';
}

/**
 * Filters the repository information Traduttore uses for self-managed GitLab repositories.
 *
 * @param \Required\Traduttore\Repository|null $repository Repository instance.
 * @param \Required\Traduttore\Project         $project    Project information.
 * @return \Required\Traduttore\Repository|null Filtered Repository instance.
 */
function myplugin_filter_traduttore_repository( \Required\Traduttore\Repository $repository = null, \Required\Traduttore\Project $project ) {
	$url  = $project->get_source_url_template();
	$host = $url ? wp_parse_url( $url, PHP_URL_HOST ) : null;

	if ( 'gitlab.example.com' === $host ) {
		return new MySelfhostedGitLabRepository( $project );
	}

	return $repository;
}

add_filter( 'traduttore.repository', 'myplugin_filter_traduttore_repository', 10, 2 );
```

That's all. This way Traduttore knows that `gitlab.example.com` hosts a GitLab instance and that it can download repositories hosted there using the built-in Git loader.

Ideally, you put this code into a custom WordPress plugin in your WordPress site that runs Traduttore.

[Learn more about developing WordPress plugins](https://developer.wordpress.org/plugins/).

In the future, this step might be replaced by a WP-CLI command or an extended settings UI in GlotPress.
