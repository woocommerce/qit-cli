<?php

namespace QIT_CLI_Tests;

use Exception;
use FilesystemIterator;
use lucatume\DI52\Container;
use QIT_CLI\App;
use QIT_CLI\Commands\QITCommand;
use QIT_CLI\IO\Output;
use QIT_CLI\TestConfig;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\get_manager_url;

echo "Fetching latest sync.json from production QIT Manager... ";
require_once __DIR__ . '/data/pull-sync-json.php';
echo "Done.\n";

define( 'UNIT_TESTS', true );

function qit_tests_reset_config_dir() {
	exec( 'rm -rf /tmp/.woo-qit-tests' );

	if ( ! mkdir( '/tmp/.woo-qit-tests' ) ) {
		throw new RuntimeException( 'Could not create config dir for tests..' );
	}

	if ( ! mkdir( '/tmp/.woo-qit-tests/environments' ) ) {
		throw new RuntimeException( 'Could not create environments dir for tests.' );
	}

	if ( ! file_exists( __DIR__ . '/data/environments/e2e.zip' ) ) {
		throw new RuntimeException( 'Could not find e2e environment for tests.' );
	}

	if ( ! copy( __DIR__ . '/data/environments/e2e.zip', '/tmp/.woo-qit-tests/environments/e2e.zip' ) ) {
		throw new RuntimeException( 'Could not copy e2e environment for tests.' );
	}
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/helpers.php';

$verbose = false;

// Check if PHPUnit is running in verbose mode.
if ( isset( $_SERVER['argv'] ) && in_array( '--verbose', $_SERVER['argv'], true ) || in_array( '-v', $_SERVER['argv'], true ) ) {
	$verbose = true;
}

qit_tests_reset_config_dir();

putenv( sprintf( 'QIT_HOME=%s/.woo-qit-tests', sys_get_temp_dir() ) );

// Initialize DI container.
$container = new Container();
App::setContainer( $container );

// Temporary Output for pre-bootstrapping.
App::bind( Output::class, NullOutput::class );

// Mocks the response of a sync request with local data.
App::setVar( sprintf( 'mock_%s%s', get_manager_url(), '/wp-json/cd/v1/cli/sync' ), file_get_contents( __DIR__ . '/data/sync.json' ) );

/** @var Application $qit_application */
$GLOBALS['qit_application'] = require_once __DIR__ . '/../src/bootstrap.php';
$GLOBALS['qit_application']->setAutoExit( false );

/**
 * The bootstrap process will conditionally skip adding some commands,
 * such as "RemovePartner" command if there are no Partners added.
 *
 * For testing purposes, we register all available commands.
 *
 * For that, we loop over the Commands folder and add any Command that
 * is not registered yet.
 *
 * @var SplFileInfo $file
 * @var RecursiveDirectoryIterator $it
 */
$failed_to_build = [];
$it              = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( __DIR__ . '/../src/Commands', FilesystemIterator::SKIP_DOTS ) );
foreach ( $it as $file ) {
	if ( $file->isFile() && $file->getExtension() === 'php' && ! $file->isLink() ) {
		$content = file_get_contents( $file->getPathname() );

		$namespace = $class = null;

		// Match namespace and class from the file content
		if ( preg_match( '/namespace\s+([^;]+);/', $content, $matches ) ) {
			$namespace = $matches[1];
		}
		if ( preg_match( '/class\s+(\w+)/', $content, $matches ) ) {
			$class = $matches[1];
		}

		if ( is_null( $namespace ) || is_null( $class ) ) {
			if ( $verbose ) {
				echo "Skipping file without namespace or class: {$file->getPathname()}\n";
			}
			continue;
		}

		$fqdn = sprintf( '%s\\%s', $namespace, $class );

		if ( ! class_exists( $fqdn ) ) {
			if ( $verbose ) {
				echo "Skipping non-existing class: $fqdn\n";
			}
			continue;
		}

		if ( ! ( new ReflectionClass( $fqdn ) )->isSubclassOf( Command::class ) ) {
			if ( $verbose ) {
				echo "Skipping non-command class: $fqdn\n";
			}
			continue;
		}
		if ( ! ( new ReflectionClass( $fqdn ) )->isInstantiable() ) {
			if ( $verbose ) {
				echo "Skipping non-instantiable class: $fqdn\n";
			}
			continue;
		}
		if ( is_null( $fqdn::getDefaultName() ) ) {
			if ( $verbose ) {
				echo "Skipping command without default name: $fqdn\n";
			}
			continue;
		}

		if ( ! $GLOBALS['qit_application']->has( $fqdn::getDefaultName() ) ) {
			if ( $verbose ) {
				echo "Adding command: $fqdn\n";
			}
			try {
				$GLOBALS['qit_application']->add( App::make( $fqdn ) );
			} catch ( Exception $e ) {
				$failed_to_build[] = $fqdn;
			}
		} else {
			if ( $verbose ) {
				echo "Skipping already added command: $fqdn\n";
			}
		}
	}
}
if ( ! $verbose ) {
	// Mention they can use verbose to see which commands are being added.
	echo "Use --verbose to see which commands are being added.\n";
}
/*
 * Commands that use "reuseOption" might require a specific load order, which is respected
 * on our manual bootstrap.php, but not here.
 *
 * In that case, we "defer" any command that fails to add and try to add them again
 * after all other commands have been added.
 */
