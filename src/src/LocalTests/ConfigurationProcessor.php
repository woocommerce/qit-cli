<?php

namespace QIT_CLI\LocalTests;

use QIT_CLI\App;
use QIT_CLI\Environment\EnvConfigLoader;
use QIT_CLI\Environment\Extension;
use QIT_CLI\WooExtensionsList;
use Symfony\Component\Console\Input\InputInterface;

class ConfigurationProcessor {
	/** @var EnvConfigLoader */
	protected $config_loader;

	/** @var WooExtensionsList */
	protected $woo_extensions_list;

	/** @var bool */
	protected $is_development;

	public function __construct( EnvConfigLoader $config_loader, WooExtensionsList $woo_extensions_list ) {
		$this->config_loader       = $config_loader;
		$this->woo_extensions_list = $woo_extensions_list;
	}

	/**
	 * Process and merge configuration from qit.yml and CLI options into a final set of env:up options.
	 *
	 * @param string|null         $woo_extension_slug The slug of the SUT if resolved from an ID, otherwise the original slug, or null if none.
	 * @param InputInterface      $input              The input interface for CLI arguments and options.
	 * @param array<string,mixed> $env_up_options     The partially processed env_up_options from RunE2ECommand.
	 * @param string|null         $sut_type           Either 'plugin', 'theme', or null if no SUT.
	 *
	 * @return array<string,mixed> Final configuration suitable for passing to env:up (e.g., $env_up_options).
	 */
	public function process_configuration(
		?string $woo_extension_slug,
		InputInterface $input,
		array $env_up_options,
		?string $sut_type
	): array {
		if ( $input->getOption( 'config' ) ) {
			App::setVar( 'QIT_CONFIG_OVERRIDE', $input->getOption( 'config' ) );
		}

		$env_config = $this->config_loader->load_config();
		if ( ! isset( $env_config['plugins'] ) || ! is_array( $env_config['plugins'] ) ) {
			$env_config['plugins'] = [];
		}
		if ( ! isset( $env_config['themes'] ) || ! is_array( $env_config['themes'] ) ) {
			$env_config['themes'] = [];
		}

		// Handle numeric keys in qit.yml for plugins before proceeding.
		// If qit.yml has numeric keys, convert them to slug-based keys.
		$this->normalize_numeric_qit_plugins( $env_config );

		$woo_extension_raw   = $input->getArgument( 'woo_extension' );
		$test                = $input->getArgument( 'test' );
		$source_option       = $input->getOption( 'source' );
		$source              = ! empty( $source_option ) ? $source_option : '';
		$sut_action          = $input->getOption( 'sut_action' );
		$dependencies_option = $input->getOption( 'dependencies' ) ?? Extension::ACTIONS['bootstrap'];

		// If original was numeric and no source provided, keep source as numeric ID.
		if ( empty( $source ) && ! empty( $woo_extension_raw ) && is_numeric( $woo_extension_raw ) ) {
			$source = $woo_extension_raw;
		}

		$has_sut = ! empty( $woo_extension_slug ) || ! empty( $source ) || ! empty( $sut_action );

		// If no sut_type given, default to 'plugin'.
		// This should not happen if we resolved correctly, but just in case.
		if ( $sut_type === null ) {
			$sut_type = 'plugin';
		}

		if ( $has_sut ) {
			$this->finalize_sut_definition(
				$woo_extension_slug,
				$source,
				$test,
				$dependencies_option,
				$env_up_options,
				$env_config,
				$input,
				$sut_type,
				$sut_action
			);
		} else {
			// No SUT: Just handle CLI plugins/themes and skip dependencies.
			$this->add_cli_plugins( $env_config, $input );
			$this->normalize_plugins( $env_config );
			$this->normalize_themes( $env_config );

			$env_up_options['--plugin'] = array_values( $env_config['plugins'] );
			$env_up_options['--theme']  = array_values( $env_config['themes'] );
		}

		if ( App::getVar( 'QIT_ACTIVATION_TEST' ) ) {
			foreach ( [ 'plugin', 'theme' ] as $type ) {
				$key = "--$type";

				if ( isset( $env_up_options[ $key ] ) && is_array( $env_up_options[ $key ] ) ) {
					foreach ( $env_up_options[ $key ] as &$item ) {
						if ( isset( $item['slug'] ) && $item['slug'] === 'woocommerce' ) {
							$item['action']    = Extension::ACTIONS['test'];
							$item['test_tags'] = [ 'activation' ];
						} else {
							$item['action']    = Extension::ACTIONS['bootstrap'];
							$item['test_tags'] = [ 'pre-activation' ];
						}
					}
					unset( $item );
				}
			}
		}

		$this->is_development = ! empty( $source_option ) && file_exists( $source_option );

		return $env_up_options;
	}

