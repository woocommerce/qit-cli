<?php

namespace QIT_AI_Webserver;

final class ToolContext {

	public function __construct(
		public readonly string $wp_root,
		public readonly string $sut_dir,
		public readonly array $deps   // each: ['slug' => ..., 'type' => ..., 'path' => ...]
	) {}
}
