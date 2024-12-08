<?php

namespace QIT_CLI\LocalTests;

use QIT_CLI\App;
use QIT_CLI\Environment\EnvConfigLoader;
use QIT_CLI\Environment\Extension;
use Symfony\Component\Console\Input\InputInterface;

class ConfigurationProcessor {
	/** @var EnvConfigLoader */
	protected $config_loader;

	/** @var bool */
	protected $is_development;

	public function __construct( EnvConfigLoader $config_loader ) {
		$this->config_loader = $config_loader;
	}

	/**
	 * Process and merge configuration from qit.yml and CLI options into a final set of env:up options.
	 *
	 * @param InputInterface      $input
	 * @param array<string,mixed> $env_up_options The partially processed env_up_options from RunE2ECommand.
	 * @param string              $sut_type Either 'plugin' or 'theme'.
	 *
	 * @return array<string,mixed> Final configuration suitable for passing to env:up (e.g., $env_up_options).
	 */
	public function process_configuration( InputInterface $input, array $env_up_options, string $sut_type ): array {
		// If a config override is provided via CLI, set it.
		if ( $input->getOption( 'config' ) ) {
			App::setVar( 'QIT_CONFIG_OVERRIDE', $input->getOption( 'config' ) );
		}

		// Load qit.yml config.
		$env_config = $this->config_loader->load_config();
		if ( ! isset( $env_config['plugins'] ) || ! is_array( $env_config['plugins'] ) ) {
			$env_config['plugins'] = [];
		}
		if ( ! isset( $env_config['themes'] ) || ! is_array( $env_config['themes'] ) ) {
			$env_config['themes'] = [];
		}

		$woo_extension    = $input->getArgument( 'woo_extension' );
		$test             = $input->getArgument( 'test' );
		$source_option    = $input->getOption( 'source' );
		$source           = ! empty( $source_option ) ? $source_option : '';
		$sut_action       = $input->getOption( 'sut_action' );
		$dependencies_opt = $input->getOption( 'dependencies' ) ?? Extension::ACTIONS['bootstrap'];

		$has_sut = ! empty( $woo_extension ) || ! empty( $source ) || ! empty( $sut_action );

		if ( $has_sut ) {
			$this->finalize_sut_definition(
				$woo_extension,
				$source,
				$test,
				$dependencies_opt,
				$env_up_options,
				$env_config,
				$input,
				$sut_type
			);
		} else {
			// No SUT: Just handle CLI plugins/themes and skip dependencies.
			$this->add_cli_plugins( $env_config, $input );
			$this->normalize_plugins( $env_config );
			$this->normalize_themes( $env_config );
			if ( $sut_type === 'theme' ) {
				$env_up_options['--theme'] = array_values( $env_config['themes'] );
			} else {
				$env_up_options['--plugin'] = array_values( $env_config['plugins'] );
			}
		}

		$this->is_development = ! empty( $input->getOption( 'source' ) ) && file_exists( $input->getOption( 'source' ) );

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
		string $sut_type
	): void {
		if ( empty( $woo_extension ) ) {
			// If no woo_extension, there's no main SUT, so skip dependencies.
			$this->add_cli_plugins( $env_config, $input );
			$this->normalize_plugins( $env_config );
			$this->normalize_themes( $env_config );
			if ( $sut_type === 'theme' ) {
				$env_up_options['--theme'] = array_values( $env_config['themes'] );
			} else {
				$env_up_options['--plugin'] = array_values( $env_config['plugins'] );
			}

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

		// Ensure action is 'test' if not set.
		if ( ! isset( $sut_base['action'] ) ) {
			$sut_base['action'] = Extension::ACTIONS['test'];
		}

		// Place SUT in themes if sut_type is 'theme', else in plugins.
		if ( $sut_type === 'theme' ) {
			$env_config['themes'][ $woo_extension ] = $sut_base;
		} else {
			$env_config['plugins'][ $woo_extension ] = $sut_base;
		}

		$woo_extension_id = App::getVar( 'QIT_SUT' );

		$this->apply_dependencies( $woo_extension_id, $dependencies_option, $env_up_options );
		$this->add_cli_plugins( $env_config, $input );
		$this->normalize_plugins( $env_config );
		$this->normalize_themes( $env_config );

		if ( $sut_type === 'theme' ) {
			$env_up_options['--theme'] = array_values( $env_config['themes'] );
		} else {
			$env_up_options['--plugin'] = array_values( $env_config['plugins'] );
		}
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
			$parts      = explode( ':', $cli_plugin );
			$cli_slug   = $parts[0];
			$cli_action = Extension::ACTIONS['test'];
			$cli_tags   = [];

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
					'source'    => $cli_slug,
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
				$plugin_config['action'] = Extension::ACTIONS['test'];
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
	 * Add dependencies as strings to --plugin (and php_extensions to --php_extension).
	 *
	 * @param int|null            $woo_extension_id
	 * @param string              $dependencies_option
	 * @param array<string,mixed> $env_up_options
	 *
	 * @return void
	 */
	protected function apply_dependencies( $woo_extension_id, string $dependencies_option, array &$env_up_options ): void {
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

		// Add PHP extensions if any.
		foreach ( $dependencies['php_extensions'] as $php_extension ) {
			if ( ! in_array( $php_extension, $env_up_options['--php_extension'], true ) ) {
				$env_up_options['--php_extension'][] = $php_extension;
			}
		}

		// Add plugin dependencies as strings.
		$woo_version = $env_up_options['--woo'] ?? null;

		foreach ( $dependencies['plugins'] as $dep_plugin ) {
			if ( $woo_version && stripos( $dep_plugin, 'woocommerce:' ) !== false ) {
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
				$formatted_plugin             = "{$dep_plugin}:{$dependencies_option}";
				$env_up_options['--plugin'][] = $formatted_plugin;
			}
		}
	}

	public function is_development(): bool {
		return $this->is_development;
	}
}
