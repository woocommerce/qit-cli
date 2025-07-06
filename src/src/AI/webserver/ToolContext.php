<?php

namespace QIT_AI_Webserver;

final class ToolContext
{
    public function __construct(
        public readonly string $wpRoot,
        public readonly string $sutDir,
        public readonly array  $deps   // each: ['slug' => ..., 'type' => ..., 'path' => ...]
    ) {}
}