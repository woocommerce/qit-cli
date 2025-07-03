<?php

namespace QIT_AI_Webserver\Lib;

use Exception;
use LLPhant\Chat\AnthropicChat;
use LLPhant\Chat\ChatInterface;
use LLPhant\Chat\Message;
use LLPhant\Chat\OllamaChat;
use LLPhant\Chat\OpenAIChat;
use LLPhant\OllamaConfig;
use QIT_AI_Webserver\ToolRegistry;

class LLPhantIntegration {
	private string $installDir;
	private ?ChatInterface $chat = null;
	private string $provider;
	private array $config;
	private $logger;

	public function __construct( string $provider = 'ollama', array $config = [], $logger = null ) {
		$this->provider   = $provider;
		$this->config     = $config;
		$this->logger     = $logger;
		$this->installDir = sys_get_temp_dir() . '/qit-llphant';

		$this->ensureLLPhantInstalled();
	}

	public function getChat(): ChatInterface {
		return $this->chat;
	}

	public function reinitialize( array $runtimeConfig = [] ): void {
		// invalidate old chat
		$this->chat   = null;
		$this->config = array_merge( $this->config, $runtimeConfig );
		$this->initializeProvider( $runtimeConfig );
	}

	/**
	 * Ensure the provider is initialized
	 *
	 * @param array $options Runtime options for initialization
	 */
	public function ensureInitialized( array $options = [] ): void {
		if ( ! $this->chat ) {
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
				'theodo-group/llphant' => 'dev-main#e0e01fbb696a56acc5652c573f155f538dc9936e',
				'nikic/php-parser'     => '^5',
			],
			'config'  => [
				'optimize-autoloader'    => true,
				'classmap-authoritative' => true,
				'minimum-stability'     => 'dev',
			]
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
			case 'ollama':
				$this->initializeOllama( $config );
				break;
			case 'openai':
				$this->initializeOpenAI( $config );
				break;
			case 'anthropic':
				$this->initializeAnthropic( $config );
				break;
			default:
				throw new Exception( "Unsupported provider: " . $this->provider );
		}

