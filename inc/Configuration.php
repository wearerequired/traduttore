<?php
/**
 * Configuration class
 *
 * @since 3.0.0
 */

namespace Required\Traduttore;

/**
 * Configuration class.
 *
 * @since 3.0.0
 *
 * @phpstan-type ProjectConfig array{ mergeWith?: string, textDomain?: string,exclude?: string[] }
 */
class Configuration {
	/**
	 * Repository path.
	 *
	 * @since 3.0.0
	 *
	 * @var string Repository path.
	 */
	protected string $path;

	/**
	 * Repository configuration.
	 *
	 * @since 3.0.0
	 *
	 * @var array Repository configuration.
	 *
	 * @phpstan-var ProjectConfig
	 */
	protected array $config = [];

	/**
	 * Class constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param string $path Repository path.
	 */
	public function __construct( string $path ) {
		$this->path = $path;

		$this->config = $this->load_config();
	}

	/**
	 * Returns the repository path.
	 *
	 * @since 3.0.0
	 *
	 * @return string $path Repository path.
	 */
	public function get_path(): string {
		return $this->path;
	}

	/**
	 * Returns the configuration array.
	 *
	 * @since 3.0.0
	 *
	 * @return array<string,string|string[]> Repository configuration.
	 *
	 * @phpstan-return ProjectConfig
	 */
	public function get_config(): array {
		return $this->config;
	}

	/**
	 * Returns a single config.
	 *
	 * @since 3.0.0
	 *
	 * @param string $key Config key.
	 * @return string|string[]|null Config value.
	 *
	 * @phpstan-template T of key-of<ProjectConfig>
	 * @phpstan-param T $key
	 * @phpstan-return ProjectConfig[T] | null
	 */
	public function get_config_value( string $key ): mixed {
		if ( isset( $this->config[ $key ] ) ) {
			return $this->config[ $key ];
		}

		return null;
	}

	/**
	 * Loads the configuration for the current path.
	 *
	 * @since 3.0.0
	 *
	 * @return array<string,string|string[]> Configuration data if found.
	 *
	 * @phpstan-return ProjectConfig
	 */
	protected function load_config(): array {
		$config_file   = trailingslashit( $this->path ) . 'traduttore.json';
		$composer_file = trailingslashit( $this->path ) . 'composer.json';

		if ( file_exists( $config_file ) ) {
			/**
			 * Traduttore configuration.
			 *
			 * @phpstan-var ProjectConfig $config
			 * @var array<string, string | string[]> $config
			 */
			$config = json_decode( (string) file_get_contents( $config_file ), true );

			if ( $config ) {
				return $config;
			}
		}

		if ( file_exists( $composer_file ) ) {
			/**
			 * Composer configuration.
			 *
			 * @phpstan-var array{extra?: array{ traduttore?: ProjectConfig } } $config
			 * @var array{extra?: array{ traduttore?: array<string, string | string[]> } } $config
			 */
			$config = json_decode( (string) file_get_contents( $composer_file ), true );

			if ( $config && isset( $config['extra']['traduttore'] ) ) {
				return $config['extra']['traduttore'];
			}
		}

		return [];
	}
}
