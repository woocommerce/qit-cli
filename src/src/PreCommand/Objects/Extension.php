<?php

namespace QIT_CLI\PreCommand\Objects;

use lucatume\DI52\App;
use QIT_CLI\WooExtensionsList;

class Extension implements \JsonSerializable {
	/** @var array<string> Supported extension types. */
	const TYPES = [
		'plugin' => 'plugin',
		'theme'  => 'theme',
	];

	const PRIORITY_LOW    = 10;
	const PRIORITY_MEDIUM = 50;
	const PRIORITY_HIGH   = 100;

	/** @var string The unique identifier (slug) of the extension. */
	public $slug;

	/** @var string|null The entrypoint file (e.g., main PHP file for plugins, style.css for themes). */
	public $entrypoint;

	/** @var string|int|array<string,mixed>|null The source (slug, URL, directory, zip file, or build config). */
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

	/** @var int Priority for processing, defaults to PRIORITY_MEDIUM. */
	public $priority = self::PRIORITY_MEDIUM;

	/** @var int|null WooCommerce.com ID, if applicable. */
	public $wccom_id;

	/** @var string|null Reason for automatic addition, if applicable. */
	public $added_automatically = null;

	/** @var string|null The source type of the extension (see qit json schema). */
	public $from = null;

	/** @var string|null Build command to run before using a local source. */
	public $build_command = null;

	/** @var string|null Working directory for the build command. */
	public $build_cwd = null;

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
		}
	}

	/**
	 * @param string                              $slug The extension slug.
	 * @param string                              $type The extension type ('plugin' or 'theme').
	 * @param string|int|array<string,mixed>|null $source Optional source (slug, URL, directory, zip file, or build config).
	 */
	public function __construct( string $slug, string $type, $source = null ) {
		if ( ! in_array( $type, self::TYPES, true ) ) {
			throw new \InvalidArgumentException( "Invalid extension type: $type. Must be one of: " . implode( ', ', self::TYPES ) );
		}
		$this->slug   = $slug;
		$this->type   = $type;
		$this->source = $source;
	}

	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return array_filter( [
			'slug'                => $this->slug,
			'type'                => $this->type,
			'from'                => $this->from,
			'version'             => $this->version,
			'source'              => $this->source,
			'directory'           => $this->directory,
			'downloaded_source'   => $this->downloaded_source,
			'entrypoint'          => $this->entrypoint,
			'handler'             => $this->handler,
			'priority'            => $this->priority,
			'wccom_id'            => $this->wccom_id,
			'added_automatically' => $this->added_automatically,
			'build_command'       => $this->build_command,
			'build_cwd'           => $this->build_cwd,
			// phpcs:ignore PEAR.Functions.FunctionCallSignature.Indent -- WordPress standards conflict with PEAR on multi-line function indentation
		], function ( $value ) {
			return $value !== null;
		} );
	}

	/**
	 * Create an Extension object from an array.
	 *
	 * @param array<string,mixed> $data The array data.
	 *
	 * @return Extension The deserialized Extension object.
	 */
	public static function fromArray( array $data ): Extension {
		// Slug is required - fail loudly if missing
		if ( ! isset( $data['slug'] ) || $data['slug'] === '' ) {
			$e = new \InvalidArgumentException(
				'Extension slug is required but missing or empty. Data: ' . json_encode( $data )
			);
			throw $e;
		}

		// Type is required - fail loudly if missing
		if ( ! isset( $data['type'] ) || ! in_array( $data['type'], self::TYPES, true ) ) {
			throw new \InvalidArgumentException(
				'Extension type must be "plugin" or "theme". Got: ' . ( $data['type'] ?? 'null' )
			);
		}

		$extension                      = new Extension(
			$data['slug'],
			$data['type'],
			$data['source'] ?? null
		);
		$extension->from                = $data['from'] ?? null;
		$extension->version             = $data['version'] ?? 'undefined';
		$extension->directory           = $data['directory'] ?? null;
		$extension->downloaded_source   = $data['downloaded_source'] ?? null;
		$extension->entrypoint          = $data['entrypoint'] ?? null;
		$extension->handler             = $data['handler'] ?? null;
		$extension->priority            = $data['priority'] ?? self::PRIORITY_MEDIUM;
		$extension->wccom_id            = $data['wccom_id'] ?? null;
		$extension->added_automatically = $data['added_automatically'] ?? null;
		$extension->build_command       = $data['build_command'] ?? null;
		$extension->build_cwd           = $data['build_cwd'] ?? null;

		return $extension;
	}
}
