<?php
namespace QIT_CLI\PreCommand\Pipeline;

use QIT_CLI\Commands\QITCommand;
use QIT_CLI\PreCommand\Configuration\ResolvedConfiguration;
use QIT_CLI\PreCommand\Results\EnvironmentResult;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Mutable data bag passed through pipeline stages.
 * Use typed getters/setters where practical; fall back to $data for misc values.
 */
class PipelineContext {

	public QITCommand $command;
	public InputInterface $input;
	public OutputInterface $output;

	/** @var ?object final result produced by one of the Build* stages */
	private ?object $result = null;

	/** @var array<string,mixed> ad‑hoc stage data */
	public array $data = [];

	public function __construct(
		QITCommand $command,
		InputInterface $input,
		OutputInterface $output
	) {
		$this->command = $command;
		$this->input   = $input;
		$this->output  = $output;
	}

	public function set_result( object $result ): self {
		$this->result = $result;
		return $this;
	}

	public function get_result(): ?object {
		return $this->result;
	}

	/**
	 * Generic setter for context data.
	 *
	 * @param string $key   The key to set.
	 * @param mixed  $value The value to set.
	 * @return self
	 */
	public function set( string $key, $value ): self {
		$this->data[ $key ] = $value;
		return $this;
	}

	/**
	 * Generic getter for context data.
	 *
	 * @param string $key           The key to retrieve.
	 * @param mixed  $default_value The default value if key doesn't exist.
	 * @return mixed The value from context or default.
	 */
	public function get( string $key, $default_value = null ) {
		return $this->data[ $key ] ?? $default_value;
	}

	/**
	 * Get the resolved configuration.
	 *
	 * @return ResolvedConfiguration|null The resolved configuration or null if not set.
	 */
	public function get_resolved_config(): ?ResolvedConfiguration {
		return $this->get( 'resolved_config' );
	}

	/**
	 * Set the resolved configuration.
	 *
	 * @param ResolvedConfiguration $config The resolved configuration.
	 * @return self
	 */
	public function set_resolved_config( ResolvedConfiguration $config ): self {
		return $this->set( 'resolved_config', $config );
	}

	/**
	 * Get the environment result.
	 *
	 * @return EnvironmentResult|null The environment result or null if not set.
	 */
	public function get_env_result(): ?EnvironmentResult {
		return $this->get( 'env_result' );
	}

	/**
	 * Set the environment result.
	 *
	 * @param EnvironmentResult $env_result The environment result.
	 * @return self
	 */
	public function set_env_result( EnvironmentResult $env_result ): self {
		return $this->set( 'env_result', $env_result );
	}

	/**
	 * Get the test packages.
	 *
	 * @param array<string, array<string, mixed>> $default_value Default value if test packages are not set.
	 * @return array<string, array<string, mixed>> The test packages or default if not set.
	 */
	public function get_test_packages( array $default_value = [] ): array {
		return $this->get( 'test_packages', $default_value );
	}

	/**
	 * Set the test packages.
	 *
	 * @param array<string, array<string, mixed>> $test_packages The test packages.
	 * @return self
	 */
	public function set_test_packages( array $test_packages ): self {
		return $this->set( 'test_packages', $test_packages );
	}

	/**
	 * Get the configuration file path.
	 *
	 * @return string|null The configuration file path or null if not set.
	 */
	public function get_config_file(): ?string {
		return $this->get( 'config_file' );
	}

	/**
	 * Set the configuration file path.
	 *
	 * @param string $config_file The configuration file path.
	 * @return self
	 */
	public function set_config_file( string $config_file ): self {
		return $this->set( 'config_file', $config_file );
	}

	/**
	 * Get the SUT slug.
	 *
	 * @return string|null The SUT slug or null if not set.
	 */
	public function get_sut_slug(): ?string {
		return $this->get( 'sut_slug' );
	}

	/**
	 * Set the SUT slug.
	 *
	 * @param string $sut_slug The SUT slug.
	 * @return self
	 */
	public function set_sut_slug( string $sut_slug ): self {
		return $this->set( 'sut_slug', $sut_slug );
	}

	/**
	 * Get the SUT type.
	 *
	 * @return string|null The SUT type or null if not set.
	 */
	public function get_sut_type(): ?string {
		return $this->get( 'sut_type' );
	}

	/**
	 * Set the SUT type.
	 *
	 * @param string $sut_type The SUT type.
	 * @return self
	 */
	public function set_sut_type( string $sut_type ): self {
		return $this->set( 'sut_type', $sut_type );
	}
}
