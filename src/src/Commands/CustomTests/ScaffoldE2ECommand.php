<?php

namespace QIT_CLI\Commands\CustomTests;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use function QIT_CLI\normalize_path;

class ScaffoldE2ECommand extends Command {
	protected static $defaultName = 'scaffold:e2e'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected function configure() {
		$this
			->addArgument( 'path', InputArgument::REQUIRED, 'The path to scaffold an example E2E test.' )
			->setDescription( 'Scaffold an example E2E test.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$path = $input->getArgument( 'path' );

		$path_to_generate = normalize_path( $path );

		if ( file_exists( $path_to_generate ) ) {
			if ( ! $this->getHelper( 'question' )->ask( $input, $output, new ConfirmationQuestion( "Directory already exists. Scaffold E2E tests in \"$path_to_generate\" anyway? <question>(y/n)</question> ", false ) ) ) {
				return Command::SUCCESS;
			}
		}

		if ( file_exists( $path_to_generate ) ) {
			$output->writeln( '<error>Directory already exists: ' . $path_to_generate . '</error>' );

			return Command::FAILURE;
		}

		if ( ! mkdir( $path_to_generate, 0755, true ) ) {
			$output->writeln( '<error>Could not create directory: ' . $path_to_generate . '</error>' );

			return Command::FAILURE;
		}

		// Create basic bootstrap.
		if ( ! mkdir( $path_to_generate . '/bootstrap', 0755, true ) ) {
			$output->writeln( '<error>Could not create directory: ' . $path_to_generate . '/bootstrap</error>' );

			return Command::FAILURE;
		}

		// bootstrap.sh.
		if ( ! file_put_contents( $path_to_generate . '/bootstrap/bootstrap.sh', $this->generate_bootstrap_shell_example() ) ) {
			$output->writeln( '<error>Could not create file: ' . $path_to_generate . '/bootstrap/bootstrap.sh</error>' );

			return Command::FAILURE;
		}

		// bootstrap.php (read from text file to avoid our prefixer).
		if ( ! file_put_contents( $path_to_generate . '/bootstrap/bootstrap.php', file_get_contents( __DIR__ . '/scaffolding/bootstrap-php.txt' ) ) ) {
			$output->writeln( '<error>Could not create file: ' . $path_to_generate . '/bootstrap/bootstrap.php</error>' );

			return Command::FAILURE;
		}

		// mu-plugin.php (read from text file to avoid our prefixer).
		if ( ! file_put_contents( $path_to_generate . '/bootstrap/mu-plugin.php', file_get_contents( __DIR__ . '/scaffolding/mu-plugin.txt' ) ) ) {
			$output->writeln( '<error>Could not create file: ' . $path_to_generate . '/bootstrap/mu-plugin.php</error>' );

			return Command::FAILURE;
		}

		// example.spec.js.
		if ( ! file_put_contents( $path_to_generate . '/example.spec.js', $this->example_spec_js() ) ) {
			$output->writeln( '<error>Could not create file: ' . $path_to_generate . '/example.spec.js</error>' );

			return Command::FAILURE;
		}

		$output->writeln( '<info>Example E2E test generated in: ' . $path_to_generate . '</info>' );
		$output->writeln( "You can now run your first test with <comment>qit run:e2e <your_slug> \"$path_to_generate\" --ui</comment>" );
		$output->writeln( 'You can start writing your tests with codegen: <comment>qit run:e2e --codegen</comment>' );
		$output->writeln( 'And when you are ready, you can publish your tests with <comment>qit test-tags:upload <your_slug> <path_to_test></comment>' );
		$output->writeln( 'Read more about it on our documentation: https://qit.woo.com/docs/custom-tests/generating-tests' );

		return Command::SUCCESS;
	}

	protected function generate_bootstrap_shell_example(): string {
		return <<<'SHELL'
#!/bin/bash

# Bootstrap Shell Script (Optional)

# Purpose: This script is executed before test runs to set up the testing environment.
#
# Usage:
# - Use WP CLI to configure prerequisites for your tests. 
# - Example: To install a specific theme required for tests:
#   wp theme install twentytwentynine
#   (You can then activate this theme during your tests)
#
# Note: Delete this file if it's not required for your setup.
#
# Documentation: Detailed instructions available at https://qit.woo.com/docs/custom-tests/generating-tests
SHELL;
	}

	protected function example_spec_js(): string {
		return <<<'JS'
/*
 * This is an example E2E test. You can write your own tests, or generate them with Codegen.
 * 
 * Read more about it on our documentation: https://qit.woo.com/docs/custom-tests/generating-tests
 */
import { test, expect } from '@playwright/test';
import qit from '/qitHelpers';

test('I can see my plugin menu', async ({ page }) => {
    // Log-in as admin.
    await qit.loginAsAdmin(page);
    // View WordPress Core "Dashboard" heading
    await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();
    // Click on my menu on the sidebar.
    // await page.getByRole('link', { name: 'My Plugin Menu', exact: true }).click();
    // await page.waitForLoadState('networkidle');
    // Assert I see my welcome message when I click it.
    // await expect(page.locator('h3')).toContainText('Welcome to My Plugin!');
});
JS;
	}
}
