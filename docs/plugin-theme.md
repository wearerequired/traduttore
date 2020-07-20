---
layout: default
title: Plugin & Theme Integration
nav_order: 9
---

# Plugin & Theme Integration

Using our little helper library called [Traduttore Registry](https://github.com/wearerequired/traduttore-registry), you can then tell WordPress that translations for your project should be loaded from Traduttore.

**Note:** Traduttore Registry requires PHP 7.1 or higher.

## Setting up Traduttore Registry

If you're using [Composer](https://getcomposer.org/) to manage dependencies, you can use the following command to add the library to your WordPress plugin or theme:

```bash
composer require wearerequired/traduttore-registry
```

After that, you can use `Required\Traduttore_Registry\add_project( $type, $slug, $api_url )` in your theme or plugin.

**Note:** Alternatively, you could copy the library's code to your project. Also, on a multisite install it's recommended to use it in a must-use plugin.

**Parameters:**

* `$type`: either `plugin` or `theme`.
* `$slug`: must match the theme/plugin directory slug.
* `$api_url`: the URL to the Traduttore project translation API.

### Example

Here's an example of how you can use `add_project()` in your plugin or theme:

```php
\Required\Traduttore_Registry\add_project(
	'plugin',
	'example-plugin',
	'https://<glotpress-url>/api/translations/acme/acme-plugin/'
);

\Required\Traduttore_Registry\add_project(
	'theme',
	'example-theme',
	'https://<glotpress-url>/api/translations/acme/acme-theme/'
);
```

Replace `glotpress-url` with the home URL of your site and the base path to the GlotPress installation, by default `/glotpress`.  
It's important that the slug matches the folder name of the plugin or theme. The URL is the one of the [Traduttore REST API](api.md), which should be publicly accessible.

Ideally you call `add_project()` in a function hooked to `init`, e.g. like this:

```php
function myplugin_init_traduttore() {
	\Required\Traduttore_Registry\add_project(
		'plugin',
		'example-plugin',
		'https://<glotpress-url>/api/translations/acme/acme-plugin/'
	);
}
add_action( 'init', 'myplugin_init_traduttore' );
```
