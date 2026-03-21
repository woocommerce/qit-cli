<?php
/**
 *  QIT CLI – Scaffold command (fixed UX)
 */

namespace QIT_CLI\Commands\TestPackages;

use QIT_CLI\Commands\QITCommand;
use QIT_CLI\QITInput;
use QIT_CLI\WooExtensionsList;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use function QIT_CLI\normalize_path;

class PackageScaffoldCommand extends QITCommand {
	/**
	 * @var string
	 * @static
	 */
	protected static $defaultName = 'package:scaffold'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	private WooExtensionsList $extensions;

	public function __construct( WooExtensionsList $extensions ) {
		parent::__construct();
		$this->extensions = $extensions;
	}

	protected function configure(): void {
		parent::configure();

		$this
			->addArgument(
				'target_dir',
				InputArgument::REQUIRED,
				'Directory to scaffold the test package (must not already exist)'
			)
			->addOption(
				'package',
				null,
				InputOption::VALUE_REQUIRED,
				'Package identifier in format namespace/package:version (e.g., woocommerce/checkout-tests:1.0.0)'
			)
			->addOption(
				'framework',
				null,
				InputOption::VALUE_REQUIRED,
				'Test framework (only "playwright" is supported)',
				'playwright'
			)
			->addOption(
				'test-type',
				null,
				InputOption::VALUE_REQUIRED,
				'Test type (only "e2e" is supported)',
				'e2e'
			)
			->addOption(
				'package-type',
				null,
				InputOption::VALUE_REQUIRED,
				'Package type: "test" (runs tests) or "utility" (setup only)',
				'test'
			)
			->addOption(
				'only-manifest',
				null,
				InputOption::VALUE_NONE,
				'Create qit-test.json only (skip npm scaffolding)'
			)
			->addOption(
				'with-schema',
				null,
				InputOption::VALUE_NONE,
				'Include $schema field for IDE validation support'
			)
			->setDescription( 'Scaffold a test package (E2E tests) or utility package (setup/configuration)' )
			->setHelp(
				'Scaffold test packages or utility packages under a namespace (extension slug) that you maintain.' . "\n\n" .
				'Package Types:' . "\n" .
				'  - test: Full test package with Playwright E2E tests (includes run phase)' . "\n" .
				'  - utility: Configuration/setup package without tests (no run phase)' . "\n\n" .
				'Package identifier format: namespace/package:version' . "\n" .
				'Example: woocommerce/checkout-tests:1.0.0' . "\n" .
				'  - The namespace must be an extension slug you maintain' . "\n" .
				'  - The package identifies this specific test package' . "\n" .
				'  - The version is optional (defaults to 1.0.0 if not specified)'
			);
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		$io           = new SymfonyStyle( $input, $output );
		$fs           = new Filesystem();
		$target_dir   = normalize_path( $input->getArgument( 'target_dir' ) );
		$package_id   = (string) $input->getOption( 'package' );
		$framework    = strtolower( (string) $input->getOption( 'framework' ) );
		$test_type    = strtolower( (string) $input->getOption( 'test-type' ) );
		$package_type = strtolower( (string) $input->getOption( 'package-type' ) );

		// Initialize namespace, package_name, and version for parsing later
		$namespace    = '';
		$package_name = '';
		$version      = '1.0.0'; // Default version

		/*
		---------------------------------------------------------------------
		 * Explain the workflow
		 * -------------------------------------------------------------------
		 */
		$io->title( 'Scaffold Test Package' );
		$io->writeln( '<comment>This command creates files locally on your machine.</comment>' );
		$io->writeln( '<comment>Nothing is published or uploaded yet.</comment>' );
		$io->writeln( '' );
		$io->writeln( '<info>Workflow:</info>' );
		$io->writeln( '  1. <info>Scaffold</info> → Create local test package files (this command)' );
		$io->writeln( '  2. <info>Develop</info> → Write your tests and customize' );
		$io->writeln( '  3. <info>Publish</info> → Upload to QIT registry (qit package:publish)' );
		$io->writeln( '' );

		/*
		---------------------------------------------------------------------
		 * Pre‑flight validation
		 * -------------------------------------------------------------------
		 */
		if ( $fs->exists( $target_dir ) ) {
			$io->error( sprintf( 'Directory already exists: %s', $target_dir ) );

			return Command::FAILURE;
		}
		if ( $framework !== 'playwright' ) {
			$io->error( 'Only "playwright" is supported for now.' );

			return Command::FAILURE;
		}
		if ( $test_type !== 'e2e' ) {
			$io->error( 'Only "e2e" is supported for now.' );

			return Command::FAILURE;
		}
		if ( ! in_array( $package_type, [ 'test', 'utility' ], true ) ) {
			$io->error( 'Package type must be either "test" or "utility".' );

			return Command::FAILURE;
		}

		$helper = $this->getHelper( 'question' );

		/*
		---------------------------------------------------------------------
		 * Ask for package identifier (namespace/package:version)
		 * -------------------------------------------------------------------
		 */
		if ( $package_id === '' ) {
			$io->writeln( "\n<comment>Package identifier structure:</comment>" );
			$io->writeln( '  <info>namespace/package:version</info>' );
			$io->writeln( '  Example: <info>woocommerce/checkout-tests:1.0.0</info>' );
			$io->writeln( '  - The namespace must be an extension slug you maintain' );
			$io->writeln( '  - The package identifies this specific test package' );
			$io->writeln( '  - The version is optional (defaults to 1.0.0)' );

			$q = new Question( 'Package identifier (namespace/package:version) > ' );
			$q->setValidator( function ( $answer ) {
				return $this->validate_package_identifier( $answer );
			} );
			$package_id = (string) $helper->ask( $input, $output, $q );
		} else {
			$this->validate_package_identifier( $package_id ); // throws on failure
		}

		// Parse the package identifier (namespace/package:version)
		if ( ! str_contains( $package_id, '/' ) ) {
			throw new \RuntimeException( 'Package identifier must be in format "namespace/package:version"' );
		}

		// Use regex to parse namespace/package:version format
		// Pattern: namespace/package:version where :version is optional
		if ( ! preg_match( '/^([^\/]+)\/([^:]+)(:(.+))?$/', $package_id, $matches ) ) {
			throw new \RuntimeException( 'Invalid package identifier format. Expected: namespace/package:version' );
		}

		$namespace    = $matches[1];
		$package_name = $matches[2];
		$version      = $matches[4] ?? '1.0.0'; // Default to 1.0.0 if not provided

		// Validate namespace ownership
		$this->validate_namespace( $namespace );
		$io->writeln( sprintf( '✓ You are a maintainer of "%s"', $namespace ) );
		$io->writeln( sprintf( '✓ Package identifier: <info>%s/%s:%s</info>', $namespace, $package_name, $version ) );

		/*
		---------------------------------------------------------------------
		 * Files & manifest
		 * -------------------------------------------------------------------
		 */
		try {
			$fs->mkdir( [
				$target_dir,
				"$target_dir/bootstrap",
				"$target_dir/results",
			], 0755 );
		} catch ( \Throwable $e ) {
			$io->error( 'Unable to create directory: ' . $e->getMessage() );

			return Command::FAILURE;
		}

		/* bootstrap/global-setup.sh */
		$global_setup_sh = <<<BASH
#!/bin/bash
# ------------------------------------------------------------------
# Global Setup – executed INSIDE the WP container
# ------------------------------------------------------------------
# Put your plugin/extension into a _minimal ready state_ here.
#   – Creates sandbox credentials
#   – Disables onboarding banners
#   – Turns off tracking, etc.
# This runs **once** per test run (even if your package is only in
# `global_setup`) and should finish fast.

set -euo pipefail

echo "[globalSetup] Starting global configuration..."
# Example:
# wp option update my_plugin_onboarding_complete yes
echo "[globalSetup] Done."
BASH;
		file_put_contents( "$target_dir/bootstrap/global-setup.sh", $global_setup_sh );
		chmod( "$target_dir/bootstrap/global-setup.sh", 0755 );

		/* bootstrap/setup.sh (isolated setup) */
		$setup_sh = <<<BASH
#!/bin/bash
# ------------------------------------------------------------------
# Isolated Setup – executed INSIDE the WP container
# ------------------------------------------------------------------
# Runs before the *run* phase of THIS package only.
# Safe place to create test data that must not leak to other packages.

set -euo pipefail

echo "[setup] Creating sample data ..."
# Example:
# wp wc product create --name="Test Product" --type=simple --price=9.99
echo "[setup] Done."
BASH;
		file_put_contents( "$target_dir/bootstrap/setup.sh", $setup_sh );
		chmod( "$target_dir/bootstrap/setup.sh", 0755 );

		/* bootstrap/global-teardown.sh */
		$global_teardown_sh = <<<BASH
#!/bin/bash
# ------------------------------------------------------------------
# Global Teardown – executed INSIDE the WP container
# ------------------------------------------------------------------
# Runs once at the very end.  Clean up anything created in globalSetup.

set -euo pipefail

echo "[globalTeardown] Cleaning up ..."
# Example:
# wp option delete my_plugin_sandbox_token
echo "[globalTeardown] Done."
BASH;
		file_put_contents( "$target_dir/bootstrap/global-teardown.sh", $global_teardown_sh );
		chmod( "$target_dir/bootstrap/global-teardown.sh", 0755 );

		/* qit-test.json – wired to the three scripts above */
		$manifest = [];

		// Optionally include $schema for IDE validation
		if ( $input->getOption( 'with-schema' ) ) {
			$manifest['$schema'] = 'https://raw.githubusercontent.com/woocommerce/qit-cli/trunk/src/src/PreCommand/Schemas/test-package-manifest-schema.json';
		}

		// Build base manifest structure
		$manifest = array_merge( $manifest, [
			'package'      => $namespace . '/' . $package_name, // Combined namespace/package identifier
			'package_type' => $package_type, // Explicitly set package type
			'requires'     => [
				'network' => false, // Explicitly show this field for clarity
			],
		] );

		if ( $package_type === 'test' ) {
			// Add test_type for test packages (not needed for utility packages)
			$manifest['test_type'] = $test_type;

			// Test package: includes run phase and results
			$manifest['test'] = [
				'phases'  => [
					'globalSetup'    => [ './bootstrap/global-setup.sh' ],
					'setup'          => [ './bootstrap/setup.sh' ],
					'run'            => [ 'npx playwright test' ],
					'teardown'       => [],
					'globalTeardown' => [ './bootstrap/global-teardown.sh' ],
				],
				'results' => [
					'ctrf-json'  => './results/ctrf.json',
					'allure-dir' => './results/allure',
					'blob-dir'   => './results/blob',
				],
			];
		} else {
			// Utility package: only setup/teardown phases, no run or results
			$manifest['test'] = [
				'phases' => [
					'globalSetup'    => [ './bootstrap/global-setup.sh' ],
					'setup'          => [ './bootstrap/setup.sh' ],
					'teardown'       => [],
					'globalTeardown' => [ './bootstrap/global-teardown.sh' ],
				],
			];
		}
		file_put_contents(
			"$target_dir/qit-test.json",
			json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . PHP_EOL
		);

		/* Validate manifest (will throw if schema mismatch) */
		try {
			( new \QIT_CLI\PreCommand\Configuration\Parser\TestPackageManifestParser() )
				->parse( "$target_dir/qit-test.json" );
		} catch ( \Throwable $e ) {
			$fs->remove( $target_dir );
			$io->error( 'Manifest validation failed: ' . $e->getMessage() );

			return Command::FAILURE;
		}

		if ( $input->getOption( 'only-manifest' ) ) {
			$io->success( 'qit-test.json created at ' . $target_dir . '/qit-test.json' );

			return Command::SUCCESS;
		}

		// Skip npm scaffolding for utility packages (they don't need Playwright)
		if ( $package_type === 'utility' ) {
			$io->success( 'Utility package scaffolded successfully!' );
			$io->writeln( sprintf( "\n<comment>🗒  Edit bootstrap/*.sh to configure setup/teardown.</comment>" ) );
			$io->writeln( sprintf( "\nNext → qit package:publish %s", $target_dir ) );

			return Command::SUCCESS;
		}

		/*
		---------------------------------------------------------------------
		 * Extra Playwright scaffolding (test packages only)
		 * -------------------------------------------------------------------
		 */
		try {
			$this->ensure_npm();
			$this->write_package_json( $target_dir );
			$this->write_playwright_config( $target_dir );
			$this->write_sample_test( $target_dir );
			$this->install_dev_dependencies( $target_dir, $output );
		} catch ( \Throwable $e ) {
			$fs->remove( $target_dir );
			$io->error( $e->getMessage() );

			return Command::FAILURE;
		}

		/*
		---------------------------------------------------------------------
		 * Done
		 * -------------------------------------------------------------------
		 */
		$io->writeln( 'Scaffolding test package…' );
		$io->writeln( sprintf( "\n🟩 Package scaffolded (%s • %s)", $test_type, $framework ) );
		$io->writeln(
			"\n<comment>🗒  Edit bootstrap/*.sh to configure global or isolated setup.</comment>"
		);
		$io->writeln( sprintf( "\nNext → qit package:publish %s", $target_dir ) );

		return Command::SUCCESS;
	}