	/**
	 * Finalize the SUT definition by merging CLI and qit.yml:
	 * - Determine source and test_tags
	 * - Ensure action=test
	 * - Apply dependencies
	 * - Add CLI plugins
	 * - Normalize all plugins/themes
	 *
	 * @param string|null         $woo_extension
	 * @param string|null         $source
	 * @param string|null         $test
	 * @param string              $dependencies_option
	 * @param array<string,mixed> $env_up_options
	 * @param array<string,mixed> $env_config
	 * @param InputInterface      $input
	 * @param string              $sut_type
	 * @param string|null         $sut_action The SUT action provided by CLI, if any (e.g. 'bootstrap' for activation tests).
	 *
	 * @return void
	 */
	protected function finalize_sut_definition(
		$woo_extension,
		$source,
		$test,
		$dependencies_option,
		array &$env_up_options,
		array &$env_config,
		InputInterface $input,
		string $sut_type,
		?string $sut_action
	): void {
		if ( empty( $woo_extension ) ) {
			// If no woo_extension, there's no main SUT, so skip dependencies.
			$this->add_cli_plugins( $env_config, $input );
			$this->normalize_plugins( $env_config );
			$this->normalize_themes( $env_config );
			$env_up_options['--plugin'] = array_values( $env_config['plugins'] );
			$env_up_options['--theme']  = array_values( $env_config['themes'] );

			return;
		}

		$sut_base = $this->get_sut_base( $env_config, $woo_extension );

		// Resolve source to an absolute path if possible.
		if ( ! empty( $source ) ) {
			$resolved_source = realpath( $source );
			if ( $resolved_source && file_exists( $resolved_source ) ) {
				$source = $resolved_source;
			}
			$sut_base['source'] = $source;
		}

		// Override test_tags if provided via CLI.
		if ( ! empty( $test ) ) {
			$sut_base['test_tags'] = array_map( 'trim', explode( ',', $test ) );
		} else {
			if ( empty( $sut_base['test_tags'] ) ) {
				$sut_base['test_tags'] = [ 'default' ];
			}
		}

		// If sut_action is provided, use it instead of defaulting to test
		// Otherwise, ensure action is 'test' if not set.
		if ( ! empty( $sut_action ) ) {
			$sut_base['action'] = $sut_action;
		} else {
			if ( ! isset( $sut_base['action'] ) ) {
				$sut_base['action'] = Extension::ACTIONS['test'];
			}
		}

		// Place SUT in themes if sut_type is 'theme', else in plugins.
		if ( $sut_type === 'theme' ) {
			$env_config['themes'][ $woo_extension ] = $sut_base;
		} else {
			$env_config['plugins'][ $woo_extension ] = $sut_base;
		}

		$woo_extension_id = App::getVar( 'QIT_SUT' );

		$this->apply_dependencies( $woo_extension_id, $dependencies_option, $env_up_options, $env_config );
		$this->add_cli_plugins( $env_config, $input );
		$this->normalize_plugins( $env_config );
		$this->normalize_themes( $env_config );

		$env_up_options['--theme']  = array_values( $env_config['themes'] );
		$env_up_options['--plugin'] = array_values( $env_config['plugins'] );
	}

