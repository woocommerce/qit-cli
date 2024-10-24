<?php

namespace QIT_CLI\Environment;

use QIT_CLI\Environment\Environments\EnvInfo;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

class PluginActivationReportRenderer {
	/** @var OutputInterface */
	protected $output;

	public function __construct( OutputInterface $output ) {
		$this->output = $output;
	}

	public function render_php_activation_report( EnvInfo $env_info, string $activation_output ): void {
		$activation_report_file = $env_info->temporary_env . 'bin/plugin-activation-report.json';

		if ( ! file_exists( $activation_report_file ) ) {
			// Probably no plugins to activate?
			$this->output->writeln( '<info>No plugins to activate.</info>' );

			return;
		}

		$activation_report = json_decode( file_get_contents( $activation_report_file ), true );

		if ( ! is_array( $activation_report ) ) {
			$this->output->writeln( '<error>Invalid plugin activation report generated.</error>' );
			$this->output->writeln( $activation_output );

			return;
		}

		$has_big_debug_log = false;

		foreach ( $activation_report as $r ) {
			/**
			 * @var array{
			 *     plugin: string,
			 *     activated: bool,
			 *     debug_log: array<string>,
			 * } $r
			 */
			$expected_schema = [
				'plugin'    => 'string',
				'activated' => 'boolean',
				'debug_log' => 'array',
			];

			foreach ( $expected_schema as $key => $type ) {
				if ( ! array_key_exists( $key, $r ) || gettype( $r[ $key ] ) !== $type ) {
					$this->output->writeln( '<error>Invalid plugin activation report generated.</error>' );
					$this->output->writeln( $activation_output );

					return;
				}
			}

			if ( ! $r['activated'] ) {
				throw new \RuntimeException( sprintf( "Plugin %s failed to activate. Output:\n %s", $r['plugin'], $activation_output ) );
			}

			if ( ! empty( $r['debug_log'] ) ) {
				$this->output->writeln(
					sprintf(
						'<error>New debug log entries were generated while activating plugin "%s"%s:</error>',
						$r['plugin'], // @phan-suppress-current-line PhanTypeMismatchArgumentInternal
						count( $r['debug_log'] ) > 10 ? sprintf( ' (%d lines total, showing last 10)', count( $r['debug_log'] ) ) : ''
					)
				);
				if ( count( $r['debug_log'] ) > 10 ) {
					$has_big_debug_log = true;
					$r['debug_log']    = array_slice( $r['debug_log'], - 10 );
				}
				$table = new Table( $this->output );
				foreach ( $r['debug_log'] as $line ) {
					$table->addRow( [ $line ] );
				}
				$table->render();
			}
		}

		if ( $has_big_debug_log ) {
			$this->output->writeln( sprintf( '<info>Some debug logs were too big to show. Full logs: %s</info>', $activation_report_file ) );
		}
	}
}
