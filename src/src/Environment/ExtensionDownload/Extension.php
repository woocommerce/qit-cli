<?php

namespace QIT_CLI\Environment\ExtensionDownload;

use QIT_CLI\Environment\ExtensionDownload\Handlers\Handler;

class Extension {
	/** @var string */
	public $extension_identifier;

	/** @var 'plugin'|'theme' */
	public $type;

	/**
	 * @var string A FQDN for an instance of Handler.
	 * @see Handler
	 */
	public $handler;

	/** @var string */
	public $path;

	/** @var string */
	public $version = 'undefined';

	/** @var string */
	public $download_url;
}