	/**
	 * Retrieve a base configuration for the SUT from qit.yml if present, otherwise return defaults.
	 *
	 * @param array<string,mixed> $env_config
	 * @param string              $woo_extension
	 *
	 * @return array<string,mixed>
	 */
	protected function get_sut_base( array $env_config, string $woo_extension ): array {
		if ( isset( $env_config['plugins'][ $woo_extension ] ) ) {
			return $env_config['plugins'][ $woo_extension ];
		}

		if ( isset( $env_config['themes'][ $woo_extension ] ) ) {
			return $env_config['themes'][ $woo_extension ];
		}

		// Default configuration if not defined in qit.yml.
		return [
			'slug'      => $woo_extension,
			'source'    => $woo_extension,
			'test_tags' => [ 'default' ],
		];
	}

	/**
	 * Merge CLI plugins into the env_config as objects.
	 *
	 * @param array<string,mixed> $env_config
	 * @param InputInterface      $input
	 *
	 * @return void
	 */
	protected function add_cli_plugins( array &$env_config, InputInterface $input ): void {
		$cli_plugins = $input->getOption( 'plugin' );
		if ( empty( $cli_plugins ) ) {
			return;
		}

		foreach ( $cli_plugins as $cli_plugin ) {
			$parts           = explode( ':', $cli_plugin );
			$original_source = $parts[0];
			$cli_slug        = $parts[0]; // This will be changed if numeric.
			$cli_action      = Extension::ACTIONS['bootstrap'];
			$cli_tags        = [];

			// Handle numeric plugin IDs.
			if ( is_numeric( $cli_slug ) ) {
				try {
					$resolved_slug = $this->woo_extensions_list->get_woo_extension_slug_by_id( (int) $cli_slug );
					$cli_slug      = $resolved_slug;
				} catch ( \Exception $e ) {
					throw new \RuntimeException( "Failed to resolve extension ID {$cli_slug}: " . $e->getMessage() );
				}
			}

			if ( isset( $parts[1] ) && in_array( $parts[1], Extension::ACTIONS, true ) ) {
				$cli_action = $parts[1];
				if ( isset( $parts[2] ) && ! empty( $parts[2] ) ) {
					$cli_tags = array_map( 'trim', explode( ',', $parts[2] ) );
				}
			} else {
				if ( isset( $parts[1] ) ) {
					$cli_tags = array_map( 'trim', explode( ',', $parts[1] ) );
				}
			}

			// By default, we assume extensions go to plugins if not defined otherwise.
			// This is consistent with original logic.
			if ( ! isset( $env_config['plugins'] ) ) {
				$env_config['plugins'] = [];
			}

			// If plugin not defined in qit.yml, create a new entry.
			if ( ! isset( $env_config['plugins'][ $cli_slug ] ) && ! isset( $env_config['themes'][ $cli_slug ] ) ) {
				$env_config['plugins'][ $cli_slug ] = [
					'slug'      => $cli_slug,
					'source'    => $original_source,
					'test_tags' => empty( $cli_tags ) ? [ 'default' ] : $cli_tags,
					'action'    => $cli_action,
				];
			} else {
				// If found in plugins.
				if ( isset( $env_config['plugins'][ $cli_slug ] ) ) {
					if ( ! empty( $cli_tags ) ) {
						$env_config['plugins'][ $cli_slug ]['test_tags'] = $cli_tags;
					} elseif ( empty( $env_config['plugins'][ $cli_slug ]['test_tags'] ) ) {
						$env_config['plugins'][ $cli_slug ]['test_tags'] = [ 'default' ];
					}

					$env_config['plugins'][ $cli_slug ]['action'] = $cli_action;
				}

				// If found in themes.
				if ( isset( $env_config['themes'][ $cli_slug ] ) ) {
					if ( ! empty( $cli_tags ) ) {
						$env_config['themes'][ $cli_slug ]['test_tags'] = $cli_tags;
					} elseif ( empty( $env_config['themes'][ $cli_slug ]['test_tags'] ) ) {
						$env_config['themes'][ $cli_slug ]['test_tags'] = [ 'default' ];
					}

					$env_config['themes'][ $cli_slug ]['action'] = $cli_action;
				}
			}
		}
	}

