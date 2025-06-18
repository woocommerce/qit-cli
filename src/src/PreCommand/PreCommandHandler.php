<?php

namespace QIT_CLI\PreCommand;

use QIT_CLI\App;
use QIT_CLI\Commands\QITCommand;
use QIT_CLI\PreCommand\Configuration\ConfigurationResolver;
use QIT_CLI\PreCommand\Configuration\ResolvedConfiguration;
use QIT_CLI\PreCommand\Interfaces\ConfigurableTestCommand;
use QIT_CLI\PreCommand\Interfaces\EnvironmentCommand;
use QIT_CLI\PreCommand\Interfaces\LocalTestCommand;
use QIT_CLI\PreCommand\Results\ConfigurationResult;
use QIT_CLI\PreCommand\Results\EnvironmentResult;
use QIT_CLI\PreCommand\Results\LocalTestResult;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PreCommandHandler {
	use SmartOptionExtraction;

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

	public function handle( QITCommand $command, InputInterface $input, OutputInterface $output, ?string $config_file = null ): object {
		$output->writeln( '<comment>DEBUG: PreCommandHandler called for ' . get_class( $command ) . '</comment>' );

		// Extract SUT from CLI input if provided
		$sut_slug = $input->hasArgument( 'sut' ) ? $input->getArgument( 'sut' ) : null;
		$sut_type = $input->hasOption( 'type' ) ? $input->getOption( 'type' ) : null;

		// Resolve configuration
		$resolved_config = $this->config_resolver->resolve( $config_file, $sut_slug, $sut_type );

		// Validate SUT for test commands
		if ( $command instanceof ConfigurableTestCommand || $command instanceof LocalTestCommand ) {
			if ( ! $resolved_config->sut ) {
				throw new \RuntimeException( 'System Under Test (SUT) is required for test commands. Specify via CLI argument or qit.json.' );
			}
			$output->writeln( '<comment>DEBUG: Handling as Test Command</comment>' );

			if ( $command instanceof LocalTestCommand ) {
				return $this->handleLocalTest( $command, $input, $output, $resolved_config );
			}

			return $this->handleRemoteTest( $command, $input, $output, $resolved_config );
		}

		if ( $command instanceof EnvironmentCommand ) {
			$output->writeln( '<comment>DEBUG: Handling as EnvironmentCommand</comment>' );

			return $this->handleEnvironment( $command, $input, $output, $resolved_config );
		}

		throw new \RuntimeException( 'Command does not implement any PreCommand interface' );
	}

	protected function handleEnvironment(
		EnvironmentCommand $command,
		InputInterface $input,
		OutputInterface $output,
		ResolvedConfiguration $resolved_config
	): EnvironmentResult {
		$output->writeln( '<info>Resolving environment configuration...</info>', OutputInterface::VERBOSITY_VERBOSE );

		$env_name = $command->getEnvironmentName();

		// Extract explicit CLI overrides
		$option_mapping = $this->getEnvironmentOptionMapping();
		$cli_overrides  = $this->extractExplicitOptions( $command, $input, $option_mapping );

		$env_result = $this->env_resolver->resolve(
			$resolved_config,
			$env_name,
			$command->shouldPrepareEnvironment(),
			$cli_overrides,
			$input
		);

		return $env_result;
	}

	protected function handleLocalTest(
		LocalTestCommand $command,
		InputInterface $input,
		OutputInterface $output,
		ResolvedConfiguration $resolved_config
	): LocalTestResult {
		$output->writeln( '<info>Resolving test configuration...</info>', OutputInterface::VERBOSITY_VERBOSE );

		$test_type   = $command->getTestType();
		$profile     = $command->getTestProfile();
		$test_config = $resolved_config->get_test_config( $test_type, $profile );

		$env_name = $command->getEnvironmentName();

		// Extract explicit CLI overrides for environment and test options
		$env_mapping  = $this->getEnvironmentOptionMapping();
		$test_mapping = $this->getTestOptionMapping();

		$env_overrides  = $this->extractExplicitOptions( $command, $input, $env_mapping );
		$test_overrides = $this->extractExplicitOptions( $command, $input, $test_mapping );

		$env_result = $this->env_resolver->resolve(
			$resolved_config,
			$env_name,
			$command->shouldPrepareEnvironment(),
			$env_overrides,
			$input
		);

		// Merge test overrides into test config
		$test_config = array_merge( $test_config, $test_overrides );

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

	protected function handleRemoteTest(
		ConfigurableTestCommand $command,
		InputInterface $input,
		OutputInterface $output,
		ResolvedConfiguration $resolved_config
	): ConfigurationResult {
		$output->writeln( '<info>Preparing test configuration...</info>', OutputInterface::VERBOSITY_VERBOSE );

		$test_type   = $command->getTestType();
		$profile     = $command->getTestProfile();
		$test_config = $resolved_config->get_test_config( $test_type, $profile );

		// Extract test-specific overrides
		$test_mapping   = $this->getTestOptionMapping();
		$test_overrides = $this->extractExplicitOptions( $command, $input, $test_mapping );

		// Merge CLI overrides into test config
		$test_config = array_merge( $test_config, $test_overrides );

		return new ConfigurationResult( $resolved_config, $test_config );
	}

	/**
	 * Define option name mappings for environment commands.
	 *
	 * @return array<string, string> Mapping of CLI option names to config keys.
	 */
	protected function getEnvironmentOptionMapping(): array {
		return [
			'plugin'        => 'plugins',
			'theme'         => 'themes',
			'volume'        => 'volumes',
			'php_extension' => 'php_extensions',
			'env'           => 'env_vars',
			'env_file'      => 'env_files',
		];
	}

	/**
	 * Define option name mappings for test commands.
	 *
	 * @return array<string, string> Mapping of CLI option names to config keys.
	 */
	protected function getTestOptionMapping(): array {
		return [
			'phpstan_level' => 'phpstan_level',
		];
	}

	protected function build_api_payload( array $test_config, ?array $sut ): array {
		$payload = [];

		// Add test configuration parameters
		foreach ( $test_config as $key => $value ) {
			// Skip internal configuration keys
			if ( in_array( $key, [ 'environment', 'test_packages', 'extends' ], true ) ) {
				continue;
			}
			$payload[ $key ] = $value;
		}

		// Add SUT information if available
		if ( $sut ) {
			$payload['sut_slug'] = $sut['slug'];
			$payload['sut_type'] = $sut['type'];
		}

		return array_filter( $payload );
	}
}