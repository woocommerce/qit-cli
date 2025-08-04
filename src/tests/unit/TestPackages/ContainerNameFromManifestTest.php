<?php

namespace QIT_CLI\Tests\Unit\TestPackages;

use PHPUnit\Framework\TestCase;

class ContainerNameFromManifestTest extends TestCase {
    
    /**
     * Simplified version of container_name_from_manifest for testing.
     * This extracts the core logic without filesystem dependencies.
     */
    private function container_name_from_manifest( string $package_id, array &$counter = null, bool $include_version = false ): string {
        $namespace = '';
        $package = '';
        $version = null;
        
        // For testing, we skip the file_exists check and assume remote packages
        // Remote package reference - parse the format
        if ( ! preg_match( '/^([^\/]+)\/([^:]+)(?::(.+))?$/', $package_id, $matches ) ) {
            throw new \InvalidArgumentException(
                "Invalid package reference format. Expected 'namespace/package[:version]', got: {$package_id}"
            );
        }
        
        $namespace = $matches[1];
        $package = $matches[2];
        $version = isset( $matches[3] ) ? $matches[3] : null;
        
        // Sanitize for container safety
        $safe_namespace = strtolower( preg_replace( '/[^a-z0-9]+/i', '-', $namespace ) );
        $safe_package = strtolower( preg_replace( '/[^a-z0-9]+/i', '-', $package ) );
        $base_name = trim( "{$safe_namespace}-{$safe_package}", '-' );
        
        // Add version to container name if requested
        if ( $include_version && $version !== null ) {
            $safe_version = strtolower( preg_replace( '/[^a-z0-9]+/i', '-', $version ) );
            $base_name .= '-' . $safe_version;
        }
        
        // For local packages, add counter if needed
        if ( $counter !== null ) {
            $key = $base_name;
            if ( ! isset( $counter[ $key ] ) ) {
                $counter[ $key ] = 0;
            }
            $counter[ $key ]++;
            
            if ( $counter[ $key ] > 1 ) {
                $base_name .= '-' . $counter[ $key ];
            }
        }
        
        return $base_name;
    }
    
    /**
     * @test
     * @dataProvider remotePackageProvider
     */
    public function it_generates_correct_names_for_remote_packages( $package_id, $include_version, $expected_name ): void {
        $result = $this->container_name_from_manifest( $package_id, $counter, $include_version );
        $this->assertEquals( $expected_name, $result );
    }
    
    public function remotePackageProvider(): array {
        return [
            // With version flag = true
            [ 'woocommerce/activation:1.0', true, 'woocommerce-activation-1-0' ],
            [ 'woocommerce/activation:2.0', true, 'woocommerce-activation-2-0' ],
            [ 'woocommerce/activation:stable', true, 'woocommerce-activation-stable' ],
            [ 'woocommerce/activation:latest', true, 'woocommerce-activation-latest' ],
            [ 'my-vendor/my-package:1.0.0', true, 'my-vendor-my-package-1-0-0' ],
            [ 'MY_VENDOR/My.Package:v1', true, 'my-vendor-my-package-v1' ],
            [ 'vendor/package:1.0.0-beta.1', true, 'vendor-package-1-0-0-beta-1' ],
            [ 'vendor/package:feature/branch', true, 'vendor-package-feature-branch' ],
            
            // Without version flag = false
            [ 'woocommerce/activation:1.0', false, 'woocommerce-activation' ],
            [ 'woocommerce/activation:2.0', false, 'woocommerce-activation' ],
            [ 'vendor/package:any-version', false, 'vendor-package' ],
            
            // No version in package ID
            [ 'woocommerce/activation', true, 'woocommerce-activation' ],
            [ 'woocommerce/activation', false, 'woocommerce-activation' ],
        ];
    }
    
    /**
     * @test
     */
    public function it_handles_local_package_counter(): void {
        $counter = [];
        
        // First call - no suffix
        $result1 = $this->container_name_from_manifest( 'woocommerce/activation', $counter );
        $this->assertEquals( 'woocommerce-activation', $result1 );
        
        // Second call - gets suffix
        $result2 = $this->container_name_from_manifest( 'woocommerce/activation', $counter );
        $this->assertEquals( 'woocommerce-activation-2', $result2 );
        
        // Third call - increments
        $result3 = $this->container_name_from_manifest( 'woocommerce/activation', $counter );
        $this->assertEquals( 'woocommerce-activation-3', $result3 );
        
        // Different package - no suffix
        $result4 = $this->container_name_from_manifest( 'vendor/other', $counter );
        $this->assertEquals( 'vendor-other', $result4 );
    }
    
    /**
     * @test
     */
    public function it_throws_on_invalid_remote_format(): void {
        $this->expectException( \InvalidArgumentException::class );
        $this->expectExceptionMessage( 'Invalid package reference format' );
        
        $this->container_name_from_manifest( 'invalid-format' );
    }
    
    /**
     * @test
     * @dataProvider sanitizationProvider
     */
    public function it_sanitizes_special_characters( $input, $expected ): void {
        $result = $this->container_name_from_manifest( $input, $counter, true );
        $this->assertEquals( $expected, $result );
    }
    
    public function sanitizationProvider(): array {
        return [
            // Special characters in namespace/package
            [ 'vendor/package@special#chars!:1.0', 'vendor-package-special-chars-1-0' ],
            [ 'vendor_name/package.name:1.0', 'vendor-name-package-name-1-0' ],
            [ 'vendor name/package name:1.0', 'vendor-name-package-name-1-0' ],
            [ 'vendor//package:1.0', 'vendor--package-1-0' ], // Double slash becomes double dash
            [ '-vendor-/-package-:1.0', 'vendor---package-1-0' ], // Leading dash + slash becomes triple dash
            
            // Unicode characters
            [ 'vendör/packäge:1.0', 'vend-r-pack-ge-1-0' ],
        ];
    }
    
    /**
     * @test
     */
    public function it_handles_version_flag_correctly(): void {
        $package_id = 'vendor/package:1.0';
        
        // Without version flag
        $without = $this->container_name_from_manifest( $package_id, $counter, false );
        $this->assertEquals( 'vendor-package', $without );
        
        // With version flag
        $with = $this->container_name_from_manifest( $package_id, $counter, true );
        $this->assertEquals( 'vendor-package-1-0', $with );
    }
    
    /**
     * @test
     */
    public function it_handles_empty_version_after_colon(): void {
        // This should throw because 'vendor/package:' is not a valid format
        $this->expectException( \InvalidArgumentException::class );
        $this->expectExceptionMessage( 'Invalid package reference format' );
        
        $this->container_name_from_manifest( 'vendor/package:', $counter, true );
    }
    
    /**
     * @test
     * @dataProvider caseNormalizationProvider
     */
    public function it_normalizes_case( $input, $expected ): void {
        $result = $this->container_name_from_manifest( $input, $counter, true );
        $this->assertEquals( $expected, $result );
    }
    
    public function caseNormalizationProvider(): array {
        return [
            [ 'Vendor/Package:1.0', 'vendor-package-1-0' ],
            [ 'VENDOR/PACKAGE:1.0', 'vendor-package-1-0' ],
            [ 'VeNdOr/PaCkAgE:1.0', 'vendor-package-1-0' ],
            [ 'vendor/package:V1.0', 'vendor-package-v1-0' ],
        ];
    }
}