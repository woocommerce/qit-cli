<?php

namespace QIT_CLI\Config;

use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;

class MergedOptionsInputWrapper implements InputInterface
{
	private InputInterface $input;
	private array $mergedOptions;
	private array $arguments;

	public function __construct(InputInterface $input, array $mergedOptions, array $arguments)
	{
		$this->input = $input;
		$this->mergedOptions = $mergedOptions;
		$this->arguments = $arguments;
	}

	public function getOption(string $name)
	{
		// Return from merged options if set, otherwise fall back to the original input
		return array_key_exists($name, $this->mergedOptions) ? $this->mergedOptions[$name] : $this->input->getOption($name);
	}

	public function getArgument(string $name)
	{
		// Return from arguments if set, otherwise fall back to the original input
		return array_key_exists($name, $this->arguments) ? $this->arguments[$name] : $this->input->getArgument($name);
	}

	public function hasArgument(string $name): bool
	{
		return array_key_exists($name, $this->arguments) || $this->input->hasArgument($name);
	}

	public function getArguments(): array
	{
		// Merge arguments, giving priority to the custom arguments
		return array_merge($this->input->getArguments(), $this->arguments);
	}

	public function hasOption(string $name): bool
	{
		return array_key_exists($name, $this->mergedOptions) || $this->input->hasOption($name);
	}

	public function getOptions(): array
	{
		// Merge options, giving priority to the custom merged options
		return array_merge($this->input->getOptions(), $this->mergedOptions);
	}

	public function hasParameterOption($values, bool $onlyParams = false): bool
	{
		// Delegate to the original input
		return $this->input->hasParameterOption($values, $onlyParams);
	}

	public function getParameterOption($values, $default = false, bool $onlyParams = false)
	{
		// Delegate to the original input
		return $this->input->getParameterOption($values, $default, $onlyParams);
	}

	public function bind(InputDefinition $definition): void
	{
		$this->input->bind($definition);
	}

	public function validate(): void
	{
		$this->input->validate();
	}

	public function isInteractive(): bool
	{
		return $this->input->isInteractive();
	}

	public function setInteractive(bool $interactive): void
	{
		$this->input->setInteractive($interactive);
	}

	public function setOption(string $name, $value): void
	{
		$this->mergedOptions[$name] = $value;
	}

	public function setArgument(string $name, $value): void
	{
		$this->arguments[$name] = $value;
	}

	public function __toString(): string
	{
		return (string) $this->input;
	}

	public function getFirstArgument()
	{
		// Return the first argument from our arguments array, or fall back to the original input
		return !empty($this->arguments) ? reset($this->arguments) : $this->input->getFirstArgument();
	}
}