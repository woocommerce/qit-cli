<?php

namespace QIT_CLI\Blueprints;

/**
 * Translates a WordPress Playground Blueprint (v1) into a QIT environment.
 *
 * Two kinds of output:
 *  - Declarative: versions, plugins and themes become a qit.json environment block.
 *  - Imperative: everything else becomes shell/WP-CLI commands run in the container.
 *
 * Playground executes Blueprints against PHP-WASM + SQLite; QIT runs real PHP-FPM +
 * MySQL in Docker. Steps that only make sense inside Playground are reported as
 * unsupported instead of being silently dropped.
 *
 * @see https://developer.wordpress.org/playground/blueprints/steps
 */
class BlueprintTranspiler {

	/** Where Playground mounts WordPress. */
	public const PLAYGROUND_WP_ROOT = '/wordpress';

	/** Where QIT mounts WordPress inside the container. */
	public const QIT_WP_ROOT = '/var/www/html';

	/** @var array<string, string> Playground PHP aliases → concrete QIT PHP versions. */
	private static array $php_aliases = [
		'latest' => '8.3',
		'next'   => '8.4',
	];

	/** @var array<string, string> Playground WP aliases → QIT WordPress versions. */
	private static array $wp_aliases = [
		'latest'  => 'stable',
		'beta'    => 'rc',
		'rc'      => 'rc',
		'nightly' => 'nightly',
		'trunk'   => 'nightly',
	];

	/** @var string|null Directory holding the Blueprint, for resolving "bundled" resources. */
	private ?string $bundle_dir = null;

	/**
	 * @param array<string, mixed> $blueprint      The decoded Blueprint.
	 * @param string|null          $blueprint_path Path the Blueprint was read from. Required to
	 *                                             resolve "bundled" resources, which are relative
	 *                                             to the Blueprint's own directory.
	 */
	public function transpile( array $blueprint, ?string $blueprint_path = null ): TranspiledBlueprint {
		$result = new TranspiledBlueprint();

		$this->bundle_dir = $blueprint_path !== null ? ( realpath( dirname( $blueprint_path ) ) ?: null ) : null;

		$this->transpile_preferred_versions( $blueprint, $result );
		$this->transpile_features( $blueprint, $result );

		if ( isset( $blueprint['landingPage'] ) && is_string( $blueprint['landingPage'] ) ) {
			$result->landing_page = $blueprint['landingPage'];
		}

		// Top-level shorthands, applied before steps so steps can override them.
		$this->transpile_plugin_shorthand( $blueprint, $result );
		$this->transpile_constants( $blueprint['constants'] ?? [], $result );
		$this->transpile_site_options( $blueprint['siteOptions'] ?? [], $result );

		foreach ( $blueprint['steps'] ?? [] as $step ) {
			// Blueprints allow false/null entries so steps can be toggled off.
			if ( empty( $step ) || ! is_array( $step ) ) {
				continue;
			}
			$this->transpile_step( $step, $result );
		}

		$result->steps = $this->coalesce_plugin_steps( $result->steps );

		return $result;
	}

	/**
	 * Merge runs of `wp plugin activate|deactivate` into single commands.
	 *
	 * Blueprints routinely install a dozen plugins deactivated; one WP-CLI call per
	 * plugin means one WordPress bootstrap per plugin.
	 *
	 * @param array<int, array{command: string, description: string, tolerant: bool}> $steps
	 *
	 * @return array<int, array{command: string, description: string, tolerant: bool}>
	 */
	private function coalesce_plugin_steps( array $steps ): array {
		$merged = [];

		foreach ( $steps as $step ) {
			$matches = [];

			if ( preg_match( "/^wp plugin (activate|deactivate) ('[^']+')$/", $step['command'], $matches ) !== 1 ) {
				$merged[] = $step;
				continue;
			}

			$previous = end( $merged );
			$verb     = $matches[1];

			if ( $previous !== false && strpos( $previous['command'], "wp plugin {$verb} " ) === 0 ) {
				$index                           = count( $merged ) - 1;
				$merged[ $index ]['command']    .= ' ' . $matches[2];
				$merged[ $index ]['slug_count']  = ( $merged[ $index ]['slug_count'] ?? 1 ) + 1;
				$merged[ $index ]['description'] = sprintf(
					'%s %d plugins',
					$verb === 'activate' ? 'Activate' : 'Deactivate',
					$merged[ $index ]['slug_count']
				);
				continue;
			}

			$merged[] = $step;
		}

		// Drop the bookkeeping key so the emitted steps keep their documented shape.
		return array_map( static function ( array $step ): array {
			unset( $step['slug_count'] );

			return $step;
		}, $merged );
	}

