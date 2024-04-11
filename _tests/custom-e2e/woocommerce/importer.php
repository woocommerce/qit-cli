<?php

$directory = new RecursiveDirectoryIterator( './tests' );
$iterator  = new RecursiveIteratorIterator( $directory );

foreach ( $iterator as $file ) {
	if ( $file->isFile() && $file->getExtension() === 'js' ) {
		$filePath     = $file->getRealPath();
		$fileContents = file_get_contents( $filePath );

		if ( strpos( $fileContents, 'qit.getEnv' ) !== false && strpos( $fileContents, '/qitHelpers' ) === false ) {
			$newContents = "const qit = require('/qitHelpers');\n" . $fileContents;
			file_put_contents( $filePath, $newContents );
			echo "Updated file: " . $filePath . "\n";
		}
	}
}
