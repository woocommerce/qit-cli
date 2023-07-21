<?php

namespace QIT_CLI_Tests\data;

function calculate_directory_checksum( string $dir ): array {
	$file_checksums = [];
	$it             = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $dir, \FilesystemIterator::SKIP_DOTS ) );

	foreach ( $it as $filename => $fileInfo ) {
		if ( $fileInfo->isDir() ) {
			continue;
		}

		$relative_path                    = substr( $filename, strlen( $dir ) + 1 );
		$file_checksums[ $relative_path ] = hash_file( 'sha256', $filename );
	}

	ksort( $file_checksums );

	return $file_checksums;
}
