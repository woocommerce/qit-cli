<?php

namespace QIT_CLI\Environment\ExtensionDownload;

class Extension {
	/** @var string */
	public $extension_identifier;

	/** @var string|int */
	public $source;

	/** @var 'plugin'|'theme' */
	public $type;

	/** @var string */
	public $slug;

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

	/** @var string */
	public $test_tag;
}
