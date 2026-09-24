<?php

namespace QIT_CLI\Commands\Blueprint;

use QIT_CLI\Blueprints\BlueprintParser;
use QIT_CLI\Blueprints\BlueprintTranspiler;
use QIT_CLI\Commands\QITCommand;
use QIT_CLI\QITInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Converts a Playground Blueprint into a qit.json environment block, without
 * booting anything. Handy to see what `qit env:up --blueprint` would do.
 */
class BlueprintImportCommand extends QITCommand {
	protected static $defaultName = 'blueprint:import'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected function configure(): void {
		parent::configure();

		$this->setDescription( 'Converts a WordPress Playground Blueprint into a qit.json environment block.' )
			->addArgument( 'blueprint', InputArgument::REQUIRED, 'Path to a Blueprint JSON file' )
			->addOption( 'environment', 'e', InputOption::VALUE_OPTIONAL, 'Name of the generated environment block', 'default' )
			->addOption( 'output', 'O', InputOption::VALUE_OPTIONAL, 'Write the qit.json fragment to this file instead of stdout' )
			->setHelp( <<<'HELP'
Convert a Blueprint into QIT configuration:

  <info>qit blueprint:import ./blueprint.json</info>
  <info>qit blueprint:import ./blueprint.json --environment=blueprint --output=qit.json</info>

Blueprint steps that cannot be expressed declaratively are listed as setup
commands. To actually run them, boot the environment with:

  <info>qit env:up --blueprint=./blueprint.json</info>
HELP
			);
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		$path      = (string) $input->getArgument( 'blueprint' );
		$blueprint = ( new BlueprintParser() )->from_file( $path );
		$result    = ( new BlueprintTranspiler() )->transpile( $blueprint, $path );

		$env_name = (string) ( $input->getOption( 'environment' ) ?: 'default' );
		$json     = json_encode( $result->to_qit_json( $env_name ), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . "\n";

		$output_file = $input->getOption( 'output' );

		if ( $output_file ) {
			if ( file_put_contents( $output_file, $json ) === false ) {
				$output->writeln( sprintf( '<error>Could not write to %s</error>', $output_file ) );

				return self::FAILURE;
			}
			$output->writeln( sprintf( '<info>Wrote %s</info>', $output_file ) );
		} else {
			$output->write( $json );
		}

		if ( $result->has_steps() ) {
			$output->writeln( '' );
			$output->writeln( sprintf( '<comment>%d setup command(s) will run after the environment boots:</comment>', count( $result->steps ) ) );
			foreach ( $result->steps as $step ) {
				$output->writeln( sprintf( '  • %s', $step['description'] ) );
				if ( $output->isVerbose() ) {
					$output->writeln( sprintf( '    <fg=gray>%s</>', $step['command'] ) );
				}
			}
		}

		foreach ( $result->warnings as $warning ) {
			$output->writeln( sprintf( '<comment>Warning: %s</comment>', $warning ) );
		}

		if ( $result->landing_page !== null ) {
			$output->writeln( sprintf( '<comment>Landing page: %s (open it once the environment is up)</comment>', $result->landing_page ) );
		}

		return self::SUCCESS;
	}
}
