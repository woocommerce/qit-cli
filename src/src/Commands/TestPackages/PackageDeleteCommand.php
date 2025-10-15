<?php

namespace QIT_CLI\Commands\TestPackages;

use QIT_CLI\Commands\QITCommand;
use QIT_CLI\QITInput;
use QIT_CLI\RequestBuilder;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use function QIT_CLI\get_manager_url;

class PackageDeleteCommand extends QITCommand {

	protected function configure(): void {
		parent::configure();

		$this
			->setName( 'package:delete' )
			->setDescription( 'Delete a test package from the QIT registry' )
			->addArgument(
				'package_id',
				InputArgument::REQUIRED,
				'Package identifier in format namespace/package:version (e.g., woocommerce/e2e:latest)'
			)
			->addOption(
				'yes',
				'y',
				InputOption::VALUE_NONE,
				'Skip confirmation prompt'
			)
			->addOption(
				'format',
				null,
				InputOption::VALUE_REQUIRED,
				'Output format (table, json)',
				'table'
			);
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		$io         = new SymfonyStyle( $input, $output );
		$package_id = $input->getArgument( 'package_id' );
		$yes        = $input->getOption( 'yes' );
		$format     = $input->getOption( 'format' );

		// Validate package ID format
		if ( ! preg_match( '/^[^\/]+\/[^:]+:.+$/', $package_id ) ) {
			$io->error( 'Invalid package ID format. Expected: namespace/package:version' );
			return 1;
		}

		// Confirmation prompt unless --yes is used
		if ( ! $yes ) {
			$question = new ConfirmationQuestion(
				"Are you sure you want to delete package '{$package_id}'? This action cannot be undone. [y/N] ",
				false
			);

			if ( ! $this->getHelper( 'question' )->ask( $input, $output, $question ) ) {
				$io->writeln( 'Deletion cancelled.' );
				return 0;
			}
		}

		try {
			$result = $this->delete_from_manager( $package_id, $output );
			$this->output_result( $result, $format, $output, $io );
			return 0;
		} catch ( \Exception $e ) {
			if ( $format === 'json' ) {
				$output->writeln( json_encode( [
					'success'    => false,
					'error'      => $e->getMessage(),
					'package_id' => $package_id,
				], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
			} else {
				$io->error( $e->getMessage() );
			}
			return 1;
		}
	}

	/**
	 * Delete package from Manager endpoint
	 *
	 * @return array<string, mixed>
	 */
	private function delete_from_manager( string $package_id, OutputInterface $output ): array {
		$output->writeln( '🗑️  Deleting from QIT registry...' );

		$response = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v2/cli/delete-test-package' ) )
			->with_method( 'POST' )
			->with_post_body( [
				'package_id' => $package_id,
			] )
			->request();

		$data = json_decode( $response, true );

		if ( ! is_array( $data ) ) {
			throw new \RuntimeException( 'Invalid response from delete API' );
		}

		if ( isset( $data['code'] ) && $data['code'] !== 200 ) {
			$error_message = $data['message'] ?? 'Delete failed';
			throw new \RuntimeException( $error_message );
		}

		return $data;
	}

	/**
	 * Output deletion result
	 *
	 * @param array<string, mixed> $result
	 */
	private function output_result( array $result, string $format, OutputInterface $output, SymfonyStyle $io ): void {
		if ( $format === 'json' ) {
			$output->writeln( json_encode( [
				'success'    => true,
				'message'    => $result['message'] ?? 'Package deleted successfully',
				'package_id' => $result['package_id'] ?? null,
			], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
		} else {
			$io->success( $result['message'] ?? 'Package deleted successfully! 🎉' );
		}
	}
}
