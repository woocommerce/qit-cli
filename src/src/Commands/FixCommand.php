<?php

namespace QIT_CLI\Commands;

use QIT_CLI\Fixer\Exceptions\SecurityFixerException;
use QIT_CLI\Fixer\SecurityFixer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;

class FixCommand extends Command {
	protected static $defaultName = 'fix';

	protected function configure() {
		$this
			->setDescription( 'Fix the identified security issues in a plugin' )
			->addArgument( 'test_run_id', InputArgument::REQUIRED, 'The ID of the test run' )
			->addArgument( 'plugin_dir', InputArgument::REQUIRED, 'The path to the plugin directory' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$command = $this->getApplication()->find( GetCommand::getDefaultName() );

		// Execute the GetCommand to retrieve the test result JSON
		$get_command        = new ArrayInput( [
			'test_run_id' => $input->getArgument( 'test_run_id' ),
			'--json'      => true,
		] );
		$get_command_output = new BufferedOutput();
		$command->run( $get_command, $get_command_output );

		try {
			$json = json_decode( $get_command_output->fetch(), true );
			if ( empty( $json['test_result_json'] ) ) {
				$output->writeln( '<error>Invalid JSON returned from the API.</error>' );

				return Command::FAILURE;
			}
		} catch ( \JsonException $e ) {
			$output->writeln( '<error>Invalid JSON returned from the API.</error>' );
			$output->writeln( $e->getMessage() );

			return Command::FAILURE;
		} catch ( \Exception $e ) {
			return Command::FAILURE;
		}

		try {
			$securityFixer = new SecurityFixer();
			$securityFixer->fix( $input->getArgument( 'plugin_dir' ), $json['test_result_json'] );
			$output->writeln( '<info>Security issues have been fixed in the plugin.</info>' );

			return 0;
		} catch ( SecurityFixerException $e ) {
			$output->writeln( '<error>An error occurred while fixing the security issues:</error>' );
			$output->writeln( $e->getMessage() );

			return 1;
		}
	}
}