		// Apply schema if provided
		if ( isset( $config['format'] ) ) {
			// OpenAI/Anthropic: response_format for JSON schema
			// Ollama: format for JSON structure
			if ( $this->chat instanceof OpenAIChat || $this->chat instanceof AnthropicChat ) {
				OpenAIChat::setModelOption( 'response_format', [ 'type' => 'json_schema', 'schema' => $config['format'] ] );
			} elseif ( $this->chat instanceof OllamaChat ) {
				$this->chat->setModelOption( 'format', $config['format'] );
			}
		}
	}

	private function initializeOllama( array $config ): void {
		$config = array_merge( [
			'url'   => 'http://localhost:11434',
			'model' => 'llama3.2'
		], $config );

		// Parse URL to get host and port
		$parsedUrl = parse_url( $config['url'] );
		$host      = ( $parsedUrl['scheme'] ?? 'http' ) . '://' . ( $parsedUrl['host'] ?? 'localhost' );
		$port      = $parsedUrl['port'] ?? 11434;

		// Create OllamaConfig object
		$ollamaConfig        = new OllamaConfig();
		$ollamaConfig->url   = $host . ':' . $port . '/api/';
		$ollamaConfig->model = $config['model'];

		// Create Ollama chat instance using the correct LLPhant API
		$this->chat = new OllamaChat( $ollamaConfig );
	}

	private function initializeOpenAI( array $config ): void {
		if ( ! isset( $config['api_key'] ) ) {
			throw new Exception( "OpenAI requires an API key" );
		}

		$config = array_merge( [
			'model' => 'gpt-4-turbo-preview'
		], $config );

		$this->chat = new OpenAIChat(
			$config['api_key'],
			$config['model']
		);
	}

	private function initializeAnthropic( array $config ): void {
		if ( ! isset( $config['api_key'] ) ) {
			throw new Exception( "Anthropic requires an API key" );
		}

		$config = array_merge( [
			'model' => 'claude-3-opus-20240229'
		], $config );

		$this->chat = new AnthropicChat(
			$config['api_key'],
			$config['model']
		);
	}

	/**
	 * Generate a response from the chat model
	 *
	 * Based on LLPhant documentation:
	 * - Uses Message objects for generateChat()
	 * - System messages are set via setSystemMessage()
	 * - generateText() for single prompts, generateChat() for conversations
	 *
	 * @param array $messages Array of messages with 'role' and 'content'
	 * @param array $options Additional options like temperature, max_tokens
	 *
	 * @return array Response with 'response', 'model', 'duration', 'provider'
	 */
	public function generateResponse( array $messages, array $options = [] ): array {
		// Initialize on first use with runtime options
		if ( ! $this->chat ) {
			$this->initializeProvider( $options );
		}

		try {
			$startTime = microtime( true );

			// Set model options if provided
			if ( isset( $options['temperature'] ) ) {
				$this->chat->setModelOption( 'temperature', $options['temperature'] );
			}
			if ( isset( $options['max_tokens'] ) ) {
				$this->chat->setModelOption( 'max_tokens', $options['max_tokens'] );
			}

			//$this->chat->setModelOption( 'keep_alive', 0 );

			// Convert messages to LLPhant Message objects
			// Based on documentation: Message::system(), Message::user(), Message::assistant()
			$llphantMessages = [];
			$systemMessage   = '';

			foreach ( $messages as $message ) {
				switch ( $message['role'] ) {
					case 'system':
						// System messages are concatenated and set separately
						$systemMessage .= $message['content'] . "\n";
						break;
					case 'user':
						$llphantMessages[] = Message::user( $message['content'] );
						break;
					case 'assistant':
						$llphantMessages[] = Message::assistant( $message['content'] );
						break;
					case 'tool':
						// Tool results are passed as user messages with context
						$llphantMessages[] = Message::user( "Tool Result: " . $message['content'] );
						break;
				}
			}

			// Set system message if present
			if ( ! empty( trim( $systemMessage ) ) ) {
				$this->chat->setSystemMessage( trim( $systemMessage ) );
			}

			// Generate response
			if ( empty( $llphantMessages ) ) {
				throw new Exception( "No messages provided for generation" );
			}

			// Use generateChat for conversations, generateText for single messages
			$response = count( $llphantMessages ) > 1
				? $this->chat->generateChat( $llphantMessages )
				: $this->chat->generateText( $llphantMessages[0]->content );

			$duration = microtime( true ) - $startTime;

			// Get model from options or config
			$model = $options['model'] ?? $this->config['model'] ?? 'unknown';

			return [
				'response' => is_string( $response ) ? $response : (string) $response,
				'model'    => $model,
				'duration' => $duration,
				'provider' => $this->provider
			];

		} catch ( Exception $e ) {
			$this->log_error( "LLPhant generation failed: " . $e->getMessage() );
			throw $e;
		}
	}

	/**
	 * Generate a response with tool support
	 *
	 * Uses generateTextOrReturnFunctionCalled() for tool interactions
	 * Processes tool results and recurses if needed
	 *
	 * @param array $messages Conversation messages
	 * @param array $tools Array of tool names to register
	 * @param ToolRegistry $toolRegistry Registry containing tool implementations
	 * @param array $options Additional options
	 *
	 * @return array Response with message content and tool_calls
	 */
	public function generateWithTools(
		array $messages,
		array $tools,
		ToolRegistry $toolRegistry,
		array $options = []
	): array {
		// Initialize on first use with runtime options
		if ( ! $this->chat ) {
			$this->initializeProvider( $options );
		}

		try {
			// Set model options if provided
			if ( isset( $options['temperature'] ) ) {
				$this->chat->setModelOption( 'temperature', $options['temperature'] );
			}
			if ( isset( $options['max_tokens'] ) ) {
				$this->chat->setModelOption( 'max_tokens', $options['max_tokens'] );
			}

			// Register tools with the chat instance
			foreach ( $tools as $toolName ) {
				if ( $tool = $toolRegistry->getTool( $toolName ) ) {
					$this->chat->addTool( $tool->getFunctionInfo() );
				}
			}

			// Convert messages to LLPhant Message objects
			$llphantMessages = [];
			$systemMessage   = '';

			foreach ( $messages as $message ) {
				switch ( $message['role'] ) {
					case 'system':
						$systemMessage .= $message['content'] . "\n";
						break;
					case 'user':
						$llphantMessages[] = Message::user( $message['content'] );
						break;
					case 'assistant':
						$llphantMessages[] = Message::assistant( $message['content'] );
						break;
					case 'tool':
						$llphantMessages[] = Message::user( "Tool Result: " . $message['content'] );
						break;
				}
			}

			// Set system message if present
			if ( ! empty( trim( $systemMessage ) ) ) {
				$this->chat->setSystemMessage( trim( $systemMessage ) );
			}

			if ( empty( $llphantMessages ) ) {
				throw new Exception( "No messages provided for tool generation" );
			}

			// Use the last message content for tool calls
			// LLPhant's generateTextOrReturnFunctionCalled expects a string prompt
			$lastMessageContent = $llphantMessages[ count( $llphantMessages ) - 1 ]->content;
			$answer             = $this->chat->generateTextOrReturnFunctionCalled( $lastMessageContent );

			// Handle tool calls if returned
			if ( is_array( $answer ) ) {
				$toolCalls = [];
				foreach ( $answer as $functionInfo ) {
					$args   = json_decode( $functionInfo->jsonArgs, true ) ?? [];
					$result = $toolRegistry->execute_tool( $functionInfo->name, $args );

					$toolCalls[] = [
						'function' => [
							'name'      => $functionInfo->name,
							'arguments' => $args,
						],
						'result'   => $result,
					];

					// Add tool result to messages for next iteration
					$messages[] = [ 'role' => 'tool', 'content' => json_encode( $result ) ];
				}

				// Recurse to handle additional tool calls or generate final response
				return $this->generateWithTools( $messages, $tools, $toolRegistry, $options );
			}

			// Return final text response
			return [
				'message' => [
					'content'    => is_string( $answer ) ? $answer : (string) $answer,
					'tool_calls' => [],
				],
			];

		} catch ( Exception $e ) {
			$this->log_error( "LLPhant tool generation failed: " . $e->getMessage() );
			throw $e;
		}
	}

	public function ensureModel( string $model ): bool {
		if ( $this->provider === 'ollama' ) {
			// For Ollama, check if model exists
			$cmd = sprintf( 'ollama show %s 2>&1', escapeshellarg( $model ) );
			exec( $cmd, $output, $returnCode );

			if ( $returnCode !== 0 ) {
				// Try to pull the model
				$this->log_info( "Pulling Ollama model: $model" );
				$cmd = sprintf( 'ollama pull %s 2>&1', escapeshellarg( $model ) );
				exec( $cmd, $output, $returnCode );

				return $returnCode === 0;
			}

			return true;
		}

		// For cloud providers, models are always available
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
}
