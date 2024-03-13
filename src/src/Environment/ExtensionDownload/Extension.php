<?php

namespace QIT_CLI\Environment\ExtensionDownload;

class Extension {
	/** @var string */
	public $extension_identifier;

	/** @var 'plugin'|'theme' */
	public $type;

	/**
	 * @var string A FQDN for an instance of Handler.
	 * @see \QIT_CLI\Environment\ExtensionDownload\Handlers\Handler
	 */
	public $handler;

	/** @var string */
	public $path;

	/** @var string */
	public $version = 'undefined';

	/** @var string */
	public $download_url;
}
