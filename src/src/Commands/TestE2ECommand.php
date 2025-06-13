<?php

namespace QIT_CLI\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Example implementation of a test command that uses the resolved configuration
 */
class TestE2ECommand extends QITCommand {
	protected static $defaultName = 'test:e2e';
	protected static $defaultDescription = 'Run end-to-end tests';

	protected function configure(): void {
		parent::configure();

		$this->addArgument(
			'profile',
			InputArgument::OPTIONAL,
			'Test profile to run',
			'default'
		);

		$this->addOption(
			'parallel',
			null,
			InputOption::VALUE_OPTIONAL,
			'Run tests in parallel (max workers)',
			false
		);

		$this->addOption(
			'fail-fast',
			null,
			InputOption::VALUE_NONE,
			'Stop on first failure'
		);

		$this->addOption(
			'package',
			null,
			InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
			'Run only specific test packages'
		);

		$this->addOption(
			'skip',
			null,
			InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
			'Skip specific test packages or patterns'
		);

		$this->addOption(
			'timeout',
			null,
			InputOption::VALUE_OPTIONAL,
			'Override timeout in seconds'
		);

		$this->addOption(
			'debug',
			null,
			InputOption::VALUE_NONE,
			'Enable debug mode'
		);

		$this->addOption(
			'keep-environment',
			null,
			InputOption::VALUE_NONE,
			'Keep the test environment after tests complete'
		);
	}

	protected function doExecute( InputInterface $input, OutputInterface $output ): int {
		// Output configuration summary
		$this->output_config_summary();

		// Check required secrets
		$this->check_secrets();

		// Get test configuration
		$test_config = $this->get_test_configuration();
		$env_config  = $this->get_environment_config();

		// Get test packages to run
		$packages = $this->get_test_packages_to_run( $input );

		if ( empty( $packages ) ) {
			$output->writeln( '<error>No test packages to run</error>' );

			return Command::FAILURE;
		}

		$output->writeln( sprintf( '<info>Running %d test packages...</info>', count( $packages ) ) );

		// Here's where you would actually run the tests
		// For now, we'll just show what would be run
		$this->display_test_plan( $packages, $env_config, $output );

		// In a real implementation, this would:
		// 1. Create the environment using $env_config
		// 2. Install SUT and other extensions
		// 3. Run each test package
		// 4. Collect and report results

		return Command::SUCCESS;
	}

	/**
	 * Get test packages to run based on configuration and filters
	 */
	protected function get_test_packages_to_run( InputInterface $input ): array {
		$all_packages = $this->get_test_packages();

		// Filter by --package option if provided
		$package_filter = $input->getOption( 'package' );
		if ( ! empty( $package_filter ) ) {
			$filtered = [];
			foreach ( $package_filter as $filter ) {
				foreach ( $all_packages as $ref => $package ) {
					if ( fnmatch( $filter, $ref ) || fnmatch( $filter, $package['package'] ?? '' ) ) {
						$filtered[ $ref ] = $package;
					}
				}
			}
			$all_packages = $filtered;
		}

		// Apply skip patterns
		$skip_patterns = $input->getOption( 'skip' );
		if ( ! empty( $skip_patterns ) ) {
			foreach ( $skip_patterns as $pattern ) {
				foreach ( $all_packages as $ref => $package ) {
					if ( fnmatch( $pattern, $ref ) || fnmatch( $pattern, $package['package'] ?? '' ) ) {
						unset( $all_packages[ $ref ] );
					}
				}
			}
		}

		// Apply test configuration tweaks
		$test_config = $this->get_test_configuration();
		if ( isset( $test_config['tweaks']['skip'] ) ) {
			foreach ( $test_config['tweaks']['skip'] as $pattern ) {
				foreach ( $all_packages as $ref => $package ) {
					if ( fnmatch( $pattern, $ref ) ) {
						unset( $all_packages[ $ref ] );
					}
				}
			}
		}

		return $all_packages;
	}

