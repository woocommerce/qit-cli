<?php

namespace QIT_CLI\Environment;

class Extension {
	/** @var array<string> */
	const TYPES = [
		'plugin' => 'plugin',
		'theme'  => 'theme',
	];

	const PRIORITY_LOW    = 10;
	const PRIORITY_MEDIUM = 50;
	const PRIORITY_HIGH   = 100;
	
	public string $slug;

	/** @var string The entrypoint of the extension, the main PHP file if a plugin or style.css if a theme. */
	public string $entrypoint;

	/** @var string|int The "source" can be a slug, a URL, a directory or a zip file. */
	public $source;

	/** @var string|int|null The file or directory of the source once it's downloaded (or, if it was already a local file, points to it). */
	public $downloaded_source;

	/**
	 * @see Extension::TYPES
	 * @var string
	 */
	public string $type;

	/** @var string A FQDN for an instance of Handler. */
	public string $handler;

	public string $version = 'undefined';

	/** @var string|null Action for the extension, set by commands (e.g., 'activate', 'test'). */
	public ?string $action;

	/** @var array<string>|null Test tags for testing, set by testing commands. */
	public ?array $test_tags;

	public int $priority = self::PRIORITY_MEDIUM;

	/** @var int|null */
	public ?int $wccom_id;
}
