<?php
/**
 * Command for printing various details about the environment.
 *
 * @since 3.0.0
 *
 * @package Required\Traduttore\CLI
 */

namespace Required\Traduttore\CLI;

use Required\Traduttore\ZipProvider;
use WP_CLI;
use WP_CLI_Command;

/**
 * Environment information command.
 *
 * @since 3.0.0
 */
class InfoCommand extends WP_CLI_Command {
	/**
	 * Print various details about the environment.
	 *
	 * ## OPTIONS
	 *
	 * [--format=<format>]
	 * : Render output in a particular format.
	 * ---
	 * default: list
	 * options:
	 *   - list
	 *   - json
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     # Display various data about the Traduttore environment
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
	 * @since 3.0.0
	 *
	 * @param array $args Command args.
	 * @param array $assoc_args Associative args.
	 */
	public function __invoke( $args, $assoc_args ) {
		$plugin_version = \Required\Traduttore\VERSION;
		$wp_version     = get_bloginfo( 'version' );
		$gp_version     = GP_VERSION;

		$wp_cli_version = WP_CLI_VERSION;
		$git_binary     = $this->get_git_binary_path();
		$hg_binary      = $this->get_hg_binary_path();
		$svn_binary     = $this->get_svn_binary_path();
		$wp_cli_binary  = $this->get_wp_binary_path();
		$cache_dir      = ZipProvider::get_cache_dir();

		if ( \WP_CLI\Utils\get_flag_value( $assoc_args, 'format' ) === 'json' ) {
			$info = array(
				'traduttore_version' => $plugin_version,
				'wp_version'         => $wp_version,
				'gp_version'         => $gp_version,
				'wp_cli_version'     => $wp_cli_version,
				'wp_cli_path'        => $wp_cli_binary,
				'git_path'           => $git_binary,
				'hg_path'            => $hg_binary,
				'svn_path'           => $svn_binary,
				'cache_dir'          => $cache_dir,
			);

			WP_CLI::line( json_encode( $info ) );
		} else {
			WP_CLI::line( "Traduttore version:\t" . $plugin_version );
			WP_CLI::line( "WordPress version:\t" . $wp_version );
			WP_CLI::line( "GlotPress version:\t" . $gp_version );
			WP_CLI::line( "WP-CLI version:\t\t" . $wp_cli_version );
			WP_CLI::line( "WP-CLI binary path:\t" . $wp_cli_binary );
			WP_CLI::line( "Git binary path:\t" . ( $git_binary ?: '(not found)' ) );
			WP_CLI::line( "Mercurial binary path:\t" . ( $hg_binary ?: '(not found)' ) );
			WP_CLI::line( "Subversion binary path:\t" . ( $svn_binary ?: '(not found)' ) );
			WP_CLI::line( "Cache directory:\t" . $cache_dir );
		}
	}

	/**
	 * Returns the path to the Git binary.
	 *
	 * @since 3.0.0
	 *
	 * @return null|string Binary path on success, null otherwise.
	 */
	protected function get_git_binary_path(): ?string {
		exec(
			escapeshellcmd( 'which git' ),
			$output,
			$status
		);

		return 0 === $status ? $output[0] : null;
	}

	/**
	 * Returns the path to the Mercurial binary.
	 *
	 * @since 3.0.0
	 *
	 * @return null|string Binary path on success, null otherwise.
	 */
	protected function get_hg_binary_path(): ?string {
		exec(
			escapeshellcmd( 'which hg' ),
			$output,
			$status
		);

		return 0 === $status ? $output[0] : null;
	}

	/**
	 * Returns the path to the Subversion binary.
	 *
	 * @since 3.0.0
	 *
	 * @return null|string Binary path on success, null otherwise.
	 */
	protected function get_svn_binary_path(): ?string {
		exec(
			escapeshellcmd( 'which svn' ),
			$output,
			$status
		);

		return 0 === $status ? $output[0] : null;
	}

	/**
	 * Returns the path to the WP-CLI binary.
	 *
	 * @since 3.0.0
	 *
	 * @return null|string Binary path on success, null otherwise.
	 */
	protected function get_wp_binary_path(): ?string {
		if ( defined( 'TRADUTTORE_WP_BIN' ) && TRADUTTORE_WP_BIN ) {
			return TRADUTTORE_WP_BIN;
		}

		exec(
			escapeshellcmd( 'which wp' ),
			$output,
			$status
		);

		return 0 === $status ? $output[0] : null;
	}
}
