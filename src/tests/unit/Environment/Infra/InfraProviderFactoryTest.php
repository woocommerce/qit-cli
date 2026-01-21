<?php

declare( strict_types=1 );

namespace QIT_CLI_Tests\Environment\Infra;

use PHPUnit\Framework\TestCase;
use QIT_CLI\Environment\Infra\InfraProvider;
use QIT_CLI\Environment\Infra\InfraProviderFactory;
use QIT_CLI\Environment\Infra\LocalProvider;

class InfraProviderFactoryTest extends TestCase {
	public function test_create_local_provider(): void {
		$factory  = new InfraProviderFactory();
		$provider = $factory->create( 'local' );

		$this->assertInstanceOf( LocalProvider::class, $provider );
		$this->assertInstanceOf( InfraProvider::class, $provider );
	}

	public function test_create_throws_for_unknown_type(): void {
		$factory = new InfraProviderFactory();

		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Unknown provider type: unknown' );

		$factory->create( 'unknown' );
	}

	public function test_create_exception_lists_available_types(): void {
		$factory = new InfraProviderFactory();

		try {
			$factory->create( 'invalid' );
			$this->fail( 'Expected InvalidArgumentException was not thrown' );
		} catch ( \InvalidArgumentException $e ) {
			$this->assertStringContainsString( 'Available: local', $e->getMessage() );
		}
	}

	public function test_get_available_types(): void {
		$factory = new InfraProviderFactory();
		$types   = $factory->get_available_types();

		$this->assertContains( 'local', $types );
	}

	public function test_register_custom_provider(): void {
		$factory = new InfraProviderFactory();
		$factory->register( 'custom', LocalProvider::class );

		$this->assertContains( 'custom', $factory->get_available_types() );
	}

	public function test_register_can_override_existing_type(): void {
		$factory = new InfraProviderFactory();

		// Create a mock provider class reference (use LocalProvider as a stand-in)
		$factory->register( 'local', LocalProvider::class );

		$provider = $factory->create( 'local' );
		$this->assertInstanceOf( LocalProvider::class, $provider );
	}

	public function test_create_returns_new_instance_each_time(): void {
		$factory   = new InfraProviderFactory();
		$provider1 = $factory->create( 'local' );
		$provider2 = $factory->create( 'local' );

		$this->assertNotSame( $provider1, $provider2 );
	}
}
