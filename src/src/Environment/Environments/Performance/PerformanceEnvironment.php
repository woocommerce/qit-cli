<?php

namespace QIT_CLI\Environment\Environments\Performance;

use QIT_CLI\App;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\Environments\Environment;
use QIT_CLI\Environment\Environments\ThemeActivation;
use QIT_CLI\Environment\EnvUpChecker;
use QIT_CLI\Environment\PluginActivationReportRenderer;
use QIT_CLI\Tunnel\TunnelRunner;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

class PerformanceEnvironment extends Environment {
	/** @var string */
	protected $description = 'Performance Environment';

	/**
	 * @var PerformanceEnvInfo
	 */
	protected $env_info;

	/** @var bool */
	protected $skip_activating_plugins = false;

	/** @var bool */
	protected $skip_activating_themes = false;

	public function get_name(): string {
		return 'performance';
	}

	public function set_skip_activating_plugins( bool $skip_activating_plugins ): void {
		$this->skip_activating_plugins = $skip_activating_plugins;
	}

	public function set_skip_activating_themes( bool $skip_activating_themes ): void {
		$this->skip_activating_themes = $skip_activating_themes;
	}

	protected function post_generate_docker_compose(): void {
		$qit_conf = $this->env_info->temporary_env . '/docker/nginx/conf.d/qit.conf';

		if ( ! file_exists( $qit_conf ) ) {
			throw new \RuntimeException( 'Could not find qit.conf' );
		}

		// Set up performance test scaffolding files.
		$this->setup_performance_scaffolding();
	}

	private function setup_performance_scaffolding(): void {
		$k6_dir = $this->env_info->temporary_env . '/k6';

		if ( ! file_exists( $k6_dir ) ) {
			if ( ! mkdir( $k6_dir, 0755, true ) ) {
				throw new \RuntimeException( 'Could not create k6 directory: ' . $k6_dir );
			}
		}

		// Create k6 test configuration generator.
		$this->create_k6_config_generator();

		// Create qitHelpers for k6 tests.
		$this->create_k6_helpers();

		// Create test-info.json structure.
		$this->create_test_info_structure();
	}

