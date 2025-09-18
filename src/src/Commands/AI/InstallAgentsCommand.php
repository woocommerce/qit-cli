<?php

namespace QIT_CLI\Commands\AI;

use QIT_CLI\Commands\QITCommand;
use QIT_CLI\Config;
use QIT_CLI\QITInput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

class InstallAgentsCommand extends QITCommand {
	/** @var Config */
	private Config $config;

	protected static $defaultName = 'ai:install-agents'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	public function __construct( Config $config ) {
		$this->config = $config;
		parent::__construct();
	}

	protected function configure(): void {
		parent::configure();
		$this
			->setDescription( 'Install QIT AI agents for Claude Code to understand QIT commands better' )
			->setHelp( <<<'HELP'
INSTALL QIT AI AGENTS FOR CLAUDE CODE

This command installs QIT-specific AI agents and configures your project
to automatically delegate QIT tasks to specialized agents.

Requirements:
  - CLAUDE.md must exist in the current directory
  - This is where agents will be installed (.claude/agents/)

The installer will:
  - Install specialized QIT agents to .claude/agents/ (project-local)
  - Create CLAUDE.qit.md with QIT-specific instructions
  - Add @CLAUDE.qit.md import to your existing CLAUDE.md

Example usage:
  <info>qit ai:install-agents</info>

After installation:
  - Claude Code will automatically delegate QIT tasks to specialized agents
  - Commit .claude/ to git so your team gets the agents automatically
  - Other developers just pull - no need to run this command
  - Remove QIT support by deleting CLAUDE.qit.md and its import line

Note: Agents are installed project-local to ensure version consistency across your team.
HELP
			);
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		// First, handle CLAUDE.md setup
		if ( ! $this->update_claude_md( $output ) ) {
			// CLAUDE.md not found, cannot proceed
			return Command::FAILURE;
		}

		// Target directory for agents (project-local)
		$agents_dir = getcwd() . '/.claude/agents';

		// Create target directory if it doesn't exist
		if ( ! is_dir( $agents_dir ) ) {
			if ( ! mkdir( $agents_dir, 0755, true ) ) {
				$output->writeln( '<error>Failed to create directory: ' . $agents_dir . '</error>' );

				return Command::FAILURE;
			}
			$output->writeln( '<info>Created directory: .claude/agents/</info>' );
		}

		// Get all agents (back to multi-agent approach)
		$agents = $this->get_agents();

		// Always clean up obsolete agents first
		$removed_count = $this->remove_obsolete_agents( $agents_dir, $agents, $output );

		// Calculate current version
		$current_version   = $this->calculate_version( $agents );
		$installed_version = $this->get_installed_version( $agents_dir );

		// Check if all agents are present
		$all_agents_present = $this->check_all_agents_present( $agents_dir, $agents );

		// Check if update is needed
		$is_update = false;
		if ( $installed_version !== null ) {
			if ( $installed_version === $current_version && $all_agents_present && $removed_count === 0 ) {
				$output->writeln( '' );
				$output->writeln( '<info>✓ QIT AI agents are already up to date (version ' . $current_version . ')</info>' );

				return Command::SUCCESS;
			}
			$is_update = true;
			$output->writeln( '' );
			$output->writeln( '<comment>Updating QIT agents from version ' . $installed_version . ' to ' . $current_version . '</comment>' );
		} else {
			$output->writeln( '' );
			$output->writeln( '<comment>Installing QIT agents version ' . $current_version . '</comment>' );
		}

		// Install agents
		$output->writeln( '<comment>Installing QIT agents...</comment>' );
		$copied_count = 0;
		$failed_count = 0;

		foreach ( $agents as $filename => $content ) {
			$target_file = $agents_dir . '/' . $filename;
			$action      = file_exists( $target_file ) ? 'Updated' : 'Installed';

			if ( file_put_contents( $target_file, $content ) !== false ) {
				$output->writeln( sprintf( '  <info>✓</info> %s: %s', $action, $filename ) );
				++$copied_count;
			} else {
				$output->writeln( sprintf( '  <error>✗</error> Failed to write: %s', $filename ) );
				++$failed_count;
			}
		}

		// Save version information
		if ( $failed_count === 0 ) {
			if ( ! $this->save_version( $agents_dir, $current_version ) ) {
				$output->writeln( '<warning>Warning: Could not save version information</warning>' );
			}
		}

		// Final summary
		$output->writeln( '' );
		if ( $failed_count === 0 ) {
			$output->writeln( sprintf(
				'<info>✓ Successfully %s %d QIT AI agents to .claude/agents/ (version %s)</info>',
				$is_update ? 'updated' : 'installed',
				$copied_count,
				$current_version
			) );
			$output->writeln( '<info>Claude Code will now understand QIT commands better!</info>' );
			$output->writeln( '' );
			$output->writeln( '<comment>💡 Tip: Commit .claude/ to git so your team gets the agents automatically!</comment>' );
			$output->writeln( '' );
			$output->writeln( 'Installed specialized agents:' );
			$output->writeln( '  Test Package Agents:' );
			$output->writeln( '    • <info>qit-test-package-runner</info> - Test package execution and environment management' );
			$output->writeln( '    • <info>qit-test-package-debugger</info> - Systematic debugging specialist' );
			$output->writeln( '    • <info>qit-test-package-creator</info> - Test package creation with schema validation' );
			$output->writeln( '  Managed Test Agents:' );
			$output->writeln( '    • <info>qit-code-quality</info> - PHPStan, PHPCompatibility, Validation, Plugin-Check' );
			$output->writeln( '    • <info>qit-security</info> - Security and malware scanning' );
			$output->writeln( '    • <info>qit-woo-tests</info> - WooCommerce E2E, API, and compatibility tests' );
			$output->writeln( '    • <info>qit-performance</info> - Performance benchmarking and analysis' );

			return Command::SUCCESS;
		} else {
			$output->writeln( sprintf(
				'<error>Installation completed with errors: %d succeeded, %d failed</error>',
				$copied_count,
				$failed_count
			) );

			return Command::FAILURE;
		}
	}

