<?php

namespace QIT_CLI\Commands\Environment;

use QIT_CLI\Environment\EnvInfo;
use QIT_CLI\Environment\EnvironmentMonitor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function QIT_CLI\format_elapsed_time;

class ListEnvironmentCommand extends Command {
	/** @var EnvironmentMonitor */
	protected $environment_monitor;

	protected static $defaultName = 'env:list'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	public function __construct( EnvironmentMonitor $environment_monitor ) {
		$this->environment_monitor = $environment_monitor;
		parent::__construct( static::$defaultName ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

	protected function configure() {
		$this->setDescription( 'List running environments.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$running = $this->environment_monitor->get();

		if ( empty( $running ) ) {
			$output->writeln( '<info>No environments running.</info>' );

			return Command::SUCCESS;
		}

		$io = new SymfonyStyle( $input, $output );

		$output->writeln( '<info>Running environments:</info>' );

		$definitions = [];

		foreach ( $running as $environment ) {
			/** @var EnvInfo $environment */
			$elapsed = format_elapsed_time( time() - $environment->created_at );

			/**
			 * EnvInfo is iterable (public properties) but PHPStan flags it anyway.
			 *
			 * @see https://github.com/phpstan/phpstan/issues/1060
			 * @phpstan-ignore-next-line
			 */
			foreach ( $environment as $k => $v ) {
				if ( $k === 'created_at' ) {
					$v = $elapsed;
				}
				if ( is_array( $v ) ) {
					$v = implode( ', ', $v );
				}
				$definitions[] = [ ucwords( $k ) => $v ];
			}

			// Add a separator between environments.
			$definitions[] = new TableSeparator();
		}

		// Remove the last separator.
		array_pop( $definitions );

		$io->definitionList( ...$definitions );

		return Command::SUCCESS;
	}
}
