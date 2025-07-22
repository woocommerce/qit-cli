<?php
namespace QIT_CLI\PreCommand\Objects;

/**
 * **MVP** representation – just enough for the tests.
 *
 * Keep it immutable; we'll extend later with version, source, etc.
 */
class TestPackage implements \JsonSerializable {
	public string $slug;      // "woocommerce/checkout"
	public string $version;   // "stable", "v1.2.3" …

	public function __construct( string $slug, string $version ) {
		$this->slug    = $slug;
		$this->version = $version;
	}

	public static function fromString( string $spec ): self {
		[$slug, $version] = explode( ':', $spec, 2 ) + [1 => 'latest'];
		return new self( $slug, $version );
	}

	public function jsonSerialize(): mixed {
		return [ 'slug' => $this->slug, 'version' => $this->version ];
	}
}