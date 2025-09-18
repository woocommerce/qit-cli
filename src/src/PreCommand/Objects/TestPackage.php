<?php
namespace QIT_CLI\PreCommand\Objects;

/**
 * **MVP** representation – just enough for the tests.
 *
 * Keep it immutable; we'll extend later with version, source, etc.
 */
class TestPackage implements \JsonSerializable {
	/** @var string "woocommerce/checkout" */
	public string $slug;
	/** @var string "stable", "v1.2.3" … */
	public string $version;

	public function __construct( string $slug, string $version ) {
		$this->slug    = $slug;
		$this->version = $version;
	}

	public static function fromString( string $spec ): self {
		if ( strpos( $spec, ':' ) === false ) {
			throw new \InvalidArgumentException( "Package specification '$spec' is missing a version. Please specify a version (e.g., '$spec:latest' or '$spec:1.0.0')" );
		}
		[$slug, $version] = explode( ':', $spec, 2 );
		return new self( $slug, $version );
	}

	/**
	 * @return array{slug: string, version: string}
	 */
	public function jsonSerialize(): array {
		return [
			'slug'    => $this->slug,
			'version' => $this->version,
		];
	}
}
