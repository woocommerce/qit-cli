<?php

namespace QIT_CLI\Config\Parsers;

class CustomTestPackageParser extends AbstractConfigParser {
	public function parse( $value, array $context = [] ): array {
		if ( ! is_array( $value ) ) {
			throw new \RuntimeException( 'Custom test packages must be an array.' );
		}

		foreach ( $value as $test_type => $packages ) {
			if ( ! is_string( $test_type ) ) {
				throw new \RuntimeException( 'Custom test package test type must be a string.' );
			}
			if ( ! is_array( $packages ) ) {
				throw new \RuntimeException( "Packages for test type '$test_type' must be an array." );
			}
			foreach ( $packages as $package_name => $config ) {
				if ( ! is_string( $package_name ) ) {
					throw new \RuntimeException( "Package name for test type '$test_type' must be a string." );
				}
				if ( ! is_array( $config ) ) {
					throw new \RuntimeException( "Configuration for custom test package '$test_type:$package_name' must be an array." );
				}
				foreach ( $config as $config_key => $config_value ) {
					switch ( $config_key ) {
						case 'extends':
						case 'root_path':
						case 'description':
						case 'test_command':
							if ( ! is_string( $config_value ) ) {
								throw new \RuntimeException( "'$config_key' in custom test package '$test_type:$package_name' must be a string." );
							}
							break;
						case 'test_results':
							if ( ! is_array( $config_value ) ) {
								throw new \RuntimeException( "test_results in custom test package '$test_type:$package_name' must be an array." );
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
								throw new \RuntimeException( "lifecycle in custom test package '$test_type:$package_name' must be an array." );
							}
							foreach ( $config_value as $lifecycle_phase => $lifecycle_script ) {
								if ( ! in_array( $lifecycle_phase, [ 'setup', 'teardown' ] ) ) {
									throw new \RuntimeException( "Invalid lifecycle phase '$lifecycle_phase' in '$test_type:$package_name'. Must be 'setup' or 'teardown'." );
								}
								if ( ! is_string( $lifecycle_script ) ) {
									throw new \RuntimeException( "Lifecycle script for '$lifecycle_phase' in '$test_type:$package_name' must be a string." );
								}
							}
							break;
						case 'mu_plugins':
							if ( ! is_array( $config_value ) ) {
								throw new \RuntimeException( "mu_plugins in custom test package '$test_type:$package_name' must be an array." );
							}
							foreach ( $config_value as $plugin_path ) {
								if ( ! is_string( $plugin_path ) ) {
									throw new \RuntimeException( "MU plugin path in '$test_type:$package_name' must be a string." );
								}
							}
							break;
						case 'constraints':
							if ( ! is_array( $config_value ) ) {
								throw new \RuntimeException( "constraints in custom test package '$test_type:$package_name' must be an array." );
							}
							foreach ( $config_value as $constraint_type => $constraint_value ) {
								if ( $constraint_type === 'wordpress' ) {
									if ( ! is_string( $constraint_value ) ) {
										throw new \RuntimeException( "WordPress constraint in '$test_type:$package_name' must be a string." );
									}
								} elseif ( $constraint_type === 'requires_plugins' ) {
									if ( ! is_array( $constraint_value ) ) {
										throw new \RuntimeException( "requires_plugins in '$test_type:$package_name' must be an array." );
									}
									foreach ( $constraint_value as $plugin_name => $plugin_version ) {
										if ( ! is_string( $plugin_name ) ) {
											throw new \RuntimeException( "Plugin name in requires_plugins for '$test_type:$package_name' must be a string." );
										}
										if ( ! is_string( $plugin_version ) ) {
											throw new \RuntimeException( "Plugin version for '$plugin_name' in '$test_type:$package_name' must be a string." );
										}
									}
								} else {
									throw new \RuntimeException( "Unknown constraint type '$constraint_type' in '$test_type:$package_name'." );
								}
							}
							break;
						case 'required_secrets':
							if ( ! is_array( $config_value ) ) {
								throw new \RuntimeException( "required_secrets in custom test package '$test_type:$package_name' must be an array." );
							}
							foreach ( $config_value as $secret ) {
								if ( ! is_string( $secret ) ) {
									throw new \RuntimeException( "Required secret in '$test_type:$package_name' must be a string." );
								}
							}
							break;
						case 'env_vars':
							if ( ! is_array( $config_value ) ) {
								throw new \RuntimeException( "env_vars in custom test package '$test_type:$package_name' must be an array." );
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
							throw new \RuntimeException( "Unknown key '$config_key' in custom test package '$test_type:$package_name' configuration." );
					}
				}
			}
		}

		return $this->resolve_extends( $value, 'custom test package' );
	}
}