<?php

namespace QIT_CLI\Commands\CustomTests;

use QIT_CLI\Commands\QITCommand;
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
		$packageJsonPath = $directory . '/package.json';
		$hasPackageJson  = is_file( $packageJsonPath );
		$checks[]        = $this->makeCheck(
			label: 'Has package.json (MUST)',
			pass: $hasPackageJson,
			info: $hasPackageJson
				? 'Found package.json'
				: 'No package.json found',
			severity: 'must'
		);

		// 2. Must check: "scripts.qit-e2e" in package.json
		$hasQitE2EScript = false;
		if ( $hasPackageJson ) {
			$packageJsonContent = file_get_contents( $packageJsonPath );
			$pkg                = json_decode( $packageJsonContent, true );
			if ( json_last_error() === JSON_ERROR_NONE && is_array( $pkg ) ) {
				$qitE2EScript = $pkg['scripts']['qit-e2e'] ?? null;
				if ( ! empty( $qitE2EScript ) && is_string( $qitE2EScript ) ) {
					$hasQitE2EScript = true;
				}
			}
		}
		$checks[] = $this->makeCheck(
			label: 'package.json has "qit-e2e" script (MUST)',
			pass: $hasQitE2EScript,
			info: $hasQitE2EScript
				? 'Found scripts.qit-e2e in package.json'
				: 'No "qit-e2e" script found',
			severity: 'must'
		);

		// 3. Optional check: qit-e2e.(json|yml)
		$qitJsonPath     = $directory . '/qit-e2e.json';
		$qitYamlPath     = $directory . '/qit-e2e.yml';
		$hasQitE2EConfig = false;
		$configIsValid   = false;
		$configData      = null;
		$configFileUsed  = '';

		if ( is_file( $qitJsonPath ) ) {
			$hasQitE2EConfig = true;
			$configFileUsed  = 'qit-e2e.json';
			$content         = file_get_contents( $qitJsonPath );
			$parsed          = json_decode( $content, true );
			if ( json_last_error() === JSON_ERROR_NONE && is_array( $parsed ) ) {
				$configIsValid = true;
				$configData    = $parsed;
			}
		} elseif ( is_file( $qitYamlPath ) ) {
			$hasQitE2EConfig = true;
			$configFileUsed  = 'qit-e2e.yml';
			$content         = file_get_contents( $qitYamlPath );
			try {
				$parsed = Yaml::parse( $content );
				if ( is_array( $parsed ) ) {
					$configIsValid = true;
					$configData    = $parsed;
				}
			} catch ( \Exception $e ) {
				$configIsValid = false;
			}
		}

		$checks[] = $this->makeCheck(
			label: 'Has optional qit-e2e.(json|yml)',
			pass: $hasQitE2EConfig,
			info: $hasQitE2EConfig
				? "Found $configFileUsed"
				: 'Neither qit-e2e.json nor qit-e2e.yml found (defaults used)',
			severity: 'optional'
		);

		if ( $hasQitE2EConfig ) {
			$checks[] = $this->makeCheck(
				label: 'qit-e2e config parses correctly (MUST if present)',
				pass: $configIsValid,
				info: $configIsValid
					? "Successfully parsed $configFileUsed"
					: "Error parsing $configFileUsed",
				severity: 'must'
			);
		}

		// 4. If NO config file, check for default bootstrap scripts (optional, but recommended).
		// The spec says it *falls back* to these if no config is present, so let's check for existence:
		if ( ! $hasQitE2EConfig ) {
			$bootstrapDir = $directory . '/bootstrap';
			$defaultFiles = [
				'shared-setup.sh',
				'setup.sh',
				'teardown.sh',
				'shared-teardown.sh',
				'mu-plugin.php',
			];
			foreach ( $defaultFiles as $f ) {
				$path     = $bootstrapDir . '/' . $f;
				$checks[] = $this->makeCheck(
					label: "Default fallback file '$f' (optional but recommended)",
					pass: is_file( $path ),
					info: is_file( $path )
						? "Found $f in bootstrap/ directory"
						: "Not found: $f",
					severity: 'optional'
				);
			}
		}

		// 5. If config file is present, check that listed script/plugin files actually exist
		// (We won't parse outside references, just check existence.)
		if ( $hasQitE2EConfig && $configIsValid && is_array( $configData ) ) {
			$keysToCheck = [ 'sharedSetup', 'setup', 'teardown', 'sharedTeardown', 'muPlugins' ];
			foreach ( $keysToCheck as $key ) {
				if ( isset( $configData[ $key ] ) && is_array( $configData[ $key ] ) ) {
					foreach ( $configData[ $key ] as $file ) {
						$fullPath = $directory . '/' . $file;
						$checks[] = $this->makeCheck(
							label: "$configFileUsed [$key] references '$file' (MUST exist)",
							pass: is_file( $fullPath ),
							info: is_file( $fullPath )
								? "Found $file"
								: "$file not found in $directory",
							severity: 'must'
						);
					}
				}
			}
		}

		// 6. Directory size < 128 MB (must)
		$dirSize    = $this->getDirectorySize( $directory );
		$under128mb = ( $dirSize < 128 * 1024 * 1024 );
		$checks[]   = $this->makeCheck(
			label: 'Test directory size < 128MB (MUST)',
			pass: $under128mb,
			info: $under128mb
				? 'Directory size: ' . $this->humanFileSize( $dirSize )
				: 'Directory too large: ' . $this->humanFileSize( $dirSize ),
			severity: 'must'
		);

		// -----------------------------
		// Output results (checklist)
		// -----------------------------
		$errorCount   = 0;
		$warningCount = 0;
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
					++$errorCount;
				} else {
					++$warningCount;
				}
			}
		}

		if ( $errorCount > 0 ) {
			$output->writeln( "<error>{$errorCount} required checks failed.</error>" );

			return Command::FAILURE;
		}

		if ( $warningCount > 0 ) {
			$output->writeln( "<comment>All required checks passed, but there are {$warningCount} optional warnings.</comment>" );

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
