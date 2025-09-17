<?php

namespace QIT_CLI\AI;

class AgentVersionChecker {
	/** @var string */
	private const VERSION_FILE = '/.qit-agent-version';

	/** @var string */
	private const LAST_CHECK_FILE = '/.qit-agent-last-check';

	/** @var int */
	private const CHECK_INTERVAL = 86400; // 24 hours in seconds

	/**
	 * Check if agents need updating (once per day).
	 *
	 * @return array|null Array with status info or null if check not needed
	 */
	public function check_agent_status(): ?array {
		$home = getenv( 'HOME' );
		if ( empty( $home ) ) {
			return null;
		}

		// Only check if Claude Code is installed (has ~/.claude directory)
		$claude_dir = $home . '/.claude';
		if ( ! is_dir( $claude_dir ) ) {
			return null; // No notification if Claude Code not installed
		}

		// Only check once per day
		if ( ! $this->should_check_agents() ) {
			return null;
		}

		// Update last check time
		$this->update_last_check_time();

		$agents_dir   = $home . '/.claude/agents';
		$version_file = $agents_dir . self::VERSION_FILE;

		// Check if agents are installed
		if ( ! is_dir( $agents_dir ) || ! file_exists( $version_file ) ) {
			return [
				'status'  => 'not_installed',
				'message' => 'QIT AI agents are not installed. Run <info>qit ai:install-agents</info> to enable Claude Code integration.',
			];
		}

		// Check version
		$version_data = json_decode( file_get_contents( $version_file ), true );
		if ( ! isset( $version_data['version'] ) ) {
			return [
				'status'  => 'unknown',
				'message' => 'QIT AI agents version unknown. Consider reinstalling with <info>qit ai:install-agents</info>',
			];
		}

		$installed_version = $version_data['version'];
		$installed_at      = $version_data['installed_at'] ?? 0;
		$current_version   = $this->calculate_current_version();

		// Check if outdated
		if ( $installed_version !== $current_version ) {
			$days_old = floor( ( time() - $installed_at ) / 86400 );
			return [
				'status'           => 'outdated',
				'installed'        => $installed_version,
				'available'        => $current_version,
				'days_old'         => $days_old,
				'message'          => sprintf(
					'QIT AI agents are outdated (version %s, installed %d days ago). Run <info>qit ai:install-agents</info> to update to %s.',
					$installed_version,
					$days_old,
					$current_version
				),
			];
		}

		// Agents are up to date - no message needed
		return null;
	}

	/**
	 * Check if we should check agent status (once per day).
	 *
	 * @return bool
	 */
	private function should_check_agents(): bool {
		$home = getenv( 'HOME' );
		if ( empty( $home ) ) {
			return false;
		}

		$check_file = $home . '/.claude/agents' . self::LAST_CHECK_FILE;
		if ( ! file_exists( $check_file ) ) {
			return true;
		}

		$last_check = intval( file_get_contents( $check_file ) );
		return ( time() - $last_check ) > self::CHECK_INTERVAL;
	}

	/**
	 * Update the last check timestamp.
	 */
	private function update_last_check_time(): void {
		$home = getenv( 'HOME' );
		if ( empty( $home ) ) {
			return;
		}

		$agents_dir = $home . '/.claude/agents';
		if ( ! is_dir( $agents_dir ) ) {
			@mkdir( $agents_dir, 0755, true );
		}

		$check_file = $agents_dir . self::LAST_CHECK_FILE;
		file_put_contents( $check_file, time() );
	}

	/**
	 * Calculate what the current version should be based on current code.
	 *
	 * @return string
	 */
	private function calculate_current_version(): string {
		// We need to calculate the same hash that InstallAgentsCommand would generate
		// This is a simplified version - ideally we'd share the logic with InstallAgentsCommand
		$schema_dir = __DIR__ . '/../PreCommand/Schemas/';
		$hash_input = '';

		// Include schema file contents (matching InstallAgentsCommand)
		if ( file_exists( $schema_dir . 'test-package-manifest-schema.json' ) ) {
			$hash_input .= file_get_contents( $schema_dir . 'test-package-manifest-schema.json' );
		}
		if ( file_exists( $schema_dir . 'qit-schema.json' ) ) {
			$hash_input .= file_get_contents( $schema_dir . 'qit-schema.json' );
		}

		// Include the agent files themselves
		$agents_dir = __DIR__ . '/../Commands/AI/';
		if ( file_exists( $agents_dir . 'InstallAgentsCommand.php' ) ) {
			// Use a simplified hash based on file size and modification time
			// since we can't easily recreate the exact agent content
			$hash_input .= filesize( $agents_dir . 'InstallAgentsCommand.php' );
			$hash_input .= filemtime( $agents_dir . 'InstallAgentsCommand.php' );
		}

		// Generate version similar to InstallAgentsCommand
		$full_hash  = hash( 'sha256', $hash_input );
		$short_hash = substr( $full_hash, 0, 8 );

		return gmdate( 'Y.m.d' ) . '-' . $short_hash;
	}

	/**
	 * Force a re-check on next command run.
	 */
	public function reset_check_timer(): void {
		$home = getenv( 'HOME' );
		if ( empty( $home ) ) {
			return;
		}

		$check_file = $home . '/.claude/agents' . self::LAST_CHECK_FILE;
		if ( file_exists( $check_file ) ) {
			@unlink( $check_file );
		}
	}
}