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
trait CommandReuseTrait {
	protected function add_option_from_command( string $command_name, string $option_name ): self {
		/**
		 * @var \Symfony\Component\Console\Command\Command $this The command to add options.
		 */
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

		return $this;
	}
}
