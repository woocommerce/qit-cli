<?php

use QIT_CLI\App;
use QIT_CLI\OptionReuseTrait;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

class OptionReuseTraitTest extends \QIT_CLI_Tests\QITTestCase {
	/** @var Command */
	protected $source_command;

	public function setUp(): void {
		parent::setUp();

		$this->source_command = new Command( 'test:option-reuse-source' );
		$this->source_command->addOption( 'flag', null, InputOption::VALUE_NONE, 'A flag option' );
		$this->source_command->addOption( 'required_value', 'r', InputOption::VALUE_REQUIRED, 'A required value option', 'the-default' );
		$this->source_command->addOption( 'optional_value', null, InputOption::VALUE_OPTIONAL, 'An optional value option', 'optional-default' );
		$this->source_command->addOption( 'array_value', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'An array value option', [ 'a', 'b' ] );
		$this->source_command->addOption( 'negatable_flag', null, InputOption::VALUE_NONE | InputOption::VALUE_NEGATABLE, 'A negatable flag option' );

		App::make( Application::class )->add( $this->source_command );
	}

	protected function make_consumer_command(): Command {
		return new class( 'test:option-reuse-consumer' ) extends Command {
			use OptionReuseTrait;

			public function reuse( string $command_name, string $option_name ): void {
				$this->reuseOption( $command_name, $option_name );
			}
		};
	}

	public function test_reused_options_behave_like_the_originals() {
		$consumer = $this->make_consumer_command();

		foreach ( array_keys( $this->source_command->getDefinition()->getOptions() ) as $option_name ) {
			$consumer->reuse( 'test:option-reuse-source', $option_name );
		}

		foreach ( $this->source_command->getDefinition()->getOptions() as $option_name => $original ) {
			$reused = $consumer->getDefinition()->getOption( $option_name );

			$this->assertSame( $original->acceptValue(), $reused->acceptValue(), "acceptValue mismatch for --$option_name" );
			$this->assertSame( $original->isValueRequired(), $reused->isValueRequired(), "isValueRequired mismatch for --$option_name" );
			$this->assertSame( $original->isValueOptional(), $reused->isValueOptional(), "isValueOptional mismatch for --$option_name" );
			$this->assertSame( $original->isArray(), $reused->isArray(), "isArray mismatch for --$option_name" );
			$this->assertSame( $original->isNegatable(), $reused->isNegatable(), "isNegatable mismatch for --$option_name" );
			$this->assertSame( $original->getDefault(), $reused->getDefault(), "getDefault mismatch for --$option_name" );
			$this->assertSame( $original->getShortcut(), $reused->getShortcut(), "getShortcut mismatch for --$option_name" );
			$this->assertSame( $original->getDescription(), $reused->getDescription(), "getDescription mismatch for --$option_name" );
		}
	}

	public function test_reusing_unknown_option_throws() {
		$consumer = $this->make_consumer_command();

		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Failed to reuse option "does_not_exist" from command "test:option-reuse-source"' );

		$consumer->reuse( 'test:option-reuse-source', 'does_not_exist' );
	}

	public function test_reusing_option_from_real_command() {
		// 'env:up' is the real source of reused options in RunE2ECommand.
		$consumer = $this->make_consumer_command();
		$consumer->reuse( 'env:up', 'php_version' );

		$original = App::make( Application::class )->find( 'env:up' )->getDefinition()->getOption( 'php_version' );
		$reused   = $consumer->getDefinition()->getOption( 'php_version' );

		$this->assertSame( $original->acceptValue(), $reused->acceptValue() );
		$this->assertSame( $original->isValueRequired(), $reused->isValueRequired() );
		$this->assertSame( $original->isArray(), $reused->isArray() );
		$this->assertSame( $original->getDefault(), $reused->getDefault() );
	}
}
