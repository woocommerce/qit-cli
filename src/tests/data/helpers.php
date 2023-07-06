<?php

namespace QIT_CLI_Tests\data;

function calculate_directory_checksum( string $dir ): string {
	$checksum = hash_init( 'sha256' );
	$it       = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $dir, \FilesystemIterator::SKIP_DOTS ) );

	foreach ( $it as $filename => $fileInfo ) {
		if ( $fileInfo->isDir() ) {
			continue;
		}

		hash_update( $checksum, file_get_contents( $filename ) );
	}

	return hash_final( $checksum );
}