	/**
	 * Normalize all plugins to ensure test_tags, action, slug, and source are properly set.
	 *
	 * @param array<string,mixed> $env_config
	 *
	 * @return void
	 */
	protected function normalize_plugins( array &$env_config ): void {
		if ( ! isset( $env_config['plugins'] ) || ! is_array( $env_config['plugins'] ) ) {
			$env_config['plugins'] = [];
		}

		foreach ( $env_config['plugins'] as $plugin_slug => &$plugin_config ) {
			if ( empty( $plugin_config['test_tags'] ) ) {
				$plugin_config['test_tags'] = [ 'default' ];
			} else {
				$normalized_tags = [];
				foreach ( (array) $plugin_config['test_tags'] as $tag ) {
					if ( strpos( $tag, ',' ) !== false ) {
						$split_tags      = array_map( 'trim', explode( ',', $tag ) );
						$normalized_tags = array_merge( $normalized_tags, $split_tags );
					} else {
						$normalized_tags[] = $tag;
					}
				}
				$plugin_config['test_tags'] = $normalized_tags;
			}

			if ( ! isset( $plugin_config['action'] ) ) {
				$plugin_config['action'] = Extension::ACTIONS['bootstrap'];
			}

			if ( ! isset( $plugin_config['slug'] ) ) {
				$plugin_config['slug'] = $plugin_slug;
			}

			// Set a default source if none is specified.
			if ( ! isset( $plugin_config['source'] ) ) {
				$plugin_config['source'] = $plugin_slug;
			}
		}
		unset( $plugin_config );
	}

	/**
	 * Normalize all themes to ensure test_tags, action, slug, and source are properly set.
	 *
	 * @param array<string,mixed> $env_config
	 *
	 * @return void
	 */
	protected function normalize_themes( array &$env_config ): void {
		if ( ! isset( $env_config['themes'] ) || ! is_array( $env_config['themes'] ) ) {
			$env_config['themes'] = [];
		}

		foreach ( $env_config['themes'] as $theme_slug => &$theme_config ) {
			if ( empty( $theme_config['test_tags'] ) ) {
				$theme_config['test_tags'] = [ 'default' ];
			} else {
				$normalized_tags = [];
				foreach ( (array) $theme_config['test_tags'] as $tag ) {
					if ( strpos( $tag, ',' ) !== false ) {
						$split_tags      = array_map( 'trim', explode( ',', $tag ) );
						$normalized_tags = array_merge( $normalized_tags, $split_tags );
					} else {
						$normalized_tags[] = $tag;
					}
				}
				$theme_config['test_tags'] = $normalized_tags;
			}

			if ( ! isset( $theme_config['action'] ) ) {
				$theme_config['action'] = Extension::ACTIONS['test'];
			}

			if ( ! isset( $theme_config['slug'] ) ) {
				$theme_config['slug'] = $theme_slug;
			}

			// Set a default source if none is specified.
			if ( ! isset( $theme_config['source'] ) ) {
				$theme_config['source'] = $theme_slug;
			}
		}
		unset( $theme_config );
	}

