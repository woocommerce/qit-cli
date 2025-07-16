<?php

namespace QIT_AI_Webserver;

final class ToolContext {
	/** @var string */
	public $wpRoot;
	
	/** @var string */
	public $sutDir;
	
	/** @var array<array{slug: string, type: string, path: string}> */
	public $deps;

	/** @var array<string, mixed>|null */
	public $path_context;

	/** @var \QIT_AI_Webserver\Lib\FactStore|null */
	public $fact_store;

	/**
	 * @param array<array{slug: string, type: string, path: string}> $deps
	 */
	public function __construct( string $wp_root, string $sut_dir, array $deps ) {
		$this->wpRoot = $wp_root;
		$this->sutDir = $sut_dir;
		$this->deps   = $deps;
		$this->path_context = null;
		$this->fact_store = null;
	}
}