	/**
	 * Update or create CLAUDE.qit.md and add import to CLAUDE.md.
	 *
	 * @param OutputInterface $output
	 *
	 * @return bool Whether to continue with agent installation
	 */
	private function update_claude_md( OutputInterface $output ): bool {
		// Check if CLAUDE.md exists in current directory
		$claude_md_path = getcwd() . '/CLAUDE.md';
		if ( ! file_exists( $claude_md_path ) ) {
			$output->writeln( '<error>CLAUDE.md not found in current directory.</error>' );
			$output->writeln( '' );
			$output->writeln( 'This command must be run in a project with CLAUDE.md.' );
			$output->writeln( '' );
			$output->writeln( 'To set up QIT agents:' );
			$output->writeln( '  1. Navigate to your project with CLAUDE.md' );
			$output->writeln( '  2. Run <info>qit ai:install-agents</info>' );
			return false;
		}

		$output->writeln( '' );
		$output->writeln( '<comment>Setting up QIT agents for this project...</comment>' );

		// Determine where to create CLAUDE.qit.md (same directory as CLAUDE.md)
		$claude_qit_path = dirname( $claude_md_path ) . '/CLAUDE.qit.md';

		// Create or update CLAUDE.qit.md
		$qit_content = $this->get_qit_content_for_import();
		if ( file_put_contents( $claude_qit_path, $qit_content ) === false ) {
			$output->writeln( '<error>Failed to create CLAUDE.qit.md</error>' );
			return true; // Continue with agent installation even if CLAUDE.qit.md creation fails
		}
		$output->writeln( '<info>✓ Created/updated CLAUDE.qit.md</info>' );

		// Check if import already exists
		$content     = file_get_contents( $claude_md_path );
		$import_line = '@CLAUDE.qit.md';

		if ( strpos( $content, $import_line ) !== false ) {
			$output->writeln( '<info>✓ CLAUDE.md already imports CLAUDE.qit.md</info>' );
		} else {
			// Add import at the end
			$updated_content = rtrim( $content ) . "\n\n" . $import_line . "\n";

			if ( file_put_contents( $claude_md_path, $updated_content ) !== false ) {
				$output->writeln( '<info>✓ Added QIT import to CLAUDE.md</info>' );
			} else {
				$output->writeln( '<warning>Warning: Could not update CLAUDE.md</warning>' );
				$output->writeln( 'Manually add this line to CLAUDE.md: <info>@CLAUDE.qit.md</info>' );
				return true; // Continue with agent installation even if CLAUDE.md update fails
			}
		}

		$output->writeln( '' );
		$output->writeln( '<info>Claude Code will now automatically delegate QIT tasks to specialized agents!</info>' );
		$output->writeln( '' );
		$output->writeln( 'To remove QIT support later:' );
		$output->writeln( '  1. Remove the @CLAUDE.qit.md line from CLAUDE.md' );
		$output->writeln( '  2. Delete the CLAUDE.qit.md file' );

		return true; // Successfully completed, continue with agent installation
	}

