<?php

namespace QIT_CLI\PreCommand\ConfigFile\Parsers;

class CustomTestPackageParser extends AbstractConfigParser {
	public function parse( $value, array $context = [] ): array {
		if ( ! is_array( $value ) ) {
			throw new \RuntimeException( 'Test packages must be an array.' );
		}

		foreach ( $value as $test_type => $packages ) {
			if ( ! is_string( $test_type ) ) {
				throw new \RuntimeException( 'Test package test type must be a string.' );
			}
			if ( ! is_array( $packages ) ) {
				throw new \RuntimeException( "Packages for test type '$test_type' must be an array." );
			}
			foreach ( $packages as $package_name => $config ) {
				if ( ! is_string( $package_name ) ) {
					throw new \RuntimeException( "Package name for test type '$test_type' must be a string." );
				}
				if ( ! is_array( $config ) ) {
					throw new \RuntimeException( "Configuration for test package '$test_type:$package_name' must be an array." );
				}
				foreach ( $config as $config_key => $config_value ) {
					switch ( $config_key ) {
						case 'extends':
						case 'test_dir':
						case 'description':
						case 'test_command':
							if ( ! is_string( $config_value ) ) {
								throw new \RuntimeException( "'$config_key' in test package '$test_type:$package_name' must be a string." );
							}
							break;
						case 'test_results':
							if ( ! is_array( $config_value ) ) {
								throw new \RuntimeException( "test_results in test package '$test_type:$package_name' must be an array." );
							}
							foreach ( $config_value as $result_type => $result_path ) {
								if ( ! is_string( $result_type ) ) {
									throw new \RuntimeException( "Test result type in '$test_type:$package_name' must be a string." );
								}
								if ( ! is_string( $result_path ) ) {
									throw new \RuntimeException( "Test result path for '$result_type' in '$test_type:$package_name' must be a string." );
								}
							}
							break;
						case 'lifecycle':
							if ( ! is_array( $config_value ) ) {
								throw new \RuntimeException( "lifecycle in test package '$test_type:$package_name' must be an array." );
							}
							foreach ( $config_value as $lifecycle_phase => $scripts ) {
								if ( ! in_array( $lifecycle_phase, [ 'before_all_tests', 'after_all_tests', 'before_sut_tests', 'after_sut_tests' ] ) ) {
									throw new \RuntimeException( "Invalid lifecycle phase '$lifecycle_phase' in '$test_type:$package_name'. Must be one of: before_all_tests, after_all_tests, before_sut_tests, after_sut_tests." );
								}
								if ( ! is_array( $scripts ) ) {
									throw new \RuntimeException( "Scripts for lifecycle phase '$lifecycle_phase' in '$test_type:$package_name' must be an array." );
								}
								foreach ( $scripts as $index => $script ) {
									if ( ! is_array( $script ) ) {
										throw new \RuntimeException( "Script at index $index in lifecycle phase '$lifecycle_phase' in '$test_type:$package_name' must be an array." );
									}
									if ( ! isset( $script['command'] ) || ! is_string( $script['command'] ) ) {
										throw new \RuntimeException( "Script at index $index in lifecycle phase '$lifecycle_phase' in '$test_type:$package_name' must have a 'command' key with a string value." );
									}
									if ( isset( $script['priority'] ) && ! is_int( $script['priority'] ) ) {
										throw new \RuntimeException( "Priority at index $index in lifecycle phase '$lifecycle_phase' in '$test_type:$package_name' must be an integer." );
									}
									if ( isset( $script['runs_on'] ) && ! in_array( $script['runs_on'], [ 'docker', 'host' ] ) ) {
										throw new \RuntimeException( "runs_on at index $index in lifecycle phase '$lifecycle_phase' in '$test_type:$package_name' must be 'docker' or 'host'." );
									}
								}
							}
							break;
						case 'mu_plugins':
						case 'required_secrets':
							if ( ! is_array( $config_value ) ) {
								throw new \RuntimeException( "$config_key in test package '$test_type:$package_name' must be an array." );
							}
							foreach ( $config_value as $item ) {
								if ( ! is_string( $item ) ) {
									throw new \RuntimeException( "Item in $config_key in '$test_type:$package_name' must be a string." );
								}
							}
							break;
						case 'env_vars':
							if ( ! is_array( $config_value ) ) {
								throw new \RuntimeException( "env_vars in test package '$test_type:$package_name' must be an array." );
							}
							foreach ( $config_value as $var_name => $var_value ) {
								if ( ! is_string( $var_name ) ) {
									throw new \RuntimeException( "Environment variable name in '$test_type:$package_name' must be a string." );
								}
								if ( ! is_string( $var_value ) ) {
									throw new \RuntimeException( "Environment variable value for '$var_name' in '$test_type:$package_name' must be a string." );
								}
							}
							break;
						default:
							throw new \RuntimeException( "Unknown key '$config_key' in test package '$test_type:$package_name' configuration." );
					}
				}
			}
		}

		return $this->resolve_extends( $value, 'test package' );
	}
}