if ( ! empty( $failed_to_build ) ) {
	foreach ( $failed_to_build as $fqdn ) {
		echo "Adding deferred command: $fqdn\n";
		$GLOBALS['qit_application']->add( App::make( $fqdn ) );
	}
}

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
		     ->addOption( 'variant', null, InputOption::VALUE_OPTIONAL, 'Test variant', 'default' );
	}

	protected function doExecute( InputInterface $input, OutputInterface $output ): int {
		$variant = $input->getOption( 'variant' );
		try {
			$testConfigData = $this->config->get_test_config( 'foo', $variant );
		} catch ( \RuntimeException $e ) {
			$output->writeln( "<error>Test variant '$variant' not found for test type 'foo'.</error>" );

			return Command::FAILURE;
		}
		$testConfig = new FooTestConfig( $testConfigData );
		$param      = $input->getOption( 'param' );
		$output->writeln( "Running foo test variant: $variant with param: $param" );
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
		     ->addOption( 'variant', null, InputOption::VALUE_OPTIONAL, 'Test variant', 'default' );
	}

	protected function doExecute( InputInterface $input, OutputInterface $output ): int {
		$variant = $input->getOption( 'variant' );
		try {
			$testConfigData = $this->config->get_test_config( 'bar', $variant );
		} catch ( \RuntimeException $e ) {
			$output->writeln( "<error>Test variant '$variant' not found for test type 'bar'.</error>" );

			return Command::FAILURE;
		}
		$testConfig = new BarTestConfig( $testConfigData );
		$param      = $input->getOption( 'param' );
		$output->writeln( "Running bar test variant: $variant with param: $param" );
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
		     ->addOption( 'variant', null, InputOption::VALUE_OPTIONAL, 'Test variant', 'default' );
	}

	protected function doExecute( InputInterface $input, OutputInterface $output ): int {
		$variant = $input->getOption( 'variant' );
		try {
			$testConfigData = $this->config->get_test_config( 'baz', $variant );
		} catch ( \RuntimeException $e ) {
			$output->writeln( "<error>Test variant '$variant' not found for test type 'baz'.</error>" );

			return Command::FAILURE;
		}
		$testConfig = new BazTestConfig( $testConfigData );
		$param      = $input->getOption( 'param' );
		$output->writeln( "Running baz test variant: $variant with param: $param" );
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

	protected function doExecute( InputInterface $input, OutputInterface $output ): int {
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
			$output->writeln( 'Config: ' . json_encode( $this->config->getAll() ) );

			return Command::SUCCESS;
		}

		// Output config file path only if explicitly requested
		if ( $input->getOption( 'config' ) && ! $input->getOption( 'output-config' ) ) {
			$output->writeln( 'Config file: ' . $this->config->getConfigFile() );

			return Command::SUCCESS;
		}

		// Output all config data if requested
		if ( $input->getOption( 'output-config' ) ) {
			$output->writeln( 'Config: ' . json_encode( $this->config->getAll() ) );

			return Command::SUCCESS;
		}

		// Default: Output config data
		$output->writeln( 'Config: ' . json_encode( $this->config->getAll() ) );

		return Command::SUCCESS;
	}
}

define( 'UNIT_TESTS_BOOTSTRAPPED', true );