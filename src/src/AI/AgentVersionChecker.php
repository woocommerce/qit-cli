<?php

namespace QIT_CLI\AI;

use QIT_CLI\App;
use QIT_CLI\Cache;
use Symfony\Component\Filesystem\Path;

class AgentVersionChecker {
	/** @var string */
	private const VERSION_FILE = '/.qit-agent-version';

	/** @var string */
	private const CACHE_KEY_LAST_CHECK = 'qit_ai_agents_last_check';

	/**
	 * Check if agents need updating (once per day).
	 *
	 * @return array|null Array with status info or null if check not needed
	 */
	public function check_agent_status(): ?array {
		// Skip in CI environments
		if ( $this->is_ci_environment() ) {
			return null;
		}

		// Check if Claude Code is installed
		if ( ! is_dir( Path::canonicalize( '~/.claude' ) ) ) {
			return null;
		}

		// Check for CLAUDE.qit.md in current directory
		$claude_qit_path = getcwd() . '/CLAUDE.qit.md';
		if ( file_exists( $claude_qit_path ) ) {
			// CLAUDE.qit.md exists - ensure agents are installed
			$agents_dir = getcwd() . '/.claude/agents';
			$version_file = $agents_dir . self::VERSION_FILE;

			// Check if agents are installed with correct versions
			if ( ! is_dir( $agents_dir ) ) {
				// No agents at all - auto-install silently
				$this->auto_install_agents();
				return null;
			}

			// Agents directory exists, check if all required agents are present
			if ( ! $this->agents_are_installed( $agents_dir ) ) {
				// Some agents missing or outdated - prompt for update
				return [
					'status'  => 'outdated',
					'message' => 'QIT AI agents are outdated or incomplete. Run <info>qit ai:install-agents</info> to update them. (This reminder appears once daily)',
				];
			}

			// Check version to see if agents need updating
			if ( file_exists( $version_file ) ) {
				$version_data = json_decode( file_get_contents( $version_file ), true );
				$installed_version = $version_data['version'] ?? '';
				$current_version = $this->calculate_current_version();

				if ( $installed_version !== $current_version ) {
					return [
						'status'  => 'outdated',
						'message' => 'QIT AI agents are outdated. Run <info>qit ai:install-agents</info> to update them. (This reminder appears once daily)',
					];
				}
			}

			return null; // All agents present and correct
		}

		// No CLAUDE.qit.md, check normally for agent installation
		$agents_dir = getcwd() . '/.claude/agents';
		$version_file = $agents_dir . self::VERSION_FILE;

		// Only check once per day
		if ( ! $this->should_check_agents() ) {
			return null;
		}

		// Update last check time
		$this->update_last_check_time();

		// Check if agents are installed
		if ( ! is_dir( $agents_dir ) || ! file_exists( $version_file ) ) {
			return [
				'status'  => 'not_installed',
				'message' => 'QIT AI agents are not installed in this project. Run <info>qit ai:install-agents</info> to enable Claude Code integration. (This reminder appears once daily)',
			];
		}

		// For project-local agents, we don't need version checking
		// Git handles versioning - just check if they exist
		return null;
	}

	/**
	 * Check if we should check agent status (once per day).
	 *
	 * @return bool
	 */
	private function should_check_agents(): bool {
		$cache = App::make( Cache::class );
		$cache_key = self::CACHE_KEY_LAST_CHECK . '_' . md5( getcwd() );
		$last_check = $cache->get( $cache_key );

		if ( $last_check === null ) {
			return true;
		}

		// Cache stores the timestamp, check if 24 hours have passed
		return ( time() - intval( $last_check ) ) > DAY_IN_SECONDS;
	}

	/**
	 * Update the last check timestamp.
	 */
	private function update_last_check_time(): void {
		$cache = App::make( Cache::class );
		$cache_key = self::CACHE_KEY_LAST_CHECK . '_' . md5( getcwd() );
		// Store current timestamp with 25 hour expiration (slightly more than check interval)
		$cache->set( $cache_key, time(), DAY_IN_SECONDS + HOUR_IN_SECONDS );
	}

	/**
	 * Calculate what the current version should be based on current code.
	 *
	 * @return string
	 */
	private function calculate_current_version(): string {
		// Calculate the same hash that InstallAgentsCommand generates
		$agents = $this->get_agents();

		// Include all agent content
		$combined_content = '';
		foreach ( $agents as $filename => $content ) {
			$combined_content .= $filename . $content;
		}

		// Include schema files in version calculation
		$schema_dir = __DIR__ . '/../PreCommand/Schemas/';
		if ( file_exists( $schema_dir . 'test-package-manifest-schema.json' ) ) {
			$combined_content .= file_get_contents( $schema_dir . 'test-package-manifest-schema.json' );
		}
		if ( file_exists( $schema_dir . 'qit-schema.json' ) ) {
			$combined_content .= file_get_contents( $schema_dir . 'qit-schema.json' );
		}

		// Generate a short hash that changes when content changes
		$full_hash  = hash( 'sha256', $combined_content );
		$short_hash = substr( $full_hash, 0, 8 );

		// Create a version string with date and hash
		return gmdate( 'Y.m.d' ) . '-' . $short_hash;
	}

