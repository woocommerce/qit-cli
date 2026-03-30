<?php
declare( strict_types=1 );

namespace QIT_CLI\Commands\Environment;

use QIT_CLI\App;
use QIT_CLI\Commands\QITCommand;
use QIT_CLI\Environment\EnvironmentMonitor;
use QIT_CLI\Environment\Environments\E2E\E2EEnvironment;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\Environments\Performance\PerformanceEnvironment;
use QIT_CLI\Environment\Environments\Performance\PerformanceEnvInfo;
use QIT_CLI\Environment\Environments\QITEnvInfo;
use QIT_CLI\Environment\Infra\InfraProviderFactory;
use QIT_CLI\PreCommand\Configuration\CliConfigParser;
use QIT_CLI\PreCommand\Configuration\ConfigMerger;
use QIT_CLI\QITInput;
use QIT_CLI\Tunnel\TunnelRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use QIT_CLI\Utils\PackageReferenceUtils;
use function QIT_CLI\is_windows;

/**
 * Qit env:up  – create a disposable local E2E environment.
 */
class UpEnvironmentCommand extends QITCommand {
	/** @var E2EEnvironment */
	private E2EEnvironment $e2e_environment;
	/** @var PerformanceEnvironment */
	private PerformanceEnvironment $performance_environment;
	/** @var TunnelRunner */
	private TunnelRunner $tunnel_runner;
	/** @var \QIT_CLI\PreCommand\Extensions\VersionResolver */
	private \QIT_CLI\PreCommand\Extensions\VersionResolver $version_resolver;
	/** @var \QIT_CLI\Environment\EnvironmentVars */
	private \QIT_CLI\Environment\EnvironmentVars $environment_vars;
	/** @var EnvironmentMonitor */
	private EnvironmentMonitor $environment_monitor;

	protected static $defaultName = 'env:up'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	public function __construct( E2EEnvironment $e2e_environment, PerformanceEnvironment $performance_environment, TunnelRunner $tunnel_runner, \QIT_CLI\PreCommand\Extensions\VersionResolver $version_resolver, \QIT_CLI\Environment\EnvironmentVars $environment_vars, EnvironmentMonitor $environment_monitor ) {
		$this->e2e_environment         = $e2e_environment;
		$this->performance_environment = $performance_environment;
		$this->tunnel_runner           = $tunnel_runner;
		$this->version_resolver        = $version_resolver;
		$this->environment_vars        = $environment_vars;
		$this->environment_monitor     = $environment_monitor;
		parent::__construct();
	}

