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
use QIT_CLI\PreCommand\Pipeline\Stages\ResolveEnvironmentStage;
use QIT_CLI\PreCommand\Pipeline\Stages\ConsolidateWooCommerceStage;
use QIT_CLI\PreCommand\Pipeline\Stages\BuildEnvironmentResultStage;
use QIT_CLI\PreCommand\Pipeline\Stages\ResolveTestPackagesStage;
use QIT_CLI\PreCommand\Pipeline\Stages\BuildLocalTestResultStage;
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
		}

		if ( $command instanceof LocalTestCommand ) {
			$context = ( new ValidateSUTStage() )->process( $context );
			$context = ( new ResolveEnvironmentStage( $this->env_resolver ) )->process( $context );
			$context = ( new ConsolidateWooCommerceStage() )->process( $context );
			$context = ( new ResolveTestPackagesStage( $this->test_package_resolver ) )->process( $context );
			$context = ( new BuildLocalTestResultStage() )->process( $context );
		}

		if ( $command instanceof EnvironmentCommand ) {
			$context = ( new ResolveEnvironmentStage( $this->env_resolver ) )->process( $context );
			$context = ( new ConsolidateWooCommerceStage() )->process( $context );
			$context = ( new BuildEnvironmentResultStage() )->process( $context );
		}

		/**
		 * Early-bail mechanism for test runner.
		 *
		 * When QIT_SELF_TEST=precommand is set, this will build the objects as usual
		 * but return the result without executing the actual command. This is useful
		 * for testing the configuration resolution phase without running the full command.
		 *
		 * This allows tests to be split into different layers:
		 * 1. Pre-Command tests (pure unit tests) - Only test the configuration resolution
		 * 2. Command runtime tests (lightweight functional tests) - Test the command execution
		 * 3. Full integration tests (slow, end-to-end scenarios) - Test the entire system
		 *
		 * Use the qit_precommand() function in tests to trigger this early-bail mechanism.
		 */
		if ( getenv( 'QIT_SELF_TEST' ) === 'precommand' ) {
			$output->writeln( json_encode( $context->get_result(), JSON_PRETTY_PRINT ) );
			throw new PrecommandEarlyReturn();
		}

		return $context->get_result();
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
			'test_package'  => 'test_packages',
		];
	}
}
