<?php

namespace QIT_CLI\Commands;

use QIT_CLI\App;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\PreCommand\Parsers\ConfigParser;
use QIT_CLI\PreCommand\InputPriorityHandler;
use QIT_CLI\PreCommand\EnvInfoBuilder;
use QIT_CLI\PreCommand\TestProfileHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\is_option_explicitly_provided;

abstract class QITCommand extends Command {
	protected static array $commands_with_environments = [
		'env:up',
		'run:e2e',
		'run:activation',
	];

	protected InputInterface $input;
	protected ?EnvInfo $env_info = null;
	protected array $merged_options = [];

	protected function configure(): void {
		$this->addOption(
			'config',
			null,
			InputOption::VALUE_OPTIONAL,
			'Path to the qit.json configuration file, which may extend a base configuration.',
			'qit.json'
		);

		if ( $this->needs_test_profile() ) {
			$this->addOption(
				'profile',
				null,
				InputOption::VALUE_OPTIONAL,
				'The profile to use for the test. If not set, will use the default profile.',
				'default'
			);
		}
	}

	public function execute( InputInterface $input, OutputInterface $output ): int {
		$this->input = $input;
		$config_file = $input->getOption( 'config' );
		file_put_contents( '/tmp/qit/qit_debug.log', "QITCommand: Loading config file: $config_file\n", FILE_APPEND );
		try {
			$config = new ConfigParser( $config_file, $this->getApplication() );
			file_put_contents( '/tmp/qit/qit_debug.log', "QITCommand: Config parsed successfully\n", FILE_APPEND );
		} catch ( \RuntimeException $e ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "QITCommand: Config parsing failed: {$e->getMessage()}\n", FILE_APPEND );
			$output->writeln( "<error>Error loading config: {$e->getMessage()}</error>" );

			return Command::FAILURE;
		}

		$this->merged_options = $this->get_merged_options( $input, $output, $config );

		if ( $this->needs_environment() ) {
			$environment_handler = App::make( EnvInfoBuilder::class );
			file_put_contents( '/tmp/qit/qit_debug.log', "QITCommand: Building environment info\n", FILE_APPEND );
			$this->env_info = $environment_handler->build_env_info( $input, $output, $this->merged_options, $config );
		}

		if ( getenv( 'QIT_TESTING_ENV_INFO' ) && $this->env_info ) {
			$output->writeln( json_encode( $this->env_info ) );

			return Command::SUCCESS;
		}

		try {
			return $this->doExecute( $input, $output );
		} catch ( \RuntimeException $e ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "QITCommand: Execution failed: {$e->getMessage()}\n", FILE_APPEND );
			$output->writeln( "<error>{$e->getMessage()}</error>" );

			return Command::FAILURE;
		}
	}

	protected function needs_environment(): bool {
		return in_array( static::getDefaultName(), static::$commands_with_environments, true );
	}

	protected function needs_test_profile(): bool {
		return str_starts_with( static::getDefaultName(), 'run:' );
	}

	protected function get_merged_options( InputInterface $input, OutputInterface $output, ConfigParser $config ): array {
		$command_defaults = $this->get_command_defaults();
		$config_section   = [];

		if ( $this->needs_environment() ) {
			$environment    = $input->getOption( 'environment' ) ?? 'default';
			$config_section = $config->get_environment( $environment );
		}

		if ( $this->needs_test_profile() ) {
			$test_profile_handler = App::make( TestProfileHandler::class );
			$profile              = $input->getOption( 'profile' ) ?: 'default';
			$profile_explicit     = is_option_explicitly_provided( $input, 'profile' );
			$profile_config       = [];

			if ( $profile_explicit || ! empty( $config->get_test_config( $this->get_test_type(), $profile ) ) ) {
				try {
					$profile_config = $test_profile_handler->load_profile( $this->get_test_type(), $profile, $config );
				} catch ( \RuntimeException $e ) {
					if ( $profile_explicit ) {
						$output->writeln( "<error>{$e->getMessage()}</error>" );
						throw $e;
					}
				}
			}

			foreach ( $profile_config as $key => $value ) {
				if ( ! isset( $config_section[ $key ] ) ) {
					$config_section[ $key ] = $value;
				} elseif ( is_array( $config_section[ $key ] ) && is_array( $value ) ) {
					$config_section[ $key ] = array_unique( array_merge( $config_section[ $key ], $value ), SORT_REGULAR );
				} else {
					$config_section[ $key ] = $value;
				}
			}
		}

		$input_priority_handler = App::make( InputPriorityHandler::class );

		return $input_priority_handler->get_config_from_input( $input, $config_section, $command_defaults, EnvInfoBuilder::get_pluralizable_keys() );
	}

	protected function get_test_type(): string {
		if ( $this->needs_test_profile() ) {
			return explode( ':', static::getDefaultName() )[1];
		}
		throw new \RuntimeException( 'This command does not support test profiles.' );
	}

	protected function get_command_defaults(): array {
		$defaults = [];
		foreach ( $this->getDefinition()->getOptions() as $option ) {
			$defaults[ $option->getName() ] = $option->getDefault();
		}

		return $defaults;
	}

	protected function get_merged_options_array(): array {
		return $this->merged_options;
	}

	protected function get_env_info(): ?EnvInfo {
		return $this->env_info;
	}

	abstract protected function doExecute( InputInterface $input, OutputInterface $output ): int;
}