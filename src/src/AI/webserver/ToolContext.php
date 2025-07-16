<?php

namespace QIT_AI_Webserver;

final class ToolContext {

	public function __construct(
		public string $wp_root,
		public string $sut_dir,
		public array $deps   // each: ['slug' => ..., 'type' => ..., 'path' => ...]
	) {}
}
