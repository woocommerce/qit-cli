<?php

namespace QIT_CLI\PreCommand\Download\Extensions;

use QIT_CLI\Environment\Extension;
use Symfony\Component\Console\Output\OutputInterface;

abstract class CustomHandler extends Handler {
	public function __construct( OutputInterface $output ) {
		parent::__construct( $output );
	}

	/**
	 * Determines if this handler should process the given extension.
	 *
	 * @param Extension $extension The extension to check.
	 *
	 * @return bool True if the handler should process the extension, false otherwise.
	 */
	abstract public function should_handle( Extension $extension ): bool;
}