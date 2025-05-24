<?php

namespace QIT_CLI\Commands\Utils;

use QIT_CLI\PreCommand\ConfigFile\QITConfig;

class TestProfileHandler {
	public function load_profile(string $test_type, string $profile, QITConfig $config): array {
		return $config->get_test_config($test_type, $profile);
	}
}