	/**
	 * @param array<string, mixed> $blueprint
	 */
	private function transpile_preferred_versions( array $blueprint, TranspiledBlueprint $result ): void {
		$preferred = $blueprint['preferredVersions'] ?? [];

		if ( ! empty( $preferred['php'] ) ) {
			$php                               = (string) $preferred['php'];
			$result->env_config['php_version'] = self::$php_aliases[ $php ] ?? $php;

			if ( isset( self::$php_aliases[ $php ] ) ) {
				$result->add_warning( sprintf(
					'preferredVersions.php "%s" resolved to PHP %s (QIT needs a concrete version).',
					$php,
					$result->env_config['php_version']
				) );
			}
		}

		if ( ! empty( $preferred['wp'] ) ) {
			$wp                                      = (string) $preferred['wp'];
			$result->env_config['wordpress_version'] = self::$wp_aliases[ $wp ] ?? $wp;
		}
	}

	/**
	 * @param array<string, mixed> $blueprint
	 */
	private function transpile_features( array $blueprint, TranspiledBlueprint $result ): void {
		$features = $blueprint['features'] ?? [];

		if ( array_key_exists( 'networking', $features ) && $features['networking'] === false ) {
			$result->env_config['network_mode'] = 'offline';
		}

		if ( ! empty( $blueprint['extraLibraries'] ) ) {
			$result->add_warning( 'extraLibraries ignored: QIT environments always ship WP-CLI.' );
		}

		if ( ! empty( $blueprint['phpExtensionBundles'] ) ) {
			$result->add_warning( sprintf(
				'phpExtensionBundles (%s) ignored: QIT images bundle their own extensions. Add --php_extension if one is missing.',
				implode( ', ', (array) $blueprint['phpExtensionBundles'] )
			) );
		}
	}

	/**
	 * The top-level `plugins` shorthand: a list of slugs, URLs or resources.
	 *
	 * @param array<string, mixed> $blueprint
	 */
	private function transpile_plugin_shorthand( array $blueprint, TranspiledBlueprint $result ): void {
		foreach ( $blueprint['plugins'] ?? [] as $plugin ) {
			$this->add_extension( 'plugins', $plugin, [], $result );
		}
	}

