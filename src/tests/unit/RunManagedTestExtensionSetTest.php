<?php

namespace QIT_CLI_Tests;

use QIT_CLI\Commands\ExtensionSetTrait;
use QIT_CLI\QITInput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Guards the locally-handled managed tests (run:activation, run:woo-api,
 * run:woo-e2e) against losing `--extension_set`.
 *
 * These commands were converted from Manager schema-driven commands (which
 * exposed --extension_set automatically) to static classes extending
 * RunE2ECommand. They are now in CreateRunCommands' $ignored_test_types, so
 * the option must be declared on each command and resolved CLI-side via
 * ExtensionSetTrait. run:woo-api and run:woo-e2e previously regressed here.
 */
class RunManagedTestExtensionSetTest extends QITTestCase {

	/**
	 * @return array<string,array{string}>
	 */
	public function extension_set_command_provider(): array {
		return [
			'activation' => [ 'run:activation' ],
			'woo-api'    => [ 'run:woo-api' ],
			'woo-e2e'    => [ 'run:woo-e2e' ],
		];
	}

	/**
	 * @dataProvider extension_set_command_provider
	 */
	public function test_command_exposes_extension_set_option( string $command_name ): void {
		$command = $GLOBALS['qit_application']->find( $command_name );

		$this->assertTrue(
			$command->getDefinition()->hasOption( 'extension_set' ),
			sprintf( '%s must expose the --extension_set option.', $command_name )
		);
	}

	public function test_resolves_extension_set_into_plugins(): void {
		$input  = $this->make_input( [ '--extension_set' => 'test-set' ] );
		$output = new BufferedOutput();

		$result = $this->make_resolver()->resolve( $input, $output );

		$this->assertNull( $result );
		$this->assertSame( [ 'wccom-plugin-4', 'wccom-plugin-5' ], $input->getOption( 'plugin' ) );
	}

	public function test_extension_set_merges_with_explicit_plugins_without_duplicates(): void {
		$input = $this->make_input( [
			'--extension_set' => 'test-set',
			'--plugin'        => [ 'my-plugin', 'wccom-plugin-4' ],
		] );

		$result = $this->make_resolver()->resolve( $input, new BufferedOutput() );

		$this->assertNull( $result );
		$this->assertSame(
			[ 'my-plugin', 'wccom-plugin-4', 'wccom-plugin-5' ],
			$input->getOption( 'plugin' )
		);
	}

	public function test_no_extension_set_leaves_plugins_untouched(): void {
		$input = $this->make_input( [ '--plugin' => [ 'my-plugin' ] ] );

		$result = $this->make_resolver()->resolve( $input, new BufferedOutput() );

		$this->assertNull( $result );
		$this->assertSame( [ 'my-plugin' ], $input->getOption( 'plugin' ) );
	}

	public function test_unknown_extension_set_returns_invalid(): void {
		$input  = $this->make_input( [ '--extension_set' => 'does-not-exist' ] );
		$output = new BufferedOutput();

		$result = $this->make_resolver()->resolve( $input, $output );

		$this->assertSame( Command::INVALID, $result );
		$this->assertStringContainsString( 'Unknown extension set "does-not-exist"', $output->fetch() );
	}

	/**
	 * A minimal object that exposes the trait's protected resolver for testing.
	 */
	private function make_resolver(): object {
		return new class() {
			use ExtensionSetTrait;

			public function resolve( QITInput $input, BufferedOutput $output ): ?int {
				return $this->resolve_extension_set( $input, $output );
			}
		};
	}

	/**
	 * @param array<string,mixed> $cli_options
	 */
	private function make_input( array $cli_options ): QITInput {
		$definition = new InputDefinition();
		$definition->addOption( new InputOption( 'extension_set', null, InputOption::VALUE_OPTIONAL ) );
		$definition->addOption( new InputOption( 'plugin', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, '', [] ) );

		return new QITInput( new ArrayInput( $cli_options, $definition ), [], 'e2e' );
	}
}
