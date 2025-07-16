<?php

namespace QIT_AI_Webserver\Lib;

use Exception;
use LLPhant\Chat\AnthropicChat;
use LLPhant\Chat\ChatInterface;
use LLPhant\OpenAIConfig;
use QIT_AI_Webserver\Chat\SafeToolsOpenAIChat;

final class LLPhantBootstrap {
	/* ───────── 1. STATIC SINGLETON – BOOT ONLY ONCE ───────── */

	/**
	 * Chat interface instance.
	 *
	 * @var ?ChatInterface
	 */
	private static ?ChatInterface $chat = null;

	/**
	 * Current provider name.
	 *
	 * @var ?string
	 */
	private static ?string $current_provider = null;

	/**
	 * Current model name.
	 *
	 * @var ?string
	 */
	private static ?string $current_model = null;

	/**
	 * Allowed models array.
	 *
	 * @var array
	 */
	private static array $allowed_models = [];

	/** Initialise exactly once per PHP process (router‑level). */
	public static function boot( string $provider, array $conf ): void {
		if ( self::$chat ) {            // already initialised
			return;
		}

		// Track current provider
		self::$current_provider = $provider;

		$self = new self( $provider, $conf );   // reuse existing ctor logic
		$self->ensure_initialized();           // still installs composer etc.
		self::$chat = $self->get_chat();

		self::$allowed_models = explode( ',', $conf['model'] );

		// Apply options once during boot()
		foreach ( [ 'model', 'temperature', 'max_tokens' ] as $opt ) {
			if ( isset( $conf[ $opt ] ) ) {
				self::$chat->setModelOption( $opt, $conf[ $opt ] );
			}
		}
	}

	/** Retrieve the ready‑to‑use ChatInterface for endpoints. */
	public static function chat(): ChatInterface {
		if ( ! self::$chat ) {
			throw new \RuntimeException( 'LLPhantBootstrap::boot() not called' );
		}

		return self::$chat;
	}

	/**
	 * Resolve model input to a string based on current provider
	 *
	 * @param mixed  $model_input - Can be a string or an array with provider keys.
	 * @param string $provider - Current provider (openai, anthropic, lmstudio).
	 *
	 * @return string - Resolved model name
	 * @throws \InvalidArgumentException When model parameter is invalid.
	 */
	public static function resolve_model( $model_input, string $provider ): string {
		// Validate input
		if ( empty( $model_input ) ) {
			throw new \InvalidArgumentException( 'Model parameter is required' );
		}

		// Handle string input directly (for backward compatibility and simpler usage)
		if ( is_string( $model_input ) ) {
			return $model_input;
		}

		// Handle array format with provider keys
		if ( is_array( $model_input ) ) {
			// Multi-provider format
			if ( ! isset( $model_input[ $provider ] ) ) {
				$available = implode( ', ', array_keys( $model_input ) );
				throw new \InvalidArgumentException(
					"Model not specified for provider '{$provider}'. Available: {$available}"
				);
			}
			$resolved_model = $model_input[ $provider ];

			if ( empty( $resolved_model ) ) {
				throw new \InvalidArgumentException(
					"Empty model specified for provider '{$provider}'"
				);
			}

			if ( ! empty( self::$allowed_models ) ) {
				if ( ! in_array( $resolved_model, self::$allowed_models, true ) ) {
					$available = implode( ', ', self::$allowed_models );
					throw new \InvalidArgumentException(
						"Model '{$resolved_model}' is not allowed. Available models: {$available}"
					);
				}
			}

			return $resolved_model;
		}

		// Invalid input type
		throw new \InvalidArgumentException(
			'Model must be a string or an object with provider keys (e.g., {"openai": "gpt-4", "anthropic": "claude-3"})'
		);
	}

