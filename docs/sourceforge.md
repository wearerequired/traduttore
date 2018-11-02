#  SourceForge Repository Configuration

Traduttore supports both private and public source code repositories hosted on [SourceForge.com](https://sourceforge.net).

SourceForge simultaneously supports Git, Mercurial, and Subversion to access their repositories, and you can choose whichever system you want when setting up a project in Traduttore. 

## Repository Access

Traduttore connects to SourceForge via either HTTPS or SSH to fetch a project's repository. If you're projects are not public, you need to make sure that the server has access to them by providing an SSH key.

You can learn more about this at [SourceForge's SSH Key Overview](https://sourceforge.net/p/forge/documentation/SSH%20Keys/)

## Webhooks

To enable automatic string extraction from your SourceForge projects, you need to create a new webhook for each of them. Webhooks are available for Git, SVN, and Mercurial repositories.

1. In your repository, expand the Admin section in the left menu. Then, click on the "Webhooks" link.
2. Under the `repo-push` label, click on "Create".
3. Set `https://<url-to-your-glotpress-site>.com/wp-json/traduttore/v1/incoming-webhook` as the payload URL.
5. Enter the secret token defined in `TRADUTTORE_SOURCEFORGE_SYNC_SECRET` or leave empty to generate one automatically (make sure to update the constant accordingly).

Now, every time you push changes to SourceForge, Traduttore will get notified and then attempts to update the project's translatable strings automatically.

**Note:** The `TRADUTTORE_SOURCEFORGE_SYNC_SECRET` constant needs to be defined in your `wp-config.php` file to enable webhooks. Use the secret from step 5 for this.

Check out the [Configuration](configuration.md) section for a list of possible constants.
