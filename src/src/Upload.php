<?php

namespace QIT_CLI;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use ZipArchive;

class Upload {
	/** @var array<RequestBuilder> $_requests The requests made by this Upload instance,
	 * Only available in Unit tests context.
	 */
	public $_requests;

	/** @var RequestBuilder $request_builder */
	protected $request_builder;

	public function __construct( RequestBuilder $request_builder ) {
		$this->request_builder = $request_builder;
	}

	/**
	 * @param string          $upload_type Either 'build' or 'custom-test'.
	 * @param int             $woo_extension_id The Woo extension ID that this development build is associated to.
	 * @param string          $zip_path The local Zip file path.
	 * @param OutputInterface $output The output instance.
	 * @param string          $test_type The test type, if a 'custom-test'.
	 *
	 * @return string The Upload ID or empty, if a custom test type.
	 */
	public function upload_build( string $upload_type, int $woo_extension_id, string $zip_path, OutputInterface $output, string $test_type = '', string $test_tag = '' ): string {
		if ( ! file_exists( $zip_path ) ) {
			throw new \RuntimeException( sprintf( 'File %s does not exist.', $zip_path ) );
		}

		if ( ! is_readable( $zip_path ) ) {
			throw new \RuntimeException( sprintf( 'File %s is not readable.', $zip_path ) );
		}

		if ( $upload_type === 'build' ) {
			$endpoint = '/wp-json/cd/v1/upload-build';
		} elseif ( $upload_type === 'test-report' ) {
			$endpoint = '/wp-json/cd/v1/upload-test-report';
		} elseif ( $upload_type === 'custom-test' ) {
			$endpoint = '/wp-json/cd/v1/cli/upload-test';
		} elseif ( $upload_type === 'test-media' ) {
			$endpoint = '/wp-json/cd/v1/upload-test-media';
		} else {
			throw new \InvalidArgumentException( 'Invalid upload type.' );
		}

		$this->check_zip_consistency( $zip_path, $output );

		$file = fopen( $zip_path, 'rb' );
		$stat = fstat( $file );

		$chunk_size_kb = App::getVar( 'UPLOAD_CHUNK_KB', 4096 ); // 4mb.
		$chunk_size_kb = $chunk_size_kb * 1024; // Converts KB to bytes.
		$current_chunk = 0;
		$total_chunks  = intval( ceil( $stat['size'] / ( $chunk_size_kb ) ) );
		$cd_upload_id  = generate_uuid4();

		$progress_bar_output = App::getVar( 'QIT_JSON_MODE' ) ? new NullOutput() : $output;

		$progress_bar = new ProgressBar( $progress_bar_output, $total_chunks );
		$output->writeln( 'Uploading zip...' );
		$progress_bar->start();

		while ( ! feof( $file ) ) {
			++$current_chunk;

			$data = [
				'cd_upload_id'     => $cd_upload_id,
				'woo_extension_id' => $woo_extension_id,
				'current_chunk'    => $current_chunk,
				'md5_sum'          => md5_file( $zip_path ),
				'total_chunks'     => $total_chunks,
				'chunk'            => base64_encode( fread( $file, $chunk_size_kb ) ),
			];

			if ( ! empty( $test_type ) ) {
				$data['test_type'] = $test_type;
			}

			if ( ! empty( $test_tag ) ) {
				$data['test_tag'] = $test_tag;
			}

			$r = $this->request_builder
					->with_url( get_manager_url() . $endpoint )
					->with_method( 'POST' )
					->with_expected_status_codes( [ 200, 206 ] )
					->with_timeout_in_seconds( 60 )
					->with_post_body( $data )
					->request();

			if ( defined( 'UNIT_TESTS' ) && UNIT_TESTS ) {
				$this->_requests[] = clone $this->request_builder;
			}

			$progress_bar->advance();
		}

		if ( empty( $r ) ) {
			throw new \UnexpectedValueException( "The upload wasn't successful." );
		}

		$parsed_response = json_decode( $r, true );

		if ( ! is_array( $parsed_response ) ) {
			throw new \UnexpectedValueException( "The upload wasn't successful. Expected a JSON response, got: " . $r );
		}

		if ( empty( $parsed_response['upload_id'] ) ) {
			throw new \UnexpectedValueException( "The upload wasn't successful. Expected 'upload_id' key not found in response." );
		}

		$progress_bar->finish();
		$output->writeln( '' );

		return $parsed_response['upload_id'];
	}

	/**
	 * Checks if the given zip file passes a zip consistency check.
	 *
	 * @param string          $filepath The zip file path.
	 * @param OutputInterface $output The output instance.
	 *
	 * @throws \RuntimeException If the file is not a zip file.
	 *
	 * @return void
	 */
	protected function check_zip_consistency( string $filepath, OutputInterface $output ) {
		$zip = new ZipArchive();

		$opened = $zip->open( $filepath, ZipArchive::CHECKCONS );

		// Early bail: Tolerable inconsistency.
		if ( $opened === 21 ) {
			$output->writeln( '<comment>ZIP file failed consistency check. We will proceed with the upload, as macOS Archive Utility is known to generate ZIP files that are non-compliant with the ZIP specification.</comment>' );

			return;
		}

		if ( $opened !== true ) {
			throw new \RuntimeException( 'This is not a valid ZIP file.' );
		}
	}
}
