#!/usr/bin/env php
<?php
/**
 * Snapshot environment config resolution for all meaningful input combinations.
 *
 * Runs qit_run_env_up (self-test mode) for each scenario and saves the resolved
 * EnvInfo JSON. Used as baseline before/after refactoring to detect regressions.
 *
 * Usage:
 *   php snapshot-env-resolution.php [--output-dir=/path/to/snapshots]
 *
 * Output: One JSON file per scenario in the output directory, plus a summary.json
 * mapping scenario names to their key fields.
 */

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Process\Process;

// Parse args
$output_dir = '/tmp/qit-resolution-snapshots-' . date( 'Ymd-His' );
foreach ( $argv as $arg ) {
	if ( str_starts_with( $arg, '--output-dir=' ) ) {
		$output_dir = substr( $arg, 13 );
	}
}

if ( ! is_dir( $output_dir ) ) {
	mkdir( $output_dir, 0755, true );
}

// Bootstrap globals (same as integration test bootstrap)
$GLOBALS['qit-php'] = __DIR__ . '/../../../src/qit-cli.php';

// Use existing QIT_HOME if available, otherwise error
$qit_home = getenv( 'QIT_HOME' ) ?: null;
if ( ! $qit_home ) {
	// Try to find a cached one
	$cache_files = glob( sys_get_temp_dir() . '/qit-init-cache-*.json' );
	if ( ! empty( $cache_files ) ) {
		$cache_data = json_decode( file_get_contents( end( $cache_files ) ), true );
		$qit_home   = $cache_data['source_qit_home'] ?? null;
	}
}

if ( ! $qit_home || ! is_dir( $qit_home ) ) {
	fwrite( STDERR, "Error: No QIT_HOME found. Run integration tests first to initialize.\n" );
	exit( 1 );
}

echo "Using QIT_HOME: $qit_home\n";
echo "Output dir: $output_dir\n\n";

/**
 * Run a qit env:up command in self-test mode and return the parsed JSON.
 *
 * @param array<string>                    $cli_args  CLI arguments after 'env:up'
 * @param array<string,mixed>|null         $config    qit.json config to pass
 * @return array{output: string, json: ?array<string,mixed>, exit_code: int}
 */
function run_scenario( array $cli_args, ?array $config = null ): array {
	$args = [ 'php', $GLOBALS['qit-php'], 'env:up', '--json' ];
	$args = array_merge( $args, $cli_args );

	$env = [
		'QIT_HOME'            => $GLOBALS['qit_home_for_run'],
		'MANAGER_URL'         => $_ENV['QIT_CUSTOM_TESTS_URL'] ?? 'http://qit.test:8081',
		'QIT_DISABLE_CLEANUP' => '1',
		'QIT_SELF_TESTS'      => '1',
		'QIT_SELF_TEST'       => 'env_up',
		'QIT_NO_PULL'         => '1',
		'CI'                  => '1',
		'COLUMNS'             => '300',
	];

	// Write config file if provided
	if ( $config !== null ) {
		$config_file = sys_get_temp_dir() . '/qit-snapshot-config-' . md5( json_encode( $config ) ) . '.json';
		file_put_contents( $config_file, json_encode( $config ) );
		$args[] = '--config';
		$args[] = $config_file;
	}

	$process = new Process( $args );
	$process->setTimeout( 60 );
	$process->setEnv( $env );
	$process->run();

	$output = $process->getOutput();
	$json   = json_decode( $output, true );

	return [
		'output'    => $output,
		'json'      => $json,
		'exit_code' => $process->getExitCode(),
	];
}

/**
 * Extract the fields we care about for comparison.
 *
 * @param array<string,mixed>|null $json
 * @return array<string,mixed>
 */
function extract_key_fields( ?array $json ): array {
	if ( $json === null ) {
		return [ 'error' => 'null response' ];
	}

	$plugins = [];
	foreach ( $json['plugins'] ?? [] as $p ) {
		$plugins[] = [
			'slug'    => $p['slug'] ?? '?',
			'version' => $p['version'] ?? 'undefined',
			'from'    => $p['from'] ?? '?',
		];
	}

	$themes = [];
	foreach ( $json['themes'] ?? [] as $t ) {
		$themes[] = [
			'slug'    => $t['slug'] ?? '?',
			'version' => $t['version'] ?? 'undefined',
			'from'    => $t['from'] ?? '?',
		];
	}

	return [
		'php_version'         => $json['php_version'] ?? '',
		'wordpress_version'   => $json['wordpress_version'] ?? '',
		'woocommerce_version' => $json['woocommerce_version'] ?? '',
		'object_cache'        => $json['object_cache'] ?? false,
		'plugins'             => $plugins,
		'themes'              => $themes,
		'php_extensions'      => $json['php_extensions'] ?? [],
	];
}

// Set global for run function
$GLOBALS['qit_home_for_run'] = $qit_home;

// Load .env for MANAGER_URL
$env_file = __DIR__ . '/.env';
if ( file_exists( $env_file ) ) {
	foreach ( file( $env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES ) as $line ) {
		if ( str_starts_with( $line, '#' ) ) {
			continue;
		}
		[ $key, $value ] = explode( '=', $line, 2 );
		$_ENV[ trim( $key ) ] = trim( trim( $value ), '"' );
	}
}

// ── Define scenarios ──

