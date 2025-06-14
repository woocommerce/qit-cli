<?php

namespace QIT_CLI\PreCommand;

use QIT_CLI\App;
use QIT_CLI\Commands\QITCommand;
use QIT_CLI\PreCommand\Configuration\ConfigurationResolver;
use QIT_CLI\PreCommand\Interfaces\ConfigurableTestCommand;
use QIT_CLI\PreCommand\Interfaces\EnvironmentCommand;
use QIT_CLI\PreCommand\Interfaces\LocalTestCommand;
use QIT_CLI\PreCommand\Results\ConfigurationResult;
use QIT_CLI\PreCommand\Results\EnvironmentResult;
use QIT_CLI\PreCommand\Results\LocalTestResult;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PreCommandHandler {
	protected ConfigurationResolver $config_resolver;
	protected EnvironmentResolver $env_resolver;
	protected TestPackageResolver $test_package_resolver;

	public function __construct(
		ConfigurationResolver $config_resolver,
		EnvironmentResolver $env_resolver,
		TestPackageResolver $test_package_resolver
	) {
		$this->config_resolver       = $config_resolver;
		$this->env_resolver          = $env_resolver;
		$this->test_package_resolver = $test_package_resolver;
	}

	/**
	 * Handle PreCommand resolution based on command interfaces
	 */
	public function handle( QITCommand $command, InputInterface $input, OutputInterface $output ): object {
		$output->writeln( '<comment>DEBUG: PreCommandHandler called for ' . get_class( $command ) . '</comment>' );

		if ( $command instanceof LocalTestCommand ) {
			$output->writeln( '<comment>DEBUG: Handling as LocalTestCommand</comment>' );

			return $this->handleLocalTest( $command, $input, $output );
		}

		if ( $command instanceof EnvironmentCommand ) {
			$output->writeln( '<comment>DEBUG: Handling as EnvironmentCommand</comment>' );

			return $this->handleEnvironment( $command, $input, $output );
		}

		if ( $command instanceof ConfigurableTestCommand ) {
			$output->writeln( '<comment>DEBUG: Handling as ConfigurableTestCommand</comment>' );

			return $this->handleRemoteTest( $command, $input, $output );
		}

		throw new \RuntimeException( 'Command does not implement any PreCommand interface' );
	}

	/**
	 * Handle local test commands - full resolution including test packages
	 */
	protected function handleLocalTest( LocalTestCommand $command, InputInterface $input, OutputInterface $output ): LocalTestResult {
		$output->writeln( '<info>Resolving test configuration...</info>', OutputInterface::VERBOSITY_VERBOSE );

		// 1. Parse and resolve configuration
		$config_file     = $input->getOption( 'config' );
		$resolved_config = $this->config_resolver->resolve( $config_file );

		// 2. Get test configuration
		$test_type   = $command->getTestType();
		$profile     = $command->getTestProfile();
		$test_config = $resolved_config->get_test_config( $test_type, $profile );

		// 3. Resolve environment with all extensions
		$env_name   = $command->getEnvironmentName();
		$env_result = $this->env_resolver->resolve(
			$resolved_config,
			$env_name,
			$command->shouldPrepareEnvironment()
		);

		// 4. Resolve and download test packages
		$test_packages = $this->test_package_resolver->resolve(
			$resolved_config,
			$test_type,
			$profile
		);

		return new LocalTestResult(
			$resolved_config,
			$env_result->env_info,
			$test_packages,
			$test_config
		);
	}

	/**
	 * Handle environment commands - just environment setup
	 */
	protected function handleEnvironment( EnvironmentCommand $command, InputInterface $input, OutputInterface $output ): EnvironmentResult {
		$output->writeln( '<info>Resolving environment configuration...</info>', OutputInterface::VERBOSITY_VERBOSE );

		// 1. Parse and resolve configuration
		$config_file     = $input->getOption( 'config' );
		$resolved_config = $this->config_resolver->resolve( $config_file );

		// 2. Resolve environment
		$env_name   = $command->getEnvironmentName();
		$env_result = $this->env_resolver->resolve(
			$resolved_config,
			$env_name,
			$command->shouldPrepareEnvironment()
		);

		return $env_result;
	}

	/**
	 * Handle remote test commands - just configuration for API
	 */
	protected function handleRemoteTest( ConfigurableTestCommand $command, InputInterface $input, OutputInterface $output ): ConfigurationResult {
		$output->writeln( '<info>Preparing test configuration...</info>', OutputInterface::VERBOSITY_VERBOSE );

		// 1. Parse configuration (minimal resolution)
		$config_file     = $input->getOption( 'config' );
		$resolved_config = $this->config_resolver->resolve( $config_file );

		// 2. Get test configuration
		$test_type   = $command->getTestType();
		$profile     = $command->getTestProfile();
		$test_config = $resolved_config->get_test_config( $test_type, $profile );

		// 3. Prepare configuration result
		return new ConfigurationResult( $resolved_config, $test_config );
	}
}