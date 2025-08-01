<?php

namespace QIT_CLI\PreCommand\Objects;

/**
 * Value object representing System-Under-Test (SUT) input from CLI arguments.
 *
 * This class encapsulates SUT configuration that comes from CLI parameters,
 * providing a clean interface for the SUT resolution logic.
 */
final class SutInput {
	public string $slug;
	public string $type;   // 'plugin'|'theme'

	/** @var array<string,mixed> */
	public array $source;  // {type: 'url'|'local'|'wporg'|'build' ...}

	/** @var bool Did this come from CLI (vs qit.json)? */
	public bool $from_cli = false;

	/**
	 * Constructor for SutInput.
	 */
	public function __construct( string $slug = '', string $type = 'plugin' ) {
		$this->slug     = $slug;
		$this->type     = $type;
		$this->source   = [ 'type' => 'wporg' ];
		$this->from_cli = true;
	}

	/**
	 * Convert to array format compatible with existing SUT configuration.
	 *
	 * @return array<string,mixed>
	 */
	public function to_array(): array {
		return [
			'slug'   => $this->slug,
			'type'   => $this->type,
			'source' => $this->source,
		];
	}

	/**
	 * Create SutInput from array data.
	 *
	 * @param array<string,mixed> $data
	 */
	public static function from_array( array $data ): self {
		$sut           = new self();
		$sut->slug     = $data['slug'] ?? '';
		$sut->type     = $data['type'] ?? 'plugin';
		$sut->source   = $data['source'] ?? [];
		$sut->from_cli = $data['from_cli'] ?? false;

		return $sut;
	}
}
