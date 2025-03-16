<?php

namespace QIT_CLI\LocalTests;

use QIT_CLI\App;
use QIT_CLI\Environment\EnvConfigLoader;
use QIT_CLI\Environment\Extension;
use QIT_CLI\PluginDependencies;
use QIT_CLI\WooExtensionsList;
use Symfony\Component\Console\Input\InputInterface;

class ConfigurationProcessor {
	/** @var EnvConfigLoader */
	protected $config_loader;

	/** @var WooExtensionsList */
	protected $woo_extensions_list;

	/** @var PluginDependencies */
	protected $plugin_dependencies;

	public function __construct(
		EnvConfigLoader $config_loader,
		WooExtensionsList $woo_extensions_list,
		PluginDependencies $plugin_dependencies
	) {
		$this->config_loader       = $config_loader;
		$this->woo_extensions_list = $woo_extensions_list;
		$this->plugin_dependencies = $plugin_dependencies;
	}

	/**
	 * @param string|null $woo_extension_slug The main SUT slug (if any).
	 * @param int|null $woo_extension_id The main SUT numeric ID (if any).
	 * @param string|null $sut_type 'plugin' or 'theme' or null if none.
	 * @param InputInterface $input CLI input interface (for reading e.g. --source, --dependencies).
	 * @param array $env_up_options The array of --options we'll pass to env:up.
	 *
	 * @return array Updated $env_up_options
	 */
	public function process_configuration(
		?string $woo_extension_slug,
		?int $woo_extension_id,
		?string $sut_type,
		InputInterface $input,
		array $env_up_options
	): array {
		// 1) Possibly add the SUT to the --plugin / --theme list if we have a main extension.
		//    Because EnvConfigLoader and PluginsAndThemesParser will handle the actual parse+merge,
		//    here we only need to ensure we set the CLI option in short syntax, e.g. "foo:test:tag1,tag2".
		if ( $woo_extension_slug ) {
			// If no explicit SUT type, default to plugin
			if ( ! $sut_type ) {
				$sut_type = 'plugin';
			}

			// Determine SUT action
			$sut_action = $input->getOption( 'sut_action' ) ?: Extension::ACTIONS['test'];

			// Determine test tags for the SUT
			$test_arg  = $input->getArgument( 'test' );
			$test_tags = $test_arg ? explode( ',', $test_arg ) : [ 'default' ];

			// If user specified a local/URL source, we put that in the short syntax's "source" part
			$source_option = $input->getOption( 'source' ) ?? $woo_extension_slug;

			/**
			 * Add to --plugin or --theme using short syntax:
			 *   slug:action:tag1,tag2
			 *
			 * The EnvConfigLoader + PluginsAndThemesParser will parse each entry and do final normalization.
			 */
			$sut_string = sprintf(
				'%s:%s:%s',
				$source_option,
				$sut_action,
				implode( ',', $test_tags )
			);

			if ( $sut_type === 'theme' ) {
				$env_up_options['--theme'][] = $sut_string;
			} else {
				$env_up_options['--plugin'][] = $sut_string;
			}

			// Apply dependencies if needed
			$dependencies_option = $input->getOption( 'dependencies' ) ?? Extension::ACTIONS['bootstrap'];
			if ( ! empty( $woo_extension_id ) && $dependencies_option !== 'none' ) {
				$env_up_options = $this->apply_dependencies(
					$woo_extension_id,
					$dependencies_option,
					$env_up_options
				);
			}

			return $env_up_options;
		}

		return $env_up_options;
	}

	/**
	 * Adds dependency plugins (and PHP extensions) to $env_up_options in short syntax,
	 * using PluginDependencies. This replicates old "apply_dependencies()" logic
	 * but in a minimal way.
	 */
	protected function apply_dependencies(
		int $woo_extension_id,
		string $dependencies_option,
		array $env_up_options
	): array {
		$dependencies = $this->plugin_dependencies->get_plugin_and_php_ext_dependencies(
			$woo_extension_id,
			[] // Additional data if needed
		);

		// Add any required PHP extensions
		if ( ! isset( $env_up_options['--php_extension'] ) ) {
			$env_up_options['--php_extension'] = [];
		}
		foreach ( $dependencies['php_extensions'] as $php_ext ) {
			if ( ! in_array( $php_ext, $env_up_options['--php_extension'], true ) ) {
				$env_up_options['--php_extension'][] = $php_ext;
			}
		}

		// Add plugin dependencies in short syntax "slug:action".
		if ( ! isset( $env_up_options['--plugin'] ) ) {
			$env_up_options['--plugin'] = [];
		}

		$woo_version = $env_up_options['--woo'] ?? null;

		foreach ( $dependencies['plugins'] as $dep_plugin ) {
			// skip if user pinned a specific Woo version
			if ( $woo_version && stripos( $dep_plugin, 'woocommerce:' ) !== false ) {
				continue;
			}

			// Append :{dependencies_option} to ensure we get the correct action
			$formatted_plugin = sprintf( '%s:%s', $dep_plugin, $dependencies_option );

			// Skip duplicates
			if ( ! in_array( $formatted_plugin, $env_up_options['--plugin'], true ) ) {
				$env_up_options['--plugin'][] = $formatted_plugin;
			}
		}

		return $env_up_options;
	}
}