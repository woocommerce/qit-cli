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

		if ( ! $this->getHelper( 'question' )->ask( $input, $output, new ConfirmationQuestion( "Generating E2E tests in \"$path_to_generate\" <question>Continue? (y/n)</question> ", false ) ) ) {
			return Command::SUCCESS;
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

		// bootstrap.php.
		if ( ! file_put_contents( $path_to_generate . '/bootstrap/bootstrap.php', $this->bootstrap_php_example() ) ) {
			$output->writeln( '<error>Could not create file: ' . $path_to_generate . '/bootstrap/bootstrap.php</error>' );

			return Command::FAILURE;
		}

		// mu-plugin.php.
		if ( ! file_put_contents( $path_to_generate . '/bootstrap/mu-plugin.php', $this->bootstrap_mu_plugin_example() ) ) {
			$output->writeln( '<error>Could not create file: ' . $path_to_generate . '/bootstrap/mu-plugin.php</error>' );

			return Command::FAILURE;
		}

		// example.spec.js.
		if ( ! file_put_contents( $path_to_generate . '/example.spec.js', $this->example_spec_js() ) ) {
			$output->writeln( '<error>Could not create file: ' . $path_to_generate . '/example.spec.js</error>' );

			return Command::FAILURE;
		}

		$output->writeln( '<info>Example E2E test generated in: ' . $path_to_generate . '</info>' );
		$output->writeln( 'You can now run your first test with <comment>qit run:e2e <your_slug> <path_to_test> --ui</comment>' );
		$output->writeln( 'You can start writing your tests with codegen: <comment>qit run:e2e <your_slug> --codegen</comment>' );
		$output->writeln( 'And when you are ready, you can publish your tests with <comment>qit upload:test <your_slug> <path_to_test></comment>' );
		$output->writeln( 'Read more about it on our documentation: https://qit.woo.com/docs/' );

		return Command::SUCCESS;
	}

	protected function generate_bootstrap_shell_example(): string {
		return <<<'SHELL'
#!/bin/bash

# (All bootstrap files are optional, if you don't need them, just delete this file)
#
# This is an example bootstrap shell script.
# It will be executed before the tests are run.
# You can use WP CLI here to setup things that are needed for your tests.
# For instance, if your tests require a specific theme to be active, you can install it here.
# wp theme install twentytwentynine
# Then, during your test, you can activate the theme you installed.
# Or anything else you might need to do with WP CLI.
#
# Read more about it on our documentation: https://qit.woo.com/docs/
SHELL;
	}

	protected function bootstrap_php_example(): string {
		return <<<'PHP'
<?php
/*
 * (All bootstrap files are optional, if you don't need them, just delete this file)
 * 
 * This is an example bootstrap PHP script.
 * It will be executed before the tests are run.
 * This file will be called only ONCE during the bootstrap phase, before the tests are run.
 * If you need something that runs on every request, use the mu-plugin.php instead.
 * This is a plain PHP script without WordPress.
 * If you need WordPress here, you can load it with:
 * require '/var/www/html/wp-load.php';
 * 
 * Read more about it on our documentation: https://qit.woo.com/docs/
 */
PHP;
	}

	protected function bootstrap_mu_plugin_example(): string {
		return <<<'PHP'
<?php
/*
 * (All bootstrap files are optional, if you don't need them, just delete this file)
 * 
 * This is an example mu-plugin PHP script.
 * It will be executed on every request.
 * 
 * Read more about it on our documentation: https://qit.woo.com/docs/
 */
PHP;
	}

	protected function example_spec_js(): string {
		return <<<'JS'
/*
 * This is an example E2E test. You can write your own tests, or generate them with Codegen.
 * 
 * Read more about it on our documentation: https://qit.woo.com/docs/
 */
import { test, expect } from '@playwright/test';

test('I can see my plugin menu', async ({ page }) => {
    // Log-in as admin.
    await page.goto('/wp-admin/');
    await page.getByLabel('Username or Email Address').click();
    await page.getByLabel('Username or Email Address').fill('admin');
    await page.getByLabel('Password', { exact: true }).click();
    await page.getByLabel('Password', { exact: true }).fill('password');
    await page.getByRole('button', { name: 'Log In' }).click();
    await page.waitForLoadState('networkidle');
    // Force go to wp-admin just to avoid any onboarding guide.
    await page.goto('/wp-admin/');
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
