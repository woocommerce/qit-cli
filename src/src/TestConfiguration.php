<?php

namespace QIT_CLI;

use QIT_CLI\PreCommand\Objects\Extension;

class TestConfiguration {
	public Extension $extension;

	public string $action;

	/** @var array<string> Test tags to run. */
	public array $test_tags;

	public ?string $version;

	public function __construct( Extension $extension, string $action, array $test_tags = [ 'default' ], ?string $version = null ) {
		$this->extension = $extension;
		$this->action    = $action;
		$this->test_tags = $test_tags;
		$this->version   = $version;
	}
}
