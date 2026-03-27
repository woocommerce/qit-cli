<?php

namespace QIT_CLI\Commands\Environment;

use QIT_CLI\Commands\QITCommand;
use QIT_CLI\Environment\EnvironmentMonitor;
use QIT_CLI\Environment\EnvironmentVars;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\Environments\QITEnvInfo;
use QIT_CLI\QITInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class EnvSourceCommand extends QITCommand {
	/** @var EnvironmentVars */
	private EnvironmentVars $environment_vars;

	/** @var EnvironmentMonitor */
	private EnvironmentMonitor $environment_monitor;

	protected static $defaultName = 'env:source'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	public function __construct( EnvironmentVars $environment_vars, EnvironmentMonitor $environment_monitor ) {
		$this->environment_vars    = $environment_vars;
		$this->environment_monitor = $environment_monitor;
		parent::__construct();
	}

	protected function configure(): void {
		parent::configure();
		$this
			->addArgument( 'env_id', InputArgument::OPTIONAL, 'The environment ID to generate source file for. If not provided, uses the most recent environment.' )
			->setDescription( 'Configure your shell to run tests against a QIT environment' );
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		$env_id = $input->getArgument( 'env_id' );

		$environments = $this->environment_monitor->get();

		if ( empty( $environments ) ) {
			$output->writeln( '<error>No environment found. Start one with: qit env:up</error>' );
			return 1;
		}

		// If no env_id provided, use the most recent environment.
		if ( empty( $env_id ) ) {
			// Sort by created_at descending to get most recent.
			uasort( $environments, function ( EnvInfo $a, EnvInfo $b ) {
				return $b->created_at - $a->created_at;
			} );

			$env_info = reset( $environments );
		} else {
			if ( ! isset( $environments[ $env_id ] ) ) {
				$output->writeln( sprintf( '<error>Environment %s not found</error>', $env_id ) );
				return 1;
			}

			$env_info = $environments[ $env_id ];
		}

		if ( ! $env_info instanceof QITEnvInfo ) {
			$output->writeln( sprintf( '<error>Environment %s is not a QIT environment.</error>', $env_info->env_id ) );
			return 1;
		}

		// Generate and save the source file.
		$files = $this->environment_vars->save_environment_file( $env_info );

		// Output just the path (for sourcing).
		// This allows: source "$(qit env:source)"
		$output->writeln( $files['shell'] );

		// In verbose mode, show all environment variables.
		if ( $output->isVerbose() ) {
			$vars = $this->environment_vars->get_mapping( $env_info );

			// Merge in custom environment variables (including secrets).
			if ( ! empty( $env_info->envs ) ) {
				$vars = array_merge( $vars, $env_info->envs );
			}

			$output->writeln( '', OutputInterface::VERBOSITY_VERBOSE );
			$output->writeln( '<comment>Environment variables that will be exported:</comment>', OutputInterface::VERBOSITY_VERBOSE );
			foreach ( $vars as $key => $value ) {
				$output->writeln( sprintf( '  <info>%s</info>=<comment>%s</comment>', $key, $value ), OutputInterface::VERBOSITY_VERBOSE );
			}
			$output->writeln( '', OutputInterface::VERBOSITY_VERBOSE );
		}

		return 0;
	}
}
