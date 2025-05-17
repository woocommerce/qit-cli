<?php

namespace QIT_CLI_Tests;

use QIT_CLI\Commands\QITCommand;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

class QITCommandTest extends QITTestCase {
	use MatchesSnapshots;

	public function tearDown(): void {
		// Clean up temporary files
		foreach ( [ 'qit.json', 'custom.json' ] as $file ) {
			if ( file_exists( $file ) ) {
				unlink( $file );
			}
		}
		parent::tearDown();
	}

	public function test_loads_config_successfully() {
		file_put_contents( 'qit.json', json_encode( [ 'key' => 'value' ] ) );

		$command = new class extends QITCommand {
			protected function configure(): void {
				parent::configure();
				$this->setName( 'test:config' );
			}

			protected function doExecute( InputInterface $input, OutputInterface $output ): int {
				$output->writeln( 'Config loaded: ' . json_encode( $this->config->getAll() ) );

				return Command::SUCCESS;
			}
		};

		$tester = new CommandTester( $command );
		$tester->execute( [] );

		$this->assertStringContainsString( 'Config loaded: {"key":"value"}', $tester->getDisplay() );
		$this->assertEquals( Command::SUCCESS, $tester->getStatusCode() );
	}

	public function test_handles_missing_config_file() {
		$command = new class extends QITCommand {
			protected function configure(): void {
				parent::configure();
				$this->setName( 'test:config' );
			}

			protected function doExecute( InputInterface $input, OutputInterface $output ): int {
				$output->writeln( 'Config: ' . json_encode( $this->config->getAll() ) );

				return Command::SUCCESS;
			}
		};

		$tester = new CommandTester( $command );
		$tester->execute( [] );

		$this->assertStringContainsString( 'Config: []', $tester->getDisplay() );
		$this->assertEquals( Command::SUCCESS, $tester->getStatusCode() );
	}

	public function test_overridable_inputs() {
		file_put_contents( 'qit.json', json_encode( [ 'setting' => 'default' ] ) );

		$command = new class extends QITCommand {
			protected function configure(): void {
				parent::configure();
				$this->setName( 'test:config' );
				$this->addOption( 'setting', null, InputOption::VALUE_OPTIONAL, 'Override setting', 'default' );
			}

			protected function doExecute( InputInterface $input, OutputInterface $output ): int {
				$setting = $input->getOption( 'setting' );
				$output->writeln( "Setting: $setting" );

				return Command::SUCCESS;
			}
		};

		$tester = new CommandTester( $command );
		$tester->execute( [ '--setting' => 'overridden' ] );
		$this->assertStringContainsString( 'Setting: overridden', $tester->getDisplay() );

		$tester->execute( [] );
		$this->assertStringContainsString( 'Setting: default', $tester->getDisplay() );
	}

	public function test_custom_config_file() {
		file_put_contents( 'custom.json', json_encode( [ 'key' => 'custom_value' ] ) );

		$command = new class extends QITCommand {
			protected function configure(): void {
				parent::configure();
				$this->setName( 'test:config' );
			}

			protected function doExecute( InputInterface $input, OutputInterface $output ): int {
				$output->writeln( 'Config file: ' . $this->config->getConfigFile() );
				$output->writeln( 'Config: ' . json_encode( $this->config->getAll() ) );

				return Command::SUCCESS;
			}
		};

		$tester = new CommandTester( $command );
		$tester->execute( [ '--config' => 'custom.json' ] );
		$this->assertStringContainsString( 'Config file: custom.json', $tester->getDisplay() );
		$this->assertStringContainsString( 'Config: {"key":"custom_value"}', $tester->getDisplay() );
		$this->assertEquals( Command::SUCCESS, $tester->getStatusCode() );
	}

	public function test_invalid_json() {
		file_put_contents( 'qit.json', '{invalid json}' );

		$command = new class extends QITCommand {
			protected function configure(): void {
				parent::configure();
				$this->setName( 'test:config' );
			}

			protected function doExecute( InputInterface $input, OutputInterface $output ): int {
				return Command::SUCCESS; // Should not reach here
			}
		};

		$tester = new CommandTester( $command );
		$tester->execute( [] );

		$this->assertStringContainsString( 'Invalid qit.json format. Must be a JSON object.', $tester->getDisplay() );
		$this->assertEquals( Command::FAILURE, $tester->getStatusCode() );
	}
}