	/*
	-------------------------------------------------------------------------
	 * Helpers
	 * -----------------------------------------------------------------------
	 */

	/**
	 * Validate package identifier (namespace/package:version).
	 *
	 * @param string $identifier The package identifier to validate.
	 *
	 * @return string The validated identifier.
	 * @throws \RuntimeException If validation fails.
	 */
	private function validate_package_identifier( string $identifier ): string {
		if ( ! str_contains( $identifier, '/' ) ) {
			throw new \RuntimeException( 'Package identifier must be in format "namespace/package:version"' );
		}

		// Parse namespace/package:version format
		if ( ! preg_match( '/^([^\/]+)\/([^:]+)(:(.+))?$/', $identifier, $matches ) ) {
			throw new \RuntimeException( 'Invalid package identifier format. Expected: namespace/package:version' );
		}

		$namespace = $matches[1];
		$package   = $matches[2];
		$version   = $matches[4] ?? '1.0.0';

		// Validate namespace and package parts (but not version, which can contain dots)
		$this->validate_slug( $namespace, 'Namespace' );
		$this->validate_slug( $package, 'Package name' );

		// Validate version format if provided (must be semver-like: x.y.z)
		if ( isset( $matches[4] ) && ! preg_match( '/^\d+\.\d+\.\d+/', $version ) ) {
			throw new \RuntimeException( "Version must be in semver format (e.g., 1.0.0), got: $version" );
		}

		// Check namespace ownership
		if ( ! $this->extensions->user_maintains( $namespace ) ) {
			throw new \RuntimeException( "You are not a maintainer of \"{$namespace}\"." );
		}

		return $identifier;
	}

