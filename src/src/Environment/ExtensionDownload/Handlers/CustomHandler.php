<?php

namespace QIT_CLI\Environment\ExtensionDownload\Handlers;

use QIT_CLI\Environment\ExtensionDownload\Extension;

/**
 * Extend this class and include it with "--require=my-custom-handler.php"
 * when calling QIT to install premium/private plugins/themes from custom sources.
 */
abstract class CustomHandler extends Handler {
	/**
	 * @param Extension $extension
	 *
	 * @return bool True if this handler should handle this ID, false if not.
	 */
	abstract public function should_handle( Extension $extension ): bool;
}