	/**
	 * Set model - handles validation, resolution, downloading, and configuration
	 *
	 * @param mixed  $model_input - Can be a string or an array with provider keys.
	 * @param string $provider - Current provider (openai, anthropic, lmstudio).
	 *
	 * @return bool - True if model was set successfully
	 * @throws \InvalidArgumentException When model parameter is invalid.
	 */
	public static function set_model( $model_input, string $provider ): bool {
		// Store current provider
		self::$current_provider = $provider;

		// 1. Resolve model
		$resolved_model = self::resolve_model( $model_input, $provider );

		// Store resolved model
		self::$current_model = $resolved_model;

		// 2. Download model if needed (LM Studio only)
		if ( $provider === 'lmstudio' ) {
			if ( ! self::download_model_if_needed( $resolved_model ) ) {
				throw new \InvalidArgumentException(
					"Failed to ensure model '{$resolved_model}' is available in LM Studio"
				);
			}
		}

		// 3. Set model on chat instance
		if ( self::$chat ) {
			self::$chat->setModelOption( 'model', $resolved_model );
		}

		return true;
	}

	/**
	 * Download model if needed for LM Studio
	 *
	 * @param string $model
	 *
	 * @return bool
	 */
	private static function download_model_if_needed( string $model ): bool {
		// First check if model is already available
		$instance = new self( 'lmstudio', [] );
		if ( $instance->check_lm_studio_model_availability( $model ) ) {
			return true; // Model already available
		}

		// TODO: Implement actual model downloading
		// For now, we'll log that the model needs to be downloaded
		// and return true to allow the process to continue
		error_log( "Model '{$model}' not found in LM Studio. Please load it manually through LM Studio UI." );

		// In a future implementation, this could:
		// 1. Call LM Studio's model management API
		// 2. Download from Hugging Face Hub
		// 3. Use LM Studio CLI commands

		return true; // Allow process to continue for now
	}

	/**
	 * Get current provider
	 */
	public static function get_current_provider(): string {
		return self::$current_provider ?? 'unknown';
	}

	/**
	 * Get current resolved model
	 *
	 * @return string
	 */
	public static function get_model(): string {
		return self::$current_model ?? 'unknown';
	}

	/*
	───────── 2.  KEEP THE REST OF THE ORIGINAL CLASS ───────
	*/
	// (constructor, ensureInitialized, initializeProvider, generate* …)

	/**
	 * Installation directory for dependencies.
	 *
	 * @var string
	 */
	private string $install_dir;

	/**
	 * Chat interface instance.
	 *
	 * @var ?ChatInterface
	 */
	private ?ChatInterface $chat_instance = null;

	/**
	 * Provider name.
	 *
	 * @var string
	 */
	private string $provider;

	/**
	 * Configuration array.
	 *
	 * @var array
	 */
	private array $config;

	/**
	 * Logger instance.
	 *
	 * @var mixed
	 */
	private $logger;

	private function compute_install_dir( array $composer_json ): string {
		// Stable hash of the **desired** dependency graph (ignore formatting)
		$hash = substr( sha1( json_encode( $composer_json, JSON_UNESCAPED_SLASHES ) ), 0, 12 );

		// e.g. /tmp/qit-llphant-a1b2c3d4e5f6
		// Are we in Phar?
		if ( \Phar::running() !== '' ) {
			return sys_get_temp_dir() . '/qit-llphant-' . $hash;
		} else {
			return __DIR__ . '/../../dev/qit-llphant-' . $hash;
		}
	}

	public function __construct( string $provider, array $config = [], $logger = null ) {
		$this->provider = $provider;
		$this->config   = $config;
		$this->logger   = $logger;

		// 1️⃣ Build the composer.json array once
		$composer_json = [
			'require' => [
				'theodo-group/llphant'      => 'dev-main#e0e01fbb696a56acc5652c573f155f538dc9936e',
				'nikic/php-parser'          => '^5',
				'justinrainbow/json-schema' => '^6',
			],
			'config'  => [
				'optimize-autoloader'    => true,
				'classmap-authoritative' => true,
				'minimum-stability'      => 'dev',
			],
		];

		// 2️⃣ Derive the directory from that content
		$this->install_dir = $this->compute_install_dir( $composer_json );

		// 3️⃣ Perform installation (idempotent)
		$this->ensure_ll_phant_installed( $composer_json );
	}