	/**
	 * Force a re-check on next command run.
	 */
	public function reset_check_timer(): void {
		$cache = App::make( Cache::class );
		$cache_key = self::CACHE_KEY_LAST_CHECK . '_' . md5( getcwd() );
		$cache->delete( $cache_key );
	}

	/**
	 * Check if we're in a CI environment.
	 *
	 * @return bool
	 */
	private function is_ci_environment(): bool {
		// Check common CI environment variables
		return getenv( 'CI' ) !== false ||
		       getenv( 'CONTINUOUS_INTEGRATION' ) !== false ||
		       getenv( 'GITHUB_ACTIONS' ) !== false ||
		       getenv( 'GITLAB_CI' ) !== false ||
		       getenv( 'CIRCLECI' ) !== false ||
		       getenv( 'JENKINS_URL' ) !== false ||
		       getenv( 'BUILDKITE' ) !== false ||
		       getenv( 'TRAVIS' ) !== false;
	}

	/**
	 * Check if agents are properly installed.
	 *
	 * @param string $agents_dir The agents directory path
	 *
	 * @return bool
	 */
	private function agents_are_installed( string $agents_dir ): bool {
		if ( ! is_dir( $agents_dir ) ) {
			return false;
		}

		// Check for key agent files
		$required_agents = [
			// Test package agents
			'qit-test-package-runner.md',
			'qit-test-package-debugger.md',
			'qit-test-package-creator.md',
			// Managed test agents
			'qit-code-quality.md',
			'qit-security.md',
			'qit-woo-tests.md',
			'qit-performance.md',
		];

		foreach ( $required_agents as $agent ) {
			if ( ! file_exists( $agents_dir . '/' . $agent ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Auto-install agents when CLAUDE.qit.md exists but agents are missing.
	 */
	private function auto_install_agents(): void {
		// Get the agents and install them
		$agents_dir = getcwd() . '/.claude/agents';
		if ( ! is_dir( $agents_dir ) ) {
			@mkdir( $agents_dir, 0755, true );
		}

		// Get agents directly (duplicated from InstallAgentsCommand for now)
		$agents = $this->get_agents();

		// Install each agent
		foreach ( $agents as $filename => $content ) {
			$target_file = $agents_dir . '/' . $filename;
			file_put_contents( $target_file, $content );
		}

		// Save version information
		$version = $this->calculate_current_version();
		$version_data = [
			'version'       => $version,
			'installed_at'  => time(),
		];
		file_put_contents( $agents_dir . self::VERSION_FILE, json_encode( $version_data, JSON_PRETTY_PRINT ) );
	}

	/**
	 * Get the agent files.
	 * This is duplicated from InstallAgentsCommand but necessary for auto-install.
	 *
	 * @return array
	 */
	private function get_agents(): array {
		$agents_dir = __DIR__ . '/../Commands/AI/agents/';

		return [
			// Test package agents
			'qit-test-package-runner.md'    => file_get_contents( $agents_dir . 'qit-test-package-runner.md.keep' ),
			'qit-test-package-debugger.md'  => file_get_contents( $agents_dir . 'qit-test-package-debugger.md.keep' ),
			'qit-test-package-creator.md'   => $this->get_creator_agent_content(),
			// Managed test agents
			'qit-code-quality.md'           => file_get_contents( $agents_dir . 'qit-code-quality.md.keep' ),
			'qit-security.md'               => file_get_contents( $agents_dir . 'qit-security.md.keep' ),
			'qit-woo-tests.md'              => file_get_contents( $agents_dir . 'qit-woo-tests.md.keep' ),
			'qit-performance.md'            => file_get_contents( $agents_dir . 'qit-performance.md.keep' ),
		];
	}

	/**
	 * Get the content for qit-test-package-creator agent with schemas.
	 *
	 * @return string
	 */
	private function get_creator_agent_content(): string {
		// Load schemas
		$schema_dir = __DIR__ . '/../PreCommand/Schemas/';
		$package_schema = file_exists( $schema_dir . 'test-package-manifest-schema.json' )
			? file_get_contents( $schema_dir . 'test-package-manifest-schema.json' )
			: '{}';
		$qit_config_json = file_exists( $schema_dir . 'qit-schema.json' )
			? file_get_contents( $schema_dir . 'qit-schema.json' )
			: '{}';

		return str_replace(
			[
				'{{TEST_PACKAGE_JSON_SCHEMA}}',
				'{{QIT_CONFIG_JSON_SCHEMA}}',
			],
			[
				$package_schema,
				$qit_config_json,
			],
			file_get_contents( __DIR__ . '/../Commands/AI/agents/qit-test-package-creator.md.keep' )
		);
	}
}