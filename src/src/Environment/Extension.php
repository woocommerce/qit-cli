<?php

namespace QIT_CLI\Environment;

use lucatume\DI52\App;
use QIT_CLI\WooExtensionsList;

class Extension {
	/** @var array<string> Supported extension types. */
	const TYPES = [
		'plugin' => 'plugin',
		'theme'  => 'theme',
	];

	const ACTIONS = [
		'activate'  => 'activate',
		'bootstrap' => 'bootstrap',
		'test'      => 'test',
	];

	const PRIORITY_LOW    = 10;
	const PRIORITY_MEDIUM = 50;
	const PRIORITY_HIGH   = 100;

	/** @var string The unique identifier (slug) of the extension. */
	public $slug;

	/** @var string|null The entrypoint file (e.g., main PHP file for plugins, style.css for themes). */
	public $entrypoint;

	/** @var string|int|null The source (slug, URL, directory, or zip file). */
	public $source;

	/** @var string|null The path to the local directory, if applicable. */
	public $directory;

	/** @var string|int|null The path to the downloaded source, if applicable. */
	public $downloaded_source;

	/** @var string The type of extension ('plugin' or 'theme'). */
	public $type;

	/** @var string|null Fully qualified domain name for the handler class. */
	public $handler;

	/** @var string Version of the extension, defaults to 'undefined'. */
	public $version = 'undefined';

	/** @var string|null Action for the extension, set by commands (e.g., 'activate', 'test'). */
	public $action;

	/** @var array<string>|null Test tags for testing, set by testing commands. */
	public $test_tags;

	/** @var int Priority for processing, defaults to PRIORITY_MEDIUM. */
	public $priority = self::PRIORITY_MEDIUM;

	/** @var int|null WooCommerce.com ID, if applicable. */
	public $wccom_id;

	/** @var string|null Reason for automatic addition, if applicable. */
	public $added_automatically = null;

	/** @var string|null The source type of the extension ('wporg', 'wccom', 'local', 'zip'). */
	public $from = null;

	public function populate_from(): void {
		if ( ! $this->source ) {
			return;
		}

		if ( strpos( $this->source, 'wordpress.org' ) !== false ) {
			$this->from = 'wporg';
		} elseif ( strpos( $this->source, 'woocommerce.com' ) !== false ) {
			$this->from     = 'wccom';
			$this->wccom_id = App::make( WooExtensionsList::class )->get_woo_extension_id_by_slug( $this->slug );
		} elseif ( is_dir( $this->source ) || is_file( $this->source ) ) {
			$this->from = 'local';
		} elseif ( strpos( $this->source, '.zip' ) !== false ) {
			$this->from = 'zip';
		}
	}

	/**
	 * @param string          $slug The extension slug.
	 * @param string          $type The extension type ('plugin' or 'theme').
	 * @param string|int|null $source Optional source (slug, URL, directory, or zip file).
	 */
	public function __construct( string $slug, string $type, $source = null ) {
		if ( ! in_array( $type, self::TYPES, true ) ) {
			throw new \InvalidArgumentException( "Invalid extension type: $type. Must be one of: " . implode( ', ', self::TYPES ) );
		}
		$this->slug   = $slug;
		$this->type   = $type;
		$this->source = $source;
	}
}
