<?php

namespace QIT_CLI\Commands\CustomTests;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use function QIT_CLI\normalize_path;

class ScaffoldE2ECommand extends Command {
	protected static $defaultName = 'scaffold:e2e'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected function configure() {
		$this
			->addArgument( 'path', InputArgument::REQUIRED, 'The path to scaffold an example E2E test.' )
			->addOption( 'advanced', 'f', InputOption::VALUE_NONE, 'Include advanced scaffolding.' )
			->setDescription( 'Scaffold an example E2E test.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$path = $input->getArgument( 'path' );

		$path_to_generate = normalize_path( $path );

		if ( file_exists( $path_to_generate ) ) {
			if ( ! $this->getHelper( 'question' )->ask( $input, $output, new ConfirmationQuestion( "Directory already exists. Scaffold E2E tests in \"$path_to_generate\" anyway? <question>(y/n)</question> ", false ) ) ) {
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

		// Create basic 'qit' directory.
		if ( ! mkdir( $path_to_generate . '/qit', 0755, true ) ) {
			$output->writeln( '<error>Could not create directory: ' . $path_to_generate . '/qit</error>' );

			return Command::FAILURE;
		}

		// We bootstrap with these files by default.
		$files = [
			'sharedSetup-sh.txt'     => '/qit/sharedSetup.sh',
			'sharedSetup-js.txt'     => '/qit/sharedSetup.js',

			'isolatedSetup-sh.txt'   => '/qit/isolatedSetup.sh',
			'isolatedSetup-js.txt'   => '/qit/isolatedSetup.js',

			'mu-plugin-php.txt'      => '/qit/mu-plugin.php',
			'dependencies-json.txt'  => '/qit/dependencies.json',
			'example-spec-js.txt'    => '/example.spec.js',
		];

		// If the user requests the advanced bootstrapping, we include every possibility.
		if ( $input->getOption( 'advanced' ) ) {
			$files = array_merge( $files, [
				'sharedSetup-php.txt' => '/qit/sharedSetup.php',

				'isolatedSetup-php.txt' => '/qit/isolatedSetup.php',

				'sharedTeardown-sh.txt'  => '/qit/sharedTeardown.sh',
				'sharedTeardown-php.txt' => '/qit/sharedTeardown.php',
				'sharedTeardown-js.txt'  => '/qit/sharedTeardown.js',

				'isolatedTeardown-sh.txt'  => '/qit/isolatedTeardown.sh',
				'isolatedTeardown-php.txt' => '/qit/isolatedTeardown.php',
				'isolatedTeardown-js.txt'  => '/qit/isolatedTeardown.js',
			] );
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

	function create_file_from_template( OutputInterface $output, string $path_to_generate, string $source, string $destination ): int {
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