	/**
	 * @param array<string, mixed> $step
	 */
	private function transpile_step( array $step, TranspiledBlueprint $result ): void {
		$name = $step['step'] ?? '';

		switch ( $name ) {
			case 'installPlugin':
				$this->add_extension( 'plugins', $step['pluginData'] ?? ( $step['pluginZipFile'] ?? null ), $step['options'] ?? [], $result );
				break;

			case 'installTheme':
				$this->add_extension( 'themes', $step['themeData'] ?? ( $step['themeZipFile'] ?? null ), $step['options'] ?? [], $result );
				break;

			case 'activatePlugin':
				$slug = $this->plugin_slug_from_path( (string) ( $step['pluginPath'] ?? $step['pluginName'] ?? '' ) );
				if ( $slug !== '' ) {
					$result->add_step( 'wp plugin activate ' . escapeshellarg( $slug ), 'Activate plugin ' . $slug );
				}
				break;

			case 'activateTheme':
				$theme = (string) ( $step['themeFolderName'] ?? '' );
				if ( $theme !== '' ) {
					$result->add_step( 'wp theme activate ' . escapeshellarg( $theme ), 'Activate theme ' . $theme );
				}
				break;

			case 'login':
				$user = (string) ( $step['username'] ?? 'admin' );
				if ( $user === 'admin' ) {
					$result->add_warning( 'login step ignored: QIT environments already provide a logged-in "admin" user.' );
				} else {
					$result->add_warning( sprintf(
						'login step for user "%s" ignored: QIT signs in as "admin". Add a createUser step or a setup command if you need another user.',
						$user
					) );
				}
				break;

			case 'defineWpConfigConsts':
				$this->transpile_constants( $step['consts'] ?? [], $result );
				break;

			case 'setSiteOptions':
				$this->transpile_site_options( $step['options'] ?? [], $result );
				break;

			case 'updateUserMeta':
				$user_id = (int) ( $step['userId'] ?? 1 );
				foreach ( $step['meta'] ?? [] as $key => $value ) {
					$result->add_step(
						sprintf(
							'wp user meta update %d %s %s%s',
							$user_id,
							escapeshellarg( (string) $key ),
							escapeshellarg( $this->scalar_to_cli( $value ) ),
							$this->needs_json_format( $value ) ? ' --format=json' : ''
						),
						sprintf( 'Update user %d meta %s', $user_id, $key ),
						true
					);
				}
				break;

			case 'setSiteLanguage':
				$language = (string) ( $step['language'] ?? '' );
				if ( $language !== '' ) {
					$result->add_step(
						'wp language core install ' . escapeshellarg( $language ) . ' --activate',
						'Set site language to ' . $language
					);
				}
				break;

			case 'runPHP':
				$this->add_payload_step( $this->translate_paths_in_code( $step['code'] ?? '' ), 'php', 'wp eval-file %s', 'Run PHP', $result );
				break;

			case 'runSql':
				if ( ( $step['sql']['resource'] ?? '' ) === 'bundled' ) {
					$bundled = $this->bundled_container_path( $step['sql'], $result );

					if ( $bundled !== null ) {
						$result->add_step( sprintf( 'wp db query < %s', escapeshellarg( $bundled ) ), 'Run bundled SQL' );
						$result->add_warning( 'runSql executed against MySQL: Blueprints written for Playground target SQLite and may not be portable.' );
					}
					break;
				}

				$sql = $this->resource_contents( $step['sql'] ?? null, $result, 'runSql' );
				if ( $sql !== null ) {
					$sql = (string) $this->translate_paths_in_code( $sql );
					$this->add_payload_step( $sql, 'sql', 'wp db query < %s', 'Run SQL', $result );
					$result->add_warning( 'runSql executed against MySQL: Blueprints written for Playground target SQLite and may not be portable.' );
				}
				break;

			case 'wp-cli':
				$command = trim( (string) $this->translate_paths_in_code( (string) ( $step['command'] ?? '' ) ) );
				if ( $command !== '' ) {
					$result->add_step( $this->normalize_wp_cli( $command ), 'WP-CLI: ' . $command );
				}
				break;

			case 'writeFile':
				if ( ( $step['data']['resource'] ?? '' ) === 'bundled' ) {
					$bundled = $this->bundled_container_path( $step['data'], $result );
					$path    = $this->translate_path( (string) ( $step['path'] ?? '' ) );

					if ( $bundled !== null ) {
						$result->add_step(
							sprintf( 'mkdir -p %s && cp %s %s', escapeshellarg( dirname( $path ) ), escapeshellarg( $bundled ), escapeshellarg( $path ) ),
							'Write bundled file to ' . $path
						);
					}
					break;
				}

				$contents = $this->resource_contents( $step['data'] ?? null, $result, 'writeFile' );
				if ( $contents !== null ) {
					$path = $this->translate_path( (string) ( $step['path'] ?? '' ) );
					$result->add_step( $this->write_file_command( $path, $contents ), 'Write ' . $path );
				}
				break;

			case 'mkdir':
				$path = $this->translate_path( (string) ( $step['path'] ?? '' ) );
				$result->add_step( 'mkdir -p ' . escapeshellarg( $path ), 'Create directory ' . $path );
				break;

			case 'rm':
			case 'rmdir':
				$path = $this->translate_path( (string) ( $step['path'] ?? '' ) );
				$result->add_step( 'rm -rf ' . escapeshellarg( $path ), 'Remove ' . $path );
				break;

			case 'mv':
			case 'cp':
				$from = $this->translate_path( (string) ( $step['fromPath'] ?? '' ) );
				$to   = $this->translate_path( (string) ( $step['toPath'] ?? '' ) );
				$bin  = $name === 'mv' ? 'mv' : 'cp -R';
				$result->add_step( sprintf( '%s %s %s', $bin, escapeshellarg( $from ), escapeshellarg( $to ) ), sprintf( '%s %s → %s', $name, $from, $to ) );
				break;

			case 'unzip':
				$zip  = ( $step['zipFile']['resource'] ?? '' ) === 'bundled'
					? (string) $this->bundled_container_path( $step['zipFile'], $result )
					: $this->translate_path( (string) ( $step['zipFile']['path'] ?? $step['zipPath'] ?? '' ) );
				$dest = $this->translate_path( (string) ( $step['extractToPath'] ?? '' ) );
				if ( $zip === '' ) {
					$result->add_unsupported( 'unzip', 'only zip files already present in the container can be extracted.' );
					break;
				}
				$result->add_step(
					sprintf( 'mkdir -p %s && unzip -o %s -d %s', escapeshellarg( $dest ), escapeshellarg( $zip ), escapeshellarg( $dest ) ),
					sprintf( 'Unzip %s into %s', $zip, $dest )
				);
				break;

			case 'importWxr':
			case 'importFile':
				$this->transpile_import_wxr( $step, $result );
				break;

			case 'request':
				$this->transpile_request( $step, $result );
				break;

			case 'defineSiteUrl':
				$result->add_unsupported( 'defineSiteUrl', 'QIT assigns the site URL when the environment boots.' );
				break;

			case 'enableMultisite':
				$result->add_unsupported( 'enableMultisite', 'QIT environments are single-site; multisite needs nginx and wp-config changes.' );
				break;

			case 'resetData':
			case 'importWordPressFiles':
			case 'runWpInstallationWizard':
			case 'importThemeStarterContent':
			case 'runPHPWithOptions':
			case 'writeFiles':
				$result->add_unsupported( (string) $name, 'Playground-specific step with no QIT equivalent.' );
				break;

			default:
				$result->add_unsupported( (string) $name, 'unknown step.' );
				break;
		}
	}

