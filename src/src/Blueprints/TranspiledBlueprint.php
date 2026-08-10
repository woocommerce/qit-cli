<?php

namespace QIT_CLI\Blueprints;

/**
 * The result of transpiling a Blueprint: a QIT environment config block plus
 * the ordered shell commands that reproduce the Blueprint steps that cannot be
 * expressed declaratively.
 */
class TranspiledBlueprint {

	/** @var array<string, mixed> A qit.json "environments.<name>" block. */
	public array $env_config = [];

	/** @var array<int, array{command: string, description: string, tolerant: bool}> Ordered commands, executed inside the container. */
	public array $steps = [];

	/** @var string[] Non-fatal notes about lossy or skipped conversions. */
	public array $warnings = [];

	/** @var string[] Blueprint step names that have no QIT equivalent. */
	public array $unsupported = [];

	/** @var string|null The Blueprint landingPage, if any. */
	public ?string $landing_page = null;

	public function has_steps(): bool {
		return ! empty( $this->steps );
	}

	/**
	 * @param string $command     Shell command, executed in the WordPress container.
	 * @param string $description Human-readable label for the command.
	 * @param bool   $tolerant    Whether a non-zero exit should stop the remaining steps.
	 */
	public function add_step( string $command, string $description, bool $tolerant = false ): void {
		$this->steps[] = [
			'command'     => $command,
			'description' => $description,
			'tolerant'    => $tolerant,
		];
	}

	public function add_warning( string $warning ): void {
		if ( ! in_array( $warning, $this->warnings, true ) ) {
			$this->warnings[] = $warning;
		}
	}

	public function add_unsupported( string $step, string $reason ): void {
		if ( ! in_array( $step, $this->unsupported, true ) ) {
			$this->unsupported[] = $step;
		}
		$this->add_warning( sprintf( 'Skipped unsupported step "%s": %s', $step, $reason ) );
	}

	/**
	 * The Blueprint expressed as a qit.json fragment.
	 *
	 * @param string $env_name Name of the environment block.
	 *
	 * @return array<string, mixed>
	 */
	public function to_qit_json( string $env_name = 'default' ): array {
		return [
			'$schema'      => 'https://raw.githubusercontent.com/woocommerce/qit-cli/trunk/src/src/PreCommand/Schemas/qit-schema.json',
			'environments' => [
				$env_name => $this->env_config,
			],
		];
	}

	/**
	 * Materialise the steps as a QIT utility package so the existing package
	 * phase runner executes them right after the environment boots.
	 *
	 * @param string $dir Directory to write the package into. Created if missing.
	 *
	 * @return string The package directory.
	 */
	public function write_utility_package( string $dir ): string {
		if ( ! is_dir( $dir ) && ! mkdir( $dir, 0755, true ) && ! is_dir( $dir ) ) {
			throw new BlueprintException( sprintf( 'Could not create Blueprint package directory: %s', $dir ) );
		}

		$commands = [];

		foreach ( $this->steps as $step ) {
			$commands[] = [
				'command'           => $step['command'],
				'runs_on'           => 'docker',
				'timeout'           => 600,
				// WP-CLI reports "could not update" when WordPress considers a value
				// unchanged. Playground ignores that, and one such option must not
				// abort the rest of the Blueprint.
				'continue_on_error' => ! empty( $step['tolerant'] ),
			];
		}

		$manifest = [
			'package'      => 'blueprint/steps',
			'package_type' => 'utility',
			'description'  => 'Steps transpiled from a WordPress Playground Blueprint.',
			'test'         => [
				'phases' => [
					'globalSetup' => $commands,
				],
			],
		];

		$written = file_put_contents(
			$dir . '/qit-test.json',
			json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . "\n"
		);

		if ( $written === false ) {
			throw new BlueprintException( sprintf( 'Could not write Blueprint package manifest to %s', $dir ) );
		}

		return $dir;
	}
}
