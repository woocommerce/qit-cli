<?php

namespace QIT_CLI\Blueprint;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Translates a WordPress Playground blueprint JSON into a QIT environment config array.
 *
 * Blueprint values are treated as a "base" — QIT-specific fields (woo, object_cache, sut, test_packages)
 * are merged on top by the caller.
 */
class BlueprintTranslator {
	/**
	 * Steps that are silently ignored (QIT handles these natively).
	 *
	 * @var array<string>
	 */
	private const IGNORED_STEPS = [
		'login',
		'landingPage',
	];

	/**
	 * Steps that emit a warning and are skipped.
	 *
	 * @var array<string>
	 */
	private const UNSUPPORTED_STEPS = [
		'enableMultisite',
		'writeFile',
		'cp',
		'mv',
		'rm',
		'rmdir',
		'mkdir',
		'unzip',
		'importWxr',
		'importThemeStarterContent',
		'setPhpIniEntry',
	];

	/**
	 * Translate a parsed blueprint JSON into a QIT environment config array.
	 *
	 * @param array<string,mixed>  $blueprint The parsed blueprint JSON.
	 * @param OutputInterface|null $output    Optional output for warnings.
	 *
	 * @return array<string,mixed> QIT environment config with keys: php, wp, plugins, themes, setup_commands.
	 */
	public static function translate( array $blueprint, ?OutputInterface $output = null ): array {
		$config = [];

		// Translate preferred versions (map to QIT keys).
		if ( isset( $blueprint['preferredVersions'] ) ) {
			$versions = $blueprint['preferredVersions'];
			if ( ! empty( $versions['php'] ) && $versions['php'] !== 'latest' ) {
				$config['php_version'] = $versions['php'];
			}
			if ( ! empty( $versions['wp'] ) && $versions['wp'] !== 'latest' ) {
				$config['wordpress_version'] = $versions['wp'];
			}
		}

		$plugins        = [];
		$themes         = [];
		$setup_commands = [];

		// Translate steps.
		$steps = $blueprint['steps'] ?? [];
		foreach ( $steps as $step ) {
			if ( ! is_array( $step ) || empty( $step['step'] ) ) {
				continue;
			}

			$step_type = $step['step'];

			// Silently ignored steps.
			if ( in_array( $step_type, self::IGNORED_STEPS, true ) ) {
				continue;
			}

			// Ignored blueprint-level features.
			if ( $step_type === 'setSiteOptions' && isset( $step['options'] ) ) {
				foreach ( $step['options'] as $key => $value ) {
					$escaped_value    = is_string( $value ) ? $value : json_encode( $value );
					$setup_commands[] = sprintf( 'wp option update %s %s', escapeshellarg( $key ), escapeshellarg( $escaped_value ) );
				}
				continue;
			}

			// Unsupported steps: warn and skip.
			if ( in_array( $step_type, self::UNSUPPORTED_STEPS, true ) ) {
				if ( $output ) {
					$output->writeln( sprintf( '<comment>Blueprint: unsupported step "%s" — skipping.</comment>', $step_type ) );
				}
				continue;
			}

			switch ( $step_type ) {
				case 'installPlugin':
					$plugin = self::translate_install_extension( $step, 'plugin' );
					if ( $plugin ) {
						$plugins[] = $plugin;
					}
					break;

				case 'installTheme':
					$theme = self::translate_install_extension( $step, 'theme' );
					if ( $theme ) {
						$themes[] = $theme;
					}
					break;

				case 'defineWpConfigConsts':
					if ( isset( $step['consts'] ) && is_array( $step['consts'] ) ) {
						foreach ( $step['consts'] as $key => $value ) {
							$raw_flag = '';
							if ( is_bool( $value ) ) {
								$value    = $value ? 'true' : 'false';
								$raw_flag = ' --raw';
							} elseif ( is_int( $value ) || is_float( $value ) ) {
								$raw_flag = ' --raw';
							}
							$setup_commands[] = sprintf( 'wp config set %s %s%s', escapeshellarg( $key ), escapeshellarg( (string) $value ), $raw_flag );
						}
					}
					break;

				case 'wp-cli':
					if ( ! empty( $step['command'] ) ) {
						// Strip leading "wp " if present since docker_wp adds it.
						$command = $step['command'];
						if ( str_starts_with( $command, 'wp ' ) ) {
							$command = substr( $command, 3 );
						}
						$setup_commands[] = 'wp ' . $command;
					}
					break;

				case 'runPHP':
					if ( ! empty( $step['code'] ) ) {
						$setup_commands[] = sprintf( 'wp eval %s', escapeshellarg( $step['code'] ) );
					}
					break;

				case 'activatePlugin':
					if ( ! empty( $step['pluginPath'] ) ) {
						$slug             = basename( $step['pluginPath'], '.php' );
						$slug             = dirname( $step['pluginPath'] ) !== '.' ? dirname( $step['pluginPath'] ) : $slug;
						$setup_commands[] = sprintf( 'wp plugin activate %s', escapeshellarg( $slug ) );
					}
					break;

				case 'activateTheme':
					if ( ! empty( $step['themeFolderName'] ) ) {
						$setup_commands[] = sprintf( 'wp theme activate %s', escapeshellarg( $step['themeFolderName'] ) );
					}
					break;

				default:
					if ( $output ) {
						$output->writeln( sprintf( '<comment>Blueprint: unknown step "%s" — skipping.</comment>', $step_type ) );
					}
					break;
			}
		}

		// Note: blueprint features (e.g., features.networking) are silently ignored — QIT manages these.

		if ( ! empty( $plugins ) ) {
			$config['plugins'] = $plugins;
		}
		if ( ! empty( $themes ) ) {
			$config['themes'] = $themes;
		}
		if ( ! empty( $setup_commands ) ) {
			$config['setup_commands'] = $setup_commands;
		}

		return $config;
	}

