<?php

namespace QIT_CLI\Commands\TestPackages;

use QIT_CLI\Commands\QITCommand;
use QIT_CLI\Config;
use QIT_CLI\WooExtensionsList;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use function QIT_CLI\normalize_path;

class PackageScaffoldCommand extends QITCommand {
	protected static $defaultName = 'package:scaffold'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	/** @var WooExtensionsList */
	private $woo_extensions_list;

	public function __construct( WooExtensionsList $woo_extensions_list ) {
		parent::__construct();
		$this->woo_extensions_list = $woo_extensions_list;
	}

	protected function configure(): void {
		parent::configure();
		$this
			->addArgument( 'target_dir', InputArgument::REQUIRED, 'Directory to scaffold the E2E test package (must not exist).' )
			->addOption( 'vendor', null, InputOption::VALUE_REQUIRED, 'Vendor slug for the test package (e.g. acme).' )
			->addOption( 'package', null, InputOption::VALUE_REQUIRED, 'Package slug for the test package (e.g. my-plugin-tests).' )
			->addOption( 'framework', null, InputOption::VALUE_REQUIRED, 'Framework to use (currently only "playwright" is accepted).', 'playwright' )
			->addOption( 'test-type', null, InputOption::VALUE_REQUIRED, 'Test type to use (currently only "e2e" is accepted).', 'e2e' )
			->addOption( 'only-manifest', null, InputOption::VALUE_NONE, 'Create manifest.json only and exit.' )
			->setDescription( 'Scaffold an E2E test package with --framework and --test-type options (currently only Playwright E2E is supported).' )
			->setHelp( 'Note: if you authenticate with an e‑mail address you must publish under an extension slug you maintain; personal namespaces are reserved for partner aliases.' );
	}

