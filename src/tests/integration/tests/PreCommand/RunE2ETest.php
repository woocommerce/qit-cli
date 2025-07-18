<?php

namespace integration\tests\PreCommand;

use QIT\SelfTests\CustomTests\Traits\ScaffoldHelpers;
use QIT\SelfTests\CustomTests\Traits\SnapshotHelpers;
use Spatie\Snapshots\Drivers\JsonDriver;

/**
 * Pre-Command (static) tests for the RunE2E command.
 * These tests resolve configuration using qit_precommand() and never spin up Docker.
 */
class RunE2ETest extends \PHPUnit\Framework\TestCase {
    use SnapshotHelpers;
    use ScaffoldHelpers;

    public function test_woo_version_and_plugin_are_merged() {
        $json = qit_precommand([
            'run:e2e',
            'woocommerce-amazon-s3-storage',
            $this->scaffold_test(),
            '--woo',
            'stable',
            '--plugin',
            'woocommerce',
            '--json',
        ]);

        $env     = json_decode($json, true);
        $plugins = array_filter(
            $env['env_info']['plugins'],
            fn ($p) => $p['slug'] === 'woocommerce'
        );

        $this->assertCount(1, $plugins);
        $this->assertSame('stable', array_values($plugins)[0]['version']);
    }

    public function test_can_use_space() {
        $output = qit_precommand( [
            'run:e2e',
            'woocommerce-amazon-s3-storage',
            $this->scaffold_test(),
            '--plugin',
            'woocommerce',
            '--json',
        ] );

        $env = json_decode( $output, true );
        $this->assertContains( 'woocommerce', array_column( $env['env_info']['plugins'] ?? [], 'slug' ) );
    }

    public function test_can_use_equal_signs() {
        $output = qit_precommand( [
            'run:e2e',
            'woocommerce-amazon-s3-storage',
            $this->scaffold_test(),
            '--plugin=woocommerce',
            '--json',
        ] );

        $env = json_decode( $output, true );
        $this->assertContains( 'woocommerce', array_column( $env['env_info']['plugins'] ?? [], 'slug' ) );
    }

    public function test_directory_with_same_basename_as_sut() {
        $this->scaffold_plugin( 'woocommerce-amazon-s3-storage' );

        $output = qit_precommand( [
            'run:e2e',
            'woocommerce-amazon-s3-storage',
            $this->scaffold_test(),
            '--plugin=woocommerce',
            '--json',
        ] );

        $env    = json_decode( $output, true );
        $output = $this->normalize_env_info( $env );
        $output = json_encode( $output, JSON_PRETTY_PRINT );

        $this->assertMatchesNormalizedSnapshot( $output, new JsonDriver() );
    }

    public function test_directory_with_same_basename_as_sut_with_env_up() {
        $this->scaffold_plugin( 'woocommerce-amazon-s3-storage' );

        $output = qit_precommand( [
            'run:e2e',
            'woocommerce-amazon-s3-storage',
            $this->scaffold_test(),
            '--plugin=woocommerce',
            '--json',
        ] );

        $env    = json_decode( $output, true );
        $output = $this->normalize_env_info( $env );
        $output = json_encode( $output, JSON_PRETTY_PRINT );

        $this->assertMatchesNormalizedSnapshot( $output, new JsonDriver() );
    }
}