	/**
	 * Add a plugin/theme to the environment config, or fall back to a command.
	 *
	 * @param string                           $key           Either "plugins" or "themes".
	 * @param array<string, mixed>|string|null $resource_data The Blueprint resource.
	 * @param array<string, mixed>             $options       Blueprint step options.
	 * @param TranspiledBlueprint              $result        Accumulator for the transpiled output.
	 */
	private function add_extension( string $key, $resource_data, array $options, TranspiledBlueprint $result ): void {
		$parsed = $this->parse_resource( $resource_data, $result );

		if ( $parsed === null ) {
			$result->add_warning( sprintf( 'Skipped a %s entry: unsupported resource type.', rtrim( $key, 's' ) ) );

			return;
		}

		// WooCommerce is a first-class citizen in QIT: pin it instead of listing it.
		if ( $key === 'plugins' && ( $parsed['slug'] ?? '' ) === 'woocommerce' && $parsed['from'] === 'wporg' ) {
			$result->env_config['woocommerce_version'] = $parsed['version'] ?? 'stable';

			return;
		}

		$result->env_config[ $key ]   = $result->env_config[ $key ] ?? [];
		$result->env_config[ $key ][] = $parsed;

		$slug     = $parsed['slug'];
		$activate = $options['activate'] ?? true;

		if ( $key === 'plugins' && $activate === false ) {
			// QIT activates everything it installs, so undo it to match the Blueprint.
			$result->add_step( 'wp plugin deactivate ' . escapeshellarg( $slug ), 'Deactivate plugin ' . $slug );
		}

		if ( $key === 'themes' && $activate === true ) {
			$result->add_step( 'wp theme activate ' . escapeshellarg( $slug ), 'Activate theme ' . $slug );
		}
	}

	/**
	 * Resolve a "bundled" resource to an absolute path next to the Blueprint.
	 *
	 * @param array<string, mixed> $resource_data The Blueprint resource.
	 *
	 * @return string|null Null when it cannot be resolved, with a warning recorded.
	 */
	private function resolve_bundled_path( array $resource_data, TranspiledBlueprint $result ): ?string {
		$relative = (string) ( $resource_data['path'] ?? '' );

		if ( $relative === '' ) {
			return null;
		}

		if ( $this->bundle_dir === null ) {
			$result->add_warning( sprintf( 'Cannot resolve bundled file "%s": the Blueprint path is unknown.', $relative ) );

			return null;
		}

		// Strip a leading "./" only — ltrim() with a character list would also eat
		// the dots of "../", quietly turning an escape into a different local file.
		$resolved = realpath( $this->bundle_dir . '/' . preg_replace( '#^\./#', '', $relative ) );

		if ( $resolved === false || ! is_file( $resolved ) ) {
			$result->add_warning( sprintf( 'Bundled file "%s" was not found next to the Blueprint.', $relative ) );

			return null;
		}

		// A Blueprint may only ship files from its own directory.
		if ( strpos( $resolved, $this->bundle_dir . '/' ) !== 0 ) {
			$result->add_warning( sprintf( 'Bundled file "%s" resolves outside the Blueprint directory and was skipped.', $relative ) );

			return null;
		}

		return $resolved;
	}

