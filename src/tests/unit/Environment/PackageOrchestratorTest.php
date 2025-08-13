<?php

namespace QIT_CLI\Tests\Unit\Environment;

use PHPUnit\Framework\TestCase;
use QIT_CLI\Environment\PackageOrchestrator;
use QIT_CLI\Environment\SecretManager;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Test PackageOrchestrator functionality including CTRF generation and output suppression.
 */
class PackageOrchestratorTest extends TestCase {

	private PackageOrchestrator $orchestrator;
	private BufferedOutput $output;
	private string $temp_dir;

	protected function setUp(): void {
		parent::setUp();
		$this->output = new BufferedOutput();
		$this->orchestrator = new PackageOrchestrator( $this->output );
		$this->temp_dir = sys_get_temp_dir() . '/qit-test-' . uniqid();
		mkdir( $this->temp_dir, 0755, true );
	}

	protected function tearDown(): void {
		if ( is_dir( $this->temp_dir ) ) {
			$this->rrmdir( $this->temp_dir );
		}
		parent::tearDown();
	}

	private function rrmdir( $dir ): void {
		if ( is_dir( $dir ) ) {
			$objects = scandir( $dir );
			foreach ( $objects as $object ) {
				if ( $object != "." && $object != ".." ) {
					if ( is_dir( $dir . "/" . $object ) ) {
						$this->rrmdir( $dir . "/" . $object );
					} else {
						unlink( $dir . "/" . $object );
					}
				}
			}
			rmdir( $dir );
		}
	}

	/**
	 * Test recording lifecycle commands and generating CTRF.
	 */
	public function test_records_lifecycle_commands(): void {
		// Show a command
		$this->orchestrator->show_command( 'npm install', 'host' );
		
		// Simulate some output
		$this->orchestrator->parse_line( 'Installing dependencies...' );
		$this->orchestrator->parse_line( 'Added 100 packages' );
		
		// Record successful command
		$this->orchestrator->record_lifecycle_command( 0, 'setup', 'test-package' );
		
		// Verify it was recorded
		$results = $this->orchestrator->get_lifecycle_results();
		$this->assertCount( 1, $results );
		$this->assertEquals( 'npm install', $results[0]['name'] );
		$this->assertEquals( 'passed', $results[0]['status'] );
		$this->assertEquals( 'setup', $results[0]['extra']['phase'] );
		$this->assertEquals( 'test-package', $results[0]['extra']['package'] );
		$this->assertEquals( 0, $results[0]['extra']['exitCode'] );
	}

	/**
	 * Test recording failed commands.
	 */
	public function test_records_failed_commands(): void {
		$this->orchestrator->show_command( 'npm test', 'docker' );
		$this->orchestrator->parse_line( 'Running tests...' );
		$this->orchestrator->parse_line( 'Test failed!' );
		
		// Record failed command
		$this->orchestrator->record_lifecycle_command( 1, 'run', 'test-package' );
		
		$results = $this->orchestrator->get_lifecycle_results();
		$this->assertCount( 1, $results );
		$this->assertEquals( 'failed', $results[0]['status'] );
		$this->assertEquals( 1, $results[0]['extra']['exitCode'] );
	}

	/**
	 * Test saving orchestrator CTRF to file.
	 */
	public function test_saves_orchestrator_ctrf(): void {
		// Record multiple commands
		$this->orchestrator->show_command( 'echo "setup"', 'host' );
		$this->orchestrator->record_lifecycle_command( 0, 'setup', 'package-1' );
		
		$this->orchestrator->show_command( 'echo "teardown"', 'host' );
		$this->orchestrator->record_lifecycle_command( 0, 'teardown', 'package-1' );
		
		// Save CTRF
		$this->orchestrator->save_orchestrator_ctrf( $this->temp_dir );
		
		// Verify file was created
		$ctrf_file = $this->temp_dir . '/ctrf/orchestrator.json';
		$this->assertFileExists( $ctrf_file );
		
		// Verify content
		$ctrf_data = json_decode( file_get_contents( $ctrf_file ), true );
		$this->assertIsArray( $ctrf_data );
		$this->assertArrayHasKey( 'results', $ctrf_data );
		$this->assertArrayHasKey( 'tests', $ctrf_data['results'] );
		$this->assertCount( 2, $ctrf_data['results']['tests'] );
		
		// Verify summary
		$this->assertEquals( 2, $ctrf_data['results']['summary']['tests'] );
		$this->assertEquals( 2, $ctrf_data['results']['summary']['passed'] );
		$this->assertEquals( 0, $ctrf_data['results']['summary']['failed'] );
	}

