<?php

namespace QIT_CLI_Tests;

use QIT_CLI\App;
use QIT_CLI\Commands\QITCommand;
use QIT_CLI\QITConfig;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

class QITCommandTest extends QITTestCase {
	use MatchesSnapshots;

	protected Application $application;

	public function setUp(): void {
		parent::setUp();
		$this->application = new Application();
		$this->application->add( new FooTestCommand() );
		$this->application->add( App::make( \QIT_CLI\Commands\CustomTests\RunE2ECommand::class ) );
	}

	public function tearDown(): void {
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
		$command->setApplication( $this->application );
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
		$command->setApplication( $this->application );
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
		$command->setApplication( $this->application );
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
		$command->setApplication( $this->application );
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
				return Command::SUCCESS;
			}
		};
		$command->setApplication( $this->application );
		$tester = new CommandTester( $command );
		$tester->execute( [] );
		$this->assertMatchesTextSnapshot( $tester->getDisplay() );
		$this->assertEquals( Command::FAILURE, $tester->getStatusCode() );
	}

	public function test_environment_inheritance() {
		file_put_contents( 'qit.json', json_encode( [
			'environments' => [
				'base'   => [ 'php_version' => '8.2', 'wordpress_version' => 'stable', 'plugins' => [ 'woocommerce' ] ],
				'legacy' => [ 'extends' => 'base', 'php_version' => '7.4', 'wordpress_version' => '6.1' ]
			]
		] ) );
		$command = new class extends QITCommand {
			protected function configure(): void {
				parent::configure();
				$this->setName( 'test:env' );
			}

			protected function doExecute( InputInterface $input, OutputInterface $output ): int {
				try {
					$legacy_env = $this->config->get_environment( 'legacy' );
					$output->writeln( json_encode( $legacy_env ) );
				} catch ( \RuntimeException $e ) {
					$output->writeln( "<error>{$e->getMessage()}</error>" );

					return Command::FAILURE;
				}

				return Command::SUCCESS;
			}
		};
		$command->setApplication( $this->application );
		$tester = new CommandTester( $command );
		$tester->execute( [] );
		$expected = json_encode( [
			'php_version'       => '7.4',
			'wordpress_version' => '6.1',
			'plugins'           => [ 'woocommerce' ]
		] );
		$this->assertStringContainsString( $expected, $tester->getDisplay() );
		$this->assertEquals( Command::SUCCESS, $tester->getStatusCode() );
	}

	public function test_missing_environment() {
		file_put_contents( 'qit.json', json_encode( [] ) );
		$command = new class extends QITCommand {
			protected function configure(): void {
				parent::configure();
				$this->setName( 'test:env' );
			}

			protected function doExecute( InputInterface $input, OutputInterface $output ): int {
				try {
					$this->config->get_environment( 'non_existing' );
					$output->writeln( 'Should not reach here' );
				} catch ( \RuntimeException $e ) {
					$output->writeln( "<error>{$e->getMessage()}</error>" );

					return Command::FAILURE;
				}

				return Command::SUCCESS;
			}
		};
		$command->setApplication( $this->application );
		$tester = new CommandTester( $command );
		$tester->execute( [] );
		$this->assertMatchesTextSnapshot( $tester->getDisplay() );
		$this->assertEquals( Command::FAILURE, $tester->getStatusCode() );
	}

	public function test_custom_test_package_inheritance() {
		file_put_contents( 'qit.json', json_encode( [
			'custom_test_packages' => [
				'default' => [ 'root_path' => './tests/e2e', 'test_command' => 'npx playwright test' ],
				'basic'   => [ 'extends' => 'default', 'test_command' => 'npx playwright test --grep @basic' ]
			]
		] ) );
		$command = new class extends QITCommand {
			protected function configure(): void {
				parent::configure();
				$this->setName( 'test:package' );
			}

			protected function doExecute( InputInterface $input, OutputInterface $output ): int {
				try {
					$basic_package = $this->config->get_custom_test_package( 'basic' );
					$output->writeln( json_encode( $basic_package ) );
				} catch ( \RuntimeException $e ) {
					$output->writeln( "<error>{$e->getMessage()}</error>" );

					return Command::FAILURE;
				}

				return Command::SUCCESS;
			}
		};
		$command->setApplication( $this->application );
		$tester = new CommandTester( $command );
		$tester->execute( [] );
		$expected = json_encode( [
			'root_path'    => './tests/e2e',
			'test_command' => 'npx playwright test --grep @basic'
		] );
		$this->assertStringContainsString( $expected, $tester->getDisplay() );
		$this->assertEquals( Command::SUCCESS, $tester->getStatusCode() );
	}

	public function test_missing_custom_test_package() {
		file_put_contents( 'qit.json', json_encode( [] ) );
		$command = new class extends QITCommand {
			protected function configure(): void {
				parent::configure();
				$this->setName( 'test:package' );
			}

			protected function doExecute( InputInterface $input, OutputInterface $output ): int {
				try {
					$this->config->get_custom_test_package( 'non_existing' );
					$output->writeln( 'Should not reach here' );
				} catch ( \RuntimeException $e ) {
					$output->writeln( "<error>{$e->getMessage()}</error>" );

					return Command::FAILURE;
				}

				return Command::SUCCESS;
			}
		};
		$command->setApplication( $this->application );
		$tester = new CommandTester( $command );
		$tester->execute( [] );
		$this->assertMatchesTextSnapshot( $tester->getDisplay() );
		$this->assertEquals( Command::FAILURE, $tester->getStatusCode() );
	}

	public function test_get_group_tests() {
		file_put_contents( 'qit.json', json_encode( [
			'tests'  => [
				'foo' => [
					'default' => [ 'param' => 'value' ]
				],
				'e2e' => [
					'default' => [ 'env' => 'base' ]
				]
			],
			'groups' => [
				'pre_release' => [ 'foo:default', 'e2e:default' ]
			]
		] ) );
		$config      = new QITConfig( 'qit.json', $this->application );
		$group_tests = $config->get_group_tests( 'pre_release' );
		$this->assertCount( 2, $group_tests );
		$this->assertEquals( 'foo', $group_tests[0]['type'] );
		$this->assertEquals( 'default', $group_tests[0]['variant'] );
		$this->assertEquals( [ 'param' => 'value' ], $group_tests[0]['config'] );
		$this->assertEquals( 'e2e', $group_tests[1]['type'] );
		$this->assertEquals( 'default', $group_tests[1]['variant'] );
		$this->assertEquals( [ 'env' => 'base' ], $group_tests[1]['config'] );
	}

	public function test_get_group_tests_invalid_ref() {
		file_put_contents( 'qit.json', json_encode( [
			'tests'  => [
				'foo' => [
					'default' => [ 'param' => 'value' ]
				]
			],
			'groups' => [
				'invalid' => [ 'foo:invalid' ]
			]
		] ) );
		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( "Test variant 'invalid' from reference 'foo:invalid' in group 'invalid' not found in tests configuration." );
		new QITConfig( 'qit.json', $this->application );
	}

	public function test_get_test_matrix() {
		file_put_contents( 'qit.json', json_encode( [
			'tests' => [
				'e2e' => [
					'default' => [
						'test_matrix' => [
							[ 'slug' => 'woocommerce', 'test_package' => 'e2e:basic' ],
							[ 'slug' => 'my-plugin', 'test_package' => './tests/e2e' ]
						]
					]
				]
			]
		] ) );
		$config = new QITConfig( 'qit.json', $this->application );
		$matrix = $config->get_test_matrix( 'e2e', 'default' );
		$this->assertEquals( [
			[ 'slug' => 'woocommerce', 'test_package' => 'e2e:basic' ],
			[ 'slug' => 'my-plugin', 'test_package' => './tests/e2e' ]
		], $matrix );
	}

	public function test_get_test_matrix_invalid() {
		file_put_contents( 'qit.json', json_encode( [
			'tests' => [
				'e2e' => [
					'default' => [
						'test_matrix' => 'not_an_array'
					]
				]
			]
		] ) );
		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( "Test_matrix in 'e2e:default' must be an array." );
		new QITConfig( 'qit.json', $this->application );
	}

	public function test_invalid_test_profile_key() {
		file_put_contents( 'qit.json', json_encode( [
			'tests' => [
				'foo' => [
					'default' => [ 'invalid_key' => 'value' ]
				]
			]
		] ) );
		$command = new FooTestCommand();
		$command->setApplication( $this->application );
		$tester = new CommandTester( $command );
		$tester->execute( [] );
		$this->assertMatchesTextSnapshot( $tester->getDisplay() );
		$this->assertEquals( Command::FAILURE, $tester->getStatusCode() );
	}
}