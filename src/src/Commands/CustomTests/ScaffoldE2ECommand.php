<?php

namespace QIT_CLI\Commands\CustomTests;

use QIT_CLI\Commands\QITCommand;
use QIT_CLI\Environment\Environments\EnvInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use function QIT_CLI\normalize_path;

class ScaffoldE2ECommand extends QITCommand {
	protected static $defaultName = 'scaffold:e2e'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected function configure(): void {
		parent::configure();
		$this
			->addArgument( 'path', InputArgument::REQUIRED, 'The path to scaffold an example E2E test.' )
			->addOption( 'with-shared', 's', InputOption::VALUE_NONE, 'Include shared setup examples.' )
			->addOption( 'with-teardown', 't', InputOption::VALUE_NONE, 'Include teardown examples.' )
			->setDescription( 'Scaffold an example E2E test.' );
	}

	protected function doExecute( InputInterface $input, OutputInterface $output, ?EnvInfo $env_info ): int {
		$path = $input->getArgument( 'path' );

		$path_to_generate = normalize_path( $path );

		if ( file_exists( $path_to_generate ) ) {
			if ( ! $this->getHelper( 'question' )->ask( $input, $output, new ConfirmationQuestion( "Directory already exists. Do you want to delete this directory and Scaffold E2E tests in \"$path_to_generate\" anyway? <question>(y/n)</question> ", false ) ) ) {
				return Command::SUCCESS;
			}

			try {
				$this->safely_delete_scaffolded_directory( $path_to_generate );
			} catch ( \Exception $e ) {
				$io = new SymfonyStyle( $input, $output );

				$io->warning( [
					"Could not delete the existing directory: $path_to_generate",
					$e->getMessage(),
				] );

				$output->writeln( '<comment>For safety reasons, only expected files are deleted.</comment>' );
				$output->writeln( '<comment>Please delete the directory "' . $path_to_generate . '" manually and try again.</comment>' );

				return Command::FAILURE;
			}
		}

		if ( file_exists( $path_to_generate ) ) {
			$output->writeln( '<error>Directory already exists: ' . $path_to_generate . '</error>' );

			return Command::FAILURE;
		}

		if ( ! mkdir( $path_to_generate, 0755, true ) ) {
			$output->writeln( '<error>Could not create directory: ' . $path_to_generate . '</error>' );

			return Command::FAILURE;
		}

		// Create basic 'bootstrap' directory.
		if ( ! mkdir( $path_to_generate . '/bootstrap', 0755, true ) ) {
			$output->writeln( '<error>Could not create directory: ' . $path_to_generate . '/bootstrap</error>' );

			return Command::FAILURE;
		}

		// We bootstrap with isolated setup files by default.
		$files = [
			'setup-sh.txt'          => '/bootstrap/setup.sh',
			'setup-js.txt'          => '/bootstrap/setup.js',
			'mu-plugin-php.txt'     => '/bootstrap/mu-plugin.php',
			'dependencies-json.txt' => '/bootstrap/dependencies.json',
			'example-spec-js.txt'   => '/example.spec.js',
		];

		// If the user requests shared setup examples, we include them.
		if ( $input->getOption( 'with-shared' ) ) {
			$files = array_merge( $files, [
				'shared-setup-sh.txt' => '/bootstrap/shared-setup.sh',
				'shared-setup-js.txt' => '/bootstrap/shared-setup.js',
			] );
		}

		// If the user requests teardown examples, include both isolated and shared teardowns.
		if ( $input->getOption( 'with-teardown' ) ) {
			$teardown_files = [
				'teardown-sh.txt' => '/bootstrap/teardown.sh',
				'teardown-js.txt' => '/bootstrap/teardown.js',
			];

			// Add shared teardowns only if shared setups were requested.
			if ( $input->getOption( 'with-shared' ) ) {
				$teardown_files = array_merge( $teardown_files, [
					'shared-teardown-sh.txt' => '/bootstrap/shared-teardown.sh',
					'shared-teardown-js.txt' => '/bootstrap/shared-teardown.js',
				] );
			}

			$files = array_merge( $files, $teardown_files );
		}

		foreach ( $files as $example => $destination ) {
			$result = $this->create_file_from_template( $output, $path_to_generate, $example, $destination );
			if ( $result === Command::FAILURE ) {
				return $result;
			}
		}

		$output->writeln( '<info>Example E2E test generated in: ' . $path_to_generate . '</info>' );
		$output->writeln( "You can now run your first test with <comment>qit run:e2e <your_slug> \"$path_to_generate\" --ui</comment>" );
		$output->writeln( 'You can start writing your tests with codegen: <comment>qit run:e2e --codegen</comment>' );
		$output->writeln( 'And when you are ready, you can publish your tests with <comment>qit test-tags:upload <your_slug> <path_to_test></comment>' );
		$output->writeln( 'Read more about it on our documentation: https://qit.woo.com/docs/custom-tests/generating-tests' );

		return Command::SUCCESS;
	}

