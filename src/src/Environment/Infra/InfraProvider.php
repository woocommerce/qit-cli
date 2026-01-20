<?php
declare( strict_types=1 );

namespace QIT_CLI\Environment\Infra;

use QIT_CLI\Environment\Environments\QITEnvInfo;

/**
 * Infrastructure provider interface for environment provisioning.
 * Implementations handle local (Docker) or cloud (remote) infrastructure.
 */
interface InfraProvider {
	/**
	 * Provision infrastructure (Docker containers or cloud instance).
	 * After this call, the environment should be ready for WordPress setup.
	 *
	 * @param QITEnvInfo $env Environment configuration.
	 *
	 * @throws \RuntimeException If provisioning fails.
	 */
	public function provision( QITEnvInfo $env ): void;

	/**
	 * Execute command inside the environment.
	 *
	 * @param QITEnvInfo           $env      Environment configuration.
	 * @param array<string>        $command  Command to execute (e.g., ['wp', 'plugin', 'list']).
	 * @param array<string,string> $env_vars Environment variables.
	 * @param int                  $timeout  Timeout in seconds (0 = no timeout).
	 *
	 * @return string Command stdout.
	 *
	 * @throws \RuntimeException If command fails.
	 */
	public function exec( QITEnvInfo $env, array $command, array $env_vars = [], int $timeout = 300 ): string;

	/**
	 * Get the site URL for the environment.
	 *
	 * @param QITEnvInfo $env Environment configuration.
	 *
	 * @return string Site URL (e.g., https://abc123.example.com).
	 */
	public function get_site_url( QITEnvInfo $env ): string;

	/**
	 * Reset environment to post-setup state (restore DB snapshot).
	 *
	 * @param QITEnvInfo $env Environment configuration.
	 */
	public function reset( QITEnvInfo $env ): void;

	/**
	 * Destroy the infrastructure (Docker down or release cloud instance).
	 *
	 * @param QITEnvInfo $env Environment configuration.
	 */
	public function destroy( QITEnvInfo $env ): void;

	/**
	 * Upload a file to the environment.
	 *
	 * @param QITEnvInfo $env         Environment configuration.
	 * @param string     $local_path  Local file path.
	 * @param string     $remote_path Remote destination path.
	 */
	public function upload( QITEnvInfo $env, string $local_path, string $remote_path ): void;

	/**
	 * Download a file from the environment.
	 *
	 * @param QITEnvInfo $env         Environment configuration.
	 * @param string     $remote_path Remote file path.
	 * @param string     $local_path  Local destination path.
	 */
	public function download( QITEnvInfo $env, string $remote_path, string $local_path ): void;

	/**
	 * Check if the infrastructure is healthy and responsive.
	 *
	 * @param QITEnvInfo $env Environment configuration.
	 *
	 * @return bool True if healthy.
	 */
	public function is_healthy( QITEnvInfo $env ): bool;

	/**
	 * Get the provider type identifier.
	 *
	 * @return string Provider type ('local' or 'cloud').
	 */
	public function get_type(): string;
}
