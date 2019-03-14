<?php
/**
 * CLI command namespace class.
 *
 * @since 3.0.0
 *
 * @package Required\Traduttore\CLI
 */

namespace Required\Traduttore\CLI;

/**
 * Manages Traduttore projects.
 *
 * ## EXAMPLES
 *
 *     # Display various data about the Traduttore environment.
 *     $ wp traduttore info
 *     Traduttore version:     3.0.0-alpha
 *     WordPress version:      4.9.8
 *     GlotPress version:      2.3.1
 *     WP-CLI version:         2.0.1
 *     WP-CLI binary path:     /usr/local/bin/wp
 *     Git binary path:        /usr/bin/git
 *     Mercurial binary path:  (not found)
 *     Subversion binary path: (not found)
 *     Cache directory:        /var/www/network.required.com/wp-content/traduttore
 *
 *     # Display various data about a given project.
 *     $ wp traduttore project info foo
 *     Project ID:            1
 *     Project name:          Foo Project
 *     Project slug:          foo
 *     Version:               1.0.1
 *     Text domain:           foo-plugin
 *     Last updated:          2018-11-11 11:11:11
 *     Repository Cache:      /tmp/traduttore-github.com-wearerequired-foo
 *     Repository URL:        (unknown)
 *     Repository Type:       github
 *     Repository VCS Type:   (unknown)
 *     Repository Visibility: private
 *     Repository SSH URL:    git@github.com:wearerequired/foo.git
 *     Repository HTTPS URL:  https://github.com/wearerequired/foo.git
 *     Repository Instance:   Required\Traduttore\Repository\GitHub
 *     Loader Instance:       Required\Traduttore\Loader\Git
 *
 *     # Generate language packs for the project with ID 123.
 *     $ wp traduttore project build 123
 *     Language pack generated for translation set (ID: 1)
 *     Language pack generated for translation set (ID: 3)
 *     Language pack generated for translation set (ID: 7)
 *     Success: Language pack generation finished
 *
 *     # Remove cached repository for given project ID.
 *     $ wp traduttore project cache clear 123
 *     Success: Removed cached Git repository for project (ID: 123)!
 */
class CommandNamespace extends \WP_CLI\Dispatcher\CommandNamespace {

}
