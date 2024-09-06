<?php

namespace QIT_CLI;

use ReflectionProperty;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputOption;

/**
 * This is a trait meant to be used in the context of Command classes.
 *
 * It allows to reuse options declared in other commands.
 */
trait OptionReuseTrait {
	protected $reused_options = [];

	protected function reuseOption( string $command_name, string $option_name ): self { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid -- Use camelCase for consistency with the context where this is used.
		$command = App::make( Application::class )->find( $command_name );

		$option = $command->getDefinition()->getOption( $option_name );

		// Using reflection to access the 'mode' private property of the option.
		$reflected_option = new ReflectionProperty( InputOption::class, 'mode' );
		$reflected_option->setAccessible( true );
		$mode = $reflected_option->getValue( $option );

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
