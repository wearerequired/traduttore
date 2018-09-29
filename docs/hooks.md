---
id: hooks
title: Hooks and Filters
sidebar_label: Available Hooks
---

All WordPress hooks and filters provided by Traduttore are prefixed with `traduttore.`.

## Action Hooks

### `traduttore.updated`

**Since:** 3.0.0

Fires after translations have been updated.

**Parameters:**

* `$project`: The GlotPress project that was updated.
* `$stats`: Stats about the number of imported translations.
* `$translations`: PO object containing all the translations from the POT file.

----

### `traduttore.zip_generated`

**Since:** 3.0.0

Fires after a ZIP file for a given translation set has been generated.

**Parameters:**

* `$zip_file`: Path to the generated ZIP file.
* `$zip_url`: URL to the generated ZIP file.
* `$translation_set`: Translation set the ZIP is for.

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

**Since:** 2.0.3

Filters whether a Slack notification for translation updates from GitHub should be sent.

**Parameters:**

* `$send_message`: Whether to send a notification or not. Default true.
* `$translation_set`: Translation set the ZIP is for.
* `$project`: The GlotPress project that was updated.

----

### `traduttore.zip_generated_notification_message`

**Since:** 2.0.3

Filters the Slack notification message when a new translation ZIP file is built.

**Parameters:**

* `$message`: The notification message.
* `$translation_set`: Translation set the ZIP is for.
* `$project`: The GlotPress project that was updated.

----

### `traduttore.updated_send_notification`

**Since:** 2.0.3

Filters whether a Slack notification for translation updates from GitHub should be sent.

**Parameters:**

* `$send_message`: Whether to send a notification or not. Defaults to true, unless there were no string changes at all.
* `$project`: The GlotPress project that was updated.
* `$stats`: Stats about the number of imported translations.

----

### `traduttore_updated_from_github_notification_message`

**Since:** 2.0.3

Filters the Slack notification message when new translations are updated from GitHub.

**Parameters:**

* `$message`: The notification message.
* `$project`: The GlotPress project that was updated.
* `$stats`: Stats about the number of imported translations.
