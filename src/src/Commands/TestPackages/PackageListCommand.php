<?php

namespace QIT_CLI\Commands\TestPackages;

use QIT_CLI\Commands\QITCommand;
use QIT_CLI\RequestBuilder;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function QIT_CLI\get_manager_url;

class PackageListCommand extends QITCommand {

	protected function configure(): void {
		$this
			->setName( 'package:list' )
			->setDescription( 'List all test packages available to the current partner' )
			->addOption(
				'format',
				null,
				InputOption::VALUE_REQUIRED,
				'Output format (table, json)',
				'table'
			)
			->addOption(
				'test-type',
				't',
				InputOption::VALUE_REQUIRED,
				'Filter by test type (e2e, api, etc.)'
			)
			->addOption(
				'namespace',
				null,
				InputOption::VALUE_REQUIRED,
				'Filter by namespace'
			)
			->addOption(
				'owned-only',
				'o',
				InputOption::VALUE_NONE,
				'Show only packages owned by the current partner'
			);
	}

	protected function doExecute( InputInterface $input, OutputInterface $output ): int {
		$io         = new SymfonyStyle( $input, $output );
		$format     = $input->getOption( 'format' );
		$test_type  = $input->getOption( 'test-type' );
		$namespace  = $input->getOption( 'namespace' );
		$owned_only = $input->getOption( 'owned-only' );

		try {
			$packages = $this->fetch_packages_from_manager( $test_type, $namespace, $owned_only, $output );
			$this->output_packages( $packages, $format, $output, $io );

			return 0;
		} catch ( \Exception $e ) {
			if ( $format === 'json' ) {
				$output->writeln( json_encode( [
					'success'  => false,
					'error'    => $e->getMessage(),
					'packages' => [],
				], JSON_PRETTY_PRINT ) );
			} else {
				$io->error( $e->getMessage() );
			}

			return 1;
		}
	}

	/**
	 * Fetch packages from Manager endpoint
	 *
	 * @return array<string, mixed>
	 */
	private function fetch_packages_from_manager( ?string $test_type, ?string $namespace, bool $owned_only, OutputInterface $output ): array {
		$output->writeln( '📦 Fetching available packages...' );

		$post_body = [];
		if ( $test_type ) {
			$post_body['test_type'] = $test_type;
		}
		if ( $namespace ) {
			$post_body['namespace'] = $namespace;
		}
		if ( $owned_only ) {
			$post_body['owned_only'] = '1';
		}

		$url = get_manager_url() . '/wp-json/cd/v1/cli/test-packages';

		$response = ( new RequestBuilder( $url ) )
			->with_method( 'POST' )
			->with_post_body( $post_body )
			->request();

		$data = json_decode( $response, true );

		if ( ! is_array( $data ) ) {
			throw new \RuntimeException( 'Invalid response from packages API' );
		}

		if ( isset( $data['code'] ) && $data['code'] !== 200 ) {
			$error_message = $data['message'] ?? 'Failed to fetch packages';
			throw new \RuntimeException( $error_message );
		}

		return $data;
	}

	/**
	 * Output packages list
	 */
	private function output_packages( array $response, string $format, OutputInterface $output, SymfonyStyle $io ): void {
		$packages     = $response['packages'] ?? [];
		$total        = $response['total'] ?? count( $packages );
		$owned_count  = $response['owned_count'] ?? 0;
		$public_count = $response['public_count'] ?? 0;

		if ( $format === 'json' ) {
			$output->writeln( json_encode( [
				'success'      => true,
				'total'        => $total,
				'owned_count'  => $owned_count,
				'public_count' => $public_count,
				'packages'     => $packages,
			], JSON_PRETTY_PRINT ) );

			return;
		}

		// Table format (default)
		if ( empty( $packages ) ) {
			$io->info( 'No packages found matching your criteria.' );

			return;
		}

		$io->title( 'Available Test Packages' );

		if ( $owned_count > 0 && $public_count > 0 ) {
			$io->text( sprintf(
				'Found %d packages total (%d owned by you, %d public)',
				$total,
				$owned_count,
				$public_count
			) );
		} elseif ( $owned_count > 0 ) {
			$io->text( sprintf( 'Found %d packages owned by you', $owned_count ) );
		} elseif ( $public_count > 0 ) {
			$io->text( sprintf( 'Found %d public packages', $public_count ) );
		}

		$table = new Table( $output );
		$table->setHeaders( [
			'Package ID',
			'Namespace',
			'Package',
			'Test Type',
			'Version',
			'Size',
			'Visibility',
			'Created',
		] );

		foreach ( $packages as $package ) {
			$size       = $this->format_file_size( $package['size_bytes'] ?? 0 );
			$visibility = $package['visibility'] === 'private' ? '👤 Private' : '🌐 Public';
			$created    = $this->format_date( $package['created_at'] ?? '' );

			$table->addRow( [
				$package['package_id'] ?? 'N/A',
				$package['namespace'] ?? 'N/A',
				$package['package'] ?? 'N/A',
				$package['test_type'] ?? 'N/A',
				$package['version'] ?? 'N/A',
				$size,
				$visibility,
				$created,
			] );
		}

		$table->render();

		$io->newLine();
		$io->text( '💡 Use <info>qit package:download <package-id></info> to download a package' );
		$io->text( '💡 Use <info>qit package:delete <package-id></info> to delete packages you own' );
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
