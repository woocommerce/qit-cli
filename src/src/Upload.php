<?php

namespace QIT_CLI;

use Symfony\Component\Console\Helper\ProgressBar;
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
	 * @param int             $woo_extension_id The Woo extension ID that this development build is associated to.
	 * @param string          $extension_slug The Woo Extension slug.
	 * @param string          $zip_path The local Zip file path.
	 * @param OutputInterface $output The output instance.
	 *
	 * @return string The Upload ID.
	 */
	public function upload_build( int $woo_extension_id, string $extension_slug, string $zip_path, OutputInterface $output ): string {
		if ( ! file_exists( $zip_path ) ) {
			throw new \RuntimeException( sprintf( 'File %s does not exist.', $zip_path ) );
		}

		if ( ! is_readable( $zip_path ) ) {
			throw new \RuntimeException( sprintf( 'File %s is not readable.', $zip_path ) );
		}

		$this->check_zip_consistency( $zip_path, $output );

		$file = fopen( $zip_path, 'rb' );
		$stat = fstat( $file );

		$chunk_size_kb = App::getVar( 'UPLOAD_CHUNK_KB', 1024 * 4 );
		$current_chunk = 0;
		$total_chunks  = intval( ceil( $stat['size'] / ( $chunk_size_kb * 1024 ) ) );
		$cd_upload_id  = generate_uuid4();

		$progress_bar = new ProgressBar( $output, $total_chunks );
		$output->writeln( 'Uploading zip...' );
		$progress_bar->start();

		while ( ! feof( $file ) ) {
			$current_chunk ++;

			$r = $this->request_builder
					->with_url( get_manager_url() . '/wp-json/cd/v1/upload-build' )
					->with_method( 'POST' )
					->with_expected_status_codes( [ 200, 206 ] )
					->with_timeout_in_seconds( 60 )
					->with_post_body( [
						'cd_upload_id'     => $cd_upload_id,
						'woo_extension_id' => $woo_extension_id,
						'current_chunk'    => $current_chunk,
						'md5_sum'          => md5_file( $zip_path ),
						'total_chunks'     => $total_chunks,
						'chunk'            => base64_encode( fread( $file, $chunk_size_kb * 1024 ) ),
					] )
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

		if ( ! is_array( $parsed_response ) || empty( $parsed_response['upload_id'] ) ) {
			throw new \UnexpectedValueException( "The upload wasn't successful." );
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

		if ( $opened === 21 ) {
			$output->writeln( '<comment>Zip file failed consistency check. We will proceed with the upload, as macOS Archive Utility is known to generate zip files that are non-compliant with the Zip specification.</comment>' );
		}

		if ( $opened !== true ) {
			throw new \RuntimeException( 'This is not a valid zip file.' );
		}
	}
}
