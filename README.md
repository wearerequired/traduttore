# Traduttore

Customizations for translate.required.com.

## Usage

### CLI

#### Generate ZIP files

Easily create ZIP files containing the translations for all translation sets of a project.

```bash
wp traduttore generate_zip <project>
```

ZIP files will automatically be updated when the translations change. This WP-CLI command is mostly for debugging / testing.

#### Update translations from GitHub

Easily update a project's translatable string from a GitHub repository.

```bash
wp traduttore update_from_github <repository_url>
```

This step can be automated by setting up a new webhook on GitHub. To do this, follow these steps:

1. In your repository, go to Settings -> Webhooks. You might need to enter your password.
2. Click on "Add webhook".
3. Set `https://translate.required.com/wp-json/github-webhook/v1/push-event` as the payload URL.
4. Choose `application/json` as the content type.
5. Enter the secret key that can be found in 1Password.
6. Leave the other options as is and submit the form.

Now, every time you push changes to GitHub, Traduttore will get notified and then attempts to update the project's translatable strings automatically.
