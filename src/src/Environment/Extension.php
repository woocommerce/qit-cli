<?php

namespace QIT_CLI\Environment;

class Extension {
	public static $allowed_actions = [
		'install'   => 'install',
		'bootstrap' => 'bootstrap',
		'test'      => 'test',
	];

	/** @var string The input the user provided. */
	public $extension_identifier;

	/** @var string|int The "source" that points to the extension file or directory. */
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

	/** @var string The locall path to a extension file (to be used in the temporary environment) */
	public $path;

	/** @var string */
	public $version = 'undefined';

	/** @var string */
	public $download_url;

	/** @var string */
	public $test_tag;

	/**
	 * @var string<'install'|'bootstrap'|'test'>
	 */
	public $action;

	/**
	 * @var array{
	 *     source: string,
	 * } Keys are the test tags. Values are the source of the test, which is usually the same of the test tag, but can be overridden to be a local path as well.
	 */
	public $test_tags;
}
