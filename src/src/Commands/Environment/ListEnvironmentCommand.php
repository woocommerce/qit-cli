<?php

namespace QIT_CLI\Commands\Environment;

use QIT_CLI\App;
use QIT_CLI\Commands\QITCommand;
use QIT_CLI\Environment\EnvironmentMonitor;
use QIT_CLI\Environment\EnvironmentSelectorTrait;
use QIT_CLI\QITInput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Terminal;
use function QIT_CLI\format_elapsed_time;

class ListEnvironmentCommand extends QITCommand {
	use EnvironmentSelectorTrait;

	protected static $defaultName = 'env:list'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected EnvironmentMonitor $environment_monitor;

	public function __construct( EnvironmentMonitor $environment_monitor ) {
		$this->environment_monitor = $environment_monitor;
		parent::__construct( static::$defaultName ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

	protected function configure(): void {
		parent::configure();
		$this
			->setDescription( 'List running environments.' )
			->addArgument( 'env_id', InputArgument::OPTIONAL, 'Environment ID to show (otherwise list all).' )
			->addOption( 'field', 'f', InputOption::VALUE_OPTIONAL, 'Show just a specific field.' )
			->addOption( 'json', 'j', InputOption::VALUE_NONE, 'Machine-readable JSON output' );
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		$io      = new SymfonyStyle( $input, $output );
		$running = $this->environment_monitor->get();
		$env_id  = $input->getArgument( 'env_id' );
		$field   = $input->getOption( 'field' );

		if ( empty( $running ) ) {
			if ( $input->getOption( 'json' ) ) {
				$output->writeln( json_encode( [], JSON_UNESCAPED_SLASHES ) );
			} else {
				$output->writeln( '<info>No environments running.</info>' );
			}

			return Command::SUCCESS;
		}

		if ( $env_id ) {
			$selected_env = $this->find_environment_or_error( $running, $env_id, $io );
			if ( ! $selected_env ) {
				return Command::FAILURE;
			}
			$running = [ $selected_env ];
		}

		if ( $input->getOption( 'json' ) ) {
			$output->writeln( json_encode( array_values( $running ), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) );

			return Command::SUCCESS;
		}

		// If "field" option is being used and there is only one environment in $running, just print it.
		if ( $field && count( $running ) === 1 ) {
			$env = array_shift( $running );
			if ( ! property_exists( $env, $field ) ) {
				$io->writeln( sprintf( '<error>Field "%s" does not exist.</error>', $field ) );

				return Command::FAILURE;
			}
			$io->writeln( $env->$field );

			return Command::SUCCESS;
		}

		// Otherwise, list them all (or if there's now only one in the array, we do the usual table logic).
		$output->writeln( '<info>Running environments:</info>' );

		$terminal_width = App::make( Terminal::class )->getWidth();
		$longest_header = 0;
		$definitions    = [];

		// Find the longest header name (we do exactly like your original code)
		foreach ( $running as $environment ) {
			// @phpstan-ignore-next-line
			// @phan-suppress-next-line PhanTypeSuspiciousNonTraversableForeach - EnvInfo properties are accessible
			foreach ( $environment as $k => $v ) {
				if ( strlen( $k ) > $longest_header ) {
					$longest_header = strlen( $k );
				}
			}
		}

		// Build the definition list
		foreach ( $running as $environment ) {
			$elapsed = format_elapsed_time( time() - $environment->created_at );

			// @phpstan-ignore-next-line
			// @phan-suppress-next-line PhanTypeSuspiciousNonTraversableForeach - EnvInfo properties are accessible
			foreach ( $environment as $k => $v ) {
				if ( $k === 'created_at' ) {
					$v = $elapsed;
				}
				if ( is_array( $v ) ) {
					// "implode" only works on flat arrays of scalars, otherwise we need "json_encode".
					$is_flat = count( array_filter( $v, function ( $item ) {
						return is_array( $item ) || is_object( $item );
					} ) ) === 0;
					$v       = $is_flat ? implode( "\n", $v ) : json_encode( $v, JSON_UNESCAPED_SLASHES );
				}

				$v = wordwrap( $v, $terminal_width - $longest_header - 10, "\n", true );

				$definitions[] = [ ucwords( $k ) => $v ];
			}
			$definitions[] = new TableSeparator();
		}

		// Remove the last separator
		array_pop( $definitions );

		$io->definitionList( ...$definitions );

		return Command::SUCCESS;
	}
}