	/**
	 * Test output suppression with secrets redaction.
	 */
	public function test_suppresses_output_with_redaction(): void {
		// Create secret manager
		$secret_manager = new SecretManager();
		$secret_manager->add_secret_value( 'API_KEY', 'secret123' );
		$this->orchestrator->set_secret_manager( $secret_manager );
		
		// Enable suppression
		$this->orchestrator->set_suppress_output( true );
		
		// Parse lines containing secrets
		$this->orchestrator->parse_line( 'Using API key: secret123' );
		$this->orchestrator->parse_line( 'Connection established' );
		
		// Output should be suppressed
		$output = $this->output->fetch();
		$this->assertStringNotContainsString( 'secret123', $output );
		$this->assertStringNotContainsString( 'Using API key', $output );
		$this->assertStringNotContainsString( 'Connection established', $output );
	}

	/**
	 * Test showing suppressed output on failure.
	 */
	public function test_shows_suppressed_output_on_failure(): void {
		// Create secret manager
		$secret_manager = new SecretManager();
		$secret_manager->add_secret_value( 'PASSWORD', 'mypass' );
		$this->orchestrator->set_secret_manager( $secret_manager );
		
		// Enable suppression
		$this->orchestrator->set_suppress_output( true );
		
		// Parse lines
		$this->orchestrator->parse_line( 'Connecting with password: mypass' );
		$this->orchestrator->parse_line( 'Error occurred!' );
		
		// Show suppressed output (e.g., on failure)
		$this->orchestrator->show_suppressed_output( 10 );
		
		$output = $this->output->fetch();
		// Should show redacted output
		$this->assertStringContainsString( '[REDACTED:PASSWORD]', $output );
		$this->assertStringNotContainsString( 'mypass', $output );
		$this->assertStringContainsString( 'Error occurred!', $output );
	}

	/**
	 * Test output truncation in CTRF.
	 */
	public function test_truncates_output_in_ctrf(): void {
		$this->orchestrator->show_command( 'long-running-command', 'host' );
		
		// Add many lines of output
		for ( $i = 0; $i < 50; $i++ ) {
			$this->orchestrator->parse_line( "Line $i of output" );
		}
		
		$this->orchestrator->record_lifecycle_command( 0, 'setup', 'test-package' );
		
		$results = $this->orchestrator->get_lifecycle_results();
		$output = $results[0]['extra']['output'];
		
		// Output should be truncated
		$this->assertLessThanOrEqual( 1000, strlen( $output ) );
		$this->assertStringContainsString( 'Line 0', $output );
		$this->assertStringNotContainsString( 'Line 49', $output ); // Should be truncated
	}

	/**
	 * Test CI environment detection.
	 */
	public function test_detects_ci_environment(): void {
		// Set CI environment variable
		putenv( 'CI=true' );
		
		// Create new orchestrator to test CI detection
		$orchestrator = new PackageOrchestrator( $this->output );
		
		// In CI, output should be suppressed by default (unless verbose)
		$orchestrator->parse_line( 'This should be suppressed in CI' );
		
		$output = $this->output->fetch();
		$this->assertStringNotContainsString( 'This should be suppressed in CI', $output );
		
		// Clean up
		putenv( 'CI' );
	}

	/**
	 * Test that lifecycle results are properly formatted.
	 */
	public function test_lifecycle_result_format(): void {
		$this->orchestrator->show_command( 'wp plugin activate woocommerce', 'docker' );
		$this->orchestrator->parse_line( 'Plugin activated successfully' );
		$this->orchestrator->record_lifecycle_command( 0, 'globalSetup', 'core-package' );
		
		$results = $this->orchestrator->get_lifecycle_results();
		$result = $results[0];
		
		// Check all required fields
		$this->assertArrayHasKey( 'name', $result );
		$this->assertArrayHasKey( 'id', $result );
		$this->assertArrayHasKey( 'status', $result );
		$this->assertArrayHasKey( 'duration', $result );
		$this->assertArrayHasKey( 'extra', $result );
		
		// Check extra fields
		$this->assertArrayHasKey( 'type', $result['extra'] );
		$this->assertEquals( 'lifecycle', $result['extra']['type'] );
		$this->assertArrayHasKey( 'phase', $result['extra'] );
		$this->assertArrayHasKey( 'package', $result['extra'] );
		$this->assertArrayHasKey( 'exitCode', $result['extra'] );
		$this->assertArrayHasKey( 'output', $result['extra'] );
		
		// Check output contains parsed line
		$this->assertStringContainsString( 'Plugin activated successfully', $result['extra']['output'] );
	}

	/**
	 * Test empty CTRF is not saved.
	 */
	public function test_empty_ctrf_not_saved(): void {
		// Don't record any commands
		$this->orchestrator->save_orchestrator_ctrf( $this->temp_dir );
		
		// File should not be created
		$ctrf_file = $this->temp_dir . '/ctrf/orchestrator.json';
		$this->assertFalse( file_exists( $ctrf_file ), 'CTRF file should not be created when there are no results' );
	}
}