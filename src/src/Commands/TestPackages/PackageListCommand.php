<?php

namespace QIT_CLI\Commands\TestPackages;

use QIT_CLI\Commands\QITCommand;
use QIT_CLI\QITInput;
use QIT_CLI\RequestBuilder;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function QIT_CLI\get_manager_url;

class PackageListCommand extends QITCommand {

	protected function configure(): void {
		parent::configure();

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
				'json',
				'j',
				InputOption::VALUE_NONE,
				'Output in JSON format (shorthand for --format=json)'
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
				'type',
				null,
				InputOption::VALUE_REQUIRED,
				'Filter by package type (utility, test, all)'
			)
			->addOption(
				'owned-only',
				'o',
				InputOption::VALUE_NONE,
				'Show only packages owned by the current partner'
			)
			->addOption(
				'limit',
				'l',
				InputOption::VALUE_REQUIRED,
				'Number of parent packages to display per page (subpackages always included)',
				'20'
			)
			->addOption(
				'page',
				'p',
				InputOption::VALUE_REQUIRED,
				'Page number to display',
				'1'
			)
			->addOption(
				'no-pagination',
				null,
				InputOption::VALUE_NONE,
				'Disable pagination and show all packages'
			);
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		$io     = new SymfonyStyle( $input, $output );
		$format = $input->getOption( 'format' );

		// Handle --json shorthand option
		if ( $input->getOption( 'json' ) ) {
			$format = 'json';
		}

		$test_type     = $input->getOption( 'test-type' );
		$namespace     = $input->getOption( 'namespace' );
		$type          = $input->getOption( 'type' );
		$owned_only    = $input->getOption( 'owned-only' );
		$limit         = (int) $input->getOption( 'limit' );
		$page          = (int) $input->getOption( 'page' );
		$no_pagination = $input->getOption( 'no-pagination' );

		// If no-pagination is set, use a very high limit
		if ( $no_pagination ) {
			$limit = 9999;
			$page  = 1;
		}

		try {
			$packages = $this->fetch_packages_from_manager( $test_type, $namespace, $type, $owned_only, $limit, $page, $output );
			$this->output_packages( $packages, $format, $output, $io, $input );

			return 0;
		} catch ( \Exception $e ) {
			if ( $format === 'json' ) {
				$output->writeln( json_encode( [
					'success'  => false,
					'error'    => $e->getMessage(),
					'packages' => [],
				], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
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
	private function fetch_packages_from_manager( ?string $test_type, ?string $namespace_param, ?string $type, bool $owned_only, int $limit, int $page, OutputInterface $output ): array {
		$output->writeln( '📦 Fetching available packages...' );

		$post_body = [
			'limit' => $limit,
			'page'  => $page,
		];

		if ( $test_type ) {
			$post_body['test_type'] = $test_type;
		}
		if ( $namespace_param ) {
			$post_body['namespace'] = $namespace_param;
		}
		if ( $type ) {
			$post_body['type'] = $type;
		}
		if ( $owned_only ) {
			$post_body['owned_only'] = '1';
		}

		$url = get_manager_url() . '/wp-json/cd/v2/cli/test-packages';

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
			// For pagination out of bounds, show a helpful message
			if ( $data['code'] === 400 && strpos( $error_message, 'Page' ) === 0 ) {
				throw new \RuntimeException( $error_message );
			}
			throw new \RuntimeException( $error_message );
		}

		return $data;
	}

	/**
	 * Output packages list
	 *
	 * @param array<string, mixed> $response
	 */
	private function output_packages( array $response, string $format, OutputInterface $output, SymfonyStyle $io, QITInput $input ): void {
		$packages      = $response['packages'] ?? [];
		$total         = $response['total'] ?? count( $packages );
		$total_parents = $response['total_parents'] ?? 0;
		$owned_count   = $response['owned_count'] ?? 0;
		$public_count  = $response['public_count'] ?? 0;
		$page          = $response['page'] ?? 1;
		$limit         = $response['limit'] ?? 50;
		$total_pages   = $response['total_pages'] ?? 1;

		if ( $format === 'json' ) {
			$output->writeln( json_encode( [
				'success'       => true,
				'total'         => $total,
				'total_parents' => $total_parents,
				'owned_count'   => $owned_count,
				'public_count'  => $public_count,
				'page'          => $page,
				'limit'         => $limit,
				'total_pages'   => $total_pages,
				'packages'      => $packages,
			], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );

			return;
		}

		// Table format (default)
		if ( empty( $packages ) ) {
			$io->info( 'No packages found matching your criteria.' );

			return;
		}

		$io->title( 'Available Test Packages' );

		// Show pagination info
		if ( $total_pages > 1 && ! $input->getOption( 'no-pagination' ) ) {
			$io->text( sprintf(
				'Page %d of %d (showing %d parent packages with their subpackages)',
				$page,
				$total_pages,
				min( $limit, $total_parents - ( ( $page - 1 ) * $limit ) )
			) );
		}

		if ( $owned_count > 0 && $public_count > 0 ) {
			$io->text( sprintf(
				'Found %d packages total (%d parent packages, %d owned by you, %d public)',
				$total,
				$total_parents,
				$owned_count,
				$public_count
			) );
		} elseif ( $owned_count > 0 ) {
			$io->text( sprintf( 'Found %d packages owned by you (%d parent packages)', $owned_count, $total_parents ) );
		} elseif ( $public_count > 0 ) {
			$io->text( sprintf( 'Found %d public packages (%d parent packages)', $public_count, $total_parents ) );
		}

		$table = new Table( $output );
		$table->setHeaders( [
			'Package ID',
			'Namespace',
			'Version',
			'Size',
			'Visibility',
			'Created',
		] );

		foreach ( $packages as $package ) {
			$size       = $this->format_file_size( $package['size_bytes'] ?? 0 );
			$visibility = $package['visibility'] === 'private' ? '👤 Private' : '🌐 Public';
			$created    = $this->format_date( $package['created_at'] ?? '' );

			// Add visual indicator for subpackages
			$package_id = $package['package_id'] ?? 'N/A';
			if ( ! empty( $package['is_subpackage'] ) && $package['is_subpackage'] === true ) {
				$package_id = '  └─ ' . $package_id;
				// Show parent package info in the visibility column
				if ( ! empty( $package['parent_package'] ) ) {
					$visibility .= sprintf( ' (sub of %s)',
						explode( ':', $package['parent_package'] )[0] ?? $package['parent_package']
					);
				}
			}

			// Add utility indicator based on package_type
			if ( isset( $package['package_type'] ) && $package['package_type'] === 'utility' ) {
				$visibility .= ' 🔧 Utility';
			}

			$table->addRow( [
				$package_id,
				$package['namespace'] ?? 'N/A',
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
		$io->text( '💡 Use <info>--type utility</info> to show only utility packages' );

		// Show pagination navigation hints
		if ( $total_pages > 1 && ! $input->getOption( 'no-pagination' ) ) {
			$io->newLine();
			if ( $page < $total_pages ) {
				$io->text( sprintf( '📄 Use <info>--page %d</info> to see the next page', $page + 1 ) );
			}
			if ( $page > 1 ) {
				$io->text( sprintf( '📄 Use <info>--page %d</info> to see the previous page', $page - 1 ) );
			}
			$io->text( '📄 Use <info>--no-pagination</info> to see all packages at once' );
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
