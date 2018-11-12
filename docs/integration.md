# Integration

Using our little helper library called [Traduttore Registry](https://github.com/wearerequired/traduttore-registry), you can then tell WordPress that translations for your project should be loaded from Traduttore.

**Note:** Traduttore Registry requires PHP 7.0 or higher.

## Setting up Traduttore Registry

If you're using [Composer](https://getcomposer.org/) to manage dependencies, you can use the following command to add the library to your WordPress plugin or theme:

```bash
composer require wearerequired/traduttore
```

After that, you can use `Required\Traduttore_Registry\add_project( $type, $slug, $api_url )` in your theme or plugin. On a multisite install, it's recommend to use it in a must-use plugin instead.

**Note:** Alternatively, you could copy the library's code to your project.

`$type` can either be `plugin` or `theme`
`$slug` must match the theme/plugin directory slug
`$api_url` is the URL to the Traduttore project translation api

### Example

Here's an example of how you can use `add_project()` in your plugin or theme:

```php
\Required\Traduttore_Registry\add_project(
	'plugin',
	'example-plugin',
	'https://<home-url>/api/translations/acme/acme-plugin/'
);

\Required\Traduttore_Registry\add_project(
	'theme',
	'example-theme',
	'https://<home-url>/api/translations/acme/acme-theme/'
);
```

It's important that the slug matches the folder name of the plugin or theme. The URL is the one of the [api.md](Traduttore REST API), which should be publicly accessible.

Ideally you call `add_project()` in a function hooked to `init`, e.g. like this:

```php
function myplugin_init_traduttore() {
	\Required\Traduttore_Registry\add_project(
		'plugin',
		'example-plugin',
		'https://<home-url>/api/translations/acme/acme-plugin/'
	);
}
add_action( 'init', 'myplugin_init_traduttore' );
```
