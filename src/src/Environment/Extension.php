<?php

namespace QIT_CLI\Environment;

class Extension {
	public static $allowed_actions = [
		'install'   => 'install',
		'bootstrap' => 'bootstrap',
		'test'      => 'test',
	];

	public static $allowed_types = [
		'plugin' => 'plugin',
		'theme'  => 'theme',
	];

	/** @var string The input the user provided. */
	public $extension_identifier;

	/** @var string|int The "source" can be a slug, a URL, a directory or a zip file. */
	public $source;

	/** @var string|int The file or directory of the source once it's downloaded (or, if it was already a local file, points to it). */
	public $downloaded_source;

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
	public $version = 'undefined';

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
