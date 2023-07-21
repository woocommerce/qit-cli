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
	protected static $defaultName = 'fix'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected function configure() {
		$this
			->setDescription( 'Fix the identified security issues in a plugin' )
			->addArgument( 'test_run_id', InputArgument::REQUIRED, 'The ID of the test run' )
			->addArgument( 'plugin_dir', InputArgument::REQUIRED, 'The path to the plugin directory' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$command = $this->getApplication()->find( GetCommand::getDefaultName() );

		// Execute the GetCommand to retrieve the test result JSON.
		$get_command        = new ArrayInput( [
			'test_run_id' => $input->getArgument( 'test_run_id' ),
			'--json'      => true,
		] );
		$get_command_output = new BufferedOutput();
		$command->run( $get_command, $get_command_output );

		try {
			$json = json_decode( $get_command_output->fetch(), true );

			if ( is_null( $json ) ) {
				throw new \UnexpectedValueException();
			}

			if ( empty( $json['test_result_json'] ) ) {
				$output->writeln( '<error>Invalid JSON returned from the API.</error>' );

				return Command::FAILURE;
			}
			if ( empty( $json['ai_suggestion_status'] ) ) {
				$output->writeln( '<error>This test report does not support the AI fixer. Please run a new test.</error>' );

				return Command::FAILURE;
			}

			if ( $json['ai_suggestion_status'] !== 'done' ) {
				$output->writeln( '<error>This test report does not have AI suggestions. Please request the AI Suggestions on the test report page.</error>' );

				return Command::FAILURE;
			}
		} catch ( \UnexpectedValueException $e ) {
			$output->writeln( '<error>Invalid JSON returned from the API.</error>' );

			return Command::FAILURE;
		} catch ( \Exception $e ) {
			return Command::FAILURE;
		}

		try {
			$security_fixer = new SecurityFixer();
			$security_fixer->fix( $input->getArgument( 'plugin_dir' ), $json['test_result_json'] );

			$completion_message = <<<EOD
    The Security Fixer has now completed its execution. Please bear in mind that 
    this tool is powered by an AI model in its early stages of training. 
    The automatically generated fixes are experimental and might produce invalid 
    PHP code or recommend non-existent functions.

    It is crucial for every suggested fix to be carefully reviewed and tested by 
    a professional developer. Although this tool aims to ease the task of making 
    code secure, it is not a substitute for professional code review and should be 
    used as a helper tool.

    We strongly encourage you to provide feedback about the suggestions. Your 
    valuable input will help to further refine and improve this AI model.
EOD;

			$output->writeln( "<info>$completion_message</info>" );
			$output->writeln( '<info>Automatically applied AI recommendations. Please review them carefully.</info>' );

			return 0;
		} catch ( SecurityFixerException $e ) {
			$output->writeln( '<error>An error occurred while applying the automatic fixer:</error>' );
			$output->writeln( $e->getMessage() );

			return 1;
		}
	}
}