	/**
	 * The in-container path of a bundled file, shipped with the generated package.
	 *
	 * @param array<string, mixed> $resource_data The Blueprint resource.
	 */
	private function bundled_container_path( array $resource_data, TranspiledBlueprint $result ): ?string {
		$source = $this->resolve_bundled_path( $resource_data, $result );

		return $source === null ? null : $result->add_asset( $source );
	}

	/**
	 * Convert a Blueprint resource into a QIT extension entry.
	 *
	 * @param array<string, mixed>|string|null $resource_data The Blueprint resource.
	 * @param TranspiledBlueprint              $result        Accumulator for the transpiled output.
	 *
	 * @return array<string, string>|null Null when the resource cannot be mapped.
	 */
	private function parse_resource( $resource_data, TranspiledBlueprint $result ): ?array {
		if ( is_string( $resource_data ) ) {
			// Shorthand: a bare slug or a URL.
			if ( preg_match( '#^https?://#i', $resource_data ) === 1 ) {
				return [
					'slug' => $this->slug_from_url( $resource_data ),
					'from' => 'url',
					'url'  => $resource_data,
				];
			}

			return [
				'slug' => $resource_data,
				'from' => 'wporg',
			];
		}

		if ( ! is_array( $resource_data ) ) {
			return null;
		}

		$type = $resource_data['resource'] ?? '';

		if ( $type === 'wordpress.org/plugins' || $type === 'wordpress.org/themes' ) {
			$entry = [
				'slug' => (string) ( $resource_data['slug'] ?? '' ),
				'from' => 'wporg',
			];
			if ( ! empty( $resource_data['version'] ) ) {
				$entry['version'] = (string) $resource_data['version'];
			}

			return $entry['slug'] === '' ? null : $entry;
		}

		if ( $type === 'bundled' ) {
			// Shipped next to the Blueprint; QIT installs local zips and directories already.
			$source = $this->resolve_bundled_path( $resource_data, $result );

			return $source === null ? null : [
				'slug' => $this->slug_from_url( $source ),
				'from' => 'local',
				'path' => $source,
			];
		}

		if ( $type === 'url' ) {
			$url = (string) ( $resource_data['url'] ?? '' );

			return $url === '' ? null : [
				'slug' => (string) ( $resource_data['slug'] ?? $this->slug_from_url( $url ) ),
				'from' => 'url',
				'url'  => $url,
			];
		}

		return null;
	}

	/**
	 * @param array<string, mixed> $consts
	 */
	private function transpile_constants( array $consts, TranspiledBlueprint $result ): void {
		foreach ( $consts as $name => $value ) {
			$raw = is_bool( $value ) || is_int( $value ) || is_float( $value ) || $value === null;

			$result->add_step(
				sprintf(
					'wp config set %s %s --type=constant%s',
					escapeshellarg( (string) $name ),
					escapeshellarg( $this->scalar_to_cli( $value ) ),
					$raw ? ' --raw' : ''
				),
				'Define constant ' . $name
			);
		}
	}

	/**
	 * @param array<string, mixed> $options
	 */
	private function transpile_site_options( array $options, TranspiledBlueprint $result ): void {
		if ( empty( $options ) ) {
			return;
		}

		// One WP bootstrap for the whole set, and update_option() semantics rather
		// than WP-CLI's: `wp option update` calls an unchanged value an error, which
		// Playground does not. Options WordPress refuses to store are still reported.
		$php = "<?php\n"
			. '$qit_options = json_decode( base64_decode( ' . var_export( base64_encode( (string) json_encode( $options ) ), true ) . " ), true );\n"
			. <<<'PHP'
foreach ( $qit_options as $qit_key => $qit_value ) {
	if ( get_option( $qit_key ) === $qit_value ) {
		WP_CLI::log( "unchanged: $qit_key" );
		continue;
	}

	update_option( $qit_key, $qit_value );

	if ( get_option( $qit_key ) != $qit_value ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		WP_CLI::warning( "option not applied by WordPress: $qit_key" );
	} else {
		WP_CLI::log( "set: $qit_key" );
	}
}
PHP;

		$this->add_payload_step(
			$php,
			'php',
			'wp eval-file %s',
			sprintf( 'Set %d site option(s)', count( $options ) ),
			$result
		);
	}

