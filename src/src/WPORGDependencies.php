<?php

namespace QIT_CLI;

class WPORGDependencies {
	/** @var Cache $cache */
	protected $cache;

	public function __construct( Cache $cache ) {
		$this->cache = $cache;
	}


}