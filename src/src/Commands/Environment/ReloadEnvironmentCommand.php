<?php

namespace QIT_CLI\Commands\Environment;

use QIT_CLI\Environment\EnvironmentMonitor;
use QIT_CLI\Environment\EnvironmentSelectorTrait;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\Docker;
use QIT_CLI\LocalTests\E2E\ExtensionTestRunner;
use QIT_CLI\LocalTests\E2E\SharedSetupRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Reloads the environment to the post-SUT setup state (i.e. plugin-setup-snapshot.sql).
 */
class ReloadEnvironmentCommand extends Command {
	use EnvironmentSelectorTrait;

	protected static $defaultName = 'env:reload';

	protected EnvironmentMonitor $environment_monitor;
	protected Docker $docker;

	public function __construct(
		EnvironmentMonitor $environment_monitor,
		Docker $docker
	) {
		$this->environment_monitor = $environment_monitor;
		$this->docker              = $docker;
		parent::__construct( static::$defaultName );
	}

	protected function configure(): void {
		parent::configure();
		$this
			->setDescription( 'Reloads a running environment to the post-SUT setup state.' )
			->addArgument(
				'env_id',
				InputArgument::OPTIONAL,
				'Environment ID to reload. If omitted and only one environment is running, that one is used.'
			);
	}

	protected function doExecute( InputInterface $input, OutputInterface $output ): int {
		$io      = new SymfonyStyle( $input, $output );
		$running = $this->environment_monitor->get();
		$env_id  = $input->getArgument( 'env_id' );

		// Use trait to pick environment
		$env = $this->find_environment_or_error( $running, $env_id, $io );
		if ( ! $env ) {
			return self::FAILURE; // error printed
		}

		if ( ! $env instanceof E2EEnvInfo ) {
			$io->error( 'Could not reload: environment info is not E2EEnvInfo.' );

			return self::FAILURE;
		}

		$io->writeln( "<info>Reloading environment {$env->env_id} from /qit/plugin-setup-snapshot.sql...</info>" );

		try {
			$this->docker->run_inside_docker(
				$env,
				[ 'wp', 'db', 'import', '/qit/plugin-setup-snapshot.sql' ]
			);
			$io->success( 'Environment reloaded to post-SUT setup state.' );
		} catch ( \Exception $e ) {
			$io->error( 'Failed to reload environment: ' . $e->getMessage() );

			return self::FAILURE;
		}

		return self::SUCCESS;
	}
}