	/**
	 * Translate an installPlugin or installTheme step into a QIT extension config.
	 *
	 * @param array<string,mixed> $step The blueprint step.
	 * @param string              $type 'plugin' or 'theme'.
	 *
	 * @return array<string,string>|null The extension config, or null if not translatable.
	 */
	private static function translate_install_extension( array $step, string $type ): ?array {
		$resource = $step[ $type === 'plugin' ? 'pluginData' : 'themeData' ]
			?? $step[ $type === 'plugin' ? 'pluginZipFile' : 'themeZipFile' ]
			?? null;

		// Handle resource object format.
		if ( is_array( $resource ) ) {
			$resource_type = $resource['resource'] ?? null;

			if ( $resource_type === 'wordpress.org/plugins' || $resource_type === 'wordpress.org/themes' ) {
				$slug = $resource['slug'] ?? null;
				if ( $slug ) {
					return [
						'slug' => $slug,
						'from' => 'wporg',
					];
				}
			}

			if ( $resource_type === 'url' ) {
				$url = $resource['url'] ?? null;
				if ( $url ) {
					$slug = $step['options']['slug'] ?? self::slug_from_url( $url );
					return [
						'slug' => $slug,
						'from' => 'url',
						'url'  => $url,
					];
				}
			}
		}

		// Handle simple string (slug or URL).
		if ( is_string( $resource ) ) {
			if ( str_starts_with( $resource, 'http' ) ) {
				return [
					'slug' => self::slug_from_url( $resource ),
					'from' => 'url',
					'url'  => $resource,
				];
			}

			return [
				'slug' => $resource,
				'from' => 'wporg',
			];
		}

		return null;
	}

	/**
	 * Derive a slug from a URL by extracting the filename without extension.
	 *
	 * @param string $url The URL.
	 *
	 * @return string The derived slug.
	 */
	private static function slug_from_url( string $url ): string {
		$path     = parse_url( $url, PHP_URL_PATH ) ?? '';
		$filename = basename( $path );

		return pathinfo( $filename, PATHINFO_FILENAME ) ?: 'unknown';
	}
}
