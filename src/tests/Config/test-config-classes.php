<?php

namespace QIT_CLI_Tests;

use QIT_CLI\Commands\QITCommand;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\TestConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FooTestConfig extends TestConfig {
	public function getTestType(): string {
		return 'foo';
	}
}

class FooTestCommand extends QITCommand {
	protected function configure(): void {
		parent::configure();
		$this->setName( 'run:foo' )
		     ->setDescription( 'Run a foo test' )
		     ->addOption( 'param', null, InputOption::VALUE_OPTIONAL, 'Test parameter', 'default' )
		     ->addOption( 'profile', null, InputOption::VALUE_OPTIONAL, 'Test profile', 'default' );
	}

	protected function doExecute( InputInterface $input, OutputInterface $output, ?EnvInfo $env_info ): int {
		$profile = $input->getOption( 'profile' );
		try {
			$testConfigData = $this->config->get_test_config( 'foo', $profile );
		} catch ( \RuntimeException $e ) {
			$output->writeln( "<error>Test profile '$profile' not found for test type 'foo'.</error>" );

			return Command::FAILURE;
		}
		$testConfig = new \QIT_CLI_Tests\FooTestConfig( $testConfigData );
		$param      = $input->getOption( 'param' );
		$output->writeln( "Running foo test profile: $profile with param: $param" );
		$output->writeln( 'Test config: ' . json_encode( $testConfig->getConfig() ) );

		return Command::SUCCESS;
	}
}

class BarTestConfig extends TestConfig {
	public function getTestType(): string {
		return 'bar';
	}
}

class BarTestCommand extends QITCommand {
	protected function configure(): void {
		parent::configure();
		$this->setName( 'run:bar' )
		     ->setDescription( 'Run a bar test' )
		     ->addOption( 'param', null, InputOption::VALUE_OPTIONAL, 'Test parameter', 'default' )
		     ->addOption( 'profile', null, InputOption::VALUE_OPTIONAL, 'Test profile', 'default' );
	}

	protected function doExecute( InputInterface $input, OutputInterface $output, ?EnvInfo $env_info ): int {
		$profile = $input->getOption( 'profile' );
		try {
			$testConfigData = $this->config->get_test_config( 'bar', $profile );
		} catch ( \RuntimeException $e ) {
			$output->writeln( "<error>Test profile '$profile' not found for test type 'bar'.</error>" );

			return Command::FAILURE;
		}
		$testConfig = new \QIT_CLI_Tests\BarTestConfig( $testConfigData );
		$param      = $input->getOption( 'param' );
		$output->writeln( "Running bar test profile: $profile with param: $param" );
		$output->writeln( 'Test config: ' . json_encode( $testConfig->getConfig() ) );

		return Command::SUCCESS;
	}
}

class BazTestConfig extends TestConfig {
	public function getTestType(): string {
		return 'baz';
	}
}

class BazTestCommand extends QITCommand {
	protected function configure(): void {
		parent::configure();
		$this->setName( 'run:baz' )
		     ->setDescription( 'Run a baz test' )
		     ->addOption( 'param', null, InputOption::VALUE_OPTIONAL, 'Test parameter', 'default' )
		     ->addOption( 'profile', null, InputOption::VALUE_OPTIONAL, 'Test profile', 'default' );
	}

	protected function doExecute( InputInterface $input, OutputInterface $output, ?EnvInfo $env_info ): int {
		$profile = $input->getOption( 'profile' );
		try {
			$testConfigData = $this->config->get_test_config( 'baz', $profile );
		} catch ( \RuntimeException $e ) {
			$output->writeln( "<error>Test profile '$profile' not found for test type 'baz'.</error>" );

			return Command::FAILURE;
		}
		$testConfig = new \QIT_CLI_Tests\BazTestConfig( $testConfigData );
		$param      = $input->getOption( 'param' );
		$output->writeln( "Running baz test profile: $profile with param: $param" );
		$output->writeln( 'Test config: ' . json_encode( $testConfig->getConfig() ) );

		return Command::SUCCESS;
	}
}

class TestConfigCommand extends QITCommand {
	protected function configure(): void {
		parent::configure();
		$this->setName( 'test:config' )
		     ->setDescription( 'Test QITConfig functionality' )
		     ->addOption( 'setting', null, InputOption::VALUE_OPTIONAL, 'Override setting' )
		     ->addOption( 'output-config', null, InputOption::VALUE_NONE, 'Output all config data' )
		     ->addOption( 'get-environment', null, InputOption::VALUE_OPTIONAL, 'Get specific environment' )
		     ->addOption( 'get-package', null, InputOption::VALUE_OPTIONAL, 'Get specific custom test package' );
	}

	protected function doExecute( InputInterface $input, OutputInterface $output, ?EnvInfo $env_info ): int {
		// Get specific environment
		if ( $env = $input->getOption( 'get-environment' ) ) {
			try {
				$output->writeln( json_encode( $this->config->get_environment( $env ) ) );

				return Command::SUCCESS;
			} catch ( \RuntimeException $e ) {
				$output->writeln( "<error>{$e->getMessage()}</error>" );

				return Command::FAILURE;
			}
		}

		// Get specific custom test package
		if ( $package = $input->getOption( 'get-package' ) ) {
			try {
				$output->writeln( json_encode( $this->config->get_custom_test_package( $package ) ) );

				return Command::SUCCESS;
			} catch ( \RuntimeException $e ) {
				$output->writeln( "<error>{$e->getMessage()}</error>" );

				return Command::FAILURE;
			}
		}

		// Output setting value if provided
		if ( $input->hasOption( 'setting' ) && $input->getOption( 'setting' ) !== null ) {
			$output->writeln( 'Setting: ' . $input->getOption( 'setting' ) );

			return Command::SUCCESS;
		}

		// Handle custom config file with output-config
		if ( $input->getOption( 'config' ) && $input->getOption( 'output-config' ) ) {
			$output->writeln( 'Config: ' . json_encode( $this->config->get_all() ) );

			return Command::SUCCESS;
		}

		// Output config file path only if explicitly requested
		if ( $input->getOption( 'config' ) && ! $input->getOption( 'output-config' ) ) {
			$output->writeln( 'Config file: ' . $this->config->get_config_file() );

			return Command::SUCCESS;
		}

		// Output all config data if requested
		if ( $input->getOption( 'output-config' ) ) {
			$output->writeln( 'Config: ' . json_encode( $this->config->get_all() ) );

			return Command::SUCCESS;
		}

		// Default: Output config data
		$output->writeln( 'Config: ' . json_encode( $this->config->get_all() ) );

		return Command::SUCCESS;
	}
}