$scenarios = [
	'01-bare' => [
		'description' => 'Bare env:up, no flags, no config',
		'cli'         => [],
		'config'      => null,
	],
	'02-woo-flag-only' => [
		'description' => '--woo 8.8.0 only',
		'cli'         => [ '--woo', '8.8.0' ],
		'config'      => null,
	],
	'03-plugin-woo-only' => [
		'description' => '--plugin woocommerce only (bare slug)',
		'cli'         => [ '--plugin', 'woocommerce' ],
		'config'      => null,
	],
	'04-woo-flag-and-plugin-woo' => [
		'description' => '--woo 8.8.0 --plugin woocommerce (the bug)',
		'cli'         => [ '--woo', '8.8.0', '--plugin', 'woocommerce' ],
		'config'      => null,
	],
	'05-config-woo-only' => [
		'description' => 'Config woo: "8.5.0" only',
		'cli'         => [],
		'config'      => [ 'environments' => [ 'default' => [ 'woo' => '8.5.0' ] ] ],
	],
	'06-config-woo-plus-cli-woo' => [
		'description' => 'Config woo: "8.5.0" + --woo 8.8.0 (CLI wins)',
		'cli'         => [ '--woo', '8.8.0' ],
		'config'      => [ 'environments' => [ 'default' => [ 'woo' => '8.5.0' ] ] ],
	],
	'07-config-plugin-woo-versioned' => [
		'description' => 'Config plugins with versioned WooCommerce',
		'cli'         => [],
		'config'      => [ 'environments' => [ 'default' => [ 'plugins' => [
			[ 'slug' => 'woocommerce', 'from' => 'wporg', 'version' => '8.0.0' ],
		] ] ] ],
	],
	'08-config-woo-plus-config-plugin-versioned' => [
		'description' => 'Config woo: "8.5.0" + config plugin woocommerce:8.0.0',
		'cli'         => [],
		'config'      => [ 'environments' => [ 'default' => [
			'woo'     => '8.5.0',
			'plugins' => [
				[ 'slug' => 'woocommerce', 'from' => 'wporg', 'version' => '8.0.0' ],
			],
		] ] ],
	],
	'09-config-plugin-woo-bare' => [
		'description' => 'Config plugins: ["woocommerce"] (bare slug)',
		'cli'         => [],
		'config'      => [ 'environments' => [ 'default' => [ 'plugins' => [ 'woocommerce' ] ] ] ],
	],
	'10-cli-php-wp' => [
		'description' => '--php 8.3 --wp 6.8 (scalar overrides)',
		'cli'         => [ '--php_version', '8.3', '--wordpress_version', '6.8' ],
		'config'      => null,
	],
	'11-config-php-plus-cli-php' => [
		'description' => 'Config php: "7.4" + --php 8.3 (CLI overrides config)',
		'cli'         => [ '--php_version', '8.3' ],
		'config'      => [ 'environments' => [ 'default' => [ 'php' => '7.4' ] ] ],
	],
	'12-config-php-only' => [
		'description' => 'Config php: "7.4" only',
		'cli'         => [],
		'config'      => [ 'environments' => [ 'default' => [ 'php' => '7.4' ] ] ],
	],
	'13-multiple-plugins-no-woo' => [
		'description' => '--plugin akismet --plugin jetpack (no WooCommerce)',
		'cli'         => [ '--plugin', 'akismet', '--plugin', 'jetpack' ],
		'config'      => null,
	],
	'14-config-plus-cli-plugins' => [
		'description' => 'Config plugins + CLI plugins merge',
		'cli'         => [ '--plugin', 'jetpack' ],
		'config'      => [ 'environments' => [ 'default' => [ 'plugins' => [ 'akismet' ] ] ] ],
	],
	'15-config-woo-long-form' => [
		'description' => 'Config woocommerce_version: "8.5.0" (long-form key)',
		'cli'         => [],
		'config'      => [ 'environments' => [ 'default' => [ 'woocommerce_version' => '8.5.0' ] ] ],
	],
];

// ── Run all scenarios ──

$summary = [];
$passed  = 0;
$failed  = 0;

foreach ( $scenarios as $name => $scenario ) {
	echo sprintf( "[%s] %s ... ", $name, $scenario['description'] );

	$result     = run_scenario( $scenario['cli'], $scenario['config'] );
	$key_fields = extract_key_fields( $result['json'] );

	// Save full output
	file_put_contents(
		"$output_dir/$name.json",
		json_encode( [
			'scenario'   => $scenario,
			'key_fields' => $key_fields,
			'exit_code'  => $result['exit_code'],
			'raw_output' => $result['output'],
		], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES )
	);

	// Save key fields for summary
	$summary[ $name ] = [
		'description'         => $scenario['description'],
		'exit_code'           => $result['exit_code'],
		'php_version'         => $key_fields['php_version'],
		'wordpress_version'   => $key_fields['wordpress_version'],
		'woocommerce_version' => $key_fields['woocommerce_version'],
		'plugin_slugs'        => array_column( $key_fields['plugins'], 'slug' ),
	];

	if ( $result['exit_code'] === 0 && $result['json'] !== null ) {
		echo "OK (woo={$key_fields['woocommerce_version']}, php={$key_fields['php_version']}, plugins=" . implode( ',', array_column( $key_fields['plugins'], 'slug' ) ) . ")\n";
		$passed++;
	} else {
		echo "FAIL (exit={$result['exit_code']})\n";
		$failed++;
	}
}

// Save summary
file_put_contents(
	"$output_dir/summary.json",
	json_encode( $summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES )
);

echo "\n";
echo "Results: $passed passed, $failed failed\n";
echo "Snapshots saved to: $output_dir\n";
echo "Summary: $output_dir/summary.json\n";
