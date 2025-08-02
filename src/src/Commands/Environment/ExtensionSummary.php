<?php

namespace QIT_CLI\Commands\Environment;

use QIT_CLI\Environment\Environments\E2E\E2EEnvironment;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Handles the display of extension (plugins and themes) information in a formatted table.
 */
class ExtensionSummary {
	/** @var E2EEnvironment */
	private E2EEnvironment $e2e_environment;

	public function __construct( E2EEnvironment $e2e_environment ) {
		$this->e2e_environment = $e2e_environment;
	}

	/**
	 * Render plugins and themes tables.
	 *
	 * @param OutputInterface $output
	 * @param E2EEnvInfo      $info
	 * @return void
	 */
	public function render_extension_tables( OutputInterface $output, E2EEnvInfo $info ): void {
		$this->render_plugins_table( $output, $info );

		// Only show themes in verbose mode
		if ( $output->isVerbose() ) {
			$this->render_themes_table( $output, $info );
		}
	}

	/**
	 * Render plugins table.
	 *
	 * @param OutputInterface $output
	 * @param E2EEnvInfo      $info
	 * @return void
	 */
	private function render_plugins_table( OutputInterface $output, E2EEnvInfo $info ): void {
		if ( empty( $info->plugins ) ) {
			return;
		}

		$output->writeln( 'Plugins:' );
		$table = new Table( $output );
		$table
			->setStyle( 'box' )
			->setHeaders( [ 'Slug', 'Version', 'Source', 'Status' ] );

		foreach ( $info->plugins as $plugin ) {
			$table->addRow( [
				$plugin->slug,
				$this->get_plugin_version( $info, $plugin->slug ),
				$plugin->from ?? 'unknown',
				$this->get_plugin_status( $info, $plugin->slug ),
			] );
		}

		$table->render();
	}

	/**
	 * Render themes table.
	 *
	 * @param OutputInterface $output
	 * @param E2EEnvInfo      $info
	 * @return void
	 */
	private function render_themes_table( OutputInterface $output, E2EEnvInfo $info ): void {
		// Get all installed themes from WordPress
		$installed_themes = $this->get_installed_themes( $info );
		if ( empty( $installed_themes ) ) {
			return;
		}

		$output->writeln( 'Themes:' );
		$table = new Table( $output );
		$table
			->setStyle( 'box' )
			->setHeaders( [ 'Slug', 'Version', 'Source', 'Status' ] );

		// Create source mapping from our internal data
		$theme_source_map = $this->create_source_map( $info->themes );

		foreach ( $installed_themes as $theme ) {
			$table->addRow( [
				$theme['name'],
				$theme['version'] ?? 'unknown',
				$theme_source_map[ $theme['name'] ] ?? 'unknown',
				$theme['status'] ?? 'unknown',
			] );
		}

		$table->render();
	}

	/**
	 * Get all themes from WordPress environment via WP CLI.
	 *
	 * @param E2EEnvInfo $info
	 * @return array<string,mixed>
	 */
	private function get_installed_themes( E2EEnvInfo $info ): array {
		try {
			$reflection      = new \ReflectionClass( $this->e2e_environment );
			$docker_property = $reflection->getProperty( 'docker' );
			$docker_property->setAccessible( true );
			$docker = $docker_property->getValue( $this->e2e_environment );

			$output = $docker->run_inside_docker(
				$info,
				[ 'wp', 'theme', 'list', '--fields=name,status,version', '--format=json' ],
				[ 'WP_CLI_ALLOW_ROOT' => 'true' ],
				'0:0',
				30
			);

			return json_decode( trim( $output ), true ) ?: [];
		} catch ( \Exception $e ) {
			return [];
		}
	}

	/**
	 * Create source mapping from our internal extension data.
	 *
	 * @param array<\QIT_CLI\PreCommand\Objects\Extension> $extensions
	 * @return array<string,string>
	 */
	private function create_source_map( array $extensions ): array {
		$source_map = [];
		foreach ( $extensions as $extension ) {
			$source_map[ $extension->slug ] = $extension->from ?? 'unknown';
		}
		return $source_map;
	}

	/**
	 * Get plugin status from the WordPress environment.
	 *
	 * @param E2EEnvInfo $info
	 * @param string     $plugin_slug
	 * @return string
	 */
	private function get_plugin_status( E2EEnvInfo $info, string $plugin_slug ): string {
		try {
			// Access the docker property through reflection since it's protected
			$reflection      = new \ReflectionClass( $this->e2e_environment );
			$docker_property = $reflection->getProperty( 'docker' );
			$docker_property->setAccessible( true );
			$docker = $docker_property->getValue( $this->e2e_environment );

			$output = $docker->run_inside_docker(
				$info,
				[ 'wp', 'plugin', 'is-active', $plugin_slug ],
				[ 'WP_CLI_ALLOW_ROOT' => 'true' ],
				'0:0',
				30
			);
			return trim( $output ) === '' ? 'active' : 'inactive';
		} catch ( \Exception $e ) {
			return 'unknown';
		}
	}

	/**
	 * Get plugin version from the WordPress environment.
	 *
	 * @param E2EEnvInfo $info
	 * @param string     $plugin_slug
	 * @return string
	 */
	private function get_plugin_version( E2EEnvInfo $info, string $plugin_slug ): string {
		try {
			// Access the docker property through reflection since it's protected
			$reflection      = new \ReflectionClass( $this->e2e_environment );
			$docker_property = $reflection->getProperty( 'docker' );
			$docker_property->setAccessible( true );
			$docker = $docker_property->getValue( $this->e2e_environment );

			$output = $docker->run_inside_docker(
				$info,
				[ 'wp', 'plugin', 'get', $plugin_slug, '--field=version' ],
				[ 'WP_CLI_ALLOW_ROOT' => 'true' ],
				'0:0',
				30
			);
			return trim( $output ) ?: 'unknown';
		} catch ( \Exception $e ) {
			return 'unknown';
		}
	}
}
