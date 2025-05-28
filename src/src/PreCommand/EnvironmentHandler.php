<?php

namespace QIT_CLI\PreCommand;

use QIT_CLI\App;
use QIT_CLI\Config as QITConfig;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\Environments\Environment;
use QIT_CLI\Environment\EnvironmentVersionResolver;
use QIT_CLI\Environment\EnvParser;
use QIT_CLI\Environment\EnvVolumeParser;
use QIT_CLI\PreCommand\ConfigFile\ConfigParser;
use QIT_CLI\Environment\Extension;
use QIT_CLI\PreCommand\Download\Extensions\ExtensionDownloader;
use QIT_CLI\PreCommand\Download\Extensions\DependencyParser;
use QIT_CLI\PreCommand\Download\Tests\CustomTestsDownloader;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\normalize_path;

class EnvironmentHandler {
	public static function get_pluralizable_keys(): array {
		return [
			'plugin'        => 'plugins',
			'theme'         => 'themes',
			'volume'        => 'volumes',
			'php_extension' => 'php_extensions',
			'env'           => 'env_vars',
		];
	}

	public function build_env_info( InputInterface $input, OutputInterface $output, array $merged_options, ConfigParser $config ): EnvInfo {
		$environment = $input->getOption( 'environment' ) ?: 'default';
		$woo_version = isset( $merged_options['woo_version'] ) ? $merged_options['woo_version'] : null;

		$env_parser                     = App::make( EnvParser::class );
		$env_vars                       = $env_parser->parse(
			$input->getOption( 'env' ) ?: [],
			$input->getOption( 'env_file' ) ?: []
		);
		$env_vars['WP_CLI_CONFIG_PATH'] = '/qit/wp-cli.yml';

		$config_section = $config->get_environment( $environment );
		$config_envs    = isset( $config_section['env_vars'] ) && is_array( $config_section['env_vars'] ) ? $config_section['env_vars'] : [];
		$env_vars       = array_merge( $config_envs, $env_vars );

		$env_config = [];
		foreach ( $config_section as $key => $value ) {
			if ( $key === 'env_vars' ) {
				continue;
			}
			$mapped_key = self::get_pluralizable_keys()[ $key ] ?? $key;
			if ( in_array( $mapped_key, [ 'plugins', 'themes' ], true ) && is_array( $value ) ) {
				$env_config[ $mapped_key ] = array_map( function ( $item ) use ( $mapped_key ) {
					$type = $mapped_key === 'plugins' ? 'plugin' : 'theme';
					if ( is_string( $item ) ) {
						$extension = new Extension( $item, $type );
					} elseif ( is_array( $item ) && isset( $item['slug'] ) ) {
						$extension = new Extension( $item['slug'], $type );
						if ( isset( $item['from'] ) ) {
							$extension->from = $item['from'];
						}
						if ( isset( $item['path'] ) ) {
							$extension->directory = $item['path'];
						}
						if ( isset( $item['url'] ) ) {
							$extension->source = $item['url'];
						}
					} elseif ( $item instanceof Extension ) {
						$extension = $item;
					} else {
						throw new \RuntimeException( "Invalid $mapped_key config entry: " . json_encode( $item ) );
					}
					if ( ! isset( $extension->from ) ) {
						$extension->from = 'wporg';
					}

					return $extension;
				}, $value );
			} else {
				$env_config[ $mapped_key ] = $value;
			}
		}

		foreach ( $merged_options as $key => $value ) {
			if ( $key === 'env' || $key === 'env_file' ) {
				continue;
			}
			$mapped_key = self::get_pluralizable_keys()[ $key ] ?? $key;
			if ( $mapped_key === 'plugins' && ! empty( $value ) ) {
				$cli_plugins = [];
				$cli_values  = is_array( $value ) ? $value : [ $value ];
				$seen_slugs  = array_map( fn( $p ) => is_object( $p ) ? $p->slug : $p, $env_config['plugins'] ?? [] );
				foreach ( $cli_values as $plugin ) {
					if ( is_object( $plugin ) && isset( $plugin->slug ) ) {
						$slug = $plugin->slug;
						if ( ! in_array( $slug, $seen_slugs, true ) ) {
							$cli_plugins[] = $plugin;
							$seen_slugs[]  = $slug;
						}
					} elseif ( is_string( $plugin ) ) {
						$slug = $plugin;
						if ( ! in_array( $slug, $seen_slugs, true ) ) {
							$extension       = new Extension( $slug, 'plugin' );
							$extension->from = 'wporg';
							$cli_plugins[]   = $extension;
							$seen_slugs[]    = $slug;
						}
					} else {
						throw new \RuntimeException( 'CLI plugin must be a string or Extension object, got ' . gettype( $plugin ) );
					}
				}
				$env_config['plugins'] = array_merge( $env_config['plugins'] ?? [], $cli_plugins );
			} elseif ( $mapped_key === 'themes' && ! empty( $value ) ) {
				$cli_themes = [];
				$cli_values = is_array( $value ) ? $value : [ $value ];
				$seen_slugs = array_map( fn( $t ) => is_object( $t ) ? $t->slug : $t, $env_config['themes'] ?? [] );
				foreach ( $cli_values as $theme ) {
					if ( is_object( $theme ) && isset( $theme->slug ) ) {
						$slug = $theme->slug;
						if ( ! in_array( $slug, $seen_slugs, true ) ) {
							$cli_themes[] = $theme;
							$seen_slugs[] = $slug;
						}
					} elseif ( is_string( $theme ) ) {
						$slug = $theme;
						if ( ! in_array( $slug, $seen_slugs, true ) ) {
							$extension       = new Extension( $slug, 'theme' );
							$extension->from = 'wporg';
							$cli_themes[]    = $extension;
							$seen_slugs[]    = $slug;
						}
					} else {
						throw new \RuntimeException( 'CLI theme must be a string or Extension object, got ' . gettype( $theme ) );
					}
				}
				$env_config['themes'] = array_merge( $env_config['themes'] ?? [], $cli_themes );
			} elseif ( ( isset( $env_config[ $mapped_key ] ) && is_array( $env_config[ $mapped_key ] ) ) || is_array( $value ) ) {
				$cli_values                = is_array( $value ) ? $value : [ $value ];
				$config_values             = isset( $env_config[ $mapped_key ] ) ? (array) $env_config[ $mapped_key ] : [];
				$env_config[ $mapped_key ] = array_unique( array_merge( $config_values, $cli_values ), SORT_REGULAR );
			} elseif ( $value !== null ) {
				$env_config[ $mapped_key ] = $value;
			}
		}

		$env_config['env_vars'] = $env_vars;

		$env_config = array_merge( [
			'environment'             => 'e2e',
			'dependencies_mode'       => 'activate',
			'php_version'             => '8.0',
			'wp_version'              => 'stable',
			'woo_version'             => null,
			'plugins'                 => [],
			'themes'                  => [],
			'volumes'                 => [],
			'php_extensions'          => [],
			'object_cache'            => false,
			'env_vars'                => [],
			'extension_set'           => null,
			'tunnel'                  => false,
			'tunnel_type'             => 'no_tunnel',
			'runner_args'             => [],
			'domain'                  => 'localhost',
			'skip_activating_plugins' => false,
			'skip_activating_themes'  => false,
			'tests'                   => [],
			'playwright_config'       => [],
			'pw_test_tag'             => '',
			'sut_slug'                => null,
			'sut_type'                => null,
			'sut_entrypoint'          => null,
			'sut_path'                => null,
			'sut_id'                  => null,
			'nginx_port'              => null,
			'notify'                  => null,
			'is_development_build'    => false,
		], $env_config );

		$tunnel                    = $merged_options['tunnel'] ?? 'no_tunnel';
		$env_config['tunnel']      = $tunnel !== 'no_tunnel';
		$env_config['tunnel_type'] = $tunnel;

		$env_info                = new E2EEnvInfo();
		$env_info->env_id        = uniqid();
		$env_info->environment   = $env_config['environment'];
		$env_info->temporary_env = normalize_path( Environment::get_temp_envs_dir() . $env_info->environment . '-' . $env_info->env_id );
		$env_info->created_at    = time();
		$env_info->status        = 'pending';
		if ( ! empty( $env_config['extension_set'] ) ) {
			$env_info              = App::make( ExtensionSetResolver::class )->resolve( $env_info, [ 'overrides' => [ 'extension_set' => $env_config['extension_set'] ] ] );
			$env_config['plugins'] = array_merge( $env_config['plugins'], $env_info->plugins );
			$env_config['themes']  = array_merge( $env_config['themes'], $env_info->themes );
		}

		$extension_downloader = App::make( ExtensionDownloader::class );
		$dependency_parser    = App::make( DependencyParser::class );
		$tests_downloader     = App::make( CustomTestsDownloader::class );
		$cache_dir            = normalize_path( QITConfig::get_qit_dir() . 'cache' );
		$seen_slugs           = [];
		$pending_extensions   = array_merge( $env_config['plugins'], $env_config['themes'] );
		$final_plugins        = $env_config['plugins'];
		$final_themes         = $env_config['themes'];
		$php_extensions       = $env_config['php_extensions'];

		while ( ! empty( $pending_extensions ) ) {
			$current_batch      = $pending_extensions;
			$pending_extensions = [];

			$extension_downloader->download( $env_info, $cache_dir, array_filter( $current_batch, fn( $e ) => $e->type === 'plugin' ), array_filter( $current_batch, fn( $e ) => $e->type === 'theme' ) );

			foreach ( $current_batch as $ext ) {
				if ( in_array( $ext->slug, $seen_slugs, true ) ) {
					continue;
				}
				$seen_slugs[] = $ext->slug;

				if ( ! empty( $ext->downloaded_source ) ) {
					$deps = $dependency_parser->parse( $ext->downloaded_source, $ext->type );
					foreach ( $deps as $dep_slug ) {
						if ( ! in_array( $dep_slug, $seen_slugs, true ) ) {
							$dep_ext                      = new Extension( $dep_slug, 'plugin' );
							$dep_ext->from                = 'wporg';
							$dep_ext->added_automatically = 'Added as a dependency';
							$pending_extensions[]         = $dep_ext;
							$final_plugins[]              = $dep_ext;
						}
					}
				}
			}
		}

		$tests_downloader->download( $env_info, $cache_dir, $final_plugins, $final_themes );

		$has_woocommerce = false;
		foreach ( $final_plugins as $k => $plugin ) {
			if ( $plugin->slug === 'woocommerce' ) {
				$has_woocommerce = $k;
				break;
			}
		}

		if ( ! empty( $woo_version ) && $has_woocommerce !== false ) {
			$output->writeln( "<comment>Overriding WooCommerce version to: $woo_version</comment>" );
			unset( $final_plugins[ $has_woocommerce ] );
		}

		if ( ! empty( $woo_version ) ) {
			$woo_plugin                      = EnvironmentVersionResolver::resolve_woo( $woo_version, $final_plugins );
			$woo_plugin->added_automatically = 'Added due to specified WooCommerce version';
			// Preserve original 'from' setting
			$woo_plugin->from   = 'wporg';
			$woo_plugin->source = 'https://downloads.wordpress.org/plugin/woocommerce.8.0.0.zip';
			$final_plugins[]    = $woo_plugin;
		}

		$env_config['volumes'] = App::make( EnvVolumeParser::class )->parse_volumes( $env_config['volumes'] ?: [] );

		foreach ( $env_config as $key => &$value ) {
			switch ( $key ) {
				case 'php_extensions':
					if ( ! is_array( $value ) ) {
						throw new \RuntimeException( 'PHP extensions must be an array' );
					}
					$value = array_unique( array_filter( $value, 'is_string' ) );
					foreach ( $value as $ext_name ) {
						if ( ! preg_match( '/^[a-z0-9_-]+$/i', $ext_name ) ) {
							throw new \RuntimeException( 'Invalid PHP extension name: ' . $ext_name );
						}
						if ( strlen( $ext_name ) > 50 ) {
							throw new \RuntimeException( 'PHP extension name too long: ' . $ext_name );
						}
					}
					break;
				case 'wp_version':
					$value = EnvironmentVersionResolver::resolve_wp( $value );
					break;
			}
		}

		$env_info->dependencies_mode       = $env_config['dependencies_mode'];
		$env_info->volumes                 = $env_config['volumes'];
		$env_info->docker_images           = $env_config['docker_images'] ?? [];
		$env_info->docker_network          = $env_config['docker_network'] ?? '';
		$env_info->php_extensions          = $php_extensions;
		$env_info->plugins                 = $final_plugins;
		$env_info->themes                  = $final_themes;
		$env_info->tunnel                  = $env_config['tunnel'];
		$env_info->tunnel_type             = $env_config['tunnel_type'];
		$env_info->runner_args             = $env_config['runner_args'];
		$env_info->wp_version              = $env_config['wp_version'];
		$env_info->object_cache            = $env_config['object_cache'];
		$env_info->php_version             = $env_config['php_version'];
		$env_info->domain                  = ( getenv( 'QIT_EXPOSE_ENVIRONMENT_TO' ) === 'DOCKER' ) ? "qitenvnginx{$env_info->env_id}" : ( getenv( 'QIT_DOMAIN' ) ? getenv( 'QIT_DOMAIN' ) : 'localhost' );
		$env_info->skip_activating_plugins = $env_config['skip_activating_plugins'];
		$env_info->skip_activating_themes  = $env_config['skip_activating_themes'];
		$env_info->tests                   = $env_config['tests'];
		$env_info->playwright_config       = $env_config['playwright_config'] ?? [];
		$env_info->pw_test_tag             = $env_config['pw_test_tag'];
		$env_info->woo_version             = $env_config['woo_version'] ?? '';
		$env_info->is_development_build    = $env_config['is_development_build'];
		$env_info->notify                  = $env_config['notify'] ?? '';
		$env_info->env                     = $env_config['env_vars'];

		file_put_contents( '/tmp/qit_debug.log', 'Final Env Info: ' . print_r( (array) $env_info, true ) . "\n", FILE_APPEND );

		return $env_info;
	}
}