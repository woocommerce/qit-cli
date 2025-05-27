<?php

namespace QIT_CLI\PreCommand;

use QIT_CLI\PreCommand\ConfigFile\ConfigParser;

class TestProfileHandler {
	public function load_profile( string $test_type, string $profile, ConfigParser $config ): array {
		$config_data = $config->get_test_config( $test_type, $profile );
		if ( empty( $config_data ) ) {
			throw new \RuntimeException( "Test profile '$profile' for test type '$test_type' not found." );
		}

		return $config_data;
	}
}
