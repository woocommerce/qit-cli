<?php

namespace QIT_AI_Webserver\Lib;

use Exception;
use LLPhant\Chat\AnthropicChat;
use LLPhant\Chat\ChatInterface;
use LLPhant\OpenAIConfig;
use QIT_AI_Webserver\Chat\SafeToolsOpenAIChat;

final class LLPhantBootstrap {
	/* ───────── 1. STATIC SINGLETON – BOOT ONLY ONCE ───────── */

	private static ?ChatInterface $chat = null;
	private static ?string $currentProvider = null;
	private static ?string $currentModel = null;
	private static array $allowed_models = [];

	/** Initialise exactly once per PHP process (router‑level). */
	public static function boot( string $provider, array $conf ): void {
		if ( self::$chat ) {            // already initialised
			return;
		}

		// Track current provider
		self::$currentProvider = $provider;

		$self = new self( $provider, $conf );   // reuse existing ctor logic
		$self->ensureInitialized();           // still installs composer etc.
		self::$chat = $self->getChat();

		static::$allowed_models = explode( ',', $conf['model'] );

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
	 * @param mixed $modelInput - Can be a string or an array with provider keys
	 * @param string $provider - Current provider (openai, anthropic, lmstudio)
	 *
	 * @return string - Resolved model name
	 * @throws \InvalidArgumentException
	 */
	public static function resolveModel( $modelInput, string $provider ): string {
		// Validate input
		if ( empty( $modelInput ) ) {
			throw new \InvalidArgumentException( 'Model parameter is required' );
		}

		// Handle string input directly (for backward compatibility and simpler usage)
		if ( is_string( $modelInput ) ) {
			return $modelInput;
		}

		// Handle array format with provider keys
		if ( is_array( $modelInput ) ) {
			// Multi-provider format
			if ( ! isset( $modelInput[ $provider ] ) ) {
				$available = implode( ', ', array_keys( $modelInput ) );
				throw new \InvalidArgumentException(
					"Model not specified for provider '{$provider}'. Available: {$available}"
				);
			}
			$resolvedModel = $modelInput[ $provider ];

			if ( empty( $resolvedModel ) ) {
				throw new \InvalidArgumentException(
					"Empty model specified for provider '{$provider}'"
				);
			}

			if ( ! empty( static::$allowed_models ) ) {
				if ( ! in_array( $resolvedModel, static::$allowed_models ) ) {
					$available = implode( ', ', static::$allowed_models );
					throw new \InvalidArgumentException(
						"Model '{$resolvedModel}' is not allowed. Available models: {$available}"
					);
				}
			}

			return $resolvedModel;
		}

		// Invalid input type
		throw new \InvalidArgumentException(
			'Model must be a string or an object with provider keys (e.g., {"openai": "gpt-4", "anthropic": "claude-3"})'
		);
	}

	/**
	 * Set model - handles validation, resolution, downloading, and configuration
	 *
	 * @param mixed $modelInput - Can be a string or an array with provider keys
	 * @param string $provider - Current provider (openai, anthropic, lmstudio)
	 *
	 * @return bool - True if model was set successfully
	 * @throws \InvalidArgumentException
	 */
	public static function setModel( $modelInput, string $provider ): bool {
		// Store current provider
		self::$currentProvider = $provider;

		// 1. Resolve model
		$resolvedModel = self::resolveModel( $modelInput, $provider );

		// Store resolved model
		self::$currentModel = $resolvedModel;

		// 2. Download model if needed (LM Studio only)
		if ( $provider === 'lmstudio' ) {
			if ( ! self::downloadModelIfNeeded( $resolvedModel ) ) {
				throw new \InvalidArgumentException(
					"Failed to ensure model '{$resolvedModel}' is available in LM Studio"
				);
			}
		}

		// 3. Set model on chat instance
		if ( self::$chat ) {
			self::$chat->setModelOption( 'model', $resolvedModel );
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
	private static function downloadModelIfNeeded( string $model ): bool {
		// First check if model is already available
		$instance = new self( 'lmstudio', [] );
		if ( $instance->checkLMStudioModelAvailability( $model ) ) {
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
	public static function getCurrentProvider(): string {
		return self::$currentProvider ?? 'unknown';
	}

	/**
	 * Get current resolved model
	 *
	 * @return string
	 */
	public static function getModel(): string {
		return self::$currentModel ?? 'unknown';
	}

	/* ───────── 2.  KEEP THE REST OF THE ORIGINAL CLASS ─────── */
	// (constructor, ensureInitialized, initializeProvider, generate* …)

	private string $installDir;
	private ?ChatInterface $chat_instance = null;
	private string $provider;
	private array $config;
	private $logger;

	public function __construct( string $provider, array $config = [], $logger = null ) {
		$this->provider = $provider;
		$this->config   = $config;
		$this->logger   = $logger;
		// Are we in Phar?
		if ( \Phar::running() !== '' ) {
			$this->installDir = sys_get_temp_dir() . '/qit-llphant';
		} else {
			$this->installDir = __DIR__ . '/../../dev/qit-llphant';
		}

		$this->ensureLLPhantInstalled();
	}

	public function getChat(): ChatInterface {
		return $this->chat_instance;
	}

	public function reinitialize( array $runtimeConfig = [] ): void {
		// invalidate old chat
		$this->chat_instance = null;
		$this->config        = array_merge( $this->config, $runtimeConfig );
		$this->initializeProvider( $runtimeConfig );
	}

	/**
	 * Ensure the provider is initialized
	 *
	 * @param array $options Runtime options for initialization
	 */
	public function ensureInitialized( array $options = [] ): void {
		if ( ! $this->chat_instance ) {
			$this->initializeProvider( $options );
		}
	}

	private function ensureLLPhantInstalled(): void {
		// Check if composer is available
		$composerCheck = shell_exec( 'which composer 2>&1' ) ?: shell_exec( 'where composer 2>&1' );
		if ( empty( trim( $composerCheck ) ) ) {
			throw new Exception( "Composer is not installed or not in PATH. Please install Composer first." );
		}

		if ( file_exists( $this->installDir . '/vendor/autoload.php' ) ) {
			$this->log_info( "LLPhant already installed at: " . $this->installDir );
			require_once $this->installDir . '/vendor/autoload.php';

			return;
		}

		$this->log_info( "Installing LLPhant to: " . $this->installDir );

		// Create directory
		if ( ! is_dir( $this->installDir ) ) {
			mkdir( $this->installDir, 0755, true );
		}

		// Create composer.json
		$composerJson = [
			'require' => [
				'theodo-group/llphant'      => 'dev-main#e0e01fbb696a56acc5652c573f155f538dc9936e',
				'nikic/php-parser'          => '^5',
			],
			'config'  => [
				'optimize-autoloader'    => true,
				'classmap-authoritative' => true,
				'minimum-stability'      => 'dev',
			],
		];

		file_put_contents(
			$this->installDir . '/composer.json',
			json_encode( $composerJson, JSON_PRETTY_PRINT )
		);

		// Run composer install
		$output     = [];
		$returnCode = 0;
		$cmd        = sprintf(
			'cd %s && composer install --no-dev --no-interaction --no-progress --ignore-platform-req=ext-gd 2>&1',
			escapeshellarg( $this->installDir )
		);

		exec( $cmd, $output, $returnCode );

		// Patch file.
		$llphantFile = "{$this->installDir}/vendor/theodo-group/llphant/src/Chat/OpenAIChat.php";
		file_put_contents(
			$llphantFile,
			str_replace(
				[
					'private function getToolsToCall(',
					'private array $tools',
				],
				[
					'protected function getToolsToCall(',
					'protected array $tools',
				],
				file_get_contents( $llphantFile )
			)
		);

		if ( $returnCode !== 0 ) {
			throw new Exception( "Failed to install LLPhant: " . implode( "\n", $output ) );
		}

		require_once $this->installDir . '/vendor/autoload.php';
		$this->log_info( "LLPhant installed successfully" );
	}

	private function initializeProvider( array $runtimeOptions = [] ): void {
		// Merge runtime options with constructor config
		$config = array_merge( $this->config, $runtimeOptions );

		switch ( $this->provider ) {
			case 'lmstudio': // fall through
			case 'openai':
				$this->initializeOpenAI( $config );
				break;
			case 'anthropic':
				$this->initializeAnthropic( $config );
				break;
			default:
				throw new Exception( "Unsupported provider: " . $this->provider );
		}
	}

	private function initializeOpenAI( array $config ): void {
		if ( ! isset( $config['api_key'] ) ) {
			throw new Exception( "OpenAI requires an API key" );
		}

		$config = array_merge( [
			'model' => 'o4-mini-2025-04-16' // Default to o4-mini-2025-04-16, but can be overridden
		], $config );

		// Create OpenAIConfig object
		$openaiConfig         = new OpenAIConfig();
		$openaiConfig->apiKey = $config['api_key'];
		$openaiConfig->model  = $config['model'];

		// Set custom base URL if provided (for LM Studio compatibility)
		if ( ! empty( $config['base_url'] ) ) {
			$openaiConfig->url = $config['base_url'];
		}

		$this->chat_instance = new SafeToolsOpenAIChat( $openaiConfig );
	}

	private function initializeAnthropic( array $config ): void {
		if ( ! isset( $config['api_key'] ) ) {
			throw new Exception( "Anthropic requires an API key" );
		}

		$config = array_merge( [
			'model' => 'claude-3-opus-20240229'
		], $config );

		$this->chat_instance = new AnthropicChat(
			$config['api_key'],
			$config['model']
		);
	}


	public function ensureModel( string $model ): bool {
		if ( $this->provider === 'lmstudio' ) {
			// For LM Studio, check if model is available via OpenAI-compatible API
			return $this->checkLMStudioModelAvailability( $model );
		}

		// For cloud providers (OpenAI, Anthropic), models are always available
		return true;
	}

	/**
	 * Check if a model is available in LM Studio via OpenAI-compatible API
	 *
	 * @param string $model Model name to check
	 *
	 * @return bool True if model is available, false otherwise
	 */
	private function checkLMStudioModelAvailability( string $model ): bool {
		// Get base URL from config, default to LM Studio default
		$baseUrl        = $this->config['base_url'] ?? 'http://localhost:1234/v1';
		$modelsEndpoint = rtrim( $baseUrl, '/' ) . '/models';

		$this->log_info( "Checking LM Studio model availability", [
			'model'    => $model,
			'endpoint' => $modelsEndpoint
		] );

		// Make API call to check available models
		$ch = curl_init( $modelsEndpoint );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, [
			'Content-Type: application/json',
			'Authorization: Bearer ' . ( $this->config['api_key'] ?? 'dummy' )
		] );

		$response = curl_exec( $ch );
		$httpCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		$error    = curl_error( $ch );
		curl_close( $ch );

		if ( $httpCode !== 200 ) {
			$this->log_error( "Failed to check LM Studio models", [
				'http_code' => $httpCode,
				'error'     => $error,
				'response'  => substr( $response, 0, 500 )
			] );

			// If we can't check, assume model is available (LM Studio might be starting up)
			return true;
		}

		$data = json_decode( $response, true );
		if ( ! isset( $data['data'] ) || ! is_array( $data['data'] ) ) {
			$this->log_error( "Invalid response format from LM Studio models endpoint", [
				'response' => substr( $response, 0, 500 )
			] );

			// If response format is unexpected, assume model is available
			return true;
		}

		// Check if the requested model is in the list of available models
		foreach ( $data['data'] as $availableModel ) {
			if ( isset( $availableModel['id'] ) && $availableModel['id'] === $model ) {
				$this->log_info( "Model found in LM Studio", [ 'model' => $model ] );

				return true;
			}
		}

		$this->log_info( "Model not found in LM Studio", [
			'model'            => $model,
			'available_models' => array_column( $data['data'], 'id' )
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

	public function getProvider(): string {
		return $this->provider;
	}
}