	/**
	 * Get the QIT content for CLAUDE.qit.md file.
	 *
	 * @return string QIT content for import
	 */
	private function get_qit_content_for_import(): string {
		return <<<'MARKDOWN'
# QIT Testing Framework Instructions

## 🚨 CRITICAL: MANDATORY QIT DELEGATION RULES 🚨

**YOU MUST ALWAYS DELEGATE TO QIT AGENTS - NO EXCEPTIONS!**

### IMMEDIATE DELEGATION TRIGGERS

**STOP AND DELEGATE IMMEDIATELY when you see:**
1. The word "qit" (ANY case: qit, QIT, Qit) ANYWHERE in the message
2. User mentions ANY QIT-related concepts:
   - E2E tests, Playwright tests, test packages
   - `npx playwright test` commands or test failures
   - Environment sourcing, `qit env` commands
   - WooCommerce/WordPress testing
   - Connection refused errors with localhost ports
   - `qit run:` commands (e2e, api, security, etc.)
   - Test package configuration files
   - Test debugging, logs, screenshots
   - CI/CD test failures

### CRITICAL RULES

**⚠️ MANDATORY: Configuration File Modifications**

You MUST use the `qit-test-package-creator` agent BEFORE any modifications to:
- `qit.json`
- `qit-test.json`
- Any test package manifest files
- Test configuration files

**Why**: This agent contains the JSON schemas and validation logic. Using it ensures:
- Schema compliance
- Valid field values
- Proper structure
- No breaking changes

**NEVER** modify these files directly or with other agents first!

### Specialized QIT Agents

#### Test Package Agents

##### qit-test-package-creator (Configuration & Schemas)
**REQUIRED for**: ANY configuration file changes
**Capabilities**:
- Contains QIT JSON schemas
- Validates configuration structure
- Creates test packages
- Migrates test structures
- Ensures schema compliance

##### qit-test-package-runner (Test Package Execution & Environment)
**Use for**:
- Running `qit run:e2e` commands
- Manual environment management (`qit env:up/down`)
- Executing test packages with Playwright
- Environment sourcing for debugging
- Test package lifecycle management
- "Connection refused" errors and environment issues

##### qit-test-package-debugger (Debug Specialist)
**Use for**:
- Test failures analysis
- Playwright timeout errors
- Log examination
- Screenshot analysis
- Root cause identification

#### Managed Test Agents

##### qit-code-quality (Code Standards)
**Use for**:
- PHPStan static analysis issues
- PHPCompatibility warnings
- WordPress coding standards (Validation)
- Plugin structure problems (Plugin-Check)

##### qit-security (Security & Malware)
**Use for**:
- Security vulnerability detection
- Malware scanning results
- Dependency vulnerabilities
- Hard-coded secrets detection

##### qit-woo-tests (WooCommerce Tests)
**Use for**:
- Woo E2E test failures (with Allure reports)
- Woo API test issues
- Activation problems
- Extension compatibility checks

##### qit-performance (Performance Analysis)
**Use for**:
- Performance benchmarking
- Response time analysis
- Database query optimization
- Resource consumption issues

### Orchestration Patterns

#### Pattern: Modifying Configuration
```
1. ALWAYS START with qit-test-package-creator (get schema, validate)
2. Make modifications through the creator agent
3. Optionally run tests with qit-test-package-runner
```

#### Pattern: Debugging Failed Tests
```
1. Check environment with qit-test-package-runner
2. Analyze failures with qit-test-package-debugger
3. Re-run with debug flags using qit-test-package-runner
```

#### Pattern: Creating New Tests
```
1. Use qit-test-package-creator for structure
2. Validate with qit-test-package-creator
3. Set up environment and execute with qit-test-package-runner
```

### SELF-CORRECTION RULE

**IF YOU CATCH YOURSELF:**
- Reading qit.json or qit-test.json directly → STOP! Use qit-test-package-creator
- Running QIT commands directly → STOP! Use qit-test-package-runner
- Debugging test failures yourself → STOP! Use qit-test-package-debugger
- Working with any QIT file → STOP! Delegate to appropriate agent

### How to Delegate

**IMMEDIATELY say one of these based on the task:**

For Test Packages:
- "I'll use the qit-test-package-creator agent for this configuration task."
- "I'll use the qit-test-package-runner agent for test execution and environment management."
- "I'll delegate to the qit-test-package-debugger to analyze this test failure."

For Managed Tests:
- "I'll delegate to the qit-code-quality agent for PHPStan/PHPCompatibility/Validation issues."
- "I'll use the qit-security agent to help with security and malware concerns."
- "I'll delegate to the qit-woo-tests agent for WooCommerce test failures."
- "I'll use the qit-performance agent for performance analysis."

### DO NOT

- ❌ Modify qit.json or qit-test.json without qit-test-package-creator
- ❌ Attempt QIT tasks without using specialized agents
- ❌ Skip schema validation for configuration changes
- ❌ Use general knowledge instead of QIT agents

### FINAL REMINDER

**YOU DO NOT HAVE QIT KNOWLEDGE!** The QIT agents have:
- Complete schemas and validation rules (you don't)
- QIT-specific workflows and best practices (you don't)
- Environment management expertise (you don't)
- Systematic debugging approaches (you don't)

**EVERY QIT task MUST use QIT agents - NO EXCEPTIONS!**

If you're unsure, err on the side of delegating to QIT agents.
MARKDOWN;
	}

	/**
	 * Get the schema content from file as a string.
	 *
	 * @param string $schema_file Schema filename.
	 *
	 * @return string The schema JSON string
	 */
	private function load_schema_json( string $schema_file ): string {
		$schema_path = __DIR__ . '/../../PreCommand/Schemas/' . $schema_file;
		if ( ! file_exists( $schema_path ) ) {
			throw new \RuntimeException( "Schema file not found: $schema_path" );
		}
		$content = file_get_contents( $schema_path );
		if ( $content === false ) {
			throw new \RuntimeException( "Failed to read schema file: $schema_path" );
		}

		return $content;
	}

	/**
	 * Calculate version hash based on agent content.
	 *
	 * @param array $agents Agent contents.
	 *
	 * @return string Version hash
	 */
	private function calculate_version( array $agents ): string {
		// Include all agent content
		$combined_content = '';
		foreach ( $agents as $filename => $content ) {
			$combined_content .= $filename . $content;
		}

		// Include schema files in version calculation
		$combined_content .= $this->load_schema_json( 'test-package-manifest-schema.json' );
		$combined_content .= $this->load_schema_json( 'qit-schema.json' );

		// Generate a short hash that changes when content changes
		$full_hash  = hash( 'sha256', $combined_content );
		$short_hash = substr( $full_hash, 0, 8 );

		// Create a version string with date and hash
		return gmdate( 'Y.m.d' ) . '-' . $short_hash;
	}

	/**
	 * Get the currently installed agent version.
	 *
	 * @param string $agents_dir The agents directory path.
	 *
	 * @return string|null The installed version or null if not found
	 */
	private function get_installed_version( string $agents_dir ): ?string {
		$version_file = $agents_dir . '/.qit-agent-version';
		if ( file_exists( $version_file ) ) {
			$version_data = json_decode( file_get_contents( $version_file ), true );

			return $version_data['version'] ?? null;
		}

		return null;
	}

	/**
	 * Save the agent version information.
	 *
	 * @param string $agents_dir The agents directory path.
	 * @param string $version The version to save.
	 *
	 * @return bool Success status
	 */
	private function save_version( string $agents_dir, string $version ): bool {
		$version_file = $agents_dir . '/.qit-agent-version';
		$version_data = [
			'version'      => $version,
			'installed_at' => time(),
		];

		return file_put_contents( $version_file, json_encode( $version_data, JSON_PRETTY_PRINT ) ) !== false;
	}

	/**
	 * Get agent definitions with their content.
	 *
	 * @return array<string, string> Array of filename => content
	 */
	private function get_agents(): array {
		// Load schemas as JSON strings
		$test_package_json = $this->load_schema_json( 'test-package-manifest-schema.json' );
		$qit_config_json   = $this->load_schema_json( 'qit-schema.json' );

		// Build the agents array with all specialized agents
		return [
			// Test package specialists
			'qit-test-package-runner.md'   => file_get_contents( __DIR__ . '/agents/qit-test-package-runner.md.keep' ),
			'qit-test-package-debugger.md' => file_get_contents( __DIR__ . '/agents/qit-test-package-debugger.md.keep' ),
			'qit-test-package-creator.md'  => str_replace(
				[
					'{{TEST_PACKAGE_JSON_SCHEMA}}',
					'{{QIT_CONFIG_JSON_SCHEMA}}',
				],
				[
					$test_package_json,
					$qit_config_json,
				],
				file_get_contents( __DIR__ . '/agents/qit-test-package-creator.md.keep' )
			),
			// Managed tests specialists
			'qit-code-quality.md'          => file_get_contents( __DIR__ . '/agents/qit-code-quality.md.keep' ),
			'qit-security.md'              => file_get_contents( __DIR__ . '/agents/qit-security.md.keep' ),
			'qit-woo-tests.md'             => file_get_contents( __DIR__ . '/agents/qit-woo-tests.md.keep' ),
			'qit-performance.md'           => file_get_contents( __DIR__ . '/agents/qit-performance.md.keep' ),
		];
	}

	/**
	 * Remove obsolete agents that are no longer part of the system.
	 *
	 * @param string          $agents_dir The agents directory path.
	 * @param array           $current_agents Array of current agent filenames.
	 * @param OutputInterface $output The output interface.
	 * @return int Number of agents removed
	 */
	private function remove_obsolete_agents( string $agents_dir, array $current_agents, OutputInterface $output ): int {
		if ( ! is_dir( $agents_dir ) ) {
			return 0;
		}

		// Get list of existing agent files
		$existing_files = glob( $agents_dir . '/qit-*.md' );
		if ( empty( $existing_files ) ) {
			return 0;
		}

		// Check each existing file
		$removed_count = 0;
		foreach ( $existing_files as $existing_file ) {
			$filename = basename( $existing_file );

			// If this agent is not in the current agents list, remove it
			if ( ! array_key_exists( $filename, $current_agents ) ) {
				if ( @unlink( $existing_file ) ) {
					$output->writeln( sprintf( '  <comment>✓</comment> Removed obsolete agent: %s', $filename ) );
					++$removed_count;
				}
			}
		}

		if ( $removed_count > 0 ) {
			$output->writeln( '' );
		}

		return $removed_count;
	}

	/**
	 * Check if all required agents are present.
	 *
	 * @param string $agents_dir The agents directory path.
	 * @param array  $agents Array of required agents.
	 * @return bool True if all agents are present
	 */
	private function check_all_agents_present( string $agents_dir, array $agents ): bool {
		foreach ( array_keys( $agents ) as $agent_file ) {
			if ( ! file_exists( $agents_dir . '/' . $agent_file ) ) {
				return false;
			}
		}
		return true;
	}
}
