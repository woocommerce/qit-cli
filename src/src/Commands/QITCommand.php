<?php

namespace QIT_CLI\Commands;

use QIT_CLI\Config\InputPriorityHandler;
use QIT_CLI\Config\MergedOptionsInputWrapper;
use QIT_CLI\Config\QITConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class QITCommand extends Command {
	/** @var string The root section in qit.json (e.g., 'tests', 'environments') */
	protected string $config_root_section = '';

	/** @var string The test type for test commands (e.g., 'e2e') */
	protected string $test_type = '';

	protected function configure(): void {
		$this->addOption(
			'config',
			null,
			InputOption::VALUE_OPTIONAL,
			'Path to the qit.json configuration file.',
			'qit.json'
		);

		// Automatically set config_root_section and test_type based on defaultName
		if ( static::$defaultName && str_starts_with( static::$defaultName, 'run:' ) ) {
			$this->config_root_section = 'tests';
			$this->test_type           = substr( static::$defaultName, 4 ); // Extract after 'run:'
			self::add_profile_option( $this );
		}
	}

	public static function add_profile_option( Command $command ): void {
		$command->addOption(
			'profile',
			null,
			InputOption::VALUE_OPTIONAL,
			'The profile to use for the test. If not set, will use the default profile.',
			'default'
		);
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$config_file = $input->getOption( 'config' );
		try {
			$config = new QITConfig( $config_file, $this->getApplication() );
		} catch ( \RuntimeException $e ) {
			$output->writeln( "<error>Error loading config: {$e->getMessage()}</error>" );

			return Command::FAILURE;
		}

		try {
			$config_section = $this->get_config_section( $config, $input );
		} catch ( \RuntimeException $e ) {
			$output->writeln( "<error>Error accessing config section: {$e->getMessage()}</error>" );

			return Command::FAILURE;
		}

		$command_defaults       = $this->get_command_defaults();
		$input_priority_handler = new InputPriorityHandler();
		$merged_options         = $input_priority_handler->get_config_from_input( $input, $config_section, $command_defaults );

		// Create new input with merged options, passing parameters in the correct order
		$new_input = new MergedOptionsInputWrapper( $input, $merged_options, $input->getArguments() );

		try {
			return $this->doExecute( $new_input, $output );
		} catch ( \RuntimeException $e ) {
			$output->writeln( "<error>{$e->getMessage()}</error>" );

			return Command::FAILURE;
		}
	}

	/**
	 * Get the relevant section of the config based on the command’s needs.
	 */
	protected function get_config_section( QITConfig $config, InputInterface $input ): array {
		if ( empty( $this->config_root_section ) ) {
			return []; // No config section needed
		}

		if ( $this->config_root_section === 'tests' ) {
			if ( empty( $this->test_type ) ) {
				throw new \RuntimeException( "Test type must be set for commands using 'tests' config." );
			}
			$profile = $input->getOption( 'profile' ) ?? 'default';

			return $config->get_test_config( $this->test_type, $profile );
		} elseif ( $this->config_root_section === 'environments' ) {
			$environment = $input->getOption( 'environment' ) ?? 'default';

			return $config->get_environment( $environment );
		}

		throw new \RuntimeException( "Unknown config root section: {$this->config_root_section}" );
	}

	/**
	 * Extract default values from the command’s option definitions.
	 */
	protected function get_command_defaults(): array {
		$defaults = [];
		foreach ( $this->getDefinition()->getOptions() as $option ) {
			$defaults[ $option->getName() ] = $option->getDefault();
		}

		return $defaults;
	}

	/**
	 * Abstract method for child commands to implement their logic.
	 *
	 * @param InputInterface $input Original input for arguments or rare cases.
	 * @param OutputInterface $output For writing output.
	 *
	 * @return int Command exit code.
	 */
	abstract protected function doExecute( InputInterface $input, OutputInterface $output ): int;
}