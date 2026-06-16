<?php
declare( strict_types=1 );

namespace QIT_CLI\Commands;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Commands\RunE2ECommand;
use QIT_CLI\QITInput;
use QIT_CLI\RemoteTestRunner;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Dynamically registers remote test‑type commands (run:security, run:phpstan, …)
 * based on schemas fetched from the Manager.
 *
 * – Merges profile‑level configuration from qit.json with CLI overrides
 * – Supports SUT, ZIP uploads, groups, wait/timeout, etc.
 */
class CreateRunCommands extends DynamicCommandCreator {

	/** @var Cache */
	protected Cache $cache;
	/** @var RemoteTestRunner */
	protected RemoteTestRunner $remote_test_runner;

	public function __construct(
		Cache $cache,
		RemoteTestRunner $remote_test_runner
	) {
		$this->cache              = $cache;
		$this->remote_test_runner = $remote_test_runner;
	}

	/**
	 * Public API
	 */
	public function register_commands( Application $application ): void {
		$ignored_test_types = [ 'activation', 'performance', 'woo-api', 'woo-e2e' ]; // Activation/woo-api/woo-e2e handled locally, performance both remotely and locally.
		$schemas            = $this->cache->get_manager_sync_data( 'schemas' );

		foreach ( $this->cache->get_manager_sync_data( 'test_types' ) as $test_type ) {
			if ( \in_array( $test_type, $ignored_test_types, true ) ) {
				continue;
			}
			$this->register_command_by_schema( $application, $test_type, $schemas[ $test_type ] ?? [] );
		}
	}

	/**
	 * Register a command by schema.
	 *
	 * @param Application  $application The application instance.
	 * @param string       $test_type   The test type.
	 * @param array<mixed> $schema      The schema configuration.
	 */
	protected function register_command_by_schema( Application $application, string $test_type, array $schema ): void {

		/**
		 * Anonymous DynamicCommand – one per test‑type
		 */
		$command = new class(
			$test_type,
			$this->remote_test_runner
		) extends DynamicCommand {

			/** @var RemoteTestRunner */
			private RemoteTestRunner $remote_test_runner;

			public function __construct(
				string $test_type,
				RemoteTestRunner $remote_test_runner
			) {
				$this->remote_test_runner = $remote_test_runner;
				parent::__construct( $test_type );
				$this->setName( "run:$test_type" );
				$this->setDescription( "Run $test_type tests on QIT (waits for completion by default)" );
			}

			/**
			 * Main execution
			 *
			 * @param QITInput        $input
			 * @param OutputInterface $output
			 *
			 * @return int
			 */
			public function doExecute( QITInput $input, OutputInterface $output ): int {
				return $this->remote_test_runner->execute(
					$this,
					$this->test_type,
					$this->options_to_send,
					$input,
					$output
				);
			}

		};

		/**
		 * CLI definition helpers (schema‑driven)
		 * Note: add_schema_to_command appends alias info (--wp, --woo, --php) to option descriptions
		 */
		self::add_schema_to_command( $command, $schema );

		/* Standard non‑schema arguments / flags */
		$command
			->addArgument( 'sut', InputArgument::OPTIONAL, 'Extension slug or WooCommerce.com ID' )
			->addOption( 'zip', null, InputOption::VALUE_OPTIONAL, '(Optional) Local ZIP / dir / URL build to test' )
			->addOption( 'json', 'j', InputOption::VALUE_NEGATABLE, '(Optional) Output raw JSON response', false )
			->addOption( 'async', null, InputOption::VALUE_NEGATABLE, '(Optional) Enqueue test and return immediately without waiting', false )
			->addOption( 'wait', 'w', InputOption::VALUE_NEGATABLE, '(Deprecated) Wait for test completion - this is now the default behavior', false )
			->addOption( 'print-report-url', null, InputOption::VALUE_NEGATABLE, '(Optional) Print the test report URL (contains sensitive data - use cautiously in public logs)', false )
			->addOption( 'timeout', 't', InputOption::VALUE_OPTIONAL, '(Optional) Wait timeout in seconds', null )
			->addOption( 'group', 'g', InputOption::VALUE_NEGATABLE, '(Optional) Register the run into a group', false );

		// Ensure zip gets forwarded
		$command->add_option_to_send( 'zip' );

		/* Hide old "e2e" alias if Manager says so */
		if ( $test_type === 'e2e' ) {
			$hide = $this->cache->get_manager_sync_data( 'hide_e2e' );
			if ( ! $hide ) {
				$application->add( App::make( RunE2ECommand::class ) );
			}
		} else {
			$application->add( $command );
		}
	}
}
