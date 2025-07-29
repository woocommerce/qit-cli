<?php

namespace QIT_CLI\Tests\Unit\PreCommand;

use PHPUnit\Framework\TestCase;
use QIT_CLI\PreCommand\TinyPreCommand;
use QIT_CLI\PreCommand\Configuration\ConfigMerger;
use QIT_CLI\PreCommand\Configuration\ConfigurationResolver;
use QIT_CLI\PreCommand\Configuration\ResolvedConfiguration;
use QIT_CLI\PreCommand\Configuration\Parser\QitJsonParser;
use QIT_CLI\PreCommand\Extensions\ExtensionResolver;
use QIT_CLI\PreCommand\Extensions\ResolvedExtensions;
use QIT_CLI\PreCommand\Download\TestPackageDownloader;
use QIT_CLI\Cache;
use QIT_CLI\PreCommand\PrecommandEarlyReturn;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class TinyPreCommandTest extends TestCase {

	private ConfigMerger $merger;

	protected function setUp(): void {
		$this->merger = new ConfigMerger();
	}

	/**
	 * Create a real ConfigurationResolver with minimal dependencies.
	 */
	private function createRealResolver(array $config = []): ConfigurationResolver {
		// Create temporary config file
		$tmp = sys_get_temp_dir() . '/qit_' . uniqid() . '.json';
		file_put_contents($tmp, json_encode($config));

		// Create minimal dependencies
		$parser = new QitJsonParser();
		$extensionResolver = $this->createMock(ExtensionResolver::class);
		$packageDownloader = $this->createMock(TestPackageDownloader::class);
		$cache = $this->createMock(Cache::class);
		$output = new NullOutput();

		// Configure extension resolver to return empty results
		$resolvedExtensions = new ResolvedExtensions();
		$extensionResolver->method('resolve')->willReturn($resolvedExtensions);

		return new ConfigurationResolver($parser, $extensionResolver, $packageDownloader, $cache, $output);
	}

	/**
	 * Create a real ResolvedConfiguration with test data.
	 */
	private function createRealResolvedConfig(array $environments = [], array $testTypes = []): ResolvedConfiguration {
		$config = new ResolvedConfiguration([]);
		$config->environments = $environments;
		$config->test_types = $testTypes;
		return $config;
	}

	/**
	 * Test that configuration is only parsed once (lazy memoisation).
	 */
	public function test_lazy_memoisation(): void {
		// Follow the issue description example exactly
		$input = new ArrayInput(['--php' => '8.1', '--plugin' => ['woocommerce']]);

		$tmp = sys_get_temp_dir() . '/qit_' . uniqid() . '.json';
		file_put_contents($tmp, json_encode(['environments' => ['default' => ['wp' => '6.0']]]));

		$resolver = new ConfigurationResolver(
			new QitJsonParser(),
			$this->createMock(ExtensionResolver::class),
			$this->createMock(TestPackageDownloader::class),
			$this->createMock(Cache::class),
			new NullOutput()
		);
		$pre = new TinyPreCommand($input, $tmp, $resolver, new ConfigMerger());

		// Call get_environment_config twice - should return identical results
		$result1 = $pre->get_environment_config();
		$result2 = $pre->get_environment_config();

		$this->assertEquals($result1, $result2, 'Multiple calls should return identical results');
		$this->assertSame('8.1', $result1['php'] ?? null, 'CLI php should be in result');
	}

	/**
	 * Test automatic key pluralisation for array values.
	 */
	public function test_key_pluralisation(): void {
		// Use real ArrayInput with array options
		$input = new ArrayInput([
			'--plugin' => ['woocommerce', 'jetpack'],
			'--theme' => ['storefront'],
			'--volume' => ['/tmp:/tmp']
		]);

		// Create real resolver with empty config
		$resolver = $this->createRealResolver();

		$tinyPreCommand = new TinyPreCommand($input, null, $resolver, $this->merger);
		$result = $tinyPreCommand->get_environment_config();

		// Array values should be pluralized in the explicit options
		$this->assertArrayHasKey('plugins', $result, 'plugin should be pluralized to plugins');
		$this->assertArrayHasKey('themes', $result, 'theme should be pluralized to themes');
		$this->assertArrayHasKey('volumes', $result, 'volume should be pluralized to volumes');
		
		$this->assertEquals(['woocommerce', 'jetpack'], $result['plugins']);
		$this->assertEquals(['storefront'], $result['themes']);
		$this->assertEquals(['/tmp:/tmp'], $result['volumes']);
	}

	/**
	 * Test special key mappings (e.g., env -> env_vars).
	 */
	public function test_special_key_mappings(): void {
		// Use real ArrayInput with env option
		$input = new ArrayInput([
			'--env' => ['DEBUG=1', 'LOG_LEVEL=debug']
		]);

		// Create real resolver with empty config
		$resolver = $this->createRealResolver();

		$tinyPreCommand = new TinyPreCommand($input, null, $resolver, $this->merger);
		$result = $tinyPreCommand->get_environment_config();

		// env should be mapped to env_vars
		$this->assertArrayHasKey('env_vars', $result, 'env should be mapped to env_vars');
		$this->assertEquals(['DEBUG=1', 'LOG_LEVEL=debug'], $result['env_vars']);
	}

	/**
	 * Test explicit option detection - only explicitly provided options should override.
	 */
	public function test_explicit_option_detection(): void {
		// Use real ArrayInput with only explicitly provided php option
		$input = new ArrayInput(['--php' => '8.1']);

		// Create real resolver with config that has wp: 6.0
		$config = ['environments' => ['default' => ['wp' => '6.0']]];
		$resolver = $this->createRealResolver($config);

		$tinyPreCommand = new TinyPreCommand($input, null, $resolver, $this->merger);
		$result = $tinyPreCommand->get_environment_config();

		// Result should contain php from CLI and wp from config
		$this->assertIsArray($result, 'Result should be an array');
		$this->assertEquals('8.1', $result['php'] ?? null, 'Explicitly provided CLI php should be in result');
		$this->assertEquals('6.0', $result['wp'] ?? null, 'Config wp should be used when not explicitly provided via CLI');
	}

	/**
	 * Test phpstan_level special case conversion to integer.
	 */
	public function test_phpstan_level_conversion(): void {
		$input = new ArrayInput(['--phpstan_level' => '5']);

		// Create real resolver with empty config
		$resolver = $this->createRealResolver();

		$tinyPreCommand = new TinyPreCommand($input, null, $resolver, $this->merger);
		$result = $tinyPreCommand->get_environment_config();

		// phpstan_level should be converted to integer if present
		$this->assertArrayHasKey('phpstan_level', $result, 'phpstan_level should be in result');
		$this->assertIsInt($result['phpstan_level'], 'phpstan_level should be converted to integer');
		$this->assertEquals(5, $result['phpstan_level'], 'phpstan_level should be 5');
	}

	/**
	 * Test merge happy path with CLI > config > defaults precedence.
	 */
	public function test_merge_happy_path(): void {
		$input = new ArrayInput(['--php' => '8.1', '--wp' => '6.1']);

		// Create real resolver with config values
		$config = ['environments' => ['default' => ['php' => '7.4', 'woo' => 'stable']]];
		$resolver = $this->createRealResolver($config);

		$tinyPreCommand = new TinyPreCommand($input, null, $resolver, $this->merger);
		$result = $tinyPreCommand->get_environment_config();

		$this->assertIsArray($result, 'Result should be an array');
		// Verify CLI overrides config
		$this->assertEquals('8.1', $result['php'], 'CLI php should override config');
		$this->assertEquals('6.1', $result['wp'], 'CLI wp should override config');
		$this->assertEquals('stable', $result['woo'], 'Config woo should be preserved');
	}

	/**
	 * Test error handling when configuration resolution fails.
	 */
	public function test_configuration_error_handling(): void {
		$input = new ArrayInput(['--php' => '8.1']);

		// Create a resolver that will cause get_environment to fail
		// We'll create a ResolvedConfiguration that throws an exception
		$resolver = $this->createRealResolver();
		
		// Create a custom ResolvedConfiguration that throws on get_environment
		$brokenConfig = new class([]) extends ResolvedConfiguration {
			public function get_environment(string $environment): array {
				throw new \RuntimeException('Config file not found');
			}
		};
		
		// We need to mock the resolver to return our broken config
		$mockResolver = $this->createMock(ConfigurationResolver::class);
		$mockResolver->method('resolve')->willReturn($brokenConfig);

		$tinyPreCommand = new TinyPreCommand($input, null, $mockResolver, $this->merger);
		
		// Should not throw exception, should handle gracefully with empty config
		$result = $tinyPreCommand->get_environment_config();
		$this->assertIsArray($result, 'Should handle config errors gracefully');
		$this->assertEquals('8.1', $result['php'], 'CLI args should still work');
	}

	/**
	 * Test QIT_SELF_TEST=precommand early return functionality.
	 */
	public function test_early_return_functionality(): void {
		// Set environment variable
		putenv('QIT_SELF_TEST=precommand');

		$input = new ArrayInput(['--php' => '8.1']);

		// Create real resolver with test config
		$config = ['environments' => ['default' => ['wp' => '6.0']]];
		$resolver = $this->createRealResolver($config);

		$tinyPreCommand = new TinyPreCommand($input, null, $resolver, $this->merger);

		try {
			$this->expectException(PrecommandEarlyReturn::class);
			$tinyPreCommand->get_environment_config();
		} finally {
			// Clean up environment variable
			putenv('QIT_SELF_TEST');
		}
	}
}