	private function create_k6_config_generator(): void {
		$generator_content = '<?php
// k6 config generator for performance tests
$projects = json_decode(getenv("PROJECTS"), true);
$config_overrides = json_decode(getenv("CONFIG_OVERRIDES"), true);
$test_result_path = getenv("TEST_RESULT_PATH");

$k6_config = [
    "scenarios" => [],
    "thresholds" => [
        "http_req_duration" => ["p(95)<500"],
        "http_req_failed" => ["rate<0.1"],
    ],
    "options" => [
        "summaryTrendStats" => ["avg", "min", "med", "max", "p(90)", "p(95)"],
        "summaryTimeUnit" => "ms",
    ],
];

// Apply config overrides
if ($config_overrides) {
    $k6_config = array_merge_recursive($k6_config, $config_overrides);
}

// Generate k6 configuration
$output_path = getenv("SAVE_AS");
file_put_contents($output_path, json_encode($k6_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
';

		file_put_contents( $this->env_info->temporary_env . '/k6/k6-config-generator.php', $generator_content );
	}

	private function create_k6_helpers(): void {
		$helpers_content = 'import { check, sleep } from "k6";
import http from "k6/http";

export const qitHelpers = {
    // WordPress specific helpers
    wp: {
        login: function(baseUrl, username = "admin", password = "password") {
            const loginUrl = `${baseUrl}/wp-admin/admin-ajax.php`;
            const loginData = {
                action: "heartbeat",
                _wpnonce: this.getNonce(baseUrl),
                log: username,
                pwd: password,
            };
            
            return http.post(loginUrl, loginData);
        },
        
        getNonce: function(baseUrl) {
            const response = http.get(`${baseUrl}/wp-login.php`);
            const nonce = response.html().find("input[name=\'_wpnonce\']").attr("value");
            return nonce || "";
        },
    },
    
    // WooCommerce specific helpers
    woo: {
        addToCart: function(baseUrl, productId, quantity = 1) {
            const addToCartUrl = `${baseUrl}/?wc-ajax=add_to_cart`;
            const data = {
                product_id: productId,
                quantity: quantity,
            };
            
            return http.post(addToCartUrl, data);
        },
        
        viewCart: function(baseUrl) {
            return http.get(`${baseUrl}/cart/`);
        },
        
        checkout: function(baseUrl) {
            return http.get(`${baseUrl}/checkout/`);
        },
    },
    
    // Common test utilities
    utils: {
        randomString: function(length = 8) {
            const chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
            let result = "";
            for (let i = 0; i < length; i++) {
                result += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            return result;
        },
        
        waitFor: function(seconds = 1) {
            sleep(seconds);
        },
        
        checkResponse: function(response, expectedStatus = 200) {
            return check(response, {
                "status is " + expectedStatus: (r) => r.status === expectedStatus,
                "response time < 500ms": (r) => r.timings.duration < 500,
            });
        },
    },
};
';

		file_put_contents( $this->env_info->temporary_env . '/k6/qitHelpers.js', $helpers_content );
	}

	private function create_test_info_structure(): void {
		// This will be populated during test execution similar to E2E tests
		$test_info = [
			'SUT_SLUG' => '',
			'SUT_TYPE' => '',
			'SUT_ENTRYPOINT' => '',
			'SUT_QIT_CONFIG' => [],
			'PLUGIN_ACTIVATION_STACK' => [],
		];

		file_put_contents( $this->env_info->temporary_env . '/k6/test-info.json', json_encode( $test_info ) );
	}

	public function create_environment( array $config ): PerformanceEnvInfo {
		// Create environment info from config
		$env_info = new PerformanceEnvInfo();
		$env_info->sut_slug = $config['sut'];
		$env_info->sut_action = $config['sut_action'];
		$env_info->wp_version = $config['wp_version'];
		$env_info->woo_version = $config['woo_version'];
		$env_info->php_version = $config['php_version'];
		$env_info->object_cache = $config['object_cache'];
		$env_info->plugins = $config['plugins'];
		$env_info->php_extensions = $config['php_extensions'];

		// Set skip flags
		$this->skip_activating_plugins = $config['skip_activating_plugins'] ?? false;
		$this->skip_activating_themes = $config['skip_activating_themes'] ?? false;

		// Set up the environment
		$this->env_info = $env_info;
		$this->setup_environment();

		return $env_info;
	}

	public function destroy_environment( string $env_id ): void {
		// Clean up Docker containers and volumes
		$docker = App::make( Docker::class );
		
		// Stop and remove containers
		$containers = [
			"qit_env_php_$env_id",
			"qit_env_nginx_$env_id",
			"qit_env_mysql_$env_id",
			"qit_env_k6_$env_id",
		];

		foreach ( $containers as $container ) {
			$docker->stop_container( $container );
			$docker->remove_container( $container );
		}

		// Remove volumes
		$docker->remove_volume( "qit_env_volume_$env_id" );
		$docker->remove_network( "qit_env_network_$env_id" );
	}

	private function setup_environment(): void {
		// Generate environment ID
		$this->env_info->env_id = uniqid( 'perf_' );
		
		// Set up temporary environment directory
		$this->env_info->temporary_env = sys_get_temp_dir() . '/qit_performance_' . $this->env_info->env_id;
		
		if ( ! mkdir( $this->env_info->temporary_env, 0755, true ) ) {
			throw new \RuntimeException( 'Could not create temporary environment directory' );
		}

		// Set up Docker network
		$this->env_info->docker_network = "qit_env_network_{$this->env_info->env_id}";
		
		// Create Docker compose configuration
		$this->generate_docker_compose();
		
		// Start the environment
		$this->start_environment();
	}

	private function generate_docker_compose(): void {
		// Generate Docker Compose configuration similar to E2E environment
		// This would include PHP, MySQL, Nginx containers
		// Implementation would follow the same pattern as E2EEnvironment
		
		// For now, we'll create a basic structure
		$compose_content = [
			'version' => '3.8',
			'services' => [
				'php' => [
					'image' => 'wordpress:php' . ($this->env_info->php_version ?: '8.1') . '-fpm',
					'container_name' => "qit_env_php_{$this->env_info->env_id}",
					'networks' => [ $this->env_info->docker_network ],
					'volumes' => [
						"qit_env_volume_{$this->env_info->env_id}:/var/www/html",
					],
				],
				'nginx' => [
					'image' => 'nginx:alpine',
					'container_name' => "qit_env_nginx_{$this->env_info->env_id}",
					'networks' => [ $this->env_info->docker_network ],
					'volumes' => [
						"qit_env_volume_{$this->env_info->env_id}:/var/www/html",
					],
					'ports' => [ '80' ],
				],
				'mysql' => [
					'image' => 'mysql:8.0',
					'container_name' => "qit_env_mysql_{$this->env_info->env_id}",
					'networks' => [ $this->env_info->docker_network ],
					'environment' => [
						'MYSQL_ROOT_PASSWORD' => 'password',
						'MYSQL_DATABASE' => 'wordpress',
						'MYSQL_USER' => 'wordpress',
						'MYSQL_PASSWORD' => 'password',
					],
				],
			],
			'networks' => [
				$this->env_info->docker_network => [
					'driver' => 'bridge',
				],
			],
			'volumes' => [
				"qit_env_volume_{$this->env_info->env_id}" => null,
			],
		];

		$compose_dir = $this->env_info->temporary_env . '/docker';
		if ( ! mkdir( $compose_dir, 0755, true ) ) {
			throw new \RuntimeException( 'Could not create docker directory' );
		}

		file_put_contents( $compose_dir . '/docker-compose.yml', yaml_emit( $compose_content ) );
	}

	private function start_environment(): void {
		// Start Docker containers
		$docker = App::make( Docker::class );
		$compose_file = $this->env_info->temporary_env . '/docker/docker-compose.yml';
		
		$process = new Process([
			$docker->find_docker_compose(),
			'-f', $compose_file,
			'up', '-d'
		]);

		$process->run();

		if ( ! $process->isSuccessful() ) {
			throw new \RuntimeException( 'Failed to start environment: ' . $process->getErrorOutput() );
		}

		// Wait for environment to be ready
		$this->wait_for_environment();
	}

	private function wait_for_environment(): void {
		// Wait for the environment to be ready
		// This would check if WordPress is accessible and ready
		$max_attempts = 30;
		$attempt = 0;

		while ( $attempt < $max_attempts ) {
			try {
				// Check if environment is ready
				$nginx_port = $this->get_nginx_port();
				$this->env_info->nginx_port = $nginx_port;
				$this->env_info->site_url = "http://localhost:$nginx_port";
				
				// Simple health check
				$response = file_get_contents( $this->env_info->site_url, false, stream_context_create([
					'http' => [
						'timeout' => 5,
						'ignore_errors' => true,
					],
				]));

				if ( $response !== false ) {
					break;
				}
			} catch ( \Exception $e ) {
				// Continue waiting
			}

			$attempt++;
			sleep( 2 );
		}

		if ( $attempt >= $max_attempts ) {
			throw new \RuntimeException( 'Environment failed to start within timeout' );
		}
	}

	private function get_nginx_port(): int {
		// Get the mapped port for nginx container
		$docker = App::make( Docker::class );
		$container_name = "qit_env_nginx_{$this->env_info->env_id}";
		
		$process = new Process([
			$docker->find_docker(),
			'port', $container_name, '80'
		]);

		$process->run();

		if ( ! $process->isSuccessful() ) {
			throw new \RuntimeException( 'Failed to get nginx port' );
		}

		$output = trim( $process->getOutput() );
		if ( preg_match( '/0\.0\.0\.0:(\d+)/', $output, $matches ) ) {
			return (int) $matches[1];
		}

		throw new \RuntimeException( 'Could not parse nginx port' );
	}
}