<?php

namespace QIT_CLI\Environment\Environments\E2E;

use QIT_CLI\Environment\Environments\EnvInfo;

class E2EEnvInfo extends EnvInfo {
	/** @var string */
	public $environment = 'e2e';

	/** @var string The site URL, if any. */
	public $site_url;

	/** @var string */
	public $wordpress_version = '';

	/** @var bool */
	public $redis = false;

	/** @var string */
	public $php_version;
}