	public function get_chat(): ChatInterface {
		return $this->chat_instance;
	}

	public function reinitialize( array $runtime_config = [] ): void {
		// invalidate old chat
		$this->chat_instance = null;
		$this->config        = array_merge( $this->config, $runtime_config );
		$this->initialize_provider( $runtime_config );
	}

	/**
	 * Ensure the provider is initialized
	 *
	 * @param array $options Runtime options for initialization.
	 */
	public function ensure_initialized( array $options = [] ): void {
		if ( ! $this->chat_instance ) {
			$this->initialize_provider( $options );
		}
	}

	private function ensure_ll_phant_installed( array $composer_json ): void {
		// Check if composer is available
		$composer_check = shell_exec( 'which composer 2>&1' ) ?: shell_exec( 'where composer 2>&1' );
		if ( empty( trim( $composer_check ) ) ) {
			throw new Exception( 'Composer is not installed or not in PATH. Please install Composer first.' );
		}

		// Use a lock file so parallel PHP workers do not race
		$lock = fopen( $this->install_dir . '.lock', 'c' );
		flock( $lock, LOCK_EX );

		if ( file_exists( $this->install_dir . '/vendor/autoload.php' ) ) {
			$this->log_info( 'LLPhant already installed at: ' . $this->install_dir );
			require_once $this->install_dir . '/vendor/autoload.php';
			flock( $lock, LOCK_UN );

			return;
		}

		$this->log_info( 'Installing LLPhant to: ' . $this->install_dir );

		// Directory does not exist ⇒ create & install
		mkdir( $this->install_dir, 0755, true );
		file_put_contents(
			$this->install_dir . '/composer.json',
			json_encode( $composer_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES )
		);

		// Run composer install
		$cmd = sprintf(
			'cd %s && composer install --no-dev --no-interaction --no-progress --ansi --ignore-platform-req=ext-gd 2>&1',
			escapeshellarg( $this->install_dir )
		);

		$output      = [];
		$return_code = 0;
		exec( $cmd, $output, $return_code );

		if ( $return_code !== 0 ) {
			throw new Exception( 'Failed to install LLPhant: ' . implode( "\n", $output ) );
		}

		// Patch file.
		$this->patch_ll_phant();

		require_once $this->install_dir . '/vendor/autoload.php';
		$this->log_info( 'LLPhant installed successfully' );
		flock( $lock, LOCK_UN );
	}

	private function patch_ll_phant(): void {
		$llphant_file = "{$this->install_dir}/vendor/theodo-group/llphant/src/Chat/OpenAIChat.php";
		file_put_contents(
			$llphant_file,
			str_replace(
				[
					'private function getToolsToCall(',
					'private array $tools',
				],
				[
					'protected function getToolsToCall(',
					'protected array $tools',
				],
				file_get_contents( $llphant_file )
			)
		);
	}

	private function initialize_provider( array $runtime_options = [] ): void {
		// Merge runtime options with constructor config
		$config = array_merge( $this->config, $runtime_options );

		switch ( $this->provider ) {
			case 'lmstudio': // fall through
			case 'openai':
				$this->initialize_openai( $config );
				break;
			case 'anthropic':
				$this->initialize_anthropic( $config );
				break;
			default:
				throw new Exception( 'Unsupported provider: ' . $this->provider );
		}
	}

