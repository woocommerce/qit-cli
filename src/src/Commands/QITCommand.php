<?php

namespace QIT_CLI\Commands;

use QIT_CLI\App;
use QIT_CLI\PreCommand\Configuration\ConfigurationResolver;
use QIT_CLI\PreCommand\Configuration\ResolvedConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class QITCommand extends Command {
	protected static array $commands_with_config = [
		'test:e2e',
		'test:security',
		'test:phpstan',
		'test:compatibility',
		'test:performance',
		'run',
	];

	protected InputInterface $input;
	protected OutputInterface $output;
	protected ?ResolvedConfiguration $resolved_config = null;

	protected function configure(): void {
		if ( $this->needs_config() ) {
			$this->addOption(
				'config',
				'',
				InputOption::VALUE_OPTIONAL,
				'Path to the qit.json configuration file',
				'./qit.json'
			);

			$this->addOption(
				'profile',
				'',
				InputOption::VALUE_OPTIONAL,
				'The profile to use for the test',
				'default'
			);

			$this->addOption(
				'environment',
				'e',
				InputOption::VALUE_OPTIONAL,
				'Override the environment to use'
			);

			$this->addOption(
				'no-cache',
				null,
				InputOption::VALUE_NONE,
				'Skip configuration cache and re-resolve everything'
			);
		}
	}

	public function execute( InputInterface $input, OutputInterface $output ): int {
		$this->input  = $input;
		$this->output = $output;

		// Resolve configuration if needed
		if ( $this->needs_config() ) {
			try {
				$this->resolved_config = $this->resolve_configuration();
			} catch ( \Exception $e ) {
				$output->writeln( "<error>Configuration error: {$e->getMessage()}</error>" );

				return Command::FAILURE;
			}
		}

		try {
			return $this->doExecute( $input, $output );
		} catch ( \RuntimeException $e ) {
			$output->writeln( "<error>{$e->getMessage()}</error>" );

			return Command::FAILURE;
		}
	}

	/**
	 * Check if this command needs configuration
	 */
	protected function needs_config(): bool {
		$name = static::getDefaultName();
		if ( ! $name ) {
			return false;
		}

		// Check if command starts with any of the config-needing prefixes
		foreach ( static::$commands_with_config as $prefix ) {
			if ( str_starts_with( $name, $prefix ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Resolve the configuration
	 */
	protected function resolve_configuration(): ResolvedConfiguration {
		$config_file = $this->input->getOption( 'config' );

		if ( ! file_exists( $config_file ) ) {
			throw new \RuntimeException( "Configuration file not found: $config_file" );
		}

		$resolver = App::make( ConfigurationResolver::class );

		// Use cache unless --no-cache is specified
		if ( $this->input->getOption( 'no-cache' ) ) {
			return $resolver->resolve( $config_file );
		} else {
			return $resolver->resolve_with_cache( $config_file );
		}
	}

	/**
	 * Get the resolved configuration
	 */
	protected function get_resolved_config(): ?ResolvedConfiguration {
		return $this->resolved_config;
	}

	/**
	 * Get test configuration for current command
	 */
	protected function get_test_configuration(): array {
		if ( ! $this->resolved_config ) {
			throw new \RuntimeException( 'No configuration resolved' );
		}

		$test_type = $this->get_test_type();
		$profile   = $this->input->getOption( 'profile' ) ?? 'default';

		return $this->resolved_config->get_test_config( $test_type, $profile );
	}

	/**
	 * Get the test type from command name
	 */
	protected function get_test_type(): string {
		$name = static::getDefaultName();
		if ( ! $name || ! str_starts_with( $name, 'test:' ) ) {
			throw new \RuntimeException( 'Not a test command' );
		}

		return substr( $name, 5 ); // Remove 'test:' prefix
	}

	/**
	 * Get environment configuration
	 */
	protected function get_environment_config(): array {
		if ( ! $this->resolved_config ) {
			throw new \RuntimeException( 'No configuration resolved' );
		}

		// Check for environment override
		$env_name = $this->input->getOption( 'environment' );

		if ( ! $env_name ) {
			// Get from test configuration
			$test_config = $this->get_test_configuration();
			$env_name    = $test_config['environment'] ?? 'default';
		}

		return $this->resolved_config->get_environment( $env_name );
	}

	/**
	 * Check if secrets are required
	 */
	protected function check_secrets(): void {
		if ( ! $this->resolved_config || ! $this->resolved_config->requires_secrets() ) {
			return;
		}

		$required = $this->resolved_config->get_required_secrets();
		$missing  = [];

		foreach ( $required as $secret ) {
			if ( empty( getenv( $secret ) ) ) {
				$missing[] = $secret;
			}
		}

		if ( ! empty( $missing ) ) {
			throw new \RuntimeException(
				"Missing required secrets:\n" .
				implode( "\n", array_map( fn( $s ) => "  - $s", $missing ) ) .
				"\n\nSet these as environment variables or use 'qit secret:set'"
			);
		}
	}

	/**
	 * Get all test packages for current configuration
	 */
	protected function get_test_packages(): array {
		if ( ! $this->resolved_config ) {
			return [];
		}

		$test_type = $this->get_test_type();
		$profile   = $this->input->getOption( 'profile' ) ?? 'default';

		return $this->resolved_config->get_test_packages_for_config( $test_type, $profile );
	}

	/**
	 * Output configuration summary
	 */
	protected function output_config_summary(): void {
		if ( ! $this->resolved_config || ! $this->output->isVerbose() ) {
			return;
		}

		$this->output->writeln( '<info>Configuration Summary:</info>' );
		$this->output->writeln( sprintf( '  SUT: %s (%s)',
			$this->resolved_config->sut['slug'],
			$this->resolved_config->sut['type']
		) );

		$test_type = $this->get_test_type();
		$profile   = $this->input->getOption( 'profile' ) ?? 'default';
		$this->output->writeln( sprintf( '  Test: %s:%s', $test_type, $profile ) );

		$env = $this->get_environment_config();
		$this->output->writeln( sprintf( '  Environment: %s (PHP %s, WP %s)',
			$this->input->getOption( 'environment' ) ?? 'from profile',
			$env['php_version'] ?? 'default',
			$env['wp_version'] ?? 'default'
		) );

		$packages = $this->get_test_packages();
		$this->output->writeln( sprintf( '  Test Packages: %d', count( $packages ) ) );

		if ( $this->resolved_config->requires_secrets() ) {
			$this->output->writeln( sprintf( '  Required Secrets: %d',
				count( $this->resolved_config->get_required_secrets() )
			) );
		}

		$this->output->writeln( '' );
	}

	abstract protected function doExecute( InputInterface $input, OutputInterface $output ): int;
}