	/**
	 * Add dependencies as strings to --plugin and their corresponding arrays to $env_config['plugins'],
	 * and PHP extensions directly to $env_up_options['--php_extension'].
	 *
	 * By this point, $env_config should already be normalized by EnvConfigLoader, ensuring
	 * that 'plugins' key is used consistently. No separate handling of 'plugin' vs 'plugins' is needed.
	 *
	 * @param int|null            $woo_extension_id
	 * @param string              $dependencies_option
	 * @param array<string,mixed> $env_up_options
	 * @param array<string,mixed> $env_config
	 *
	 * @return void
	 */
	protected function apply_dependencies( ?int $woo_extension_id, string $dependencies_option, array &$env_up_options, array &$env_config ): void {
		if ( empty( $woo_extension_id ) || $dependencies_option === 'none' ) {
			return;
		}

		/** @var \QIT_CLI\PluginDependencies $dependencies_service */
		$dependencies_service = App::make( \QIT_CLI\PluginDependencies::class );
		$dependencies         = $dependencies_service->get_plugin_and_php_ext_dependencies( $woo_extension_id, [] );

		if ( ! isset( $env_up_options['--php_extension'] ) ) {
			$env_up_options['--php_extension'] = [];
		}
		if ( ! isset( $env_up_options['--plugin'] ) ) {
			$env_up_options['--plugin'] = [];
		}
		if ( ! isset( $env_config['plugins'] ) || ! is_array( $env_config['plugins'] ) ) {
			$env_config['plugins'] = [];
		}

		// Add PHP extensions directly to $env_up_options['--php_extension'].
		foreach ( $dependencies['php_extensions'] as $php_extension ) {
			if ( ! in_array( $php_extension, $env_up_options['--php_extension'], true ) ) {
				$env_up_options['--php_extension'][] = $php_extension;
			}
		}

		$woo_version = $env_up_options['--woo'] ?? null;

		// Add plugin dependencies to both $env_up_options['--plugin'] and $env_config['plugins'].
		foreach ( $dependencies['plugins'] as $dep_plugin ) {
			if ( $woo_version && stripos( $dep_plugin, 'woocommerce:' ) !== false ) {
				// Skip this dependency if a Woo version is specified and it conflicts.
				continue;
			}

			$plugin_slug     = strtok( $dep_plugin, ':' );
			$already_present = false;
			foreach ( $env_up_options['--plugin'] as $existing_plugin ) {
				if ( stripos( $existing_plugin, $plugin_slug ) !== false ) {
					$already_present = true;
					break;
				}
			}

			if ( ! $already_present ) {
				// Append ":{$dependencies_option}" to ensure the correct action is assigned.
				$formatted_plugin             = "{$dep_plugin}:{$dependencies_option}";
				$env_up_options['--plugin'][] = $formatted_plugin;
			}

			// Ensure the plugin is also in $env_config['plugins'].
			if ( ! isset( $env_config['plugins'][ $plugin_slug ] ) ) {
				$env_config['plugins'][ $plugin_slug ] = [
					'slug'      => $plugin_slug,
					'source'    => $plugin_slug,
					'test_tags' => [ 'default' ],
					'action'    => $dependencies_option,
				];
			}
		}
	}

	/**
	 * Handle numeric keys found in qit.yml for plugins.
	 * Convert them to slug-based keys while preserving source as numeric.
	 *
	 * @param array<string,mixed> $env_config
	 *
	 * @return void
	 */
	protected function normalize_numeric_qit_plugins( array &$env_config ): void {
		$updated_plugins = [];
		foreach ( $env_config['plugins'] as $key => $cfg ) {
			if ( is_numeric( $key ) ) {
				try {
					$resolved_slug = $this->woo_extensions_list->get_woo_extension_slug_by_id( (int) $key );
					// Keep original numeric ID as source if not set.
					if ( ! isset( $cfg['source'] ) ) {
						$cfg['source'] = (string) $key;
					}
					// Use resolved slug as new key.
					$updated_plugins[ $resolved_slug ] = $cfg;
				} catch ( \Exception $e ) {
					// If fails, just keep numeric key as a slug.
					if ( ! isset( $cfg['source'] ) ) {
						$cfg['source'] = (string) $key;
					}
					$updated_plugins[ $key ] = $cfg;
				}
			} else {
				$updated_plugins[ $key ] = $cfg;
			}
		}
		$env_config['plugins'] = $updated_plugins;
	}

	public function is_development(): bool {
		return $this->is_development;
	}
}
