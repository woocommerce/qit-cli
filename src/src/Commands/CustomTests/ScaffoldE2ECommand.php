<?php

namespace QIT_CLI\Commands\CustomTests;

use QIT_CLI\Commands\QITCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use function QIT_CLI\normalize_path;

class ScaffoldE2ECommand extends QITCommand {
	protected static $defaultName = 'scaffold:e2e'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected function configure(): void {
		parent::configure();
		$this
			->addArgument( 'target_dir', InputArgument::REQUIRED, 'Directory to scaffold the E2E test package (must not exist).' )
			->addOption( 'vendor', null, InputOption::VALUE_REQUIRED, 'Vendor slug for the test package (e.g. acme).' )
			->addOption( 'package', null, InputOption::VALUE_REQUIRED, 'Package slug for the test package (e.g. my-plugin-tests).' )
			->addOption( 'framework', null, InputOption::VALUE_REQUIRED, 'Framework to use (only "playwright" is supported).' )
			->addOption( 'only-manifest', null, InputOption::VALUE_NONE, 'Create manifest.json only and exit.' )
			->setDescription( 'Scaffold an E2E test package (manifest-first approach).' );
	}

	protected function doExecute( InputInterface $input, OutputInterface $output ): int {
		$io       = new SymfonyStyle( $input, $output );
		$target_dir = $input->getArgument( 'target_dir' );
		$vendor   = $input->getOption( 'vendor' );
		$package  = $input->getOption( 'package' );
		$framework = $input->getOption( 'framework' );

		// Interactive prompts for required values when missing.
		$question_helper = $this->getHelper( 'question' );
		
		// Prompt for vendor if missing
		if ( empty( $vendor ) ) {
			$vendor_question = new Question( 'Vendor (e.g. acme): ' );
			$vendor_question->setValidator( function ( $answer ) {
				if ( empty( $answer ) || ! preg_match( '/^[a-zA-Z0-9_.-]+$/', $answer ) ) {
					throw new \RuntimeException( 'Vendor must be non-empty and contain only letters, numbers, underscores, dots, and hyphens.' );
				}
				return $answer;
			} );
			$vendor = $question_helper->ask( $input, $output, $vendor_question );
		} else {
			// Validate vendor from option
			if ( ! preg_match( '/^[a-zA-Z0-9_.-]+$/', $vendor ) ) {
				$io->error( 'Vendor must contain only letters, numbers, underscores, dots, and hyphens.' );
				return Command::FAILURE;
			}
		}

		// Prompt for package if missing
		if ( empty( $package ) ) {
			$package_question = new Question( 'Package (e.g. my-plugin-tests): ' );
			$package_question->setValidator( function ( $answer ) {
				if ( empty( $answer ) || ! preg_match( '/^[a-zA-Z0-9_.-]+$/', $answer ) ) {
					throw new \RuntimeException( 'Package must be non-empty and contain only letters, numbers, underscores, dots, and hyphens.' );
				}
				return $answer;
			} );
			$package = $question_helper->ask( $input, $output, $package_question );
		} else {
			// Validate package from option
			if ( ! preg_match( '/^[a-zA-Z0-9_.-]+$/', $package ) ) {
				$io->error( 'Package must contain only letters, numbers, underscores, dots, and hyphens.' );
				return Command::FAILURE;
			}
		}

		// Prompt for framework if missing
		if ( empty( $framework ) ) {
			$framework_question = new Question( 'Framework (default playwright) [playwright]: ', 'playwright' );
			$framework_question->setValidator( function ( $answer ) {
				$answer = strtolower( trim( $answer ) );
				if ( $answer !== 'playwright' ) {
					throw new \RuntimeException( 'Only "playwright" framework is supported.' );
				}
				return $answer;
			} );
			$framework = $question_helper->ask( $input, $output, $framework_question );
		} else {
			// Validate framework from option
			if ( strtolower( $framework ) !== 'playwright' ) {
				$io->error( 'Only "playwright" framework is supported.' );
				return Command::FAILURE;
			}
			$framework = strtolower( $framework );
		}

		$target_dir = normalize_path( $target_dir );
		if ( file_exists( $target_dir ) ) {
			$io->error( sprintf( 'Directory already exists: %s', $target_dir ) );
			return Command::FAILURE;
		}

		try {
			(new Filesystem())->mkdir( $target_dir, 0755 );
			// Create bootstrap subdirectory
			(new Filesystem())->mkdir( $target_dir . DIRECTORY_SEPARATOR . 'bootstrap', 0755 );
		} catch ( \Exception $e ) {
			$io->error( 'Unable to create directory: ' . $e->getMessage() );
			return Command::FAILURE;
		}

		// Create bootstrap/setup.sh with executable permissions
		$setup_script_path = $target_dir . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'setup.sh';
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
		$manifest = [
			'$schema'  => 'https://qit.woo.com/json-schema/test-package',
			'vendor'   => $vendor,
			'package'  => $package,
			'test_type'=> 'e2e',
			'test' => [
				'phases'  => [
					'beforeAllPlugins' => [],
					'setup'            => ['./bootstrap/setup.sh'],
					'run'              => [ 'npx playwright test' ],
					'teardown'         => [],
					'afterAllPlugins'  => [],
				],
				'results' => [
					'ctrf-json' => './results/ctrf.json',
					'allure-dir'=> './results/allure'
				],
			],
		];

		file_put_contents( $manifest_path, json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );

		// Validate manifest against schema.
		try {
			$parser = new \QIT_CLI\PreCommand\Configuration\Parser\TestPackageManifestParser();
			$parser->parse( $manifest_path );
		} catch ( \Throwable $e ) {
			(new Filesystem())->remove( $target_dir );
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
			(new Filesystem())->remove( $target_dir );
			$io->error( $e->getMessage() );
			return Command::FAILURE;
		}

		$io->success( 'E2E scaffold generated in ' . $target_dir );
		$io->writeln( 'Next steps:' );
		$io->writeln( '  • npm install (if not already done by Playwright wizard)' );
		$io->writeln( '  • Edit bootstrap/setup.sh to customize your test environment setup' );
		$io->writeln( sprintf( '  • qit package:publish %s/%s:<tag> %s', $vendor, $package, $target_dir ) );

		return Command::SUCCESS;
	}

