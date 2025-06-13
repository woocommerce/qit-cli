<?php

/*
namespace QIT_CLI_Tests\PreCommand\Configuration;

use QIT_CLI\App;
use QIT_CLI\Commands\QITCommand;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\PreCommand\Configuration\Merger\CLIInputMerger;
use QIT_CLI\PreCommand\Configuration\Parser\QitJsonParser;
use QIT_CLI\PreCommand\EnvInfoBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class CLIInputMergerTest extends TestCase {
	use MatchesSnapshots;

	private string $temp_dir = '/tmp/qit_cli_input_merger_test';
	private array $to_delete = [];

	protected function setUp(): void {
		parent::setUp();
		$this->delete_dir( $this->temp_dir );
		mkdir( $this->temp_dir, 0777, true );
	}

	protected function tearDown(): void {
		foreach ( $this->to_delete as $path ) {
			if ( file_exists( $path ) ) {
				unlink( $path );
			}
		}
		$this->delete_dir( $this->temp_dir );
		parent::tearDown();
	}

	public function test_version_overrides(): void {
		$config_file = $this->temp_dir . '/qit.json';
		$config      = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'awesome-plugin',
				'source' => [
					'type' => 'directory',
					'path' => $this->temp_dir . '/plugin',
				],
			],
			'environments' => [
				'default' => [
					'plugins'     => [ [ 'slug' => 'woocommerce', 'source' => [ 'type' => 'wporg' ] ] ],
					'wp_version'  => '6.0',
					'php_version' => '7.4',
					'woo_version' => '6.0.0',
				],
			],
		];
		mkdir( $this->temp_dir . '/plugin', 0777, true );
		file_put_contents( $config_file, json_encode( $config ) );
		$this->to_delete[] = $config_file;

		$cli_args = [
			'config'      => $config_file,
			'wp_version'  => '6.1',
			'php_version' => '8.0',
			'woo_version' => '8.0.0',
			'environment' => 'default',
		];

		$env_info = $this->run_command( $cli_args );
		$this->assertEquals( '6.1', $env_info['wp_version'] );
		$this->assertEquals( '8.0', $env_info['php_version'] );
		$this->assertEquals( '8.0.0', $env_info['woo_version'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_plugin_and_theme_overrides(): void {
		$config_file = $this->temp_dir . '/qit.json';
		$config      = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'awesome-plugin',
				'source' => [
					'type' => 'directory',
					'path' => $this->temp_dir . '/plugin',
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [ [ 'slug' => 'woocommerce', 'source' => [ 'type' => 'wporg' ] ] ],
					'themes'  => [ [ 'slug' => 'storefront', 'source' => [ 'type' => 'wporg' ] ] ],
				],
			],
		];
		mkdir( $this->temp_dir . '/plugin', 0777, true );
		file_put_contents( $config_file, json_encode( $config ) );
		$this->to_delete[] = $config_file;

		$cli_args = [
			'config'      => $config_file,
			'plugin'      => [ 'contact-form-7' ],
			'theme'       => [ 'twentytwentyone' ],
			'environment' => 'default',
		];

		$env_info = $this->run_command( $cli_args );
		$plugins  = array_map( fn( $p ) => is_array( $p ) ? $p['slug'] : $p->slug, $env_info['plugins'] );
		$themes   = array_map( fn( $t ) => is_array( $t ) ? $t['slug'] : $t->slug, $env_info['themes'] );
		$this->assertContains( 'woocommerce', $plugins );
		$this->assertContains( 'contact-form-7', $plugins );
		$this->assertContains( 'storefront', $themes );
		$this->assertContains( 'twentytwentyone', $themes );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_env_and_env_file_overrides(): void {
		$env_file = $this->temp_dir . '/test_' . uniqid() . '.env';
		file_put_contents( $env_file, "FILE_VAR=file\nSHARED_VAR=file" );
		$this->to_delete[] = $env_file;

		$config_file = $this->temp_dir . '/qit.json';
		$config      = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'awesome-plugin',
				'source' => [
					'type' => 'directory',
					'path' => $this->temp_dir . '/plugin',
				],
			],
			'environments' => [
				'default' => [
					'plugins'  => [ [ 'slug' => 'woocommerce', 'source' => [ 'type' => 'wporg' ] ] ],
					'env_vars' => [
						'CONFIG_VAR' => 'config',
						'SHARED_VAR' => 'config',
					],
				],
			],
		];
		mkdir( $this->temp_dir . '/plugin', 0777, true );
		file_put_contents( $config_file, json_encode( $config ) );
		$this->to_delete[] = $config_file;

		$cli_args = [
			'config'      => $config_file,
			'env'         => [ 'CLI_VAR=cli', 'SHARED_VAR=cli' ],
			'env_file'    => [ $env_file ],
			'environment' => 'default',
		];

		$env_info = $this->run_command( $cli_args );
		$this->assertArrayHasKey( 'env', $env_info );
		$this->assertEquals( 'config', $env_info['env']['CONFIG_VAR'] );
		$this->assertEquals( 'file', $env_info['env']['FILE_VAR'] );
		$this->assertEquals( 'cli', $env_info['env']['CLI_VAR'] );
		$this->assertEquals( 'cli', $env_info['env']['SHARED_VAR'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_null_values_preserve_config(): void {
		$config_file = $this->temp_dir . '/qit.json';
		$config      = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'awesome-plugin',
				'source' => [
					'type' => 'directory',
					'path' => $this->temp_dir . '/plugin',
				],
			],
			'environments' => [
				'default' => [
					'plugins'     => [ [ 'slug' => 'woocommerce', 'source' => [ 'type' => 'wporg' ] ] ],
					'wp_version'  => '6.0',
					'php_version' => '7.4',
				],
			],
		];
		mkdir( $this->temp_dir . '/plugin', 0777, true );
		file_put_contents( $config_file, json_encode( $config ) );
		$this->to_delete[] = $config_file;

		$cli_args = [
			'config'      => $config_file,
			'wp_version'  => null,
			'php_version' => '8.0',
			'environment' => 'default',
		];

		$env_info = $this->run_command( $cli_args );
		$this->assertEquals( '6.0', $env_info['wp_version'] );
		$this->assertEquals( '8.0', $env_info['php_version'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_extends_with_overrides(): void {
		$base_file   = $this->temp_dir . '/base.json';
		$base_config = [
			'environments' => [
				'default' => [
					'wp_version'  => '6.0',
					'php_version' => '7.4',
					'plugins'     => [ [ 'slug' => 'woocommerce', 'source' => [ 'type' => 'wporg' ] ] ],
				],
			],
		];
		file_put_contents( $base_file, json_encode( $base_config ) );
		$this->to_delete[] = $base_file;

		$config_file = $this->temp_dir . '/qit.json';
		$config      = [
			'extends'      => 'base.json',
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'awesome-plugin',
				'source' => [
					'type' => 'directory',
					'path' => $this->temp_dir . '/plugin',
				],
			],
			'environments' => [
				'default' => [
					'php_version' => '8.0',
				],
			],
		];
		mkdir( $this->temp_dir . '/plugin', 0777, true );
		file_put_contents( $config_file, json_encode( $config ) );
		$this->to_delete[] = $config_file;

		$cli_args = [
			'config'      => $config_file,
			'wp_version'  => '6.1',
			'plugin'      => [ 'contact-form-7' ],
			'environment' => 'default',
		];

		$env_info = $this->run_command( $cli_args );
		$this->assertEquals( '6.1', $env_info['wp_version'] );
		$this->assertEquals( '8.0', $env_info['php_version'] );
		$plugins = array_map( fn( $p ) => is_array( $p ) ? $p['slug'] : $p->slug, $env_info['plugins'] );
		$this->assertContains( 'woocommerce', $plugins );
		$this->assertContains( 'contact-form-7', $plugins );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	private function run_command( array $cli_args ): array {
		// Mock EnvInfoBuilder
		/** @var EnvInfoBuilder|MockObject $env_info_builder *
		$env_info_builder = $this->createMock( EnvInfoBuilder::class );
		$env_info         = new E2EEnvInfo();
		$env_info_builder->method( 'build_env_info' )->willReturn( $env_info );
		App::singleton( EnvInfoBuilder::class, fn() => $env_info_builder );

		// Mock CLIInputMerger to capture merged options
		/** @var CLIInputMerger|MockObject $input_merger *
		$input_merger = $this->createMock( CLIInputMerger::class );
		$input_merger->method( 'get_config_from_input' )->willReturnCallback( function ( $input, $config_section, $defaults, $pluralizable_keys ) use ( &$env_info ) {
			$merged = ( new CLIInputMerger() )->get_config_from_input( $input, $config_section, $defaults, $pluralizable_keys );
			// Populate env_info with merged values
			foreach ( $merged as $key => $value ) {
				if ( $key === 'plugins' || $key === 'themes' ) {
					$env_info[ $key ] = array_map( fn( $item ) => is_array( $item ) ? $item : [ 'slug' => $item ], $value );
				} elseif ( $key === 'env_vars' || $key === 'env' ) {
					$env_info['env'] = $value;
				} else {
					$env_info[ $key ] = $value;
				}
			}

			return $merged;
		} );
		App::singleton( CLIInputMerger::class, fn() => $input_merger );

		// Create a mock command
		$command = new class extends QITCommand {
			protected function configure(): void {
				parent::configure();
				$this->setName( 'env:up' ); // Command that needs environment
				$this->addOption( 'wp_version', null, InputOption::VALUE_OPTIONAL );
				$this->addOption( 'php_version', null, InputOption::VALUE_OPTIONAL );
				$this->addOption( 'woo_version', null, InputOption::VALUE_OPTIONAL );
				$this->addOption( 'plugin', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY );
				$this->addOption( 'theme', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY );
				$this->addOption( 'env', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY );
				$this->addOption( 'env_file', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY );
				$this->addOption( 'environment', null, InputOption::VALUE_OPTIONAL, 'Environment name', 'default' );
			}

			protected function doExecute( InputInterface $input, OutputInterface $output ): int {
				return Command::SUCCESS;
			}
		};

		$input  = new ArrayInput( $cli_args );
		$output = new BufferedOutput();
		$command->run( $input, $output );

		return (array) $env_info;
	}

	private function delete_dir( string $dir ): void {
		if ( ! is_dir( $dir ) ) {
			return;
		}
		$files = array_diff( scandir( $dir ), [ '.', '..' ] );
		foreach ( $files as $file ) {
			$path = "$dir/$file";
			if ( is_dir( $path ) ) {
				$this->delete_dir( $path );
			} else {
				unlink( $path );
			}
		}
		rmdir( $dir );
	}
}
*/