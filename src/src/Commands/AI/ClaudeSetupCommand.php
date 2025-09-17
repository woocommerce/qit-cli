<?php

namespace QIT_CLI\Commands\AI;

use QIT_CLI\App;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class ClaudeSetupCommand extends Command {
	protected static $defaultName = 'ai:claude-setup';

	protected function configure() {
		$this
			->setDescription( 'Set up QIT context for Claude Code integration' )
			->setHelp( 'This command creates a CLAUDE.qit.md file with QIT documentation and adds it to CLAUDE.md for Claude Code integration.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$cwd = getcwd();
		$claude_qit_path = $cwd . '/CLAUDE.qit.md';
		$claude_md_path = $cwd . '/CLAUDE.md';

		// Generate the current content to check if update is needed
		$new_content = $this->generate_claude_qit_content();
		$new_checksum = md5( $new_content );

		// Check if file exists and if it's up to date
		if ( file_exists( $claude_qit_path ) ) {
			$existing_checksum = md5_file( $claude_qit_path );

			// Compare with what would be generated now
			if ( $existing_checksum !== $new_checksum ) {
				$output->writeln( '<comment>⚠️  CLAUDE.qit.md is out of date.</comment>' );
				$output->writeln( '<comment>The QIT documentation has been updated. Run `qit ai:claude-setup` to get the latest version.</comment>' );
				$output->writeln( '' );
			}
		}

		$output->writeln( 'Setting up QIT context for Claude Code...' );

		// Content already generated above
		$content = $new_content;

		// Write CLAUDE.qit.md
		if ( file_put_contents( $claude_qit_path, $content ) === false ) {
			$output->writeln( '<error>Failed to create CLAUDE.qit.md</error>' );
			return Command::FAILURE;
		}
		$output->writeln( '<info>✓</info> Created CLAUDE.qit.md' );

		// Handle CLAUDE.md
		if ( file_exists( $claude_md_path ) ) {
			$claude_content = file_get_contents( $claude_md_path );

			// Check if already imported
			if ( strpos( $claude_content, '@CLAUDE.qit.md' ) !== false ) {
				$output->writeln( '<info>✓</info> CLAUDE.md already imports CLAUDE.qit.md' );
			} else {
				// Add import at the beginning of CLAUDE.md
				$updated_content = "@CLAUDE.qit.md\n\n" . $claude_content;
				if ( file_put_contents( $claude_md_path, $updated_content ) === false ) {
					$output->writeln( '<error>Failed to update CLAUDE.md</error>' );
					return Command::FAILURE;
				}
				$output->writeln( '<info>✓</info> Added @CLAUDE.qit.md import to CLAUDE.md' );
			}
		} else {
			// Create CLAUDE.md with just the import
			$content = "@CLAUDE.qit.md\n\n# Project Instructions\n\n(Add your project-specific instructions here)\n";
			if ( file_put_contents( $claude_md_path, $content ) === false ) {
				$output->writeln( '<error>Failed to create CLAUDE.md</error>' );
				return Command::FAILURE;
			}
			$output->writeln( '<info>✓</info> Created CLAUDE.md with QIT import' );
		}

		$output->writeln( '' );
		$output->writeln( '<fg=green>Claude Code will now understand QIT commands!</>' );
		$output->writeln( '' );
		$output->writeln( 'To remove QIT support later:' );
		$output->writeln( '  1. Remove the @CLAUDE.qit.md line from CLAUDE.md' );
		$output->writeln( '  2. Delete the CLAUDE.qit.md file' );
		$output->writeln( '' );
		$output->writeln( '<comment>💡 Tip: Commit these files to git so your team gets the context automatically!</comment>' );

		return Command::SUCCESS;
	}

	/**
	 * Generate the content for CLAUDE.qit.md
	 */
	private function generate_claude_qit_content(): string {
		// Load the template
		$template_path = __DIR__ . '/claude/CLAUDE.qit.md.keep';
		if ( ! file_exists( $template_path ) ) {
			throw new \RuntimeException( 'CLAUDE.qit.md template not found' );
		}

		$template = file_get_contents( $template_path );

		// Get dynamic content
		$test_package_schema = $this->get_test_package_schema();
		$qit_config_schema = $this->get_qit_config_schema();
		$command_list = $this->get_command_list();

		// Replace placeholders
		$replacements = [
			'{{COMMAND_HELP_OUTPUTS}}' => '<!-- Use qit <command> --help for detailed command information -->',
			'{{TEST_PACKAGE_SCHEMA}}' => $test_package_schema,
			'{{QIT_CONFIG_SCHEMA}}' => $qit_config_schema,
			'{{COMMAND_LIST}}' => $command_list,
		];

		return str_replace( array_keys( $replacements ), array_values( $replacements ), $template );
	}



	/**
	 * Get test package schema
	 */
	private function get_test_package_schema(): string {
		$schema_file = __DIR__ . '/../../PreCommand/Schemas/test-package-manifest-schema.json';
		if ( ! file_exists( $schema_file ) ) {
			return "```json\n(Schema not available)\n```";
		}

		$schema = file_get_contents( $schema_file );
		$json = json_decode( $schema, true );

		// Simplify for readability
		if ( $json && isset( $json['properties'] ) ) {
			$simplified = [
				'description' => $json['description'] ?? 'Test package configuration',
				'properties' => array_map( function( $prop ) {
					return [
						'type' => $prop['type'] ?? 'unknown',
						'description' => $prop['description'] ?? '',
						'required' => in_array( $prop, $json['required'] ?? [], true ),
					];
				}, $json['properties'] ),
			];
			return "```json\n" . json_encode( $simplified, JSON_PRETTY_PRINT ) . "\n```";
		}

		// If we can't simplify, just show first 1000 chars
		if ( strlen( $schema ) > 1000 ) {
			$schema = substr( $schema, 0, 1000 ) . '... (truncated)';
		}
		return "```json\n$schema\n```";
	}

	/**
	 * Get command list from qit list
	 */
	private function get_command_list(): string {
		$application = App::make( Application::class );
		$commands = $application->all();

		// Group commands by namespace
		$grouped = [];

		foreach ( $commands as $name => $command ) {
			// Skip hidden and internal commands
			if ( $command->isHidden() || strpos( $name, '_' ) === 0 ) {
				continue;
			}

			$description = $command->getDescription();

			// Determine group
			if ( strpos( $name, ':' ) !== false ) {
				list( $group ) = explode( ':', $name, 2 );
			} else {
				$group = 'general';
			}

			if ( ! isset( $grouped[$group] ) ) {
				$grouped[$group] = [];
			}

			$grouped[$group][] = sprintf( "  %-30s %s", $name, $description );
		}

		// Sort groups
		ksort( $grouped );

		// Build output
		$output = [];
		foreach ( $grouped as $group => $group_commands ) {
			$group_title = ucfirst( str_replace( '-', ' ', $group ) );
			$output[] = "### $group_title Commands\n";
			$output[] = "```";
			$output[] = implode( "\n", $group_commands );
			$output[] = "```\n";
		}

		return implode( "\n", $output );
	}

	/**
	 * Get QIT config schema
	 */
	private function get_qit_config_schema(): string {
		$schema_file = __DIR__ . '/../../PreCommand/Schemas/qit-schema.json';
		if ( ! file_exists( $schema_file ) ) {
			return "```json\n(Schema not available)\n```";
		}

		$schema = file_get_contents( $schema_file );
		$json = json_decode( $schema, true );

		// Simplify for readability
		if ( $json && isset( $json['properties'] ) ) {
			$simplified = [
				'description' => $json['description'] ?? 'QIT configuration for extensions',
				'properties' => array_map( function( $prop ) {
					return [
						'type' => $prop['type'] ?? 'unknown',
						'description' => $prop['description'] ?? '',
					];
				}, $json['properties'] ),
			];
			return "```json\n" . json_encode( $simplified, JSON_PRETTY_PRINT ) . "\n```";
		}

		// If we can't simplify, just show first 1000 chars
		if ( strlen( $schema ) > 1000 ) {
			$schema = substr( $schema, 0, 1000 ) . '... (truncated)';
		}
		return "```json\n$schema\n```";
	}

}