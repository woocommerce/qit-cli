<?php

namespace QIT_CLI\Environment\ExtensionDownload\Handlers;

use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\ExtensionDownload\Extension;

abstract class Handler {
	abstract public function maybe_download( Extension $e, string $cache_dir, EnvInfo $env_info ): void;
}