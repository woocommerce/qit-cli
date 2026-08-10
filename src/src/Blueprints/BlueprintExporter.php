<?php

namespace QIT_CLI\Blueprints;

use QIT_CLI\PreCommand\Configuration\ConfigMerger;

/**
 * The reverse direction: a QIT environment block → a Playground Blueprint (v1).
 *
 * Useful to hand a failing QIT environment to someone as a Playground link.
 * QIT can express things Playground cannot (local paths, Docker volumes, PHP
 * extensions, Xdebug), so the export is lossy by design and reports what it
 * dropped.
 */
class BlueprintExporter {

	/** @var array<string, string> QIT WordPress versions → Playground aliases. */
	private static array $wp_aliases = [
		'stable'  => 'latest',
		'latest'  => 'latest',
		'rc'      => 'beta',
		'beta'    => 'beta',
		'nightly' => 'nightly',
		'trunk'   => 'nightly',
	];

	/** @var string[] */
	private array $warnings = [];

	/**
	 * @param array<string, mixed> $env_config A qit.json "environments.<name>" block.
	 *
	 * @return array<string, mixed> A Blueprint, ready to json_encode().
	 */
	public function export( array $env_config ): array {
		$this->warnings = [];

		// qit.json allows the short forms (php/wp/woo); work with canonical keys.
		$env_config = ConfigMerger::normalize( $env_config );

		$blueprint = [
			'$schema'           => 'https://playground.wordpress.net/blueprint-schema.json',
			'landingPage'       => '/wp-admin/',
			'preferredVersions' => [],
			'steps'             => [
				[
					'step'     => 'login',
					'username' => 'admin',
					'password' => 'password',
				],
			],
		];

		if ( ! empty( $env_config['php_version'] ) ) {
			$blueprint['preferredVersions']['php'] = (string) $env_config['php_version'];
		}

		if ( ! empty( $env_config['wordpress_version'] ) ) {
			$wp                                   = (string) $env_config['wordpress_version'];
			$blueprint['preferredVersions']['wp'] = self::$wp_aliases[ $wp ] ?? $wp;
		}

		if ( empty( $blueprint['preferredVersions'] ) ) {
			unset( $blueprint['preferredVersions'] );
		}

		// WooCommerce is pinned separately in QIT; Playground needs it as a plugin.
		if ( ! empty( $env_config['woocommerce_version'] ) ) {
			$blueprint['steps'][] = $this->install_step( 'plugin', [
				'slug'    => 'woocommerce',
				'from'    => 'wporg',
				'version' => (string) $env_config['woocommerce_version'],
			] );
		}

		foreach ( $env_config['plugins'] ?? [] as $plugin ) {
			$step = $this->install_step( 'plugin', $plugin );
			if ( $step !== null ) {
				$blueprint['steps'][] = $step;
			}
		}

		$activated_a_theme = false;

		foreach ( $env_config['themes'] ?? [] as $theme ) {
			// Only one theme can be active, and QIT activates the first one it installs.
			$step = $this->install_step( 'theme', $theme, ! $activated_a_theme );
			if ( $step !== null ) {
				$blueprint['steps'][] = $step;
				$activated_a_theme    = true;
			}
		}

		$this->warn_about_unexportable( $env_config );

		return $blueprint;
	}

	/**
	 * @return string[] Notes about anything that could not be expressed as a Blueprint.
	 */
	public function get_warnings(): array {
		return $this->warnings;
	}

	/**
	 * @param string                           $type      Either "plugin" or "theme".
	 * @param array<string, mixed>|string|null $extension A QIT extension entry.
	 * @param bool                             $activate  Whether the Blueprint should activate it.
	 *
	 * @return array<string, mixed>|null
	 */
	private function install_step( string $type, $extension, bool $activate = true ): ?array {
		if ( is_string( $extension ) ) {
			$extension = preg_match( '#^https?://#i', $extension ) === 1
				? [
					'slug' => basename( $extension ),
					'from' => 'url',
					'url'  => $extension,
				]
				: [
					'slug' => $extension,
					'from' => 'wporg',
				];
		}

		if ( ! is_array( $extension ) ) {
			return null;
		}

		$slug = (string) ( $extension['slug'] ?? '' );
		$from = (string) ( $extension['from'] ?? 'wporg' );

		if ( $slug === '' ) {
			return null;
		}

		if ( $from === 'wporg' ) {
			$resource = [
				'resource' => $type === 'plugin' ? 'wordpress.org/plugins' : 'wordpress.org/themes',
				'slug'     => $slug,
			];

			$version = (string) ( $extension['version'] ?? '' );
			if ( $version !== '' && $version !== 'stable' && $version !== 'latest' ) {
				$resource['version'] = $version;
			}
		} elseif ( $from === 'url' ) {
			$resource = [
				'resource' => 'url',
				'url'      => (string) ( $extension['url'] ?? '' ),
			];
		} else {
			$this->warnings[] = sprintf(
				'%s "%s" comes from "%s" and cannot be expressed in a Blueprint. Publish it or host the zip and re-export.',
				ucfirst( $type ),
				$slug,
				$from
			);

			return null;
		}

		if ( $type === 'plugin' ) {
			return [
				'step'       => 'installPlugin',
				'pluginData' => $resource,
				'options'    => [ 'activate' => $activate ],
			];
		}

		return [
			'step'      => 'installTheme',
			'themeData' => $resource,
			'options'   => [ 'activate' => $activate ],
		];
	}

	/**
	 * @param array<string, mixed> $env_config
	 */
	private function warn_about_unexportable( array $env_config ): void {
		$unexportable = [
			'volumes'        => 'Docker volume mounts',
			'php_extensions' => 'custom PHP extensions',
			'envs'           => 'environment variables',
			'object_cache'   => 'the Redis object cache',
			'utilities'      => 'utility packages',
			'global_setup'   => 'global setup packages',
		];

		foreach ( $unexportable as $key => $label ) {
			if ( ! empty( $env_config[ $key ] ) ) {
				$this->warnings[] = sprintf( 'Dropped %s: Playground has no equivalent.', $label );
			}
		}

		if ( ! empty( $env_config['xdebug'] ) ) {
			$this->warnings[] = 'Dropped Xdebug: Playground has no equivalent.';
		}
	}
}
