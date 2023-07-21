<?php


namespace QIT_CLI\Fixer\Exceptions;

class SecurityFixerException extends \Exception {
	public static function plugin_dir_not_found( string $plugin_dir ): self {
		return new self( sprintf( 'Plugin directory %s not found.', $plugin_dir ) );
	}

	public static function test_result_json_invalid(): self {
		return new self( 'Test result JSON is invalid.' );
	}

	public static function unexpected_filepath(): self {
		return new self( 'Unexpected file path in JSON.' );
	}

	public static function plugin_path_does_not_match( string $local_plugin_basename, string $result_plugin_basename ): self {
		return new self( sprintf( 'Plugin path does not match. Expected %s, but local folder is %s.', $result_plugin_basename, $local_plugin_basename ) );
	}

	public static function file_does_not_exist_locally( string $file_path ): self {
		return new self( sprintf( 'File %s does not exist locally.', $file_path ) );
	}

	public static function too_many_files_not_found(): self {
		return new self( 'The plugin in the test report seems substantially different than the one you are trying to fix locally. Please check if the provided path matches the path in the plugin from the test report.' );
	}
}
