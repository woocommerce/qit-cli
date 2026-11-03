<?php

namespace QIT_CLI\Commands;

use QIT_CLI\MCP\McpServer;
use QIT_CLI\MCP\StdioTransport;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class McpCommand extends Command {
	protected static $defaultName = 'mcp'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	private McpServer $server;
	private StdioTransport $transport;

	public function __construct( McpServer $server, StdioTransport $transport ) {
		$this->server    = $server;
		$this->transport = $transport;

		parent::__construct( static::$defaultName ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

	protected function configure(): void {
		$this
			->setDescription( 'Start the read-only QIT MCP server over stdio.' )
			->setHelp( 'Starts a Model Context Protocol server for structured QIT run reporting and debugging context.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		unset( $input, $output );

		return $this->transport->run( $this->server );
	}
}
