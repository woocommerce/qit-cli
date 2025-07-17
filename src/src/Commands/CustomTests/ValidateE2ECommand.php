<?php

namespace QIT_CLI\Commands\CustomTests;

use QIT_CLI\Commands\QITCommand;
use QIT_CLI\Environment\Environments\EnvInfo;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class ValidateE2ECommand extends QITCommand {

	protected static $defaultName = 'validate:e2e'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected function configure(): void {
		parent::configure();
		$this
			->setDescription( 'Validates that an E2E test directory complies with the QIT Custom Tests specification.' )
			->addArgument( 'directory', InputArgument::REQUIRED, 'The test directory to validate' );
	}

	protected function doExecute( InputInterface $input, OutputInterface $output ): int {
		$directory = rtrim( $input->getArgument( 'directory' ), '/' );

		$checks = [];

		// 1. Must check: package.json
		$package_json_path = $directory . '/package.json';
		$has_package_json  = is_file( $package_json_path );
		$checks[]          = $this->makeCheck(
			'Has package.json (MUST)',
			$has_package_json,
			$has_package_json ? 'Found package.json' : 'No package.json found',
			'must'
		);

		// 2. Must check: "scripts.qit-e2e" in package.json
		$has_qit_e2_e_script = false;
		if ( $has_package_json ) {
			$package_json_content = file_get_contents( $package_json_path );
			$pkg                  = json_decode( $package_json_content, true );
			if ( json_last_error() === JSON_ERROR_NONE && is_array( $pkg ) ) {
				$qit_e2_e_script = $pkg['scripts']['qit-e2e'] ?? null;
				if ( ! empty( $qit_e2_e_script ) && is_string( $qit_e2_e_script ) ) {
					$has_qit_e2_e_script = true;
				}
			}
		}
		$checks[] = $this->makeCheck(
			'package.json has "qit-e2e" script (MUST)',
			$has_qit_e2_e_script,
			$has_qit_e2_e_script
				? 'Found scripts.qit-e2e in package.json'
				: 'No "qit-e2e" script found',
			'must'
		);

		// 3. Optional check: qit-e2e.(json|yml)
		$qit_json_path       = $directory . '/qit-e2e.json';
		$qit_yaml_path       = $directory . '/qit-e2e.yml';
		$has_qit_e2_e_config = false;
		$config_is_valid     = false;
		$config_data         = null;
		$config_file_used    = '';

		if ( is_file( $qit_json_path ) ) {
			$has_qit_e2_e_config = true;
			$config_file_used    = 'qit-e2e.json';
			$content             = file_get_contents( $qit_json_path );
			$parsed              = json_decode( $content, true );
			if ( json_last_error() === JSON_ERROR_NONE && is_array( $parsed ) ) {
				$config_is_valid = true;
				$config_data     = $parsed;
			}
		} elseif ( is_file( $qit_yaml_path ) ) {
			$has_qit_e2_e_config = true;
			$config_file_used    = 'qit-e2e.yml';
			$content             = file_get_contents( $qit_yaml_path );
			try {
				$parsed = Yaml::parse( $content );
				if ( is_array( $parsed ) ) {
					$config_is_valid = true;
					$config_data     = $parsed;
				}
			} catch ( \Exception $e ) {
				$config_is_valid = false;
			}
		}

		$checks[] = $this->makeCheck(
			'Has optional qit-e2e.(json|yml)',
			$has_qit_e2_e_config,
			$has_qit_e2_e_config
				? "Found $config_file_used"
				: 'Neither qit-e2e.json nor qit-e2e.yml found (defaults used)',
			'optional'
		);

		if ( $has_qit_e2_e_config ) {
			$checks[] = $this->makeCheck(
				'qit-e2e config parses correctly (MUST if present)',
				$config_is_valid,
				$config_is_valid
					? "Successfully parsed $config_file_used"
					: "Error parsing $config_file_used",
				'must'
			);
		}

		// 4. If NO config file, check for default bootstrap scripts (optional, but recommended).
		// The spec says it *falls back* to these if no config is present, so let's check for existence:
		if ( ! $has_qit_e2_e_config ) {
			$bootstrap_dir = $directory . '/bootstrap';
			$default_files = [
				'shared-setup.sh',
				'setup.sh',
				'teardown.sh',
				'shared-teardown.sh',
				'mu-plugin.php',
			];
			foreach ( $default_files as $f ) {
				$path     = $bootstrap_dir . '/' . $f;
				$checks[] = $this->makeCheck(
					"Default fallback file '$f' (optional but recommended)",
					is_file( $path ),
					is_file( $path )
						? "Found $f in bootstrap/ directory"
						: "Not found: $f",
					'optional'
				);
			}
		}

		// 5. If config file is present, check that listed script/plugin files actually exist
		// (We won't parse outside references, just check existence.)
		if ( $has_qit_e2_e_config && $config_is_valid && is_array( $config_data ) ) {
			$keys_to_check = [ 'sharedSetup', 'setup', 'teardown', 'sharedTeardown', 'muPlugins' ];
			foreach ( $keys_to_check as $key ) {
				if ( isset( $config_data[ $key ] ) && is_array( $config_data[ $key ] ) ) {
					foreach ( $config_data[ $key ] as $file ) {
						$full_path = $directory . '/' . $file;
						$checks[]  = $this->makeCheck(
							"$config_file_used [$key] references '$file' (MUST exist)",
							is_file( $full_path ),
							is_file( $full_path )
								? "Found $file"
								: "$file not found in $directory",
							'must'
						);
					}
				}
			}
		}

		// 6. Directory size < 128 MB (must)
		$dir_size   = $this->getDirectorySize( $directory );
		$under128mb = ( $dir_size < 128 * 1024 * 1024 );
		$checks[]   = $this->makeCheck(
			'Test directory size < 128MB (MUST)',
			$under128mb,
			$under128mb
				? 'Directory size: ' . $this->humanFileSize( $dir_size )
				: 'Directory too large: ' . $this->humanFileSize( $dir_size ),
			'must'
		);

		// -----------------------------
		// Output results (checklist)
		// -----------------------------
		$error_count   = 0;
		$warning_count = 0;
		foreach ( $checks as $check ) {

			if ( $check['severity'] === 'must' ) {
				// must => ✅ or ❌
				$mark = $check['pass'] ? "\u{2705}" : "\u{274C}"; // ✅ : ❌
			} else {
				// optional => use an info icon if pass, or a dashed circle if not present
				$mark = $check['pass'] ? "\u{2139}\u{FE0F}" : "\u{26A0}\u{FE0F}"; // ℹ️ or ⚠️
			}

			$output->writeln( "{$mark} {$check['label']} - {$check['info']}" );
			if ( ! $check['pass'] ) {
				if ( $check['severity'] === 'must' ) {
					++$error_count;
				} else {
					++$warning_count;
				}
			}
		}

		if ( $error_count > 0 ) {
			$output->writeln( "<error>{$error_count} required checks failed.</error>" );

			return Command::FAILURE;
		}

		if ( $warning_count > 0 ) {
			$output->writeln( "<comment>All required checks passed, but there are {$warning_count} optional warnings.</comment>" );

			return Command::SUCCESS;
		}

		$output->writeln( '<info>All checks passed (required + optional)!</info>' );

		return Command::SUCCESS;
	}

	/**
	 * Helper to create a standardized check array.
	 */
	private function makeCheck( string $label, bool $pass, string $info, string $severity = 'must' ): array {
		// severity = 'must' or 'optional'
		return [
			'label'    => $label,
			'pass'     => $pass,
			'info'     => $info,
			'severity' => $severity,
		];
	}

	/**
	 * Recursively compute size of directory contents.
	 */
	private function getDirectorySize( string $dir ): int {
		$size     = 0;
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $dir, \FilesystemIterator::SKIP_DOTS )
		);
		foreach ( $iterator as $file ) {
			$size += $file->getSize();
		}

		return $size;
	}

	/**
	 * Convert bytes to a human-readable string (e.g., "12.5 MB").
	 */
	private function humanFileSize( int $bytes, int $decimals = 2 ): string {
		$sz     = [ 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' ];
		$factor = floor( ( strlen( (string) $bytes ) - 1 ) / 3 );

		return sprintf( "%.{$decimals}f %s", $bytes / pow( 1024, $factor ), $sz[ $factor ] );
	}
}
