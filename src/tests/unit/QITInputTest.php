<?php

namespace QIT_CLI_Tests;

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use QIT_CLI\QITInput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Unit tests for QITInput — specifically profile-to-environment option merging.
 */
class QITInputTest extends TestCase {

	/**
	 * Build a QITInput with a fake Symfony input and a resolved config.
	 *
	 * @param array<string,mixed> $cli_options CLI options to simulate.
	 * @param array<string,mixed> $resolved_config The resolved qit.json config.
	 * @param string              $test_type Test type (e2e, activation, etc.).
	 *
	 * @return QITInput
	 */
	private function make_input( array $cli_options, array $resolved_config, string $test_type = 'e2e' ): QITInput {
		$definition = new InputDefinition();
		$definition->addArgument( new InputArgument( 'sut', InputArgument::OPTIONAL ) );
		$definition->addOption( new InputOption( 'config', null, InputOption::VALUE_OPTIONAL ) );
		$definition->addOption( new InputOption( 'environment', null, InputOption::VALUE_OPTIONAL ) );
		$definition->addOption( new InputOption( 'profile', null, InputOption::VALUE_OPTIONAL ) );
		$definition->addOption( new InputOption( 'php_version', null, InputOption::VALUE_OPTIONAL ) );
		$definition->addOption( new InputOption( 'wordpress_version', null, InputOption::VALUE_OPTIONAL ) );
		$definition->addOption( new InputOption( 'woocommerce_version', null, InputOption::VALUE_OPTIONAL ) );
		$definition->addOption( new InputOption( 'object_cache', null, InputOption::VALUE_NONE ) );
		$definition->addOption( new InputOption( 'xdebug', null, InputOption::VALUE_OPTIONAL, '', false ) );
		$definition->addOption( new InputOption( 'plugin', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, '', [] ) );
		$definition->addOption( new InputOption( 'theme', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, '', [] ) );
		$definition->addOption( new InputOption( 'volume', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, '', [] ) );
		$definition->addOption( new InputOption( 'php_extension', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, '', [] ) );
		$definition->addOption( new InputOption( 'tunnel', null, InputOption::VALUE_OPTIONAL ) );
		$definition->addOption( new InputOption( 'env', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, '', [] ) );
		$definition->addOption( new InputOption( 'env_file', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, '', [] ) );
		$definition->addOption( new InputOption( 'skip_activating_plugins', null, InputOption::VALUE_NONE ) );
		$definition->addOption( new InputOption( 'skip_activating_themes', null, InputOption::VALUE_NONE ) );
		$definition->addOption( new InputOption( 'json', null, InputOption::VALUE_NONE ) );
		$definition->addOption( new InputOption( 'test-package', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, '', [] ) );

		$symfony_input = new ArrayInput( $cli_options, $definition );

		return new QITInput( $symfony_input, $resolved_config, $test_type );
	}

	// ── Profile inline values are injected into environment options ──

	public function test_profile_inline_php_reaches_env_options(): void {
		$input = $this->make_input(
			[],
			[
				'test_types' => [
					'e2e' => [
						'default' => [ 'php' => '8.3', 'wp' => '6.0' ],
					],
				],
			]
		);

		$options = $input->get_environment_options();

		$this->assertSame( '8.3', $options['--php_version'] ?? null, 'Profile php should map to --php_version' );
		$this->assertSame( '6.0', $options['--wordpress_version'] ?? null, 'Profile wp should map to --wordpress_version' );
	}

	public function test_cli_overrides_profile_inline_values(): void {
		$input = $this->make_input(
			[ '--php_version' => '7.4' ],
			[
				'test_types' => [
					'e2e' => [
						'default' => [ 'php' => '8.3', 'wp' => '6.0' ],
					],
				],
			]
		);

		$options = $input->get_environment_options();

		$this->assertSame( '7.4', $options['--php_version'] ?? null, 'CLI --php_version must override profile' );
		$this->assertSame( '6.0', $options['--wordpress_version'] ?? null, 'Profile wp still applies when CLI does not override' );
	}

	public function test_long_form_keys_in_profile_also_work(): void {
		$input = $this->make_input(
			[],
			[
				'test_types' => [
					'e2e' => [
						'default' => [
							'php_version'         => '8.1',
							'wordpress_version'   => '6.4',
							'woocommerce_version' => '8.5',
						],
					],
				],
			]
		);

		$options = $input->get_environment_options();

		$this->assertSame( '8.1', $options['--php_version'] ?? null );
		$this->assertSame( '6.4', $options['--wordpress_version'] ?? null );
		$this->assertSame( '8.5', $options['--woocommerce_version'] ?? null );
	}

	public function test_no_profile_means_no_injection(): void {
		$input = $this->make_input( [], [] );

		$options = $input->get_environment_options();

		$this->assertArrayNotHasKey( '--php_version', $options );
		$this->assertArrayNotHasKey( '--wordpress_version', $options );
		$this->assertArrayNotHasKey( '--woocommerce_version', $options );
	}

	public function test_profile_woo_version_reaches_env_options(): void {
		$input = $this->make_input(
			[],
			[
				'test_types' => [
					'e2e' => [
						'default' => [ 'woo' => '9.0.0' ],
					],
				],
			]
		);

		$options = $input->get_environment_options();

		$this->assertSame( '9.0.0', $options['--woocommerce_version'] ?? null );
	}

	public function test_environment_reference_passes_through_as_option(): void {
		$input = $this->make_input(
			[],
			[
				'test_types' => [
					'e2e' => [
						'default' => [
							'environment' => 'staging',
							'php'         => '8.3',
						],
					],
				],
			]
		);

		$options = $input->get_environment_options();

		// Environment name is passed through for env:up to resolve
		$this->assertSame( 'staging', $options['--environment'] ?? null );
		// Inline php is also passed — it will override the environment's php in env:up
		$this->assertSame( '8.3', $options['--php_version'] ?? null );
	}

	public function test_cli_environment_overrides_profile_environment(): void {
		$input = $this->make_input(
			[ '--environment' => 'production' ],
			[
				'test_types' => [
					'e2e' => [
						'default' => [
							'environment' => 'staging',
						],
					],
				],
			]
		);

		$options = $input->get_environment_options();

		$this->assertSame( 'production', $options['--environment'] ?? null, 'CLI --environment must override profile' );
	}
}