	/**
	 * @param array<string, mixed> $step
	 */
	private function transpile_import_wxr( array $step, TranspiledBlueprint $result ): void {
		$file = $step['file'] ?? null;
		$url  = is_array( $file ) && ( $file['resource'] ?? '' ) === 'url' ? (string) $file['url'] : null;

		$result->add_step(
			'wp plugin install wordpress-importer --activate',
			'Install the WordPress Importer (needed by importWxr)'
		);

		if ( $url !== null ) {
			$target = '/tmp/qit-blueprint-import.xml';
			$result->add_step(
				sprintf( 'curl -fsSL %s -o %s', escapeshellarg( $url ), escapeshellarg( $target ) ),
				'Download WXR file'
			);
			$result->add_step(
				sprintf( 'wp import %s --authors=create', escapeshellarg( $target ) ),
				'Import WXR content'
			);

			return;
		}

		if ( is_array( $file ) && ( $file['resource'] ?? '' ) === 'bundled' ) {
			$bundled = $this->bundled_container_path( $file, $result );

			if ( $bundled === null ) {
				$result->add_unsupported( 'importWxr', 'the bundled WXR file could not be resolved.' );

				return;
			}

			$result->add_step(
				sprintf( 'wp import %s --authors=create', escapeshellarg( $bundled ) ),
				'Import bundled WXR content'
			);

			return;
		}

		$contents = $this->resource_contents( $file, $result, 'importWxr' );

		if ( $contents === null ) {
			$result->add_unsupported( 'importWxr', 'only "url", "bundled" and inline literal WXR resources are supported.' );

			return;
		}

		$target = '/tmp/qit-blueprint-import.xml';
		$result->add_step( $this->write_file_command( $target, $contents ), 'Write WXR file' );
		$result->add_step( sprintf( 'wp import %s --authors=create', escapeshellarg( $target ) ), 'Import WXR content' );
	}

	/**
	 * @param array<string, mixed> $step
	 */
	private function transpile_request( array $step, TranspiledBlueprint $result ): void {
		$request = $step['request'] ?? [];
		$url     = (string) ( $request['url'] ?? '' );

		if ( $url === '' ) {
			$result->add_unsupported( 'request', 'no URL given.' );

			return;
		}

		$method = strtoupper( (string) ( $request['method'] ?? 'GET' ) );
		$body   = $request['body'] ?? null;

		// Relative URLs are resolved against the environment's own site URL.
		$target = preg_match( '#^https?://#i', $url ) === 1
			? escapeshellarg( $url )
			: '"$(wp option get home)' . $this->escape_double_quoted( $url ) . '"';

		$command = sprintf( 'curl -fsSL -X %s', escapeshellarg( $method ) );

		if ( is_string( $body ) && $body !== '' ) {
			$command .= ' --data ' . escapeshellarg( $body );
		} elseif ( is_array( $body ) && ! empty( $body ) ) {
			foreach ( $body as $field => $value ) {
				$command .= ' --data-urlencode ' . escapeshellarg( $field . '=' . $this->scalar_to_cli( $value ) );
			}
		}

		$result->add_step( $command . ' ' . $target . ' > /dev/null', sprintf( '%s request to %s', $method, $url ) );
	}

	/**
	 * Write a payload to a temp file inside the container and run a command on it.
	 *
	 * Base64 keeps arbitrary PHP/SQL out of shell quoting entirely.
	 *
	 * @param mixed               $payload          The file contents.
	 * @param string              $extension        Temp file extension.
	 * @param string              $command_template sprintf template receiving the temp file path.
	 * @param string              $description      Human-readable label.
	 * @param TranspiledBlueprint $result           Accumulator for the transpiled output.
	 */
	private function add_payload_step( $payload, string $extension, string $command_template, string $description, TranspiledBlueprint $result ): void {
		$contents = $this->resource_contents( $payload, $result, $description );

		if ( $contents === null || $contents === '' ) {
			return;
		}

		$path = sprintf( '/tmp/qit-blueprint-%s.%s', substr( md5( $contents ), 0, 8 ), $extension );

		$result->add_step(
			$this->write_file_command( $path, $contents ) . ' && ' . sprintf( $command_template, escapeshellarg( $path ) ),
			$description
		);
	}

