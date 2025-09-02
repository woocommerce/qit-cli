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
				'Package identifier in format namespace/name (e.g., woocommerce/checkout-tests)'
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
			->setDescription( 'Scaffold a Playwright E2E test package' )
			->setHelp(
				'You can scaffold test packages only under a namespace (extension slug) that you maintain.' . "\n\n" .
				'Package identifier format: namespace/name' . "\n" .
				'Example: woocommerce/checkout-tests' . "\n" .
				'  - The namespace must be an extension slug you maintain' . "\n" .
				'  - The name identifies this specific test package' . "\n" .
				'  - Version is specified when publishing or using the package'
			);
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		$io         = new SymfonyStyle( $input, $output );
		$fs         = new Filesystem();
		$target_dir = normalize_path( $input->getArgument( 'target_dir' ) );
		$package_id = (string) $input->getOption( 'package' );
		$framework  = strtolower( (string) $input->getOption( 'framework' ) );
		$test_type  = strtolower( (string) $input->getOption( 'test-type' ) );

		// Initialize namespace and package_name for parsing later
		$namespace    = '';
		$package_name = '';

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

		$helper = $this->getHelper( 'question' );

		/*
		---------------------------------------------------------------------
		 * Ask for package identifier (namespace/name)
		 * -------------------------------------------------------------------
		 */
		if ( $package_id === '' ) {
			$io->writeln( "\n<comment>Package identifier structure:</comment>" );
			$io->writeln( '  <info>namespace/name</info>' );
			$io->writeln( '  Example: <info>woocommerce/checkout-tests</info>' );
			$io->writeln( '  - The namespace must be an extension slug you maintain' );
			$io->writeln( '  - The name identifies this specific test package' );

			$q = new Question( 'Package identifier (namespace/name) > ' );
			$q->setValidator( function ( $answer ) {
				return $this->validate_package_identifier( $answer );
			} );
			$package_id = (string) $helper->ask( $input, $output, $q );
		} else {
			$this->validate_package_identifier( $package_id ); // throws on failure
		}

		// Parse the package identifier
		if ( ! str_contains( $package_id, '/' ) ) {
			throw new \RuntimeException( 'Package identifier must be in format "namespace/name"' );
		}
		[ $namespace, $package_name ] = explode( '/', $package_id, 2 );

		// Validate namespace ownership
		$this->validate_namespace( $namespace );
		$io->writeln( sprintf( '✓ You are a maintainer of "%s"', $namespace ) );
		$io->writeln( sprintf( '✓ Package identifier: <info>%s</info>', $package_id ) );

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
			$manifest['$schema'] = 'https://qit.woo.com/json-schema/test-package';
		}

		$manifest = array_merge( $manifest, [
			'package'          => $namespace . '/' . $package_name,
			'requires_network' => false, // Explicitly show this field for clarity
			'test'             => [
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
			],
		] );
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

		/*
		---------------------------------------------------------------------
		 * Extra Playwright scaffolding
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
	 * Validate package identifier (namespace/name).
	 *
	 * @param string $identifier The package identifier to validate.
	 *
	 * @return string The validated identifier.
	 * @throws \RuntimeException If validation fails.
	 */
	private function validate_package_identifier( string $identifier ): string {
		if ( ! str_contains( $identifier, '/' ) ) {
			throw new \RuntimeException( 'Package identifier must be in format "namespace/name"' );
		}

		[ $namespace, $name ] = explode( '/', $identifier, 2 );

		// Validate both parts
		$this->validate_slug( $namespace, 'Namespace' );
		$this->validate_slug( $name, 'Package name' );

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
    trace: 'on-first-retry',
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