	/**
	 * Validate namespace (extension slug).
	 *
	 * @param string $slug The slug to validate.
	 *
	 * @return string The validated slug.
	 * @throws \RuntimeException If validation fails.
	 */
	private function validate_namespace( string $slug ): string {
		$this->validate_slug( $slug, 'Namespace' );

		if ( ! $this->extensions->user_maintains( $slug ) ) {
			throw new \RuntimeException( "You are not a maintainer of \"{$slug}\"." );
		}

		return $slug;
	}

	private function validate_slug( string $slug, string $label = 'Slug' ): string {
		if ( ! preg_match( '/^[a-zA-Z0-9_.-]+$/', $slug ) ) {
			throw new \RuntimeException( "{$label} may contain only letters, numbers, underscores, dots and hyphens." );
		}

		return $slug;
	}

	private function ensure_npm(): void {
		$proc = \Symfony\Component\Process\Process::fromShellCommandline( 'command -v npm' );
		$proc->run();
		if ( ! $proc->isSuccessful() ) {
			throw new \RuntimeException( 'npm must be installed and in $PATH.' );
		}
	}

	private function write_package_json( string $dir ): void {
		$pkg = [
			'private'         => true,
			'type'            => 'module',
			'devDependencies' => [],
			'scripts'         => [ 'test:e2e' => 'playwright test' ],
		];
		file_put_contents(
			"$dir/package.json",
			json_encode( $pkg, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . PHP_EOL
		);
	}

	private function write_playwright_config( string $dir ): void {
		$config = <<<'JS'
import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './tests',
  forbidOnly: !!process.env.CI,
  retries: 0,
  fullyParallel: false,
  workers: 1,
  reporter: [
    ['list'],
    ['html', { open: 'never' }],
    ['playwright-ctrf-json-reporter', {
      outputDir: './results',
      outputFile: 'ctrf.json',
    }],
    ['allure-playwright', {
      resultsDir: './results/allure',
    }],
    ['blob', {
      outputDir: './results/blob',
    }],
  ],
  use: {
    baseURL: process.env.QIT_SITE_URL || 'http://localhost:8080',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
    trace: 'retain-on-failure',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
});

JS;
		file_put_contents( "$dir/playwright.config.js", $config );
	}

