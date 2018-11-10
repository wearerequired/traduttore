# Hooks and Filters

All WordPress hooks and filters provided by Traduttore are prefixed with `traduttore.`.

## Action Hooks

### `traduttore.updated`

**Since:** 3.0.0

Fires after translations have been updated.

**Parameters:**

* `$project`: The project that was updated.
* `$stats`: Stats about the number of imported translations.
* `$translations`: PO object containing all the translations from the POT file.

----

### `traduttore.zip_generated`

**Since:** 3.0.0

Fires after a language pack for a given translation set has been generated.

**Parameters:**

* `$file`: Path to the generated language pack.
* `$url`: URL to the generated language pack.
* `$translation_set`: Translation set the language pack is for.

## Filters

### `traduttore.git_clone_use_https`

**Since:** 3.0.0

Filters whether HTTPS or SSH should be used to clone a repository.

**Parameters:**

* `$use_https`: Whether to use HTTPS or SSH. Defaults to HTTPS for public repositories.
* `$repository`: The current repository.

----

### `traduttore.git_clone_url`

**Since:** 3.0.0

Filters the URL used to clone a Git repository.

**Parameters:**

* `$clone_url`: The URL to clone a Git repository.
* `$repository`: The current repository.

----

### `traduttore.git_https_credentials`

**Since:** 3.0.0

Filters the credentials to be used for connecting to a Git repository via HTTPS.

**Parameters:**

* `$credentials`: Git credentials in the form `username:password`. Default empty string.
* `$repository`: The current repository.

----

### `traduttore.zip_generated_send_notification`

**Since:** 3.0.0

Filters whether a Slack notification for translation updates from GitHub should be sent.

**Parameters:**

* `$send_message`: Whether to send a notification or not. Default true.
* `$translation_set`: Translation set the language pack is for.
* `$project`: The project that was updated.

----

### `traduttore.zip_generated_notification_message`

**Since:** 3.0.0

Filters the Slack notification message for when a new language pack has been built.

**Parameters:**

* `$message`: The notification message.
* `$translation_set`: Translation set the language pack is for.
* `$project`: The project that was updated.

----

### `traduttore.updated_send_notification`

**Since:** 3.0.0

Filters whether a Slack notification for translation updates from GitHub should be sent.

Make sure to set up Slack notifications first, as outlined in the [Notifications](notifications.md) section.

**Parameters:**

* `$send_message`: Whether to send a notification or not. Defaults to true, unless there were no string changes at all.
* `$project`: The project that was updated.
* `$stats`: Stats about the number of imported translations.

----

### `traduttore.updated_notification_message`

**Since:** 3.0.0

Filters the Slack notification message when new translations are updated.

**Parameters:**

* `$message`: The notification message.
* `$project`: The project that was updated.
* `$stats`: Stats about the number of imported translations.

----

### `traduttore.generate_zip_delay`

**Since:** 3.0.0

Filters the delay for scheduled language pack generation.

**Parameters:**

* `$delay`: Delay in minutes. Default is 5 minutes.
* `$translation_set`: Translation set the ZIP generation will be scheduled for.

----

### `traduttore.update_delay`

**Since:** 3.0.0

Filters the delay for scheduled project updates.

**Parameters:**

* `$delay`: Delay in minutes. Default is 3 minutes.
* `$project`: The current project.

----

### `traduttore.webhook_secret`

**Since:** 3.0.0

Filters the sync secret for an incoming webhook request.

**Parameters:**

* `$secret`: Webhook sync secret.
* `$handler`: The current webhook handler instance.
* `$project`: The current project if found.
