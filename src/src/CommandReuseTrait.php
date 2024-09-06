<?php

namespace QIT_CLI;

use ReflectionProperty;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Input\InputOption;

/**
 * This is a trait meant to be used in the context of Command classes.
 *
 * It allows to reuse options declared in other commands.
 */
trait CommandReuseTrait {
	protected function add_option_from_command( string $command_name, string $option_name ) {
		/**
		 * @var Command $this
		 */
		$command = App::make( 'app' )->find( $command_name );

		if ( ! $command ) {
			throw new CommandNotFoundException( "Command {$command_name} not found." );
		}

		/** @var InputOption $option */
		$option = $command->getDefinition()->getOption( $option_name );

		if ( ! $option ) {
			throw new \InvalidArgumentException( "Option {$option_name} not found in command {$command_name}." );
		}

		// Using reflection to access the 'mode' private property of the option
		$reflectedOption = new ReflectionProperty( InputOption::class, 'mode' );
		$reflectedOption->setAccessible( true );
		$mode = $reflectedOption->getValue( $option );

		if ( $mode === InputOption::VALUE_NONE ) {
			$default = null;
		} else {
			$default = $option->getDefault();
		}

		$this->addOption(
			$option->getName(),
			$option->getShortcut(),
			$mode,
			$option->getDescription(),
			$default
		);
	}
}