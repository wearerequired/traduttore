<?php
/**
 * Configuration class.
 *
 * @since 3.0.0
 *
 * @package Required\Traduttore
 */

namespace Required\Traduttore;

/**
 * Configuration class.
 *
 * @since 3.0.0
 */
class Configuration {
	/**
	 * Repository path.
	 *
	 * @var string Repository path.
	 */
	protected $path;

	/**
	 * Repository configuration.
	 *
	 * @var array Repository configuration.
	 */
	protected $config = [];

	/**
	 * Class constructor.
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
	 * @return string $path Repository path.
	 */
	public function get_path() : string {
		return $this->path;
	}

	/**
	 * Returns the configuration array.
	 *
	 * @return array Repository configuration.
	 */
	public function get_config() : array {
		return $this->config;
	}

	/**
	 * Returns a single config.
	 *
	 * @param string $key Config key.
	 * @return mixed|null Config value.
	 */
	public function get_config_value( string $key ) {
		if ( isset( $this->config[ $key ] ) ) {
			return $this->config[ $key ];
		}

		return null;
	}

	/**
	 * Loads the configuration for the current path.
	 *
	 * @return array Configuration data if found.
	 */
	protected function load_config() : array {
		$config_file   = trailingslashit( $this->path ) . 'traduttore.json';
		$composer_file = trailingslashit( $this->path ) . 'composer.json';

		if ( file_exists( $config_file ) ) {
			$config = json_decode( file_get_contents( $config_file ), true );

			if ( $config ) {
				return $config;
			}
		}

		if ( file_exists( $composer_file ) ) {
			$config = json_decode( file_get_contents( $composer_file ), true );

			if ( $config && isset( $config['extra'], $config['extra']['traduttore'] ) ) {
				return $config['extra']['traduttore'];
			}
		}

		return [];
	}
}
