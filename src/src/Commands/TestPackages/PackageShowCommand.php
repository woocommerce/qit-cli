<?php

namespace QIT_CLI\Commands\TestPackages;

use QIT_CLI\Commands\QITCommand;
use QIT_CLI\QITInput;
use QIT_CLI\RequestBuilder;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function QIT_CLI\get_manager_url;

class PackageShowCommand extends QITCommand {

	protected function configure(): void {
		parent::configure();

		$this
			->setName( 'package:show' )
			->setDescription( 'Show details of a test package from QIT registry' )
			->addArgument(
				'package',
				InputArgument::REQUIRED,
				'Package identifier in namespace/package-name:version format'
			)
			->addOption(
				'format',
				null,
				InputOption::VALUE_REQUIRED,
				'Output format (table, json)',
				'table'
			)
			->addOption(
				'json',
				'j',
				InputOption::VALUE_NONE,
				'Output in JSON format (shorthand for --format=json)'
			);
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		$io      = new SymfonyStyle( $input, $output );
		$package = $input->getArgument( 'package' );
		$format  = $input->getOption( 'format' );

		// Handle --json shorthand option.
		if ( $input->getOption( 'json' ) ) {
			$format = 'json';
		}

		try {
			$package_data = $this->fetch_package_from_manager( $package, $output );
			$this->output_package( $package_data, $format, $output, $io );

			return 0;
		} catch ( \Exception $e ) {
			if ( $format === 'json' ) {
				$output->writeln( json_encode( [
					'success' => false,
					'error'   => $e->getMessage(),
					'package' => null,
				], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
			} else {
				$io->error( $e->getMessage() );
			}

			return 1;
		}
	}

	/**
	 * Fetch package from Manager endpoint
	 *
	 * @return array<string, mixed>
	 */
	private function fetch_package_from_manager( string $package_id, OutputInterface $output ): array {
		$output->writeln( '📦 Fetching package details...' );

		$url = get_manager_url() . '/wp-json/cd/v2/cli/test-package';

		$response = ( new RequestBuilder( $url ) )
			->with_method( 'POST' )
			->with_post_body( [ 'package_id' => $package_id ] )
			->request();

		$data = json_decode( $response, true );

		if ( ! is_array( $data ) ) {
			throw new \RuntimeException( 'Invalid response from package API' );
		}

		if ( isset( $data['code'] ) && $data['code'] !== 200 ) {
			$error_message = $data['message'] ?? 'Failed to fetch package';
			throw new \RuntimeException( $error_message );
		}

		if ( ! isset( $data['package'] ) ) {
			throw new \RuntimeException( 'Invalid response: missing package data' );
		}

		return $data['package'];
	}

	/**
	 * Output package details
	 *
	 * @param array<string, mixed> $package
	 */
	private function output_package( array $package, string $format, OutputInterface $output, SymfonyStyle $io ): void {
		if ( $format === 'json' ) {
			$output->writeln( json_encode( [
				'success' => true,
				'package' => $package,
			], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );

			return;
		}

		// Table format (default)
		$package_id   = $package['package_id'] ?? 'N/A';
		$package_type = $package['package_type'] ?? 'test';
		$visibility   = $package['visibility'] ?? 'private';
		$is_utility   = $package_type === 'utility';

		// Title section.
		$io->title( 'Package Details: ' . $package_id );

		// Basic information section.
		$io->section( 'Basic Information' );

		$type_label = $is_utility ? '<comment>🔧 Utility Package</comment>' : '<info>Test Package</info>';
		$vis_label  = $visibility === 'public' ? '<info>🌐 Public</info>' : '<comment>👤 Private</comment>';

		$io->definitionList(
			[ 'Package ID' => $package_id ],
			[ 'Type' => $type_label ],
			[ 'Namespace' => $package['namespace'] ?? 'N/A' ],
			[ 'Version' => $package['version'] ?? 'N/A' ],
			[ 'Visibility' => $vis_label ],
			[ 'Size' => $this->format_file_size( $package['size_bytes'] ?? 0 ) ],
			[ 'Created' => $this->format_date( $package['created_at'] ?? '' ) ]
		);

		// Manifest section.
		if ( isset( $package['manifest'] ) && is_array( $package['manifest'] ) ) {
			$manifest = $package['manifest'];

			// Description.
			if ( isset( $manifest['description'] ) && ! empty( $manifest['description'] ) ) {
				$io->section( 'Description' );
				$io->text( $manifest['description'] );
			}

			// Tags.
			if ( isset( $manifest['tags'] ) && is_array( $manifest['tags'] ) && ! empty( $manifest['tags'] ) ) {
				$io->section( 'Tags' );
				$io->text( implode( ', ', $manifest['tags'] ) );
			}

			// Phases section.
			if ( isset( $manifest['test']['phases'] ) && is_array( $manifest['test']['phases'] ) ) {
				$io->section( 'Phases' );

				$phases = $manifest['test']['phases'];

				$phase_info = [];
				foreach ( [ 'globalSetup', 'setup', 'run', 'teardown', 'globalTeardown' ] as $phase_name ) {
					$commands = $phases[ $phase_name ] ?? [];
					if ( ! empty( $commands ) && is_array( $commands ) ) {
						$command_str  = implode( "\n        ", $commands );
						$phase_info[] = sprintf( '  <info>✓ %s:</info>', $phase_name );
						$phase_info[] = '        ' . $command_str;
					} else {
						$status       = ( $phase_name === 'run' && $is_utility ) ?
							'<comment>✗ ' . $phase_name . ': (none - this is a utility package)</comment>' :
							'  <fg=gray>✗ ' . $phase_name . ': (none)</>';
						$phase_info[] = $status;
					}
				}

				foreach ( $phase_info as $info ) {
					$output->writeln( $info );
				}
			}

			// Requirements section.
			if ( isset( $manifest['requires'] ) && is_array( $manifest['requires'] ) ) {
				$requires = $manifest['requires'];

				$has_requirements = false;
				if ( ! empty( $requires['plugins'] ) || ! empty( $requires['themes'] ) || ! empty( $requires['secrets'] ) ) {
					$has_requirements = true;
				}

				if ( $has_requirements ) {
					$io->section( 'Requirements' );

					if ( ! empty( $requires['plugins'] ) && is_array( $requires['plugins'] ) ) {
						$output->writeln( '  <info>Plugins:</info>' );
						foreach ( $requires['plugins'] as $plugin ) {
							$output->writeln( '    • ' . $plugin );
						}
					}

					if ( ! empty( $requires['themes'] ) && is_array( $requires['themes'] ) ) {
						$output->writeln( '  <info>Themes:</info>' );
						foreach ( $requires['themes'] as $theme ) {
							$output->writeln( '    • ' . $theme );
						}
					}

					if ( ! empty( $requires['secrets'] ) && is_array( $requires['secrets'] ) ) {
						$output->writeln( '  <info>Secrets:</info>' );
						foreach ( $requires['secrets'] as $secret ) {
							$output->writeln( '    • ' . $secret );
						}
					}
				}
			}
		}

		// Subpackage information.
		if ( ! empty( $package['is_subpackage'] ) && $package['is_subpackage'] === true ) {
			$io->section( 'Subpackage Information' );
			$parent = $package['parent_package'] ?? 'N/A';
			$output->writeln( sprintf( '  <comment>This is a subpackage of:</comment> %s', $parent ) );
		}

		// Usage hints.
		$io->newLine();
		$io->text( '💡 Use <info>qit package:download ' . $package_id . '</info> to download this package' );
		if ( $is_utility ) {
			$io->text( '💡 Add to your qit.json under <info>"utilities"</info> to use in environments' );
		} else {
			$io->text( '💡 Add to your qit.json under <info>"test_packages"</info> to run tests' );
		}
	}

	/**
	 * Format file size in human readable format
	 */
	private function format_file_size( int $bytes ): string {
		if ( $bytes === 0 ) {
			return '0 B';
		}

		$units  = [ 'B', 'KB', 'MB', 'GB' ];
		$factor = floor( log( $bytes, 1024 ) );

		return sprintf( '%.1f %s', $bytes / pow( 1024, $factor ), $units[ $factor ] );
	}

	/**
	 * Format date in human readable format
	 */
	private function format_date( string $date ): string {
		if ( empty( $date ) ) {
			return 'N/A';
		}

		try {
			$dt = new \DateTime( $date );

			return $dt->format( 'Y-m-d H:i' );
		} catch ( \Exception $e ) {
			return 'N/A';
		}
	}
}
