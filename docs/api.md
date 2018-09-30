---
id: api
title: REST API
---

Traduttore adds new routes to both the WordPress REST API as well as the API provided by GlotPress.

## REST API

### `/github-webhook/v1/push-event`

**Methods:**

* `POST`

Traduttore can be set up to listen to incoming webhooks from GitHub. This way, translations can be updated every time you push changes to your GitHub repository.

Check out the [Getting Started](installation.md) guide to learn how to set up webhooks.

## `/api/translations/<project>`

**Methods:**

* `GET`

This API route is used to distribute all the available language packs for a given project. This way, a WordPress site can be configured to download translations for a specific plugin via the API.

**Example:**

Fetching `https://<home_url>/api/translations/my-project` would result in a response like this:

```json
{
  "translations": [
    {
      "language": "de_DE",
      "version": "1.0",
      "updated": false,
      "english_name": "German",
      "native_name": "Deutsch",
      "package": "https://<home_url>/content/traduttore/my-project-de_DE.zip",
      "iso": [
        "de"
      ]
    }
  ]
}
```