#  GitLab Repository Configuration

Traduttore supports both private and public Git repositories hosted on [GitLab.com](https://gitlab.com) as well as self-managed GitLab instances.

## Repository Access

Traduttore connects to GitLab via either HTTPS or SSH to fetch a project's repository. If you're projects are not public, you need to make sure that the server has access to them by providing an SSH key. Ideally, you'd create a so-called [machine user](https://developer.github.com/v3/guides/managing-deploy-keys/#machine-users) for this purpose.

You can learn more about this at [Connecting to GitHub with SSH](https://help.github.com/articles/connecting-to-github-with-ssh/)

## Webhooks

To enable automatic string extraction from your GitLab projects, you need to create a new webhook for each of them.

1. In your repository, go to Settings -> Integrations. You might need to enter your password.
2. Click on "Add webhook".
3. Set `https://<url-to-your-glotpress-site>.com/wp-json/traduttore/v1/incoming-webhook` as the payload URL.
5. Enter the secret token defined in `TRADUTTORE_GITLAB_SYNC_SECRET`.
6. In the "Trigger" section, select only `Push events`.

Now, every time you push changes to GitLab, Traduttore will get notified and then attempts to update the project's translatable strings automatically.

**Note:** The `TRADUTTORE_GITLAB_SYNC_SECRET` constant needs to be defined in your `wp-config.php` file to enable webhooks. Use the secret from step 5 for this.

Check out the [Configuration](configuration.md) section for a list of possible constants.

## Self-managed GitLab

Some people prefer to install GitLab on their own system instead of using [GitLab.com](https://gitlab.com).

Unfortunately, Traduttore does not yet automatically recognize self-managed repositories, which means there is some manual configuration involved.

Let's say your GitLab instance is available via `gitlab.example.com`. To tell Traduttore this should be treated as such, you can hook into the `traduttore.repository` filter to do so. Here's an example:

```php
class MySelfhostedGitLabRepository extends \Required\Traduttore\Repository\GitLab {
	/**
	 * Indicates whether a GitLab repository is publicly accessible or not.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether the repository is publicly accessible.
	 */
	public function is_public() : bool {
		$response = wp_remote_head( 'https://gitlab.example.com/api/v4/projects/' . rawurlencode( $this->get_name() ) );

		return 200 === wp_remote_retrieve_response_code( $response );
	}
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

Ideally, you put this code into a custom WordPress plugin in your WordPress site that runs Traduttore.

[Learn more about developing WordPress plugins](https://developer.wordpress.org/plugins/).