	protected function doExecute( InputInterface $input, OutputInterface $output ): int {
		$io         = new SymfonyStyle( $input, $output );
		$target_dir = $input->getArgument( 'target_dir' );
		$vendor     = $input->getOption( 'vendor' );
		$package    = $input->getOption( 'package' );
		$framework  = $input->getOption( 'framework' );
		$test_type  = $input->getOption( 'test-type' );

		// Interactive prompts for required values when missing.
		$question_helper = $this->getHelper( 'question' );

		$target_dir = normalize_path( $target_dir );
		if ( file_exists( $target_dir ) ) {
			$io->error( sprintf( 'Directory already exists: %s', $target_dir ) );

			return Command::FAILURE;
		}

		// Get current partner name
		$current_environment = Config::get_current_manager_backend();
		$partner_name_parts  = explode( '-', $current_environment );
		$current_partner     = end( $partner_name_parts );

		// Single prompt for package reference in vendor/package format
		if ( empty( $vendor ) || empty( $package ) ) {
			$default_reference = $current_partner . '/tests';

			$package_ref_question = new Question(
				sprintf(
					"Package reference (vendor/package)\n  • Use your partner alias '%s'  →  %s/<package>\n  • Or an extension slug you maintain   →  woocommerce-payments/<package>\n[default: %s]: ",
					$current_partner,
					$current_partner,
					$default_reference
				)
			);

			$package_ref_question->setValidator( function ( $answer ) use ( $default_reference, $current_partner ) {
				// Use default if empty
				if ( empty( $answer ) ) {
					$answer = $default_reference;
				}

				// Validate format
				if ( ! preg_match( '/^[a-zA-Z0-9_.-]+\/[a-zA-Z0-9_.-]+$/', $answer ) ) {
					throw new \RuntimeException( 'Package reference must be in vendor/package format and contain only letters, numbers, underscores, dots, and hyphens.' );
				}

				list( $vendor_part, $package_part ) = explode( '/', $answer, 2 );

				// Validate vendor part - must be partner alias or owned extension
				if ( $vendor_part !== $current_partner ) {
					// Check if it's a WooCommerce extension they maintain
					$extensions      = $this->woo_extensions_list->get_woo_extension_list();
					$extension_found = false;
					foreach ( $extensions as $ext ) {
						if ( $ext['slug'] === $vendor_part ) {
							$extension_found = true;
							break;
						}
					}

					if ( ! $extension_found ) {
						throw new \RuntimeException( sprintf( "You are not a maintainer of '%s'.", $vendor_part ) );
					}
				}

				return $answer;
			} );

			$package_reference = $question_helper->ask( $input, $output, $package_ref_question );

			// Use default if still empty
			if ( empty( $package_reference ) ) {
				$package_reference = $default_reference;
			}

			// Split the reference
			list( $vendor, $package ) = explode( '/', $package_reference, 2 );

			// Show success message
			if ( $vendor === $current_partner ) {
				$io->writeln( sprintf( '✔ Using: %s', $package_reference ) );
			} else {
				$io->writeln( sprintf( '✓ Verified – you are a maintainer of \'%s\'', $vendor ) );
			}
		} else {
			// Validate vendor and package from options
			if ( ! preg_match( '/^[a-zA-Z0-9_.-]+$/', $vendor ) ) {
				$io->error( 'Vendor must contain only letters, numbers, underscores, dots, and hyphens.' );
				return Command::FAILURE;
			}
			if ( ! preg_match( '/^[a-zA-Z0-9_.-]+$/', $package ) ) {
				$io->error( 'Package must contain only letters, numbers, underscores, dots, and hyphens.' );
				return Command::FAILURE;
			}

			// Validate vendor ownership - must be partner alias or owned extension
			if ( $vendor !== $current_partner ) {
				// Check if it's a WooCommerce extension they maintain
				$extensions      = $this->woo_extensions_list->get_woo_extension_list();
				$extension_found = false;
				foreach ( $extensions as $ext ) {
					if ( $ext['slug'] === $vendor ) {
						$extension_found = true;
						break;
					}
				}

				if ( ! $extension_found ) {
					$io->error( sprintf( "You are not a maintainer of '%s'.", $vendor ) );
					return Command::FAILURE;
				}
			}
		}

		// Validate framework option
		if ( strtolower( $framework ) !== 'playwright' ) {
			$io->error( 'Error: only \'playwright\' is supported for --framework.' );
			return Command::FAILURE;
		}
		$framework = strtolower( $framework );

		// Validate test-type option
		if ( strtolower( $test_type ) !== 'e2e' ) {
			$io->error( 'Error: only \'e2e\' is supported for --test-type.' );
			return Command::FAILURE;
		}
		$test_type = strtolower( $test_type );

		try {
			( new Filesystem() )->mkdir( $target_dir, 0755 );
			// Create bootstrap subdirectory
			( new Filesystem() )->mkdir( $target_dir . DIRECTORY_SEPARATOR . 'bootstrap', 0755 );
		} catch ( \Exception $e ) {
			$io->error( 'Unable to create directory: ' . $e->getMessage() );

			return Command::FAILURE;
		}

		// Create bootstrap/setup.sh with executable permissions
		$setup_script_path    = $target_dir . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'setup.sh';
		$setup_script_content = <<<'BASH'
#!/bin/bash
# Example isolated setup script.
# 🛈 The plugin under test is already active.
# Add commands below; they run inside the Docker container used by QIT.
# Examples:
# wp theme install storefront --activate
# wp option update blogname "My e2e test site"

echo "Setup complete"
BASH;

		file_put_contents( $setup_script_path, $setup_script_content );
		chmod( $setup_script_path, 0755 );

		$manifest_path = $target_dir . DIRECTORY_SEPARATOR . 'manifest.json';
		$manifest      = [
'$schema'   => 'https://qit.woo.com/json-schema/test-package',
'vendor'    => $vendor,
'package'   => $package,
'test_type' => $test_type,
			'test'      => [
				'phases'  => [
					'beforeAllPlugins' => [],
					'setup'            => [ './bootstrap/setup.sh' ],
					'run'              => [ 'npx playwright test' ],
					'teardown'         => [],
					'afterAllPlugins'  => [],
				],
				'results' => [
					'ctrf-json'  => './results/ctrf.json',
					'allure-dir' => './results/allure',
				],
			],
		];

		file_put_contents( $manifest_path, json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );

		// Validate manifest against schema.
		try {
			$parser = new \QIT_CLI\PreCommand\Configuration\Parser\TestPackageManifestParser();
			$parser->parse( $manifest_path );
		} catch ( \Throwable $e ) {
			( new Filesystem() )->remove( $target_dir );
			$io->error( 'Manifest validation failed: ' . $e->getMessage() );

			return Command::FAILURE;
		}

		if ( $input->getOption( 'only-manifest' ) ) {
			$io->success( 'manifest.json created at ' . $manifest_path );

			return Command::SUCCESS;
		}

		try {
			$this->ensure_npm_available();
			$this->write_package_json( $target_dir );
			$this->write_playwright_config( $target_dir );
			$this->write_sample_test( $target_dir );
			$this->install_dev_dependencies( $target_dir, $output );
		} catch ( \Throwable $e ) {
			// Cleanup on failure.
			( new Filesystem() )->remove( $target_dir );
			$io->error( $e->getMessage() );

			return Command::FAILURE;
		}

		$io->writeln( 'Scaffolding test package…' );
		$io->writeln( sprintf( "\n🟩 Package scaffolded (%s • %s)", $test_type, $framework ) );
		$io->writeln( sprintf( "\nNext → qit package:publish %s", $target_dir ) );

		return Command::SUCCESS;
	}