	protected function create_file_from_template( OutputInterface $output, string $path_to_generate, string $source, string $destination ): int {
		if ( ! file_put_contents( "$path_to_generate/$destination", file_get_contents( __DIR__ . "/scaffolding/$source" ) ) ) {
			$output->writeln( '<error>Could not create file: ' . basename( $destination ) . '</error>' );

			return Command::FAILURE;
		}

		return Command::SUCCESS;
	}

	/**
	 * Safely deletes a scaffolded directory after performing safety checks.
	 *
	 * @param string $path_to_generate The path to the directory to be safely deleted.
	 *
	 * @throws \RuntimeException If the directory contains unexpected files or directories.
	 */
	protected function safely_delete_scaffolded_directory( string $path_to_generate ): void {
		$expected_files = [
			'./'        => [
				'*.spec.js',
			],
			'bootstrap' => [
				'*.sh',
				'*.php',
				'*.js',
				'dependencies.json',
			],
		];

		if ( ! is_dir( $path_to_generate ) ) {
			throw new \RuntimeException( "$path_to_generate is not a directory" );
		}

		$root_iterator = new \DirectoryIterator( $path_to_generate );

		$has_bootstrap_dir = false;

		foreach ( $root_iterator as $fileinfo ) {
			if ( $fileinfo->isDot() ) {
				continue;
			}

			$filename = $fileinfo->getFilename();

			if ( $fileinfo->isDir() ) {
				if ( $filename === 'bootstrap' ) {
					$has_bootstrap_dir = true;
				} else {
					throw new \RuntimeException( "Unexpected directory '$filename' found in the root directory." );
				}
			} elseif ( $fileinfo->isFile() ) {
				// Check if the file matches any of the expected patterns in './'.
				$matches_expected = false;
				foreach ( $expected_files['./'] as $pattern ) {
					if ( fnmatch( $pattern, $filename ) ) {
						$matches_expected = true;
						break;
					}
				}
				if ( ! $matches_expected ) {
					throw new \RuntimeException( "Unexpected file '$filename' found in the root directory." );
				}
			} else {
				throw new \RuntimeException( "Unexpected item '$filename' found in the root directory." );
			}
		}

		// If 'bootstrap' directory exists, check its contents.
		if ( $has_bootstrap_dir ) {
			$bootstrap_path = $path_to_generate . DIRECTORY_SEPARATOR . 'bootstrap';
			if ( ! is_dir( $bootstrap_path ) ) {
				throw new \RuntimeException( "'bootstrap' exists but is not a directory." );
			}

			$bootstrap_iterator = new \DirectoryIterator( $bootstrap_path );

			// Iterate over bootstrap directory.
			foreach ( $bootstrap_iterator as $fileinfo ) {
				if ( $fileinfo->isDot() ) {
					continue;
				}

				$filename = $fileinfo->getFilename();

				if ( $fileinfo->isDir() ) {
					throw new \RuntimeException( "Unexpected directory '$filename' found in the 'bootstrap' directory." );
				} elseif ( $fileinfo->isFile() ) {
					// Check if the file matches any of the expected patterns in 'bootstrap'.
					$matches_expected = false;
					foreach ( $expected_files['bootstrap'] as $pattern ) {
						if ( fnmatch( $pattern, $filename ) ) {
							$matches_expected = true;
							break;
						}
					}
					if ( ! $matches_expected ) {
						throw new \RuntimeException( "Unexpected file '$filename' found in the 'bootstrap' directory." );
					}
				} else {
					throw new \RuntimeException( "Unexpected item '$filename' found in the 'bootstrap' directory." );
				}
			}
		}

		// All safety checks passed, proceed to delete the directory.
		$filesystem = new Filesystem();

		try {
			$filesystem->remove( $path_to_generate );
		} catch ( \Exception $exception ) {
			throw new \RuntimeException( "An error occurred while deleting '$path_to_generate': " . $exception->getMessage() );
		}
	}
}
