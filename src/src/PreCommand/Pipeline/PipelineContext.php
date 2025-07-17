<?php
namespace QIT_CLI\PreCommand\Pipeline;

use QIT_CLI\Commands\QITCommand;
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

	public function set( string $key, $value ): self {
		$this->data[ $key ] = $value;
		return $this;
	}

	/**
	 * Get a value from the context data.
	 *
	 * @param string $key The key to retrieve.
	 * @param mixed  $default_value The default value if key doesn't exist.
	 * @return mixed The value from context or default.
	 */
	public function get( string $key, $default_value = null ) {
		return $this->data[ $key ] ?? $default_value;
	}
}
