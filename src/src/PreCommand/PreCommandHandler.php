<?php

namespace QIT_CLI\PreCommand;

use QIT_CLI\App;
use QIT_CLI\Commands\QITCommand;
use QIT_CLI\PreCommand\Configuration\ConfigurationResolver;
use QIT_CLI\PreCommand\Configuration\ResolvedConfiguration;
use QIT_CLI\PreCommand\Interfaces\ConfigurableTestCommand;
use QIT_CLI\PreCommand\Interfaces\EnvironmentCommand;
use QIT_CLI\PreCommand\Interfaces\LocalTestCommand;
use QIT_CLI\PreCommand\Pipeline\PipelineContext;
use QIT_CLI\PreCommand\Pipeline\Stages\ExtractInputStage;
use QIT_CLI\PreCommand\Pipeline\Stages\ResolveConfigStage;
use QIT_CLI\PreCommand\Pipeline\Stages\ValidateSUTStage;
use QIT_CLI\PreCommand\Pipeline\Stages\BuildApiPayloadStage;
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

		// ---------- new pipeline bootstrap ----------
		$context = new PipelineContext( $command, $input, $output );

		// if caller passed an explicit $config_file argument (unit tests etc.)
		if ( $config_file ) {
			$context->set( 'config_file', $config_file );
		}

		$context = ( new ExtractInputStage() )->process( $context );
		$context = ( new ResolveConfigStage( $this->config_resolver ) )->process( $context );

		/** @var ResolvedConfiguration $resolved_config */
		$resolved_config = $context->get( 'resolved_config' );
		// ---------------------------------------------

		if ( $command instanceof ConfigurableTestCommand ) {
			$context = ( new ValidateSUTStage() )->process( $context );
			$context = ( new BuildApiPayloadStage() )->process( $context );
			return $context->get_result();
		}

		// Validate SUT for local test commands
		if ( $command instanceof LocalTestCommand ) {
			if ( ! $resolved_config->sut ) {
				throw new \RuntimeException( 'System Under Test (SUT) is required for test commands. Specify via CLI argument or qit.json.' );
			}
			$output->writeln( '<comment>DEBUG: Handling as Local Test Command</comment>' );

			return $this->handle_local_test( $command, $input, $output, $resolved_config );
		}

		if ( $command instanceof EnvironmentCommand ) {
			$output->writeln( '<comment>DEBUG: Handling as EnvironmentCommand</comment>' );

			return $this->handle_environment( $command, $input, $output, $resolved_config );
		}

		throw new \RuntimeException( 'Command does not implement any PreCommand interface' );
	}

	protected function handle_environment(
		EnvironmentCommand $command,
		InputInterface $input,
		OutputInterface $output,
		ResolvedConfiguration $resolved_config
	): EnvironmentResult {
		$output->writeln( '<info>Resolving environment configuration...</info>', OutputInterface::VERBOSITY_VERBOSE );

		$env_name = $command->get_environment_name();

		// Extract explicit CLI overrides
		$option_mapping = $this->get_environment_option_mapping();
		$cli_overrides  = $this->extract_explicit_options( $command, $input, $option_mapping );

		$env_result = $this->env_resolver->resolve(
			$resolved_config,
			$env_name,
			$command->should_prepare_environment(),
			$cli_overrides,
			$input
		);

		return $env_result;
	}

	protected function handle_local_test(
		LocalTestCommand $command,
		InputInterface $input,
		OutputInterface $output,
		ResolvedConfiguration $resolved_config
	): LocalTestResult {
		$output->writeln( '<info>Resolving test configuration...</info>', OutputInterface::VERBOSITY_VERBOSE );

		$test_type   = $command->get_test_type();
		$profile     = $command->get_test_profile();
		$test_config = $resolved_config->get_test_config( $test_type, $profile );

		$env_name = $command->get_environment_name();

		// Extract explicit CLI overrides for environment and test options
		$env_mapping  = $this->get_environment_option_mapping();
		$test_mapping = $this->get_test_option_mapping();

		$env_overrides  = $this->extract_explicit_options( $command, $input, $env_mapping );
		$test_overrides = $this->extract_explicit_options( $command, $input, $test_mapping );

		$env_result = $this->env_resolver->resolve(
			$resolved_config,
			$env_name,
			$command->should_prepare_environment(),
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

	/**
	 * Define option name mappings for environment commands.
	 *
	 * @return array<string, string> Mapping of CLI option names to config keys.
	 */
	protected function get_environment_option_mapping(): array {
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
	protected function get_test_option_mapping(): array {
		return [
			'phpstan_level' => 'phpstan_level',
		];
	}
}
