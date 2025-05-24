<?php

namespace QIT_CLI\Commands\Utils;

use QIT_CLI\PreCommand\ConfigFile\QITConfig;

class TestProfileHandler {
	public function load_profile( string $test_type, string $profile, QITConfig $config ): array {
		$config_data = $config->get_test_config( $test_type, $profile );
		if ( empty( $config_data ) ) {
			throw new \RuntimeException( "Test profile '$profile' for test type '$test_type' not found." );
		}

		return $config_data;
	}
}