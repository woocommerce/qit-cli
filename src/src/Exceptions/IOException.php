<?php

namespace QIT_CLI\Exceptions;

class IOException extends \Exception {
	public static function cant_write_to_file( string $filepath ) {
		return new self( "Can't write to file, please check if it's writable: $filepath" );
	}
}