	/**
	 * Display what tests would be run
	 */
	protected function display_test_plan( array $packages, array $env_config, OutputInterface $output ): void {
		$output->writeln( '' );
		$output->writeln( '<comment>Test Execution Plan:</comment>' );
		$output->writeln( '' );

		// Environment details
		$output->writeln( '<info>Environment:</info>' );
		$output->writeln( sprintf( '  PHP Version: %s', $env_config['php_version'] ?? 'default' ) );
		$output->writeln( sprintf( '  WordPress Version: %s', $env_config['wp_version'] ?? 'default' ) );

		if ( isset( $env_config['woo_version'] ) ) {
			$output->writeln( sprintf( '  WooCommerce Version: %s', $env_config['woo_version'] ) );
		}

		if ( isset( $env_config['object_cache'] ) && $env_config['object_cache'] ) {
			$output->writeln( '  Object Cache: Enabled (Redis)' );
		}

		if ( ! empty( $env_config['plugins'] ) ) {
			$output->writeln( sprintf( '  Additional Plugins: %d', count( $env_config['plugins'] ) ) );
			if ( $output->isVerbose() ) {
				foreach ( $env_config['plugins'] as $plugin ) {
					$output->writeln( sprintf( '    - %s',
						is_array( $plugin ) ? $plugin['slug'] : $plugin
					) );
				}
			}
		}

		$output->writeln( '' );

		// System Under Test
		$output->writeln( '<info>System Under Test:</info>' );
		$sut = $this->resolved_config->sut;
		$output->writeln( sprintf( '  Type: %s', $sut['type'] ) );
		$output->writeln( sprintf( '  Slug: %s', $sut['slug'] ) );
		$output->writeln( sprintf( '  Source: %s', $this->format_source( $sut['source'] ) ) );

		$output->writeln( '' );

		// Test packages
		$output->writeln( '<info>Test Packages:</info>' );
		foreach ( $packages as $ref => $package ) {
			$output->writeln( sprintf( '  %s:', $ref ) );

			if ( isset( $package['description'] ) ) {
				$output->writeln( sprintf( '    Description: %s', $package['description'] ) );
			}

			if ( isset( $package['test_type'] ) ) {
				$output->writeln( sprintf( '    Type: %s', $package['test_type'] ) );
			}

			if ( isset( $package['tags'] ) && ! empty( $package['tags'] ) ) {
				$output->writeln( sprintf( '    Tags: %s', implode( ', ', $package['tags'] ) ) );
			}

			if ( $this->resolved_config->is_local_package( $ref ) ) {
				$output->writeln( sprintf( '    Location: %s', $package['path'] ?? 'local' ) );
			} else {
				$output->writeln( sprintf( '    Version: %s', $package['version'] ?? 'latest' ) );
			}

			if ( isset( $package['timeout'] ) ) {
				$output->writeln( sprintf( '    Timeout: %d seconds', $package['timeout'] ) );
			}
		}

		$output->writeln( '' );

		// Execution options
		$output->writeln( '<info>Execution Options:</info>' );

		$parallel = $this->input->getOption( 'parallel' );
		if ( $parallel !== false ) {
			$output->writeln( sprintf( '  Parallel: %s', $parallel ?: 'auto' ) );
		}

		if ( $this->input->getOption( 'fail-fast' ) ) {
			$output->writeln( '  Fail Fast: Enabled' );
		}

		if ( $this->input->getOption( 'debug' ) ) {
			$output->writeln( '  Debug Mode: Enabled' );
		}

		if ( $this->input->getOption( 'keep-environment' ) ) {
			$output->writeln( '  Keep Environment: Yes' );
		}

		$timeout = $this->input->getOption( 'timeout' );
		if ( $timeout ) {
			$output->writeln( sprintf( '  Global Timeout: %d seconds', $timeout ) );
		}

		$output->writeln( '' );

		// Required resources
		if ( $this->resolved_config->requires_secrets() ) {
			$output->writeln( '<info>Required Secrets:</info>' );
			foreach ( $this->resolved_config->get_required_secrets() as $secret ) {
				$has_value = ! empty( getenv( $secret ) );
				$status    = $has_value ? '<info>✓</info>' : '<error>✗</error>';
				$output->writeln( sprintf( '  %s %s', $status, $secret ) );
			}
			$output->writeln( '' );
		}

		if ( $this->resolved_config->requires_external_services() ) {
			$output->writeln( '<info>Required External Services:</info>' );
			foreach ( $this->resolved_config->required_services as $service ) {
				$output->writeln( sprintf( '  - %s', $service ) );
			}
			$output->writeln( '' );
		}

		// PHP Extensions
		$php_extensions = $this->resolved_config->get_all_php_extensions();
		if ( ! empty( $php_extensions ) ) {
			$output->writeln( '<info>Required PHP Extensions:</info>' );
			$output->writeln( '  ' . implode( ', ', $php_extensions ) );
			$output->writeln( '' );
		}
	}

	/**
	 * Format source for display
	 */
	protected function format_source( array $source ): string {
		switch ( $source['type'] ) {
			case 'local':
				return sprintf( 'Local directory (%s)', $source['path'] ?? '.' );

			case 'build':
				return sprintf( 'Build command (%s)', $source['command'] ?? 'unknown' );

			case 'url':
				return sprintf( 'URL (%s)', parse_url( $source['url'] ?? '', PHP_URL_HOST ) ?: 'unknown' );

			case 'wporg':
				return sprintf( 'WordPress.org (%s)', $source['version'] ?? 'stable' );

			case 'wccom':
				return sprintf( 'WooCommerce.com (%s)', $source['version'] ?? 'stable' );

			default:
				return $source['type'];
		}
	}
}