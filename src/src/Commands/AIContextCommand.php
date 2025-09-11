<?php

namespace QIT_CLI\Commands;

use QIT_CLI\Config;
use QIT_CLI\QITInput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class AIContextCommand extends QITCommand {
	/** @var Config */
	private Config $config;

	protected static $defaultName = 'ai-context'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	public function __construct( Config $config ) {
		$this->config = $config;
		parent::__construct();
	}

	protected function configure(): void {
		parent::configure();
		$this
			->setDescription( 'Provide Agentic AI context for various QIT operations' )
			->addArgument( 'type', InputArgument::OPTIONAL, 'The type of context to provide' )
			->addArgument( 'run_id', InputArgument::OPTIONAL, 'For failed-e2e: specific run ID (defaults to most recent)' )
			->setHelp( <<<'HELP'
AGENTIC AI CONTEXT PROVIDER

This command provides comprehensive context for Agentic AI to work with QIT effectively.
Different context types help AI agents understand QIT's architecture, debug failures,
and write test packages.

AVAILABLE CONTEXT TYPES:

  <info>failed-e2e</info>                  Investigation context for debugging E2E test failures
                           Provides failure details, logs, commands, and debugging steps
                           
  <info>qit-basics</info>                  Understanding QIT's orchestration model and architecture
                           Explains how QIT differs from standard test runners
                           
  <info>understanding-test-packages</info>  Test package lifecycle: global setup, setup, teardown phases
                           Knowledge decoupling and state management concepts
                           
  <info>writing-test-packages</info>        Best practices for creating QIT test packages
                           (Coming soon)
                           
  <info>test-script-execution</info>        How test package scripts are executed in QIT
                           Working directories, file paths, and environment variables
                           
  <info>test-execution-scenarios</info>    How test packages are executed in different contexts
                           Manual testing vs automated runs, phase execution rules

USAGE EXAMPLES:

  # Get context for debugging a failed test run
  qit ai-context failed-e2e
  
  # Get context for a specific test run
  qit ai-context failed-e2e run-abc123
  
  # Understand QIT's architecture
  qit ai-context qit-basics
  
  # Learn about test package structure
  qit ai-context understanding-test-packages
  
  # List all available contexts
  qit ai-context

AGENTIC AI WORKFLOW:

  1. AI encounters a situation (test failure, needs to write tests, etc.)
  2. AI runs appropriate context command
  3. AI receives targeted, comprehensive context
  4. AI can follow references to other context types for deeper understanding
  5. AI proceeds with evidence-based analysis or creation

Each context type provides cross-references to related contexts, enabling
AI agents to progressively build understanding as needed.

WHY CONTEXT MATTERS:

With 60,000+ plugins in the WooCommerce ecosystem using QIT, AI agents need
to understand QIT's unique architecture, especially the knowledge decoupling
that allows plugins to test compatibility without knowing each other's internals.
HELP
			);
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		$type = $input->getArgument( 'type' );

		// If no type specified, list available contexts
		if ( empty( $type ) ) {
			return $this->listContextTypes( $output );
		}

		// Route to appropriate context handler
		switch ( $type ) {
			case 'failed-e2e':
				return $this->showFailedE2EContext( $input, $output );
			case 'qit-basics':
				return $this->showQITBasicsContext( $output );
			case 'understanding-test-packages':
				return $this->showTestPackagesContext( $output );
			case 'test-script-execution':
				return $this->showTestScriptExecutionContext( $output );
			case 'test-execution-scenarios':
				return $this->showTestExecutionScenariosContext( $output );
			case 'writing-test-packages':
				$output->writeln( '<comment>Writing test packages context coming soon!</comment>' );
				$output->writeln( '' );
				$output->writeln( 'For now, try:' );
				$output->writeln( '  • <info>qit ai-context understanding-test-packages</info> - Learn about test structure' );
				$output->writeln( '  • <info>qit ai-context qit-basics</info> - Understand QIT architecture' );
				return Command::SUCCESS;
			default:
				$output->writeln( sprintf( '<error>Unknown context type: %s</error>', $type ) );
				$output->writeln( '' );
				return $this->listContextTypes( $output );
		}
	}

	/**
	 * List available context types
	 */
	private function listContextTypes( OutputInterface $output ): int {
		$output->writeln( '<info>═══════════════════════════════════════════════════════════════════</info>' );
		$output->writeln( '<info>AVAILABLE AI CONTEXT TYPES</info>' );
		$output->writeln( '<info>═══════════════════════════════════════════════════════════════════</info>' );
		$output->writeln( '' );

		$output->writeln( '<comment>failed-e2e</comment>' );
		$output->writeln( '  Investigation context for debugging E2E test failures' );
		$output->writeln( '  Usage: <info>qit ai-context failed-e2e [run_id]</info>' );
		$output->writeln( '' );

		$output->writeln( '<comment>qit-basics</comment>' );
		$output->writeln( '  Understanding QIT\'s orchestration model and architecture' );
		$output->writeln( '  Usage: <info>qit ai-context qit-basics</info>' );
		$output->writeln( '' );

		$output->writeln( '<comment>understanding-test-packages</comment>' );
		$output->writeln( '  Test package lifecycle: global setup, setup, teardown phases' );
		$output->writeln( '  Usage: <info>qit ai-context understanding-test-packages</info>' );
		$output->writeln( '' );

		$output->writeln( '<comment>test-script-execution</comment>' );
		$output->writeln( '  How test package scripts are executed in QIT' );
		$output->writeln( '  Working directories, file paths, and environment variables' );
		$output->writeln( '  Usage: <info>qit ai-context test-script-execution</info>' );
		$output->writeln( '' );

		$output->writeln( '<comment>test-execution-scenarios</comment>' );
		$output->writeln( '  How test packages are executed in different contexts' );
		$output->writeln( '  Usage: <info>qit ai-context test-execution-scenarios</info>' );
		$output->writeln( '' );

		$output->writeln( '<comment>writing-test-packages</comment> <fg=gray>(coming soon)</>' );
		$output->writeln( '  Best practices for creating QIT test packages' );
		$output->writeln( '' );

		$output->writeln( '<info>TIP:</info> Start with <comment>qit ai-context qit-basics</comment> if you\'re new to QIT' );
		$output->writeln( '' );

		return Command::SUCCESS;
	}

	/**
	 * Show QIT basics context
	 */
	private function showQITBasicsContext( OutputInterface $output ): int {
		$output->writeln( '' );
		$output->writeln( '<info>═══════════════════════════════════════════════════════════════════</info>' );
		$output->writeln( '<info>QIT BASICS - AGENTIC AI CONTEXT</info>' );
		$output->writeln( '<info>═══════════════════════════════════════════════════════════════════</info>' );
		$output->writeln( '' );

		$output->writeln( '<comment>WHAT IS QIT?</comment>' );
		$output->writeln( '• QIT (Quality Insights Toolkit) is a test orchestration system for WooCommerce' );
		$output->writeln( '• Tests plugins/themes for cross-compatibility with WordPress & WooCommerce' );
		$output->writeln( '• Used by 60,000+ plugins in the ecosystem for quality assurance' );
		$output->writeln( '• Tests run in isolated Docker containers with Playwright' );
		$output->writeln( '' );

		$output->writeln( '<comment>QIT\'S UNIQUE ORCHESTRATION MODEL:</comment>' );
		$output->writeln( '• Unlike standard test runners (Jest, PHPUnit), QIT orchestrates MULTIPLE' );
		$output->writeln( '  independent Playwright instances that share a WordPress environment' );
		$output->writeln( '' );
		$output->writeln( 'Standard test runner:' );
		$output->writeln( '  [Single Process] → [All Tests] → [Single Report]' );
		$output->writeln( '' );
		$output->writeln( 'QIT orchestration:' );
		$output->writeln( '  [Package 1 Playwright] ↘' );
		$output->writeln( '  [Package 2 Playwright] → [Shared WordPress] → [Merged Reports]' );
		$output->writeln( '  [Package N Playwright] ↗' );
		$output->writeln( '' );

		$output->writeln( '<comment>KEY ARCHITECTURAL DECISIONS:</comment>' );
		$output->writeln( '1. <info>Multiple Playwright Instances</info>' );
		$output->writeln( '   • Each test package runs in its OWN Playwright process' );
		$output->writeln( '   • Enables parallel execution and isolation' );
		$output->writeln( '' );
		$output->writeln( '2. <info>Shared WordPress Environment</info>' );
		$output->writeln( '   • All packages test against the SAME WordPress instance' );
		$output->writeln( '   • Realistic cross-plugin compatibility testing' );
		$output->writeln( '' );
		$output->writeln( '3. <info>Database Isolation, Filesystem Persistence</info>' );
		$output->writeln( '   • Database: Resets between packages (clean slate)' );
		$output->writeln( '   • Filesystem: Persists across packages (uploaded files remain)' );
		$output->writeln( '' );
		$output->writeln( '4. <info>Result Merging</info>' );
		$output->writeln( '   • Multiple Playwright reports → Single unified report' );
		$output->writeln( '   • Videos, screenshots, traces all combined' );
		$output->writeln( '' );

		$output->writeln( '<comment>WHY THIS ARCHITECTURE?</comment>' );
		$output->writeln( '• <info>Scalability</info>: Any plugin in the ecosystem can test compatibility' );
		$output->writeln( '• <info>Isolation</info>: Test failures don\'t cascade between packages' );
		$output->writeln( '• <info>Realism</info>: Tests run in actual WordPress environment' );
		$output->writeln( '• <info>Flexibility</info>: Packages can have different test strategies' );
		$output->writeln( '' );

		$output->writeln( '<comment>NEXT STEP:</comment>' );
		$output->writeln( '• <info>STRONGLY RECOMMENDED (if not already run):</info> <info>qit ai-context understanding-test-packages</info>' );
		$output->writeln( '  → Learn how test packages work with this architecture' );
		$output->writeln( '' );
		$output->writeln( '• For all available contexts: Run <info>qit ai-context --help</info>' );
		$output->writeln( '' );

		return Command::SUCCESS;
	}

	/**
	 * Show test packages context
	 */
	private function showTestPackagesContext( OutputInterface $output ): int {
		$output->writeln( '' );
		$output->writeln( '<info>═══════════════════════════════════════════════════════════════════</info>' );
		$output->writeln( '<info>UNDERSTANDING TEST PACKAGES - AGENTIC AI CONTEXT</info>' );
		$output->writeln( '<info>═══════════════════════════════════════════════════════════════════</info>' );
		$output->writeln( '' );

		$output->writeln( '<comment>EXECUTION PHASES IN DETAIL:</comment>' );
		$output->writeln( '' );

		$output->writeln( '1. <info>GLOBAL SETUP</info> (Runs ONCE for all packages)' );
		$output->writeln( '   Purpose: Create a ready-to-test baseline that ALL packages benefit from' );
		$output->writeln( '' );
		$output->writeln( '   EXAMPLES of what plugins might do:' );
		$output->writeln( '   • WooCommerce: Dismiss onboarding wizard' );
		$output->writeln( '   • Payment Gateway: Mock external APIs (Stripe, PayPal)' );
		$output->writeln( '   • Shipping Plugin: Configure zones and methods' );
		$output->writeln( '   • Subscription Plugin: Set up recurring payment schedules' );
		$output->writeln( '' );
		$output->writeln( '   RESULT: Baseline snapshot with all plugins configured' );
		$output->writeln( '' );

		$output->writeln( '2. <info>FOR EACH TEST PACKAGE:</info>' );
		$output->writeln( '' );
		$output->writeln( '   a. <info>PACKAGE SETUP</info> (Isolated preparation)' );
		$output->writeln( '      • Database restored to global baseline' );
		$output->writeln( '      • Package-specific preparations' );
		$output->writeln( '      ' );
		$output->writeln( '      EXAMPLES of what packages might do:' );
		$output->writeln( '      • Create 1000 test products' );
		$output->writeln( '      • Switch to a specific theme' );
		$output->writeln( '      • Add test users with different roles' );
		$output->writeln( '      • Configure specific plugin settings' );
		$output->writeln( '      ' );
		$output->writeln( '      These changes are ISOLATED (won\'t affect other packages)' );
		$output->writeln( '' );
		$output->writeln( '   b. <info>TEST EXECUTION</info>' );
		$output->writeln( '      • Run actual Playwright tests' );
		$output->writeln( '      • Tests interact with WordPress' );
		$output->writeln( '' );
		$output->writeln( '   c. <info>PACKAGE TEARDOWN</info>' );
		$output->writeln( '      • Collect artifacts (screenshots, videos)' );
		$output->writeln( '      • Save test results' );
		$output->writeln( '' );
		$output->writeln( '   d. <info>DATABASE RESET</info>' );
		$output->writeln( '      • Database returns to global baseline' );
		$output->writeln( '      • Filesystem persists (uploaded files remain!)' );
		$output->writeln( '' );

		$output->writeln( '3. <info>GLOBAL TEARDOWN</info> (Runs ONCE after all packages)' );
		$output->writeln( '   • Final cleanup operations' );
		$output->writeln( '   • Collect global metrics' );
		$output->writeln( '' );

		$output->writeln( '4. <info>POST-PROCESSING</info>' );
		$output->writeln( '   • Merge all Playwright reports' );
		$output->writeln( '   • Combine videos, screenshots, traces' );
		$output->writeln( '   • Generate unified test report' );
		$output->writeln( '' );

		$output->writeln( '<comment>THE POWER OF KNOWLEDGE DECOUPLING:</comment>' );
		$output->writeln( '' );
		$output->writeln( 'Key Insight: <info>Plugins don\'t need to know about each other!</info>' );
		$output->writeln( '' );
		$output->writeln( 'EXAMPLE SCENARIO:' );
		$output->writeln( '• Plugin A: "Simple Product Reviews"' );
		$output->writeln( '• Plugin B: "WooCommerce Subscriptions"' );
		$output->writeln( '• Plugin C: "Stripe Payment Gateway"' );
		$output->writeln( '' );
		$output->writeln( 'In Global Setup:' );
		$output->writeln( '• WooCommerce dismisses its wizard' );
		$output->writeln( '• Stripe configures test mode' );
		$output->writeln( '• Subscriptions sets up schedules' );
		$output->writeln( '' );
		$output->writeln( 'Plugin A\'s tests:' );
		$output->writeln( '• ✅ Don\'t need to know how to dismiss WooCommerce wizard' );
		$output->writeln( '• ✅ Don\'t need to configure Stripe' );
		$output->writeln( '• ✅ Don\'t need to understand Subscriptions' );
		$output->writeln( '• ✅ Can just test product reviews!' );
		$output->writeln( '' );
		$output->writeln( 'This decoupling enables the entire ecosystem to test compatibility!' );
		$output->writeln( '' );

		$output->writeln( '<comment>STATE MANAGEMENT SUMMARY:</comment>' );
		$output->writeln( '┌─────────────────┬──────────────────┬─────────────────┐' );
		$output->writeln( '│ State Type      │ Scope            │ Persistence     │' );
		$output->writeln( '├─────────────────┼──────────────────┼─────────────────┤' );
		$output->writeln( '│ Global Setup    │ All packages     │ In DB snapshot  │' );
		$output->writeln( '│ Package Setup   │ Single package   │ Until DB reset  │' );
		$output->writeln( '│ Database        │ Reset per pkg    │ None            │' );
		$output->writeln( '│ Filesystem      │ All packages     │ Entire run      │' );
		$output->writeln( '└─────────────────┴──────────────────┴─────────────────┘' );
		$output->writeln( '' );

		$output->writeln( '<comment>NEXT STEPS:</comment>' );
		$output->writeln( '• Currently debugging a test failure? Run <info>qit ai-context failed-e2e</info>' );
		$output->writeln( '  → Provides specific investigation commands and context' );
		$output->writeln( '' );
		$output->writeln( '• For all available contexts: Run <info>qit ai-context --help</info>' );
		$output->writeln( '' );

		return Command::SUCCESS;
	}

	/**
	 * Show failed E2E context (the original investigate functionality)
	 */
	private function showFailedE2EContext( QITInput $input, OutputInterface $output ): int {
		$run_id = $input->getArgument( 'run_id' );

		// Determine which run file to load
		if ( empty( $run_id ) ) {
			$run_file = $this->config::get_qit_dir() . '/last-run.json';
		} else {
			$run_file = $this->config::get_qit_dir() . '/runs/' . $run_id . '.json';
		}

		// Check if the file exists
		if ( ! file_exists( $run_file ) ) {
			if ( empty( $run_id ) ) {
				$output->writeln( '<error>No test runs found. Run tests with: qit run:e2e</error>' );
			} else {
				$output->writeln( sprintf( '<error>Run %s not found</error>', $run_id ) );
			}
			return Command::FAILURE;
		}

		// Load the run data
		$run_data = json_decode( file_get_contents( $run_file ), true );

		if ( ! $run_data ) {
			$output->writeln( '<error>Failed to parse run data</error>' );
			return Command::FAILURE;
		}

		// Get quick failure summary first if failed
		$failure_summary = null;
		$ctrf_file       = ( $run_data['artifacts']['directory'] ?? '' ) . '/final/ctrf/ctrf-report.json';
		if ( $run_data['status'] === 'failed' ) {
			// Try to load CTRF report for failure details
			if ( file_exists( $ctrf_file ) ) {
				$ctrf_data = json_decode( file_get_contents( $ctrf_file ), true );
				if ( isset( $ctrf_data['results']['tests'] ) ) {
					foreach ( $ctrf_data['results']['tests'] as $test ) {
						if ( $test['status'] === 'failed' ) {
							$failure_summary = [
								'name'    => $test['name'] ?? 'Unknown test',
								'suite'   => $test['suite'] ?? 'Unknown suite',
								'message' => $test['message'] ?? 'No error message',
								'file'    => basename( $test['filePath'] ?? 'unknown' ),
								'line'    => $this->extractLineNumber( $test['trace'] ?? '' ),
							];
							break;
						}
					}
				}
			}
		}

		// Output human-readable explanation
		$output->writeln( '' );
		$output->writeln( '<info>═══════════════════════════════════════════════════════════════════</info>' );
		$output->writeln( '<info>FAILED E2E TEST - AGENTIC AI INVESTIGATION CONTEXT</info>' );
		$output->writeln( '<info>═══════════════════════════════════════════════════════════════════</info>' );
		$output->writeln( '' );

		// Show failure summary first if available
		if ( $failure_summary ) {
			$output->writeln( '<error>⚠ FAILURE SUMMARY:</error>' );
			$output->writeln( sprintf( '• Test: "%s"', $failure_summary['name'] ) );
			$output->writeln( sprintf( '• Suite: %s', $failure_summary['suite'] ) );
			$output->writeln( sprintf( '• Location: %s%s', $failure_summary['file'], $failure_summary['line'] ? ':' . $failure_summary['line'] : '' ) );

			// Show raw error message - let AI analyze it
			$error_msg  = $failure_summary['message'];
			$first_line = strtok( $error_msg, "\n" );
			$output->writeln( sprintf( '• Error: %s', $first_line ) );

			// If it's multi-line, indicate there's more
			if ( strpos( $error_msg, "\n" ) !== false ) {
				$output->writeln( '  (Full error available in investigation commands below)' );
			}
			$output->writeln( '' );
		}

		// Show the specific run details
		$output->writeln( sprintf( '<comment>TEST RUN DETAILS (%s):</comment>', $run_data['run_id'] ?? 'unknown' ) );
		$output->writeln( sprintf( '• Status: %s', $run_data['status'] === 'passed' ? '<info>✓ PASSED</info>' : '<error>✗ FAILED</error>' ) );
		$output->writeln( sprintf( '• Timestamp: %s', $run_data['timestamp'] ?? 'unknown' ) );
		$output->writeln( '' );

		// Environment details
		$output->writeln( '<comment>ENVIRONMENT CONFIGURATION:</comment>' );
		$env = $run_data['environment'] ?? [];
		$output->writeln( sprintf( '• Environment ID: %s', $env['id'] ?? 'unknown' ) );
		$output->writeln( sprintf( '• WordPress: %s', $env['wordpress'] ?? 'unknown' ) );
		$output->writeln( sprintf( '• PHP: %s', $env['php'] ?? 'unknown' ) );
		if ( ! empty( $env['woocommerce'] ) ) {
			$output->writeln( sprintf( '• WooCommerce: %s', $env['woocommerce'] ) );
		}
		$output->writeln( sprintf( '• Site URL: %s', $env['url'] ?? 'unknown' ) );

		// Show System Under Test if present
		if ( ! empty( $env['sut'] ) ) {
			$output->writeln( '' );
			$output->writeln( '<comment>SYSTEM UNDER TEST (SUT):</comment>' );
			$sut_type = ucfirst( $env['sut']['type'] ?? 'plugin' );
			$output->writeln( sprintf( '• Type: %s', $sut_type ) );
			$output->writeln( sprintf( '• Slug: %s', $env['sut']['slug'] ?? 'unknown' ) );
			if ( ! empty( $env['sut']['id'] ) ) {
				$output->writeln( sprintf( '• ID: %s', $env['sut']['id'] ) );
			}
		}

		// Show active plugins
		if ( ! empty( $env['plugins'] ) ) {
			$output->writeln( '' );
			$output->writeln( 'Active Plugins:' );
			foreach ( $env['plugins'] as $plugin ) {
				$output->writeln( sprintf( '  • %s %s',
					$plugin['slug'] ?? 'unknown',
					! empty( $plugin['version'] ) ? '(' . $plugin['version'] . ')' : ''
				) );
			}
		}

		// Show active themes
		if ( ! empty( $env['themes'] ) ) {
			$output->writeln( '' );
			$output->writeln( 'Active Themes:' );
			foreach ( $env['themes'] as $theme ) {
				$output->writeln( sprintf( '  • %s %s',
					$theme['slug'] ?? 'unknown',
					! empty( $theme['version'] ) ? '(' . $theme['version'] . ')' : ''
				) );
			}
		}
		$output->writeln( '' );

		// Test packages executed with status
		$output->writeln( '<comment>PACKAGE EXECUTION ORDER:</comment>' );
		$packages             = $run_data['test_packages'] ?? [];
		$failed_package_index = null;

		// Try to determine which package failed from CTRF data
		if ( $failure_summary && file_exists( $ctrf_file ?? '' ) ) {
			$ctrf_data = json_decode( file_get_contents( $ctrf_file ), true );
			if ( isset( $ctrf_data['results']['tests'] ) ) {
				foreach ( $ctrf_data['results']['tests'] as $test ) {
					if ( $test['status'] === 'failed' && isset( $test['filePath'] ) ) {
						// Match the file path to a package
						foreach ( $packages as $i => $pkg ) {
							if ( strpos( $test['filePath'], $pkg['path'] ?? '' ) !== false ) {
								$failed_package_index = $i;
								break 2;
							}
						}
					}
				}
			}
		}

		foreach ( $packages as $i => $package ) {
			$pkg_id       = $package['id'] ?? 'unknown';
			$is_local     = strpos( $pkg_id, '/' ) === 0; // Local packages start with /
			$display_name = $is_local ? basename( dirname( $pkg_id ) ) : $pkg_id;

			// Show status based on what we know
			if ( $i === $failed_package_index ) {
				$status_icon = '<error>✗</error>';
				$status_text = ' <error>(FAILED HERE)</error>';
			} elseif ( $failed_package_index !== null && $i > $failed_package_index ) {
				$status_icon = '<comment>✓</comment>';
				$status_text = ' <comment>(ran after failure, DB was reset to global baseline)</comment>';
			} else {
				$status_icon = '<info>✓</info>';
				$status_text = '';
			}

			$output->writeln( sprintf( '%s Package %d: %s%s', $status_icon, $i + 1, $display_name, $status_text ) );

			if ( $is_local ) {
				$output->writeln( sprintf( '   Type: Local test package' ) );
			}
		}
		$output->writeln( '' );

		// Artifacts structure and investigation
		if ( $run_data['status'] === 'failed' ) {
			$artifacts_dir = $run_data['artifacts']['directory'] ?? '';

			// IMPORTANT: Tell AI it needs access to this directory
			$output->writeln( '<error>═══════════════════════════════════════════════════════════════════</error>' );
			$output->writeln( '<error>IMPORTANT: AI AGENT - REQUEST DIRECTORY ACCESS!</error>' );
			$output->writeln( '<error>═══════════════════════════════════════════════════════════════════</error>' );
			$output->writeln( '' );
			$output->writeln( 'To properly investigate this failure, you need access to:' );
			$output->writeln( sprintf( '<info>%s</info>', $artifacts_dir ) );
			$output->writeln( '' );
			$output->writeln( '<comment>If you cannot access this directory, ask the user:</comment>' );
			$output->writeln( '"I need access to the artifacts directory to investigate the failure properly.' );
			$output->writeln( 'Could you please grant me access to:' );
			$output->writeln( sprintf( '%s"', $artifacts_dir ) );
			$output->writeln( '' );
			$output->writeln( 'With access, you will be able to:' );
			$output->writeln( '• Read error context and page snapshots' );
			$output->writeln( '• View test execution logs' );
			$output->writeln( '• Analyze failure details in result.json files' );
			$output->writeln( '• Examine screenshots (if available)' );
			$output->writeln( '' );
			$output->writeln( '<comment>ARTIFACTS DIRECTORY STRUCTURE:</comment>' );
			$output->writeln( sprintf( 'Base: <info>%s</info>', $artifacts_dir ) );
			$output->writeln( '' );

			// Try to get real directory structure
			if ( is_dir( $artifacts_dir ) ) {
				// Check if tree command is available
				$tree_available = false;
				exec( 'which tree 2>/dev/null', $tree_check, $tree_return );
				if ( $tree_return === 0 ) {
					$tree_available = true;
				}

				if ( $tree_available ) {
					// Use tree command with limits
					$output->writeln( '<comment>(Using tree -L 3 --filelimit 50)</comment>' );
					$tree_cmd = sprintf( 'tree -L 3 --filelimit 50 %s 2>&1', escapeshellarg( $artifacts_dir ) );
					exec( $tree_cmd, $tree_output, $tree_status );
					if ( $tree_status === 0 ) {
						foreach ( $tree_output as $line ) {
							$output->writeln( $line );
						}
					} else {
						// Fallback if tree fails
						$output->writeln( '<comment>Tree command failed, using fallback...</comment>' );
						$this->showDirectoryStructureFallback( $output, $artifacts_dir, 0, 3, 50 );
					}
				} else {
					// Use PHP fallback
					$output->writeln( '<comment>(tree command not available, showing structure with depth limit 3, file limit 50)</comment>' );
					$this->showDirectoryStructureFallback( $output, $artifacts_dir, 0, 3, 50 );
				}
			} else {
				$output->writeln( '<error>Artifacts directory not found!</error>' );
			}
			$output->writeln( '' );

			$output->writeln( '<comment>TEST PACKAGE SOURCE CODE:</comment>' );
			foreach ( $packages as $i => $package ) {
				$pkg_path = $package['path'] ?? '';
				if ( $pkg_path ) {
					$output->writeln( sprintf( 'Package %d: <info>%s</info>', $i + 1, $pkg_path ) );
				}
			}
			$output->writeln( '' );

			// Read the actual test code
			$output->writeln( '<comment>FAILING TEST LOCATION:</comment>' );
			if ( $failure_summary && $failed_package_index !== null ) {
				$pkg_path = $packages[ $failed_package_index ]['path'] ?? '';
				if ( $pkg_path && isset( $failure_summary['file'] ) && $failure_summary['file'] ) {
					$test_file_path = $pkg_path . '/tests/' . $failure_summary['file'];
					$output->writeln( sprintf( 'Full path: <info>%s</info>', $test_file_path ) );
					$output->writeln( sprintf( 'Read test: <info>cat %s</info>', $test_file_path ) );
					if ( isset( $failure_summary['line'] ) && $failure_summary['line'] ) {
						$output->writeln( sprintf( 'At line %d: <info>sed -n "%d,%dp" %s</info>',
							$failure_summary['line'],
							max( 1, $failure_summary['line'] - 5 ),
							$failure_summary['line'] + 5,
							$test_file_path
						) );
					}
				}
			}
			$output->writeln( '' );

			$output->writeln( '<comment>INVESTIGATION COMMANDS:</comment>' );
			$output->writeln( '' );

			// 1. Browse artifacts structure
			$output->writeln( '1. EXPLORE ARTIFACTS STRUCTURE:' );
			$output->writeln( sprintf( '   <info>ls -la %s</info>', $artifacts_dir ) );
			$output->writeln( sprintf( '   <info>find %s -type f -name "*.png" | head -5</info>  # Screenshots', $artifacts_dir ) );
			$output->writeln( sprintf( '   <info>find %s -type f -name "*.webm" | head -5</info> # Videos', $artifacts_dir ) );
			$output->writeln( '' );

			// 2. View merged reports
			$output->writeln( '2. VIEW MERGED TEST REPORTS:' );
			$html_report_path = $artifacts_dir . '/final/html-report';
			if ( is_dir( $html_report_path ) ) {
				$output->writeln( sprintf( '   Interactive: <info>npx playwright show-report %s</info>', $html_report_path ) );
			}
			$ctrf_report = $artifacts_dir . '/final/ctrf/ctrf-report.json';
			if ( file_exists( $ctrf_report ) ) {
				$output->writeln( sprintf( '   JSON errors: <info>cat %s | jq \'.results.tests[] | select(.status=="failed")\'</info>', $ctrf_report ) );
			}
			$output->writeln( '' );

			// 3. Package-specific artifacts
			$output->writeln( '3. PACKAGE-SPECIFIC ARTIFACTS:' );
			foreach ( $packages as $i => $package ) {
				$display_name  = basename( dirname( $package['id'] ?? '' ) );
				$pkg_artifacts = $artifacts_dir . '/' . $display_name;
				if ( is_dir( $pkg_artifacts ) ) {
					$output->writeln( sprintf( '   Package %d:', $i + 1 ) );
					$output->writeln( sprintf( '   • Browse: <info>ls %s/test-results/</info>', $pkg_artifacts ) );
					$output->writeln( sprintf( '   • Report: <info>npx playwright show-report %s/playwright-report</info>', $pkg_artifacts ) );
					if ( $i === $failed_package_index ) {
						$output->writeln( '     <error>↑ This package failed</error>' );
					}
				}
			}
			$output->writeln( '' );

			// 4. WordPress debug log
			$output->writeln( '4. WordPress DEBUG LOG:' );
			$debug_log_path = $artifacts_dir . '/wordpress-debug.log';
			if ( file_exists( $debug_log_path ) ) {
				$output->writeln( sprintf( '   <info>cat %s</info>', $debug_log_path ) );
				// Show if there are PHP errors in the log
				$log_content = file_get_contents( $debug_log_path );
				if ( strpos( $log_content, 'Fatal error' ) !== false ) {
					$output->writeln( '   <error>⚠ PHP Fatal errors detected!</error>' );
				} elseif ( strpos( $log_content, 'Warning' ) !== false || strpos( $log_content, 'Notice' ) !== false ) {
					$output->writeln( '   <comment>PHP warnings/notices found</comment>' );
				}
			} else {
				$output->writeln( '   <comment>No debug log (no PHP errors occurred)</comment>' );
			}
			$output->writeln( '' );

			// 5. Traces and browser data
			$output->writeln( '5. PLAYWRIGHT TRACES & BROWSER DATA:' );
			$trace_files = glob( $artifacts_dir . '/*/test-results/*/trace.zip' );
			if ( ! empty( $trace_files ) ) {
				$output->writeln( '   Traces (includes console logs, network activity):' );
				foreach ( array_slice( $trace_files, 0, 2 ) as $trace ) {
					$output->writeln( sprintf( '   <info>npx playwright show-trace %s</info>', $trace ) );
				}
			} else {
				$output->writeln( '   <comment>No trace files found</comment>' );
			}

			// Note about HAR files and console logs
			$output->writeln( '' );
			$output->writeln( '   Browser console logs: Available in traces above' );
			$output->writeln( '   Network HAR files: <info>find ' . $artifacts_dir . ' -name "*.har"</info>' );
			$output->writeln( '   JavaScript errors: Check browser console in trace viewer' );
			$output->writeln( '' );

			// 6. Direct media access
			$output->writeln( '6. DIRECT MEDIA ACCESS:' );
			$output->writeln( '   Screenshots: <info>find ' . $artifacts_dir . ' -name "*.png" -exec file {} \;</info>' );
			$output->writeln( '   Videos: <info>find ' . $artifacts_dir . ' -name "*.webm" -exec ls -lh {} \;</info>' );
			$output->writeln( '' );
		}

		// Guide for investigation - keep it general
		$output->writeln( '<comment>INVESTIGATION APPROACH:</comment>' );
		$output->writeln( 'The artifacts directory contains evidence about what happened.' );
		$output->writeln( 'Each failure is unique - analyze without assumptions.' );
		$output->writeln( '' );
		$output->writeln( 'Available evidence types:' );
		$output->writeln( '• <info>Page snapshots</info> (.md files) - Page state at failure time' );
		$output->writeln( '• <info>Result JSON files</info> - Detailed error messages and traces' );
		$output->writeln( '• <info>Test source code</info> - What the test was attempting' );
		$output->writeln( '• <info>WordPress debug log</info> - PHP errors if any occurred' );
		$output->writeln( '• <info>Videos/screenshots</info> - Visual record if available' );
		$output->writeln( '' );
		$output->writeln( 'Remember: Tests can fail for countless different reasons.' );
		$output->writeln( 'Let the evidence guide your analysis, not assumptions.' );
		$output->writeln( '' );

		// Cross-references to other contexts
		$output->writeln( '<comment>CRITICAL CONTEXT FOR DEBUGGING:</comment>' );
		$output->writeln( '• <error>HIGHLY RECOMMENDED (if not already run):</error> <info>qit ai-context understanding-test-packages</info>' );
		$output->writeln( '  → Explains why DB resets but filesystem persists' );
		$output->writeln( '  → Clarifies global setup vs package setup failures' );
		$output->writeln( '  → Essential for understanding test isolation' );
		$output->writeln( '' );
		$output->writeln( '• For all other contexts: Run <info>qit ai-context --help</info>' );
		$output->writeln( '' );

		return Command::SUCCESS;
	}

	/**
	 * Extract line number from stack trace.
	 */
	private function extractLineNumber( string $trace ): ?int {
		if ( preg_match( '/:(\d+):/', $trace, $matches ) ) {
			return (int) $matches[1];
		}
		return null;
	}

	/**
	 * Show directory structure fallback when tree command is not available.
	 *
	 * @param OutputInterface $output The output interface.
	 * @param string          $dir Directory to show.
	 * @param int             $depth Current depth.
	 * @param int             $max_depth Maximum depth to show.
	 * @param int             $max_files Maximum files to show.
	 * @param string          $prefix Prefix for tree display.
	 * @param int             &$file_count Current file count.
	 */
	private function showDirectoryStructureFallback(
		OutputInterface $output,
		string $dir,
		int $depth = 0,
		int $max_depth = 3,
		int $max_files = 50,
		string $prefix = '',
		int &$file_count = 0
	): void {
		if ( $depth >= $max_depth ) {
			if ( $depth === $max_depth && is_dir( $dir ) ) {
				$items      = scandir( $dir );
				$real_items = array_diff( $items, [ '.', '..' ] );
				if ( count( $real_items ) > 0 ) {
					$output->writeln( $prefix . '└── ... (depth limit reached)' );
				}
			}
			return;
		}

		if ( $file_count >= $max_files ) {
			$output->writeln( $prefix . '└── ... (file limit reached: ' . $max_files . ' files)' );
			return;
		}

		if ( ! is_readable( $dir ) ) {
			return;
		}

		$items = scandir( $dir );
		$items = array_diff( $items, [ '.', '..' ] );
		$items = array_values( $items );

		foreach ( $items as $index => $item ) {
			if ( $file_count >= $max_files ) {
				$output->writeln( $prefix . '└── ... (file limit reached: ' . $max_files . ' files)' );
				break;
			}

			$path        = $dir . '/' . $item;
			$is_last     = ( $index === count( $items ) - 1 );
			$connector   = $is_last ? '└── ' : '├── ';
			$next_prefix = $prefix . ( $is_last ? '    ' : '│   ' );

			if ( is_dir( $path ) ) {
				$output->writeln( $prefix . $connector . $item . '/' );
				$this->showDirectoryStructureFallback( $output, $path, $depth + 1, $max_depth, $max_files, $next_prefix, $file_count );
			} else {
				$output->writeln( $prefix . $connector . $item );
				++$file_count;
			}
		}
	}

	/**
	 * Show test script execution context
	 */
	private function showTestScriptExecutionContext( OutputInterface $output ): int {
		$output->writeln( '═══════════════════════════════════════════════════════════════════' );
		$output->writeln( 'TEST SCRIPT EXECUTION IN QIT - AGENTIC AI CONTEXT' );
		$output->writeln( '═══════════════════════════════════════════════════════════════════' );
		$output->writeln( '' );
		$output->writeln( 'This context explains how QIT executes test package scripts,' );
		$output->writeln( 'including working directories, file paths, and environment setup.' );
		$output->writeln( '' );

		// Working Directory Section
		$output->writeln( '──────────────────────────────────────────────────────────────────' );
		$output->writeln( 'WORKING DIRECTORY BEHAVIOR' );
		$output->writeln( '──────────────────────────────────────────────────────────────────' );
		$output->writeln( '' );
		$output->writeln( 'When QIT executes test package scripts (globalSetup, setup, run, etc.),' );
		$output->writeln( 'it ALWAYS changes to the test package directory first:' );
		$output->writeln( '' );
		$output->writeln( '  <comment># QIT internally does:</comment>' );
		$output->writeln( '  cd /qit/packages/{package-name}' );
		$output->writeln( '  ./bootstrap/global-setup.sh' );
		$output->writeln( '' );
		$output->writeln( 'This means:' );
		$output->writeln( '  • Scripts execute FROM the test package root directory' );
		$output->writeln( '  • Relative paths work as expected (./test-data/, ./config/, etc.)' );
		$output->writeln( '  • No need to determine package location dynamically' );
		$output->writeln( '' );
		$output->writeln( '<info>Key Insight:</info> Your scripts can safely use relative paths!' );
		$output->writeln( '' );

		// File Path Resolution
		$output->writeln( '──────────────────────────────────────────────────────────────────' );
		$output->writeln( 'FILE PATH RESOLUTION' );
		$output->writeln( '──────────────────────────────────────────────────────────────────' );
		$output->writeln( '' );
		$output->writeln( 'CORRECT - Using relative paths:' );
		$output->writeln( '  ✓ <info>./test-data/images/logo.png</info>' );
		$output->writeln( '  ✓ <info>./config/test-settings.json</info>' );
		$output->writeln( '  ✓ <info>../other-package/shared-data.csv</info> (if mounted)' );
		$output->writeln( '' );
		$output->writeln( 'INCORRECT - Hardcoded paths that will fail:' );
		$output->writeln( '  ✗ <error>/var/www/html/wp-content/plugins/my-plugin/test-data/</error>' );
		$output->writeln( '  ✗ <error>/home/user/projects/test-data/</error>' );
		$output->writeln( '  ✗ <error>C:\\Users\\Developer\\test-data\\</error>' );
		$output->writeln( '' );
		$output->writeln( 'Example in bash script:' );
		$output->writeln( '  <comment>#!/bin/bash</comment>' );
		$output->writeln( '  <comment># In global-setup.sh</comment>' );
		$output->writeln( '  ' );
		$output->writeln( '  <comment># Upload test images - using relative path</comment>' );
		$output->writeln( '  for image in ./test-data/images/*.png; do' );
		$output->writeln( '      wp media import "$image" --title="$(basename ${image%.*})" \\' );
		$output->writeln( '        --allow-root' );
		$output->writeln( '  done' );
		$output->writeln( '' );

		// Container Paths
		$output->writeln( '──────────────────────────────────────────────────────────────────' );
		$output->writeln( 'CONTAINER PATHS AND MOUNTING' );
		$output->writeln( '──────────────────────────────────────────────────────────────────' );
		$output->writeln( '' );
		$output->writeln( 'Test packages are mounted at:' );
		$output->writeln( '  <info>/qit/packages/{package-name}/</info>' );
		$output->writeln( '' );
		$output->writeln( 'The package name is derived from:' );
		$output->writeln( '  • Local packages: directory name or manifest package field' );
		$output->writeln( '  • Remote packages: {vendor}/{package} from manifest' );
		$output->writeln( '' );
		$output->writeln( 'Examples:' );
		$output->writeln( '  • woocommerce/e2e → /qit/packages/woocommerce-e2e/' );
		$output->writeln( '  • my-tests → /qit/packages/my-tests/' );
		$output->writeln( '  • partner/integration:1.0 → /qit/packages/partner-integration/' );
		$output->writeln( '' );
		$output->writeln( 'WordPress installation:' );
		$output->writeln( '  • WordPress root: <info>/var/www/html/</info>' );
		$output->writeln( '  • Plugins: <info>/var/www/html/wp-content/plugins/</info>' );
		$output->writeln( '  • Uploads: <info>/var/www/html/wp-content/uploads/</info>' );
		$output->writeln( '' );

		// Environment Variables
		$output->writeln( '──────────────────────────────────────────────────────────────────' );
		$output->writeln( 'ENVIRONMENT VARIABLES' );
		$output->writeln( '──────────────────────────────────────────────────────────────────' );
		$output->writeln( '' );
		$output->writeln( 'QIT provides these environment variables to scripts:' );
		$output->writeln( '' );
		$output->writeln( '  <info>QIT_ENV_ID</info>         - Unique environment identifier' );
		$output->writeln( '                      Example: qitenv0583dcbd67cf4a9c' );
		$output->writeln( '' );
		$output->writeln( '  <info>QIT_SITE_URL</info>       - WordPress site URL' );
		$output->writeln( '                      Example: http://localhost:32797' );
		$output->writeln( '' );
		$output->writeln( '  <info>QIT_ADMIN_EMAIL</info>    - Admin user email' );
		$output->writeln( '                      Default: admin@example.com' );
		$output->writeln( '' );
		$output->writeln( '  <info>QIT_ADMIN_PASSWORD</info> - Admin user password' );
		$output->writeln( '                      Default: password' );
		$output->writeln( '' );
		$output->writeln( '  <info>QIT_ADMIN_USERNAME</info> - Admin username' );
		$output->writeln( '                      Default: admin' );
		$output->writeln( '' );
		$output->writeln( 'Using in scripts:' );
		$output->writeln( '  <comment>if [ -n "$QIT_ENV_ID" ]; then</comment>' );
		$output->writeln( '      <comment># Running in QIT environment</comment>' );
		$output->writeln( '      qit env:exec --env_id=$QIT_ENV_ID "wp cache flush --allow-root"' );
		$output->writeln( '  <comment>else</comment>' );
		$output->writeln( '      <comment># Running locally (wp-env, etc.)</comment>' );
		$output->writeln( '      wp cache flush' );
		$output->writeln( '  <comment>fi</comment>' );
		$output->writeln( '' );

		// Real-World Examples
		$output->writeln( '──────────────────────────────────────────────────────────────────' );
		$output->writeln( 'REAL-WORLD EXAMPLES' );
		$output->writeln( '──────────────────────────────────────────────────────────────────' );
		$output->writeln( '' );
		$output->writeln( '<comment>Example 1: Importing test data in global-setup.sh</comment>' );
		$output->writeln( '' );
		$output->writeln( '  #!/bin/bash' );
		$output->writeln( '  # Upload product images' );
		$output->writeln( '  for img in ./test-data/products/*.jpg; do' );
		$output->writeln( '      [ -f "$img" ] || continue' );
		$output->writeln( '      wp media import "$img" --title="$(basename ${img%.*})" \\' );
		$output->writeln( '        --porcelain --allow-root' );
		$output->writeln( '  done' );
		$output->writeln( '' );
		$output->writeln( '  # Import WooCommerce products from CSV' );
		$output->writeln( '  wp wc product_csv import ./test-data/products.csv --user=1 \\' );
		$output->writeln( '    --allow-root' );
		$output->writeln( '' );
		$output->writeln( '<comment>Example 2: Loading configuration in JavaScript/TypeScript tests</comment>' );
		$output->writeln( '' );
		$output->writeln( '  // In tests/setup.js' );
		$output->writeln( '  const path = require(\'path\');' );
		$output->writeln( '  const testDataDir = path.join(__dirname, \'../test-data\');' );
		$output->writeln( '  const config = require(\'../config/test-config.json\');' );
		$output->writeln( '' );
		$output->writeln( '<comment>Example 3: Conditional execution based on environment</comment>' );
		$output->writeln( '' );
		$output->writeln( '  #!/bin/bash' );
		$output->writeln( '  if [ -n "$QIT_ENV_ID" ]; then' );
		$output->writeln( '      echo "Running in QIT environment: $QIT_ENV_ID"' );
		$output->writeln( '      # QIT-specific setup' );
		$output->writeln( '      TEST_URL="$QIT_SITE_URL"' );
		$output->writeln( '  else' );
		$output->writeln( '      echo "Running in local environment"' );
		$output->writeln( '      # Local environment setup' );
		$output->writeln( '      TEST_URL="http://localhost:8888"' );
		$output->writeln( '  fi' );
		$output->writeln( '' );

		// Common Mistakes
		$output->writeln( '──────────────────────────────────────────────────────────────────' );
		$output->writeln( 'COMMON MISTAKES AND SOLUTIONS' );
		$output->writeln( '──────────────────────────────────────────────────────────────────' );
		$output->writeln( '' );
		$output->writeln( '<error>Mistake 1:</error> Hardcoding WordPress paths' );
		$output->writeln( '  Wrong: /var/www/html/wp-content/plugins/my-plugin/test-data/' );
		$output->writeln( '  Right: ./test-data/ (relative to test package)' );
		$output->writeln( '' );
		$output->writeln( '<error>Mistake 2:</error> Assuming package name in container path' );
		$output->writeln( '  Wrong: /qit/packages/my-custom-name/ (hardcoded)' );
		$output->writeln( '  Right: Use relative paths or detect via $PWD' );
		$output->writeln( '' );
		$output->writeln( '<error>Mistake 3:</error> Not checking for file existence' );
		$output->writeln( '  Wrong: wp media import ./images/*.png' );
		$output->writeln( '  Right: [ -f "$file" ] && wp media import "$file"' );
		$output->writeln( '' );
		$output->writeln( '<error>Mistake 4:</error> Using wp-env specific commands' );
		$output->writeln( '  Wrong: pnpm exec wp-env run tests-cli -- wp ...' );
		$output->writeln( '  Right: Use QIT_ENV_ID check (see examples above)' );
		$output->writeln( '' );

		// Debugging Tips
		$output->writeln( '──────────────────────────────────────────────────────────────────' );
		$output->writeln( 'DEBUGGING SCRIPT EXECUTION' );
		$output->writeln( '──────────────────────────────────────────────────────────────────' );
		$output->writeln( '' );
		$output->writeln( 'Add debug output to your scripts:' );
		$output->writeln( '' );
		$output->writeln( '  <comment>#!/bin/bash</comment>' );
		$output->writeln( '  <comment>set -x  # Enable debug output</comment>' );
		$output->writeln( '  ' );
		$output->writeln( '  echo "Current directory: $(pwd)"' );
		$output->writeln( '  echo "Directory contents: $(ls -la)"' );
		$output->writeln( '  echo "QIT_ENV_ID: $QIT_ENV_ID"' );
		$output->writeln( '  echo "Package directory structure:"' );
		$output->writeln( '  find . -type d -maxdepth 2' );
		$output->writeln( '' );
		$output->writeln( 'View script output in verbose mode:' );
		$output->writeln( '  <info>qit env:up --test-package=./my-tests -v</info>' );
		$output->writeln( '' );
		$output->writeln( 'Execute commands directly in environment:' );
		$output->writeln( '  <info>qit env:exec --env_id=$QIT_ENV_ID "pwd"</info>' );
		$output->writeln( '  <info>qit env:exec --env_id=$QIT_ENV_ID "ls -la /qit/packages/"</info>' );
		$output->writeln( '' );

		// Cross-references
		$output->writeln( '──────────────────────────────────────────────────────────────────' );
		$output->writeln( 'RELATED CONTEXTS' );
		$output->writeln( '──────────────────────────────────────────────────────────────────' );
		$output->writeln( '' );
		$output->writeln( '• For test package structure:' );
		$output->writeln( '  Run <info>qit ai-context understanding-test-packages</info>' );
		$output->writeln( '' );
		$output->writeln( '• For execution scenarios:' );
		$output->writeln( '  Run <info>qit ai-context test-execution-scenarios</info>' );
		$output->writeln( '' );
		$output->writeln( '• For QIT architecture:' );
		$output->writeln( '  Run <info>qit ai-context qit-basics</info>' );
		$output->writeln( '' );
		$output->writeln( '═══════════════════════════════════════════════════════════════════' );
		$output->writeln( '' );

		return Command::SUCCESS;
	}

	/**
	 * Show test execution scenarios context
	 */
	private function showTestExecutionScenariosContext( OutputInterface $output ): int {
		$output->writeln( '═══════════════════════════════════════════════════════════════════' );
		$output->writeln( 'TEST PACKAGE EXECUTION SCENARIOS - AGENTIC AI CONTEXT' );
		$output->writeln( '═══════════════════════════════════════════════════════════════════' );
		$output->writeln( '' );
		$output->writeln( 'UNDERSTANDING HOW TEST PACKAGES ARE EXECUTED IN DIFFERENT CONTEXTS' );
		$output->writeln( '' );

		// Scenario 1
		$output->writeln( '──────────────────────────────────────────────────────────────────' );
		$output->writeln( 'SCENARIO 1: DEVELOPER TESTING THEIR OWN PACKAGE' );
		$output->writeln( '──────────────────────────────────────────────────────────────────' );
		$output->writeln( '' );
		$output->writeln( 'Command Flow:' );
		$output->writeln( '  cd /my-plugin/tests/e2e  # Has qit-test.json' );
		$output->writeln( '  qit env:up --plugin=../../  # Auto-detects test package from qit-test.json' );
		$output->writeln( '  source $(qit env:source)' );
		$output->writeln( '  npx playwright test' );
		$output->writeln( '  qit env:reset  # Return to clean state' );
		$output->writeln( '  npx playwright test  # Run again' );
		$output->writeln( '' );
		$output->writeln( 'What Happens:' );
		$output->writeln( '  1. Environment spins up with required plugins/themes' );
		$output->writeln( '  2. GlobalSetup runs for the test package' );
		$output->writeln( '  3. Setup runs for the test package (creates test-ready state)' );
		$output->writeln( '  4. Database state is saved' );
		$output->writeln( '  5. Developer runs tests manually' );
		$output->writeln( '  6. env:reset restores to post-setup state' );
		$output->writeln( '' );
		$output->writeln( 'Use Case: Iterative development and debugging of test packages' );
		$output->writeln( '' );

		// Scenario 2
		$output->writeln( '──────────────────────────────────────────────────────────────────' );
		$output->writeln( 'SCENARIO 2: TESTING WITH MULTIPLE PACKAGES (MANUAL)' );
		$output->writeln( '──────────────────────────────────────────────────────────────────' );
		$output->writeln( '' );
		$output->writeln( 'Command Flow:' );
		$output->writeln( '  qit env:up --test-package=./checkout-tests --test-package=./payment-tests' );
		$output->writeln( '  source $(qit env:source)' );
		$output->writeln( '  cd checkout-tests && npx playwright test' );
		$output->writeln( '' );
		$output->writeln( 'What Happens:' );
		$output->writeln( '  1. Environment includes requirements from BOTH packages' );
		$output->writeln( '  2. GlobalSetup runs for BOTH packages (combined baseline)' );
		$output->writeln( '  3. Setup runs for MAIN package only (see detection rules below)' );
		$output->writeln( '  4. Developer manually runs specific test suites' );
		$output->writeln( '' );
		$output->writeln( 'Main Package Detection (priority order):' );
		$output->writeln( '  1. Package in current directory with qit-test.json' );
		$output->writeln( '  2. First local package (./checkout-tests in example)' );
		$output->writeln( '  3. First remote package (if no local packages)' );
		$output->writeln( '' );
		$output->writeln( 'Use Case: Testing package interactions and compatibility' );
		$output->writeln( '' );

		// Scenario 3
		$output->writeln( '──────────────────────────────────────────────────────────────────' );
		$output->writeln( 'SCENARIO 3: QA MANUAL EXPLORATION' );
		$output->writeln( '──────────────────────────────────────────────────────────────────' );
		$output->writeln( '' );
		$output->writeln( 'Command Flow:' );
		$output->writeln( '  qit env:up --test-package=woocommerce/smoke-tests:latest' );
		$output->writeln( '  source $(qit env:source)' );
		$output->writeln( '  # Browse WordPress admin at $QIT_SITE_URL/wp-admin' );
		$output->writeln( '  # Check WooCommerce settings manually' );
		$output->writeln( '  # Or run specific test files:' );
		$output->writeln( '  npx -y @playwright/test tests/critical-path.spec.js --headed' );
		$output->writeln( '' );
		$output->writeln( 'What Happens:' );
		$output->writeln( '  1. Downloads remote test package' );
		$output->writeln( '  2. Sets up environment with package requirements' );
		$output->writeln( '  3. Runs globalSetup and setup from the package' );
		$output->writeln( '  4. QA engineer can interact with prepared environment' );
		$output->writeln( '' );
		$output->writeln( 'Use Case: Manual verification and exploratory testing' );
		$output->writeln( '' );

		// Scenario 4
		$output->writeln( '──────────────────────────────────────────────────────────────────' );
		$output->writeln( 'SCENARIO 4: AUTOMATED CI/CD PIPELINE' );
		$output->writeln( '──────────────────────────────────────────────────────────────────' );
		$output->writeln( '' );
		$output->writeln( 'Command Flow:' );
		$output->writeln( '  qit run:e2e woocommerce \\' );
		$output->writeln( '    --test-package=./integration-tests \\' );
		$output->writeln( '    --test-package=partner/regression:2.0.0' );
		$output->writeln( '' );
		$output->writeln( 'What Happens:' );
		$output->writeln( '  1. run:e2e calls env:up with --skip-test-phases flag' );
		$output->writeln( '  2. env:up sets up environment and processes requirements' );
		$output->writeln( '  3. Test packages are prepared but phases are deferred to run:e2e' );
		$output->writeln( '  4. run:e2e orchestrates the full lifecycle:' );
		$output->writeln( '     - GlobalSetup (once for all packages)' );
		$output->writeln( '     - For each package:' );
		$output->writeln( '       * DB restore to baseline' );
		$output->writeln( '       * Package setup' );
		$output->writeln( '       * Test execution' );
		$output->writeln( '       * Package teardown' );
		$output->writeln( '       * Results collection' );
		$output->writeln( '     - GlobalTeardown (once at end)' );
		$output->writeln( '' );
		$output->writeln( 'Use Case: Full automated test execution with proper isolation' );
		$output->writeln( '' );

		// Scenario 5
		$output->writeln( '──────────────────────────────────────────────────────────────────' );
		$output->writeln( 'SCENARIO 5: DEBUGGING FAILED CI TESTS' );
		$output->writeln( '──────────────────────────────────────────────────────────────────' );
		$output->writeln( '' );
		$output->writeln( 'Command Flow:' );
		$output->writeln( '  # After CI failure...' );
		$output->writeln( '  qit run:e2e woocommerce --test-package=./failing-tests' );
		$output->writeln( '  # Observe failure' );
		$output->writeln( '' );
		$output->writeln( '  # Recreate exact environment for debugging' );
		$output->writeln( '  cd failing-plugin/tests/e2e  # Has qit-test.json' );
		$output->writeln( '  qit env:up --plugin=../../  # Auto-detects test package' );
		$output->writeln( '  source $(qit env:source)' );
		$output->writeln( '  npx playwright test --debug --headed' );
		$output->writeln( '' );
		$output->writeln( 'What Happens:' );
		$output->writeln( '  1. env:up creates same environment as CI' );
		$output->writeln( '  2. Runs same globalSetup and setup' );
		$output->writeln( '  3. Developer can debug interactively' );
		$output->writeln( '  4. Environment matches CI state exactly' );
		$output->writeln( '' );
		$output->writeln( 'Use Case: Reproducing and fixing CI failures locally' );
		$output->writeln( '' );

		// Scenario 6
		$output->writeln( '──────────────────────────────────────────────────────────────────' );
		$output->writeln( 'SCENARIO 6: ENVIRONMENT WITHOUT TEST PACKAGES' );
		$output->writeln( '──────────────────────────────────────────────────────────────────' );
		$output->writeln( '' );
		$output->writeln( 'Command Flow:' );
		$output->writeln( '  qit env:up --plugin=woocommerce:latest --theme=storefront' );
		$output->writeln( '  source $(qit env:source)' );
		$output->writeln( '  echo "Site URL: $QIT_SITE_URL"' );
		$output->writeln( '  # Manual testing, no test packages' );
		$output->writeln( '' );
		$output->writeln( 'What Happens:' );
		$output->writeln( '  1. Simple environment with specified extensions' );
		$output->writeln( '  2. NO test phases run' );
		$output->writeln( '  3. Clean WordPress/WooCommerce installation' );
		$output->writeln( '' );
		$output->writeln( 'Use Case: Manual testing without test automation' );
		$output->writeln( '' );

		// Key Architectural Decisions
		$output->writeln( '──────────────────────────────────────────────────────────────────' );
		$output->writeln( 'KEY ARCHITECTURAL DECISIONS' );
		$output->writeln( '──────────────────────────────────────────────────────────────────' );
		$output->writeln( '' );
		$output->writeln( 'Phase Execution Rules:' );
		$output->writeln( '┌─────────────────┬────────────────────┬──────────────────────┐' );
		$output->writeln( '│ Command         │ GlobalSetup        │ Setup                │' );
		$output->writeln( '├─────────────────┼────────────────────┼──────────────────────┤' );
		$output->writeln( '│ env:up manual   │ All packages       │ Main package only    │' );
		$output->writeln( '│ env:up from e2e │ Skip (deferred)    │ Skip (deferred)      │' );
		$output->writeln( '│ run:e2e         │ All packages once  │ Each package         │' );
		$output->writeln( '└─────────────────┴────────────────────┴──────────────────────┘' );
		$output->writeln( '' );
		$output->writeln( 'Main Package Detection Priority:' );
		$output->writeln( '  1. Current directory with qit-test.json (auto-detected, no --test-package needed)' );
		$output->writeln( '  2. First local package (./path/to/package)' );
		$output->writeln( '  3. First remote package (namespace/package:version)' );
		$output->writeln( '' );
		$output->writeln( 'Note: If current directory has qit-test.json, it\'s automatically used as a test package.' );
		$output->writeln( 'No need to specify --test-package=./ in this case.' );
		$output->writeln( '' );
		$output->writeln( 'State Management:' );
		$output->writeln( '  • env:up saves state after setup (for env:reset)' );
		$output->writeln( '  • run:e2e manages state per package (DB restore between)' );
		$output->writeln( '  • Filesystem persists across all operations' );
		$output->writeln( '' );
		$output->writeln( 'Flag Usage:' );
		$output->writeln( '  • --skip-test-phases: When run:e2e calls env:up' );
		$output->writeln( '  • Future: --test-package-priority to override main detection' );
		$output->writeln( '' );

		// Common Patterns
		$output->writeln( '──────────────────────────────────────────────────────────────────' );
		$output->writeln( 'COMMON PATTERNS AND BEST PRACTICES' );
		$output->writeln( '──────────────────────────────────────────────────────────────────' );
		$output->writeln( '' );
		$output->writeln( '1. Single Package Development:' );
		$output->writeln( '   cd my-plugin/tests/e2e  # Directory with qit-test.json' );
		$output->writeln( '   qit env:up --plugin=../../  # Auto-detects test package' );
		$output->writeln( '   source $(qit env:source) && npx playwright test' );
		$output->writeln( '   qit env:reset  # Return to clean state for next run' );
		$output->writeln( '' );
		$output->writeln( '2. Multi-Package Testing:' );
		$output->writeln( '   Use run:e2e for proper isolation and orchestration' );
		$output->writeln( '   qit run:e2e woocommerce --test-package=./pkg1 --test-package=./pkg2' );
		$output->writeln( '' );
		$output->writeln( '3. Debugging CI Failures:' );
		$output->writeln( '   # Recreate exact CI environment locally' );
		$output->writeln( '   cd my-plugin/tests/e2e  # Has qit-test.json' );
		$output->writeln( '   qit env:up --plugin=../../  # Auto-detects test package' );
		$output->writeln( '   source $(qit env:source) && npx playwright test --debug' );
		$output->writeln( '' );
		$output->writeln( '4. Performance Testing:' );
		$output->writeln( '   cd my-plugin/tests/performance  # Has qit-test.json' );
		$output->writeln( '   qit env:up --plugin=../../  # Auto-detects test package' );
		$output->writeln( '   source $(qit env:source)' );
		$output->writeln( '   for i in {1..10}; do npx playwright test; done' );
		$output->writeln( '' );
		$output->writeln( '5. Compatibility Testing:' );
		$output->writeln( '   # From project root (no qit-test.json):' );
		$output->writeln( '   qit env:up --test-package=./tests/e2e \\' );
		$output->writeln( '     --plugin=./ \\' );
		$output->writeln( '     --plugin=woocommerce-subscriptions:latest' );
		$output->writeln( '' );

		// Troubleshooting
		$output->writeln( '──────────────────────────────────────────────────────────────────' );
		$output->writeln( 'TROUBLESHOOTING GUIDE' );
		$output->writeln( '──────────────────────────────────────────────────────────────────' );
		$output->writeln( '' );
		$output->writeln( 'Q: Why doesn\'t setup run for all my test packages?' );
		$output->writeln( 'A: env:up runs setup only for the main package. Use run:e2e for' );
		$output->writeln( '   multiple package execution with proper isolation.' );
		$output->writeln( '' );
		$output->writeln( 'Q: How do I control which package is "main"?' );
		$output->writeln( 'A: Place qit-e2e.json in your working directory, or ensure your' );
		$output->writeln( '   target package is listed first in the command.' );
		$output->writeln( '' );
		$output->writeln( 'Q: Why does run:e2e take longer than env:up + manual tests?' );
		$output->writeln( 'A: run:e2e ensures proper isolation by restoring database state' );
		$output->writeln( '   between each package. This guarantees test independence.' );
		$output->writeln( '' );
		$output->writeln( 'Q: Can I skip globalSetup when debugging?' );
		$output->writeln( 'A: Not recommended - globalSetup creates the baseline that tests' );
		$output->writeln( '   expect. Skipping it may cause unexpected failures.' );
		$output->writeln( '' );
		$output->writeln( 'Q: How do I test packages that require different PHP versions?' );
		$output->writeln( 'A: Currently, use separate env:up calls with different --php flags.' );
		$output->writeln( '   Multi-PHP testing in single run is not yet supported.' );
		$output->writeln( '' );

		// Cross-references
		$output->writeln( '──────────────────────────────────────────────────────────────────' );
		$output->writeln( 'NEXT STEPS' );
		$output->writeln( '──────────────────────────────────────────────────────────────────' );
		$output->writeln( '' );
		$output->writeln( '• To understand test package structure:' );
		$output->writeln( '  Run <info>qit ai-context understanding-test-packages</info>' );
		$output->writeln( '' );
		$output->writeln( '• To debug a failed test run:' );
		$output->writeln( '  Run <info>qit ai-context failed-e2e</info>' );
		$output->writeln( '' );
		$output->writeln( '• To understand QIT architecture:' );
		$output->writeln( '  Run <info>qit ai-context qit-basics</info>' );
		$output->writeln( '' );
		$output->writeln( '═══════════════════════════════════════════════════════════════════' );
		$output->writeln( '' );

		return Command::SUCCESS;
	}
}
