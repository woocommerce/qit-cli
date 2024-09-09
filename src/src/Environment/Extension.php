<?php

namespace QIT_CLI\Environment;

class Extension {
	/** @var array<string> */
	const ACTIONS = [
		'activate'  => 'activate',
		'bootstrap' => 'bootstrap',
		'test'      => 'test',
	];

	/** @var array<string> */
	const TYPES = [
		'plugin' => 'plugin',
		'theme'  => 'theme',
	];

	/** @var string */
	public $slug;

	/** @var string The entrypoint of the extension, the main PHP file if a plugin or style.css if a theme. */
	public $entrypoint;

	/** @var string|int The "source" can be a slug, a URL, a directory or a zip file. */
	public $source;

	/** @var string|int The file or directory of the source once it's downloaded (or, if it was already a local file, points to it). */
	public $downloaded_source;

	/**
	 * @see Extension::TYPES
	 * @var string
	 */
	public $type;

	/**
	 * @var string A FQDN for an instance of Handler.
	 * @see \QIT_CLI\Environment\ExtensionDownload\Handlers\Handler
	 */
	public $handler;

	/** @var string */
	public $version = 'undefined';

	/**
	 * @see Extension::ACTIONS
	 * @var string
	 */
	public $action;

	/**
	 * @var array<string,string> Test tags to fetch for this extension.
	 */
	public $test_tags;
}
