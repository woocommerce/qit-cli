<?php

declare( strict_types=1 );

namespace QIT_CLI\Environment\Infra;

/**
 * Factory for creating infrastructure providers.
 */
class InfraProviderFactory {
	/** @var array<string, class-string<InfraProvider>> */
	protected array $providers = [
		'local' => LocalProvider::class,
		// 'cloud' => CloudProvider::class, // Added in Phase 5 (QIT-882)
	];

	/**
	 * Create an infrastructure provider by type.
	 *
	 * @param string $type Provider type ('local' or 'cloud').
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