	private function ensure_npm_available(): void {
		$process = \Symfony\Component\Process\Process::fromShellCommandline( 'command -v npm' );
		$process->run();
		if ( ! $process->isSuccessful() ) {
			throw new \RuntimeException( 'npm must be installed and in PATH to scaffold Playwright projects.' );
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
			$dir . '/package.json',
			json_encode( $pkg, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . PHP_EOL
		);
	}

	private function write_playwright_config( string $dir ): void {
		$cfg = <<<'JS'
import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './tests',
  forbidOnly: !!process.env.CI,
  retries: 0,
  fullyParallel: false,
  workers: 1,
  reporter: [
    'list',
    ['html', { open: 'never' }],
    ['playwright-ctrf-json-reporter', { outputFile: './results/ctrf.json' }],
    ['allure-playwright',             { outputFolder: './results/allure' }]
  ],
  use: {
    baseURL: process.env.QIT_SITE_URL || 'http://localhost:8080',
    trace: 'on-first-retry'
  },
  projects: [
    { name: 'chromium', use: { ...devices['Desktop Chrome'] } }
  ]
});
JS;
		file_put_contents( $dir . '/playwright.config.js', $cfg );
	}


	private function write_sample_test( string $dir ): void {
		( new \Symfony\Component\Filesystem\Filesystem() )
			->mkdir( $dir . '/tests', 0755 );

		$spec = <<<'JS'
import { test, expect } from '@playwright/test';

test('site is reachable and has a body', async ({ page }) => {
  const response = await page.goto('/');
  expect(response?.status()).toBe(200);

  const body = await page.locator('body');
  await expect(body).toBeVisible();
});
JS;
		file_put_contents( $dir . '/tests/example.spec.js', $spec );
	}


	private function install_dev_dependencies( string $dir, OutputInterface $out ): void {
		$packages = [
			'@playwright/test',
			'playwright-ctrf-json-reporter',
			'allure-playwright',
		];

		foreach ( $packages as $package ) {
			$proc = new \Symfony\Component\Process\Process(
				[ 'npm', 'install', '--save-dev', $package ], $dir,
				[
					'CI'                               => '1',
					'PLAYWRIGHT_SKIP_BROWSER_DOWNLOAD' => '1',
				]
			);
			$proc->setTimeout( null );
			$proc->run( fn( $t, $b ) => $out->write( $b ) );
			if ( ! $proc->isSuccessful() ) {
				throw new \Symfony\Component\Process\Exception\ProcessFailedException( $proc );
			}
		}
	}
}
