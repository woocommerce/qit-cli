<?php

namespace QIT_CLI\Commands\Blueprint;

use QIT_CLI\Blueprints\BlueprintExporter;
use QIT_CLI\Commands\QITCommand;
use QIT_CLI\QITInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Emits a Playground Blueprint that reproduces a qit.json environment block,
 * so a QIT environment can be shared as a Playground link.
 */
class BlueprintExportCommand extends QITCommand {
	protected static $defaultName = 'blueprint:export'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected function configure(): void {
		parent::configure();

		$this->setDescription( 'Exports a qit.json environment as a WordPress Playground Blueprint.' )
			->addOption( 'environment', 'e', InputOption::VALUE_OPTIONAL, 'Environment block to export', 'default' )
			->addOption( 'output', 'O', InputOption::VALUE_OPTIONAL, 'Write the Blueprint to this file instead of stdout' )
			->setHelp( <<<'HELP'
Export the current environment as a Blueprint:

  <info>qit blueprint:export</info>
  <info>qit blueprint:export --environment=legacy --output=blueprint.json</info>

The export is lossy: Docker volumes, local paths, PHP extensions and Xdebug
have no Playground equivalent and are reported as warnings.
HELP
			);
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		$env_name   = (string) ( $input->getOption( 'environment' ) ?: 'default' );
		$env_config = $this->get_environment_config( $env_name );

		if ( empty( $env_config ) ) {
			$output->writeln( sprintf( '<error>No environment "%s" found in qit.json.</error>', $env_name ) );

			return self::FAILURE;
		}

		$exporter  = new BlueprintExporter();
		$blueprint = $exporter->export( $env_config );
		$json      = json_encode( $blueprint, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . "\n";

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

		foreach ( $exporter->get_warnings() as $warning ) {
			$output->writeln( sprintf( '<comment>Warning: %s</comment>', $warning ) );
		}

		return self::SUCCESS;
	}
}
