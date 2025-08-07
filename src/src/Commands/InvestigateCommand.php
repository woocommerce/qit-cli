<?php

namespace QIT_CLI\Commands;

use QIT_CLI\Config;
use QIT_CLI\QITInput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class InvestigateCommand extends QITCommand {
	/** @var Config */
	private Config $config;
	
	protected static $defaultName = 'investigate'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase
	
	public function __construct( Config $config ) {
		$this->config = $config;
		parent::__construct();
	}
	
	protected function configure(): void {
		parent::configure();
		$this
			->setDescription( 'Provide Agentic AI Investigation Context for test failures' )
			->addArgument( 'run_id', InputArgument::OPTIONAL, 'The run ID to investigate. If not provided, uses the most recent run.' )
			->setHelp( <<<'HELP'
AGENTIC AI INVESTIGATION CONTEXT

This command provides comprehensive investigation context for Agentic AI to autonomously
debug test failures in QIT's unique orchestration environment.

QIT'S NON-STANDARD ARCHITECTURE:
Unlike typical test runners, QIT orchestrates multiple independent Playwright instances:
- Each test package runs as a SEPARATE Playwright process
- All packages share the SAME WordPress environment (database & filesystem)
- Results from multiple Playwright runs are MERGED into unified reports
- Database RESETS between packages, filesystem PERSISTS

GLOBAL SETUP vs PACKAGE SETUP (EXAMPLES):
- GLOBAL SETUP: E.g., WooCommerce dismisses wizards, payment gateways mock APIs
  → Creates a ready-to-test baseline that ALL packages benefit from
  → CRUCIAL: Plugins don't need knowledge of other plugins' setup requirements!
- PACKAGE SETUP: E.g., Individual packages can create products, switch themes
  → Isolated changes that won't affect other packages (DB resets after)

WHY THIS MATTERS FOR INVESTIGATION:
With 60,000+ plugins that can use QIT for testing, AI agents need to understand:
- Each plugin's failure is unique - no hardcoded patterns apply
- Global setup failures affect ALL packages (e.g., payment gateway not mocked)
- Package setup failures are isolated (e.g., theme switch failed)
- PHP errors, JavaScript errors, timing issues all possible
- Filesystem changes persist (uploaded files stay), DB changes don't
- Complete evidence gathering is crucial

INVESTIGATION CONTEXT PROVIDED:
- Failure summary with exact error location
- Persisted WordPress debug logs (PHP errors/warnings)
- Direct commands to view videos, traces, and reports
- Package execution order and failure points
- All artifacts in structured, predictable locations

AGENTIC AI WORKFLOW:
1. Test fails → AI sees "Agentic AI: qit investigate"
2. AI runs: qit investigate
3. AI receives complete investigation context
4. AI examines evidence: videos, logs, traces, screenshots
5. AI analyzes without assumptions (60k+ plugins = infinite variety)
6. AI provides specific findings based on evidence

This command enables Agentic AI to conduct thorough, evidence-based investigations
across the diverse ecosystem of 60,000+ WooCommerce plugins that can use QIT.
HELP
			);
	}
	
	protected function doExecute( QITInput $input, OutputInterface $output ): int {
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
		if ( $run_data['status'] === 'failed' ) {
			// Try to load CTRF report for failure details
			$ctrf_file = ( $run_data['artifacts']['directory'] ?? '' ) . '/final/ctrf/ctrf-report.json';
			if ( file_exists( $ctrf_file ) ) {
				$ctrf_data = json_decode( file_get_contents( $ctrf_file ), true );
				if ( isset( $ctrf_data['results']['tests'] ) ) {
					foreach ( $ctrf_data['results']['tests'] as $test ) {
						if ( $test['status'] === 'failed' ) {
							$failure_summary = [
								'name' => $test['name'] ?? 'Unknown test',
								'suite' => $test['suite'] ?? 'Unknown suite',
								'message' => $test['message'] ?? 'No error message',
								'file' => basename( $test['filePath'] ?? 'unknown' ),
								'line' => $this->extractLineNumber( $test['trace'] ?? '' ),
							];
							break;
						}
					}
				}
			}
		}
		
		// Output human-readable explanation of QIT's architecture and the test run
		$output->writeln( '' );
		$output->writeln( '<info>═══════════════════════════════════════════════════════════════════</info>' );
		$output->writeln( '<info>QIT TEST RUN INSPECTION</info>' );
		$output->writeln( '<info>═══════════════════════════════════════════════════════════════════</info>' );
		$output->writeln( '' );
		
		// Show failure summary first if available
		if ( $failure_summary ) {
			$output->writeln( '<error>⚠ FAILURE SUMMARY:</error>' );
			$output->writeln( sprintf( '• Test: "%s"', $failure_summary['name'] ) );
			$output->writeln( sprintf( '• Suite: %s', $failure_summary['suite'] ) );
			$output->writeln( sprintf( '• Location: %s%s', $failure_summary['file'], $failure_summary['line'] ? ':' . $failure_summary['line'] : '' ) );
			
			// Parse error message for common patterns
			$error_msg = $failure_summary['message'];
			if ( strpos( $error_msg, 'TimeoutError' ) !== false ) {
				$output->writeln( '• Error Type: <error>Timeout - Element not found or not ready</error>' );
				if ( preg_match( '/waiting for (.+?)[\n\[]/', $error_msg, $matches ) ) {
					$output->writeln( sprintf( '• Waiting for: %s', trim( $matches[1] ) ) );
				}
				if ( preg_match( '/(\d+)ms exceeded/', $error_msg, $matches ) ) {
					$output->writeln( sprintf( '• Timeout: %sms', $matches[1] ) );
				}
			} else {
				$first_line = strtok( $error_msg, "\n" );
				$output->writeln( sprintf( '• Error: %s', $first_line ) );
			}
			$output->writeln( '' );
		}
		
		// Explain QIT's unique architecture
		$output->writeln( '<comment>WHAT IS QIT?</comment>' );
		$output->writeln( '• QIT (Quality Insights Toolkit) is a test orchestration system for WooCommerce' );
		$output->writeln( '• Tests plugins/themes for cross-compatibility with WordPress & WooCommerce' );
		$output->writeln( '• Used by 60,000+ plugins in the ecosystem for quality assurance' );
		$output->writeln( '• Tests run in isolated Docker containers with Playwright' );
		$output->writeln( '' );
		
		$output->writeln( '<comment>HOW QIT ORCHESTRATION WORKS (CRITICAL TO UNDERSTAND):</comment>' );
		$output->writeln( '• Each test package runs as a SEPARATE Playwright instance' );
		$output->writeln( '• Tests share the SAME WordPress environment (database & filesystem)' );
		$output->writeln( '• Results from multiple Playwright runs are MERGED into final reports' );
		$output->writeln( '• Database snapshots RESET between packages (test isolation)' );
		$output->writeln( '• Filesystem persists across ALL packages (shared state)' );
		$output->writeln( '' );
		
		$output->writeln( '<comment>EXECUTION PHASES (CRITICAL FOR DEBUGGING):</comment>' );
		$output->writeln( '1. <info>GLOBAL SETUP</info>: Runs ONCE, creates shared baseline for ALL packages' );
		$output->writeln( '   EXAMPLES of what plugins might do in global setup:' );
		$output->writeln( '   • WooCommerce might dismiss onboarding wizard' );
		$output->writeln( '   • Payment gateway might mock external APIs (Stripe, PayPal)' );
		$output->writeln( '   • Shipping plugin might set up zones and methods' );
		$output->writeln( '   → Creates baseline DB snapshot WITH this configured state' );
		$output->writeln( '   <comment>KEY BENEFIT: Knowledge decoupling!</comment>' );
		$output->writeln( '   → Plugin X doesn\'t need to know how to dismiss WooCommerce\'s wizard' );
		$output->writeln( '   → Plugin Y doesn\'t need to know how to configure Stripe\'s test mode' );
		$output->writeln( '   → Each plugin handles its OWN setup, others benefit automatically!' );
		$output->writeln( '' );
		$output->writeln( '2. For each test package:' );
		$output->writeln( '   a. <info>SETUP</info>: Package-specific isolated preparation' );
		$output->writeln( '      • Restore DB to global baseline (all global configs intact!)' );
		$output->writeln( '      • EXAMPLES: Package might create 1000 products, switch themes, add users' );
		$output->writeln( '      • These changes are ISOLATED - won\'t affect other packages' );
		$output->writeln( '   b. <info>TEST RUN</info>: Execute the actual tests (npx playwright test)' );
		$output->writeln( '   c. <info>TEARDOWN</info>: Collect artifacts (screenshots, videos, traces)' );
		$output->writeln( '   d. Database resets to baseline (filesystem persists!)' );
		$output->writeln( '' );
		$output->writeln( '3. <info>GLOBAL TEARDOWN</info>: Final cleanup after ALL packages complete' );
		$output->writeln( '4. <info>POST-PROCESSING</info>: Merge results from all Playwright instances' );
		$output->writeln( '' );
		$output->writeln( '<comment>KEY INSIGHT - KNOWLEDGE DECOUPLING:</comment>' );
		$output->writeln( '• Global Setup = Each plugin prepares ITSELF (no cross-plugin knowledge needed!)' );
		$output->writeln( '  → Example: WooCommerce handles its own wizard, Stripe configures its own test mode' );
		$output->writeln( '• Package Setup = Test-specific data (products, users, settings for THIS test)' );
		$output->writeln( '• BENEFIT: Plugin developers test compatibility WITHOUT knowing internals of other plugins!' );
		$output->writeln( '• This decoupling is what enables 60,000+ plugins to test compatibility!' );
		$output->writeln( '' );
		
		// Show the specific run details
		$output->writeln( sprintf( '<comment>THIS RUN (%s):</comment>', $run_data['run_id'] ?? 'unknown' ) );
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
		$output->writeln( '' );
		
		// Test packages executed with status
		$output->writeln( '<comment>PACKAGE EXECUTION ORDER:</comment>' );
		$packages = $run_data['test_packages'] ?? [];
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
			$pkg_id = $package['id'] ?? 'unknown';
			$is_local = strpos( $pkg_id, '/' ) === 0; // Local packages start with /
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
				$output->writeln( sprintf( '   • Has its own setup phase (e.g., create test data)' ) );
				$output->writeln( sprintf( '   • Benefits from ALL global setups without knowing them!' ) );
				$output->writeln( sprintf( '   • No knowledge needed of WooCommerce, Stripe, etc. internals' ) );
			}
		}
		$output->writeln( '' );
		
		// Artifacts and reports
		$output->writeln( '<comment>MERGED ARTIFACTS LOCATION:</comment>' );
		$artifacts = $run_data['artifacts'] ?? [];
		if ( ! empty( $artifacts['directory'] ) ) {
			$output->writeln( sprintf( '• Directory: %s', $artifacts['directory'] ) );
			$output->writeln( '  (Contains merged results from ALL test packages)' );
		}
		
		if ( ! empty( $artifacts['reports'] ) ) {
			$output->writeln( '' );
			$output->writeln( '<comment>AVAILABLE REPORTS:</comment>' );
			foreach ( $artifacts['reports'] as $report ) {
				$output->writeln( sprintf( '• %s: %s', strtoupper( $report['type'] ?? 'unknown' ), $report['path'] ?? 'unknown' ) );
			}
		}
		$output->writeln( '' );
		
		// Debug commands
		if ( ! empty( $run_data['debug_commands'] ) ) {
			$output->writeln( '<comment>DEBUG COMMANDS YOU CAN RUN:</comment>' );
			foreach ( $run_data['debug_commands'] as $cmd ) {
				$output->writeln( sprintf( '• %s:', $cmd['description'] ?? 'Command' ) );
				$output->writeln( sprintf( '  <info>%s</info>', $cmd['command'] ?? '' ) );
			}
			$output->writeln( '' );
		}
		
		// Investigation tools
		if ( $run_data['status'] === 'failed' ) {
			$output->writeln( '<comment>INVESTIGATION COMMANDS:</comment>' );
			
			// 1. Direct commands for this specific failure
			if ( $failure_summary ) {
				$output->writeln( '1. READ THE FAILING TEST:' );
				if ( $failed_package_index !== null && isset( $packages[ $failed_package_index ]['path'] ) ) {
					$test_path = $packages[ $failed_package_index ]['path'];
					$output->writeln( sprintf( '   <info>cat %s/tests/%s</info>', $test_path, $failure_summary['file'] ) );
					if ( $failure_summary['line'] ) {
						$start = max( 1, $failure_summary['line'] - 10 );
						$end = $failure_summary['line'] + 10;
						$output->writeln( sprintf( '   Around line %d: <info>sed -n \'%d,%dp\' %s/tests/%s</info>', 
							$failure_summary['line'], $start, $end, $test_path, $failure_summary['file'] ) );
					}
				}
				$output->writeln( '' );
			}
			
			$output->writeln( '2. VIEW TEST ARTIFACTS:' );
			
			// HTML Report - most reliable way to view everything
			$html_report_path = ( $artifacts['directory'] ?? '' ) . '/final/html-report';
			if ( is_dir( $html_report_path ) ) {
				$output->writeln( sprintf( '   <comment>Interactive Report (recommended):</comment> <info>npx playwright show-report %s</info>', $html_report_path ) );
			}
			
			// Direct paths to known video locations (Playwright standard structure)
			$video_paths = [
				$artifacts['directory'] . '/final/html-report/data',
				$artifacts['directory'] . '/blob-merge-temp/resources',
			];
			
			$found_videos = false;
			foreach ( $video_paths as $path ) {
				if ( is_dir( $path ) ) {
					$videos = glob( $path . '/*.webm' );
					if ( ! empty( $videos ) ) {
						if ( ! $found_videos ) {
							$output->writeln( '   <comment>Videos:</comment>' );
							$found_videos = true;
						}
						foreach ( array_slice( $videos, 0, 2 ) as $video ) {
							$output->writeln( sprintf( '   • <info>vlc %s</info>', $video ) );
						}
					}
				}
			}
			
			// Test results directory for each package
			if ( ! empty( $packages ) ) {
				$output->writeln( '   <comment>Package test results:</comment>' );
				foreach ( $packages as $i => $package ) {
					$pkg_name = basename( dirname( $package['path'] ?? '' ) );
					$test_results = $artifacts['directory'] . '/' . $pkg_name . '/test-results';
					if ( is_dir( $test_results ) ) {
						$output->writeln( sprintf( '   • Package %d: <info>ls %s</info>', $i + 1, $test_results ) );
					}
				}
			}
			$output->writeln( '' );
			
			$output->writeln( '3. GET DETAILED ERROR INFO:' );
			$ctrf_report = ( $artifacts['directory'] ?? '' ) . '/final/ctrf/ctrf-report.json';
			if ( file_exists( $ctrf_report ) ) {
				$output->writeln( sprintf( '   Full error: <info>cat %s | jq \'.results.tests[] | select(.status=="failed")\'</info>', $ctrf_report ) );
			}
			
			// Check for error context files in standard locations
			if ( $failed_package_index !== null && isset( $packages[ $failed_package_index ] ) ) {
				$pkg_name = basename( dirname( $packages[ $failed_package_index ]['path'] ?? '' ) );
				$error_context_path = $artifacts['directory'] . '/' . $pkg_name . '/test-results/*/error-context.md';
				$output->writeln( sprintf( '   Error context: <info>cat %s</info>', $error_context_path ) );
			}
			$output->writeln( '' );
			
			$output->writeln( '4. CHECK WORDPRESS DEBUG LOG:' );
			$debug_log_path = ( $artifacts['directory'] ?? '' ) . '/wordpress-debug.log';
			if ( file_exists( $debug_log_path ) ) {
				$output->writeln( sprintf( '   <info>cat %s</info> (persisted from container)', $debug_log_path ) );
				// Show if there are PHP errors in the log
				$log_content = file_get_contents( $debug_log_path );
				if ( strpos( $log_content, 'Fatal error' ) !== false ) {
					$output->writeln( '   <error>⚠ PHP Fatal errors detected in debug log!</error>' );
				} elseif ( strpos( $log_content, 'Warning' ) !== false || strpos( $log_content, 'Notice' ) !== false ) {
					$output->writeln( '   <comment>PHP warnings/notices found in debug log</comment>' );
				}
			} else {
				$output->writeln( '   <comment>No WordPress debug log found (no PHP errors occurred)</comment>' );
			}
			$output->writeln( '' );
			
			$output->writeln( '5. PLAYWRIGHT NATIVE COMMANDS:' );
			// Use Playwright's native commands instead of find
			$output->writeln( sprintf( '   Show trace: <info>npx playwright show-trace %s/[package-name]/test-results/*/trace.zip</info>', $artifacts['directory'] ?? '/tmp' ) );
			$output->writeln( sprintf( '   Show report: <info>npx playwright show-report %s/final/html-report</info>', $artifacts['directory'] ?? '/tmp' ) );
			
			// Check for actual trace files without using find
			$trace_files = glob( ( $artifacts['directory'] ?? '/tmp' ) . '/*/test-results/*/trace.zip' );
			if ( ! empty( $trace_files ) ) {
				$output->writeln( sprintf( '   Available trace: <info>npx playwright show-trace %s</info>', $trace_files[0] ) );
			}
			$output->writeln( '' );
			
			$output->writeln( '6. UNDERSTANDING SETUP PHASE FAILURES:' );
			$output->writeln( '   • <comment>Global Setup failure?</comment> ALL packages affected' );
			$output->writeln( '     Example: If WooCommerce wizard wasn\'t dismissed, all tests see it' );
			$output->writeln( '   • <comment>Package Setup failure?</comment> Only that package affected' );
			$output->writeln( '     Example: If theme activation failed, only this package\'s tests fail' );
			$output->writeln( '   • <comment>Test failure?</comment> Could be timing, element not found, or state assumptions' );
			$output->writeln( '' );
			
			$output->writeln( '7. KEY STATE MANAGEMENT CONCEPTS:' );
			$output->writeln( '   • Database: Resets to global baseline between packages' );
			$output->writeln( '   • Filesystem: Persists across ALL packages (uploaded files stay!)' );
			$output->writeln( '   • Global Setup State: Shared foundation (each plugin self-configured)' );
			$output->writeln( '   • Package Setup State: Isolated test data (products, users, settings)' );
			$output->writeln( '   • Knowledge Decoupling: No plugin needs to know how to configure another!' );
		} else {
			$output->writeln( '<comment>TEST ANALYSIS:</comment>' );
			$output->writeln( '<info>✓ All test packages completed successfully</info>' );
			$output->writeln( '• The shared environment remained stable throughout the run' );
			$output->writeln( '• Database isolation worked correctly between packages' );
		}
		$output->writeln( '' );
		
		// Next steps
		if ( $run_data['status'] === 'failed' ) {
			$output->writeln( '<comment>SYSTEMATIC DEBUGGING APPROACH:</comment>' );
			$output->writeln( '1. <info>Watch the video</info> - See exactly what happened visually' );
			$output->writeln( '2. <info>Check PHP/WordPress logs</info> - Look for PHP fatals, warnings, or plugin conflicts' );
			$output->writeln( '3. <info>Review screenshots</info> - Capture the exact state at failure' );
			$output->writeln( '4. <info>Examine network activity</info> - Check HAR files for failed requests' );
			$output->writeln( '5. <info>Read the test code</info> - Understand what the test was trying to do' );
			$output->writeln( '6. <info>Analyze traces</info> - Get detailed execution timeline' );
			$output->writeln( '' );
			
			// Show error type for context only
			if ( $failure_summary ) {
				$error_type = 'Unknown error';
				if ( strpos( $failure_summary['message'] ?? '', 'TimeoutError' ) !== false ) {
					$error_type = 'Timeout waiting for element';
				} elseif ( strpos( $failure_summary['message'] ?? '', 'not found' ) !== false ) {
					$error_type = 'Element not found';
				} elseif ( strpos( $failure_summary['message'] ?? '', 'Error' ) !== false ) {
					$error_type = 'JavaScript/Runtime error';
				}
				$output->writeln( sprintf( 'Error Classification: <comment>%s</comment>', $error_type ) );
				$output->writeln( '' );
			}
		}
		
		// Raw data option
		$output->writeln( '<comment>For raw JSON data, add --json flag (coming soon)</comment>' );
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
}