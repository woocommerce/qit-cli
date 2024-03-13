<?php

namespace QIT_CLI\Environment\ExtensionDownload;

use QIT_CLI\Environment\ExtensionDownload\Handlers\Handler;

class Extension {
	/** @var string */
	public $extension_identifier;

	/** @var 'plugin'|'theme' */
	public $type;

	/** @var Handler */
	public $handler;

	/** @var string */
	public $path;
}