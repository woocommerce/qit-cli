<?php
/**
 * Helper Functions for QIT AI Webserver
 *
 * Contains utility functions used by the webserver endpoints.
 */

/**
 * Safely remove a directory and its contents
 */
function remove_directory_safely( $dir ) {
	if ( ! is_dir( $dir ) ) {
		log_debug( 'Not a directory, skipping removal', [ 'path' => $dir ] );

		return;
	}

	log_debug( 'Removing directory safely', [ 'dir' => $dir ] );

	$files      = array_diff( scandir( $dir ), [ '.', '..' ] );
	$file_count = 0;
	$dir_count  = 0;

	foreach ( $files as $file ) {
		$path = $dir . '/' . $file;
		if ( is_dir( $path ) ) {
			++$dir_count;
			remove_directory_safely( $path );
		} else {
			++$file_count;
			unlink( $path );
		}
	}

	rmdir( $dir );

	log_debug( 'Directory removed', [
		'dir'             => $dir,
		'files_removed'   => $file_count,
		'subdirs_removed' => $dir_count,
	] );
}

/**
 * Cleanup old sessions periodically
 */
function cleanup_old_sessions() {
	$cache_dir = sys_get_temp_dir() . '/qit-code-analysis';

	if ( ! is_dir( $cache_dir ) ) {
		log_debug( 'Cache directory does not exist, skipping cleanup', [ 'path' => $cache_dir ] );

		return;
	}

	log_debug( 'Starting cleanup of old sessions', [ 'cache_dir' => $cache_dir ] );

	$now          = time();
	$dirs_scanned = 0;
	$dirs_removed = 0;
	$dirs_skipped = 0;
	$dirs_invalid = 0;

	foreach ( scandir( $cache_dir ) as $dir ) {
		if ( $dir === '.' || $dir === '..' ) {
			continue;
		}

		++$dirs_scanned;
		$session_dir = $cache_dir . '/' . $dir;
		$real_path   = realpath( $session_dir );

		// Verify it's really inside cache_dir
		if ( $real_path === false || strpos( $real_path, realpath( $cache_dir ) ) !== 0 ) {
			log_warning( 'Skipping directory outside cache_dir', [ 'dir' => $session_dir ] );
			++$dirs_invalid;
			continue;
		}

		if ( is_dir( $real_path ) ) {
			$mtime     = filemtime( $real_path );
			$age_hours = round( ( $now - $mtime ) / 3600, 1 );

			if ( $now - $mtime > 3600 ) { // 1 hour old
				log_info( 'Removing old session directory', [
					'dir'       => $dir,
					'age_hours' => $age_hours,
				] );

				// Use PHP's recursive directory removal instead of exec
				remove_directory_safely( $real_path );
				++$dirs_removed;
			} else {
				log_debug( 'Skipping recent session directory', [
					'dir'       => $dir,
					'age_hours' => $age_hours,
				] );
				++$dirs_skipped;
			}
		}
	}

	log_info( 'Session cleanup completed', [
		'dirs_scanned' => $dirs_scanned,
		'dirs_removed' => $dirs_removed,
		'dirs_skipped' => $dirs_skipped,
		'dirs_invalid' => $dirs_invalid,
	] );
}