	private function write_sample_test( string $dir ): void {
		( new Filesystem() )->mkdir( "$dir/tests", 0755 );
		$spec = <<<'JS'
import { test, expect } from '@playwright/test';

test('site is reachable and has a body', async ({ page }) => {
  const response = await page.goto('/');
  expect(response?.status()).toBe(200);

  await expect(page.locator('body')).toBeVisible();
});
JS;
		file_put_contents( "$dir/tests/example.spec.js", $spec );
	}

	private function install_dev_dependencies( string $dir, OutputInterface $out ): void {
		$deps = [
			'@playwright/test',
			'playwright-ctrf-json-reporter',
			'allure-playwright',
		];
		foreach ( $deps as $pkg ) {
			$p = new \Symfony\Component\Process\Process(
				[ 'npm', 'install', '--save-dev', $pkg ],
				$dir,
				[
					'CI'                               => '1',
					'PLAYWRIGHT_SKIP_BROWSER_DOWNLOAD' => '1',
				]
			);
			$p->setTimeout( null );
			$p->run( function ( $type, $buffer ) use ( $out ) {
				$out->write( $buffer );
			} );
			if ( ! $p->isSuccessful() ) {
				throw new \Symfony\Component\Process\Exception\ProcessFailedException( $p );
			}
		}
	}
}
