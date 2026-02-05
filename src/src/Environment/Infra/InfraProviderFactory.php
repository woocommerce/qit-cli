<?php

declare( strict_types=1 );

namespace QIT_CLI\Environment\Infra;

/**
 * Factory for creating infrastructure providers.
 */
class InfraProviderFactory {
	/** Provider type: Local Docker environment */
	public const PROVIDER_LOCAL = 'local';

	/**
	 * All valid provider types for user input validation.
	 *
	 * @var array<string>
	 */
	public const ALLOWED_TYPES = [
		self::PROVIDER_LOCAL,
	];

	/** @var array<string, class-string<InfraProvider>> */
	protected array $providers = [
		self::PROVIDER_LOCAL => LocalProvider::class,
	];

	/**
	 * Create an infrastructure provider by type.
	 *
	 * @param string $type Provider type (e.g., 'local').
	 *
	 * @return InfraProvider
	 *
	 * @throws \InvalidArgumentException If type is unknown.
	 */
	public function create( string $type ): InfraProvider {
		if ( ! isset( $this->providers[ $type ] ) ) {
			throw new \InvalidArgumentException(
				sprintf(
					'Unknown provider type: %s. Available: %s',
					$type,
					implode( ', ', array_keys( $this->providers ) )
				)
			);
		}

		$class = $this->providers[ $type ];

		return new $class();
	}

	/**
	 * Get available provider types.
	 *
	 * @return array<string>
	 */
	public function get_available_types(): array {
		return array_keys( $this->providers );
	}

	/**
	 * Register a new provider type.
	 *
	 * @param string $type           Provider type identifier.
	 * @param string $provider_class Provider class name implementing InfraProvider.
	 */
	public function register( string $type, string $provider_class ): void {
		$this->providers[ $type ] = $provider_class;
	}
}
