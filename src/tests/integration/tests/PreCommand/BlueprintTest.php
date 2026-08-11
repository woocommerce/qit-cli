<?php

namespace integration\tests\PreCommand;

use PHPUnit\Framework\TestCase;

/**
 * Command-level behaviour of --blueprint.
 *
 * The translation itself is unit-tested (BlueprintTranspilerTest,
 * BlueprintExporterTest). What needs a real command run is the wiring: where the
 * Blueprint sits in the precedence chain, and the environments that refuse it.
 */
final class BlueprintTest extends TestCase {

	/** @var string[] Files to clean up. */
	private array $temp_files = [];

	protected function tearDown(): void {
		foreach ( $this->temp_files as $file ) {
			@unlink( $file );
		}
		$this->temp_files = [];
		parent::tearDown();
	}

	/**
	 * @param array<string, mixed> $blueprint
	 */
	private function write_blueprint( array $blueprint ): string {
		$path = tempnam( sys_get_temp_dir(), 'qit_blueprint_' ) . '.json';
		file_put_contents( $path, (string) json_encode( $blueprint ) );
		$this->temp_files[] = $path;

		return $path;
	}

	/**
	 * @param array<string, mixed> $config
	 */
	private function write_config( array $config ): string {
		$path = tempnam( sys_get_temp_dir(), 'qit_config_' );
		file_put_contents( $path, (string) json_encode( $config ) );
		$this->temp_files[] = $path;

		return $path;
	}

	// ── Precedence: Blueprint → qit.json → CLI ──

	public function test_blueprint_supplies_the_environment_when_nothing_else_does(): void {
		$blueprint = $this->write_blueprint( [
			'preferredVersions' => [ 'php' => '8.1', 'wp' => '6.5' ],
			'plugins'           => [ 'akismet' ],
		] );

		$raw = qit_run_env_up( [ 'env:up', '--json', '--blueprint', $blueprint ] );

		$this->assertStringContainsString( '"php_version":"8.1"', $raw );
		$this->assertStringContainsString( '"wordpress_version":"6.5"', $raw );
		$this->assertStringContainsString( '"slug":"akismet"', $raw );
	}

	public function test_qit_json_overrides_the_blueprint(): void {
		$blueprint = $this->write_blueprint( [ 'preferredVersions' => [ 'php' => '8.1' ] ] );
		$config    = $this->write_config( [ 'environments' => [ 'default' => [ 'php' => '8.2' ] ] ] );

		$raw = qit_run_env_up( [ 'env:up', '--json', '--blueprint', $blueprint, '--config', $config ] );

		$this->assertStringContainsString( '"php_version":"8.2"', $raw );
	}

	public function test_cli_overrides_both(): void {
		$blueprint = $this->write_blueprint( [ 'preferredVersions' => [ 'php' => '8.1' ] ] );
		$config    = $this->write_config( [ 'environments' => [ 'default' => [ 'php' => '8.2' ] ] ] );

		$raw = qit_run_env_up( [ 'env:up', '--json', '--blueprint', $blueprint, '--config', $config, '--php', '8.4' ] );

		$this->assertStringContainsString( '"php_version":"8.4"', $raw );
	}

	// ── Steps are mounted as a utility package ──

	public function test_blueprint_steps_are_mounted_for_the_environment_to_run(): void {
		$blueprint = $this->write_blueprint( [
			'steps' => [
				[ 'step' => 'setSiteOptions', 'options' => [ 'blogname' => 'Blueprint Store' ] ],
			],
		] );

		$raw = qit_run_env_up( [ 'env:up', '--json', '--blueprint', $blueprint ] );

		$this->assertStringContainsString( '/qit/packages/blueprint-steps', $raw );
		$this->assertStringContainsString( 'blueprint/steps', $raw, 'The generated package is registered for setup.' );
	}

	// ── Blueprints are e2e-only ──

	public function test_performance_environments_refuse_a_blueprint(): void {
		$blueprint = $this->write_blueprint( [ 'preferredVersions' => [ 'php' => '8.3' ] ] );

		$output = qit_run_env_up(
			[ 'env:up', '--environment_type', 'performance', '--blueprint', $blueprint ],
			[],
			1
		);

		$this->assertStringContainsString( 'not supported in performance environments', $output );
	}

	public function test_env_up_advertises_the_option(): void {
		// run:* commands only register against a connected Manager, so their wiring
		// is covered by the docker-group tests rather than here.
		$output = qit( [ 'env:up', '--help' ] );

		$this->assertStringContainsString( '--blueprint', $output );
	}
}