	/*******************************************************************
	 * CLI definition
	 ******************************************************************/
	protected function configure(): void {
		parent::configure(); // adds --config and --environment

		$this->setDescription( 'Creates a temporary local test environment that is completely ephemeral' )
			->setAliases( [ 'env:start' ] )
			/* ─ Environment selection ─ */
			->addOption(
				'environment', 'e',
				InputOption::VALUE_OPTIONAL,
				'Pick an <comment>environment block</comment> from qit.json (e.g. --environment=legacy)',
				'default'
			)
			->addOption( 'environment_type', null, InputOption::VALUE_OPTIONAL, 'The type of environment to create. Valid values: "e2e", "performance".', 'e2e' )
			/* ─ Runtime env‑vars ─ */
			->addOption( 'env', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Set env var  --env KEY=VAL', [] )
			->addOption( 'env_file', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Load vars from file  --env_file ./prod.env', [] )
			/* ─ Scalars ─ */
			->addOption( 'php_version', null, InputOption::VALUE_OPTIONAL, 'PHP version (e.g., 8.2, 8.3). Alias: --php', '8.2' )
			->addOption( 'wordpress_version', null, InputOption::VALUE_OPTIONAL, 'WordPress version (stable, rc, 6.6). Alias: --wp', 'stable' )
			->addOption( 'woocommerce_version', null, InputOption::VALUE_OPTIONAL, 'WooCommerce version. Alias: --woo', null )
			->addOption( 'object_cache', 'o', InputOption::VALUE_NONE, 'Enable Redis object cache' )
			/* ─ Lists ─ */
			->addOption( 'plugin', 'p', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Additional plugins', [] )
			->addOption( 'theme', 't', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Additional themes', [] )
			->addOption( 'test-package', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Test packages to set up environment from (processes requirements and runs setup phases)', [] )
			->addOption( 'utility', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Utility packages for environment setup (local paths or registry references)', [] )
			->addOption( 'volume', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Volumes (host:container)', [] )
			->addOption( 'php_extension', 'x', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'PHP extensions', [] )
			/* ─ Misc ─ */
			->addOption( 'tunnel', null, InputOption::VALUE_OPTIONAL, 'Enable tunnelling (cloudflare, ngrok)', 'no_tunnel' )
			->addOption( 'offline', null, InputOption::VALUE_NONE, 'Override: Force offline mode - will error if any test requires network' )
			->addOption( 'online', null, InputOption::VALUE_NONE, 'Override: Force online mode - enable network regardless of test requirements' )
			->addOption( 'skip-setup', null, InputOption::VALUE_NONE, 'Skip running setup phases even if qit-test.json is found' )
			->addOption( 'setup', null, InputOption::VALUE_OPTIONAL, 'Run setup phases from test package in specified directory', false )
			->addOption( 'skip-test-phases', null, InputOption::VALUE_NONE, 'Skip all test phases (internal use by run:e2e)' )
			->addOption( 'global-setup', null, InputOption::VALUE_NONE, 'Run globalSetup and setup phases without executing tests. Environment stays running for development.' )
			->addOption( 'skip_activating_plugins', null, InputOption::VALUE_NONE, 'Skip activating plugins during environment setup' )
			->addOption( 'skip_activating_themes', null, InputOption::VALUE_NONE, 'Skip activating themes during environment setup' )
			->addOption( 'json', 'j', InputOption::VALUE_NONE, 'Machine‑readable JSON output' )
			->addOption( 'provider', null, InputOption::VALUE_OPTIONAL, 'Infrastructure provider (currently only "local" is available)', InfraProviderFactory::PROVIDER_LOCAL )
			->setHelp( $this->getHelpText() );
	}

	/*******************************************************************
	 * Execution
	 ******************************************************************/
	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		/** @var \QIT_CLI\QITInput $input */

		/* ─ Safety guard ─ */
		if ( is_windows() ) {
			$output->writeln( '<comment>QIT environments require WSL on Windows.</comment>' );

			return Command::FAILURE;
		}

		/* ─ Validate provider type ─ */
		$provider_type = $input->getOption( 'provider' );
		if ( ! in_array( $provider_type, InfraProviderFactory::ALLOWED_TYPES, true ) ) {
			$error_message = sprintf( 'Invalid provider "%s". Must be one of: %s.', $provider_type, implode( ', ', InfraProviderFactory::ALLOWED_TYPES ) );
			if ( $input->getOption( 'json' ) ) {
				$output->writeln( json_encode( [
					'error'   => true,
					'message' => $error_message,
				] ) );
			} else {
				$output->writeln( '<error>' . $error_message . '</error>' );
			}
			return Command::FAILURE;
		}

		/*
		─ Display experimental warning when using qit.json ─
		*/
		// Check if we're using a qit.json file (either via --config or auto-detected)
		$config_file = $input->getOption( 'config' );
		if ( $config_file === null && file_exists( getcwd() . '/qit.json' ) ) {
			$config_file = getcwd() . '/qit.json';
		}

		// Show warning only when qit.json is being used and not in JSON output mode
		if ( $config_file !== null && ! $input->getOption( 'json' ) ) {
			$output->writeln( '<comment>[EXPERIMENTAL]</comment> Using qit.json - this feature is highly experimental. Please report any issues or feedback at https://github.com/woocommerce/qit-cli/issues' );
			$output->writeln( '' );
		}

		/*
		 * ─ 0. Check for test packages and process requirements ─
		 */
		// This must happen BEFORE environment creation so requirements can be included

		// Build ordered list of test packages respecting command-line order
		$all_test_packages = [];

		// Get explicit test packages from --test-package option FIRST
		$explicit_packages = $input->getOption( 'test-package' ) ?? [];

		// Expand relative paths early (especially './' and '.') to absolute paths
		// This ensures consistent handling throughout the system
		if ( ! empty( $explicit_packages ) ) {
			foreach ( $explicit_packages as &$package ) {
				// Skip if empty
				if ( empty( $package ) ) {
					continue;
				}

				// Skip absolute paths (already absolute)
				if ( substr( $package, 0, 1 ) === '/' ) {
					continue;
				}

				// Check if it's a relative path starting with . (includes ./, ., ../, etc.)
				if ( substr( $package, 0, 1 ) === '.' ) {
					// Try realpath first, fall back to manual construction if it fails
					$resolved = @realpath( $package );
					if ( $resolved === false ) {
						// realpath failed (possibly due to permissions or non-existent path)
						// For simple . and ./, use getcwd
						if ( $package === '.' || $package === './' ) {
							$package = getcwd();
						} else {
							// For other relative paths, construct manually
							// This preserves the relative path structure even if intermediate dirs don't exist
							$package = getcwd() . '/' . $package;
						}
					} else {
						$package = $resolved;
					}
				} else {
					// For other paths (not starting with . or /), try to resolve as local path
					// If it exists locally, expand it; otherwise assume it's a package reference
					$resolved = @realpath( $package );
					if ( $resolved !== false ) {
						// Path exists locally, use the resolved absolute path
						$package = $resolved;
					}
					// If realpath fails, leave it unchanged (likely a package reference like vendor/package:1.0.0)
				}
			}
			unset( $package ); // Break the reference
		}

		// Check for local test manifest
		$local_test_dir     = null;
		$has_local_manifest = false;
		if ( ! $input->getOption( 'skip-setup' ) ) {
			$setup_dir = $input->getOption( 'setup' );
			$test_dir  = $setup_dir ? $setup_dir : getcwd();
			$test_dir  = rtrim( $test_dir, '/' );
			if ( substr( $test_dir, 0, 1 ) !== '/' ) {
				$test_dir = getcwd() . '/' . $test_dir;
			}

			if ( file_exists( $test_dir . '/qit-test.json' ) ) {
				$has_local_manifest = true;
				$local_test_dir     = $test_dir;
			}
		}

		// Add packages in the right order:
		// 1. Explicit packages in command-line order
		// 2. Local directory last (if it has qit-test.json and wasn't already in explicit list)
		if ( ! empty( $explicit_packages ) ) {
			$all_test_packages = $explicit_packages;
			$this->processTestPackageRequirements( $explicit_packages, $output );
		}

		// Add local directory last if it exists and wasn't explicitly specified
		if ( $has_local_manifest && ! in_array( $local_test_dir, $all_test_packages, true ) ) {
			// Check if any explicit package points to the same directory (different path representations)
			$local_realpath = realpath( $local_test_dir );
			$already_added  = false;
			foreach ( $all_test_packages as $pkg ) {
				if ( is_dir( $pkg ) && realpath( $pkg ) === $local_realpath ) {
					$already_added = true;
					break;
				}
			}

			if ( ! $already_added ) {
				$all_test_packages[] = $local_test_dir;
				// Process requirements for local manifest
				$this->processTestPackageRequirements( [ $local_test_dir ], $output );
			}
		}

		// Check for duplicate test packages (same package in both local auto-detect and explicit --test-package)
		if ( $has_local_manifest && ! empty( $explicit_packages ) ) {
			// Read the local package ID using utility
			$local_package_id = PackageReferenceUtils::read_local_package_id( $local_test_dir );

			if ( $local_package_id ) {
				// Check if any explicit package matches the local package ID
				foreach ( $explicit_packages as $pkg ) {
					// Extract package ID using utility (returns null for local paths)
					$pkg_id = PackageReferenceUtils::extract_package_id( $pkg );

					if ( $pkg_id === $local_package_id ) {
							// Found duplicate - prepare error message
							$error_message = "Duplicate test package '{$local_package_id}' detected.\n" .
								"\n" .
								"You are running from a directory containing this test package,\n" .
								"and also explicitly specified it via --test-package.\n" .
								"\n" .
								"Conflicting sources:\n" .
								"  - {$pkg}\n" .
								"  - {$local_test_dir}\n" .
								"\n" .
								"To fix this, either:\n" .
								"  1. Run from a different directory, OR\n" .
								"  2. Don't specify --test-package (use local version)";

							// In JSON mode, output JSON error and exit cleanly
						if ( $input->getOption( 'json' ) ) {
							$output->write( json_encode( [
								'error'   => 'duplicate_package',
								'message' => $error_message,
								'env_id'  => null, // EnvironmentRunner expects env_id
							] ) );
							return Command::FAILURE;
						}

							// In normal mode, throw exception
							throw new \RuntimeException( $error_message );
					}
				}
			}
		}

		// Debug logging (skip in JSON mode to avoid filter issues)
		if ( ! $input->getOption( 'json' ) && ( $output->isVeryVerbose() || getenv( 'QIT_DEBUG' ) ) ) {
			$output->writeln( '[DEBUG] env:up - Has local manifest: ' . ( $has_local_manifest ? 'yes' : 'no' ) );
			$output->writeln( '[DEBUG] env:up - Explicit packages from --test-package: ' . json_encode( $explicit_packages ) );
			$output->writeln( '[DEBUG] env:up - All test packages to process: ' . json_encode( $all_test_packages ) );
		}

		/* ─ 1. Build the *final* env config (config‑file ⊕ CLI) ─ */
		$env_name         = $input->getOption( 'environment' ) ?? 'default';
		$environment_type = $input->getOption( 'environment_type' ) ?? 'e2e';

		// Parse → Merge → Resolve: the three-step pipeline.
		$env_config  = $this->get_environment_config( $env_name );
		$cli_overlay = CliConfigParser::parse( $input );
		$env_config  = ConfigMerger::merge( $env_config, $cli_overlay );

		// Post-merge resolution steps.
		$env_config = $this->resolveWooCommerceVersion( $env_config );
		$env_config = $this->resolveWordPressVersion( $env_config );
		$env_config = $this->resolveEnvVars( $env_config, $input );

		// Default network mode to 'auto' if not explicitly set.
		if ( ! isset( $env_config['network_mode'] ) ) {
			$env_config['network_mode'] = 'auto';
		}

		/*
		─ 1.05. Merge utility packages from environment and CLI ─
		*/
		// Check for 'utilities' (preferred) and 'global_setup' (legacy) fields from config
		$utility_packages = [];
		if ( isset( $env_config['utilities'] ) && is_array( $env_config['utilities'] ) ) {
			$utility_packages = $env_config['utilities'];
		}
		if ( isset( $env_config['global_setup'] ) && is_array( $env_config['global_setup'] ) ) {
			$utility_packages = array_merge( $utility_packages, $env_config['global_setup'] );
		}

		// Merge CLI utilities (--utility flag)
		$cli_utilities = $input->getOption( 'utility' ) ?? [];
		if ( ! empty( $cli_utilities ) ) {
			$utility_packages = array_merge( $utility_packages, $cli_utilities );
		}

		foreach ( $utility_packages as $package_ref ) {
			// Add to beginning so utilities run first
			if ( ! in_array( $package_ref, $all_test_packages, true ) ) {
				array_unshift( $all_test_packages, $package_ref );
			}
		}

		// Process requirements from utility packages (plugins, themes, secrets, etc.)
		if ( ! empty( $utility_packages ) ) {
			$this->processTestPackageRequirements( $utility_packages, $output );
		}

		/* ─ 1.1. Add SUT as a plugin/theme if defined in qit.json ─ */
		$sut = $this->get_resolved_sut();
		if ( $sut !== null && isset( $sut['type'] ) && isset( $sut['slug'] ) && isset( $sut['source'] ) ) {
			// Convert SUT to extension format and add to the appropriate list
			$sut_extension = $this->convert_sut_to_extension( $sut );

			if ( $sut['type'] === 'plugin' ) {
				$env_config['plugins'] = $env_config['plugins'] ?? [];
				// Check if the SUT plugin is not already in the list
				$sut_exists = false;
				foreach ( $env_config['plugins'] as $plugin ) {
					$slug = is_string( $plugin ) ? $plugin : ( $plugin['slug'] ?? null );
					if ( $slug === $sut['slug'] ) {
						$sut_exists = true;
						break;
					}
				}
				if ( ! $sut_exists ) {
					// Add SUT as the first plugin so it takes precedence
					array_unshift( $env_config['plugins'], $sut_extension );
				}
			} elseif ( $sut['type'] === 'theme' ) {
				$env_config['themes'] = $env_config['themes'] ?? [];
				// Check if the SUT theme is not already in the list
				$sut_exists = false;
				foreach ( $env_config['themes'] as $theme ) {
					$slug = is_string( $theme ) ? $theme : ( $theme['slug'] ?? null );
					if ( $slug === $sut['slug'] ) {
						$sut_exists = true;
						break;
					}
				}
				if ( ! $sut_exists ) {
					// Add SUT as the first theme so it takes precedence
					array_unshift( $env_config['themes'], $sut_extension );
				}
			}
		}

		/* ─ 1.5. Process test package requirements and add missing dependencies ─ */
		$this->process_test_package_requirements( $env_config, $input, $output );

		/* ─ 2. Resolve extensions using the merged config (includes CLI overrides) ─ */
		$resolved_ext = $this->download_extensions_from_config( $env_config, $environment_type );

		/* ─ 3. Use the fully-resolved extension lists ─ */
		$final_plugins = $resolved_ext->get_plugins();
		$final_themes  = $resolved_ext->get_themes();

		// Convert Extension objects to arrays and extract resolved versions
		$plugin_arrays = [];
		$sut_slug      = $sut['slug'] ?? App::getVar( 'QIT_SUT_SLUG' );

		foreach ( $final_plugins as $plugin ) {
			$plugin_arrays[] = $plugin->jsonSerialize();

			if ( $plugin->slug === 'woocommerce' && ! empty( $plugin->version ) ) {
				$env_config['woocommerce_version'] = $plugin->version;
			}
			if ( $sut_slug && $plugin->slug === $sut_slug && ! empty( $plugin->version ) ) {
				$env_config['sut']            = $env_config['sut'] ?? [];
				$env_config['sut']['version'] = $plugin->version;
			}
		}

		$theme_arrays = [];
		foreach ( $final_themes as $theme ) {
			$theme_arrays[] = $theme->jsonSerialize();

			if ( $sut_slug && $theme->slug === $sut_slug && ! empty( $theme->version ) ) {
				$env_config['sut']            = $env_config['sut'] ?? [];
				$env_config['sut']['version'] = $theme->version;
			}
		}

		/* ─ 3.5. Parse volumes to get proper associative array structure ─ */
		$parsed_volumes = [];
		if ( ! empty( $env_config['volumes'] ) ) {
			$parsed_volumes = \QIT_CLI\App::make( \QIT_CLI\Environment\EnvVolumeParser::class )->parse_volumes( $env_config['volumes'] );
		}

		/*
		─ 3.6. Determine network restriction based on network_mode ─
		*/
		// Process test packages if in auto mode
		$requires_network = false;

		// Collect network requirements from all sources
		$packages_requiring_network = [];
		$packages_requiring_tunnel  = [];

		// Check if test packages require network (from processTestPackageRequirements)
		if ( \QIT_CLI\App::getVar( 'test_package_requires_network', false ) ) {
			$requires_network             = true;
			$packages_requiring_network[] = 'local test package';
		}

		// Check if test packages require tunnel (from processTestPackageRequirements)
		if ( \QIT_CLI\App::getVar( 'test_package_requires_tunnel', false ) ) {
			$packages_requiring_tunnel[] = 'local test package';
			// Enable tunnel if required by test package
			if ( ! isset( $env_config['tunnel'] ) || ! $env_config['tunnel'] ) {
				$env_config['tunnel']      = true;
				$env_config['tunnel_type'] = 'auto'; // Let system choose appropriate tunnel type
				if ( $output->isVerbose() ) {
					$output->writeln( '<info>Tunnel enabled (required by test package)</info>' );
				}
			}
		}

		if ( isset( $env_config['network_mode'] ) && $env_config['network_mode'] === 'auto' ) {
			// Check if test packages were passed directly or in environment config
			$test_packages = [];

			// First check if test packages are in the environment config
			if ( isset( $env_config['test_packages'] ) ) {
				$test_packages = (array) $env_config['test_packages'];
			}

			// Download and check manifests
			if ( ! empty( $test_packages ) ) {
				$package_downloader = \QIT_CLI\App::make( \QIT_CLI\PreCommand\Download\TestPackageDownloader::class );
				foreach ( $test_packages as $package_ref ) {
					try {
						$manifest = $package_downloader->download_single( $package_ref, sys_get_temp_dir() . '/qit-cache' );
						if ( $manifest ) {
							if ( $manifest->requires_network() ) {
								$requires_network             = true;
								$packages_requiring_network[] = $package_ref;
								if ( $output->isVerbose() ) {
									$output->writeln( "<info>Package {$package_ref} requires network access</info>" );
								}
							}
							if ( $manifest->requires_tunnel() ) {
								$packages_requiring_tunnel[] = $package_ref;
								if ( $output->isVerbose() ) {
									$output->writeln( "<info>Package {$package_ref} requires tunnel access</info>" );
								}
							}
						}
					} catch ( \Exception $e ) {
						// For local packages, try to read the manifest directly
						if ( is_dir( $package_ref ) && file_exists( $package_ref . '/qit-test.json' ) ) {
							$manifest_data = json_decode( file_get_contents( $package_ref . '/qit-test.json' ), true );
							if ( isset( $manifest_data['requires']['network'] ) && $manifest_data['requires']['network'] ) {
								$requires_network             = true;
								$packages_requiring_network[] = $package_ref;
								if ( $output->isVerbose() ) {
									$output->writeln( "<info>Package {$package_ref} requires network access</info>" );
								}
							}
							if ( isset( $manifest_data['requires']['tunnel'] ) && $manifest_data['requires']['tunnel'] ) {
								$packages_requiring_tunnel[] = $package_ref;
								if ( $output->isVerbose() ) {
									$output->writeln( "<info>Package {$package_ref} requires tunnel access</info>" );
								}
							}
						} else {
							// Remote package failed to download - fail fast
							throw new \RuntimeException( "Failed to download test package '{$package_ref}': " . $e->getMessage() );
						}
					}
				}
			}

			// Apply tunnel requirements if any
			if ( ! empty( $packages_requiring_tunnel ) && ( ! isset( $env_config['tunnel'] ) || ! $env_config['tunnel'] ) ) {
				$env_config['tunnel']      = true;
				$env_config['tunnel_type'] = 'auto'; // Let system choose appropriate tunnel type
				if ( $output->isVerbose() ) {
					$output->writeln( sprintf( '<info>Tunnel enabled (required by %d test package(s))</info>', count( $packages_requiring_tunnel ) ) );
				}
			}

			// Set network restriction based on package requirements
			$env_config['network_restriction'] = ! $requires_network;
		} elseif ( isset( $env_config['network_mode'] ) ) {
			// For explicit modes, validate and convert
			if ( $env_config['network_mode'] === 'offline' && $requires_network ) {
				// Error: conflict between forced offline and package requirements
				$package_list = implode( "\n  - ", $packages_requiring_network );
				throw new \RuntimeException( sprintf(
					"Cannot run in offline mode.\n" .
					"%d package(s) require network access:\n  - %s\n\n" .
					"Options:\n" .
					"1. Remove --offline flag to use auto mode\n" .
					"2. Use --online flag to force network access\n" .
					'3. Exclude these test packages from the run',
					count( $packages_requiring_network ),
					$package_list
				) );
			}
			$env_config['network_restriction'] = $env_config['network_mode'] === 'offline';
		} else {
			// Default to restricted (offline) unless overridden by test package
			$env_config['network_restriction'] = ! $requires_network;
		}

		/* ─ 3.7. Process test packages for setup phases ─ */
		$test_packages_for_setup = [];
		// Always process test packages (for requirement extraction and volume mounting)
		// The actual phase execution is controlled by the skip_test_phases flag in env_info
		if ( ! empty( $all_test_packages ) ) {
			if ( $output->isVeryVerbose() || getenv( 'QIT_DEBUG' ) ) {
				$output->writeln( '[DEBUG] env:up - Preparing ' . count( $all_test_packages ) . ' packages for setup phases' );
			}

			// Determine main package using priority rules
			$main_package = $this->determineMainPackage( $all_test_packages, $output );
			if ( $output->isVeryVerbose() || getenv( 'QIT_DEBUG' ) ) {
				$output->writeln( '[DEBUG] env:up - Main package determined: ' . ( $main_package ?: 'none' ) );
			}

			// Download and prepare test packages for setup
			// Note: E2EEnvironment will run globalSetup for ALL packages and setup for FIRST (main) package
			$package_downloader = \QIT_CLI\App::make( \QIT_CLI\PreCommand\Download\TestPackageDownloader::class );

			// Use the packages in their already-determined order
			// (they're already ordered correctly: explicit packages first, then local if auto-detected)
			$packages_to_process = $all_test_packages;

			foreach ( $packages_to_process as $package_ref ) {
				try {
					// For local packages, read the manifest to get the package ID
					if ( is_dir( $package_ref ) && file_exists( $package_ref . '/qit-test.json' ) ) {
						// Read manifest to get the package ID
						$manifest_content = file_get_contents( $package_ref . '/qit-test.json' );
						$manifest_data    = json_decode( $manifest_content, true );

						if ( json_last_error() !== JSON_ERROR_NONE ) {
							throw new \RuntimeException( 'Invalid JSON in qit-test.json: ' . json_last_error_msg() );
						}

						// Package ID is required field in the manifest
						if ( ! isset( $manifest_data['package'] ) ) {
							throw new \RuntimeException( "Missing required 'package' field in {$package_ref}/qit-test.json" );
						}

						$package_id = $manifest_data['package'];

						// Create manifest object to get consistent container path
						$manifest                                = new \QIT_CLI\PreCommand\Objects\TestPackageManifest( $manifest_data );
						$container_name                          = $manifest->get_container_directory_name();
						$container_path                          = '/qit/packages/' . $container_name;
						$test_packages_for_setup[ $package_ref ] = [
							'path'           => $package_ref,
							'source'         => 'local',
							'container_path' => $container_path,
							'package_id'     => $package_id,  // Store the actual package ID
							'manifest'       => $manifest_data,  // Store manifest for run:e2e
						];
					} else {
						// Remote package - download it
						$manifest = $package_downloader->download_single( $package_ref, sys_get_temp_dir() . '/qit-cache' );
						if ( $manifest ) {
							$metadata = $package_downloader->get_package_metadata( $package_ref );

							// For subpackages, use the parent's container path since they share the same files
							if ( $manifest->is_subpackage() && $manifest->get_parent_package() ) {
								// Generate container path based on parent package
								$parent_ref     = $manifest->get_parent_package() . ':' . explode( ':', $package_ref )[1];
								$container_path = \QIT_CLI\PreCommand\Objects\TestPackageManifest::create_container_path( $parent_ref );
							} else {
								// Use normal container path for regular packages
								$container_path = \QIT_CLI\PreCommand\Objects\TestPackageManifest::create_container_path( $package_ref );
							}

							$test_packages_for_setup[ $package_ref ] = [
								'path'           => $metadata['downloaded_path'] ?? '',
								'source'         => 'registry',
								'container_path' => $container_path,
								'package_id'     => $manifest->get_package_id(),  // Get package ID from manifest
								'manifest'       => $manifest->jsonSerialize(),  // Store manifest for run:e2e
							];
						}
					}
				} catch ( \Exception $e ) {
					// If it's a security error (invalid secrets format), we should fail immediately
					if ( strpos( $e->getMessage(), 'Invalid secrets format' ) !== false ) {
						throw $e;  // Re-throw security errors
					}

					// Check if this is a missing version error
					if ( strpos( $e->getMessage(), 'missing a version number' ) !== false ||
						strpos( $e->getMessage(), 'must include a version number' ) !== false ) {
						// For JSON output, format the error properly
						if ( $input->getOption( 'json' ) ) {
							$output->writeln( json_encode( [
								'error'   => true,
								'message' => $e->getMessage(),
							] ) );
							return Command::FAILURE;
						}
						// Don't wrap the message, it's already clear
						throw new \RuntimeException( $e->getMessage() );
					}

					// Fail fast for test package errors - no point continuing if packages are missing
					throw new \RuntimeException( "Failed to prepare test package '{$package_ref}': " . $e->getMessage() );
				}
			}
		}

		/* ─ 3.75. Validate utility packages don't have run phase ─ */
		if ( ! empty( $utility_packages ) ) {
			$invalid_utilities = [];
			foreach ( $utility_packages as $package_ref ) {
				// Find this package in test_packages_for_setup
				if ( isset( $test_packages_for_setup[ $package_ref ] ) ) {
					$manifest_data = $test_packages_for_setup[ $package_ref ]['manifest'] ?? null;
					if ( $manifest_data && isset( $manifest_data['test']['phases']['run'] ) && ! empty( $manifest_data['test']['phases']['run'] ) ) {
						$package_id          = $manifest_data['package'] ?? $package_ref;
						$invalid_utilities[] = $package_id;
					}
				}
			}

			if ( ! empty( $invalid_utilities ) ) {
				$package_list = implode( "\n  - ", $invalid_utilities );
				throw new \RuntimeException(
					"Validation Error: Packages in 'utilities' or 'global_setup' must NOT have a 'run' phase.\n\n" .
					"The following packages have a run phase and cannot be used as utilities:\n  - {$package_list}\n\n" .
					"To fix this:\n" .
					"1. Remove the 'run' phase from these packages to make them utilities, OR\n" .
					"2. Move them to 'test_packages' in your test configuration, OR\n" .
					"3. Remove them from the 'utilities'/'global_setup' array\n\n" .
					'Utility packages are for environment setup only and should not execute tests.'
				);
			}
		}

		/* ─ 3.8. Add test package volumes for local and registry packages ─ */
		foreach ( $test_packages_for_setup as $package_ref => $info ) {
			// Map test packages to their container paths
			// All test packages should have both 'path' and 'container_path' set
			if ( ! empty( $info['path'] ) && ! empty( $info['container_path'] ) ) {
				// Add volume mapping for test package
				// We need to add this to parsed_volumes, not env_config['volumes']
				$parsed_volumes[ $info['container_path'] ] = $info['path'];
			}
		}

		/*
		─ 4. Materialise EnvInfo DTO ─
		*/

		$env_info_class = $this->get_env_info_class( $environment_type );

		/** @var E2EEnvInfo|PerformanceEnvInfo $env_info */
		$env_info = $env_info_class::from_array( [
			'env_id'                  => 'qitenv' . bin2hex( random_bytes( 8 ) ),
			'environment'             => $environment_type,
			'provider_type'           => $provider_type,
			'php_version'             => $env_config['php_version'] ?? '8.2',
			'wordpress_version'       => $env_config['wordpress_version'] ?? 'stable',
			'woocommerce_version'     => $env_config['woocommerce_version'] ?? '',
			'sut'                     => $env_config['sut'] ?? [],
			'object_cache'            => $env_config['object_cache'] ?? false,
			'plugins'                 => $plugin_arrays,
			'themes'                  => $theme_arrays,
			'php_extensions'          => $env_config['php_extensions'] ?? [],
			'volumes'                 => $parsed_volumes,
			'envs'                    => $env_config['envs'] ?? [],
			'test_packages_for_setup' => $test_packages_for_setup,
			'skip_test_phases'        => $input->getOption( 'skip-test-phases' ),  // Pass the flag to E2EEnvironment
			'global_setup_only'       => $input->getOption( 'global-setup' ),  // Run only globalSetup and setup phases
			'skip_activating_plugins' => $input->getOption( 'skip_activating_plugins' ),
			'skip_activating_themes'  => $input->getOption( 'skip_activating_themes' ),
			'tunnel'                  => $env_config['tunnel'] ?? false,
			'tunnel_type'             => $env_config['tunnel_type'] ?? 'no_tunnel',
			'network_restriction'     => $env_config['network_restriction'],
			'site_url'                => 'http://localhost:8080',
		] );

		/* ─ 5. Add QIT_ENV_ID and QIT_NETWORK_RESTRICTION to environment variables ─ */
		$env_info->envs['QIT_ENV_ID']              = $env_info->env_id;
		$env_info->envs['QIT_NETWORK_RESTRICTION'] = $env_info->network_restriction ? 'true' : 'false';

		/* ─ 5.5. Set up EnvironmentManager with secrets for Docker container ─ */
		$required_secrets = \QIT_CLI\App::getVar( 'test_package_required_secrets', [] );
		if ( ! empty( $required_secrets ) ) {
			$env_manager = new \QIT_CLI\Environment\EnvironmentManager();
			// Initialize with empty CLI vars (they're already in $env_info->envs) and required secrets
			$env_manager->initialize( [], [], array_keys( $required_secrets ) );

			// Store in App container for E2EEnvironment to retrieve
			\QIT_CLI\App::setVar( 'environment_manager', $env_manager );

			// Set environment manager on Docker so secrets are passed to container
			$docker = \QIT_CLI\App::make( \QIT_CLI\Environment\Docker::class );
			$docker->set_environment_manager( $env_manager );

			if ( $output->isVerbose() ) {
				$output->writeln( '<info>✓ Secrets will be passed to container environment</info>' );
			}
		}

		/* ─ 6. SELF‑TEST shortcut ─ */
		if ( getenv( 'QIT_SELF_TEST' ) === 'env_up' ) {
			$output->writeln( json_encode( $env_info, JSON_UNESCAPED_SLASHES ) );

			return Command::SUCCESS;
		}

		/* ─ 7. Honour --tunnel (validated against TunnelRunner) ─ */
		if ( $env_info->tunnel_type !== 'no_tunnel' ) {
			$this->tunnel_runner->check_tunnel_support( $env_info->tunnel_type );
			$env_info->tunnel = true;
		}

		/* ─ 8. Bring the environment up ─ */
		$environment = $this->get_environment( $environment_type );
		$environment->init( $env_info );
		$environment->up();

		/*
		 * ─ 8.5. Setup phases are now handled by E2EEnvironment::up() when test_packages_for_setup is populated ─
		 */
		// The E2EEnvironment will automatically run:
		// - globalSetup for ALL packages in test_packages_for_setup
		// - setup for the FIRST (main) package only
		// This happens when --skip-test-phases is NOT set

		/*
		 * ─ 8.6. Create database backup for env:reset (only for direct env:up calls) ─
		 */
		// Only create backup if:
		// 1. Test packages were set up
		// 2. Setup phases were NOT skipped (i.e., not called from run:e2e)
		// This ensures backup is only created for manual testing environments
		if ( ! empty( $env_info->test_packages_for_setup ) && ! $env_info->skip_test_phases ) {
			$this->createDatabaseBackup( $env_info, $output );
		}

		/* ─ 9. Generate source files ─ */
		$this->environment_vars->save_environment_file( $env_info );

		/* ─ 10. Print result ─ */
		if ( $input->getOption( 'json' ) ) {
			$output->writeln( json_encode( $env_info, JSON_UNESCAPED_SLASHES ) );
		} else {
			$this->renderHumanSummary( $output, $env_info );
			\QIT_CLI\Utils\UtilitySuggestions::render( $output, $env_info );

			// Show manual testing instructions
			$output->writeln( '' );
			$output->writeln( 'To run manual tests:' );
			$output->writeln( '  1. Navigate to your test directory' );
			$output->writeln( '  2. Load environment variables in this terminal:' );

			// Check if this is the only running environment
			$running_environments = $this->environment_monitor->get();
			if ( count( $running_environments ) === 1 ) {
				$output->writeln( '     <info>source "$(qit env:source)"</info>' );
			} else {
				$output->writeln( sprintf( '     <info>source "$(qit env:source %s)"</info>', $env_info->env_id ) );
			}

			$output->writeln( '  3. Run your tests:' );
			$output->writeln( '     <info>npx playwright test</info>' );
		}

		// Clean up DI container variables if we set them
		\QIT_CLI\App::offsetUnset( 'test_package_required_plugins' );
		\QIT_CLI\App::offsetUnset( 'test_package_required_themes' );

		return Command::SUCCESS;
	}

	/*******************************************************************
	 * Helpers
	 ******************************************************************/

	/**
	 * Get the appropriate environment instance based on the environment type.
	 */
	protected function get_environment( string $environment_type ): \QIT_CLI\Environment\Environments\Environment {
		switch ( $environment_type ) {
			case 'performance':
				return $this->performance_environment;
			case 'e2e':
			default:
				return $this->e2e_environment;
		}
	}

	/**
	 * Download extensions from the given environment configuration.
	 * This method processes the merged config that includes CLI overrides.
	 *
	 * @param array<string,mixed> $env_config
	 * @param string              $environment_type
	 *
	 * @return \QIT_CLI\PreCommand\Extensions\ResolvedExtensions
	 */
	private function download_extensions_from_config( array $env_config, string $environment_type = 'e2e' ): \QIT_CLI\PreCommand\Extensions\ResolvedExtensions {
		$extensions = [];

		// Create Extension objects from plugins in the merged config
		if ( isset( $env_config['plugins'] ) ) {
			foreach ( $env_config['plugins'] as $plugin_config ) {
				if ( is_string( $plugin_config ) ) {
					// Use the new parser for string inputs
					try {
						$extension    = \QIT_CLI\PreCommand\Extensions\ExtensionInputParser::parse( $plugin_config, 'plugin' );
						$extensions[] = $extension;
					} catch ( \InvalidArgumentException $e ) {
						throw new \RuntimeException( 'Invalid plugin specification: ' . $e->getMessage() );
					}
				} else {
					// Handle array configuration
					$extension                      = new \QIT_CLI\PreCommand\Objects\Extension( $plugin_config['slug'], 'plugin' );
					$extension->added_automatically = 'Added from CLI or environment configuration';

					if ( isset( $plugin_config['from'] ) ) {
						$extension->from = $plugin_config['from'];

						switch ( $plugin_config['from'] ) {
							case 'wporg':
								$extension->version = $plugin_config['version'] ?? 'stable';
								break;
							case 'wccom':
								$extension->version  = $plugin_config['version'] ?? 'stable';
								$extension->wccom_id = $plugin_config['wccom_id'] ?? null;
								break;
							case 'local':
								$extension->directory = $plugin_config['path'] ?? null;
								$extension->source    = $plugin_config['path'] ?? null;
								if ( isset( $plugin_config['build'] ) ) {
									$extension->build_command = $plugin_config['build'];
									$extension->build_cwd     = $plugin_config['path'] ?? null;
								}
								break;
							case 'url':
								$extension->source  = $plugin_config['url'] ?? null;
								$extension->version = $plugin_config['version'] ?? 'stable';
								break;
						}
					} else {
						// Don't set $extension->from - let ExtensionResolver determine the correct source
						$extension->version = 'stable';
					}

					$extensions[] = $extension;
				}
			}
		}

		// Create Extension objects from themes in the merged config
		if ( isset( $env_config['themes'] ) ) {
			foreach ( $env_config['themes'] as $theme_config ) {
				if ( is_string( $theme_config ) ) {
					// Use the new parser for string inputs
					try {
						$extension    = \QIT_CLI\PreCommand\Extensions\ExtensionInputParser::parse( $theme_config, 'theme' );
						$extensions[] = $extension;
					} catch ( \InvalidArgumentException $e ) {
						throw new \RuntimeException( 'Invalid theme specification: ' . $e->getMessage() );
					}
				} else {
					// Handle array configuration
					$extension                      = new \QIT_CLI\PreCommand\Objects\Extension( $theme_config['slug'], 'theme' );
					$extension->added_automatically = 'Added from CLI or environment configuration';

					if ( isset( $theme_config['from'] ) ) {
						$extension->from = $theme_config['from'];

						switch ( $theme_config['from'] ) {
							case 'wporg':
								$extension->version = $theme_config['version'] ?? 'stable';
								break;
							case 'local':
								$extension->directory = $theme_config['path'] ?? null;
								$extension->source    = $theme_config['path'] ?? null;
								if ( isset( $theme_config['build'] ) ) {
									$extension->build_command = $theme_config['build'];
									$extension->build_cwd     = $theme_config['path'] ?? null;
								}
								break;
							case 'url':
								$extension->source = $theme_config['url'] ?? null;
								break;
						}
					} else {
						// Don't set $extension->from - let ExtensionResolver determine the correct source
						$extension->version = 'stable';
					}

					$extensions[] = $extension;
				}
			}
		}

		// Remove duplicates by slug
		$unique = [];
		foreach ( $extensions as $ext ) {
			$key = $ext->slug . '_' . $ext->type;
			if ( ! isset( $unique[ $key ] ) ) {
				$unique[ $key ] = $ext;
			}
		}
		$extensions = array_values( $unique );

		// Resolve/download them using ExtensionResolver
		$env_info_class = $this->get_env_info_class( $environment_type );

		/** @var E2EEnvInfo|PerformanceEnvInfo $env_info */
		$env_info = $env_info_class::from_array( [
			'env_id'      => 'temp_' . bin2hex( random_bytes( 4 ) ),
			'environment' => $environment_type,
		] );
		$resolver = \QIT_CLI\App::make( \QIT_CLI\PreCommand\Extensions\ExtensionResolver::class );

		// Use the proper QIT cache directory
		$cache_dir = \QIT_CLI\Config::get_qit_dir() . 'cache';

		return $resolver->resolve( $extensions, $cache_dir );
	}

	/**
	 * Convert woocommerce_version scalar to a plugin entry.
	 *
	 * If the merged config contains a woocommerce_version key (from CLI --woo
	 * or from qit.json), resolve it and add a proper plugin entry. The scalar
	 * key is consumed so the rest of the system sees WooCommerce as just
	 * another plugin.
	 *
	 * @param array<string,mixed> $config Merged environment config.
	 *
	 * @return array<string,mixed>
	 */
	private function resolveWooCommerceVersion( array $config ): array {
		$woo_version = $config['woocommerce_version'] ?? null;
		unset( $config['woocommerce_version'] );

		if ( empty( $woo_version ) ) {
			return $config;
		}

		$resolved_source = $this->version_resolver->resolve_woo( $woo_version );

		if ( $resolved_source !== null ) {
			// Special version (rc, nightly) → URL source.
			$woo_plugin = [
				'slug'                => 'woocommerce',
				'from'                => 'url',
				'url'                 => $resolved_source,
				'version'             => $woo_version,
				'added_automatically' => 'Added via --woo option',
			];
		} else {
			// Regular version → wporg source.
			$woo_plugin = [
				'slug'                => 'woocommerce',
				'from'                => 'wporg',
				'version'             => $woo_version,
				'added_automatically' => 'Added via --woo option',
			];
		}

		$config['plugins'] = $config['plugins'] ?? [];

		// Remove any existing WooCommerce entries to avoid conflicts.
		$config['plugins'] = array_values( array_filter( $config['plugins'], function ( $plugin ) {
			$slug = is_string( $plugin ) ? $plugin : ( $plugin['slug'] ?? null );

			return $slug !== 'woocommerce';
		} ) );

		// Add the resolved WooCommerce plugin.
		$config['plugins'][] = $woo_plugin;

		return $config;
	}

	/**
	 * Resolve special WordPress versions (e.g. "rc" → actual URL).
	 *
	 * @param array<string,mixed> $config Merged environment config.
	 *
	 * @return array<string,mixed>
	 */
	private function resolveWordPressVersion( array $config ): array {
		if ( empty( $config['wordpress_version'] ) ) {
			return $config;
		}

		$resolved_wp = $this->version_resolver->resolve_wp( $config['wordpress_version'] );

		if ( $resolved_wp !== null ) {
			$config['wordpress_version'] = $resolved_wp;
		}

		return $config;
	}

	/**
	 * Process env vars and env files into a flat envs map.
	 *
	 * Merges: hardcoded defaults < config envs < parsed env-files + CLI --env.
	 *
	 * @param array<string,mixed> $config Merged environment config.
	 * @param InputInterface      $input  The input for CLI --env / --env_file.
	 *
	 * @return array<string,mixed>
	 */
	private function resolveEnvVars( array $config, InputInterface $input ): array {
		$default_env_vars = [
			'QIT_DISABLE_UPDATE_CHECKS' => 'true',
			'QIT_NETWORK_LOGGING'       => 'false',
		];

		$existing_env_vars = $config['envs'] ?? [];
		$env_files         = array_merge(
			$config['env_files'] ?? [],
			$input->hasOption( 'env_file' ) ? (array) $input->getOption( 'env_file' ) : []
		);

		$cli_env_vars = $input->hasOption( 'env' ) ? (array) $input->getOption( 'env' ) : [];

		$parsed_vars = \QIT_CLI\App::make( \QIT_CLI\Environment\EnvParser::class )->parse( $cli_env_vars, $env_files );

		$config['envs'] = array_merge( $default_env_vars, $existing_env_vars, $parsed_vars );
		unset( $config['env_files'] );

		return $config;
	}

	/**
	 * Render a human-readable summary of the environment.
	 *
	 * @param OutputInterface $out  The output interface.
	 * @param QITEnvInfo      $info The environment info.
	 */
	private function renderHumanSummary( OutputInterface $out, QITEnvInfo $info ): void {
		$out->writeln( '' );
		$out->writeln( sprintf( '<info>✅ Environment ready: %s</info>', $info->env_id ) );
		$out->writeln( '' );

		// URL and credentials
		$out->writeln( sprintf( '  URL:         %s', $info->site_url ) );
		$out->writeln( '  Credentials: admin/password' );

		// Stack information
		$stack_parts   = [];
		$stack_parts[] = sprintf( 'WordPress %s', $info->wordpress_version );
		$stack_parts[] = sprintf( 'PHP %s', $info->php_version );
		$out->writeln( sprintf( '  Stack:       %s', implode( ', ', $stack_parts ) ) );

		// Plugins line (only if plugins exist)
		$plugin_names = [];
		foreach ( $info->plugins as $plugin ) {
			if ( $plugin->slug === 'woocommerce' && $info->woocommerce_version ) {
				$plugin_names[] = sprintf( 'WooCommerce %s', $info->woocommerce_version );
			} elseif ( $plugin->slug !== 'woocommerce' ) {
				$plugin_names[] = sprintf( '%s %s', $this->format_plugin_name( $plugin->slug ), $plugin->version );
			}
		}
		if ( ! empty( $plugin_names ) ) {
			$out->writeln( sprintf( '  Plugins:     %s', implode( ', ', $plugin_names ) ) );
		}

		// Theme line (only if non-default theme exists)
		$theme_names = [];
		foreach ( $info->themes as $theme ) {
			// Skip default themes
			if ( ! in_array( $theme->slug, [ 'twentytwentyfour', 'twentytwentythree', 'twentytwentytwo' ], true ) ) {
				$theme_names[] = sprintf( '%s %s', $this->format_plugin_name( $theme->slug ), $theme->version );
			}
		}
		if ( ! empty( $theme_names ) ) {
			$out->writeln( sprintf( '  Theme:       %s', implode( ', ', $theme_names ) ) );
		}

		// Test packages that were set up
		if ( ! empty( $info->test_packages_for_setup ) ) {
			$out->writeln( '' );
			$out->writeln( '  Test packages prepared:' );
			if ( $info->skip_test_phases ) {
				// When phases are skipped, just list the packages
				foreach ( $info->test_packages_for_setup as $pkg_id => $pkg_info ) {
					$out->writeln( sprintf( '    • %s (phases deferred)', $pkg_id ) );
				}
			} else {
				// When phases ran, show which phases ran for each package
				$is_first = true;
				foreach ( $info->test_packages_for_setup as $pkg_id => $pkg_info ) {
					$phases = $is_first ? 'globalSetup + setup' : 'globalSetup only';
					$out->writeln( sprintf( '    • %s (%s)', $pkg_id, $phases ) );
					$is_first = false;
				}
			}
		}

		// Tunnel information (only if enabled)
		if ( $info->tunnel ) {
			$out->writeln( sprintf( '  Tunnel:      %s', $info->tunnel_type ) );
		}
	}

	/**
	 * Format plugin/theme name for display.
	 *
	 * @param string $slug The plugin/theme slug.
	 * @return string Formatted name.
	 */
	private function format_plugin_name( string $slug ): string {
		// Convert slug to title case
		$name = str_replace( [ '-', '_' ], ' ', $slug );
		return ucwords( $name );
	}

	/**
	 * Process test package requirements and add missing dependencies.
	 *
	 * @param array<string,mixed> $env_config Environment configuration (modified by reference).
	 * @param InputInterface      $input Input interface.
	 * @param OutputInterface     $output Output interface.
	 */
	private function process_test_package_requirements( array &$env_config, InputInterface $input, OutputInterface $output ): void {
		// Get pre-processed test package requirements from DI container (set by RunE2ECommand)
		$required_plugins = \QIT_CLI\App::getVar( 'test_package_required_plugins' );
		$required_themes  = \QIT_CLI\App::getVar( 'test_package_required_themes' );

		if ( empty( $required_plugins ) && empty( $required_themes ) ) {
			return;
		}

		// Ensure arrays
		$required_plugins = $required_plugins ?: [];
		$required_themes  = $required_themes ?: [];

		// Initialize arrays if they don't exist
		if ( ! isset( $env_config['plugins'] ) ) {
			$env_config['plugins'] = [];
		}
		if ( ! isset( $env_config['themes'] ) ) {
			$env_config['themes'] = [];
		}

		// Track what's already provided
		$existing_plugins = [];
		$existing_themes  = [];

		// Extract existing plugin slugs
		foreach ( $env_config['plugins'] as $plugin ) {
			$slug = is_string( $plugin ) ? $plugin : ( $plugin['slug'] ?? null );
			if ( $slug ) {
				$existing_plugins[] = $slug;
			}
		}

		// Extract existing theme slugs
		foreach ( $env_config['themes'] as $theme ) {
			$slug = is_string( $theme ) ? $theme : ( $theme['slug'] ?? null );
			if ( $slug ) {
				$existing_themes[] = $slug;
			}
		}

		// Also check SUT to avoid duplication
		$sut_slug = null;
		if ( isset( $env_config['sut'] ) && is_array( $env_config['sut'] ) && isset( $env_config['sut']['slug'] ) ) {
			$sut_slug = $env_config['sut']['slug'];
		}

		// Add missing plugins
		$added_plugins = [];
		foreach ( $required_plugins as $plugin_slug => $required_by ) {
			// Skip if it's the SUT
			if ( $sut_slug && $plugin_slug === $sut_slug ) {
				continue;
			}

			// Add if not already present
			if ( ! in_array( $plugin_slug, $existing_plugins, true ) ) {
				$env_config['plugins'][]       = $plugin_slug;
				$added_plugins[ $plugin_slug ] = $required_by;
			}
		}

		// Add missing themes
		$added_themes = [];
		foreach ( $required_themes as $theme_slug => $required_by ) {
			// Skip if it's the SUT
			if ( $sut_slug && $theme_slug === $sut_slug ) {
				continue;
			}

			// Add if not already present
			if ( ! in_array( $theme_slug, $existing_themes, true ) ) {
				$env_config['themes'][]      = $theme_slug;
				$added_themes[ $theme_slug ] = $required_by;
			}
		}

		// Report what was added (skip in JSON mode to avoid breaking JSON output)
		if ( ( ! empty( $added_plugins ) || ! empty( $added_themes ) ) && ! $input->getOption( 'json' ) ) {
			$output->writeln( '' );
			$output->writeln( '<info>Auto-installing test package dependencies:</info>' );

			foreach ( $added_plugins as $plugin_slug => $required_by ) {
				$packages = implode( ', ', array_unique( $required_by ) );
				$output->writeln( sprintf( '  → Plugin <comment>%s</comment> (required by %s)', $plugin_slug, $packages ) );
			}

			foreach ( $added_themes as $theme_slug => $required_by ) {
				$packages = implode( ', ', array_unique( $required_by ) );
				$output->writeln( sprintf( '  → Theme <comment>%s</comment> (required by %s)', $theme_slug, $packages ) );
			}
		}
	}

	/*******************************************************************
	 * Long help text
	 ******************************************************************/
	private function getHelpText(): string {
		return <<<'HELP'
Creates a fully‑configured, disposable local environment.

Precedence: CLI defaults → qit.json → explicit CLI overrides.

Examples
  <info>qit env:up</info>
      Uses the "default" environment from qit.json

  <info>qit env:up --environment=legacy</info>
      Spins up the "legacy" block from qit.json

  <info>qit env:up --php=8.3 --plugin=gutenberg</info>
      Overrides PHP version and adds Gutenberg, leaving the rest untouched

  <info>qit env:up --tunnel=cloudflare</info>
      Exposes the site publicly through Cloudflare Tunnel

  <info>qit env:up --setup=./tests/e2e</info>
      Run setup phases from test package in specified directory

  <info>qit env:up --global-setup --config=utilities.json</info>
      Run globalSetup and setup from all packages without executing tests
      Perfect for utility packages that configure environments for development

  <info>qit env:up --skip-setup</info>
      Skip automatic setup even if qit-test.json exists in current directory
HELP;
	}

	/**
	 * Determine the main package based on execution order.
	 * The main package is the one that will have its setup phase run.
	 *
	 * With the new order-respecting logic, the main package is simply
	 * the first package in the list, as they're already ordered correctly
	 * by command-line specification.
	 *
	 * @param array<string>   $packages List of package references.
	 * @param OutputInterface $output   The output interface.
	 * @return string|null The main package reference, or null if no packages.
	 */
	private function determineMainPackage( array $packages, OutputInterface $output ): ?string {
		if ( empty( $packages ) ) {
			return null;
		}

		// The first package in the list is the main package
		// (packages are already ordered correctly by command-line order)
		if ( $output->isVerbose() ) {
			$output->writeln( "<info>Main package: {$packages[0]} (first remote package)</info>" );
		}
		return $packages[0];
	}

	/**
	 * Process test packages to extract requirements.
	 *
	 * @param array<string>   $test_packages Array of test package references (local paths or remote IDs).
	 * @param OutputInterface $output The output interface.
	 */
	private function processTestPackageRequirements( array $test_packages, OutputInterface $output ): void {
		if ( $output->isVeryVerbose() || getenv( 'QIT_DEBUG' ) ) {
			$output->writeln( '[DEBUG] env:up->processTestPackageRequirements - Processing ' . count( $test_packages ) . ' packages' );
		}

		$required_plugins = \QIT_CLI\App::getVar( 'test_package_required_plugins', [] );
		$required_themes  = \QIT_CLI\App::getVar( 'test_package_required_themes', [] );
		$required_secrets = \QIT_CLI\App::getVar( 'test_package_required_secrets', [] );
		$requires_network = \QIT_CLI\App::getVar( 'test_package_requires_network', false );
		$requires_tunnel  = \QIT_CLI\App::getVar( 'test_package_requires_tunnel', false );

		foreach ( $test_packages as $package_ref ) {
			if ( $output->isVeryVerbose() || getenv( 'QIT_DEBUG' ) ) {
				$output->writeln( "[DEBUG] env:up - Processing test package: $package_ref" );
			}

			try {
				// Try to download/process the package (likely cache hit if run:e2e already downloaded)
				$package_downloader = \QIT_CLI\App::make( \QIT_CLI\PreCommand\Download\TestPackageDownloader::class );
				// Fix: Use correct method signature and cache dir (same as run:e2e uses)
				$packages_to_download = [ $package_ref => [] ];
				$manifests            = $package_downloader->download( $packages_to_download, sys_get_temp_dir() . '/qit-cache' );

				if ( isset( $manifests[ $package_ref ] ) ) {
					$manifest = $manifests[ $package_ref ];

					if ( $output->isVeryVerbose() || getenv( 'QIT_DEBUG' ) ) {
						$output->writeln( "[DEBUG] env:up - Got manifest for $package_ref (likely from cache)" );
					}

					// Extract requirements from manifest
					$this->extractManifestRequirements(
						$manifest,
						$package_ref,
						$required_plugins,
						$required_themes,
						$required_secrets,
						$requires_network,
						$requires_tunnel,
						$output
					);
				}
			} catch ( \Exception $e ) {
				// For local packages, try to read the manifest directly
				if ( is_dir( $package_ref ) && file_exists( $package_ref . '/qit-test.json' ) ) {
					try {
						$manifest_content = file_get_contents( $package_ref . '/qit-test.json' );
						$manifest_data    = json_decode( $manifest_content, true );

						if ( json_last_error() === JSON_ERROR_NONE ) {
							// Use TestPackageManifest to parse it properly
							$manifest = new \QIT_CLI\PreCommand\Objects\TestPackageManifest( $manifest_data );

							// Extract requirements from manifest
							$this->extractManifestRequirements(
								$manifest,
								$package_ref,
								$required_plugins,
								$required_themes,
								$required_secrets,
								$requires_network,
								$requires_tunnel,
								$output
							);
						}
					} catch ( \Exception $inner_e ) {
						if ( $output->isVerbose() ) {
							$output->writeln( "<warning>Could not process test package {$package_ref}: {$inner_e->getMessage()}</warning>" );
						}
					}
				} else {
					if ( $output->isVerbose() ) {
						$output->writeln( "<warning>Could not process test package {$package_ref}: {$e->getMessage()}</warning>" );
					}
				}
			}
		}

		// Store the collected requirements back to DI container for process_test_package_requirements to use
		if ( ! empty( $required_plugins ) ) {
			\QIT_CLI\App::setVar( 'test_package_required_plugins', $required_plugins );
		}
		if ( ! empty( $required_themes ) ) {
			\QIT_CLI\App::setVar( 'test_package_required_themes', $required_themes );
		}
		if ( ! empty( $required_secrets ) ) {
			\QIT_CLI\App::setVar( 'test_package_required_secrets', $required_secrets );
		}
		if ( $requires_network ) {
			\QIT_CLI\App::setVar( 'test_package_requires_network', true );
		}
		if ( $requires_tunnel ) {
			\QIT_CLI\App::setVar( 'test_package_requires_tunnel', true );
		}

		// Validate required secrets
		if ( ! empty( $required_secrets ) ) {
			$missing_secrets = [];
			foreach ( array_keys( $required_secrets ) as $secret ) {
				$value = getenv( $secret );
				if ( $value === false || $value === '' ) {
					$missing_secrets[ $secret ] = $required_secrets[ $secret ];
				}
			}

			if ( ! empty( $missing_secrets ) ) {
				$error_msg = "Missing required secrets:\n\n";
				foreach ( $missing_secrets as $secret => $packages ) {
					$package_list = implode( ', ', array_unique( $packages ) );
					$error_msg   .= "  • {$secret} (required by: {$package_list})\n";
				}
				$first_secret = array_key_first( $missing_secrets );
				$error_msg   .= "\nSet these environment variables before running this command.\n";
				$error_msg   .= "Example: export {$first_secret}='your-secret-value'";

				throw new \RuntimeException( $error_msg );
			}
		}
	}

	/**
	 * Extract requirements from a test package manifest.
	 *
	 * @param \QIT_CLI\PreCommand\Objects\TestPackageManifest $manifest The manifest to extract from.
	 * @param string                                          $package_ref The package reference for tracking which package requires what.
	 * @param array<string,array<string>>                     &$required_plugins By-reference array to append plugin requirements to.
	 * @param array<string,array<string>>                     &$required_themes By-reference array to append theme requirements to.
	 * @param array<string,array<string>>                     &$required_secrets By-reference array to append secret requirements to.
	 * @param bool                                            &$requires_network By-reference flag to set if package requires network.
	 * @param bool                                            &$requires_tunnel By-reference flag to set if package requires tunnel.
	 * @param OutputInterface                                 $output The output interface for verbose messages.
	 */
	private function extractManifestRequirements(
		\QIT_CLI\PreCommand\Objects\TestPackageManifest $manifest,
		string $package_ref,
		array &$required_plugins,
		array &$required_themes,
		array &$required_secrets,
		bool &$requires_network,
		bool &$requires_tunnel,
		OutputInterface $output
	): void {
		// Extract plugin requirements
		$plugins = $manifest->get_required_plugins();
		foreach ( $plugins as $plugin ) {
			if ( ! isset( $required_plugins[ $plugin ] ) ) {
				$required_plugins[ $plugin ] = [];
			}
			$required_plugins[ $plugin ][] = $package_ref;
		}

		// Extract theme requirements
		$themes = $manifest->get_required_themes();
		foreach ( $themes as $theme ) {
			if ( ! isset( $required_themes[ $theme ] ) ) {
				$required_themes[ $theme ] = [];
			}
			$required_themes[ $theme ][] = $package_ref;
		}

		// Extract secret requirements
		$requires = $manifest->get_requires();
		if ( ! empty( $requires['secrets'] ) ) {
			foreach ( $requires['secrets'] as $secret ) {
				if ( ! isset( $required_secrets[ $secret ] ) ) {
					$required_secrets[ $secret ] = [];
				}
				$required_secrets[ $secret ][] = $package_ref;
			}
		}

		// Check network requirement
		if ( $manifest->requires_network() ) {
			$requires_network = true;
			if ( $output->isVerbose() ) {
				$output->writeln( "<info>Test package {$package_ref} requires network access</info>" );
			}
		}

		// Check tunnel requirement
		if ( $manifest->requires_tunnel() ) {
			$requires_tunnel = true;
			if ( $output->isVerbose() ) {
				$output->writeln( "<info>Test package {$package_ref} requires tunnel access</info>" );
			}
		}
	}

	/**
	 * Convert SUT configuration to extension format for environment.
	 *
	 * @param array<string,mixed> $sut The SUT configuration from qit.json.
	 * @return array<string,mixed> Extension configuration.
	 */
	private function convert_sut_to_extension( array $sut ): array {
		$extension = [
			'slug'                => $sut['slug'],
			'added_automatically' => 'Added as SUT from qit.json',
		];

		// Handle different source types
		switch ( $sut['source']['type'] ) {
			case 'local':
				$extension['from'] = 'local';
				// Use resolved_path if available, otherwise use path
				$extension['path'] = $sut['source']['resolved_path'] ?? $sut['source']['path'];
				if ( isset( $sut['source']['build'] ) ) {
					$extension['build'] = $sut['source']['build'];
				}
				break;

			case 'url':
				$extension['from'] = 'url';
				$extension['url']  = $sut['source']['url'];
				break;

			case 'wporg':
				$extension['from']    = 'wporg';
				$extension['version'] = $sut['source']['version'] ?? 'stable';
				break;

			case 'wccom':
				$extension['from']    = 'wccom';
				$extension['version'] = $sut['source']['version'] ?? 'stable';
				if ( isset( $sut['source']['wccom_id'] ) ) {
					$extension['wccom_id'] = $sut['source']['wccom_id'];
				}
				break;

			case 'build':
				// For build sources, we need to run the build command first
				// This is handled elsewhere, for now just set as local with the output path
				$extension['from'] = 'local';
				$extension['path'] = $sut['source']['resolved_output'] ?? $sut['source']['output'];
				break;

			default:
				throw new \RuntimeException( "Unknown SUT source type: {$sut['source']['type']}" );
		}

		return $extension;
	}

	/**
	 * Create a database backup for env:reset functionality.
	 * This backup captures the state after all setup phases have completed.
	 *
	 * @param E2EEnvInfo|PerformanceEnvInfo $env_info The environment info.
	 * @param OutputInterface               $output   The output interface.
	 */
	private function createDatabaseBackup( $env_info, OutputInterface $output ): void {
		$output->write( 'Creating database backup for env:reset...' );

		// Create backup directory
		$backup_dir = sys_get_temp_dir() . '/qit-env-backups/' . $env_info->env_id;
		if ( ! is_dir( $backup_dir ) ) {
			mkdir( $backup_dir, 0755, true );
		}

		try {
			// Get Docker instance
			$docker = \QIT_CLI\App::make( \QIT_CLI\Environment\Docker::class );

			// Export database - run in WordPress directory with defaults
			$sql_dump = $docker->run_inside_docker( $env_info, [ 'sh', '-c', 'cd /var/www/html && wp db export --defaults --quiet - 2>/dev/null' ] );
			file_put_contents( $backup_dir . '/setup-complete.sql', $sql_dump );

			// Save metadata about what was backed up
			$metadata = [
				'created'             => time(),
				'env_id'              => $env_info->env_id,
				'test_packages'       => array_keys( $env_info->test_packages_for_setup ),
				'php_version'         => $env_info->php_version,
				'wordpress_version'   => $env_info->wordpress_version,
				'woocommerce_version' => $env_info->woocommerce_version ?? '',
			];
			file_put_contents( $backup_dir . '/metadata.json', json_encode( $metadata, JSON_PRETTY_PRINT ) );

			$output->writeln( ' <info>Done!</info>' );
		} catch ( \Exception $e ) {
			$output->writeln( ' <warning>Failed!</warning>' );
			$output->writeln( '<warning>Database backup failed: ' . $e->getMessage() . '</warning>' );
			// Don't fail the whole command, just warn that env:reset won't work
			$output->writeln( '<comment>Note: env:reset will not be available for this environment.</comment>' );
		}
	}

	/**
	 * Get the appropriate EnvInfo class based on environment type.
	 *
	 * @param string $environment_type The environment type ('performance' or 'e2e').
	 * @return class-string<E2EEnvInfo|PerformanceEnvInfo> The EnvInfo class name.
	 */
	private function get_env_info_class( string $environment_type ): string {
		return $environment_type === 'performance'
			? PerformanceEnvInfo::class
			: E2EEnvInfo::class;
	}
}