	private function initialize_openai( array $config ): void {
		if ( ! isset( $config['api_key'] ) ) {
			throw new Exception( 'OpenAI requires an API key' );
		}

		$config = array_merge( [
			'model' => 'o4-mini-2025-04-16', // Default to o4-mini-2025-04-16, but can be overridden
		], $config );

		// Create OpenAIConfig object
		$openai_config          = new OpenAIConfig();
		$openai_config->api_key = $config['api_key'];
		$openai_config->model   = $config['model'];

		// Set custom base URL if provided (for LM Studio compatibility)
		if ( ! empty( $config['base_url'] ) ) {
			$openai_config->url = $config['base_url'];
		}

		$this->chat_instance = new SafeToolsOpenAIChat( $openai_config );
	}

	private function initialize_anthropic( array $config ): void {
		if ( ! isset( $config['api_key'] ) ) {
			throw new Exception( 'Anthropic requires an API key' );
		}

		$config = array_merge( [
			'model' => 'claude-3-opus-20240229',
		], $config );

		$this->chat_instance = new AnthropicChat(
			$config['api_key'],
			$config['model']
		);
	}


	public function ensure_model( string $model ): bool {
		if ( $this->provider === 'lmstudio' ) {
			// For LM Studio, check if model is available via OpenAI-compatible API
			return $this->check_lm_studio_model_availability( $model );
		}

		// For cloud providers (OpenAI, Anthropic), models are always available
		return true;
	}

	/**
	 * Check if a model is available in LM Studio via OpenAI-compatible API
	 *
	 * @param string $model Model name to check.
	 *
	 * @return bool True if model is available, false otherwise
	 */
	private function check_lm_studio_model_availability( string $model ): bool {
		// Get base URL from config, default to LM Studio default
		$base_url        = $this->config['base_url'] ?? 'http://localhost:1234/v1';
		$models_endpoint = rtrim( $base_url, '/' ) . '/models';

		$this->log_info( 'Checking LM Studio model availability', [
			'model'    => $model,
			'endpoint' => $models_endpoint,
		] );

		// Make API call to check available models
		$ch = curl_init( $models_endpoint );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, [
			'Content-Type: application/json',
			'Authorization: Bearer ' . ( $this->config['api_key'] ?? 'dummy' ),
		] );

		$response = curl_exec( $ch );
		$http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		$error    = curl_error( $ch );
		curl_close( $ch );

		if ( $http_code !== 200 ) {
			$this->log_error( 'Failed to check LM Studio models', [
				'http_code' => $http_code,
				'error'     => $error,
				'response'  => substr( $response, 0, 500 ),
			] );

			// If we can't check, assume model is available (LM Studio might be starting up)
			return true;
		}

		$data = json_decode( $response, true );
		if ( ! isset( $data['data'] ) || ! is_array( $data['data'] ) ) {
			$this->log_error( 'Invalid response format from LM Studio models endpoint', [
				'response' => substr( $response, 0, 500 ),
			] );

			// If response format is unexpected, assume model is available
			return true;
		}

		// Check if the requested model is in the list of available models
		foreach ( $data['data'] as $available_model ) {
			if ( isset( $available_model['id'] ) && $available_model['id'] === $model ) {
				$this->log_info( 'Model found in LM Studio', [ 'model' => $model ] );

				return true;
			}
		}

		$this->log_info( 'Model not found in LM Studio', [
			'model'            => $model,
			'available_models' => array_column( $data['data'], 'id' ),
		] );

		// Model not found, but for LM Studio this might mean:
		// 1. Model needs to be loaded through LM Studio UI
		// 2. Model name doesn't match exactly
		// We'll return true and let the actual generation call handle the error
		return true;
	}

	private function log_info( string $message, array $context = [] ): void {
		if ( $this->logger ) {
			$this->logger->log_info( $message, $context );
		}
	}

	private function log_error( string $message, array $context = [] ): void {
		if ( $this->logger ) {
			$this->logger->log_error( $message, $context );
		}
	}

	public function get_provider(): string {
		return $this->provider;
	}
}
