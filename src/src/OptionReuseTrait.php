<?php

namespace QIT_CLI;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputOption;

/**
 * This is a trait meant to be used in the context of Command classes.
 *
 * It allows to reuse options declared in other commands.
 */
trait OptionReuseTrait {
	/** @var array<string> */
	protected $reused_options = [];

	protected function reuseOption( string $command_name, string $option_name ): self { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid -- Use camelCase for consistency with the context where this is used.
		$command = App::make( Application::class )->find( $command_name );

		try {
			$option = $command->getDefinition()->getOption( $option_name );
		} catch ( \InvalidArgumentException $e ) {
			throw new \InvalidArgumentException(
				sprintf(
					'Failed to reuse option "%s" from command "%s" in "%s". The option does not exist.',
					$option_name,
					$command_name,
					__CLASS__
				),
				0,
				$e
			);
		}

		// Reconstruct the option's "mode" bitmask from the public API.
		if ( ! $option->acceptValue() ) {
			$mode = InputOption::VALUE_NONE;
		} else {
			$mode = $option->isValueRequired() ? InputOption::VALUE_REQUIRED : InputOption::VALUE_OPTIONAL;

			if ( $option->isArray() ) {
				$mode |= InputOption::VALUE_IS_ARRAY;
			}
		}

		if ( $option->isNegatable() ) {
			$mode |= InputOption::VALUE_NEGATABLE;
		}

		if ( $mode === InputOption::VALUE_NONE ) {
			$default = null;
		} else {
			$default = $option->getDefault();
		}

		// @phan-suppress-next-line PhanUndeclaredMethod
		$this->addOption(
			$option->getName(),
			$option->getShortcut(),
			$mode,
			$option->getDescription(),
			$default
		);

		$this->reused_options[] = $option_name;

		return $this;
	}
}
