<?php

namespace QIT_CLI\Exceptions;

class InvalidZipException extends \Exception {
	public static function invalid_plugin_zip( string $zip_file, string $plugin_slug ): self {
		return new self( "Zip validation failed. We expected to find a parent directory named '$plugin_slug' in the root of the zip, if the parent directory can't be found, the zip name should be '$plugin_slug.zip'. It was '$zip_file'. Please check https://woocommerce.github.io/qit-documentation/#/zip for more information." );
	}
}