	private function ensure_npm_available(): void {
		$process = \Symfony\Component\Process\Process::fromShellCommandline( 'command -v npm' );
		$process->run();
		if ( ! $process->isSuccessful() ) {
			throw new \RuntimeException( 'npm must be installed and in PATH to scaffold Playwright projects.' );
		}
	}

	private function write_package_json(string $dir): void {
		$pkg = [
			'private'         => true,
			'type'            => 'module',
			'devDependencies' => [],
			'scripts' => [ 'test:e2e' => 'playwright test' ]
		];
		file_put_contents(
			$dir . '/package.json',
			json_encode($pkg, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL
		);
	}

	private function write_playwright_config(string $dir): void {
		$cfg = <<<'TS'
import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './tests',
  fullyParallel: true,
  reporter: [
    'list',
    ['playwright-ctrf-json-reporter', { outputFile: './results/ctrf.json' }],
    ['allure-playwright',             { outputFolder: './results/allure' }]
  ],
  use: { trace: 'on-first-retry' },
  projects: [
    { name: 'chromium', use: { ...devices['Desktop Chrome'] } },
    { name: 'firefox',  use: { ...devices['Desktop Firefox'] } },
    { name: 'webkit',   use: { ...devices['Desktop Safari'] } }
  ]
});
TS;
		file_put_contents($dir . '/playwright.config.ts', $cfg);
	}

	private function write_sample_test(string $dir): void {
		(new \Symfony\Component\Filesystem\Filesystem())
			->mkdir($dir . '/tests', 0755);

		$spec = <<<'JS'
import { test, expect } from '@playwright/test';

test('homepage title', async ({ page }) => {
  await page.goto('https://example.com');
  await expect(page).toHaveTitle(/Example Domain/);
});
JS;
		file_put_contents($dir . '/tests/example.spec.js', $spec);
	}

	private function install_dev_dependencies(string $dir, OutputInterface $out): void {
		$packages = [
			'@playwright/test',
			'playwright-ctrf-json-reporter',
			'allure-playwright'
		];

		foreach ($packages as $package) {
			$proc = new \Symfony\Component\Process\Process(
				['npm', 'install', '--save-dev', $package], $dir,
				['CI'=>'1','PLAYWRIGHT_SKIP_BROWSER_DOWNLOAD'=>'1']
			);
			$proc->setTimeout(null);
			$proc->run(fn($t,$b)=>$out->write($b));
			if (!$proc->isSuccessful()) {
				throw new \Symfony\Component\Process\Exception\ProcessFailedException($proc);
			}
		}
	}
}
