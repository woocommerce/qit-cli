<?php

namespace QIT_CLI_Tests;

use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\StringInput;

/**
 * Unit tests for the `--subpackage` option of the `run:e2e` QIT command.
 */
class RunE2ESubpackageOptionTest extends QITTestCase {

	/**
	 * Generate a {@see \Symfony\Component\Console\Input\StringInput} using the
	 * `run:e2e` command definition.
	 *
	 * @param string $cli The command line values, without the command name.
	 *
	 * @return StringInput The bound input.
	 */
	private function get_parsed_run_e2e_command_input( string $cli ): StringInput {
		$command = $GLOBALS['qit_application']->find( 'run:e2e' );

		$input = new StringInput( $cli );
		$input->bind( $command->getDefinition() );

		return $input;
	}

	/**
	 * Provide test cases for {@see test_subpackage_option_handling()}.
	 *
	 * @return array<string,array{cli:string,expected:array<string>|null}>
	 */
	public function subpackage_option_provider(): array {
		return [
			'single value is returned'                   => [
				'cli'      => '--subpackage woocommerce/my-subpackage',
				'expected' => [ 'woocommerce/my-subpackage' ],
			],
			'multiple values are returned'               => [
				'cli'      => '--subpackage woocommerce/sub-a --subpackage woocommerce/sub-b',
				'expected' => [ 'woocommerce/sub-a', 'woocommerce/sub-b' ],
			],
			'no value when option is not specified'      => [
				'cli'      => 'woocommerce',
				'expected' => [],
			],
			'option without value is rejected'           => [
				'cli'      => '--subpackage',
				'expected' => null,
			],
			'option without value before another option' => [
				'cli'      => '--subpackage --json',
				'expected' => null,
			],
		];
	}

	/**
	 * Test that the `--subpackage` option is handled correctly.
	 *
	 * @dataProvider subpackage_option_provider
	 *
	 * @param string             $cli      The command line input value, without the `run:e2e` command.
	 * @param array<string>|null $expected Expected option value, or null when the input should be rejected.
	 */
	public function test_subpackage_option_handling( string $cli, ?array $expected ): void {
		if ( null === $expected ) {
			$this->expectException( RuntimeException::class );
			$this->expectExceptionMessage( 'The "--subpackage" option requires a value.' );
		}

		$input = $this->get_parsed_run_e2e_command_input( $cli );

		$this->assertSame( $expected, $input->getOption( 'subpackage' ) );
	}
}