	/**
	 * Resolve a Blueprint value that may be a plain string or a resource object.
	 *
	 * @param mixed               $value  The Blueprint value.
	 * @param TranspiledBlueprint $result Accumulator for the transpiled output.
	 * @param string              $step   Step name, for warnings.
	 */
	private function resource_contents( $value, TranspiledBlueprint $result, string $step ): ?string {
		if ( is_string( $value ) ) {
			return $value;
		}

		if ( ! is_array( $value ) ) {
			return null;
		}

		$type = $value['resource'] ?? '';

		if ( $type === 'literal' || $type === 'literal:directory' ) {
			return isset( $value['contents'] ) ? (string) $value['contents'] : null;
		}

		if ( $type === 'vfs' ) {
			$result->add_warning( sprintf( '%s references a vfs resource; Blueprint bundles are not supported yet.', $step ) );

			return null;
		}

		return null;
	}

	/**
	 * Base64 round-trip so payloads survive shell quoting untouched.
	 */
	private function write_file_command( string $path, string $contents ): string {
		return sprintf(
			'mkdir -p %s && printf %%s %s | base64 -d > %s',
			escapeshellarg( dirname( $path ) ),
			escapeshellarg( base64_encode( $contents ) ),
			escapeshellarg( $path )
		);
	}

	/**
	 * Map Playground's /wordpress root onto the QIT container's document root.
	 */
	public function translate_path( string $path ): string {
		if ( $path === '' ) {
			return $path;
		}

		if ( strpos( $path, self::PLAYGROUND_WP_ROOT ) === 0 ) {
			return self::QIT_WP_ROOT . substr( $path, strlen( self::PLAYGROUND_WP_ROOT ) );
		}

		if ( strpos( $path, '/' ) !== 0 ) {
			return rtrim( self::QIT_WP_ROOT, '/' ) . '/' . ltrim( $path, './' );
		}

		return $path;
	}

	/**
	 * Rewrite Playground's WordPress root inside a PHP/SQL payload.
	 *
	 * Blueprints commonly do require_once '/wordpress/wp-load.php', which would
	 * fatal in a QIT container.
	 *
	 * @param mixed $code The payload, when it is a plain string.
	 *
	 * @return mixed
	 */
	private function translate_paths_in_code( $code ) {
		if ( ! is_string( $code ) ) {
			return $code;
		}

		// Match URLs first so a host path like https://example.com/wordpress/x.zip
		// survives; only bare filesystem paths are rewritten.
		return (string) preg_replace_callback(
			'#https?://\S*|' . preg_quote( self::PLAYGROUND_WP_ROOT, '#' ) . '/#',
			static function ( array $match ): string {
				return strpos( $match[0], '://' ) !== false ? $match[0] : self::QIT_WP_ROOT . '/';
			},
			$code
		);
	}

	/**
	 * Blueprint wp-cli steps carry the full command, sometimes without the binary.
	 */
	private function normalize_wp_cli( string $command ): string {
		return strpos( $command, 'wp ' ) === 0 ? $command : 'wp ' . $command;
	}

	/**
	 * "my-plugin/my-plugin.php" → "my-plugin".
	 */
	private function plugin_slug_from_path( string $path ): string {
		$path = trim( $path, '/' );

		if ( $path === '' ) {
			return '';
		}

		if ( strpos( $path, '/' ) !== false ) {
			return explode( '/', $path )[0];
		}

		return preg_replace( '/\.php$/', '', $path ) ?? $path;
	}

	private function slug_from_url( string $url ): string {
		$name = basename( (string) parse_url( $url, PHP_URL_PATH ) );
		$name = preg_replace( '/\.zip$/i', '', $name ) ?? $name;
		$name = preg_replace( '/[^a-zA-Z0-9_-]/', '-', $name ) ?? $name;

		return $name === '' ? 'blueprint-extension' : $name;
	}

	/**
	 * @param mixed $value
	 */
	private function needs_json_format( $value ): bool {
		return is_array( $value );
	}

	/**
	 * @param mixed $value
	 */
	private function scalar_to_cli( $value ): string {
		if ( is_bool( $value ) ) {
			return $value ? 'true' : 'false';
		}

		if ( $value === null ) {
			return 'null';
		}

		if ( is_array( $value ) ) {
			return (string) json_encode( $value );
		}

		return (string) $value;
	}

	private function escape_double_quoted( string $value ): string {
		return str_replace( [ '\\', '"', '$', '`' ], [ '\\\\', '\"', '\$', '\`' ], $value );
	}
}
